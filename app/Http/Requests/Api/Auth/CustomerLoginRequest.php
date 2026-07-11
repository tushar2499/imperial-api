<?php

namespace App\Http\Requests\Api\Auth;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class CustomerLoginRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required_without:email', 'nullable', 'string'],
            'email' => ['required_without:mobile', 'nullable', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }
}
