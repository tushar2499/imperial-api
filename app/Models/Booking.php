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
        'trip_date',
        'trip_time',
        'route_id',
        'boarding_id',
        'dropping_id',
        'created_by',
        'updated_by',
    ];


    /**
     * Customer
     *
     * @return BelongsTo
     */
    public function customer() : BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Booking Details
     *
     * @return HasMany
     */
    public function bookingDetails() : HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }
}
