<?php

namespace App\Http\Requests\Api\Counter;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CounterStoreRequest extends FormRequest
{
    use ApiResponse;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'integer', 'in:1,2,3'],
            'address' => [
                'required',
                'string',
                'max:255',
                Rule::unique('counters', 'address')->where(function ($query) {
                    return $query
                        ->where('type', $this->type)
                        ->where('district_id', $this->district_id)
                        ->whereNull('deleted_at');
                }),
            ],
            'land_mark' => ['nullable', 'string', 'max:255'],
            'location_url' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'primary_contact_no' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'booking_allowed_status' => ['required', 'integer', 'in:1,2,3'],
            'booking_allowed_class' => ['required', 'integer', 'in:1,2,3,4'],
            'no_of_boarding_allowed' => ['nullable', 'integer'],
            'sms_status' => ['nullable', 'integer', 'in:1,2'],
            'status' => ['nullable', 'integer', 'in:0,1'],
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to create a counter.')
        );
    }
}
