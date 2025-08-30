<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
