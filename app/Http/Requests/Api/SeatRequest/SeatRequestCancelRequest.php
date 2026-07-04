<?php

namespace App\Http\Requests\Api\SeatRequest;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SeatRequestCancelRequest extends FormRequest
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
            'issue_id' => ['required', 'string', 'max:100'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to cancel a seat request.')
        );
    }
}
