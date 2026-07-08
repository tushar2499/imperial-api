<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachBoardingDroppingResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $dateFormat = system_setting('date_format', 'd-m-Y');
        $timeFormat = system_setting('time_format', 'h:i A');

        return [
            'id' => $this->id,
            'coach_configuration_id' => $this->coach_configuration_id,
            'counter_id' => $this->counter_id,
            'type' => $this->type,
            'time' => $this->time ? $this->time->format($timeFormat) : null,
            'starting_point_status' => (int) $this->starting_point_status,
            'ending_point_status' => (int) $this->ending_point_status,
            'status' => $this->status,
            'counter' => $this->whenLoaded('counter', fn () => new CounterResource($this->counter)),
            'created_at' => $this->created_at ? date($dateFormat.' '.$timeFormat, strtotime($this->created_at)) : null,
            'updated_at' => $this->updated_at ? date($dateFormat.' '.$timeFormat, strtotime($this->updated_at)) : null,
        ];
    }
}
