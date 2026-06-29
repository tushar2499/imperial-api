<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'coach_no' => 'string',
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

    /**
     * Get the coach configurations for this coach.
     */
    public function coachConfigurations(): HasMany
    {
        return $this->hasMany(CoachConfiguration::class);
    }

    /**
     * Get the trip instances for this coach.
     */
    public function tripInstances(): HasMany
    {
        return $this->hasMany(TripInstance::class);
    }

    /**
     * Get the user who created this coach.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this coach.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
