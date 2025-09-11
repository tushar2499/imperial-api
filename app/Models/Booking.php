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
        'route_id',
        'boarding_id',
        'dropping_id',
        'total_price',
        'total_discount',
        'total_amount',
        'created_by',
        'updated_by',
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
        return $this->belongsTo(Route::class);
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
