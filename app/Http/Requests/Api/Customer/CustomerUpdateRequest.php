<?php

namespace App\Http\Requests\Api\Customer;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomerUpdateRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'mobile' => ['required', 'string', 'max:255', "unique:customers,mobile,{$id}"],
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
            $this->forbiddenResponse('You do not have permission to update this customer.')
        );
    }
}
