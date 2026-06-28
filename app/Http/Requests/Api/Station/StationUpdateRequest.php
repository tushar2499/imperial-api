<?php

namespace App\Http\Requests\Api\Station;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StationUpdateRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'transport_route_id' => ['required', 'integer', 'exists:transport_routes,id'],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to update this station.')
        );
    }
}
