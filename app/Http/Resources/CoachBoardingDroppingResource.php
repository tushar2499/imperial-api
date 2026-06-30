<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachBoardingDroppingResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'coach_configuration_id' => $this->coach_configuration_id,
            'counter_id' => $this->counter_id,
            'type' => $this->type,
            'time' => $this->time ? $this->time->format('H:i') : null,
            'starting_point_status' => (int) $this->starting_point_status,
            'ending_point_status' => (int) $this->ending_point_status,
            'status' => $this->status,
            'counter' => $this->whenLoaded('counter', fn () => new CounterResource($this->counter)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
