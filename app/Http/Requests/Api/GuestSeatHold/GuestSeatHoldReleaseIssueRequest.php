<?php

namespace App\Http\Requests\Api\GuestSeatHold;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class GuestSeatHoldReleaseIssueRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true; // public — guest_token is the ownership proof
    }

    public function rules(): array
    {
        return [
            'issue_id'    => ['required', 'string', 'max:100'],
            'guest_token' => ['required', 'string', 'max:64'],
        ];
    }
}
