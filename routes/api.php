<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DistrictController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');


Route::middleware('auth:api')->prefix('districts')->group(function () {
    Route::get('/', [DistrictController::class, 'index']);     // Get all districts
    Route::post('/', [DistrictController::class, 'store']);    // Create a new district
    Route::get('{id}', [DistrictController::class, 'show']);   // Get a specific district
    Route::put('{id}', [DistrictController::class, 'update']); // Update a district
    Route::delete('{id}', [DistrictController::class, 'destroy']); // Delete a district
});
