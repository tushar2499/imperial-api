<?php

namespace App\Http\Requests\Api\Customer;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomerStoreRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'max:255', 'unique:customers,mobile'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:0'],
            'address' => ['nullable', 'string', 'max:255'],
            'passport_no' => ['nullable', 'string', 'max:255'],
            'nid' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'integer', 'in:0,1'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to create a customer.')
        );
    }
}
