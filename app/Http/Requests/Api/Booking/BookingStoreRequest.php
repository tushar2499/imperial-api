<?php

namespace App\Http\Requests\Api\Booking;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BookingStoreRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'numeric'],
            'address' => ['nullable', 'string', 'max:255'],
            'passport_no' => ['nullable', 'string', 'max:255'],
            'nid' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],

            'trip_id' => ['required', 'integer'],
            'type' => ['required', 'integer'],
            'date' => ['required', 'date', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i:s'],
            'route_id' => ['required', 'integer', 'exists:transport_routes,id'],
            'boarding_id' => ['nullable', 'integer', 'exists:trip_boarding_droppings,id'],
            'dropping_id' => ['nullable', 'integer', 'exists:trip_boarding_droppings,id'],
            'expire_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'expire_time' => ['nullable', 'date_format:H:i'],

            'booking_details' => ['required', 'array'],
            'booking_details.*.seat_inventory_id' => ['required', 'integer'],
            'booking_details.*.seat_id' => ['required', 'integer', 'exists:seats,id'],
            'booking_details.*.price' => ['required', 'numeric'],
            'booking_details.*.discount' => ['nullable', 'numeric'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to create a booking.')
        );
    }
}
