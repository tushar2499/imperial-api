<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Counter extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'type',
        'address',
        'land_mark',
        'location_url',
        'phone',
        'mobile',
        'email',
        'primary_contact_no',
        'country',
        'district_id',
        'booking_allowed_status',
        'booking_allowed_class',
        'no_of_boarding_allowed',
        'sms_status',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'type' => 'integer',
        'address' => 'string',
        'land_mark' => 'string',
        'location_url' => 'string',
        'phone' => 'string',
        'mobile' => 'string',
        'email' => 'string',
        'primary_contact_no' => 'string',
        'country' => 'string',
        'district_id' => 'integer',
        'booking_allowed_status' => 'integer',
        'booking_allowed_class' => 'integer',
        'no_of_boarding_allowed' => 'integer',
        'sms_status' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /**
     * Get the district that this counter belongs to.
     *
     * @return BelongsTo
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    /**
     * Get the trip boarding/dropping points for this counter.
     *
     * @return HasMany
     */
    public function tripBoardingDroppings(): HasMany
    {
        return $this->hasMany(TripBoardingDropping::class, 'counter_id', 'id');
    }
}
