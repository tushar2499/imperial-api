<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $dateFormat = system_setting('date_format', 'd-m-Y');
        $timeFormat = system_setting('time_format', 'h:i A');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact_no' => $this->contact_no,
            'email' => $this->email,
            'photo' => $this->photo ? asset($this->photo) : null,
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'date_of_birth' => $this->date_of_birth ? date($dateFormat, strtotime($this->date_of_birth)) : null,
            'job_type' => $this->job_type,
            'nid_or_passport_no' => $this->nid_or_passport_no,
            'nid_or_passport_no_image' => $this->nid_or_passport_no_image ? asset($this->nid_or_passport_no_image) : null,
            'duty_hour' => $this->duty_hour,
            'joining_date' => $this->joining_date ? date($dateFormat, strtotime($this->joining_date)) : null,
            'present_address' => $this->present_address,
            'permanent_address' => $this->permanent_address,
            'district_id' => $this->district_id,
            'designation_id' => $this->designation_id,
            'license_category' => $this->license_category,
            'license_no' => $this->license_no,
            'license_expired_date' => $this->license_expired_date ? date($dateFormat, strtotime($this->license_expired_date)) : null,
            'religion' => $this->religion,
            'blood_group' => $this->blood_group,
            'marital_status' => $this->marital_status,
            'reference_name' => $this->reference_name,
            'reference_contact_no' => $this->reference_contact_no,
            'reference_remark' => $this->reference_remark,
            'nominee_name' => $this->nominee_name,
            'nominee_photo' => $this->nominee_photo ? asset($this->nominee_photo) : null,
            'nominee_contact_no' => $this->nominee_contact_no,
            'nominee_nid_or_passport_no' => $this->nominee_nid_or_passport_no,
            'nominee_nid_or_passport_no_image' => $this->nominee_nid_or_passport_no_image ? asset($this->nominee_nid_or_passport_no_image) : null,
            'nominee_relation' => $this->nominee_relation,
            'status' => $this->status,
            'academics' => $this->whenLoaded('academics', fn () => $this->academics->map(fn ($a) => [
                'id' => $a->id,
                'degree' => $a->degree,
                'field_of_study' => $a->field_of_study,
                'institute' => $a->institute,
                'passing_year' => $a->passing_year,
                'grade' => $a->grade,
            ])),
            'experiences' => $this->whenLoaded('experiences', fn () => $this->experiences->map(fn ($e) => [
                'id' => $e->id,
                'organization' => $e->organization,
                'position' => $e->position,
                'start_date' => $e->start_date ? date($dateFormat, strtotime($e->start_date)) : null,
                'end_date' => $e->end_date ? date($dateFormat, strtotime($e->end_date)) : null,
                'responsibility' => $e->responsibility,
            ])),
            'created_at' => $this->created_at ? date($dateFormat.' '.$timeFormat, strtotime($this->created_at)) : null,
            'updated_at' => $this->updated_at ? date($dateFormat.' '.$timeFormat, strtotime($this->updated_at)) : null,
        ];
    }

    /**
     * Create a new resource collection.
     *
     * @param  mixed  $resource
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public static function collection($resource)
    {
        $collection = parent::collection($resource);

        if ($resource instanceof LengthAwarePaginator) {
            return $collection->additional([
                'current_page' => $resource->currentPage(),
                'per_page' => $resource->perPage(),
                'total' => $resource->total(),
                'last_page' => $resource->lastPage(),
            ]);
        }

        return $collection;
    }
}
