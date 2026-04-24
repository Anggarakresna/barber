<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\Transaction;
use RuntimeException;

class MidtransService
{
    public function __construct()
    {
        $this->configure();
    }

    /**
     * Configure Midtrans SDK from config/services.php.
     */
    private function configure(): void
    {
        MidtransConfig::$serverKey = (string) config('services.midtrans.server_key');
        MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);
        MidtransConfig::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        MidtransConfig::$is3ds = (bool) config('services.midtrans.is_3ds', true);
    }

    private function buildCallbackUrl(string $path): string
    {
        $publicUrl = trim((string) config('services.midtrans.public_url'));

        if ($publicUrl !== '') {
            return rtrim($publicUrl, '/') . '/' . ltrim($path, '/');
        }

        return url($path);
    }

    /**
     * Create a Snap DP transaction for a booking.
     */
    public function createDpTransaction(Booking $booking, int $paymentWindowMinutes = 30): array
    {
        if (empty(config('services.midtrans.server_key')) || empty(config('services.midtrans.client_key'))) {
            throw new RuntimeException('MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY harus diisi.');
        }

        $booking->loadMissing(['user', 'service']);
        $phone = $booking->user?->getAttribute('phone');

        $orderId = $this->generateOrderId($booking);
        $startTime = Carbon::now('Asia/Jakarta');

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) ($booking->dp_amount ?: 20000),
            ],
            'customer_details' => [
                'first_name' => $booking->user->name,
                'email' => $booking->user->email,
                'phone' => is_string($phone) && $phone !== '' ? $phone : null,
            ],
            'item_details' => [[
                'id' => 'DP-BOOKING-' . $booking->id,
                'price' => (int) ($booking->dp_amount ?: 20000),
                'quantity' => 1,
                'name' => 'DP Booking #' . $booking->id,
            ]],
            'expiry' => [
                'start_time' => $startTime->format('Y-m-d H:i:s O'),
                'unit' => 'minute',
                'duration' => $paymentWindowMinutes,
            ],
            'callbacks' => [
                'finish' => $this->buildCallbackUrl('/booking/payment/finish'),
                'unfinish' => $this->buildCallbackUrl('/booking/payment/unfinish'),
                'error' => $this->buildCallbackUrl('/booking/payment/error'),
            ],
        ];

        try {
            $transaction = Snap::createTransaction($params);
        } catch (\Throwable $exception) {
            Log::error('Gagal membuat transaksi Snap Midtrans.', [
                'booking_id' => $booking->id,
                'exception' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Gagal membuat transaksi pembayaran Midtrans.', previous: $exception);
        }

        $snapToken = $transaction->token ?? null;

        if (empty($snapToken)) {
            throw new RuntimeException('Gagal mendapatkan Snap token dari Midtrans.');
        }

        return [
            'order_id' => $orderId,
            'snap_token' => $snapToken,
            'payment_expired_at' => $startTime->copy()->addMinutes($paymentWindowMinutes),
        ];
    }

    /**
     * Retrieve Midtrans transaction status by order ID.
     */
    public function getTransactionStatus(string $orderId): array
    {
        $response = Transaction::status($orderId);

        return json_decode(json_encode($response), true) ?? [];
    }

    /**
     * Validate callback signature from Midtrans webhook.
     */
    public function isValidSignature(array $payload): bool
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');
        $serverKey = (string) config('services.midtrans.server_key');

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signatureKey === '' || $serverKey === '') {
            return false;
        }

        $generatedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($generatedSignature, $signatureKey);
    }

    /**
     * Generate unique order ID for Midtrans.
     */
    private function generateOrderId(Booking $booking): string
    {
        return 'BOOKING-' . $booking->id . '-' . Str::upper(Str::random(8));
    }
}
