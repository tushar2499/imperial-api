<?php

namespace App\Http\Requests\Api\CoachConfiguration;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CoachConfigurationIndexRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'page' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'integer', 'in:0,1'],
            'coach_type' => ['nullable', 'integer', 'in:1,2'],
            'coach_id' => ['nullable', 'integer', 'exists:coaches,id'],
            'schedule_id' => ['nullable', 'integer', 'exists:schedules,id'],
            'transport_route_id' => ['nullable', 'integer', 'exists:transport_routes,id'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to view coach configurations.')
        );
    }
}
