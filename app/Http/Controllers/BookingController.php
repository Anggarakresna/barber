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
        $this->expireExpiredWaitingPaymentBookingsForAuthenticatedUser();

        if ($this->hasActiveBookingForAuthenticatedUser()) {
            $activeBooking = $this->getActiveBookingForAuthenticatedUser();
            $errorMessage = $activeBooking && $activeBooking->status === Booking::STATUS_WAITING_PAYMENT
                ? 'Anda masih memiliki booking yang menunggu pembayaran DP. Silakan selesaikan pembayaran di halaman My Booking.'
                : 'Anda masih memiliki booking yang sedang berjalan. Silakan tunggu hingga barber menyelesaikan booking Anda.';

            return redirect()->route('booking')
                ->with('error', $errorMessage);
        }

        $validated = $request->validate([
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
        ]);

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

        try {
            DB::transaction(function () use ($validated) {
                $booking = Booking::create([
                    'user_id' => Auth::id(),
                    'barber_id' => $validated['barber_id'],
                    'service_id' => $validated['service_id'],
                    'booking_date' => $validated['booking_date'],
                    'booking_time' => $validated['booking_time'],
                    'total_people' => $validated['total_people'],
                    'status' => Booking::STATUS_WAITING_PAYMENT,
                    'payment_status' => Booking::PAYMENT_STATUS_UNPAID,
                    'dp_amount' => self::DP_AMOUNT,
                ]);

                $midtransService = app(MidtransService::class);
                $transaction = $midtransService->createDpTransaction($booking, self::PAYMENT_WINDOW_MINUTES);

                $booking->update([
                    'midtrans_order_id' => $transaction['order_id'],
                    'midtrans_snap_token' => $transaction['snap_token'],
                    'payment_expired_at' => $transaction['payment_expired_at'],
                    'payment_deadline' => $transaction['payment_expired_at'],
                ]);
            });
        } catch (\Throwable $exception) {
            Log::error('Gagal membuat transaksi Midtrans saat booking.', [
                'user_id' => Auth::id(),
                'exception' => $exception->getMessage(),
            ]);

            return back()
                ->withErrors(['booking' => 'Booking gagal diproses karena transaksi pembayaran tidak dapat dibuat. Coba lagi dalam beberapa saat.'])
                ->with('error', 'Booking gagal diproses karena transaksi pembayaran tidak dapat dibuat. Coba lagi dalam beberapa saat.')
                ->withInput();
        }

        return redirect()->route('my-booking')->with('success', 'Booking berhasil dibuat. Silakan selesaikan pembayaran DP dalam 30 menit.');
    }

    /**
     * Sync Midtrans payment status for a booking.
     */
    public function syncPaymentStatus(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        return $this->syncBookingPaymentAndRedirect($booking, false);
    }

    /**
     * Sync Midtrans payment status for a booking and return JSON response.
     */
    public function syncPaymentStatusJson(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $this->expireExpiredWaitingPaymentBookingsForAuthenticatedUser();
        $booking->refresh();

        if ($booking->payment_status === Booking::PAYMENT_STATUS_PAID || $booking->status === Booking::STATUS_CONFIRMED) {
            return response()->json([
                'state' => 'paid',
                'message' => 'Pembayaran berhasil. Booking Anda sudah dikonfirmasi.',
            ]);
        }

        if ($booking->payment_status === Booking::PAYMENT_STATUS_EXPIRED || $booking->status === Booking::STATUS_CANCELLED) {
            return response()->json([
                'state' => 'cancelled',
                'message' => 'Booking dibatalkan karena pembayaran tidak berhasil atau melewati batas waktu.',
            ]);
        }

        if ($booking->status !== Booking::STATUS_WAITING_PAYMENT) {
            return response()->json([
                'state' => 'updated',
                'message' => 'Status booking sudah diperbarui.',
            ]);
        }

        if ($booking->payment_expired_at && $booking->payment_expired_at->isPast()) {
            $this->markBookingAsExpired($booking);

            return response()->json([
                'state' => 'cancelled',
                'message' => 'Booking dibatalkan karena pembayaran melewati batas waktu.',
            ]);
        }

        if (!$booking->midtrans_order_id) {
            return response()->json([
                'state' => 'error',
                'message' => 'Order Midtrans tidak ditemukan pada booking ini.',
            ], 422);
        }

        try {
            $response = app(MidtransService::class)->getTransactionStatus($booking->midtrans_order_id);
            $this->applyMidtransStatusToBooking($booking, $response);
            $booking->refresh();
        } catch (\Throwable $exception) {
            $isTransactionNotFound = str_contains(strtolower($exception->getMessage()), "doesn't exist");

            if ($isTransactionNotFound) {
                return response()->json([
                    'state' => 'pending',
                    'message' => 'Pembayaran sedang diproses Midtrans. Status akan diperbarui otomatis.',
                ]);
            }

            Log::warning('Gagal sinkronisasi status Midtrans (JSON).', [
                'booking_id' => $booking->id,
                'order_id' => $booking->midtrans_order_id,
                'exception' => $exception->getMessage(),
            ]);

            return response()->json([
                'state' => 'error',
                'message' => 'Gagal mengecek status pembayaran Midtrans. Coba lagi dalam beberapa saat.',
            ], 500);
        }

        if ($booking->payment_status === Booking::PAYMENT_STATUS_PAID) {
            return response()->json([
                'state' => 'paid',
                'message' => 'Pembayaran berhasil. Booking Anda sudah dikonfirmasi.',
            ]);
        }

        if ($booking->payment_status === Booking::PAYMENT_STATUS_EXPIRED || $booking->status === Booking::STATUS_CANCELLED) {
            return response()->json([
                'state' => 'cancelled',
                'message' => 'Booking dibatalkan karena pembayaran tidak berhasil atau melewati batas waktu.',
            ]);
        }

        return response()->json([
            'state' => 'pending',
            'message' => 'Status pembayaran masih menunggu.',
        ]);
    }

    /**
     * Handle legacy Midtrans finish URL that points to old confirm-payment path.
     */
    public function legacyConfirmPaymentReturn(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        return $this->syncBookingPaymentAndRedirect($booking, true);
    }

    /**
     * Handle Midtrans finish/unfinish/error browser return redirects.
     */
    public function midtransReturn(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->route('login');
        }

        $orderId = (string) $request->query('order_id', '');
        $booking = null;

        if ($orderId !== '') {
            $booking = Booking::query()
                ->where('user_id', $userId)
                ->where('midtrans_order_id', $orderId)
                ->latest('id')
                ->first();
        }

        if (!$booking) {
            $booking = Booking::query()
                ->where('user_id', $userId)
                ->where('status', Booking::STATUS_WAITING_PAYMENT)
                ->where(function ($query) {
                    $query->whereNull('payment_status')
                        ->orWhere('payment_status', Booking::PAYMENT_STATUS_UNPAID);
                })
                ->latest('id')
                ->first();
        }

        if (!$booking) {
            return redirect()->route('my-booking')
                ->with('error', 'Booking pembayaran tidak ditemukan atau sudah selesai diproses.');
        }

        return $this->syncBookingPaymentAndRedirect($booking, true);
    }

    /**
     * Sync booking payment status with Midtrans and return customer-friendly redirect.
     */
    private function syncBookingPaymentAndRedirect(Booking $booking, bool $isReturnFlow = false)
    {
        $this->expireExpiredWaitingPaymentBookingsForAuthenticatedUser();
        $booking->refresh();

        if ($booking->payment_status === Booking::PAYMENT_STATUS_PAID || $booking->status === Booking::STATUS_CONFIRMED) {
            return redirect()->route('my-booking')
                ->with('success', 'Pembayaran berhasil. Booking Anda sudah dikonfirmasi.');
        }

        if ($booking->payment_status === Booking::PAYMENT_STATUS_EXPIRED || $booking->status === Booking::STATUS_CANCELLED) {
            return redirect()->route('my-booking')
                ->with('error', 'Booking dibatalkan karena pembayaran tidak berhasil atau melewati batas waktu.');
        }

        if ($booking->status !== Booking::STATUS_WAITING_PAYMENT) {
            return redirect()->route('my-booking')
                ->with('success', 'Status booking sudah diperbarui.');
        }

        if ($booking->payment_expired_at && $booking->payment_expired_at->isPast()) {
            $this->markBookingAsExpired($booking);

            return redirect()->route('my-booking')
                ->with('error', 'Booking dibatalkan karena pembayaran melewati batas waktu.');
        }

        if (!$booking->midtrans_order_id) {
            return redirect()->route('my-booking')
                ->with('error', 'Order Midtrans tidak ditemukan pada booking ini.');
        }

        try {
            $response = app(MidtransService::class)->getTransactionStatus($booking->midtrans_order_id);
            $this->applyMidtransStatusToBooking($booking, $response);
            $booking->refresh();
        } catch (\Throwable $exception) {
            Log::warning('Gagal sinkronisasi status Midtrans.', [
                'booking_id' => $booking->id,
                'order_id' => $booking->midtrans_order_id,
                'exception' => $exception->getMessage(),
            ]);

            $isTransactionNotFound = str_contains(strtolower($exception->getMessage()), "doesn't exist");

            if ($isTransactionNotFound && $isReturnFlow) {
                return redirect()->route('my-booking')
                    ->with('success', 'Pembayaran sedang diproses oleh Midtrans. Jika status belum berubah, klik Cek Status beberapa saat lagi.');
            }

            $errorMessage = $isTransactionNotFound
                ? 'Transaksi Midtrans belum tersedia. Jika baru selesai bayar, tunggu beberapa detik lalu klik Cek Status lagi.'
                : 'Gagal mengecek status pembayaran Midtrans. Coba lagi dalam beberapa saat.';

            return redirect()->route('my-booking')
                ->with('error', $errorMessage);
        }

        if ($booking->payment_status === Booking::PAYMENT_STATUS_PAID) {
            return redirect()->route('my-booking')
                ->with('success', 'Pembayaran berhasil. Booking Anda sudah dikonfirmasi.');
        }

        if ($booking->payment_status === Booking::PAYMENT_STATUS_EXPIRED || $booking->status === Booking::STATUS_CANCELLED) {
            return redirect()->route('my-booking')
                ->with('error', 'Booking dibatalkan karena pembayaran tidak berhasil atau melewati batas waktu.');
        }

        return redirect()->route('my-booking')
            ->with('success', 'Status pembayaran masih menunggu. Silakan selesaikan pembayaran di Midtrans.');
    }

    /**
     * Midtrans webhook callback for payment status update.
     */
    public function midtransWebhook(Request $request)
    {
        $payload = $request->all();

        if (!app(MidtransService::class)->isValidSignature($payload)) {
            Log::warning('Webhook Midtrans ditolak karena signature tidak valid.', [
                'payload' => $payload,
            ]);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = (string) ($payload['order_id'] ?? '');

        if ($orderId === '') {
            return response()->json(['message' => 'Order ID tidak ditemukan.'], 422);
        }

        $booking = Booking::query()
            ->where('midtrans_order_id', $orderId)
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking tidak ditemukan.'], 404);
        }

        $this->applyMidtransStatusToBooking($booking, $payload);

        return response()->json(['message' => 'OK']);
    }

    /**
     * Map Midtrans transaction status to booking status.
     */
    private function applyMidtransStatusToBooking(Booking $booking, array $payload): void
    {
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        if (in_array($transactionStatus, ['capture', 'settlement'], true)) {
            if ($transactionStatus === 'capture' && $fraudStatus === 'challenge') {
                $booking->update([
                    'status' => Booking::STATUS_WAITING_PAYMENT,
                    'payment_status' => Booking::PAYMENT_STATUS_UNPAID,
                ]);

                return;
            }

            $booking->update([
                'status' => Booking::STATUS_CONFIRMED,
                'payment_status' => Booking::PAYMENT_STATUS_PAID,
            ]);

            return;
        }

        if (in_array($transactionStatus, ['pending'], true)) {
            $booking->update([
                'status' => Booking::STATUS_WAITING_PAYMENT,
                'payment_status' => Booking::PAYMENT_STATUS_UNPAID,
            ]);

            return;
        }

        if (in_array($transactionStatus, ['cancel', 'deny', 'expire', 'failure'], true)) {
            $this->markBookingAsExpired($booking);
        }
    }

    /**
     * Mark waiting payment booking as expired/cancelled.
     */
    private function markBookingAsExpired(Booking $booking): void
    {
        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'payment_status' => Booking::PAYMENT_STATUS_EXPIRED,
        ]);
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
     * Display bookings for the currently logged-in customer.
     */
    public function myBooking()
    {
        $expiredBookingsCount = $this->expireExpiredWaitingPaymentBookingsForAuthenticatedUser();
        $paymentExpiredMessage = $expiredBookingsCount > 0
            ? 'Booking dibatalkan karena pembayaran melewati batas waktu.'
            : null;

        $waitingPaymentBooking = Booking::with(['service', 'barber.user'])
            ->where('user_id', Auth::id())
            ->where('status', Booking::STATUS_WAITING_PAYMENT)
            ->where(function ($query) {
                $query->whereNull('payment_status')
                    ->orWhere('payment_status', Booking::PAYMENT_STATUS_UNPAID);
            })
            ->orderByDesc('created_at')
            ->first();

        if ($waitingPaymentBooking && empty($waitingPaymentBooking->midtrans_snap_token)) {
            try {
                $transaction = app(MidtransService::class)->createDpTransaction($waitingPaymentBooking, self::PAYMENT_WINDOW_MINUTES);

                $waitingPaymentBooking->update([
                    'midtrans_order_id' => $transaction['order_id'],
                    'midtrans_snap_token' => $transaction['snap_token'],
                    'payment_expired_at' => $transaction['payment_expired_at'],
                    'payment_deadline' => $transaction['payment_expired_at'],
                ]);

                $waitingPaymentBooking->refresh();
            } catch (\Throwable $exception) {
                Log::warning('Gagal membuat ulang transaksi Midtrans di My Booking.', [
                    'booking_id' => $waitingPaymentBooking->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        $bookings = Booking::with(['service', 'barber.user'])
            ->where('user_id', Auth::id())
            ->orderByDesc('booking_date')
            ->orderByDesc('booking_time')
            ->paginate(5);

        return view('my-booking', compact('bookings', 'waitingPaymentBooking', 'paymentExpiredMessage'));
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
}
