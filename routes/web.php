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
Route::get('/docs/refresh-token', function () {
    return view('docs.refresh_token');
});
Route::get('/docs/user', function () {
    return view('docs.user');
});
Route::get('/docs/profile', function () {
    return view('docs.profile.index');
});
Route::get('/docs/profile/update', function () {
    return view('docs.profile.update');
});
Route::get('/docs/profile/photo', function () {
    return view('docs.profile.photo');
});
Route::get('/docs/profile/password', function () {
    return view('docs.profile.password');
});

Route::get('/get-districts', [DocumentationController::class, 'get_districts']);
Route::get('/create-districts', [DocumentationController::class, 'create_districts']);
Route::get('/get-districts', [DocumentationController::class, 'get_districts']);
Route::get('/single-districts', [DocumentationController::class, 'single_districts']);
Route::get('/update-districts', [DocumentationController::class, 'update_districts']);


// Admin Users
Route::prefix('docs/admin-users')->group(function () {
    Route::get('/', function () {
        return view('docs.admin-users.index');
    });
    Route::get('/create', function () {
        return view('docs.admin-users.create');
    });
    Route::get('/single', function () {
        return view('docs.admin-users.single');
    });
    Route::get('/update', function () {
        return view('docs.admin-users.update');
    });
    Route::get('/delete', function () {
        return view('docs.admin-users.delete');
    });
});


// SeatPlanController API Endpoints
Route::prefix('docs/seat-plans')->group(function () {
    Route::get('/', function () {
        return view('docs.seat-plans.index');
    });
    Route::get('/create', function () {
        return view('docs.seat-plans.create');
    });
    Route::get('/single', function () {
        return view('docs.seat-plans.single');
    });
    Route::get('/update', function () {
        return view('docs.seat-plans.update');
    });
    Route::get('/delete', function () {
        return view('docs.seat-plans.delete');
    });
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
Route::get('/docs/routes/popular-routes', function () {
    return view('docs.routes.popular-routes');  // List all popular routes
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
Route::get('/docs/routes/update-popular-positions', function () {
    return view('docs.routes.update-popular-positions');  // Update a route by ID
});
Route::get('/docs/routes/delete', function () {
    return view('docs.routes.delete');  // Delete a route by ID
});


// Stations API Documentation Routes
Route::prefix('docs/stations')->group(function () {
    Route::get('/', function () {
        return view('docs.stations.index');
    });
    Route::get('/create', function () {
        return view('docs.stations.create');
    });
    Route::get('/single', function () {
        return view('docs.stations.single');
    });
    Route::get('/update', function () {
        return view('docs.stations.update');
    });
    Route::get('/delete', function () {
        return view('docs.stations.delete');
    });
});

// Coaches API Documentation Routes
Route::prefix('docs/coaches')->group(function () {
    Route::get('/', function () {
        return view('docs.coaches.index');
    });
    Route::get('/create', function () {
        return view('docs.coaches.create');
    });
    Route::get('/single', function () {
        return view('docs.coaches.single');
    });
    Route::get('/update', function () {
        return view('docs.coaches.update');
    });
    Route::get('/delete', function () {
        return view('docs.coaches.delete');
    });
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
Route::prefix('docs/schedules')->group(function () {
    Route::get('/', function () {
        return view('docs.schedules.index');
    });
    Route::get('/create', function () {
        return view('docs.schedules.create');
    });
    Route::get('/single', function () {
        return view('docs.schedules.single');
    });
    Route::get('/update', function () {
        return view('docs.schedules.update');
    });
    Route::get('/delete', function () {
        return view('docs.schedules.delete');
    });
});

// Fares API Documentation Routes
Route::prefix('docs/fares')->group(function () {
    Route::get('/', function () {
        return view('docs.fares.index');
    });
    Route::get('/create', function () {
        return view('docs.fares.create');
    });
    Route::get('/single', function () {
        return view('docs.fares.single');
    });
    Route::get('/update', function () {
        return view('docs.fares.update');
    });
    Route::get('/delete', function () {
        return view('docs.fares.delete');
    });
});



// Counters API Documentation Routes
Route::prefix('docs/counters')->group(function () {
    Route::get('/', function () {
        return view('docs.counters.index');
    });
    Route::get('/create', function () {
        return view('docs.counters.create');
    });
    Route::get('/single', function () {
        return view('docs.counters.single');
    });
    Route::get('/update', function () {
        return view('docs.counters.update');
    });
    Route::get('/delete', function () {
        return view('docs.counters.delete');
    });
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


// Coach Configurations API Documentation Routes
Route::get('/docs/coach-configurations', function () {
    return view('docs.coach-configurations.index');  // List all coach configurations
});
Route::get('/docs/coach-configurations/create', function () {
    return view('docs.coach-configurations.create');  // Create a new coach configuration
});
Route::get('/docs/coach-configurations/single', function () {
    return view('docs.coach-configurations.single');  // Get a specific coach configuration by ID
});
Route::get('/docs/coach-configurations/update', function () {
    return view('docs.coach-configurations.update');  // Update a specific coach configuration
});
Route::get('/docs/coach-configurations/delete', function () {
    return view('docs.coach-configurations.delete');  // Delete a specific coach configuration
});
Route::get('/docs/coach-configurations/by-schedule', function () {
    return view('docs.coach-configurations.by-schedule');  // Get coach configurations by schedule
});
Route::get('/docs/coach-configurations/by-coach', function () {
    return view('docs.coach-configurations.by-coach');  // Get coach configurations by coach
});
Route::get('/docs/coach-configurations/by-route', function () {
    return view('docs.coach-configurations.by-route');  // Get coach configurations by route
});
Route::get('/docs/coach-configurations/toggle-status', function () {
    return view('docs.coach-configurations.toggle-status');  // Toggle status
});



// Trip Instances API Documentation Routes
Route::get('/docs/trip-instances', function () {
    return view('docs.trip-instances.index');  // List all trip instances
});
Route::get('/docs/trip-instances/create', function () {
    return view('docs.trip-instances.create');  // Create a new trip instance
});
Route::get('/docs/trip-instances/single', function () {
    return view('docs.trip-instances.single');  // Get a specific trip instance by ID
});
Route::get('/docs/trip-instances/update', function () {
    return view('docs.trip-instances.update');
});
Route::get('/docs/trip-instances/delete', function () {
    return view('docs.trip-instances.delete');
});

Route::get('/docs/trip-instances/search-trips', function () {
    return view('docs.trip-instances.search-trips');  // Search trips API documentation
});





// Seat Requests API Documentation Routes
Route::get('/docs/seat-requests', function () {
    return view('docs.seat-requests.index');  // Overview of seat request system
});
Route::get('/docs/seat-requests/create', function () {
    return view('docs.seat-requests.create');  // Create seat request
});
Route::get('/docs/seat-requests/multi-seat', function () {
    return view('docs.seat-requests.multi-seat');  // Multi-seat booking workflow
});

// Individual Seat Request Cancellation Routes
Route::get('/docs/seat-requests/cancel-individual', function () {
    return view('docs.seat-requests.cancel-individual');  // Cancel individual seat request
});
Route::get('/docs/seat-requests/cancel-issue', function () {
    return view('docs.seat-requests.cancel-issue');  // Cancel all seats from issue
});


Route::get('/docs/seat-booked-blocked-requests', function () {
    return view('docs.seat-requests.book-block-request');  // Overview of seat request system
});

Route::get('/docs/seat-booked-blocked-cancel', function () {
    return view('docs.seat-requests.book-block-cancel');  // Overview of seat request system
});

// Customers API Documentation Routes
Route::prefix('docs/customers')->group(function () {
    Route::get('/', function () {
        return view('docs.customers.index');
    });
    Route::get('/create', function () {
        return view('docs.customers.create');
    });
    Route::get('/single', function () {
        return view('docs.customers.single');
    });
    Route::get('/by-mobile', function () {
        return view('docs.customers.by-mobile');
    });
     Route::get('/update-by-mobile', function () {
        return view('docs.customers.update-by-mobile');
    });
    Route::get('/update', function () {
        return view('docs.customers.update');
    });
    Route::get('/delete', function () {
        return view('docs.customers.delete');
    });
});

Route::get('/docs/trip-instances', function () {
    return view('docs.trip-instances.index');  // List all trip instances
});

// Bookings API Documentation Routes
Route::prefix('docs/bookings')->group(function () {
    Route::get('/', function () {
        return view('docs.bookings.index');
    });
    Route::get('/create', function () {
        return view('docs.bookings.create');
    });
    Route::get('/single', function () {
        return view('docs.bookings.single');
    });
    Route::get('/cancel', function () {
        return view('docs.bookings.cancel');
    });
});

// SeatPlanController API Endpoints
Route::prefix('docs/reports')->group(function () {
    Route::get('/coach-trips', function () {
        return view('docs.reports.coach-trips');
    });
    Route::get('/coach-sales', function () {
        return view('docs.reports.coach-sales');
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
