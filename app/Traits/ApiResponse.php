<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Build the standard JSON response envelope.
     *
     * @param  int  $statusCode  HTTP status code
     * @param  string  $message  Human-readable message
     * @param  mixed  $data  Response payload
     * @param  array  $errors  Validation or domain errors
     * @param  array  $meta  Optional extra metadata (e.g. pagination)
     * @return JsonResponse
     */
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

    /**
     * Return a 200 OK response with an optional metadata array.
     *
     * @param  mixed  $data  Response payload
     * @param  string  $message  Human-readable success message
     * @param  array  $meta  Optional metadata (e.g. pagination info)
     * @return JsonResponse
     */
    public function successResponse(mixed $data, string $message = 'Success', array $meta = []): JsonResponse
    {
        return $this->jsonResponse(200, $message, $data, [], $meta);
    }

    /**
     * Return a 201 Created response after a successful resource creation.
     *
     * @param  mixed  $data  The newly created resource
     * @param  string  $message  Human-readable success message
     * @return JsonResponse
     */
    public function createdResponse(mixed $data, string $message = 'Created successfully'): JsonResponse
    {
        return $this->jsonResponse(201, $message, $data);
    }

    /**
     * Return a 422 Unprocessable Entity response with validation errors.
     *
     * @param  array  $errors  Field-level validation error messages
     * @param  string  $message  Human-readable error summary
     * @return JsonResponse
     */
    public function validationFailedResponse(array $errors, string $message = 'Invalid data'): JsonResponse
    {
        return $this->jsonResponse(422, $message, [], $errors);
    }

    /**
     * Return a 401 Unauthorized response when the request lacks valid credentials.
     *
     * @param  string  $message  Human-readable error message
     * @return JsonResponse
     */
    public function unauthenticatedResponse(string $message = 'Unauthenticated'): JsonResponse
    {
        return $this->jsonResponse(401, $message, []);
    }

    /**
     * Return a 403 Forbidden response when the user is authenticated but not authorized.
     *
     * @param  string  $message  Human-readable error message
     * @return JsonResponse
     */
    public function forbiddenResponse(string $message = 'This action is forbidden'): JsonResponse
    {
        return $this->jsonResponse(403, $message, []);
    }

    /**
     * Return a generic error response with a configurable HTTP status code.
     *
     * @param  string  $message  Human-readable error message
     * @param  int  $statusCode  HTTP status code (defaults to 500)
     * @return JsonResponse
     */
    public function errorResponse(string $message, int $statusCode = 500): JsonResponse
    {
        return $this->jsonResponse($statusCode, $message, []);
    }

    /**
     * Return a 404 Not Found response when the requested resource does not exist.
     *
     * @param  string  $message  Human-readable error message
     * @return JsonResponse
     */
    public function notFoundResponse(string $message = 'Not found'): JsonResponse
    {
        return $this->jsonResponse(404, $message, []);
    }

    /**
     * Return a 405 Method Not Allowed response for unsupported HTTP verbs.
     *
     * @param  string  $message  Human-readable error message
     * @return JsonResponse
     */
    public function methodNotAllowedResponse(string $message = 'Method not allowed'): JsonResponse
    {
        return $this->jsonResponse(405, $message, []);
    }

    /**
     * Return a 500 Internal Server Error response for unexpected failures.
     *
     * @param  string  $message  Human-readable error message
     * @return JsonResponse
     */
    public function somethingWentWrongResponse(string $message = 'Something went wrong'): JsonResponse
    {
        return $this->jsonResponse(500, $message, []);
    }

    /**
     * Extract pagination metadata from a LengthAwarePaginator instance.
     *
     * @param  LengthAwarePaginator  $paginator  The paginator to extract meta from
     * @return array{current_page: int, per_page: int, total: int, last_page: int}
     */
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
