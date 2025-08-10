<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'coach_id',
        'schedule_id',
        'bus_id',
        'seat_plan_id',
        'route_id',
        'coach_type',
        'status'
    ];

    protected $casts = [
        'coach_type' => 'integer',
        'status' => 'integer'
    ];

    // Constants for coach types
    public const COACH_TYPE_AC = 1;
    public const COACH_TYPE_NON_AC = 2;

    // Constants for status
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    /**
     * Get the coach that belongs to this configuration
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    /**
     * Get the schedule that belongs to this configuration
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the bus that belongs to this configuration
     */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    /**
     * Get the seat plan that belongs to this configuration
     */
    public function seatPlan(): BelongsTo
    {
        return $this->belongsTo(SeatPlan::class);
    }

    /**
     * Get the route that belongs to this configuration
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get all boarding and dropping points for this configuration
     */
    public function boardingDroppings(): HasMany
    {
        return $this->hasMany(CoachBoardingDropping::class);
    }

    /**
     * Get boarding points only
     */
    public function boardingPoints(): HasMany
    {
        return $this->hasMany(CoachBoardingDropping::class)->where('type', CoachBoardingDropping::TYPE_BOARDING);
    }

    /**
     * Get dropping points only
     */
    public function droppingPoints(): HasMany
    {
        return $this->hasMany(CoachBoardingDropping::class)->where('type', CoachBoardingDropping::TYPE_DROPPING);
    }

    /**
     * Check if coach type is AC
     */
    public function isAC(): bool
    {
        return $this->coach_type === self::COACH_TYPE_AC;
    }

    /**
     * Check if configuration is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get coach type name
     */
    public function getCoachTypeNameAttribute(): string
    {
        return match ($this->coach_type) {
            self::COACH_TYPE_AC => 'AC',
            self::COACH_TYPE_NON_AC => 'Non-AC',
            default => 'Unknown'
        };
    }

    /**
     * Scope for active configurations
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for AC coaches
     */
    public function scopeAC($query)
    {
        return $query->where('coach_type', self::COACH_TYPE_AC);
    }

    /**
     * Scope for Non-AC coaches
     */
    public function scopeNonAC($query)
    {
        return $query->where('coach_type', self::COACH_TYPE_NON_AC);
    }
}
