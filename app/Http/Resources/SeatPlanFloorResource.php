<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class SeatPlanFloorResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'seat_plan_id' => $this->seat_plan_id,
            'name' => $this->name,
            'layout_type' => $this->layout_type,
            'rows' => $this->rows,
            'cols' => $this->cols,
            'step' => $this->step,
            'is_extra_seat' => (int) $this->is_extra_seat,
            'seats' => SeatResource::collection($this->whenLoaded('seats')),
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
