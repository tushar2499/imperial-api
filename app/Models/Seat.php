<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'is_disable',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'seat_plan_floor_id' => 'integer',
        'seat_plan_id' => 'integer',
        'row_position' => 'integer',
        'col_position' => 'integer',
        'is_disable' => 'boolean',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /**
     * Get the floor this seat belongs to.
     *
     * @return BelongsTo
     */
    public function seatPlanFloor(): BelongsTo
    {
        return $this->belongsTo(SeatPlanFloor::class);
    }

    /**
     * Get the seat plan this seat belongs to.
     *
     * @return BelongsTo
     */
    public function seatPlan(): BelongsTo
    {
        return $this->belongsTo(SeatPlan::class);
    }

    /**
     * Get all seat inventory records for this seat.
     *
     * @return HasMany
     */
    public function seatInventories(): HasMany
    {
        return $this->hasMany(SeatInventory::class);
    }

    /**
     * Get the user who created this record.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
