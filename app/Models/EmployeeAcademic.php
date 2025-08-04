<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAcademic extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'degree',
        'field_of_study',
        'institute',
        'passing_year',
        'grade',
    ];
}
