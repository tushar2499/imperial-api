<?php

namespace App\Http\Requests\Api\OfferAndPromo;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class OfferAndPromoStoreRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'expired_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'description' => ['nullable', 'string'],
            'link' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to create an offer and promo.')
        );
    }
}
