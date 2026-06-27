<?php

namespace App\Http\Requests\Api\TransportRoute;

use App\Models\TransportRoute;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class TransportRouteUpdateRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'start_id' => ['required', 'integer', 'exists:districts,id'],
            'end_id' => ['required', 'integer', 'exists:districts,id', 'different:start_id'],
            'distance' => ['required', 'numeric'],
            'duration_hours' => ['required', 'integer', 'min:0', 'max:99'],
            'duration_minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'is_popular' => ['nullable', 'boolean'],
            'station_ids' => ['nullable', 'array'],
            'station_ids.*' => ['integer', 'exists:districts,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $duplicate = TransportRoute::query()
                ->where('start_id', $this->input('start_id'))
                ->where('end_id', $this->input('end_id'))
                ->where('id', '!=', $this->route('id'))
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('end_id', 'A route with this start and end location already exists.');
            }
        });
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to perform this action.')
        );
    }
}
