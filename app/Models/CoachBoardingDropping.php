<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachBoardingDropping extends Model
{
    use HasFactory;

    protected $fillable = [
        'coach_configuration_id',
        'counter_id',
        'type',
        'time',
        'starting_point_status',
        'ending_point_status',
        'status'
    ];

    protected $casts = [
        'type' => 'integer',
        'time' => 'datetime:H:i',
        'starting_point_status' => 'boolean',
        'ending_point_status' => 'boolean',
        'status' => 'integer'
    ];

    // Constants for types
    public const TYPE_BOARDING = 1;
    public const TYPE_DROPPING = 2;

    // Constants for status
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    /**
     * Get the coach configuration that owns this boarding/dropping point
     */
    public function coachConfiguration(): BelongsTo
    {
        return $this->belongsTo(CoachConfiguration::class);
    }

    /**
     * Get the counter that belongs to this boarding/dropping point
     */
    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    /**
     * Check if this is a boarding point
     */
    public function isBoarding(): bool
    {
        return $this->type === self::TYPE_BOARDING;
    }

    /**
     * Check if this is a dropping point
     */
    public function isDropping(): bool
    {
        return $this->type === self::TYPE_DROPPING;
    }

    /**
     * Check if this point is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if this is a starting point
     */
    public function isStartingPoint(): bool
    {
        return $this->starting_point_status === 1;
    }

    /**
     * Check if this is an ending point
     */
    public function isEndingPoint(): bool
    {
        return $this->ending_point_status === 1;
    }

    /**
     * Get type name
     */
    public function getTypeNameAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_BOARDING => 'Boarding',
            self::TYPE_DROPPING => 'Dropping',
            default => 'Unknown'
        };
    }

    /**
     * Get formatted time
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->time->format('H:i');
    }

    /**
     * Scope for boarding points
     */
    public function scopeBoarding($query)
    {
        return $query->where('type', self::TYPE_BOARDING);
    }

    /**
     * Scope for dropping points
     */
    public function scopeDropping($query)
    {
        return $query->where('type', self::TYPE_DROPPING);
    }

    /**
     * Scope for active points
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for starting points
     */
    public function scopeStartingPoints($query)
    {
        return $query->where('starting_point_status', 1);
    }

    /**
     * Scope for ending points
     */
    public function scopeEndingPoints($query)
    {
        return $query->where('ending_point_status', 1);
    }
}
