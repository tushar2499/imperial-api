<?php

namespace App\Http\Requests\Api\AdminUser;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AdminUserStoreRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'user_name' => ['required', 'string', 'max:255', 'unique:users,user_name'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'type' => ['required', 'integer', 'in:1,2'],
            'password' => ['nullable', 'string', 'min:6'],
            'gender' => ['nullable', 'string', 'max:30'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'date_of_birth' => ['nullable', 'date', 'date_format:Y-m-d'],
            'role' => ['nullable', 'string', 'max:30'],
            'counter_id' => ['nullable', 'required_if:type,2', 'exists:counters,id'],
            'access_type' => ['required', 'integer', 'in:1,2,3'],
            'account_type' => ['nullable', 'integer', 'in:1,2'],
            'front_date' => ['nullable', 'numeric'],
            'back_date' => ['nullable', 'numeric'],
            'identity_type' => ['nullable', 'string', 'max:30'],
            'identity_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'sales_status' => ['nullable', 'integer', 'in:0,1'],
            'booking_status' => ['nullable', 'integer', 'in:0,1'],
            'block_status' => ['nullable', 'integer', 'in:0,1'],
            'cancel_status' => ['nullable', 'integer', 'in:0,1'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to perform this action.')
        );
    }
}
