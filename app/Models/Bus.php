<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bus extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registration_number',
        'manufacturer_company',
        'model_year',
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

    protected $casts = [
        'model_year' => 'integer',
        'tennure_of_the_terms' => 'integer',
        'delivery_date' => 'date',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];
}
