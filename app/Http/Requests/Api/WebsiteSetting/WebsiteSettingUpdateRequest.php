<?php

namespace App\Http\Requests\Api\WebsiteSetting;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class WebsiteSettingUpdateRequest extends FormRequest
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
            'opening_time' => ['required', 'string', 'max:255'],
            'closing_time' => ['required', 'string', 'max:255'],
            'google_map' => ['required', 'string'],
            'copyright' => ['required', 'string', 'max:255'],
            'footer_text' => ['required', 'string', 'max:255'],
            'facebook_link' => ['nullable', 'string', 'max:255'],
            'twitter_link' => ['nullable', 'string', 'max:255'],
            'instagram_link' => ['nullable', 'string', 'max:255'],
            'youtube_link' => ['nullable', 'string', 'max:255'],
            'tiktok_link' => ['nullable', 'string', 'max:255'],
            'whatsapp_link' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'in:1'],
            'favicon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_favicon' => ['nullable', 'in:1'],
            'preloader' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_preloader' => ['nullable', 'in:1'],
            'footer_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_footer_image' => ['nullable', 'in:1'],
            'currency_symbol' => ['required', 'string', 'max:100'],
            'currency_name' => ['required', 'string', 'max:255'],
            'currency_position' => ['required', 'string', 'in:before,after'],
            'currency_decimal_point' => ['required', 'integer', 'min:0', 'max:4'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Website name is required',
            'email.required' => 'Website email is required',
            'phone.required' => 'Website phone is required',
            'address.required' => 'Website address is required',
            'opening_time.required' => 'Website opening time is required',
            'closing_time.required' => 'Website closing time is required',
            'google_map.required' => 'Google map is required',
            'copyright.required' => 'Website copyright is required',
            'footer_text.required' => 'Website footer text is required',
            'logo.image' => 'Website logo must be an image',
            'favicon.image' => 'Website favicon must be an image',
            'preloader.image' => 'Website preloader must be an image',
            'footer_image.image' => 'Website footer logo must be an image',
            'currency_symbol.required' => 'Currency symbol is required',
            'currency_name.required' => 'Currency name is required',
            'currency_position.required' => 'Currency position is required',
            'currency_position.in' => 'Currency position must be before or after',
            'currency_decimal_point.required' => 'Currency decimal point is required',
            'currency_decimal_point.in' => 'Currency decimal point must be between 0 and 4',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to edit website settings.')
        );
    }
}
