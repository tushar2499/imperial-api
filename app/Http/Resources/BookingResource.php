<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $dateFormat = system_setting('date_format', 'd-m-Y');
        $timeFormat = system_setting('time_format', 'h:i A');

        return [
            'id' => $this->id,
            'pnr_number' => $this->pnr_number,
            'trip_id' => $this->trip_id,
            'type' => $this->type,
            'date' => $this->date ? date($dateFormat, strtotime($this->date)) : null,
            'time' => $this->time ? date($timeFormat, strtotime($this->time)) : null,
            'note' => $this->note,
            'route_id' => $this->route_id,
            'boarding_id' => $this->boarding_id,
            'dropping_id' => $this->dropping_id,
            'total_price' => $this->total_price,
            'total_discount' => $this->total_discount,
            'total_amount' => $this->total_amount,
            'expire_date' => $this->expire_date ? date($dateFormat, strtotime($this->expire_date)) : null,
            'expire_time' => $this->expire_time ? date($timeFormat, strtotime($this->expire_time)) : null,
            'created_at' => $this->created_at ? date($dateFormat.' '.$timeFormat, strtotime($this->created_at)) : null,
            'updated_at' => $this->updated_at ? date($dateFormat.' '.$timeFormat, strtotime($this->updated_at)) : null,
            'customer' => $this->whenLoaded('customer', fn () => new CustomerResource($this->customer)),
            'boarding_info' => $this->whenLoaded('boarding', fn () => $this->boarding
                ? $this->formatBoardingDropping($this->boarding)
                : null),
            'dropping_info' => $this->whenLoaded('dropping', fn () => $this->dropping
                ? $this->formatBoardingDropping($this->dropping)
                : null),
            'trip_details' => $this->whenLoaded('trip', fn () => $this->trip
                ? new TripInstanceResource($this->trip)
                : null),
            'booking_details' => $this->whenLoaded('bookingDetails', fn () => BookingDetailResource::collection($this->bookingDetails)),
        ];
    }

    public static function collection($resource): mixed
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

    private function formatBoardingDropping($point): array
    {
        return [
            'id' => $point->id,
            'trip_id' => $point->trip_id,
            'time' => $point->time,
            'counter_id' => $point->counter_id,
            'counter_name' => $point->counter?->address,
            'counter_location' => $point->counter?->land_mark,
            'district_name' => $point->counter?->district?->name,
        ];
    }
}
