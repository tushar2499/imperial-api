<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'floor',
        'rows',
        'cols',
        'layout_type',
        'created_by',
        'updated_by',
    ];

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function floors()
    {
        return $this->hasMany(SeatPlanFloor::class);
    }

    public function floorData()
    {
        return $this->hasMany(SeatPlanFloor::class, 'seat_plan_id', 'id');
    }
}
