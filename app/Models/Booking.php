<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_WAITING_PAYMENT = 'waiting_payment';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_EXPIRED = 'expired';
    public const PAYMENT_STATUS_CANCELLED = 'cancelled';

    public const ACTIVE_STATUSES = [
        self::STATUS_WAITING_PAYMENT,
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_PROCESSING,
    ];

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
        'total_people',
        'payment_status',
        'midtrans_order_id',
        'midtrans_snap_token',
        'payment_deadline',
        'payment_expired_at',
        'dp_amount',
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
        'total_people' => 'integer',
        'payment_deadline' => 'datetime',
        'payment_expired_at' => 'datetime',
        'dp_amount' => 'integer',
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
     * Scope: Get bookings that still occupy a slot.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    /**
     * Scope: Get active bookings for a specific user.
     */
    public function scopeActiveForUser($query, int $userId)
    {
        return $query->where('user_id', $userId)->active();
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
