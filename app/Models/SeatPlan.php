<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeatPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'floor',
        'rows',
        'cols',
        'layout_type',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'floor' => 'integer',
        'rows' => 'integer',
        'cols' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /**
     * Get all seats belonging to this seat plan.
     *
     * @return HasMany
     */
    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    /**
     * Get all floor records belonging to this seat plan.
     *
     * @return HasMany
     */
    public function floors(): HasMany
    {
        return $this->hasMany(SeatPlanFloor::class);
    }

    /**
     * Get all floor data associated with this seat plan.
     *
     * @return HasMany
     */
    public function floorData(): HasMany
    {
        return $this->hasMany(SeatPlanFloor::class, 'seat_plan_id', 'id');
    }
}
