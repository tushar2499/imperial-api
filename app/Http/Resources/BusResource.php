<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class BusResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'registration_number' => $this->registration_number,
            'manufacturer_company' => $this->manufacturer_company,
            'model_year' => $this->model_year,
            'chasis_no' => $this->chasis_no,
            'engine_number' => $this->engine_number,
            'country_of_origin' => $this->country_of_origin,
            'lc_code_number' => $this->lc_code_number,
            'delivery_to_dipo' => $this->delivery_to_dipo,
            'delivery_date' => $this->delivery_date,
            'color' => $this->color,
            'financed_by' => $this->financed_by,
            'tennure_of_the_terms' => $this->tennure_of_the_terms,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
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
