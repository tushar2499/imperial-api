<?php

namespace App\Http\Resources;

use App\Helpers\TripHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class TripInstanceResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $dateFormat = system_setting('date_format', 'd-m-Y');
        $timeFormat = system_setting('time_format', 'h:i A');

        return [
            'id' => $this->id,
            'coach_id' => $this->coach_id,
            'bus_id' => $this->bus_id,
            'schedule_id' => $this->schedule_id,
            'seat_plan_id' => $this->seat_plan_id,
            'route_id' => $this->route_id,
            'coach_type' => $this->coach_type,
            'coach_type_name' => TripHelper::getCoachTypeName($this->coach_type),
            'driver_id' => $this->driver_id,
            'supervisor_id' => $this->supervisor_id,
            'trip_date' => $this->trip_date ? date($dateFormat, strtotime($this->trip_date)) : null,
            'trip_date_formatted' => $this->trip_date ? Carbon::parse($this->trip_date)->format('l, F j, Y') : null,
            'status' => $this->status,
            'status_name' => TripHelper::getStatusName($this->status),
            'is_ac' => $this->coach_type == 1,
            'is_active' => $this->status == 1,
            'is_migrated' => $this->status == 2,
            'migrated_trip_id' => $this->migrated_trip_id,
            'total_seats' => TripHelper::getTotalSeats($this->seat_plan_id),
            'seat_inventory_summary' => TripHelper::getSeatInventorySummary($this->resource),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'migrated_by' => $this->migrated_by,
            'created_at' => $this->created_at ? date($dateFormat.' '.$timeFormat, strtotime($this->created_at)) : null,
            'updated_at' => $this->updated_at ? date($dateFormat.' '.$timeFormat, strtotime($this->updated_at)) : null,
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
                'time' => $bp->time ? date($timeFormat, strtotime($bp->time)) : null,
                'starting_point_status' => (int) $bp->starting_point_status,
                'ending_point_status' => (int) $bp->ending_point_status,
                'status' => $bp->status,
                'counter' => $bp->relationLoaded('counter') ? new CounterResource($bp->counter) : null,
            ])),
            'seat_inventory' => $this->when($this->seat_inventory !== null, fn () => $this->seat_inventory),
            'fares_info' => $this->when($this->fares_info !== null, fn () => $this->fares_info),
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
