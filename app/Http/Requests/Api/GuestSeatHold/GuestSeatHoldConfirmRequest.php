<?php

namespace App\Http\Requests\Api\GuestSeatHold;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuestSeatHoldConfirmRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'issue_id'    => ['required', 'string', 'max:100'],
            'guest_token' => ['required', 'string', 'max:64'],
            'boarding_id' => ['required', 'integer'],
            'dropping_id' => ['required', 'integer'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You must be logged in to confirm your booking.')
        );
    }
}
