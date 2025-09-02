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
        'status',
        'created_by',
        'updated_by',
    ];
}
