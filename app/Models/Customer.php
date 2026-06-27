<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'mobile',
        'name',
        'gender',
        'age',
        'address',
        'passport_no',
        'nid',
        'nationality',
        'email',
        'total_trips',
        'total_tickets',
        'total_cancelled_tickets',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'age' => 'integer',
        'total_trips' => 'integer',
        'total_tickets' => 'integer',
        'total_cancelled_tickets' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];
}
