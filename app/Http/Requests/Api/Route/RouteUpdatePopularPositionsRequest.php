<?php

namespace App\Http\Requests\Api\Route;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RouteUpdatePopularPositionsRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'popular_positions' => ['required', 'array'],
            'popular_positions.*.id' => ['required', 'integer', 'distinct', 'exists:routes,id'],
            'popular_positions.*.popular_position' => ['required', 'integer', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'popular_positions.*.id.required' => 'Route ID is required.',
            'popular_positions.*.id.exists' => 'Route not found.',
            'popular_positions.*.id.integer' => 'Route ID must be an integer.',
            'popular_positions.*.id.distinct' => 'Route ID must be unique.',
            'popular_positions.*.popular_position.required' => 'Popular position is required.',
            'popular_positions.*.popular_position.integer' => 'Popular position must be an integer.',
            'popular_positions.*.popular_position.distinct' => 'Popular position must be unique.',
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to perform this action.')
        );
    }
}
