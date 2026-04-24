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
        $hasPaymentStatus = Schema::hasColumn('bookings', 'payment_status');
        $hasPaymentDeadline = Schema::hasColumn('bookings', 'payment_deadline');
        $hasDpAmount = Schema::hasColumn('bookings', 'dp_amount');

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });

        Schema::table('bookings', function (Blueprint $table) use ($hasPaymentStatus, $hasPaymentDeadline, $hasDpAmount) {
            if (!$hasPaymentStatus) {
                $table->string('payment_status', 20)->default('unpaid')->after('status');
            }

            if (!$hasPaymentDeadline) {
                $table->timestamp('payment_deadline')->nullable()->after('payment_status');
            }

            if (!$hasDpAmount) {
                $table->unsignedInteger('dp_amount')->default(20000)->after('payment_deadline');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'dp_amount')) {
                $table->dropColumn('dp_amount');
            }

            if (Schema::hasColumn('bookings', 'payment_deadline')) {
                $table->dropColumn('payment_deadline');
            }

            if (Schema::hasColumn('bookings', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });
    }
};
