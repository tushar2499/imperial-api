<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DocumentationController;

Route::get('/', [DocumentationController::class, 'index'])->name('docs.index');
Route::get('/authentication', [DocumentationController::class, 'authentication_fn']);
Route::get('/docs/logout', function () {
    return view('docs.logout');
});

Route::get('/get-districts', [DocumentationController::class, 'get_districts']);
Route::get('/create-districts', [DocumentationController::class, 'create_districts']);
Route::get('/get-districts', [DocumentationController::class, 'get_districts']);
Route::get('/single-districts', [DocumentationController::class, 'single_districts']);
Route::get('/update-districts', [DocumentationController::class, 'update_districts']);


// SeatPlanController API Endpoints
Route::get('/docs/seat-plans', function () {
    return view('docs.seat-plans.index');
});
Route::get('/docs/seat-plans/create', function () {
    return view('docs.seat-plans.create');
});
Route::get('/docs/seat-plans/single', function () {
    return view('docs.seat-plans.single');
});
Route::get('/docs/seat-plans/update', function () {
    return view('docs.seat-plans.update');
});
Route::get('/docs/seat-plans/delete/{id}', function () {
    return view('docs.seat-plans.delete');
});

// SeatController API Endpoints
Route::get('/docs/seats/create', function () {
    return view('docs.seats.create');
});
Route::get('/docs/seats/update', function () {
    return view('docs.seats.update');
});
Route::get('/docs/seats/delete', function () {
    return view('docs.seats.delete');
});

// Route CRUD API Documentation Routes
Route::get('/docs/routes', function () {
    return view('docs.routes.index');  // List all routes
});
Route::get('/docs/routes/create', function () {
    return view('docs.routes.create');  // Create a new route
});
Route::get('/docs/routes/single', function () {
    return view('docs.routes.single');  // Get a specific route by ID
});
Route::get('/docs/routes/update', function () {
    return view('docs.routes.update');  // Update a route by ID
});
Route::get('/docs/routes/delete', function () {
    return view('docs.routes.delete');  // Delete a route by ID
});


// Station API Documentation Routes
Route::get('/docs/stations', function () {
    return view('docs.stations.index');  // Get all stations
});
Route::get('/docs/stations/create', function () {
    return view('docs.stations.create');  // Create a new station
});
Route::get('/docs/stations/single', function () {
    return view('docs.stations.single');  // Get a specific station
});
Route::get('/docs/stations/update', function () {
    return view('docs.stations.update');  // Update a specific station
});
Route::get('/docs/stations/delete', function () {
    return view('docs.stations.delete');  // Delete a specific station
});


// Coaches API Documentation Routes
Route::get('/docs/coaches', function () {
    return view('docs.coaches.index');  // List all coaches
});

Route::get('/docs/coaches/create', function () {
    return view('docs.coaches.create');  // Create a new coach
});

Route::get('/docs/coaches/single', function () {
    return view('docs.coaches.single');  // Get a specific coach by ID
});

Route::get('/docs/coaches/update', function () {
    return view('docs.coaches.update');  // Update a specific coach
});

Route::get('/docs/coaches/delete', function () {
    return view('docs.coaches.delete');  // Delete a specific coach
});


Route::prefix('docs/buses')->group(function () {
    Route::get('/', function () {
        return view('docs.buses.index');
    });
    Route::get('/create', function () {
        return view('docs.buses.create');
    });
    Route::get('/single', function () {
        return view('docs.buses.single');
    });
    Route::get('/update', function () {
        return view('docs.buses.update');
    });
    Route::get('/delete', function () {
        return view('docs.buses.delete');
    });
});

// Schedules API Documentation Routes
Route::get('/docs/schedules', function () {
    return view('docs.schedules.index');  // List all schedules
});
Route::get('/docs/schedules/create', function () {
    return view('docs.schedules.create');  // Create a new schedule
});
Route::get('/docs/schedules/single', function () {
    return view('docs.schedules.single');  // Get a specific schedule by ID
});
Route::get('/docs/schedules/update', function () {
    return view('docs.schedules.update');  // Update a specific schedule
});
Route::get('/docs/schedules/delete', function () {
    return view('docs.schedules.delete');  // Delete a specific schedule
});

// Fares API Documentation Routes
Route::get('/docs/fares', function () {
    return view('docs.fares.index');  // List all fares
});
Route::get('/docs/fares/create', function () {
    return view('docs.fares.create');  // Create a new fare
});
Route::get('/docs/fares/single', function () {
    return view('docs.fares.single');  // Get a specific fare by ID
});
Route::get('/docs/fares/update', function () {
    return view('docs.fares.update');  // Update a specific fare
});
Route::get('/docs/fares/delete', function () {
    return view('docs.fares.delete');  // Delete a specific fare
});


// Counters API Documentation Routes
Route::get('/docs/counters', function () {
    return view('docs.counters.index');  // List all counters
});
Route::get('/docs/counters/create', function () {
    return view('docs.counters.create');  // Create a new counter
});
Route::get('/docs/counters/single', function () {
    return view('docs.counters.single');  // Get a specific counter by ID
});
Route::get('/docs/counters/update', function () {
    return view('docs.counters.update');  // Update a specific counter
});
Route::get('/docs/counters/delete', function () {
    return view('docs.counters.delete');  // Delete a specific counter
});

// Designations API Documentation Routes
Route::prefix('docs/designations')->group(function () {
    Route::get('/', function () {
        return view('docs.designations.index');
    });
    Route::get('/create', function () {
        return view('docs.designations.create');
    });
    Route::get('/single', function () {
        return view('docs.designations.single');
    });
    Route::get('/update', function () {
        return view('docs.designations.update');
    });
    Route::get('/delete', function () {
        return view('docs.designations.delete');
    });
});

// Employee API Documentation Routes
Route::prefix('docs/employees')->group(function () {
    Route::get('/', function () {
        return view('docs.employees.index');
    });
    Route::get('/create', function () {
        return view('docs.employees.create');
    });
    Route::get('/single', function () {
        return view('docs.employees.single');
    });
    Route::get('/update', function () {
        return view('docs.employees.update');
    });
    Route::get('/delete', function () {
        return view('docs.employees.delete');
    });
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



//Route::get('login',  [AuthController::class, 'login'])->name('login');
//Route::post('login', [AuthController::class, 'login']);
//Route::post('logout', [AuthController::class, 'logout'])->name('logout');


Route::get('/clear', function () {
    \Artisan::call('config:clear');
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');
    \Artisan::call('event:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('optimize:clear');
    \Artisan::call('optimize');

    return "Clear";
});
