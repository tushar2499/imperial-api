<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Station extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'route_id',
        'district_id',
        'status',
        'created_by',
        'updated_by',
    ];
}
