<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class SeatPlanResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'floor' => $this->floor,
            'status' => $this->status,
            'floors' => $this->when(
                $this->relationLoaded('floors'),
                fn () => $this->floors->map(fn ($floor) => [
                    'id' => $floor->id,
                    'name' => $floor->name,
                    'layout_type' => $floor->layout_type,
                    'rows' => $floor->rows,
                    'cols' => $floor->cols,
                    'step' => $floor->step,
                    'is_extra_seat' => $floor->is_extra_seat,
                    'seats' => $floor->relationLoaded('seats')
                        ? $floor->seats->map(fn ($seat) => [
                            'id' => $seat->id,
                            'seat_number' => $seat->seat_number,
                            'row_position' => $seat->row_position,
                            'col_position' => $seat->col_position,
                            'seat_type' => $seat->seat_type,
                            'is_disable' => $seat->is_disable,
                            'status' => $seat->status,
                        ])->values()
                        : null,
                ])->values()
            ),
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
