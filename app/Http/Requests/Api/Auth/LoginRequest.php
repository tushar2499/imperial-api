<?php

namespace App\Http\Requests\Api\Auth;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_name' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }
}
