<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request)
    {
        // For API requests, return JSON response instead of redirecting
        if ($request->expectsJson()) {
            $response = [
                'status' => 'error',
                'code' => 401,
                'message' => 'Unauthenticated',
                'data' => 'Unauthenticated request. Please log in to access this resource.'
            ];

            abort(response()->json($response));
        }

        // For web requests, redirect to login route (optional)
        return route('login');
    }
}
