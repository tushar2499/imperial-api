<?php

namespace App\Models;

use App\Traits\AutoPartitionManager;
use App\Traits\GlobalUniqueId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TripInstance extends Model
{
    use HasFactory, AutoPartitionManager, GlobalUniqueId;

    protected $table = 'trip_instances';
    public $incrementing = false; // We'll handle ID manually
    protected $keyType = 'int';

    protected $fillable = [
        'coach_id',
        'bus_id',
        'schedule_id',
        'seat_plan_id',
        'route_id',
        'coach_type',
        'driver_id',
        'supervisor_id',
        'trip_date',
        'status',
        'migrated_trip_id',
        'created_by',
        'updated_by',
        'migrated_by'
    ];

    protected $casts = [
        'coach_type' => 'integer',
        'status' => 'integer',
        'trip_date' => 'date'
    ];

    // Constants for coach types
    public const COACH_TYPE_AC = 1;
    public const COACH_TYPE_NON_AC = 2;

    // Constants for status
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;
    public const STATUS_MIGRATED = 2;

    /**
     * Flag to prevent recursive partition switching
     */
    protected static $partitionSwitched = false;

    /**
     * Boot method to automatically set partition based on trip_date
     */
    protected static function boot()
    {
        parent::boot();

        // Only handle partition switching during creation if not already handled
        static::creating(function ($model) {
            if (!static::$partitionSwitched && $model->trip_date) {
                static::$partitionSwitched = true;

                // Get global unique ID first
                if (!$model->id) {
                    $model->id = static::getNextGlobalId();
                }

                $model->usePartition($model->trip_date);
                static::$partitionSwitched = false;
            }
        });
    }

    /**
     * Check if ID exists across all partitions
     *
     * @param int $id
     * @return bool
     */
    public static function idExistsAcrossPartitions(int $id): bool
    {
        $model = new static;
        $partitions = $model->getAllPartitionTables();

        foreach ($partitions as $partition) {
            try {
                $exists = \DB::table($partition)->where('id', $id)->exists();
                if ($exists) {
                    return true;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return false;
    }

    /**
     * Get next available ID across all partitions
     *
     * @return int
     */
    public static function getNextAvailableId(): int
    {
        $model = new static;
        $partitions = $model->getAllPartitionTables();
        $maxId = 0;

        foreach ($partitions as $partition) {
            try {
                $partitionMaxId = \DB::table($partition)->max('id') ?? 0;
                $maxId = max($maxId, $partitionMaxId);
            } catch (\Exception $e) {
                continue;
            }
        }

        return $maxId + 1;
    }
    /**
     * Create a new instance for specific date partition (auto-creates partition)
     *
     * @param string|Carbon $date
     * @return static
     */
    public static function forDate($date)
    {
        $instance = new static;
        $instance->usePartition($date);
        return $instance;
    }

    /**
     * Create a new instance for current month partition (auto-creates partition)
     *
     * @return static
     */
    public static function forCurrentMonth()
    {
        return static::forDate(now());
    }

    /**
     * Override create method to ensure partition exists
     *
     * @param array $attributes
     * @return static
     */
    public static function create(array $attributes = [])
    {
        if (isset($attributes['trip_date'])) {
            $instance = new static;

            // Set global unique ID if not provided
            if (!isset($attributes['id'])) {
                $attributes['id'] = static::getNextGlobalId();
            }

            $instance->usePartition($attributes['trip_date']);

            // Create using the partitioned instance
            $created = $instance->newQuery()->create($attributes);
            return $created;
        }

        return parent::create($attributes);
    }

    /**
     * Query builder for date range across partitions
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return \Illuminate\Database\Query\Builder
     */
    public static function queryDateRange(Carbon $startDate, Carbon $endDate)
    {
        $instance = new static;
        return $instance->queryAcrossPartitions($startDate, $endDate);
    }

    /**
     * Find trip instance by ID across all partitions
     *
     * @param int $id
     * @param Carbon|null $hintDate
     * @return static|null
     */
    public static function findAcrossPartitions($id, $hintDate = null)
    {
        // If hint date provided, try that partition first
        if ($hintDate) {
            $instance = static::forDate($hintDate)->find($id);
            if ($instance) {
                return $instance;
            }
        }

        // Search across all partitions
        $model = new static;
        $partitions = $model->getAllPartitionTables();

        foreach ($partitions as $partition) {
            try {
                $result = \DB::table($partition)->where('id', $id)->first();
                if ($result) {
                    // Create model instance with correct partition
                    $instance = new static;
                    $instance->setTable($partition);
                    return $instance->newFromBuilder($result);
                }
            } catch (\Exception $e) {
                // Continue searching if partition query fails
                continue;
            }
        }

        return null;
    }

    /**
     * Override update method to handle partition changes
     *
     * @param array $attributes
     * @param array $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        // If trip_date is being changed and it affects the partition
        if (isset($attributes['trip_date'])) {
            $newDate = $attributes['trip_date'];
            $newPartitionTable = $this->getPartitionTableName($newDate);
            $currentPartitionTable = $this->getTable();

            // If partition changes, we need special handling (should be done in controller)
            if ($newPartitionTable !== $currentPartitionTable) {
                throw new \Exception('Partition changes should be handled in the controller layer');
            }
        }

        return parent::update($attributes, $options);
    }

    // ... (All your relationships and helper methods remain the same)

    /**
     * Get the coach that belongs to this trip instance
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    /**
     * Get the bus that belongs to this trip instance
     */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    /**
     * Get the schedule that belongs to this trip instance
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the seat plan that belongs to this trip instance
     */
    public function seatPlan(): BelongsTo
    {
        return $this->belongsTo(SeatPlan::class);
    }

    /**
     * Get the route that belongs to this trip instance
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the driver that belongs to this trip instance
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    /**
     * Get the supervisor that belongs to this trip instance
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    /**
     * Get the migrated trip instance (may be in different partition)
     */
    public function migratedTrip(): BelongsTo
    {
        return $this->belongsTo(TripInstance::class, 'migrated_trip_id');
    }

    /**
     * Get the user who created this trip instance
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this trip instance
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who migrated this trip instance
     */
    public function migrator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'migrated_by');
    }

    /**
     * Check if coach type is AC
     */
    public function isAC(): bool
    {
        return $this->coach_type === self::COACH_TYPE_AC;
    }

    /**
     * Check if trip instance is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if trip instance is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Check if trip instance is migrated
     */
    public function isMigrated(): bool
    {
        return $this->status === self::STATUS_MIGRATED;
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
     * Get status name
     */
    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_MIGRATED => 'Migrated',
            default => 'Unknown'
        };
    }

    /**
     * Get formatted trip date
     */
    public function getFormattedTripDateAttribute(): string
    {
        return $this->trip_date->format('Y-m-d');
    }

    /**
     * Get current partition table name
     */
    public function getCurrentPartitionAttribute(): string
    {
        return $this->getPartitionTableName($this->trip_date ?? now());
    }

    /**
     * Scope for active trip instances
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for inactive trip instances
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Scope for migrated trip instances
     */
    public function scopeMigrated($query)
    {
        return $query->where('status', self::STATUS_MIGRATED);
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
     * Scope for specific date
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('trip_date', $date);
    }

    /**
     * Scope for date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('trip_date', [$startDate, $endDate]);
    }

    /**
     * Scope for today's trips
     */
    public function scopeToday($query)
    {
        return $query->whereDate('trip_date', today());
    }

    /**
     * Scope for upcoming trips
     */
    public function scopeUpcoming($query)
    {
        return $query->where('trip_date', '>=', today());
    }

    /**
     * Scope for past trips
     */
    public function scopePast($query)
    {
        return $query->where('trip_date', '<', today());
    }


    /**
     * Get seat inventory with seat details for this trip
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSeatInventoryWithDetails()
    {
        try {
            return \App\Models\SeatInventory::forTrip($this->id)
                ->with(['seat' => function($query) {
                    $query->select('id', 'seat_plan_id', 'seat_number', 'row_position', 'col_position', 'seat_type');
                }])
                ->select('id', 'seat_id', 'booking_status', 'blocked_until', 'booking_id', 'last_locked_user_id')
                ->get()
                ->map(function ($inventory) {
                    return [
                        'id' => $inventory->id,
                        'seat_id' => $inventory->seat_id,
                        'booking_status' => $inventory->booking_status,
                        'blocked_until' => $inventory->blocked_until,
                        'booking_id' => $inventory->booking_id,
                        'last_locked_user_id' => $inventory->last_locked_user_id,
                        'seat_number' => $inventory->seat->seat_number ?? null,
                        'row_position' => $inventory->seat->row_position ?? null,
                        'col_position' => $inventory->seat->col_position ?? null,
                        'seat_type' => $inventory->seat->seat_type ?? null,
                    ];
                });
        } catch (\Exception $e) {
            \Log::warning("Failed to load seat inventory for trip {$this->id}: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Accessor for seat inventory attribute
     */
    public function getSeatInventoryAttribute()
    {
        return $this->getSeatInventoryWithDetails()->toArray();
    }
}
