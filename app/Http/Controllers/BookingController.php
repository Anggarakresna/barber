<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Service;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    private const BOOKING_TIMEZONE = 'Asia/Jakarta';
    private const OPENING_HOUR = 9;
    private const CLOSING_HOUR = 20;
    private const DP_AMOUNT = 20000;
    private const PAYMENT_WINDOW_MINUTES = 30;

    /**
     * Show the booking form.
     */
    public function create()
    {
        $this->expireExpiredWaitingPaymentBookingsForAuthenticatedUser();

        $branches = Branch::orderBy('name')->get();
        $services = Service::all();
        $activeBooking = $this->getActiveBookingForAuthenticatedUser();

        return view('booking', compact('branches', 'services', 'activeBooking'));
    }

    /**
     * Return barbers belonging to a given branch (JSON, for AJAX).
     */
    public function barbersByBranch(Branch $branch)
    {
        $barbers = $branch->barbers()->where('is_active', true)->with('user')->get()->map(fn($b) => [
            'id'   => $b->id,
            'name' => $b->user->name,
        ]);

        return response()->json($barbers);
    }

    /**
     * Return available booking times for selected barber and date.
     */
    public function availableTimes(Request $request)
    {
        $validated = $request->validate([
            'barber_id' => ['required', 'exists:barbers,id'],
            'booking_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (!$this->isBookingDateAllowed($value)) {
                        $fail('Tanggal booking tidak boleh sebelum hari ini.');
                    }
                },
            ],
        ]);

        $operationalSlots = $this->getOperationalTimeSlotsForDate($validated['booking_date']);
        if (empty($operationalSlots) && $this->isTodayBookingClosed($validated['booking_date'])) {
            return response()->json([
                'available_times' => [],
                'message' => 'Jam booking hari ini sudah tutup',
            ]);
        }

        $bookedTimes = Booking::query()
            ->where('barber_id', $validated['barber_id'])
            ->whereDate('booking_date', $validated['booking_date'])
            ->active()
            ->pluck('booking_time')
            ->map(fn($time) => date('H:i', strtotime($time)))
            ->values()
            ->all();

        $availableTimes = array_values(array_diff($operationalSlots, $bookedTimes));

        $response = [
            'available_times' => $availableTimes,
        ];

        if (empty($availableTimes)) {
            $response['message'] = 'Slot pada tanggal ini sudah penuh.';
        }

        return response()->json($response);
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu untuk melakukan booking.');
        }

        $this->expireExpiredWaitingPaymentBookingsForAuthenticatedUser();

        if ($this->hasActiveBookingForAuthenticatedUser()) {
            $activeBooking = $this->getActiveBookingForAuthenticatedUser();
            $errorMessage = $activeBooking && $activeBooking->status === Booking::STATUS_WAITING_PAYMENT
                ? 'Anda masih memiliki booking yang menunggu pembayaran DP. Silakan selesaikan pembayaran di halaman My Booking.'
                : 'Anda masih memiliki booking yang sedang berjalan. Silakan tunggu hingga barber menyelesaikan booking Anda.';

            return redirect()->route('booking')
                ->with('error', $errorMessage);
        }

        $validator = validator($request->all(), [
            'branch_id' => ['required', 'exists:branches,id'],
            'barber_id' => ['required', 'exists:barbers,id'],
            'service_id' => ['required', 'exists:services,id'],
            'booking_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (!$this->isBookingDateAllowed($value)) {
                        $fail('Tanggal booking tidak boleh sebelum hari ini.');
                    }
                },
            ],
            'booking_time' => ['required', 'date_format:H:i', Rule::in($this->generateTimeSlots())],
            'total_people' => ['required', 'integer', 'min:1', 'max:5'],
        ], [
            'branch_id.required' => 'Silakan pilih cabang terlebih dahulu.',
            'branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'barber_id.required' => 'Silakan pilih barber terlebih dahulu.',
            'barber_id.exists' => 'Barber yang dipilih tidak valid.',
            'service_id.required' => 'Silakan pilih layanan terlebih dahulu.',
            'service_id.exists' => 'Layanan yang dipilih tidak valid.',
            'booking_date.required' => 'Silakan pilih tanggal booking.',
            'booking_date.date' => 'Format tanggal booking tidak valid.',
            'booking_time.required' => 'Silakan pilih jam booking.',
            'booking_time.date_format' => 'Format jam booking tidak valid.',
            'total_people.required' => 'Silakan isi jumlah orang.',
            'total_people.integer' => 'Jumlah orang harus berupa angka.',
            'total_people.min' => 'Jumlah orang minimal 1.',
            'total_people.max' => 'Jumlah orang maksimal 5.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->with('error', 'Silakan lengkapi data booking dengan benar.')
                ->withInput();
        }

        $validated = $validator->validated();

        $operationalSlots = $this->getOperationalTimeSlotsForDate($validated['booking_date']);
        if (!in_array($validated['booking_time'], $operationalSlots, true)) {
            $errorMessage = $this->isTodayBookingClosed($validated['booking_date'])
                ? 'Jam booking hari ini sudah tutup'
                : 'Jam booking tidak tersedia untuk waktu saat ini.';

            return back()
                ->withErrors(['booking_time' => $errorMessage])
                ->with('error', $errorMessage)
                ->withInput();
        }

        $barber = Barber::query()
            ->whereKey($validated['barber_id'])
            ->where('branch_id', $validated['branch_id'])
            ->first();

        if (!$barber) {
            return back()
                ->withErrors(['barber_id' => 'Barber tidak sesuai dengan cabang yang dipilih.'])
                ->with('error', 'Barber tidak sesuai dengan cabang yang dipilih.')
                ->withInput();
        }

        if (!$barber->is_active) {
            return back()
                ->withErrors(['barber_id' => 'Barber yang dipilih sedang tidak aktif.'])
                ->with('error', 'Barber yang dipilih sedang tidak aktif.')
                ->withInput();
        }

        $isSlotBooked = Booking::query()
            ->where('barber_id', $validated['barber_id'])
            ->whereDate('booking_date', $validated['booking_date'])
            ->whereTime('booking_time', $validated['booking_time'])
            ->active()
            ->exists();

        if ($isSlotBooked) {
            return back()
                ->withErrors(['booking_time' => 'Jadwal sudah dibooking'])
                ->with('error', 'Jadwal sudah dibooking')
                ->withInput();
        }

        $shouldUseMidtrans = $this->canCreateWaitingPaymentBooking();

        try {
            $booking = DB::transaction(function () use ($validated, $shouldUseMidtrans) {
                $bookingPayload = $this->buildBookingCreatePayload($validated, $shouldUseMidtrans);

                return Booking::create($bookingPayload);
            });
        } catch (QueryException $exception) {
            Log::error('Gagal menyimpan booking.', [
                'user_id' => Auth::id(),
                'exception' => $exception->getMessage(),
            ]);

            $message = $this->isDuplicateBookingSlotException($exception)
                ? 'Jadwal sudah terisi. Silakan pilih jam lain.'
                : 'Terjadi kesalahan saat menyimpan booking. Periksa kembali data booking Anda.';

            return back()
                ->withErrors(['booking' => $message])
                ->with('error', $message)
                ->withInput();
        }

        if (!$shouldUseMidtrans) {
            Log::warning('Booking dibuat tanpa Midtrans karena schema/konfigurasi belum siap.', [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'missing_columns' => $this->getMissingBookingColumnsForPayment(),
                'midtrans_ready' => $this->hasMidtransConfiguration(),
            ]);

            return redirect()->route('my-booking')
                ->with('success', 'Booking berhasil dibuat.');
        }

        try {
            $midtransService = app(MidtransService::class);
            $transaction = $midtransService->createDpTransaction($booking, self::PAYMENT_WINDOW_MINUTES);

            $booking->update($this->buildBookingPaymentUpdatePayload($transaction));

            return redirect()->route('my-booking')
                ->with('success', 'Booking berhasil dibuat. Silakan selesaikan pembayaran DP dalam 30 menit.')
                ->with('open_midtrans_on_load', true);
        } catch (\Throwable $exception) {
            Log::error('Gagal membuat transaksi Midtrans saat booking.', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'exception' => $exception->getMessage(),
            ]);

            return redirect()->route('my-booking')
                ->with('error', 'Booking berhasil dibuat, tetapi transaksi pembayaran sedang disiapkan. Silakan buka kembali My Booking dalam beberapa detik.')
                ->with('open_midtrans_on_load', true);
        }
    }

    /**
     * Cancel a booking owned by the currently logged-in customer.
     */
    public function cancel(Booking $booking)
    {
        // Ensure the booking belongs to the authenticated user
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // Only waiting payment, pending, or confirmed bookings can be cancelled
        if (!in_array($booking->status, [Booking::STATUS_WAITING_PAYMENT, Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED], true)) {
            return redirect()->route('my-booking')
                ->with('error', 'Booking tidak dapat dibatalkan karena statusnya sudah ' . $booking->status . '.');
        }

        $payload = [
            'status' => Booking::STATUS_CANCELLED,
        ];

        if ($booking->status === Booking::STATUS_WAITING_PAYMENT && in_array($booking->payment_status, [null, Booking::PAYMENT_STATUS_UNPAID], true)) {
            $payload['payment_status'] = Booking::PAYMENT_STATUS_CANCELLED;
        }

        $booking->update($payload);

        return redirect()->route('my-booking')
            ->with('success', 'Booking berhasil dibatalkan.');
    }

    /**
     * Generate available hourly slot labels.
     */
    private function generateTimeSlots(): array
    {
        $slots = [];

        for ($hour = self::OPENING_HOUR; $hour <= self::CLOSING_HOUR; $hour++) {
            $slots[] = sprintf('%02d:00', $hour);
        }

        return $slots;
    }

    /**
     * Build operational slots by booking date using Asia/Jakarta time.
     */
    private function getOperationalTimeSlotsForDate(string $bookingDate): array
    {
        $allSlots = $this->generateTimeSlots();

        if (!$this->isTodayInJakarta($bookingDate)) {
            return $allSlots;
        }

        if ($this->isTodayBookingClosed($bookingDate)) {
            return [];
        }

        $now = $this->nowInJakarta();
        $minimumHour = (int) $now->format('H');

        if ((int) $now->format('i') > 0) {
            $minimumHour++;
        }

        return array_values(array_filter($allSlots, function ($slot) use ($minimumHour) {
            $slotHour = (int) substr($slot, 0, 2);

            return $slotHour >= $minimumHour;
        }));
    }

    /**
     * Check if booking date is today or future in Jakarta timezone.
     */
    private function isBookingDateAllowed(string $bookingDate): bool
    {
        $requestedDate = Carbon::parse($bookingDate, self::BOOKING_TIMEZONE)->startOfDay();
        $today = $this->nowInJakarta()->startOfDay();

        return $requestedDate->greaterThanOrEqualTo($today);
    }

    /**
     * Check if provided date is today in Jakarta timezone.
     */
    private function isTodayInJakarta(string $bookingDate): bool
    {
        return Carbon::parse($bookingDate, self::BOOKING_TIMEZONE)->toDateString() === $this->nowInJakarta()->toDateString();
    }

    /**
     * Check if today's operational time has already ended in Jakarta.
     */
    private function isTodayBookingClosed(string $bookingDate): bool
    {
        if (!$this->isTodayInJakarta($bookingDate)) {
            return false;
        }

        $now = $this->nowInJakarta();
        $currentHour = (int) $now->format('H');
        $currentMinute = (int) $now->format('i');

        return $currentHour > self::CLOSING_HOUR
            || ($currentHour === self::CLOSING_HOUR && $currentMinute > 0);
    }

    /**
     * Current Jakarta time from server.
     */
    private function nowInJakarta(): Carbon
    {
        return Carbon::now(self::BOOKING_TIMEZONE);
    }

    /**
     * Get latest active booking for currently authenticated customer.
     */
    private function getActiveBookingForAuthenticatedUser(): ?Booking
    {
        $userId = Auth::id();

        if (!$userId) {
            return null;
        }

        $this->expireExpiredWaitingPaymentBookings($userId);

        return Booking::query()
            ->with(['service', 'barber.user', 'barber.branch'])
            ->activeForUser($userId)
            ->orderByDesc('booking_date')
            ->orderByDesc('booking_time')
            ->first();
    }

    /**
     * Check whether authenticated customer still has active booking.
     */
    private function hasActiveBookingForAuthenticatedUser(): bool
    {
        $userId = Auth::id();

        if (!$userId) {
            return false;
        }

        $this->expireExpiredWaitingPaymentBookings($userId);

        return Booking::query()
            ->activeForUser($userId)
            ->exists();
    }

    /**
     * Auto-cancel expired waiting payment booking for current user.
     */
    private function expireExpiredWaitingPaymentBookingsForAuthenticatedUser(): int
    {
        $userId = Auth::id();

        if (!$userId) {
            return 0;
        }

        return $this->expireExpiredWaitingPaymentBookings($userId);
    }

    /**
     * Auto-cancel expired waiting payment bookings.
     */
    private function expireExpiredWaitingPaymentBookings(?int $userId = null): int
    {
        $hasPaymentExpiredAt = Schema::hasColumn('bookings', 'payment_expired_at');
        $hasPaymentDeadline = Schema::hasColumn('bookings', 'payment_deadline');

        if (!$hasPaymentExpiredAt && !$hasPaymentDeadline) {
            return 0;
        }

        $query = Booking::query()
            ->where('status', Booking::STATUS_WAITING_PAYMENT)
            ->where(function ($builder) {
                $builder->whereNull('payment_status')
                    ->orWhere('payment_status', Booking::PAYMENT_STATUS_UNPAID);
            })
            ->where(function ($builder) use ($hasPaymentExpiredAt, $hasPaymentDeadline) {
                if ($hasPaymentExpiredAt) {
                    $builder->where(function ($expiredQuery) {
                        $expiredQuery->whereNotNull('payment_expired_at')
                            ->where('payment_expired_at', '<=', now());
                    });
                }

                if ($hasPaymentDeadline) {
                    $method = $hasPaymentExpiredAt ? 'orWhere' : 'where';

                    $builder->{$method}(function ($deadlineQuery) {
                        $deadlineQuery->whereNotNull('payment_deadline')
                            ->where('payment_deadline', '<=', now());
                    });
                }
            });

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->update([
            'status' => Booking::STATUS_CANCELLED,
            'payment_status' => Booking::PAYMENT_STATUS_EXPIRED,
            'updated_at' => now(),
        ]);
    }

    /**
     * Check whether current schema and config support waiting-payment Midtrans flow.
     */
    private function canCreateWaitingPaymentBooking(): bool
    {
        return $this->hasMidtransConfiguration() && empty($this->getMissingBookingColumnsForPayment());
    }

    /**
     * Build booking payload while remaining compatible with older schemas.
     */
    private function buildBookingCreatePayload(array $validated, bool $withMidtrans): array
    {
        $payload = [
            'user_id' => Auth::id(),
            'barber_id' => $validated['barber_id'],
            'service_id' => $validated['service_id'],
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'],
            'status' => $withMidtrans ? Booking::STATUS_WAITING_PAYMENT : Booking::STATUS_PENDING,
        ];

        if ($this->hasBookingColumns(['total_people'])) {
            $payload['total_people'] = $validated['total_people'];
        }

        if ($this->hasBookingColumns(['payment_status'])) {
            $payload['payment_status'] = $withMidtrans
                ? Booking::PAYMENT_STATUS_UNPAID
                : Booking::PAYMENT_STATUS_UNPAID;
        }

        if ($withMidtrans && $this->hasBookingColumns(['dp_amount'])) {
            $payload['dp_amount'] = self::DP_AMOUNT;
        }

        return $payload;
    }

    /**
     * Build booking payment update payload for columns that exist in the database.
     */
    private function buildBookingPaymentUpdatePayload(array $transaction): array
    {
        $payload = [];

        if ($this->hasBookingColumns(['midtrans_order_id'])) {
            $payload['midtrans_order_id'] = $transaction['order_id'];
        }

        if ($this->hasBookingColumns(['midtrans_snap_token'])) {
            $payload['midtrans_snap_token'] = $transaction['snap_token'];
        }

        if ($this->hasBookingColumns(['payment_expired_at'])) {
            $payload['payment_expired_at'] = $transaction['payment_expired_at'];
        }

        if ($this->hasBookingColumns(['payment_deadline'])) {
            $payload['payment_deadline'] = $transaction['payment_expired_at'];
        }

        return $payload;
    }

    /**
     * Determine whether bookings table contains all provided columns.
     */
    private function hasBookingColumns(array $columns): bool
    {
        foreach ($columns as $column) {
            if (!Schema::hasColumn('bookings', $column)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return payment-related booking columns that are still missing.
     *
     * @return array<int, string>
     */
    private function getMissingBookingColumnsForPayment(): array
    {
        $requiredColumns = [
            'payment_status',
            'midtrans_order_id',
            'midtrans_snap_token',
            'payment_expired_at',
            'payment_deadline',
            'dp_amount',
            'total_people',
        ];

        return array_values(array_filter($requiredColumns, fn ($column) => !Schema::hasColumn('bookings', $column)));
    }

    /**
     * Check whether Midtrans credentials are available.
     */
    private function hasMidtransConfiguration(): bool
    {
        return filled(config('services.midtrans.server_key')) && filled(config('services.midtrans.client_key'));
    }

    /**
     * Detect duplicate-slot database exceptions and convert them into friendly messages.
     */
    private function isDuplicateBookingSlotException(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'duplicate entry')
            || str_contains($message, 'unique constraint')
            || str_contains($message, 'bookings_barber_id_booking_date_booking_time_unique');
    }
}
