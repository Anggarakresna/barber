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
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedTinyInteger('total_people')->default(1)->after('booking_time');

            // Keep slot lookup fast while allowing re-booking for cancelled/completed bookings.
            $table->dropUnique('bookings_barber_id_booking_date_booking_time_unique');
            $table->index(['barber_id', 'booking_date', 'booking_time'], 'bookings_barber_date_time_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_barber_date_time_index');
            $table->unique(['barber_id', 'booking_date', 'booking_time'], 'bookings_barber_id_booking_date_booking_time_unique');
            $table->dropColumn('total_people');
        });
    }
};
