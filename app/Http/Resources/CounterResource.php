<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class CounterResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'type'                   => $this->type,
            'address'                => $this->address,
            'land_mark'              => $this->land_mark,
            'location_url'           => $this->location_url,
            'phone'                  => $this->phone,
            'mobile'                 => $this->mobile,
            'email'                  => $this->email,
            'primary_contact_no'     => $this->primary_contact_no,
            'country'                => $this->country,
            'district_id'            => $this->district_id,
            'booking_allowed_status' => $this->booking_allowed_status,
            'booking_allowed_class'  => $this->booking_allowed_class,
            'no_of_boarding_allowed' => $this->no_of_boarding_allowed,
            'sms_status'             => $this->sms_status,
            'status'                 => $this->status,
            'created_by'             => $this->created_by,
            'updated_by'             => $this->updated_by,
            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,
            'district'               => new DistrictResource($this->whenLoaded('district')),
        ];
    }

    /**
     * Create a resource collection.
     */
    public static function collection($resource)
    {
        $collection = parent::collection($resource);

        if ($resource instanceof LengthAwarePaginator) {
            return $collection->additional([
                'current_page' => $resource->currentPage(),
                'per_page'     => $resource->perPage(),
                'total'        => $resource->total(),
                'last_page'    => $resource->lastPage(),
            ]);
        }

        return $collection;
    }
}
