<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success response.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data = null, $message = 'Data retrieved successfully', $status = 200): JsonResponse
    {
        $response = [
            'status' => 'success',
            'code' => $status,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($response, $status);
    }

    /**
     * Return an error response.
     *
     * @param  string  $message
     * @param  int  $status
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($message = 'An error occurred', $status = 400, $data = null): JsonResponse
    {
        $response = [
            'status' => 'error',
            'code' => $status,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($response, $status);
    }

    /**
     * Return a validation error response.
     *
     * @param  array  $errors
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function validationErrorResponse($errors, $status = 422): JsonResponse
    {
        // Convert MessageBag to array using all() method
        $errors = $errors->all();

        $response = [
            'status' => 'error',
            'code' => $status,
            'message' => 'Validation error',
            'data' => $errors,
        ];

        return response()->json($response, $status);
    }

    /**
     * Return a not found response.
     *
     * @param  string  $message
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function notFoundResponse($message = 'Resource not found', $status = 404): JsonResponse
    {
        return $this->errorResponse($message, $status);
    }

    /**
     * Return an unauthorized response.
     *
     * @param  string  $message
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function unauthorizedResponse($message = 'Unauthorized', $status = 401): JsonResponse
    {
        return $this->errorResponse($message, $status);
    }
}
