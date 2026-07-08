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
        $dateFormat = system_setting('date_format', 'd-m-Y');
        $timeFormat = system_setting('time_format', 'h:i A');

        return [
            'id' => $this->id,
            'start_id' => $this->start_id,
            'end_id' => $this->end_id,
            'start_name' => $this->whenLoaded('startDistrict', fn () => $this->startDistrict->name),
            'end_name' => $this->whenLoaded('endDistrict', fn () => $this->endDistrict->name),
            'distance' => $this->distance,
            'duration_hours' => (int) floor($this->duration / 60),
            'duration_minutes' => $this->duration % 60,
            'is_popular' => $this->is_popular,
            'popular_position' => $this->popular_position,
            'status' => $this->status,
            'stations' => $this->whenLoaded('stations', fn () => $this->stations->map(fn ($station) => [
                'id' => $station->id,
                'transport_route_id' => $station->transport_route_id,
                'district_id' => $station->district_id,
                'district_name' => $station->relationLoaded('district') ? $station->district?->name : null,
                'status' => $station->status,
                'created_at' => $station->created_at ? date($dateFormat.' '.$timeFormat, strtotime($station->created_at)) : null,
                'updated_at' => $station->updated_at ? date($dateFormat.' '.$timeFormat, strtotime($station->updated_at)) : null,
            ])),
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
