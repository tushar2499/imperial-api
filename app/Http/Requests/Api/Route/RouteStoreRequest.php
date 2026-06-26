<?php

namespace App\Http\Requests\Api\Route;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RouteStoreRequest extends FormRequest
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
            'end_id' => ['required', 'integer', 'exists:districts,id'],
            'distance' => ['required', 'numeric'],
            'duration' => ['required', 'string'],
            'is_popular' => ['nullable', 'boolean'],
            'station_ids' => ['nullable', 'array'],
            'station_ids.*' => ['integer', 'exists:districts,id'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to perform this action.')
        );
    }
}
