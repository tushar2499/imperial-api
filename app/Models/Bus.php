<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bus extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'registration_number',
        'manufacturer_company',
        'chasis_no',
        'engine_number',
        'country_of_origin',
        'lc_code_number',
        'delivery_to_dipo',
        'delivery_date',
        'color',
        'financed_by',
        'tennure_of_the_terms',
        'status',
        'created_by',
        'updated_by',
    ];

}
