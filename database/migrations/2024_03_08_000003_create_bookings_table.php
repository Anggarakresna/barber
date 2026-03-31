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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('barber_id')->constrained('barbers')->onDelete('restrict');
            $table->foreignId('service_id')->constrained('services')->onDelete('restrict');
            $table->date('booking_date');
            $table->time('booking_time');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();

            // Indexes untuk performa query
            $table->index('user_id');
            $table->index('barber_id');
            $table->index('service_id');
            $table->index('booking_date');
            $table->index('status');

            // Unique constraint untuk mencegah double booking
            $table->unique(['barber_id', 'booking_date', 'booking_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
