<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'pnr_number',
        'trip_id',
        'type',
        'date',
        'time',
        'note',
        'route_id',
        'boarding_id',
        'dropping_id',
        'total_price',
        'total_discount',
        'total_amount',
        'expire_date',
        'expire_time',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'pnr_number' => 'string',
        'trip_id' => 'integer',
        'type' => 'integer',
        'date' => 'date',
        'time' => 'datetime:H:i',
        'note' => 'string',
        'route_id' => 'integer',
        'boarding_id' => 'integer',
        'dropping_id' => 'integer',
        'total_price' => 'decimal:3',
        'total_discount' => 'decimal:3',
        'total_amount' => 'decimal:3',
        'expire_date' => 'date',
        'expire_time' => 'datetime:H:i',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /**
     * Customer
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Trip
     *
     * @return BelongsTo
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(TripInstance::class);
    }

    /**
     * Route
     *
     * @return BelongsTo
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    /**
     * Boarding
     *
     * @return BelongsTo
     */
    public function boarding(): BelongsTo
    {
        return $this->belongsTo(TripBoardingDropping::class, 'boarding_id');
    }

    /**
     * Dropping
     *
     * @return BelongsTo
     */
    public function dropping(): BelongsTo
    {
        return $this->belongsTo(TripBoardingDropping::class, 'dropping_id');
    }

    /**
     * Booking Details
     *
     * @return HasMany
     */
    public function bookingDetails(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }
}
