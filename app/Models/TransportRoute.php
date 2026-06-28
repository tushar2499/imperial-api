<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportRoute extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transport_routes';

    protected $fillable = [
        'start_id',
        'end_id',
        'distance',
        'duration',
        'is_popular',
        'popular_position',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_id' => 'integer',
        'end_id' => 'integer',
        'distance' => 'decimal:2',
        'duration' => 'integer',
        'is_popular' => 'boolean',
        'popular_position' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /**
     * Get the starting district for this transport route.
     *
     * @return BelongsTo
     */
    public function startDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'start_id');
    }

    /**
     * Get the ending district for this transport route.
     *
     * @return BelongsTo
     */
    public function endDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'end_id');
    }

    /**
     * Get all intermediate stations along this transport route.
     *
     * @return HasMany
     */
    public function stations(): HasMany
    {
        return $this->hasMany(Station::class, 'transport_route_id');
    }

    /**
     * Get all fares defined for this transport route.
     *
     * @return HasMany
     */
    public function fares(): HasMany
    {
        return $this->hasMany(Fare::class, 'route_id');
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
