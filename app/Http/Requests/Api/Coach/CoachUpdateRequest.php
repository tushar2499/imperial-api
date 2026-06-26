<?php

namespace App\Http\Requests\Api\Coach;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CoachUpdateRequest extends FormRequest
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
            'coach_no' => ['required', 'string', 'max:255', "unique:coaches,coach_no,{$id}"],
            'seat_plan_id' => ['required', 'integer', 'exists:seat_plans,id'],
            'coach_type' => ['required', 'in:1,2'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to update this coach.')
        );
    }
}
