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
        'status',
        'blocked_until',
        'notes',
        'metadata',
    ];
}
