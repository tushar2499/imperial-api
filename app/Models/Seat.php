<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = [
        'seat_plan_floor_id',
        'seat_plan_id',
        'seat_number',
        'row_position',
        'col_position',
        'seat_type',
        'is_disabled',
        'status',
        'created_by',
        'updated_by',
    ];

    public function seatPlanFloor()
    {
        return $this->belongsTo(SeatPlanFloor::class);
    }

    public function seatPlan()
    {
        return $this->belongsTo(SeatPlan::class);
    }

    public function seatInventories()
    {
        return $this->hasMany(SeatInventory::class);
    }
}
