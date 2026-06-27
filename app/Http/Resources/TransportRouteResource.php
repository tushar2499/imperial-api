<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class TransportRouteResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'start_id' => $this->start_id,
            'end_id' => $this->end_id,
            'start_name' => $this->whenLoaded('startDistrict', fn() => $this->startDistrict->name),
            'end_name' => $this->whenLoaded('endDistrict', fn() => $this->endDistrict->name),
            'distance' => $this->distance,
            'duration' => $this->duration,
            'duration_hours' => (int) floor($this->duration / 60),
            'duration_minutes' => $this->duration % 60,
            'is_popular' => $this->is_popular,
            'popular_position' => $this->popular_position,
            'status' => $this->status,
            'stations' => $this->whenLoaded('stations', fn () => $this->stations->map(fn ($station) => [
                'id' => $station->id,
                'route_id' => $station->route_id,
                'district_id' => $station->district_id,
                'district_name' => $station->relationLoaded('district') ? $station->district?->name : null,
                'status' => $station->status,
                'created_at' => $station->created_at,
                'updated_at' => $station->updated_at,
            ])),
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
