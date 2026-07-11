<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerIsActive
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customer = auth('customer')->user();

        if ($customer && $customer->status !== 1) {
            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Your account is inactive. Please contact the administrator.',
            ], 401);
        }

        return $next($request);
    }
}
