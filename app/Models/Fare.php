<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fare extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fares';

    protected $fillable = [
        'route_id',
        'seat_plan_id',
        'coach_type',
        'seat_type',
        'from_date',
        'to_date',
        'amount',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'coach_type' => 'integer',
        'status' => 'integer',
        'from_date' => 'datetime',
        'to_date' => 'datetime'
    ];

    // Constants for coach types (matching TripInstance)
    public const COACH_TYPE_AC = 1;
    public const COACH_TYPE_NON_AC = 2;

    // Constants for seat types
    public const SEAT_TYPE_SUITE = 'Suite Class';
    public const SEAT_TYPE_BUSINESS = 'Business Class';
    public const SEAT_TYPE_SLEEPER = 'Sleeper';
    public const SEAT_TYPE_ECONOMY = 'Economy';

    // Constants for status
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    /**
     * Get the route that belongs to this fare
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the seat plan that belongs to this fare
     */
    public function seatPlan(): BelongsTo
    {
        return $this->belongsTo(SeatPlan::class);
    }

    /**
     * Get the user who created this fare
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this fare
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all trip instances that use this fare configuration
     */
    public function tripInstances(): HasMany
    {
        return $this->hasMany(TripInstance::class, 'route_id', 'route_id')
                    ->where('seat_plan_id', $this->seat_plan_id)
                    ->where('coach_type', $this->coach_type);
    }

    /**
     * Check if coach type is AC
     */
    public function isAC(): bool
    {
        return $this->coach_type === self::COACH_TYPE_AC;
    }

    /**
     * Check if coach type is Non-AC
     */
    public function isNonAC(): bool
    {
        return $this->coach_type === self::COACH_TYPE_NON_AC;
    }

    /**
     * Check if seat type is Suite Class
     */
    public function isSuiteClass(): bool
    {
        return $this->seat_type === self::SEAT_TYPE_SUITE;
    }

    /**
     * Check if seat type is Business Class
     */
    public function isBusinessClass(): bool
    {
        return $this->seat_type === self::SEAT_TYPE_BUSINESS;
    }

    /**
     * Check if seat type is Sleeper
     */
    public function isSleeper(): bool
    {
        return $this->seat_type === self::SEAT_TYPE_SLEEPER;
    }

    /**
     * Check if seat type is Economy
     */
    public function isEconomy(): bool
    {
        return $this->seat_type === self::SEAT_TYPE_ECONOMY;
    }

    /**
     * Check if fare is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if fare is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
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
     * Get seat type display name
     */
    public function getSeatTypeNameAttribute(): string
    {
        return $this->seat_type ?? 'Unknown';
    }

    /**
     * Get status name
     */
    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            default => 'Unknown'
        };
    }

    /**
     * Scope for active fares
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for inactive fares
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
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

    /**
     * Scope for Suite Class seats
     */
    public function scopeSuiteClass($query)
    {
        return $query->where('seat_type', self::SEAT_TYPE_SUITE);
    }

    /**
     * Scope for Business Class seats
     */
    public function scopeBusinessClass($query)
    {
        return $query->where('seat_type', self::SEAT_TYPE_BUSINESS);
    }

    /**
     * Scope for Sleeper seats
     */
    public function scopeSleeper($query)
    {
        return $query->where('seat_type', self::SEAT_TYPE_SLEEPER);
    }

    /**
     * Scope for Economy seats
     */
    public function scopeEconomy($query)
    {
        return $query->where('seat_type', self::SEAT_TYPE_ECONOMY);
    }

    /**
     * Scope for specific route
     */
    public function scopeByRoute($query, $routeId)
    {
        return $query->where('route_id', $routeId);
    }

    /**
     * Scope for specific seat plan
     */
    public function scopeBySeatPlan($query, $seatPlanId)
    {
        return $query->where('seat_plan_id', $seatPlanId);
    }

    /**
     * Scope for specific seat type
     */
    public function scopeBySeatType($query, $seatType)
    {
        return $query->where('seat_type', $seatType);
    }

    /**
     * Scope for date range filtering
     */
    public function scopeValidForDate($query, $date)
    {
        return $query->where(function ($q) use ($date) {
            $q->where(function ($subQuery) use ($date) {
                // If from_date is null or date is >= from_date
                $subQuery->whereNull('from_date')
                         ->orWhere('from_date', '<=', $date);
            })
            ->where(function ($subQuery) use ($date) {
                // If to_date is null or date is <= to_date
                $subQuery->whereNull('to_date')
                         ->orWhere('to_date', '>=', $date);
            });
        });
    }

    /**
     * Find fare for specific configuration
     */
    public static function findForConfiguration($routeId, $seatPlanId, $coachType, $seatType = null, $date = null)
    {
        $query = static::active()
                      ->where('route_id', $routeId)
                      ->where('seat_plan_id', $seatPlanId)
                      ->where('coach_type', $coachType);

        if ($seatType) {
            $query->where('seat_type', $seatType);
        }

        if ($date) {
            $query->validForDate($date);
        }

        return $query->first();
    }

    /**
     * Get all available seat types
     */
    public static function getSeatTypes(): array
    {
        return [
            self::SEAT_TYPE_SUITE,
            self::SEAT_TYPE_BUSINESS,
            self::SEAT_TYPE_SLEEPER,
            self::SEAT_TYPE_ECONOMY,
        ];
    }
}
