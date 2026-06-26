<?php

namespace App\Http\Requests\Api\Bus;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BusStoreRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'registration_number' => ['required', 'string', 'max:255', 'unique:buses,registration_number'],
            'manufacturer_company' => ['required', 'string', 'max:255'],
            'model_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'chasis_no' => ['required', 'string', 'max:255'],
            'engine_number' => ['required', 'string', 'max:255'],
            'country_of_origin' => ['nullable', 'string', 'max:255'],
            'lc_code_number' => ['nullable', 'string', 'max:255'],
            'delivery_to_dipo' => ['nullable', 'string', 'max:255'],
            'delivery_date' => ['nullable', 'date'],
            'color' => ['nullable', 'string', 'max:255'],
            'financed_by' => ['nullable', 'string', 'max:255'],
            'tennure_of_the_terms' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to create a bus.')
        );
    }
}
