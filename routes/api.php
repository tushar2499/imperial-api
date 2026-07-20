<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthenticateUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\CoachConfigurationController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\CustomerAuthController;
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
use App\Http\Controllers\GuestSeatHoldController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SeatRequestController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\TransportRouteController;
use App\Http\Controllers\TripInstanceController;
use App\Http\Controllers\TripInstanceSearchController;
use App\Http\Controllers\TripInstanceSeatInventoryController;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::withoutMiddleware(['auth:api'])->group(function () {

    // Admin auth
    Route::prefix('admin')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    // Customer (user) auth
    Route::prefix('user')->group(function () {
        Route::post('register', [CustomerAuthController::class, 'register']);
        Route::post('login', [CustomerAuthController::class, 'login']);
    });

    // Test route
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
    Route::get('info', [App\Http\Controllers\Api\Public\WebsiteSettingController::class, 'index']);
    Route::get('system-info', [App\Http\Controllers\Api\Public\SystemInfoController::class, 'index']);

    // Public trip instance (seat plan view — no auth needed)
    Route::get('trip-instances/{id}', [App\Http\Controllers\Api\Public\TripInstanceController::class, 'show']);

    // Public trip search
    Route::get('search-trips', [App\Http\Controllers\Api\Public\TripSearchController::class, 'searchTrips']);
});

// Guest seat hold — public (no auth required, guest_token is the ownership proof)
// Rate-limited to prevent abuse: 30 requests per minute per IP
Route::middleware(['throttle:30,1'])->prefix('guest/seat-hold')->name('guest.seat-hold.')->group(function () {
    Route::post('/', [GuestSeatHoldController::class, 'hold'])->name('hold');
    Route::post('/release', [GuestSeatHoldController::class, 'release'])->name('release');
    Route::post('/release-issue', [GuestSeatHoldController::class, 'releaseIssue'])->name('release-issue');
});

// Customer (user) protected routes
Route::middleware(['auth:customer', 'customer.active'])->prefix('user')->group(function () {
    Route::post('refresh-token', [CustomerAuthController::class, 'refreshToken']);
    Route::post('logout', [CustomerAuthController::class, 'logout']);
    Route::get('me', [CustomerAuthController::class, 'me']);
    Route::get('profile', [CustomerAuthController::class, 'showProfile']);
    Route::put('profile', [CustomerAuthController::class, 'updateProfile']);
    Route::post('profile/photo', [CustomerAuthController::class, 'updatePhoto']);
    Route::post('profile/password', [CustomerAuthController::class, 'updatePassword']);

    // Claim a guest seat hold after login
    Route::post('seat-hold/claim', [GuestSeatHoldController::class, 'claim'])->name('seat-hold.claim');

    // Confirm a claimed seat hold — creates Booking record (direct, no payment gateway)
    Route::post('seat-hold/confirm', [GuestSeatHoldController::class, 'confirm'])->name('seat-hold.confirm');

    // Initiate SSLCommerz payment session (auth required — hold must be claimed first)
    Route::post('payment/initiate', [PaymentController::class, 'initiate'])->name('payment.initiate');
});

// SSLCommerz payment — public POST routes (no auth, no CSRF)
Route::prefix('payment')->name('payment.')->group(function () {
    // Called by the Next.js /api/sslcommerz/success route handler (server-to-server)
    // Validates val_id with SSLCommerz, confirms booking, returns JSON
    Route::post('confirm', [PaymentController::class, 'confirm'])->name('confirm');

    // IPN: server-to-server from SSLCommerz servers (secondary safety net)
    Route::post('ipn', [PaymentController::class, 'ipn'])->name('ipn');

    // Legacy browser-redirect handlers kept for fallback; callbacks now go via frontend
    Route::post('success', [PaymentController::class, 'success'])->name('success');
    Route::post('fail',    [PaymentController::class, 'fail'])->name('fail');
    Route::post('cancel',  [PaymentController::class, 'cancel'])->name('cancel');
});

// Admin protected routes
Route::middleware(['auth:api', 'active'])->prefix('admin')->group(function () {
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

    // Seat plans
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
        Route::post('/', [SeatController::class, 'store']);
        Route::put('{id}', [SeatController::class, 'update']);
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
        Route::get('/', [TripInstanceController::class, 'index'])->name('index');
        Route::get('/all-active', [TripInstanceController::class, 'allActive'])->name('all-active');
        Route::post('/', [TripInstanceController::class, 'store'])->name('store');
        Route::get('/{id}', [TripInstanceController::class, 'show'])->name('show');
        Route::put('/{id}', [TripInstanceController::class, 'update'])->name('update');
        Route::delete('/{id}', [TripInstanceController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [TripInstanceController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{id}/active', [TripInstanceController::class, 'active'])->name('active');
        Route::patch('/{id}/inactive', [TripInstanceController::class, 'inactive'])->name('inactive');
        Route::patch('/{id}/migrate', [TripInstanceController::class, 'migrate'])->name('migrate');
        Route::get('/date/{date}', [TripInstanceSearchController::class, 'getByDate'])->name('by-date');
        Route::get('/today/all', [TripInstanceSearchController::class, 'getToday'])->name('today');
        Route::get('/date-range/{startDate}/{endDate}', [TripInstanceSearchController::class, 'getByDateRange'])->name('by-date-range');
        Route::get('/partition/{yearMonth}', [TripInstanceSearchController::class, 'getByPartition'])->name('by-partition');
        Route::get('/partitions/info', [TripInstanceSearchController::class, 'getPartitionInfo'])->name('partition-info');
        Route::get('{id}/seat-inventory', [TripInstanceSeatInventoryController::class, 'getSeatInventory']);
        Route::post('{id}/seat-inventory', [TripInstanceSeatInventoryController::class, 'createSeatInventory']);
        Route::post('{id}/seat-inventory/block', [TripInstanceSeatInventoryController::class, 'blockSeat']);
        Route::post('{id}/seat-inventory/book', [TripInstanceSeatInventoryController::class, 'bookSeat']);
        Route::post('{id}/seat-inventory/release', [TripInstanceSeatInventoryController::class, 'releaseSeat']);
        Route::post('{id}/seat-inventory/cleanup-expired', [TripInstanceSeatInventoryController::class, 'cleanupExpiredBlocks']);
    });

    Route::get('search-trips', [TripInstanceSearchController::class, 'searchTrips']);

    Route::prefix('seat-requests')->name('seat-requests.')->group(function () {
        Route::post('/', [SeatRequestController::class, 'seatRequest'])->name('create');
        Route::post('/cancel', [SeatRequestController::class, 'removeSeatRequest'])->name('cancel');
        Route::post('/cancel-issue', [SeatRequestController::class, 'removeAllSeatsFromIssue'])->name('cancel-issue');
    });

    Route::post('seat-booked-blocked-requests', [SeatRequestController::class, 'seatBookBlockRequest']);
    Route::post('seat-booked-blocked-cancel', [SeatRequestController::class, 'seatBookBlockCancel']);

    Route::prefix('seat-inventory')->name('seat-inventory.')->group(function () {
        Route::prefix('trips/{tripId}')->group(function () {
            Route::get('/seats', [SeatInventoryController::class, 'getTripSeats'])->name('trip.seats');
            Route::post('/seats', [SeatInventoryController::class, 'createTripSeats'])->name('trip.create-seats');
            Route::get('/availability', [SeatInventoryController::class, 'getSeatAvailability'])->name('trip.availability');
            Route::patch('/seats/bulk', [SeatInventoryController::class, 'bulkUpdateSeats'])->name('trip.bulk-update');
            Route::prefix('seats/{seatId}')->group(function () {
                Route::patch('/block', [SeatInventoryController::class, 'blockSeat'])->name('seat.block');
                Route::patch('/book', [SeatInventoryController::class, 'bookSeat'])->name('seat.book');
                Route::patch('/release', [SeatInventoryController::class, 'releaseSeat'])->name('seat.release');
                Route::patch('/cancel', [SeatInventoryController::class, 'cancelSeat'])->name('seat.cancel');
            });
        });
        Route::post('/cleanup-expired', [SeatInventoryController::class, 'cleanupExpiredBlocks'])->name('cleanup-expired');
        Route::get('/partitions/info', [SeatInventoryController::class, 'getPartitionInfo'])->name('partition-info');
    });

    // Customer management (admin)
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::get('/all-active', [CustomerController::class, 'allActive']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/by-mobile/{mobile}', [CustomerController::class, 'customerByMobile']);
        Route::patch('/by-mobile/{mobile}', [CustomerController::class, 'updateByMobile']);
        Route::get('{id}', [CustomerController::class, 'show']);
        Route::put('{id}', [CustomerController::class, 'update']);
        Route::delete('{id}', [CustomerController::class, 'destroy']);
        Route::patch('/{id}/active', [CustomerController::class, 'active']);
        Route::patch('/{id}/inactive', [CustomerController::class, 'inactive']);
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

    Route::prefix('system-settings')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\SystemSettingController::class, 'index']);
        Route::patch('/', [App\Http\Controllers\Api\SystemSettingController::class, 'update']);
    });

    Route::prefix('website-settings')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\WebsiteSettingController::class, 'index']);
        Route::patch('/', [App\Http\Controllers\Api\WebsiteSettingController::class, 'update']);
    });

    Route::prefix('offer-and-promos')->group(function () {
        Route::get('/', [OfferAndPromoController::class, 'index']);
        Route::get('/all-active', [OfferAndPromoController::class, 'allActive']);
        Route::post('/', [OfferAndPromoController::class, 'store']);
        Route::get('{id}', [OfferAndPromoController::class, 'show']);
        Route::put('{id}', [OfferAndPromoController::class, 'update']);
        Route::delete('{id}', [OfferAndPromoController::class, 'destroy']);
    });

    Route::prefix('customer-reviews')->group(function () {
        Route::get('/', [CustomerReviewController::class, 'index']);
        Route::get('/all-active', [CustomerReviewController::class, 'allActive']);
        Route::post('/', [CustomerReviewController::class, 'store']);
        Route::get('{id}', [CustomerReviewController::class, 'show']);
        Route::put('{id}', [CustomerReviewController::class, 'update']);
        Route::delete('{id}', [CustomerReviewController::class, 'destroy']);
    });

    Route::prefix('faqs')->group(function () {
        Route::get('/', [FaqController::class, 'index']);
        Route::get('/all-active', [FaqController::class, 'allActive']);
        Route::post('/', [FaqController::class, 'store']);
        Route::get('{id}', [FaqController::class, 'show']);
        Route::put('{id}', [FaqController::class, 'update']);
        Route::delete('{id}', [FaqController::class, 'destroy']);
    });
});
