<?php

namespace App\Models;

use App\Traits\AutoPartitionManager;
use App\Traits\GlobalUniqueId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SeatInventory extends Model
{
    use HasFactory, AutoPartitionManager, GlobalUniqueId;

    protected $table = 'seat_inventories';
    public $incrementing = false; // We'll handle ID manually
    protected $keyType = 'int';

    protected $fillable = [
        'trip_id',
        'seat_id',
        'booking_status',
        'blocked_until',
        'booking_id',
        'last_locked_user_id',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'booking_status' => 'integer',
        'blocked_until' => 'datetime'
    ];

    // Constants for booking status
    public const STATUS_AVAILABLE = 1;
    public const STATUS_BOOKED = 2;
    public const STATUS_BLOCKED = 3;
    public const STATUS_CANCELLED = 0;

    /**
     * Flag to prevent recursive partition switching
     */
    protected static $partitionSwitched = false;

    protected static function getSequenceTableName(): string
    {
        return 'seat_inventory_sequences';
    }


    /**
     * Boot method to automatically set partition based on trip date
     */
    protected static function boot()
    {
        parent::boot();

        // Only handle partition switching during creation if not already handled
        static::creating(function ($model) {
            if (!static::$partitionSwitched && $model->trip_id) {
                static::$partitionSwitched = true;

                // Get global unique ID first
                if (!$model->id) {
                    $model->id = static::getNextGlobalId();
                }

                $tripDate = $model->getTripDate();
                if ($tripDate) {
                    $model->usePartition($tripDate);
                }
                static::$partitionSwitched = false;
            }
        });
    }

    /**
     * Override create method to ensure unique ID
     *
     * @param array $attributes
     * @return static
     */
    public static function create(array $attributes = [])
    {
        if (isset($attributes['trip_id'])) {
            $instance = new static;
            $instance->trip_id = $attributes['trip_id'];

            // Set global unique ID if not provided
            if (!isset($attributes['id'])) {
                $attributes['id'] = static::getNextGlobalId();
            }

            $tripDate = $instance->getTripDate();
            if ($tripDate) {
                $instance->usePartition($tripDate);
                return $instance->newQuery()->create($attributes);
            }
        }

        return parent::create($attributes);
    }

    /**
     * Get trip date from related trip instance
     *
     * @return string|null
     */
    protected function getTripDate(): ?string
    {
        try {
            // First try to get from relationship if loaded
            if ($this->relationLoaded('tripInstance')) {
                return $this->tripInstance->trip_date->format('Y-m-d');
            }

            // If we don't have a trip_id, we can't find the trip
            if (!$this->trip_id) {
                return null;
            }

            // Try to find the trip across all partitions
            $tripInstance = \App\Models\TripInstance::findAcrossPartitions($this->trip_id);

            if ($tripInstance) {
                return $tripInstance->trip_date->format('Y-m-d');
            }

            return null;

        } catch (\Exception $e) {
            \Log::error("Failed to get trip date for seat inventory: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create seat inventory for specific date partition
     *
     * @param string|Carbon $date
     * @return static
     */
    public static function forDate($date)
    {
        $instance = new static;
        return $instance->usePartition($date);
    }

    /**
     * Create seat inventory for current month partition
     *
     * @return static
     */
    public static function forCurrentMonth()
    {
        return static::forDate(now());
    }

    /**
     * Create seat inventory for trip
     *
     * @param int $tripId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function forTrip($tripId)
    {
        // First, try to find the trip to get the trip date
        $tripInstance = \App\Models\TripInstance::findAcrossPartitions($tripId);

        if ($tripInstance) {
            // Use the trip date to set the correct partition
            $instance = new static;
            $instance->usePartition($tripInstance->trip_date);
            return $instance->newQuery()->where('trip_id', $tripId);
        }

        // If trip not found, try current month partition as fallback
        $instance = static::forCurrentMonth();
        return $instance->newQuery()->where('trip_id', $tripId);
    }

    


    /**
     * Get the trip instance that belongs to this seat inventory
     */
    public function tripInstance(): BelongsTo
    {
        return $this->belongsTo(TripInstance::class, 'trip_id');
    }

    /**
     * Get the seat that belongs to this inventory
     */
    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    /**
     * Get the booking that belongs to this seat inventory
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user who last locked this seat
     */
    public function lastLockedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_locked_user_id');
    }

    /**
     * Get the user who created this seat inventory
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this seat inventory
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if seat is available
     */
    public function isAvailable(): bool
    {
        return $this->booking_status === self::STATUS_AVAILABLE;
    }

    /**
     * Check if seat is booked
     */
    public function isBooked(): bool
    {
        return $this->booking_status === self::STATUS_BOOKED;
    }

    /**
     * Check if seat is blocked
     */
    public function isBlocked(): bool
    {
        return $this->booking_status === self::STATUS_BLOCKED;
    }

    /**
     * Check if seat is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->booking_status === self::STATUS_CANCELLED;
    }

    /**
     * Check if block has expired
     */
    public function isBlockExpired(): bool
    {
        return $this->isBlocked() &&
               $this->blocked_until &&
               now()->greaterThan($this->blocked_until);
    }

    /**
     * Get booking status name
     */
    public function getBookingStatusNameAttribute(): string
    {
        return match ($this->booking_status) {
            self::STATUS_AVAILABLE => 'Available',
            self::STATUS_BOOKED => 'Booked',
            self::STATUS_BLOCKED => 'Blocked',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown'
        };
    }

    /**
     * Scope for available seats
     */
    public function scopeAvailable($query)
    {
        return $query->where('booking_status', self::STATUS_AVAILABLE);
    }

    /**
     * Scope for booked seats
     */
    public function scopeBooked($query)
    {
        return $query->where('booking_status', self::STATUS_BOOKED);
    }

    /**
     * Scope for blocked seats
     */
    public function scopeBlocked($query)
    {
        return $query->where('booking_status', self::STATUS_BLOCKED);
    }

    /**
     * Scope for cancelled seats
     */
    public function scopeCancelled($query)
    {
        return $query->where('booking_status', self::STATUS_CANCELLED);
    }

    /**
     * Scope for expired blocks
     */
    public function scopeExpiredBlocks($query)
    {
        return $query->where('booking_status', self::STATUS_BLOCKED)
                    ->where('blocked_until', '<', now());
    }

    /**
     * Scope for specific trip
     */
    public function scopeForTrip($query, $tripId)
    {
        return $query->where('trip_id', $tripId);
    }

    /**
     * Scope for specific seat
     */
    public function scopeForSeat($query, $seatId)
    {
        return $query->where('seat_id', $seatId);
    }
}
