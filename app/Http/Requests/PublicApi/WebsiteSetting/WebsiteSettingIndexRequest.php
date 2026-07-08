<?php

namespace App\Http\Requests\PublicApi\WebsiteSetting;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class WebsiteSettingIndexRequest extends FormRequest
{
    use ApiResponse;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
