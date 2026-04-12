<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    /**
     * Explicit table name because "Feedback" is treated as uncountable by pluralizer.
     *
     * @var string
     */
    protected $table = 'feedbacks';

    /**
     * Since this table only has created_at, disable updated_at handling.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'message',
        'rating',
        'is_read',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
        'is_read' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that submitted this feedback (if logged in).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
