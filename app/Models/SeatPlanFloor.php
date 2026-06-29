<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Get all seats belonging to this floor.
     *
     * @return HasMany
     */
    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    /**
     * Get the seat plan this floor belongs to.
     *
     * @return BelongsTo
     */
    public function seatPlan(): BelongsTo
    {
        return $this->belongsTo(SeatPlan::class);
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
