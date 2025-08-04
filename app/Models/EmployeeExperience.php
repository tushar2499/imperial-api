<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeExperience extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'organization',
        'position',
        'start_date',
        'end_date',
        'responsibility',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
