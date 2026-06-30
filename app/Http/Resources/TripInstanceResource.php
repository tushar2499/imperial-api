<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class TripInstanceResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'coach_id' => $this->coach_id,
            'bus_id' => $this->bus_id,
            'schedule_id' => $this->schedule_id,
            'seat_plan_id' => $this->seat_plan_id,
            'route_id' => $this->route_id,
            'coach_type' => $this->coach_type,
            'driver_id' => $this->driver_id,
            'supervisor_id' => $this->supervisor_id,
            'trip_date' => $this->trip_date,
            'status' => $this->status,
            'migrated_trip_id' => $this->migrated_trip_id,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'migrated_by' => $this->migrated_by,
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
