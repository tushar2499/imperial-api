<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of all employees.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $employees = Employee::get()->map(function ($employee) {
                $employee->photo = $employee->photo ? asset($employee->photo) : null;
                return $employee;
            });

            return $this->successResponse($employees, 'employees retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve employees: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Store a newly created employee.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                             => 'required|string|max:255',
            'contact_no'                       => 'required|string|max:255',
            'email'                            => 'nullable|string|max:255',
            'photo'                            => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
            'father_name'                      => 'nullable|string|max:255',
            'mother_name'                      => 'nullable|string|max:255',
            'date_of_birth'                    => 'required|date|date_format:Y-m-d',
            'nid_or_passport_no'               => 'nullable|string|max:255',
            'nid_or_passport_no_image'         => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
            'job_type'                         => 'nullable|string|max:255',
            'duty_hour'                        => 'nullable|string|max:255',
            'joining_date'                     => 'nullable|date|date_format:Y-m-d',
            'present_address'                  => 'nullable|string',
            'permanent_address'                => 'nullable|string',
            'district_id'                      => 'nullable|exists:districts,id',
            'designation_id'                   => 'nullable|exists:designations,id',
            'license_category'                 => 'nullable|string|max:255',
            'license_no'                       => 'nullable|string|max:255',
            'license_expired_date'             => 'nullable|date|date_format:Y-m-d',
            'religion'                         => 'nullable|string|max:255',
            'blood_group'                      => 'nullable|string|max:255',
            'marital_status'                   => 'nullable|string|max:255',
            'reference_name'                   => 'nullable|string|max:255',
            'reference_contact_no'             => 'nullable|string|max:255',
            'reference_remark'                 => 'nullable|string',
            'nominee_name'                     => 'nullable|string|max:255',
            'nominee_contact_no'               => 'nullable|string|max:255',
            'nominee_photo'                    => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
            'nominee_nid_or_passport_no'       => 'nullable|string|max:255',
            'nominee_nid_or_passport_no_image' => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
            'nominee_relation'                 => 'nullable|string|max:255',
            'academics'                        => 'nullable|array',
            'academics.*.degree'               => 'required_with:academics|string|max:255',
            'academics.*.field_of_study'       => 'nullable|string|max:255',
            'academics.*.institute'            => 'required_with:academics|string|max:255',
            'academics.*.passing_year'         => 'nullable|string|max:255',
            'academics.*.grade'                => 'nullable|string|max:255',
            'experiences'                      => 'nullable|array',
            'experiences.*.organization'       => 'required_with:experiences|string|max:255',
            'experiences.*.position'           => 'required_with:experiences|string|max:255',
            'experiences.*.start_date'         => 'nullable|date|date_format:Y-m-d',
            'experiences.*.end_date'           => 'nullable|date|date_format:Y-m-d',
            'experiences.*.responsibility'     => 'nullable|string',
        ], [

            // Custom academics error messages
            'academics.*.degree.required_with'         => 'Degree is required for each academic entry.',
            'academics.*.institute.required_with'      => 'Institute is required for each academic entry.',
            'academics.*.degree.string'                => 'Degree must be a valid text.',
            'academics.*.institute.string'             => 'Institute must be a valid text.',
            'academics.*.degree.max'                   => 'Degree cannot exceed 255 characters.',
            'academics.*.institute.max'                => 'Institute cannot exceed 255 characters.',

            // Custom eductions error messages
            'experiences.*.organization.required_with' => 'Organization is required for each experience entry.',
            'experiences.*.position.required_with'     => 'Position is required for each experience entry.',
            'experiences.*.organization.string'        => 'Organization must be a valid text.',
            'experiences.*.position.string'            => 'Position must be a valid text.',
            'experiences.*.organization.max'           => 'Organization cannot exceed 255 characters.',
            'experiences.*.position.max'               => 'Position cannot exceed 255 characters.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $academics     = $request->input('academics', []);
        $academicsData = [];

        foreach ($academics as $index => $academic) {
            $academicsData[] = [
                'degree'         => $academic['degree'],
                'field_of_study' => $academic['field_of_study'] ?? null,
                'institute'      => $academic['institute'],
                'passing_year'   => $academic['passing_year'] ?? null,
                'grade'          => $academic['grade'] ?? null,
            ];
        }

        $experiences     = $request->input('experiences', []);
        $experiencesData = [];

        foreach ($experiences as $index => $experience) {
            $experiencesData[] = [
                'organization'   => $experience['organization'],
                'position'       => $experience['position'],
                'start_date'     => $experience['start_date'] ?? null,
                'end_date'       => $experience['end_date'] ?? null,
                'responsibility' => $experience['responsibility'] ?? null,
            ];
        }

        $photo_path                            = file_uploaded($request->file('photo'), 'employees');
        $nid_or_passport_no_image_path         = file_uploaded($request->file('nid_or_passport_no_image'), 'employees');
        $nominee_photo_path                    = file_uploaded($request->file('nominee_photo'), 'employees');
        $nominee_nid_or_passport_no_image_path = file_uploaded($request->file('nominee_nid_or_passport_no_image'), 'employees');

        $data = [
            'name'                             => $request->input('name'),
            'contact_no'                       => $request->input('contact_no'),
            'email'                            => $request->input('email'),
            'photo'                            => $photo_path,
            'father_name'                      => $request->input('father_name'),
            'mother_name'                      => $request->input('mother_name'),
            'date_of_birth'                    => $request->input('date_of_birth'),
            'nid_or_passport_no'               => $request->input('nid_or_passport_no'),
            'nid_or_passport_no_image'         => $nid_or_passport_no_image_path,
            'job_type'                         => $request->input('job_type'),
            'duty_hour'                        => $request->input('duty_hour'),
            'joining_date'                     => $request->input('joining_date'),
            'present_address'                  => $request->input('present_address'),
            'permanent_address'                => $request->input('permanent_address'),
            'district_id'                      => $request->input('district_id'),
            'designation_id'                   => $request->input('designation_id'),
            'license_category'                 => $request->input('license_category'),
            'license_no'                       => $request->input('license_no'),
            'license_expired_date'             => $request->input('license_expired_date'),
            'religion'                         => $request->input('religion'),
            'blood_group'                      => $request->input('blood_group'),
            'marital_status'                   => $request->input('marital_status'),
            'reference_name'                   => $request->input('reference_name'),
            'reference_contact_no'             => $request->input('reference_contact_no'),
            'reference_remark'                 => $request->input('reference_remark'),
            'nominee_name'                     => $request->input('nominee_name'),
            'nominee_contact_no'               => $request->input('nominee_contact_no'),
            'nominee_photo'                    => $nominee_photo_path,
            'nominee_nid_or_passport_no'       => $request->input('nominee_nid_or_passport_no'),
            'nominee_nid_or_passport_no_image' => $nominee_nid_or_passport_no_image_path,
            'nominee_relation'                 => $request->input('nominee_relation'),
            'created_by'                       => auth()->user()->id,
        ];

        try {
            DB::beginTransaction();

            $employee = Employee::create($data);

            if ($academicsData) {
                $employee->academics()->createMany($academicsData);
            }

            if ($experiencesData) {
                $employee->experiences()->createMany($experiencesData);
            }

            DB::commit();

            if ($employee) {
                $employee->photo                            = $employee->photo ? asset($employee->photo) : null;
                $employee->nid_or_passport_no_image         = $employee->nid_or_passport_no_image ? asset($employee->nid_or_passport_no_image) : null;
                $employee->nominee_photo                    = $employee->nominee_photo ? asset($employee->nominee_photo) : null;
                $employee->nominee_nid_or_passport_no_image = $employee->nominee_nid_or_passport_no_image ? asset($employee->nominee_nid_or_passport_no_image) : null;
            }

            return $this->successResponse(['data' => $employee], 'Employee created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to create employee: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Display the specified employee.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $employee = Employee::with(['experiences', 'academics'])->where('id', $id)->firstOrFail();

            if ($employee) {
                $employee->photo                            = $employee->photo ? asset($employee->photo) : null;
                $employee->nid_or_passport_no_image         = $employee->nid_or_passport_no_image ? asset($employee->nid_or_passport_no_image) : null;
                $employee->nominee_photo                    = $employee->nominee_photo ? asset($employee->nominee_photo) : null;
                $employee->nominee_nid_or_passport_no_image = $employee->nominee_nid_or_passport_no_image ? asset($employee->nominee_nid_or_passport_no_image) : null;
            }

            return $this->successResponse($employee, 'Employee retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve employee: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Update the specified employee.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::where('id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name'                             => 'required|string|max:255',
            'contact_no'                       => 'required|string|max:255',
            'email'                            => 'nullable|string|max:255',
            'photo'                            => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
            'father_name'                      => 'nullable|string|max:255',
            'mother_name'                      => 'nullable|string|max:255',
            'date_of_birth'                    => 'required|date|date_format:Y-m-d',
            'nid_or_passport_no'               => 'nullable|string|max:255',
            'nid_or_passport_no_image'         => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
            'job_type'                         => 'nullable|string|max:255',
            'duty_hour'                        => 'nullable|string|max:255',
            'joining_date'                     => 'nullable|date|date_format:Y-m-d',
            'present_address'                  => 'nullable|string',
            'permanent_address'                => 'nullable|string',
            'district_id'                      => 'nullable|exists:districts,id',
            'designation_id'                   => 'nullable|exists:designations,id',
            'license_category'                 => 'nullable|string|max:255',
            'license_no'                       => 'nullable|string|max:255',
            'license_expired_date'             => 'nullable|date|date_format:Y-m-d',
            'religion'                         => 'nullable|string|max:255',
            'blood_group'                      => 'nullable|string|max:255',
            'marital_status'                   => 'nullable|string|max:255',
            'reference_name'                   => 'nullable|string|max:255',
            'reference_contact_no'             => 'nullable|string|max:255',
            'reference_remark'                 => 'nullable|string',
            'nominee_name'                     => 'nullable|string|max:255',
            'nominee_contact_no'               => 'nullable|string|max:255',
            'nominee_photo'                    => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
            'nominee_nid_or_passport_no'       => 'nullable|string|max:255',
            'nominee_nid_or_passport_no_image' => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
            'nominee_relation'                 => 'nullable|string|max:255',
            'academics'                        => 'nullable|array',
            'academics.*.degree'               => 'required_with:academics|string|max:255',
            'academics.*.field_of_study'       => 'nullable|string|max:255',
            'academics.*.institute'            => 'required_with:academics|string|max:255',
            'academics.*.passing_year'         => 'nullable|string|max:255',
            'academics.*.grade'                => 'nullable|string|max:255',
            'experiences'                      => 'nullable|array',
            'experiences.*.organization'       => 'required_with:experiences|string|max:255',
            'experiences.*.position'           => 'required_with:experiences|string|max:255',
            'experiences.*.start_date'         => 'nullable|date|date_format:Y-m-d',
            'experiences.*.end_date'           => 'nullable|date|date_format:Y-m-d',
            'experiences.*.responsibility'     => 'nullable|string',
        ], [
            // Custom academics error messages
            'academics.*.degree.required_with'         => 'Degree is required for each academic entry.',
            'academics.*.institute.required_with'      => 'Institute is required for each academic entry.',
            'academics.*.degree.string'                => 'Degree must be a valid text.',
            'academics.*.institute.string'             => 'Institute must be a valid text.',
            'academics.*.degree.max'                   => 'Degree cannot exceed 255 characters.',
            'academics.*.institute.max'                => 'Institute cannot exceed 255 characters.',

            // Custom eductions error messages
            'experiences.*.organization.required_with' => 'Organization is required for each experience entry.',
            'experiences.*.position.required_with'     => 'Position is required for each experience entry.',
            'experiences.*.organization.string'        => 'Organization must be a valid text.',
            'experiences.*.position.string'            => 'Position must be a valid text.',
            'experiences.*.organization.max'           => 'Organization cannot exceed 255 characters.',
            'experiences.*.position.max'               => 'Position cannot exceed 255 characters.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $academics     = $request->input('academics', []);
        $academicsData = [];

        foreach ($academics as $index => $academic) {
            $academicsData[] = [
                'degree'         => $academic['degree'],
                'field_of_study' => $academic['field_of_study'] ?? null,
                'institute'      => $academic['institute'],
                'passing_year'   => $academic['passing_year'] ?? null,
                'grade'          => $academic['grade'] ?? null,
            ];
        }

        $experiences     = $request->input('experiences', []);
        $experiencesData = [];

        foreach ($experiences as $index => $experience) {
            $experiencesData[] = [
                'organization'   => $experience['organization'],
                'position'       => $experience['position'],
                'start_date'     => $experience['start_date'] ?? null,
                'end_date'       => $experience['end_date'] ?? null,
                'responsibility' => $experience['responsibility'] ?? null,
            ];
        }

        $photo_path                            = $employee->photo;
        $nid_or_passport_no_image_path         = $employee->nid_or_passport_no_image;
        $nominee_photo_path                    = $employee->nominee_photo;
        $nominee_nid_or_passport_no_image_path = $employee->nominee_nid_or_passport_no_image;

        if ($request->hasFile('photo')) {
            $photo_path = file_uploaded($request->file('photo'), 'employees');

            if ($photo_path) {
                delete_uploaded_file($employee->photo);
            }

        }

        if ($request->hasFile('nid_or_passport_no_image')) {
            $nid_or_passport_no_image_path = file_uploaded($request->file('nid_or_passport_no_image'), 'employees');

            if ($nid_or_passport_no_image_path) {
                delete_uploaded_file($employee->nid_or_passport_no_image);
            }

        }

        if ($request->hasFile('nominee_photo')) {
            $nominee_photo_path = file_uploaded($request->file('nominee_photo'), 'employees');

            if ($nominee_photo_path) {
                delete_uploaded_file($employee->nominee_photo);
            }

        }

        if ($request->hasFile('nominee_nid_or_passport_no_image')) {
            $nominee_nid_or_passport_no_image_path = file_uploaded($request->file('nominee_nid_or_passport_no_image'), 'employees');

            if ($nominee_nid_or_passport_no_image_path) {
                delete_uploaded_file($employee->nominee_nid_or_passport_no_image);
            }

        }

        $data = [
            'name'                             => $request->input('name'),
            'contact_no'                       => $request->input('contact_no'),
            'email'                            => $request->input('email'),
            'photo'                            => $photo_path,
            'father_name'                      => $request->input('father_name'),
            'mother_name'                      => $request->input('mother_name'),
            'date_of_birth'                    => $request->input('date_of_birth'),
            'nid_or_passport_no'               => $request->input('nid_or_passport_no'),
            'nid_or_passport_no_image'         => $nid_or_passport_no_image_path,
            'job_type'                         => $request->input('job_type'),
            'duty_hour'                        => $request->input('duty_hour'),
            'joining_date'                     => $request->input('joining_date'),
            'present_address'                  => $request->input('present_address'),
            'permanent_address'                => $request->input('permanent_address'),
            'district_id'                      => $request->input('district_id'),
            'designation_id'                   => $request->input('designation_id'),
            'license_category'                 => $request->input('license_category'),
            'license_no'                       => $request->input('license_no'),
            'license_expired_date'             => $request->input('license_expired_date'),
            'religion'                         => $request->input('religion'),
            'blood_group'                      => $request->input('blood_group'),
            'marital_status'                   => $request->input('marital_status'),
            'reference_name'                   => $request->input('reference_name'),
            'reference_contact_no'             => $request->input('reference_contact_no'),
            'reference_remark'                 => $request->input('reference_remark'),
            'nominee_name'                     => $request->input('nominee_name'),
            'nominee_contact_no'               => $request->input('nominee_contact_no'),
            'nominee_photo'                    => $nominee_photo_path,
            'nominee_nid_or_passport_no'       => $request->input('nominee_nid_or_passport_no'),
            'nominee_nid_or_passport_no_image' => $nominee_nid_or_passport_no_image_path,
            'nominee_relation'                 => $request->input('nominee_relation'),
            'updated_by'                       => auth()->user()->id,
        ];

        try {
            DB::beginTransaction();

            $employee->update($data);

            $employee->experiences()->delete();
            $employee->academics()->delete();

            $employee->experiences()->createMany($experiencesData);
            $employee->academics()->createMany($academicsData);

            $employee = $employee->refresh();

            if ($employee) {
                $employee->photo                            = $employee->photo ? asset($employee->photo) : null;
                $employee->nid_or_passport_no_image         = $employee->nid_or_passport_no_image ? asset($employee->nid_or_passport_no_image) : null;
                $employee->nominee_photo                    = $employee->nominee_photo ? asset($employee->nominee_photo) : null;
                $employee->nominee_nid_or_passport_no_image = $employee->nominee_nid_or_passport_no_image ? asset($employee->nominee_nid_or_passport_no_image) : null;
            }

            DB::commit();

            return $this->successResponse($employee, 'Employee updated successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update employee: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Remove the specified employee.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $employee = Employee::where('id', $id)->firstOrFail();

            $employee->experiences()->delete();
            $employee->academics()->delete();
            delete_uploaded_file($employee->photo);
            delete_uploaded_file($employee->nid_or_passport_no_image);
            delete_uploaded_file($employee->nominee_photo);
            delete_uploaded_file($employee->nominee_nid_or_passport_no_image);

            $employee->delete();

            DB::commit();

            return $this->successResponse(null, 'Employee deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to delete employee: ' . $e->getMessage(), 500);
        }

    }

}
