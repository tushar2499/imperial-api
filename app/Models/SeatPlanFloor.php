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

    protected $casts = [
        'seat_plan_id' => 'integer',
        'rows' => 'integer',
        'cols' => 'integer',
        'step' => 'integer',
        'is_extra_seat' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
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
