<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_id',
        'seat_inventory_id',
        'trip_id',
        'seat_id',
        'user_id',
        'guest_token',
        'status',
        'blocked_until',
        'notes',
        'metadata',
        'tran_id',
    ];

    protected $casts = [
        'seat_inventory_id' => 'integer',
        'trip_id' => 'integer',
        'seat_id' => 'integer',
        'user_id' => 'integer',
        'blocked_until' => 'datetime',
        'metadata' => 'array',
    ];
}
