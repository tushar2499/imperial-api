<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected $casts = [
        'employee_id' => 'integer',
        'passing_year' => 'integer',
    ];

    /**
     * Get the employee that owns this academic record.
     *
     * @return BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
