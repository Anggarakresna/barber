<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasPaymentStatus = Schema::hasColumn('bookings', 'payment_status');
        $hasDpAmount = Schema::hasColumn('bookings', 'dp_amount');
        $hasMidtransOrderId = Schema::hasColumn('bookings', 'midtrans_order_id');
        $hasMidtransSnapToken = Schema::hasColumn('bookings', 'midtrans_snap_token');
        $hasPaymentExpiredAt = Schema::hasColumn('bookings', 'payment_expired_at');
        $hasPaymentDeadline = Schema::hasColumn('bookings', 'payment_deadline');

        Schema::table('bookings', function (Blueprint $table) use (
            $hasPaymentStatus,
            $hasDpAmount,
            $hasMidtransOrderId,
            $hasMidtransSnapToken,
            $hasPaymentExpiredAt,
            $hasPaymentDeadline
        ) {
            if (!$hasPaymentStatus) {
                $table->string('payment_status', 20)->default('unpaid')->after('status');
            }

            if (!$hasMidtransOrderId) {
                $table->string('midtrans_order_id')->nullable()->unique()->after('payment_status');
            }

            if (!$hasMidtransSnapToken) {
                $table->text('midtrans_snap_token')->nullable()->after('midtrans_order_id');
            }

            if (!$hasPaymentExpiredAt) {
                $table->timestamp('payment_expired_at')
                    ->nullable()
                    ->after($hasPaymentDeadline ? 'payment_deadline' : 'midtrans_snap_token');
            }

            if (!$hasDpAmount) {
                $table->unsignedInteger('dp_amount')->default(20000)->after('payment_expired_at');
            }
        });

        if (Schema::hasColumn('bookings', 'payment_deadline') && Schema::hasColumn('bookings', 'payment_expired_at')) {
            DB::table('bookings')
                ->whereNull('payment_expired_at')
                ->whereNotNull('payment_deadline')
                ->update([
                    'payment_expired_at' => DB::raw('payment_deadline'),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'midtrans_order_id')) {
                try {
                    $table->dropUnique(['midtrans_order_id']);
                } catch (\Throwable $exception) {
                    // Ignore index drop issue on drivers with different naming.
                }
            }

            if (Schema::hasColumn('bookings', 'midtrans_snap_token')) {
                $table->dropColumn('midtrans_snap_token');
            }

            if (Schema::hasColumn('bookings', 'midtrans_order_id')) {
                $table->dropColumn('midtrans_order_id');
            }

            if (Schema::hasColumn('bookings', 'payment_expired_at')) {
                $table->dropColumn('payment_expired_at');
            }
        });
    }
};
