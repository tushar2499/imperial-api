<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Counter extends Model
{
    use HasFactory;

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

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function tripBoardingDroppings(): HasMany
    {
        return $this->hasMany(TripBoardingDropping::class, 'counter_id', 'id');
    }
}
