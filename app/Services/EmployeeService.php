<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    /**
     * Get a paginated listing of employees with optional search.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = Employee::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_no', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Create a new employee and their academics/experiences inside a database transaction.
     *
     * @param  array  $attributes
     * @return Employee
     */
    public function store(array $attributes): Employee
    {
        return DB::transaction(function () use ($attributes) {
            $employee = Employee::create([
                'name' => $attributes['name'],
                'contact_no' => $attributes['contact_no'],
                'email' => $attributes['email'] ?? null,
                'photo' => file_uploaded($attributes['photo'] ?? null, 'employees'),
                'father_name' => $attributes['father_name'] ?? null,
                'mother_name' => $attributes['mother_name'] ?? null,
                'date_of_birth' => $attributes['date_of_birth'],
                'nid_or_passport_no' => $attributes['nid_or_passport_no'] ?? null,
                'nid_or_passport_no_image' => file_uploaded($attributes['nid_or_passport_no_image'] ?? null, 'employees'),
                'job_type' => $attributes['job_type'] ?? null,
                'duty_hour' => $attributes['duty_hour'] ?? null,
                'joining_date' => $attributes['joining_date'] ?? null,
                'present_address' => $attributes['present_address'] ?? null,
                'permanent_address' => $attributes['permanent_address'] ?? null,
                'district_id' => $attributes['district_id'] ?? null,
                'designation_id' => $attributes['designation_id'] ?? null,
                'license_category' => $attributes['license_category'] ?? null,
                'license_no' => $attributes['license_no'] ?? null,
                'license_expired_date' => $attributes['license_expired_date'] ?? null,
                'religion' => $attributes['religion'] ?? null,
                'blood_group' => $attributes['blood_group'] ?? null,
                'marital_status' => $attributes['marital_status'] ?? null,
                'reference_name' => $attributes['reference_name'] ?? null,
                'reference_contact_no' => $attributes['reference_contact_no'] ?? null,
                'reference_remark' => $attributes['reference_remark'] ?? null,
                'nominee_name' => $attributes['nominee_name'] ?? null,
                'nominee_photo' => file_uploaded($attributes['nominee_photo'] ?? null, 'employees'),
                'nominee_contact_no' => $attributes['nominee_contact_no'] ?? null,
                'nominee_nid_or_passport_no' => $attributes['nominee_nid_or_passport_no'] ?? null,
                'nominee_nid_or_passport_no_image' => file_uploaded($attributes['nominee_nid_or_passport_no_image'] ?? null, 'employees'),
                'nominee_relation' => $attributes['nominee_relation'] ?? null,
                'status' => $attributes['status'] ?? 1,
                'created_by' => auth()->id(),
            ]);

            if (! empty($attributes['academics'])) {
                $employee->academics()->createMany(
                    collect($attributes['academics'])->map(fn ($a) => [
                        'degree' => $a['degree'],
                        'field_of_study' => $a['field_of_study'] ?? null,
                        'institute' => $a['institute'],
                        'passing_year' => $a['passing_year'] ?? null,
                        'grade' => $a['grade'] ?? null,
                    ])->all()
                );
            }

            if (! empty($attributes['experiences'])) {
                $employee->experiences()->createMany(
                    collect($attributes['experiences'])->map(fn ($e) => [
                        'organization' => $e['organization'],
                        'position' => $e['position'],
                        'start_date' => $e['start_date'] ?? null,
                        'end_date' => $e['end_date'] ?? null,
                        'responsibility' => $e['responsibility'] ?? null,
                    ])->all()
                );
            }

            return $employee->load(['academics', 'experiences']);
        });
    }

    /**
     * Find an employee by ID (with academics and experiences) or throw ModelNotFoundException.
     *
     * @param  int  $id
     * @return Employee
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Employee
    {
        $employee = Employee::with(['academics', 'experiences'])->find($id);

        if (! $employee) {
            throw new ModelNotFoundException("Employee with ID {$id} not found.");
        }

        return $employee;
    }

    /**
     * Update the specified employee and replace their academics/experiences inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return Employee
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): Employee
    {
        return DB::transaction(function () use ($id, $attributes) {
            $employee = $this->findById($id);

            $employee->update([
                'name' => $attributes['name'],
                'contact_no' => $attributes['contact_no'],
                'email' => $attributes['email'] ?? null,
                'photo' => $this->resolveFilePath($attributes['photo'] ?? null, $employee->photo),
                'father_name' => $attributes['father_name'] ?? null,
                'mother_name' => $attributes['mother_name'] ?? null,
                'date_of_birth' => $attributes['date_of_birth'],
                'nid_or_passport_no' => $attributes['nid_or_passport_no'] ?? null,
                'nid_or_passport_no_image' => $this->resolveFilePath($attributes['nid_or_passport_no_image'] ?? null, $employee->nid_or_passport_no_image),
                'job_type' => $attributes['job_type'] ?? null,
                'duty_hour' => $attributes['duty_hour'] ?? null,
                'joining_date' => $attributes['joining_date'] ?? null,
                'present_address' => $attributes['present_address'] ?? null,
                'permanent_address' => $attributes['permanent_address'] ?? null,
                'district_id' => $attributes['district_id'] ?? null,
                'designation_id' => $attributes['designation_id'] ?? null,
                'license_category' => $attributes['license_category'] ?? null,
                'license_no' => $attributes['license_no'] ?? null,
                'license_expired_date' => $attributes['license_expired_date'] ?? null,
                'religion' => $attributes['religion'] ?? null,
                'blood_group' => $attributes['blood_group'] ?? null,
                'marital_status' => $attributes['marital_status'] ?? null,
                'reference_name' => $attributes['reference_name'] ?? null,
                'reference_contact_no' => $attributes['reference_contact_no'] ?? null,
                'reference_remark' => $attributes['reference_remark'] ?? null,
                'nominee_name' => $attributes['nominee_name'] ?? null,
                'nominee_photo' => $this->resolveFilePath($attributes['nominee_photo'] ?? null, $employee->nominee_photo),
                'nominee_contact_no' => $attributes['nominee_contact_no'] ?? null,
                'nominee_nid_or_passport_no' => $attributes['nominee_nid_or_passport_no'] ?? null,
                'nominee_nid_or_passport_no_image' => $this->resolveFilePath($attributes['nominee_nid_or_passport_no_image'] ?? null, $employee->nominee_nid_or_passport_no_image),
                'nominee_relation' => $attributes['nominee_relation'] ?? null,
                'status' => $attributes['status'] ?? $employee->status,
                'updated_by' => auth()->id(),
            ]);

            $employee->academics()->delete();
            $employee->experiences()->delete();

            if (! empty($attributes['academics'])) {
                $employee->academics()->createMany(
                    collect($attributes['academics'])->map(fn ($a) => [
                        'degree' => $a['degree'],
                        'field_of_study' => $a['field_of_study'] ?? null,
                        'institute' => $a['institute'],
                        'passing_year' => $a['passing_year'] ?? null,
                        'grade' => $a['grade'] ?? null,
                    ])->all()
                );
            }

            if (! empty($attributes['experiences'])) {
                $employee->experiences()->createMany(
                    collect($attributes['experiences'])->map(fn ($e) => [
                        'organization' => $e['organization'],
                        'position' => $e['position'],
                        'start_date' => $e['start_date'] ?? null,
                        'end_date' => $e['end_date'] ?? null,
                        'responsibility' => $e['responsibility'] ?? null,
                    ])->all()
                );
            }

            return $employee->load(['academics', 'experiences']);
        });
    }

    /**
     * Set the employee status to active (1).
     *
     * @param  int  $id
     * @return Employee
     *
     * @throws ModelNotFoundException
     */
    public function activeById(int $id): Employee
    {
        return DB::transaction(function () use ($id) {
            $employee = $this->findById($id);
            $employee->update(['status' => 1]);

            return $employee->fresh();
        });
    }

    /**
     * Set the employee status to inactive (0).
     *
     * @param  int  $id
     * @return Employee
     *
     * @throws ModelNotFoundException
     */
    public function inactiveById(int $id): Employee
    {
        return DB::transaction(function () use ($id) {
            $employee = $this->findById($id);
            $employee->update(['status' => 0]);

            return $employee->fresh();
        });
    }

    /**
     * Delete the employee's uploaded files, remove their relations, and soft-delete the record.
     *
     * @param  int  $id
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $employee = $this->findById($id);

            delete_uploaded_file($employee->photo);
            delete_uploaded_file($employee->nid_or_passport_no_image);
            delete_uploaded_file($employee->nominee_photo);
            delete_uploaded_file($employee->nominee_nid_or_passport_no_image);

            $employee->academics()->delete();
            $employee->experiences()->delete();
            $employee->delete();
        });
    }

    /**
     * Upload a new file and delete the old one, or return the existing path if no new file is given.
     *
     * @param  UploadedFile|null  $newFile
     * @param  string|null  $existingPath
     * @return string|null
     */
    private function resolveFilePath(?UploadedFile $newFile, ?string $existingPath): ?string
    {
        if (! $newFile) {
            return $existingPath;
        }

        $newPath = file_uploaded($newFile, 'employees');

        if ($newPath && $existingPath) {
            delete_uploaded_file($existingPath);
        }

        return $newPath ?? $existingPath;
    }
}
