<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class CoachConfigurationResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $dateFormat = system_setting('date_format', 'd-m-Y');
        $timeFormat = system_setting('time_format', 'h:i A');

        return [
            'id' => $this->id,
            'coach_id' => $this->coach_id,
            'schedule_id' => $this->schedule_id,
            'bus_id' => $this->bus_id,
            'seat_plan_id' => $this->seat_plan_id,
            'transport_route_id' => $this->transport_route_id,
            'coach_type' => $this->coach_type,
            'status' => $this->status,
            'coach' => $this->whenLoaded('coach', fn () => new CoachResource($this->coach)),
            'schedule' => $this->whenLoaded('schedule', fn () => new ScheduleResource($this->schedule)),
            'bus' => $this->whenLoaded('bus', fn () => new BusResource($this->bus)),
            'seat_plan' => $this->whenLoaded('seatPlan', fn () => new SeatPlanResource($this->seatPlan)),
            'total_seat' => $this->whenLoaded('seatPlan', fn () => $this->seatPlan?->seats_count),
            'transport_route' => $this->whenLoaded('transportRoute', fn () => new TransportRouteResource($this->transportRoute)),
            'boarding_droppings' => $this->whenLoaded('boardingDroppings', fn () => CoachBoardingDroppingResource::collection($this->boardingDroppings)),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
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
