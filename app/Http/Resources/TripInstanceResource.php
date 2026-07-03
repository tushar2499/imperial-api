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
            'coach' => $this->whenLoaded('coach', fn () => new CoachResource($this->coach)),
            'bus' => $this->whenLoaded('bus', fn () => new BusResource($this->bus)),
            'schedule' => $this->whenLoaded('schedule', fn () => new ScheduleResource($this->schedule)),
            'seat_plan' => $this->whenLoaded('seatPlan', fn () => new SeatPlanResource($this->seatPlan)),
            'route' => $this->whenLoaded('route', fn () => new TransportRouteResource($this->route)),
            'driver' => $this->whenLoaded('driver', fn () => new EmployeeResource($this->driver)),
            'supervisor' => $this->whenLoaded('supervisor', fn () => new EmployeeResource($this->supervisor)),
            'migrated_trip' => $this->whenLoaded('migratedTrip', fn () => $this->migratedTrip ? new self($this->migratedTrip) : null),
            'boarding_droppings' => $this->whenLoaded('boardingDroppings', fn () => $this->boardingDroppings->map(fn ($bp) => [
                'id' => $bp->id,
                'counter_id' => $bp->counter_id,
                'type' => $bp->type,
                'time' => $bp->time,
                'starting_point_status' => (int) $bp->starting_point_status,
                'ending_point_status' => (int) $bp->ending_point_status,
                'status' => $bp->status,
                'counter' => $bp->relationLoaded('counter') ? new CounterResource($bp->counter) : null,
            ])),
            'fares' => FareResource::collection($this->whenLoaded('fares')),
            'seat_inventory' => $this->when($this->seat_inventory !== null, fn () => $this->seat_inventory),
            'fares_info' => $this->when($this->fares_info !== null, fn () => $this->fares_info),
            'partition_info' => $this->when($this->partition_info !== null, fn () => $this->partition_info),
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
