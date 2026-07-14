<?php

namespace App\Http\Requests\Api\GuestSeatHold;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuestSeatHoldClaimRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check(); // must be authenticated
    }

    public function rules(): array
    {
        return [
            'issue_id'    => ['required', 'string', 'max:100'],
            'guest_token' => ['required', 'string', 'max:64'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You must be logged in to claim your seat hold.')
        );
    }
}
