<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'seat_inventory_id',
        'seat_id',
        'price',
        'discount',
        'amount',
        'created_by',
        'updated_by',
    ];

    /**
     * Booking
     *
     * @return BelongsTo
     */
    public function booking() : BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Seat Inventory
     *
     * @return BelongsTo
     */
    public function seatInventory() : BelongsTo
    {
        return $this->belongsTo(SeatInventory::class);
    }
}
