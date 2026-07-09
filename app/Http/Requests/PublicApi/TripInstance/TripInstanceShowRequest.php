<?php

namespace App\Http\Requests\PublicApi\TripInstance;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class TripInstanceShowRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
