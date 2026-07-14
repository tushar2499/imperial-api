<?php

namespace App\Http\Requests\Api\GuestSeatHold;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class GuestSeatHoldRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true; // public — no auth required
    }

    public function rules(): array
    {
        return [
            'seat_inventory_id' => ['required', 'integer'],
            'trip_id'           => ['required', 'integer'],
            'guest_token'       => ['required', 'string', 'max:64'],
            'issue_id'          => ['sometimes', 'nullable', 'string', 'max:100'],
            'notes'             => ['sometimes', 'string', 'max:500'],
        ];
    }
}
