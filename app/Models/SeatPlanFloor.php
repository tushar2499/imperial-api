<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatPlanFloor extends Model
{
    use HasFactory;

    protected $fillable = [
        'seat_plan_id',
        'name',
        'layout_type',
        'rows',
        'cols',
        'step',
        'is_extra_seat',
        'created_by',
        'updated_by',
    ];

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function seatPlan()
    {
        return $this->belongsTo(SeatPlan::class);
    }
}
