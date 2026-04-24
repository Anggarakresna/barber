<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PaymentController extends Controller
{
    private const PAYMENT_WINDOW_MINUTES = 30;

    /**
     * Display bookings for the currently logged-in customer.
     */
    public function myBooking()
    {
        $expiredBookingsCount = $this->expireExpiredWaitingPaymentBookingsForAuthenticatedUser();
        $paymentExpiredMessage = $expiredBookingsCount > 0
            ? 'Booking dibatalkan karena pembayaran melewati batas waktu.'
            : null;

        $waitingPaymentBooking = null;

        if ($this->hasBookingColumns(['payment_status'])) {
            $waitingPaymentBooking = Booking::with(['service', 'barber.user'])
                ->where('user_id', Auth::id())
                ->where('status', Booking::STATUS_WAITING_PAYMENT)
                ->where(function ($query) {
                    $query->whereNull('payment_status')
                        ->orWhere('payment_status', Booking::PAYMENT_STATUS_UNPAID);
                })
                ->latest('id')
                ->first();
        }

        if (
            $waitingPaymentBooking
            && (
                $waitingPaymentBooking->status !== Booking::STATUS_WAITING_PAYMENT
                || $waitingPaymentBooking->payment_status !== Booking::PAYMENT_STATUS_UNPAID
            )
        ) {
            $waitingPaymentBooking = null;
        }

        if ($waitingPaymentBooking && empty($waitingPaymentBooking->midtrans_snap_token)) {
            $this->ensureSnapTransactionExists($waitingPaymentBooking);
            $waitingPaymentBooking->refresh();
        }

        $bookings = Booking::with(['service', 'barber.user'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(5);

        return view('my-booking', compact('bookings', 'waitingPaymentBooking', 'paymentExpiredMessage'));
    }

    /**
     * Sync Midtrans payment status for a booking.
     */
    public function syncPaymentStatus(Booking $booking)
    {
        $this->authorizeBookingOwner($booking);

        return $this->syncBookingPaymentAndRedirect($booking, false);
    }

    /**
     * Sync Midtrans payment status for a booking and return JSON response.
     */
    public function syncPaymentStatusJson(Booking $booking)
    {
        $this->authorizeBookingOwner($booking);

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
            $this->ensureSnapTransactionExists($booking);
            $booking->refresh();
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
     * Complete payment flow from Snap JavaScript callback and update booking immediately.
     */
    public function completePaymentFromSnap(Request $request, Booking $booking)
    {
        $this->authorizeBookingOwner($booking);

        $validated = $request->validate([
            'order_id' => ['nullable', 'string'],
            'transaction_status' => ['nullable', 'string'],
            'fraud_status' => ['nullable', 'string'],
            'status_code' => ['nullable'],
            'payment_type' => ['nullable', 'string'],
            'gross_amount' => ['nullable'],
        ]);

        $this->expireExpiredWaitingPaymentBookingsForAuthenticatedUser();
        $booking->refresh();

        if (
            !empty($validated['order_id'])
            && !empty($booking->midtrans_order_id)
            && $validated['order_id'] !== $booking->midtrans_order_id
        ) {
            return response()->json([
                'state' => 'error',
                'message' => 'Order Midtrans tidak sesuai dengan booking ini.',
            ], 422);
        }

        if ($booking->payment_status === Booking::PAYMENT_STATUS_PAID || $booking->status === Booking::STATUS_CONFIRMED) {
            return response()->json([
                'state' => 'paid',
                'message' => 'Pembayaran berhasil. Booking Anda sudah dikonfirmasi.',
                'redirect_url' => route('my-booking'),
            ]);
        }

        if ($booking->payment_status === Booking::PAYMENT_STATUS_EXPIRED || $booking->status === Booking::STATUS_CANCELLED) {
            return response()->json([
                'state' => 'cancelled',
                'message' => 'Booking dibatalkan karena pembayaran tidak berhasil atau melewati batas waktu.',
                'redirect_url' => route('my-booking'),
            ]);
        }

        if (!empty($validated['transaction_status'])) {
            $this->applyMidtransStatusToBooking($booking, $validated);
            $booking->refresh();
        } elseif (!empty($booking->midtrans_order_id)) {
            try {
                $response = app(MidtransService::class)->getTransactionStatus($booking->midtrans_order_id);
                $this->applyMidtransStatusToBooking($booking, $response);
                $booking->refresh();
            } catch (\Throwable $exception) {
                Log::warning('Gagal menyelesaikan pembayaran dari callback Snap.', [
                    'booking_id' => $booking->id,
                    'order_id' => $booking->midtrans_order_id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        $state = 'pending';
        $message = 'Status pembayaran masih menunggu.';

        if ($booking->payment_status === Booking::PAYMENT_STATUS_PAID || $booking->status === Booking::STATUS_CONFIRMED) {
            $state = 'paid';
            $message = 'Pembayaran berhasil. Booking Anda sudah dikonfirmasi.';
        } elseif ($booking->payment_status === Booking::PAYMENT_STATUS_EXPIRED || $booking->status === Booking::STATUS_CANCELLED) {
            $state = 'cancelled';
            $message = 'Booking dibatalkan karena pembayaran tidak berhasil atau melewati batas waktu.';
        }

        return response()->json([
            'state' => $state,
            'message' => $message,
            'redirect_url' => route('my-booking'),
        ]);
    }

    /**
     * Handle legacy Midtrans finish URL that points to old confirm-payment path.
     */
    public function legacyConfirmPaymentReturn(Booking $booking)
    {
        $this->authorizeBookingOwner($booking);

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
     * Ensure booking belongs to authenticated customer.
     */
    private function authorizeBookingOwner(Booking $booking): void
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }
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
            $this->ensureSnapTransactionExists($booking);
            $booking->refresh();
        }

        if (!$booking->midtrans_order_id) {
            return redirect()->route('my-booking')
                ->with('error', 'Transaksi pembayaran belum berhasil disiapkan. Silakan refresh halaman dan coba lagi.');
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
            $paymentStatus = $transactionStatus === 'expire'
                ? Booking::PAYMENT_STATUS_EXPIRED
                : Booking::PAYMENT_STATUS_CANCELLED;

            $this->markBookingAsCancelled($booking, $paymentStatus);
        }
    }

    /**
     * Mark waiting payment booking as expired/cancelled.
     */
    private function markBookingAsExpired(Booking $booking): void
    {
        $this->markBookingAsCancelled($booking, Booking::PAYMENT_STATUS_EXPIRED);
    }

    /**
     * Mark waiting payment booking as cancelled and store payment status detail.
     */
    private function markBookingAsCancelled(Booking $booking, string $paymentStatus): void
    {
        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'payment_status' => $paymentStatus,
        ]);
    }

    /**
     * Create Snap transaction for waiting-payment booking when token is missing.
     */
    private function ensureSnapTransactionExists(Booking $booking): void
    {
        if (
            !$this->canCreateWaitingPaymentBooking()
            || $booking->status !== Booking::STATUS_WAITING_PAYMENT
            || $booking->payment_status !== Booking::PAYMENT_STATUS_UNPAID
        ) {
            return;
        }

        try {
            $transaction = app(MidtransService::class)->createDpTransaction($booking, self::PAYMENT_WINDOW_MINUTES);

            $booking->update($this->buildBookingPaymentUpdatePayload($transaction));
        } catch (\Throwable $exception) {
            Log::warning('Gagal membuat ulang transaksi Midtrans di My Booking.', [
                'booking_id' => $booking->id,
                'exception' => $exception->getMessage(),
            ]);
        }
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
        return $this->hasMidtransConfiguration() && $this->hasBookingColumns([
            'payment_status',
            'midtrans_order_id',
            'midtrans_snap_token',
            'payment_expired_at',
            'payment_deadline',
            'dp_amount',
        ]);
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
     * Check whether Midtrans credentials are available.
     */
    private function hasMidtransConfiguration(): bool
    {
        return filled(config('midtrans.server_key', config('services.midtrans.server_key')))
            && filled(config('midtrans.client_key', config('services.midtrans.client_key')));
    }

    /**
     * Build payment update payload for columns that exist in the database.
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
}
