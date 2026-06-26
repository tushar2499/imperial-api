<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class FareResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'route_id' => $this->route_id,
            'start_name' => $this->whenLoaded('route', fn () => $this->route->startDistrict?->name),
            'end_name' => $this->whenLoaded('route', fn () => $this->route->endDistrict?->name),
            'distance' => $this->whenLoaded('route', fn () => $this->route->distance),
            'duration' => $this->whenLoaded('route', fn () => $this->route->duration),
            'route_status' => $this->whenLoaded('route', fn () => $this->route->status),
            'seat_plan_id' => $this->seat_plan_id,
            'seat_plan_name' => $this->whenLoaded('seatPlan', fn () => $this->seatPlan->name),
            'coach_type' => $this->coach_type,
            'seat_type' => $this->seat_type,
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
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
