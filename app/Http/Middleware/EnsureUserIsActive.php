<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->status !== 1) {
            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Your account is inactive. Please contact the administrator.',
            ], 401);
        }

        return $next($request);
    }
}
