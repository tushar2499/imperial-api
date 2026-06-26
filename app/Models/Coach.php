<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coach extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'coach_no',
        'seat_plan_id',
        'coach_type',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'seat_plan_id' => 'integer',
        'coach_type' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /**
     * Get the seat plan this coach is configured with.
     */
    public function seatPlan(): BelongsTo
    {
        return $this->belongsTo(SeatPlan::class);
    }
}
