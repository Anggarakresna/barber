<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CancelExpiredWaitingPaymentBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:cancel-expired-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-cancel booking waiting payment that pass payment deadline';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hasPaymentExpiredAt = Schema::hasColumn('bookings', 'payment_expired_at');
        $hasPaymentDeadline = Schema::hasColumn('bookings', 'payment_deadline');

        if (!$hasPaymentExpiredAt && !$hasPaymentDeadline) {
            $this->warn('Kolom payment_expired_at/payment_deadline tidak ditemukan.');
            return self::SUCCESS;
        }

        $expiredCount = Booking::query()
            ->where('status', Booking::STATUS_WAITING_PAYMENT)
            ->where(function ($query) {
                $query->whereNull('payment_status')
                    ->orWhere('payment_status', Booking::PAYMENT_STATUS_UNPAID);
            })
            ->where(function ($query) use ($hasPaymentExpiredAt, $hasPaymentDeadline) {
                if ($hasPaymentExpiredAt) {
                    $query->where(function ($expiredQuery) {
                        $expiredQuery->whereNotNull('payment_expired_at')
                            ->where('payment_expired_at', '<=', now());
                    });
                }

                if ($hasPaymentDeadline) {
                    $method = $hasPaymentExpiredAt ? 'orWhere' : 'where';

                    $query->{$method}(function ($deadlineQuery) {
                        $deadlineQuery->whereNotNull('payment_deadline')
                            ->where('payment_deadline', '<=', now());
                    });
                }
            })
            ->update([
                'status' => Booking::STATUS_CANCELLED,
                'payment_status' => Booking::PAYMENT_STATUS_EXPIRED,
                'updated_at' => now(),
            ]);

        $this->info("{$expiredCount} booking(s) expired and cancelled.");

        return self::SUCCESS;
    }
}
