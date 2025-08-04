<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_no',
        'email',
        'photo',
        'father_name',
        'mother_name',
        'date_of_birth',
        'job_type',
        'nid_or_passport_no',
        'nid_or_passport_no_image',
        'duty_hour',
        'joining_date',
        'present_address',
        'permanent_address',
        'district_id',
        'designation_id',
        'license_category',
        'license_no',
        'license_expired_date',
        'religion',
        'blood_group',
        'marital_status',
        'reference_name',
        'reference_contact_no',
        'reference_remark',
        'nominee_name',
        'nominee_photo',
        'nominee_contact_no',
        'nominee_nid_or_passport_no',
        'nominee_nid_or_passport_no_image',
        'nominee_relation',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * District
     *
     * @return BelongsTo
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Designation
     *
     * @return BelongsTo
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Employee Academics
     *
     * @return HasMany
     */
    public function academics(): HasMany
    {
        return $this->hasMany(EmployeeAcademic::class, 'employee_id');
    }

    /**
     * Employee Experiences
     *
     * @return HasMany
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(EmployeeExperience::class, 'employee_id');
    }

}
