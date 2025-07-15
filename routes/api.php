<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\SeatPlanController;
use App\Http\Controllers\StationController;

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

    // Routes routes
    Route::prefix('routes')->group(function () {
        Route::get('/', [RouteController::class, 'index']);
        Route::post('/', [RouteController::class, 'store']);
        Route::get('{id}', [RouteController::class, 'show']);
        Route::put('{id}', [RouteController::class, 'update']);
        Route::delete('{id}', [RouteController::class, 'destroy']);
    });

    // Stations routes
    Route::prefix('stations')->group(function () {
        Route::get('/', [StationController::class, 'index']);
        Route::post('/', [StationController::class, 'store']);
        Route::get('{id}', [StationController::class, 'show']);
        Route::put('{id}', [StationController::class, 'update']);
        Route::delete('{id}', [StationController::class, 'destroy']);
    });

    Route::prefix('seat-plans')->group(function () {
        Route::get('/', [SeatPlanController::class, 'index']);
        Route::post('/', [SeatPlanController::class, 'storeWithSeats']);
        Route::get('{id}', [SeatPlanController::class, 'show']);
        Route::put('{id}', [SeatPlanController::class, 'update']);
        Route::delete('{id}', [SeatPlanController::class, 'destroy']);
    });

    // Coaches routes
    Route::prefix('coaches')->group(function () {
        // Get all coaches
        Route::get('/', [CoachController::class, 'index']);
        // Create a new coach
        Route::post('/', [CoachController::class, 'store']);
        // Get a specific coach by ID
        Route::get('{id}', [CoachController::class, 'show']);
        // Update a specific coach by ID
        Route::put('{id}', [CoachController::class, 'update']);
        // Delete a specific coach by ID
        Route::delete('{id}', [CoachController::class, 'destroy']);
    });

    // Counters routes
    Route::prefix('counters')->group(function () {
        // Get all counters
        Route::get('/', [CounterController::class, 'index']);
        // Create a new counter
        Route::post('/', [CounterController::class, 'store']);
        // Get a specific counter by ID
        Route::get('{id}', [CounterController::class, 'show']);
        // Update a specific counter by ID
        Route::put('{id}', [CounterController::class, 'update']);
        // Delete a specific counter by ID
        Route::delete('{id}', [CounterController::class, 'destroy']);
    });

    Route::prefix('seats')->group(function () {
        // Create multiple seats under an existing seat plan
        Route::post('/', [SeatController::class, 'store']);
        // Update a specific seat by ID
        Route::put('{id}', [SeatController::class, 'update']);
        // Delete a specific seat by ID
        Route::delete('{id}', [SeatController::class, 'destroy']);
    });

});
