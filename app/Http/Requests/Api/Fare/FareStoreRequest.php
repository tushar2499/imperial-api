<?php

namespace App\Http\Requests\Api\Fare;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FareStoreRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'route_id' => ['required', 'integer', 'exists:routes,id'],
            'seat_plan_id' => ['required', 'integer', 'exists:seat_plans,id'],
            'coach_type' => ['required', 'integer', 'in:1,2'],
            'seat_type' => ['required', 'string', 'in:Suite Class,Business Class,Sleeper,Economy'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'status' => ['nullable', 'integer', 'in:0,1'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to create a fare.')
        );
    }
}
