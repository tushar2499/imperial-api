<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DistrictController;

// Explicitly define public routes without any middleware
Route::withoutMiddleware(['auth:api', 'jwt.auth'])->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Test route to verify API is working
    Route::get('test', function() {
        return response()->json([
            'message' => 'API is working',
            'timestamp' => now(),
            'method' => request()->method(),
            'url' => request()->url()
        ]);
    });
});

// Protected routes (require authentication)
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::post('logout', [AuthController::class, 'logout']);

    // User info route
    Route::get('user', function (Request $request) {
        return response()->json($request->user());
    });

    // District routes
    Route::prefix('districts')->group(function () {
        Route::get('/', [DistrictController::class, 'index']);
        Route::post('/', [DistrictController::class, 'store']);
        Route::get('{id}', [DistrictController::class, 'show']);
        Route::put('{id}', [DistrictController::class, 'update']);
        Route::delete('{id}', [DistrictController::class, 'destroy']);
    });
});
