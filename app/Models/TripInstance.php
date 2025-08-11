<?php

namespace App\Models;

use App\Traits\AutoPartitionManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TripInstance extends Model
{
    use HasFactory, AutoPartitionManager;

    protected $table = 'trip_instances';

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
     * Boot method to automatically set partition based on trip_date
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->trip_date) {
                $model->usePartition($model->trip_date);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('trip_date')) {
                $model->usePartition($model->trip_date);
            }
        });

        static::saving(function ($model) {
            if ($model->trip_date) {
                $model->usePartition($model->trip_date);
            }
        });
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
            $instance->usePartition($attributes['trip_date']);
            return $instance->newQuery()->create($attributes);
        }

        return parent::create($attributes);
    }

    /**
     * Override where method to auto-switch partition if querying by trip_date
     *
     * @param \Closure|string|array|\Illuminate\Database\Query\Expression $column
     * @param mixed $operator
     * @param mixed $value
     * @param string $boolean
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function scopeWhereDate($query, $column, $operator, $value = null, $boolean = 'and')
    {
        if ($column === 'trip_date' && $value) {
            // Auto-switch to correct partition
            $this->usePartition($value);
            $query->from($this->getTable());
        }

        return $query->whereDate($column, $operator, $value, $boolean);
    }

    /**
     * Override newQuery to ensure we're using the right partition
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery()
    {
        $builder = parent::newQuery();

        // If we have a trip_date and we're still using base table, switch to partition
        if (isset($this->attributes['trip_date']) && $this->getTable() === 'trip_instances') {
            $this->usePartition($this->attributes['trip_date']);
            $builder->from($this->getTable());
        }

        return $builder;
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
                $result = DB::table($partition)->where('id', $id)->first();
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
}
