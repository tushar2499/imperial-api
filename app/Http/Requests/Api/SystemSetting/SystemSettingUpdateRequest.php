<?php

namespace App\Http\Requests\Api\SystemSetting;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SystemSettingUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'in:1'],
            'favicon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_favicon' => ['nullable', 'in:1'],
            'preloader' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_preloader' => ['nullable', 'in:1'],
            'print_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_print_logo' => ['nullable', 'in:1'],
            'print_footer_message' => ['required', 'string', 'max:255'],
            'bin_number' => ['nullable', 'string', 'max:255'],

            'data_per_page' => ['required', 'min:1', 'max:100'],
            'currency_symbol' => ['required', 'string', 'max:100'],
            'currency_name' => ['required', 'string', 'max:255'],
            'currency_position' => ['required', 'string', 'in:before,after'],
            'currency_decimal_point' => ['required', 'min:0', 'max:4'],

            'date_format' => ['required', 'string', Rule::in(['d-m-Y', 'm-d-Y', 'Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d', 'd.m.Y', 'M d, Y', 'F d, Y'])],
            'time_format' => ['required', 'string', Rule::in(['h:i A', 'h:i:s A', 'H:i', 'H:i:s'])],

            'is_qr_code_show'      => ['nullable', 'in:0,1'],
            'seat_hold_minutes'    => ['nullable', 'min:1', 'max:4320'],
            'booking_advance_days' => ['nullable', 'min:1', 'max:365'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'phone.required' => 'Phone is required',
            'address.required' => 'Address is required',
            'logo.required' => 'Logo is required',
            'data_per_page.required' => 'Data per page is required',
            'currency_symbol.required' => 'Currency symbol is required',
            'currency_name.required' => 'Currency name is required',
            'currency_position.required' => 'Currency position is required',
            'currency_position.in' => 'Currency position must be before or after',
            'currency_decimal_point.required' => 'Currency decimal point is required',
            'currency_decimal_point.in' => 'Currency decimal point must be between 0 and 4',

            'date_format.required' => 'Date display format is required',
            'date_format.in' => 'Invalid date format selected.',
            'time_format.required' => 'Time display format is required',
            'time_format.in' => 'Invalid time format selected.',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to edit system settings.')
        );
    }
}
