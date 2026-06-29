<?php

namespace App\Http\Requests\Api\CoachConfiguration;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CoachConfigurationStoreRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'coach_id' => ['required', 'integer', 'exists:coaches,id'],
            'schedule_id' => ['required', 'integer', 'exists:schedules,id'],
            'bus_id' => ['required', 'integer', 'exists:buses,id'],
            'transport_route_id' => ['required', 'integer', 'exists:transport_routes,id'],
            'status' => ['nullable', 'integer', 'in:0,1'],
            'boarding_dropping_points' => ['required', 'array', 'min:1'],
            'boarding_dropping_points.*.counter_id' => ['required', 'integer', 'exists:counters,id'],
            'boarding_dropping_points.*.type' => ['required', 'integer', 'in:1,2'],
            'boarding_dropping_points.*.time' => ['required', 'date_format:H:i'],
            'boarding_dropping_points.*.starting_point_status' => ['nullable', 'boolean'],
            'boarding_dropping_points.*.ending_point_status' => ['nullable', 'boolean'],
            'boarding_dropping_points.*.status' => ['nullable', 'integer', 'in:0,1'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to create a coach configuration.')
        );
    }
}
