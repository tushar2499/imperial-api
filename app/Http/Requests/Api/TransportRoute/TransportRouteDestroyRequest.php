<?php

namespace App\Http\Requests\Api\TransportRoute;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransportRouteDestroyRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to perform this action.')
        );
    }
}
