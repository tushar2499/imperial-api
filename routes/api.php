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
use App\Http\Controllers\RouteController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\SeatInventoryController;
use App\Http\Controllers\SeatPlanController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\TripInstanceController;
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
        Route::get('/popular-routes', [RouteController::class, 'allPopularRoutes']);
        Route::post('/', [RouteController::class, 'store']);
        Route::get('{id}', [RouteController::class, 'show']);
        Route::put('{id}', [RouteController::class, 'update']);
        Route::patch('/update-popular-positions', [RouteController::class, 'updatePopularPositions']);
        Route::delete('{id}', [RouteController::class, 'destroy']);
        Route::get('{id}/counters', [RouteController::class, 'routeWiseCounters']);
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
        Route::get('/all-active', [CounterController::class, 'allActiveCounters']);
        Route::post('/', [CounterController::class, 'store']);
        Route::get('{id}', [CounterController::class, 'show']);
        Route::put('{id}', [CounterController::class, 'update']);
        Route::delete('{id}', [CounterController::class, 'destroy']);
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

    Route::prefix('trip-instances')->name('trip-instances.')->group(function () {
        // Basic CRUD operations
        Route::get('/', [TripInstanceController::class, 'index'])->name('index');
        Route::post('/', [TripInstanceController::class, 'store'])->name('store');
        Route::get('/{id}', [TripInstanceController::class, 'show'])->name('show');
        Route::put('/{id}', [TripInstanceController::class, 'update'])->name('update');
        Route::delete('/{id}', [TripInstanceController::class, 'destroy'])->name('destroy');

        // Date-based queries
        Route::get('/date/{date}', [TripInstanceController::class, 'getByDate'])->name('by-date');
        Route::get('/today/all', [TripInstanceController::class, 'getToday'])->name('today');
        Route::get('/date-range/{startDate}/{endDate}', [TripInstanceController::class, 'getByDateRange'])->name('by-date-range');

        // Partition-specific operations
        Route::get('/partition/{yearMonth}', [TripInstanceController::class, 'getByPartition'])->name('by-partition');
        Route::get('/partitions/info', [TripInstanceController::class, 'getPartitionInfo'])->name('partition-info');

        // Trip actions
        Route::patch('/{id}/toggle-status', [TripInstanceController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{id}/migrate', [TripInstanceController::class, 'migrate'])->name('migrate');

        Route::get('{id}/seat-inventory', [TripInstanceController::class, 'getSeatInventory']);
        Route::post('{id}/seat-inventory', [TripInstanceController::class, 'createSeatInventory']);
        Route::post('{id}/seat-inventory/block', [TripInstanceController::class, 'blockSeat']);
        Route::post('{id}/seat-inventory/book', [TripInstanceController::class, 'bookSeat']);
        Route::post('{id}/seat-inventory/release', [TripInstanceController::class, 'releaseSeat']);
        Route::post('{id}/seat-inventory/cleanup-expired', [TripInstanceController::class, 'cleanupExpiredBlocks']);
    });

    Route::get('search-trips', [TripInstanceController::class, 'searchTrips']);

    Route::group(['prefix' => 'seat-requests'], function () {
        // Request a seat (block for 5 minutes)
        Route::post('/', [TripInstanceController::class, 'seatRequest'])->name('seat-requests.create');

        // cancel a seat request (release the block)
        Route::post('/cancel', [TripInstanceController::class, 'removeSeatRequest']);
        Route::post('/cancel-issue', [TripInstanceController::class, 'removeAllSeatsFromIssue']);
    });

    Route::post('seat-booked-blocked-requests', [TripInstanceController::class, 'seatBookBlockRequest']);
    Route::post('seat-booked-blocked-cancel', [TripInstanceController::class, 'seatBookBlockCancel']);

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
    Route::prefix('offer-and-promos')->group(function () {
        Route::get('/', [OfferAndPromoController::class, 'index']);
        Route::post('/', [OfferAndPromoController::class, 'store']);
        Route::get('{id}', [OfferAndPromoController::class, 'show']);
        Route::put('{id}', [OfferAndPromoController::class, 'update']);
        Route::delete('{id}', [OfferAndPromoController::class, 'destroy']);
    });

    /**
     * Customer reviews routes
     */
    Route::prefix('customer-reviews')->group(function () {
        Route::get('/', [CustomerReviewController::class, 'index']);
        Route::post('/', [CustomerReviewController::class, 'store']);
        Route::get('{id}', [CustomerReviewController::class, 'show']);
        Route::put('{id}', [CustomerReviewController::class, 'update']);
        Route::delete('{id}', [CustomerReviewController::class, 'destroy']);
    });

    /**
     * Faq routes
     */
    Route::prefix('faqs')->group(function () {
        Route::get('/', [FaqController::class, 'index']);
        Route::post('/', [FaqController::class, 'store']);
        Route::get('{id}', [FaqController::class, 'show']);
        Route::put('{id}', [FaqController::class, 'update']);
        Route::delete('{id}', [FaqController::class, 'destroy']);
    });

});
