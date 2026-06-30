<?php

namespace App\Http\Requests\Api\TripInstance;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TripInstanceIndexRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'page' => ['nullable', 'integer', 'min:1'],
            'trip_date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'integer', 'in:0,1,2'],
            'coach_type' => ['nullable', 'integer', 'in:1,2'],
            'coach_id' => ['nullable', 'integer', 'exists:coaches,id'],
            'bus_id' => ['nullable', 'integer', 'exists:buses,id'],
            'schedule_id' => ['nullable', 'integer', 'exists:schedules,id'],
            'route_id' => ['nullable', 'integer', 'exists:transport_routes,id'],
            'driver_id' => ['nullable', 'integer', 'exists:employees,id'],
            'supervisor_id' => ['nullable', 'integer', 'exists:employees,id'],
            'today' => ['nullable', 'boolean'],
            'upcoming' => ['nullable', 'boolean'],
            'past' => ['nullable', 'boolean'],
            'sort_by' => ['nullable', 'string', 'in:trip_date,created_at,id'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to view trip instances.')
        );
    }
}
