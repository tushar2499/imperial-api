<?php

namespace App\Http\Requests\Api\Employee;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmployeeUpdateRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_no' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'date_format:Y-m-d'],
            'nid_or_passport_no' => ['nullable', 'string', 'max:255'],
            'nid_or_passport_no_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'job_type' => ['nullable', 'string', 'max:255'],
            'duty_hour' => ['nullable', 'string', 'max:255'],
            'joining_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'present_address' => ['nullable', 'string'],
            'permanent_address' => ['nullable', 'string'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'designation_id' => ['nullable', 'integer', 'exists:designations,id'],
            'license_category' => ['nullable', 'string', 'max:255'],
            'license_no' => ['nullable', 'string', 'max:255'],
            'license_expired_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'religion' => ['nullable', 'string', 'max:255'],
            'blood_group' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'string', 'max:255'],
            'reference_name' => ['nullable', 'string', 'max:255'],
            'reference_contact_no' => ['nullable', 'string', 'max:255'],
            'reference_remark' => ['nullable', 'string'],
            'nominee_name' => ['nullable', 'string', 'max:255'],
            'nominee_contact_no' => ['nullable', 'string', 'max:255'],
            'nominee_photo' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'nominee_nid_or_passport_no' => ['nullable', 'string', 'max:255'],
            'nominee_nid_or_passport_no_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'nominee_relation' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'integer', 'in:0,1'],
            'academics' => ['nullable', 'array'],
            'academics.*.degree' => ['required_with:academics', 'string', 'max:255'],
            'academics.*.field_of_study' => ['nullable', 'string', 'max:255'],
            'academics.*.institute' => ['required_with:academics', 'string', 'max:255'],
            'academics.*.passing_year' => ['nullable', 'string', 'max:255'],
            'academics.*.grade' => ['nullable', 'string', 'max:255'],
            'experiences' => ['nullable', 'array'],
            'experiences.*.organization' => ['required_with:experiences', 'string', 'max:255'],
            'experiences.*.position' => ['required_with:experiences', 'string', 'max:255'],
            'experiences.*.start_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'experiences.*.end_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'experiences.*.responsibility' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'academics.*.degree.required_with' => 'Degree is required for each academic entry.',
            'academics.*.institute.required_with' => 'Institute is required for each academic entry.',
            'academics.*.degree.string' => 'Degree must be a valid text.',
            'academics.*.institute.string' => 'Institute must be a valid text.',
            'academics.*.degree.max' => 'Degree cannot exceed 255 characters.',
            'academics.*.institute.max' => 'Institute cannot exceed 255 characters.',
            'experiences.*.organization.required_with' => 'Organization is required for each experience entry.',
            'experiences.*.position.required_with' => 'Position is required for each experience entry.',
            'experiences.*.organization.string' => 'Organization must be a valid text.',
            'experiences.*.position.string' => 'Position must be a valid text.',
            'experiences.*.organization.max' => 'Organization cannot exceed 255 characters.',
            'experiences.*.position.max' => 'Position cannot exceed 255 characters.',
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to perform this action.')
        );
    }
}
