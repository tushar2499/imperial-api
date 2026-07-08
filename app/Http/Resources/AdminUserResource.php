<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminUserResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $dateFormat = system_setting('date_format', 'd-m-Y');
        $timeFormat = system_setting('time_format', 'h:i A');

        return [
            'id' => $this->id,
            'user_name' => $this->user_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'photo' => $this->photo ? asset($this->photo) : null,
            'type' => $this->type,
            'access_type' => $this->access_type,
            'account_type' => $this->account_type,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth ? date($dateFormat, strtotime($this->date_of_birth)) : null,
            'role' => $this->role,
            'counter_id' => $this->counter_id,
            'district_id' => $this->district_id,
            'identity_type' => $this->identity_type,
            'identity_image' => $this->identity_image ? asset($this->identity_image) : null,
            'front_date' => $this->front_date ? date($dateFormat, strtotime($this->front_date)) : null,
            'back_date' => $this->back_date ? date($dateFormat, strtotime($this->back_date)) : null,
            'status' => $this->status,
            'sales_status' => $this->sales_status,
            'booking_status' => $this->booking_status,
            'block_status' => $this->block_status,
            'cancel_status' => $this->cancel_status,
            'created_at' => $this->created_at ? date($dateFormat.' '.$timeFormat, strtotime($this->created_at)) : null,
            'updated_at' => $this->updated_at ? date($dateFormat.' '.$timeFormat, strtotime($this->updated_at)) : null,
        ];
    }

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
