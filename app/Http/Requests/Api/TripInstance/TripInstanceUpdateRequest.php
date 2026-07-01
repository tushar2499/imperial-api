<?php

namespace App\Http\Requests\Api\TripInstance;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TripInstanceUpdateRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'coach_configuration_id' => ['nullable', 'exists:coach_configurations,id'],
            'schedule_id' => ['sometimes', 'exists:schedules,id'],
            'transport_route_id' => ['sometimes', 'exists:transport_routes,id'],
            'driver_id' => ['nullable', 'exists:employees,id'],
            'supervisor_id' => ['nullable', 'exists:employees,id'],
            'trip_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'integer', 'in:0,1,2'],
            'migrated_trip_id' => ['nullable', 'integer'],
            'auto_create_seat_inventory' => ['sometimes', 'boolean'],
            'boarding_dropping_points' => ['sometimes', 'array', 'min:1'],
            'boarding_dropping_points.*.counter_id' => ['required', 'exists:counters,id'],
            'boarding_dropping_points.*.type' => ['required', 'integer', 'in:1,2'],
            'boarding_dropping_points.*.time' => ['required', 'date_format:H:i'],
            'boarding_dropping_points.*.starting_point_status' => ['sometimes', 'boolean'],
            'boarding_dropping_points.*.ending_point_status' => ['sometimes', 'boolean'],
            'boarding_dropping_points.*.status' => ['sometimes', 'integer', 'in:0,1'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to update this trip instance.')
        );
    }
}
