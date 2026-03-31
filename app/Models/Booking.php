<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'barber_id',
        'service_id',
        'booking_date',
        'booking_time',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime:H:i',
    ];

    /**
     * Get the customer (user) that made the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the barber assigned to this booking.
     */
    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }

    /**
     * Get the service for this booking.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Scope: Get bookings by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Get bookings by date.
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('booking_date', $date);
    }

    /**
     * Scope: Get future bookings.
     */
    public function scopeFuture($query)
    {
        return $query->where('booking_date', '>=', now()->toDateString());
    }

    /**
     * Scope: Get past bookings.
     */
    public function scopePast($query)
    {
        return $query->where('booking_date', '<', now()->toDateString());
    }

    /**
     * Mark booking as confirmed.
     */
    public function confirm()
    {
        $this->update(['status' => 'confirmed']);
        return $this;
    }

    /**
     * Mark booking as completed.
     */
    public function complete()
    {
        $this->update(['status' => 'completed']);
        return $this;
    }

    /**
     * Mark booking as cancelled.
     */
    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
        return $this;
    }
}
