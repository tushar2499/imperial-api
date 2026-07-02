<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthenticateUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\CoachConfigurationController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerReviewController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FareController;
use App\Http\Controllers\OfferAndPromoController;
use App\Http\Controllers\Report\CoachReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\SeatInventoryController;
use App\Http\Controllers\SeatPlanController;
use App\Http\Controllers\SeatRequestController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\TransportRouteController;
use App\Http\Controllers\TripInstanceController;
use App\Http\Controllers\TripInstanceSearchController;
use App\Http\Controllers\TripInstanceSeatInventoryController;
use Illuminate\Support\Facades\Route;

// Explicitly define public routes without any middleware
Route::withoutMiddleware(['auth:api', 'jwt.auth'])->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Test route to verify API is working
    Route::get('test', function () {
        return response()->json([
            'message' => 'API is working',
            'timestamp' => now(),
            'method' => request()->method(),
            'url' => request()->url(),
        ]);
    });

    // Public home page
    Route::get('home-page', [App\Http\Controllers\Api\Public\HomePageController::class, 'index']);
});

// Protected routes (require authentication)
Route::middleware(['auth:api', 'active'])->group(function () {
    // Auth routes
    Route::post('refresh-token', [AuthController::class, 'refreshToken']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthenticateUserController::class, 'authenticateUser']);
    Route::get('profile', [AuthenticateUserController::class, 'showProfile']);
    Route::put('profile', [AuthenticateUserController::class, 'updateProfile']);
    Route::post('profile/photo', [AuthenticateUserController::class, 'updatePhoto']);
    Route::post('profile/password', [AuthenticateUserController::class, 'updatePassword']);

    // Admin User routes
    Route::prefix('admin-users')->group(function () {
        Route::get('/', [AdminUserController::class, 'index']);
        Route::post('/', [AdminUserController::class, 'store']);
        Route::get('{id}', [AdminUserController::class, 'show']);
        Route::put('{id}', [AdminUserController::class, 'update']);
        Route::delete('{id}', [AdminUserController::class, 'destroy']);
        Route::patch('/{id}/active', [AdminUserController::class, 'active']);
        Route::patch('/{id}/inactive', [AdminUserController::class, 'inactive']);
    });

    // District routes
    Route::prefix('districts')->group(function () {
        Route::get('/', [DistrictController::class, 'index']);
        Route::get('/all-active', [DistrictController::class, 'allActive']);
        Route::post('/', [DistrictController::class, 'store']);
        Route::get('{id}', [DistrictController::class, 'show']);
        Route::put('{id}', [DistrictController::class, 'update']);
        Route::delete('{id}', [DistrictController::class, 'destroy']);
        Route::patch('/{id}/active', [DistrictController::class, 'active']);
        Route::patch('/{id}/inactive', [DistrictController::class, 'inactive']);
    });

    // Transport Routes routes
    Route::prefix('transport-routes')->group(function () {
        Route::get('/', [TransportRouteController::class, 'index']);
        Route::get('/all-active', [TransportRouteController::class, 'allActive']);
        Route::get('/popular-routes', [TransportRouteController::class, 'allPopularRoutes']);
        Route::post('/', [TransportRouteController::class, 'store']);
        Route::get('{id}', [TransportRouteController::class, 'show']);
        Route::put('{id}', [TransportRouteController::class, 'update']);
        Route::patch('/update-popular-positions', [TransportRouteController::class, 'updatePopularPositions']);
        Route::delete('{id}', [TransportRouteController::class, 'destroy']);
        Route::get('{id}/counters', [TransportRouteController::class, 'routeWiseCounters']);
    });

    // Stations routes
    Route::prefix('stations')->group(function () {
        Route::get('/', [StationController::class, 'index']);
        Route::post('/', [StationController::class, 'store']);
        Route::get('{id}', [StationController::class, 'show']);
        Route::put('{id}', [StationController::class, 'update']);
        Route::delete('{id}', [StationController::class, 'destroy']);
    });

    // Schedules
    Route::prefix('schedules')->group(function () {
        Route::get('/', [ScheduleController::class, 'index']);
        Route::get('/all-active', [ScheduleController::class, 'allActive']);
        Route::post('/', [ScheduleController::class, 'store']);
        Route::get('{id}', [ScheduleController::class, 'show']);
        Route::put('{id}', [ScheduleController::class, 'update']);
        Route::delete('{id}', [ScheduleController::class, 'destroy']);
        Route::patch('/{id}/active', [ScheduleController::class, 'active']);
        Route::patch('/{id}/inactive', [ScheduleController::class, 'inactive']);
    });

    // Fares routes
    Route::prefix('fares')->group(function () {
        Route::get('/', [FareController::class, 'index']);
        Route::get('/all-active', [FareController::class, 'allActive']);
        Route::post('/', [FareController::class, 'store']);
        Route::get('{id}', [FareController::class, 'show']);
        Route::put('{id}', [FareController::class, 'update']);
        Route::delete('{id}', [FareController::class, 'destroy']);
        Route::patch('/{id}/active', [FareController::class, 'active']);
        Route::patch('/{id}/inactive', [FareController::class, 'inactive']);
    });

    // seat plan
    Route::prefix('seat-plans')->group(function () {
        Route::get('/', [SeatPlanController::class, 'index']);
        Route::get('/all-active', [SeatPlanController::class, 'allActive']);
        Route::post('/', [SeatPlanController::class, 'store']);
        Route::get('{id}', [SeatPlanController::class, 'show']);
        Route::put('{id}', [SeatPlanController::class, 'update']);
        Route::delete('{id}', [SeatPlanController::class, 'destroy']);
        Route::patch('/{id}/active', [SeatPlanController::class, 'active']);
        Route::patch('/{id}/inactive', [SeatPlanController::class, 'inactive']);
    });

    // Coaches routes
    Route::prefix('coaches')->group(function () {
        Route::get('/', [CoachController::class, 'index']);
        Route::get('/all-active', [CoachController::class, 'allActive']);
        Route::post('/', [CoachController::class, 'store']);
        Route::get('{id}', [CoachController::class, 'show']);
        Route::put('{id}', [CoachController::class, 'update']);
        Route::delete('{id}', [CoachController::class, 'destroy']);
        Route::patch('/{id}/active', [CoachController::class, 'active']);
        Route::patch('/{id}/inactive', [CoachController::class, 'inactive']);
    });

    // Buses routes
    Route::prefix('buses')->group(function () {
        Route::get('/', [BusController::class, 'index']);
        Route::post('/', [BusController::class, 'store']);
        Route::get('all-active', [BusController::class, 'allActive']);
        Route::get('model-years', [BusController::class, 'modelYears']);
        Route::get('{id}', [BusController::class, 'show']);
        Route::put('{id}', [BusController::class, 'update']);
        Route::delete('{id}', [BusController::class, 'destroy']);
        Route::patch('/{id}/active', [BusController::class, 'active']);
        Route::patch('/{id}/inactive', [BusController::class, 'inactive']);
    });

    // Counters routes
    Route::prefix('counters')->group(function () {
        Route::get('/', [CounterController::class, 'index']);
        Route::get('/all-active', [CounterController::class, 'allActiveCounters']);
        Route::post('/', [CounterController::class, 'store']);
        Route::get('{id}', [CounterController::class, 'show']);
        Route::put('{id}', [CounterController::class, 'update']);
        Route::delete('{id}', [CounterController::class, 'destroy']);
        Route::patch('/{id}/active', [CounterController::class, 'active']);
        Route::patch('/{id}/inactive', [CounterController::class, 'inactive']);
    });

    // Seats routes
    Route::prefix('seats')->group(function () {
        Route::post('/', [SeatController::class, 'store']); // Create multiple seats under an existing seat plan
        Route::put('{id}', [SeatController::class, 'update']); // Update a specific seat by ID
        Route::delete('{id}', [SeatController::class, 'destroy']); // Delete a specific seat by ID
    });

    // Designations routes
    Route::prefix('designations')->group(function () {
        Route::get('/', [DesignationController::class, 'index']);
        Route::get('/all-active', [DesignationController::class, 'allActiveDesignations']);
        Route::post('/', [DesignationController::class, 'store']);
        Route::get('{id}', [DesignationController::class, 'show']);
        Route::put('{id}', [DesignationController::class, 'update']);
        Route::delete('{id}', [DesignationController::class, 'destroy']);
        Route::patch('/{id}/active', [DesignationController::class, 'active']);
        Route::patch('/{id}/inactive', [DesignationController::class, 'inactive']);
    });

    // Employees routes
    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::get('{id}', [EmployeeController::class, 'show']);
        Route::put('{id}', [EmployeeController::class, 'update']);
        Route::delete('{id}', [EmployeeController::class, 'destroy']);
        Route::patch('/{id}/active', [EmployeeController::class, 'active']);
        Route::patch('/{id}/inactive', [EmployeeController::class, 'inactive']);
    });

    // Coach Configurations routes
    Route::prefix('coach-configurations')->name('coach-configurations.')->group(function () {
        Route::get('/', [CoachConfigurationController::class, 'index'])->name('index');
        Route::get('/all-active', [CoachConfigurationController::class, 'allActive'])->name('all-active');
        Route::post('/', [CoachConfigurationController::class, 'store'])->name('store');

        // Utility routes (must come before /{id} to avoid being caught by the dynamic segment)
        Route::get('/schedule/{scheduleId}', [CoachConfigurationController::class, 'getBySchedule'])->name('by-schedule');
        Route::get('/coach/{coachId}', [CoachConfigurationController::class, 'getByCoach'])->name('by-coach');
        Route::get('/route/{routeId}', [CoachConfigurationController::class, 'getByRoute'])->name('by-route');

        Route::get('/{id}', [CoachConfigurationController::class, 'show'])->name('show');
        Route::put('/{id}', [CoachConfigurationController::class, 'update'])->name('update');
        Route::delete('/{id}', [CoachConfigurationController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/active', [CoachConfigurationController::class, 'active'])->name('active');
        Route::patch('/{id}/inactive', [CoachConfigurationController::class, 'inactive'])->name('inactive');
    });

    Route::prefix('trip-instances')->name('trip-instances.')->group(function () {
        // CRUD
        Route::get('/', [TripInstanceController::class, 'index'])->name('index');
        Route::get('/all-active', [TripInstanceController::class, 'allActive'])->name('all-active');
        Route::post('/', [TripInstanceController::class, 'store'])->name('store');
        Route::get('/{id}', [TripInstanceController::class, 'show'])->name('show');
        Route::put('/{id}', [TripInstanceController::class, 'update'])->name('update');
        Route::delete('/{id}', [TripInstanceController::class, 'destroy'])->name('destroy');

        // Status & actions
        Route::patch('/{id}/toggle-status', [TripInstanceController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{id}/active', [TripInstanceController::class, 'active'])->name('active');
        Route::patch('/{id}/inactive', [TripInstanceController::class, 'inactive'])->name('inactive');
        Route::patch('/{id}/migrate', [TripInstanceController::class, 'migrate'])->name('migrate');

        // Date & partition queries
        Route::get('/date/{date}', [TripInstanceSearchController::class, 'getByDate'])->name('by-date');
        Route::get('/today/all', [TripInstanceSearchController::class, 'getToday'])->name('today');
        Route::get('/date-range/{startDate}/{endDate}', [TripInstanceSearchController::class, 'getByDateRange'])->name('by-date-range');
        Route::get('/partition/{yearMonth}', [TripInstanceSearchController::class, 'getByPartition'])->name('by-partition');
        Route::get('/partitions/info', [TripInstanceSearchController::class, 'getPartitionInfo'])->name('partition-info');

        // Seat inventory
        Route::get('{id}/seat-inventory', [TripInstanceSeatInventoryController::class, 'getSeatInventory']);
        Route::post('{id}/seat-inventory', [TripInstanceSeatInventoryController::class, 'createSeatInventory']);
        Route::post('{id}/seat-inventory/block', [TripInstanceSeatInventoryController::class, 'blockSeat']);
        Route::post('{id}/seat-inventory/book', [TripInstanceSeatInventoryController::class, 'bookSeat']);
        Route::post('{id}/seat-inventory/release', [TripInstanceSeatInventoryController::class, 'releaseSeat']);
        Route::post('{id}/seat-inventory/cleanup-expired', [TripInstanceSeatInventoryController::class, 'cleanupExpiredBlocks']);
    });

    Route::get('search-trips', [TripInstanceSearchController::class, 'searchTrips']);

    Route::prefix('seat-requests')->group(function () {
        Route::post('/', [SeatRequestController::class, 'seatRequest'])->name('seat-requests.create');
        Route::post('/cancel', [SeatRequestController::class, 'removeSeatRequest']);
        Route::post('/cancel-issue', [SeatRequestController::class, 'removeAllSeatsFromIssue']);
    });

    Route::post('seat-booked-blocked-requests', [SeatRequestController::class, 'seatBookBlockRequest']);
    Route::post('seat-booked-blocked-cancel', [SeatRequestController::class, 'seatBookBlockCancel']);

    Route::prefix('seat-inventory')->name('seat-inventory.')->group(function () {

        // Trip-specific seat management
        Route::prefix('trips/{tripId}')->group(function () {
            // Get all seats for a trip
            Route::get('/seats', [SeatInventoryController::class, 'getTripSeats'])->name('trip.seats');

            // Create seat inventory for a trip
            Route::post('/seats', [SeatInventoryController::class, 'createTripSeats'])->name('trip.create-seats');

            // Get seat availability summary
            Route::get('/availability', [SeatInventoryController::class, 'getSeatAvailability'])->name('trip.availability');

            // Bulk update multiple seats
            Route::patch('/seats/bulk', [SeatInventoryController::class, 'bulkUpdateSeats'])->name('trip.bulk-update');

            // Individual seat actions
            Route::prefix('seats/{seatId}')->group(function () {
                Route::patch('/block', [SeatInventoryController::class, 'blockSeat'])->name('seat.block');
                Route::patch('/book', [SeatInventoryController::class, 'bookSeat'])->name('seat.book');
                Route::patch('/release', [SeatInventoryController::class, 'releaseSeat'])->name('seat.release');
                Route::patch('/cancel', [SeatInventoryController::class, 'cancelSeat'])->name('seat.cancel');
            });
        });

        // Utility routes
        Route::post('/cleanup-expired', [SeatInventoryController::class, 'cleanupExpiredBlocks'])->name('cleanup-expired');
        Route::get('/partitions/info', [SeatInventoryController::class, 'getPartitionInfo'])->name('partition-info');
    });

    // Customer routes
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::get('/all-active', [CustomerController::class, 'allActive']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('{id}', [CustomerController::class, 'show']);
        Route::get('{mobile}/by-mobile', [CustomerController::class, 'customerByMobile']);
        Route::patch('{mobile}/update-by-mobile', [CustomerController::class, 'updateByMobile']);
        Route::put('{id}', [CustomerController::class, 'update']);
        Route::delete('{id}', [CustomerController::class, 'destroy']);
    });

    // Booking routes
    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'index']);
        Route::post('/', [BookingController::class, 'store']);
        Route::get('/{id}', [BookingController::class, 'show']);
        Route::put('/{id}', [BookingController::class, 'update']);
    });

    Route::prefix('reports')->group(function () {
        Route::get('/coach-trips', [CoachReportController::class, 'coach_trips_report']);
        Route::get('/coach-sales', [CoachReportController::class, 'coach_sales_report']);
    });

    /**
     * Website routes
     */

    /**
     * Offer and promos routes
     */
    // TODO: api crud update
    Route::prefix('offer-and-promos')->group(function () {
        Route::get('/', [OfferAndPromoController::class, 'index']);
        Route::get('/all-active', [OfferAndPromoController::class, 'allActive']);
        Route::post('/', [OfferAndPromoController::class, 'store']);
        Route::get('{id}', [OfferAndPromoController::class, 'show']);
        Route::put('{id}', [OfferAndPromoController::class, 'update']);
        Route::delete('{id}', [OfferAndPromoController::class, 'destroy']);
    });

    /**
     * Customer reviews routes
     */
    // TODO: api crud update
    Route::prefix('customer-reviews')->group(function () {
        Route::get('/', [CustomerReviewController::class, 'index']);
        Route::get('/all-active', [CustomerReviewController::class, 'allActive']);
        Route::post('/', [CustomerReviewController::class, 'store']);
        Route::get('{id}', [CustomerReviewController::class, 'show']);
        Route::put('{id}', [CustomerReviewController::class, 'update']);
        Route::delete('{id}', [CustomerReviewController::class, 'destroy']);
    });

    /**
     * Faq routes
     */
    // TODO: api crud update
    Route::prefix('faqs')->group(function () {
        Route::get('/', [FaqController::class, 'index']);
        Route::get('/all-active', [FaqController::class, 'allActive']);
        Route::post('/', [FaqController::class, 'store']);
        Route::get('{id}', [FaqController::class, 'show']);
        Route::put('{id}', [FaqController::class, 'update']);
        Route::delete('{id}', [FaqController::class, 'destroy']);
    });

});
