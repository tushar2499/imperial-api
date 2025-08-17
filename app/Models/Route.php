<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_id',
        'end_id',
        'distance',
        'duration',
        'status',
        'created_by',
        'updated_by',
    ];

    public function startDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'start_id');
    }

    public function endDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'end_id');
    }

}
