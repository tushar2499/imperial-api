<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // SSLCommerz posts to these routes from their servers — no CSRF token available
        'api/payment/success',
        'api/payment/fail',
        'api/payment/cancel',
        'api/payment/ipn',
    ];
}
