<?php

namespace App\Http\Requests\Api\Auth;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomerUpdateProfileRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth('customer')->check();
    }

    public function rules(): array
    {
        $customerId = auth('customer')->id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:30'],
            'age' => ['nullable', 'integer', 'min:1', 'max:120'],
            'address' => ['nullable', 'string', 'max:500'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'passport_no' => ['nullable', 'string', 'max:100'],
            'nid' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to perform this action.')
        );
    }
}
