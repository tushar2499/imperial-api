<?php

namespace App\Http\Requests\Api\Fare;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FareStoreRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'route_id' => ['required', 'integer', 'exists:transport_routes,id'],
            'seat_plan_id' => ['required', 'integer', 'exists:seat_plans,id'],
            'coach_type' => ['required', 'integer', 'in:1,2'],
            'seat_types' => ['required', 'array', 'min:1'],
            'seat_types.*' => ['required', 'numeric', 'min:0'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'status' => ['nullable', 'integer', 'in:0,1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $validSeatTypes = ['Suite Class', 'Business Class', 'Sleeper', 'Economy'];
            $invalidKeys = array_diff(array_keys($this->input('seat_types', [])), $validSeatTypes);

            if (! empty($invalidKeys)) {
                $validator->errors()->add(
                    'seat_types',
                    'Invalid seat types: '.implode(', ', $invalidKeys).'. Allowed: '.implode(', ', $validSeatTypes)
                );
            }
        });
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to create a fare.')
        );
    }
}
