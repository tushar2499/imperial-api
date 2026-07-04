<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingDetailResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'seat_inventory_id' => $this->seat_inventory_id,
            'seat_id' => $this->seat_id,
            'price' => $this->price,
            'discount' => $this->discount,
            'amount' => $this->amount,
            'seat' => $this->whenLoaded('seat', fn () => [
                'id' => $this->seat->id,
                'seat_number' => $this->seat->seat_number,
                'row_position' => $this->seat->row_position,
                'col_position' => $this->seat->col_position,
                'seat_type' => $this->seat->seat_type,
            ]),
        ];
    }
}
