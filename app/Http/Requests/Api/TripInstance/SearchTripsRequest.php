<?php

namespace App\Http\Requests\Api\TripInstance;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchTripsRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'trip_date' => ['required', 'date', 'date_format:Y-m-d'],
            'route_start_id' => ['required', 'integer', 'exists:districts,id'],
            'route_end_id' => ['required', 'integer', 'exists:districts,id'],
            'coach_no' => ['sometimes', 'nullable', 'string', 'max:50'],
            'schedule_id' => ['sometimes', 'nullable', 'integer', 'exists:schedules,id'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
            'coach_type' => ['sometimes', 'nullable', 'integer', 'in:1,2'],
            'boarding_counter_id' => ['sometimes', 'nullable', 'integer', 'exists:counters,id'],
            'dropping_counter_id' => ['sometimes', 'nullable', 'integer', 'exists:counters,id'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to search trips.')
        );
    }
}
