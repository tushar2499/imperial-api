<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $dateFormat = system_setting('date_format', 'd-m-Y');
        $timeFormat = system_setting('time_format', 'h:i A');

        return [
            'id' => $this->id,
            'mobile' => $this->mobile,
            'name' => $this->name,
            'gender' => $this->gender,
            'age' => $this->age,
            'address' => $this->address,
            'passport_no' => $this->passport_no,
            'nid' => $this->nid,
            'nationality' => $this->nationality,
            'email' => $this->email,
            'total_trips' => $this->total_trips,
            'total_tickets' => $this->total_tickets,
            'total_cancelled_tickets' => $this->total_cancelled_tickets,
            'status' => $this->status,
            'created_at' => $this->created_at ? date($dateFormat.' '.$timeFormat, strtotime($this->created_at)) : null,
            'updated_at' => $this->updated_at ? date($dateFormat.' '.$timeFormat, strtotime($this->updated_at)) : null,
        ];
    }

    public static function collection($resource): mixed
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
