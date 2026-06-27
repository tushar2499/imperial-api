<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    public function jsonResponse(int $statusCode, string $message, mixed $data, array $errors = [], array $meta = []): JsonResponse
    {
        $response = [
            'status' => $statusCode >= 200 && $statusCode < 300,
            'status_code' => $statusCode,
            'message' => $message,
            'data' => ! empty($data) ? $data : null,
            'errors' => ! empty($errors) ? $errors : null,
        ];
        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    public function successResponse(mixed $data, string $message = 'Success', array $meta = []): JsonResponse
    {
        return $this->jsonResponse(200, $message, $data, [], $meta);
    }

    public function createdResponse(mixed $data, string $message = 'Created successfully'): JsonResponse
    {
        return $this->jsonResponse(201, $message, $data);
    }

    public function validationFailedResponse(array $errors, string $message = 'Invalid data'): JsonResponse
    {
        return $this->jsonResponse(422, $message, [], $errors);
    }

    public function unauthenticatedResponse(string $message = 'Unauthenticated'): JsonResponse
    {
        return $this->jsonResponse(401, $message, []);
    }

    public function forbiddenResponse(string $message = 'This action is forbidden'): JsonResponse
    {
        return $this->jsonResponse(403, $message, []);
    }

    public function notFoundResponse(string $message = 'Not found'): JsonResponse
    {
        return $this->jsonResponse(404, $message, []);
    }

    public function methodNotAllowedResponse(string $message = 'Method not allowed'): JsonResponse
    {
        return $this->jsonResponse(405, $message, []);
    }

    public function somethingWentWrongResponse(string $message = 'Something went wrong'): JsonResponse
    {
        return $this->jsonResponse(500, $message, []);
    }

    public function preparePaginator(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
