<?php

namespace App\Http\Requests\Api\SeatPlan;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SeatPlanStoreRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('seat_plans', 'name')->where('floor', $this->input('floor'))],
            'floor' => ['required', 'integer', 'in:1,2'],
            'status' => ['nullable', 'integer', 'in:0,1'],
            'floors_data' => ['required', 'array', 'min:1'],
            'floors_data.*.id' => ['nullable', 'string'],
            'floors_data.*.name' => ['required', 'string', 'max:255'],
            'floors_data.*.layoutType' => ['required', 'string'],
            'floors_data.*.rows' => ['required', 'integer', 'min:1'],
            'floors_data.*.cols' => ['required', 'integer', 'min:1'],
            'floors_data.*.step' => ['required', 'integer', 'min:1'],
            'floors_data.*.extraSeat' => ['required', 'boolean'],
            'floors_data.*.seats' => ['required', 'array', 'min:1'],
            'floors_data.*.seats.*.rowNumber' => ['required', 'integer', 'min:1'],
            'floors_data.*.seats.*.colNumber' => ['required', 'integer', 'min:1'],
            'floors_data.*.seats.*.seatName' => ['nullable', 'string', 'max:255'],
            'floors_data.*.seats.*.seatType' => ['nullable', 'string', 'max:255'],
            'floors_data.*.seats.*.isDisable' => ['required', 'integer', 'in:0,1'],
            'floors_data.*.seats.*.status' => ['required', 'integer', 'in:0,1'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to create a seat plan.')
        );
    }
}
