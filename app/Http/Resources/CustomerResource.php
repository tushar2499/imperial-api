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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
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
