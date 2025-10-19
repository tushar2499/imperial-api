<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coach extends Model
{
    use HasFactory;
    use SoftDeletes;

     protected $fillable = [
        'name',
        'floor',
        'rows',
        'cols',
        'layout_type',
        'created_by',
        'updated_by',
    ];

}
