<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasTransactionId = Schema::hasColumn('bookings', 'midtrans_transaction_id');
        $hasTransactionStatus = Schema::hasColumn('bookings', 'midtrans_transaction_status');
        $hasPaymentType = Schema::hasColumn('bookings', 'midtrans_payment_type');

        Schema::table('bookings', function (Blueprint $table) use ($hasTransactionId, $hasTransactionStatus, $hasPaymentType) {
            if (!$hasTransactionId) {
                $table->string('midtrans_transaction_id')->nullable()->after('midtrans_order_id');
            }

            if (!$hasTransactionStatus) {
                $table->string('midtrans_transaction_status', 40)->nullable()->after('midtrans_transaction_id');
            }

            if (!$hasPaymentType) {
                $table->string('midtrans_payment_type', 40)->nullable()->after('midtrans_transaction_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'midtrans_payment_type')) {
                $table->dropColumn('midtrans_payment_type');
            }

            if (Schema::hasColumn('bookings', 'midtrans_transaction_status')) {
                $table->dropColumn('midtrans_transaction_status');
            }

            if (Schema::hasColumn('bookings', 'midtrans_transaction_id')) {
                $table->dropColumn('midtrans_transaction_id');
            }
        });
    }
};
