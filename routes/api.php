<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\CoachConfigurationController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FareController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\SeatPlanController;
use App\Http\Controllers\StationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Explicitly define public routes without any middleware
Route::withoutMiddleware(['auth:api', 'jwt.auth'])->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Test route to verify API is working
    Route::get('test', function () {
        return response()->json([
            'message'   => 'API is working',
            'timestamp' => now(),
            'method'    => request()->method(),
            'url'       => request()->url(),
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
        Route::get('/all-active', [DistrictController::class, 'allActiveDistricts']);
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

    //Schedules
    Route::prefix('schedules')->group(function () {
        Route::get('/', [ScheduleController::class, 'index']);
        Route::post('/', [ScheduleController::class, 'store']);
        Route::get('{id}', [ScheduleController::class, 'show']);
        Route::put('{id}', [ScheduleController::class, 'update']);
        Route::delete('{id}', [ScheduleController::class, 'destroy']);
    });

    // Fares routes
    Route::prefix('fares')->group(function () {
        Route::get('/', [FareController::class, 'index']);
        Route::post('/', [FareController::class, 'store']);
        Route::get('{id}', [FareController::class, 'show']);
        Route::put('{id}', [FareController::class, 'update']);
        Route::delete('{id}', [FareController::class, 'destroy']);
    });

    //seat plan
    Route::prefix('seat-plans')->group(function () {
        Route::get('/', [SeatPlanController::class, 'index']);
        Route::post('/', [SeatPlanController::class, 'storeWithSeats']);
        Route::get('{id}', [SeatPlanController::class, 'show']);
        Route::put('{id}', [SeatPlanController::class, 'update']);
        Route::delete('{id}', [SeatPlanController::class, 'destroy']);
    });

    // Coaches routes
    Route::prefix('coaches')->group(function () {
        Route::get('/', [CoachController::class, 'index']);
        Route::post('/', [CoachController::class, 'store']);
        Route::get('{id}', [CoachController::class, 'show']);
        Route::put('{id}', [CoachController::class, 'update']);
        Route::delete('{id}', [CoachController::class, 'destroy']);
    });

    // Buses routes
    Route::prefix('buses')->group(function () {
        Route::get('/', [BusController::class, 'index']);
        Route::post('/', [BusController::class, 'store']);
        Route::get('{id}', [BusController::class, 'show']);
        Route::put('{id}', [BusController::class, 'update']);
        Route::delete('{id}', [BusController::class, 'destroy']);
    });

    // Counters routes
    Route::prefix('counters')->group(function () {
        Route::get('/', [CounterController::class, 'index']);
        Route::post('/', [CounterController::class, 'store']);
        Route::get('{id}', [CounterController::class, 'show']);
        Route::put('{id}', [CounterController::class, 'update']);
        Route::delete('{id}', [CounterController::class, 'destroy']);
    });

    // Seats routes
    Route::prefix('seats')->group(function () {
        // Create multiple seats under an existing seat plan
        Route::post('/', [SeatController::class, 'store']);
        // Update a specific seat by ID
        Route::put('{id}', [SeatController::class, 'update']);
        // Delete a specific seat by ID
        Route::delete('{id}', [SeatController::class, 'destroy']);
    });

    // Designations routes
    Route::prefix('designations')->group(function () {
        Route::get('/', [DesignationController::class, 'index']);
        Route::get('/all-active', [DesignationController::class, 'allActiveDesignations']);
        Route::post('/', [DesignationController::class, 'store']);
        Route::get('{id}', [DesignationController::class, 'show']);
        Route::put('{id}', [DesignationController::class, 'update']);
        Route::delete('{id}', [DesignationController::class, 'destroy']);
    });

    // Employees routes
    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::get('{id}', [EmployeeController::class, 'show']);
        Route::put('{id}', [EmployeeController::class, 'update']);
        Route::delete('{id}', [EmployeeController::class, 'destroy']);
    });

    // Coach Configurations routes
    Route::prefix('coach-configurations')->name('coach-configurations.')->group(function () {
        Route::get('/', [CoachConfigurationController::class, 'index'])->name('index');
        Route::post('/', [CoachConfigurationController::class, 'store'])->name('store');
        Route::get('/{coachConfiguration}', [CoachConfigurationController::class, 'show'])->name('show');
        Route::put('/{coachConfiguration}', [CoachConfigurationController::class, 'update'])->name('update');
        Route::delete('/{coachConfiguration}', [CoachConfigurationController::class, 'destroy'])->name('destroy');

        // Additional utility routes
        Route::get('/schedule/{scheduleId}', [CoachConfigurationController::class, 'getBySchedule'])->name('by-schedule');
        Route::get('/coach/{coachId}', [CoachConfigurationController::class, 'getByCoach'])->name('by-coach');
        Route::get('/route/{routeId}', [CoachConfigurationController::class, 'getByRoute'])->name('by-route');
        Route::patch('/{coachConfiguration}/toggle-status', [CoachConfigurationController::class, 'toggleStatus'])->name('toggle-status');
    });

});
