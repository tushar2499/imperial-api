<?php

namespace App\Http\Requests\Api\SeatRequest;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SeatRequestStoreRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'seat_inventory_id' => ['required', 'integer'],
            'trip_id' => ['required', 'integer'],
            'issue_id' => ['sometimes', 'string', 'max:100'],
            'notes' => ['sometimes', 'string', 'max:500'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to create a seat request.')
        );
    }
}
