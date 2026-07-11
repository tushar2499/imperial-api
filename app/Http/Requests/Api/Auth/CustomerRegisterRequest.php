<?php

namespace App\Http\Requests\Api\Auth;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class CustomerRegisterRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'unique:customers,mobile'],
            'email' => ['nullable', 'email', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }
}
