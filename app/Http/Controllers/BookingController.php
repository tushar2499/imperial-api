<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\SeatInventory;
use App\Models\TripInstance;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of Booking.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = min((int) $request->get('per_page', 15), 1000); // Cap at 1000
            $page = max((int) $request->get('page', 1), 1); // Minimum page 1
            $searchTerm = $request->get('search');

            $query = Booking::with([
                'customer',
                'boarding',
                'dropping',
                'route',
                'bookingDetails.seat',
            ])
                ->when($searchTerm, function ($query, $searchTerm) {
                    $query->whereHas('customer', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('mobile', 'like', "%{$searchTerm}%");
                    })
                        ->orWhere('pnr_number', 'like', "%{$searchTerm}%");
                })
                ->orderBy('created_at', 'desc');

            $bookings = $query->paginate($perPage, ['*'], 'page', $page);

            return $this->successResponse($bookings, 'Bookings retrieved successfully');
        } catch (\Exception $e) {
            \Log::error('Booking retrieval failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Failed to retrieve bookings', 500);
        }
    }

    /**
     * Store a newly created Booking.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'gender' => 'nullable|string|max:255',
            'age' => 'nullable|numeric',
            'address' => 'nullable|string|max:255',
            'passport_no' => 'nullable|string|max:255',
            'nid' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',

            'trip_id' => 'required|integer',
            'type' => 'required|integer',
            'date' => 'required|date|date_format:Y-m-d',
            'time' => 'required|date_format:H:i:s',
            'route_id' => 'required|integer|exists:transport_routes,id',
            'boarding_id' => 'nullable|integer|exists:trip_boarding_droppings,id',
            'dropping_id' => 'nullable|integer|exists:trip_boarding_droppings,id',
            'expire_date' => 'nullable|date|date_format:Y-m-d',
            'expire_time' => 'nullable|date_format:H:i',

            'booking_details' => 'required|array',
            'booking_details.*.seat_inventory_id' => 'required|integer',
            'booking_details.*.seat_id' => 'required|integer|exists:seats,id',
            'booking_details.*.price' => 'required|numeric',
            'booking_details.*.discount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $authUserId = auth()->user()->id;

            DB::beginTransaction();

            // Get trip instance with all relationships for comprehensive response
            $tripInstance = TripInstance::findAcrossPartitions($request->input('trip_id'));
            if (! $tripInstance) {
                return $this->errorResponse('Trip not found', 404);
            }

            // Load trip relationships including fare
            $tripInstance->load([
                'coach', 'bus', 'schedule', 'seatPlan', 'route',
                'driver', 'supervisor', 'boardingDroppings',
            ]);

            // Find or create customer (using mobile instead of mobile_number)
            $customer = Customer::where('mobile', $request->input('mobile'))->first();

            if ($customer) {
                $customer->update([
                    'name' => $request->input('name'),
                    'gender' => $request->input('gender'),
                    'age' => $request->input('age'),
                    'address' => $request->input('address'),
                    'passport_no' => $request->input('passport_no'),
                    'nid' => $request->input('nid'),
                    'nationality' => $request->input('nationality'),
                    'email' => $request->input('email'),
                    'updated_by' => $authUserId,
                ]);
                $customer->refresh();
            } else {
                $customer = Customer::create([
                    'mobile' => $request->input('mobile'),
                    'name' => $request->input('name'),
                    'gender' => $request->input('gender'),
                    'age' => $request->input('age'),
                    'address' => $request->input('address'),
                    'passport_no' => $request->input('passport_no'),
                    'nid' => $request->input('nid'),
                    'nationality' => $request->input('nationality', 'Bangladeshi'),
                    'email' => $request->input('email'),
                    'status' => 1,
                    'created_by' => $authUserId,
                ]);
            }

            $total_tickets = 0;
            $total_price = 0;
            $total_discount = 0;
            $total_amount = 0;

            $type = $request->input('type');
            $expire_date = null;
            $expire_time = null;
            if ($type == 2 || $type == 3) {
                $expire_date_input = $request->input('expire_date');
                $expire_time_input = $request->input('expire_time');
                $expire_date = $expire_date_input && strtotime($expire_date_input) ? date('Y-m-d', strtotime($expire_date_input)) : null;
                $expire_time = $expire_time_input && strtotime($expire_time_input) ? date('H:i:s', strtotime($expire_time_input)) : null;
            }

            // Prepare booking data
            $bookingData = [
                'customer_id' => $customer->id,
                'pnr_number' => $this->generateUniquePNR(),
                'trip_id' => $request->input('trip_id'),
                'type' => $request->input('type'),
                'date' => $request->input('date'),
                'time' => $request->input('time'),
                'note' => $request->input('note') ?? null,
                'route_id' => $request->input('route_id'),
                'boarding_id' => $request->input('boarding_id'),
                'dropping_id' => $request->input('dropping_id'),
                'total_price' => 0,
                'total_discount' => 0,
                'total_amount' => 0,
                'expire_date' => $expire_date,
                'expire_time' => $expire_time,
                'created_by' => $authUserId,
            ];

            $allBookingDetailData = [];
            $seatDetails = [];

            // Process booking details and collect seat information
            foreach ($request->input('booking_details') as $detail) {
                $discount = $detail['discount'] ?? 0;
                $amount = $detail['price'] - $discount;

                // Get seat inventory with seat details
                $seatInventory = SeatInventory::forTrip($request->input('trip_id'))
                    ->where('id', $detail['seat_inventory_id'])
                    ->with('seat')
                    ->first();

                if (! $seatInventory) {
                    return $this->errorResponse('Seat inventory not found', 404);
                }

                if ($seatInventory->booking_status != SeatInventory::STATUS_AVAILABLE) {
                    return $this->errorResponse('Seat is not available for booking', 400);
                }

                // Collect seat details for response
                $seatDetails[] = [
                    'seat_inventory_id' => $detail['seat_inventory_id'],
                    'seat_id' => $detail['seat_id'],
                    'seat_number' => $seatInventory->seat->seat_number ?? null,
                    'row_position' => $seatInventory->seat->row_position ?? null,
                    'col_position' => $seatInventory->seat->col_position ?? null,
                    'seat_type' => $seatInventory->seat->seat_type ?? null,
                    'price' => $detail['price'],
                    'discount' => $discount,
                    'amount' => $amount,
                    'status' => 'sold', // Indicate that this seat is now sold
                ];

                $allBookingDetailData[] = [
                    'seat_inventory_id' => $detail['seat_inventory_id'],
                    'seat_id' => $detail['seat_id'],
                    'price' => $detail['price'],
                    'discount' => $discount,
                    'amount' => $amount,
                ];

                $total_price += $detail['price'];
                $total_tickets += 1;
                $total_discount += $discount;
                $total_amount += $amount;
            }

            // Update booking totals
            $bookingData['total_price'] = $total_price;
            $bookingData['total_discount'] = $total_discount;
            $bookingData['total_amount'] = $total_amount;

            // Create booking
            $booking = Booking::create($bookingData);

            // Get trip instance to determine trip date and schedule
            $tripInstance = TripInstance::findAcrossPartitions($request->trip_id);
            if (! $tripInstance) {
                return $this->errorResponse('Trip not found', 404);
            }

            // Calculate blocked_until based on status
            $blockedUntilCarbon = null;
            $actionMessage = '';

            // For booked status: block until trip_date + schedule - 1 hour
            $tripDateTime = Carbon::parse($tripInstance->trip_date);

            // Load schedule to get departure time
            $tripInstance->load('schedule');
            if ($tripInstance->schedule && isset($tripInstance->schedule->name)) {
                $departureTime = Carbon::parse($tripInstance->schedule->name);
                $tripDateTime->setTime($departureTime->hour, $departureTime->minute);
            } else {
                // Default to 8:00 AM if no schedule time found
                $tripDateTime->setTime(8, 0);
            }

            if ($request->type == 2 || $request->type == 4) { // Booked
                // Block until 1 hour before departure
                $blockedUntilCarbon = $tripDateTime->subHour();
                $actionMessage = 'Seat booked/sold successfully until 1 hour before departure';

            } elseif ($request->type == 3) { // Blocked
                // For blocked status: block for 5 minutes (temporary)
                $blockedUntilCarbon = now()->addMinutes(5);
                $actionMessage = 'Seat blocked successfully';
            }

            // Create booking details and update seat inventory to SOLD status
            foreach ($allBookingDetailData as $bookingDetailData) {
                $seatInventory = SeatInventory::forTrip($booking->trip_id)
                    ->where('id', $bookingDetailData['seat_inventory_id'])
                    ->first();

                if ($seatInventory && $seatInventory->booking_status == SeatInventory::STATUS_AVAILABLE) {
                    // Mark seat as SOLD instead of BOOKED
                    $seatInventory->update([
                        'booking_status' => $request->type, // Changed from STATUS_BOOKED to STATUS_SOLD
                        'booking_id' => $booking->id,
                        'blocked_until' => $blockedUntilCarbon, // Clear any blocking
                        'last_locked_user_id' => auth()->user()->id,
                        'updated_by' => $authUserId,
                    ]);
                } else {
                    DB::rollback();

                    return $this->errorResponse('Seat inventory is not available', 400);
                }

                $booking->bookingDetails()->create($bookingDetailData);
            }

            // Update customer statistics if these fields exist
            if ($customer->total_trips && $customer->total_tickets) {
                $customer->update([
                    'total_trips' => $customer->total_trips + 1,
                    'total_tickets' => $customer->total_tickets + $total_tickets,
                ]);
            }

            DB::commit();

            // Get boarding and dropping point details from booking
            $boardingInfo = null;
            $droppingInfo = null;

            if ($booking->boarding_id) {
                $boardingPoint = \DB::table('trip_boarding_droppings')
                    ->leftJoin('counters', 'trip_boarding_droppings.counter_id', '=', 'counters.id')
                    ->leftJoin('districts', 'counters.district_id', '=', 'districts.id')
                    ->where('trip_boarding_droppings.id', $booking->boarding_id)
                    ->select('trip_boarding_droppings.*', 'counters.address as counter_name', 'counters.land_mark as counter_location', 'districts.name as district_name')
                    ->first();

                if ($boardingPoint) {
                    $boardingInfo = [
                        'id' => $boardingPoint->id,
                        'trip_id' => $boardingPoint->trip_id,
                        'time' => $boardingPoint->time,
                        'counter_id' => $boardingPoint->counter_id,
                        'counter_name' => $boardingPoint->counter_name,
                        'counter_location' => $boardingPoint->counter_location,
                        'district_name' => $boardingPoint->district_name,
                    ];
                }
            }

            if ($booking->dropping_id) {
                $droppingPoint = \DB::table('trip_boarding_droppings')
                    ->leftJoin('counters', 'trip_boarding_droppings.counter_id', '=', 'counters.id')
                    ->leftJoin('districts', 'counters.district_id', '=', 'districts.id')
                    ->where('trip_boarding_droppings.id', $booking->dropping_id)
                    ->select('trip_boarding_droppings.*', 'counters.address as counter_name', 'counters.land_mark as counter_location', 'districts.name as district_name')
                    ->first();

                if ($droppingPoint) {
                    $droppingInfo = [
                        'id' => $droppingPoint->id,
                        'trip_id' => $droppingPoint->trip_id,
                        'time' => $droppingPoint->time,
                        'counter_id' => $droppingPoint->counter_id,
                        'counter_name' => $droppingPoint->counter_name,
                        'counter_location' => $droppingPoint->counter_location,
                        'district_name' => $droppingPoint->district_name,
                    ];
                }
            }

            // Prepare comprehensive response data
            $responseData = [
                'booking' => [
                    'id' => $booking->id,
                    'pnr_number' => $booking->pnr_number,
                    'trip_id' => $booking->trip_id,
                    'type' => $booking->type,
                    'date' => $booking->date,
                    'time' => $booking->time,
                    'route_id' => $booking->route_id,
                    'boarding_id' => $booking->boarding_id,
                    'dropping_id' => $booking->dropping_id,
                    'total_tickets' => $total_tickets,
                    'total_price' => $total_price,
                    'total_discount' => $total_discount,
                    'total_amount' => $total_amount,
                    'status' => 'confirmed', // Booking is confirmed
                    'created_at' => $booking->created_at,
                ],
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'mobile' => $customer->mobile,
                    'email' => $customer->email,
                    'gender' => $customer->gender,
                    'age' => $customer->age,
                    'address' => $customer->address,
                    'passport_no' => $customer->passport_no,
                    'nid' => $customer->nid,
                    'nationality' => $customer->nationality,
                ],
                'boarding_info' => $boardingInfo,
                'dropping_info' => $droppingInfo,
                'trip_details' => [
                    'trip_id' => $tripInstance->id,
                    'trip_date' => $tripInstance->trip_date->format('Y-m-d'),
                    'status' => $tripInstance->status,
                    'coach_type' => $tripInstance->coach_type,
                    'coach_type_name' => $tripInstance->coach_type_name,

                    // Coach details
                    'coach' => $tripInstance->coach ? [
                        'id' => $tripInstance->coach->id,
                        'coach_no' => $tripInstance->coach->coach_no,
                        'status' => $tripInstance->coach->status,
                    ] : null,

                    // Bus details
                    'bus' => $tripInstance->bus ? [
                        'id' => $tripInstance->bus->id,
                        'registration_number' => $tripInstance->bus->registration_number,
                        'manufacturer_company' => $tripInstance->bus->manufacturer_company,
                        'model_year' => $tripInstance->bus->model_year,
                    ] : null,

                    // Route details
                    'route' => $tripInstance->route ? [
                        'id' => $tripInstance->route->id,
                        'start_id' => $tripInstance->route->start_id,
                        'end_id' => $tripInstance->route->end_id,
                        'distance' => $tripInstance->route->distance,
                        'duration' => $tripInstance->route->duration,
                    ] : null,

                    // Schedule details
                    'schedule' => $tripInstance->schedule ? [
                        'id' => $tripInstance->schedule->id,
                        'name' => $tripInstance->schedule->name,
                    ] : null,

                    // Seat plan details
                    'seat_plan' => $tripInstance->seatPlan ? [
                        'id' => $tripInstance->seatPlan->id,
                        'name' => $tripInstance->seatPlan->name,
                        'floor' => $tripInstance->seatPlan->floor,
                        'rows' => $tripInstance->seatPlan->rows,
                        'cols' => $tripInstance->seatPlan->cols,
                    ] : null,

                    // Driver details
                    'driver' => $tripInstance->driver ? [
                        'id' => $tripInstance->driver->id,
                        'name' => $tripInstance->driver->name,
                        'contact' => $tripInstance->driver->contact,
                        'license' => $tripInstance->driver->license,
                    ] : null,

                    // Supervisor details
                    'supervisor' => $tripInstance->supervisor ? [
                        'id' => $tripInstance->supervisor->id,
                        'name' => $tripInstance->supervisor->name,
                        'contact' => $tripInstance->supervisor->contact,
                    ] : null,
                ],
                'sold_seats' => $seatDetails, // Changed from 'booked_seats' to 'sold_seats'
                'seat_status_summary' => [
                    'total_seats_sold' => count($seatDetails),
                    'seat_status' => 'sold',
                    'payment_status' => 'completed', // Assuming payment is completed on booking
                ],
            ];

            return $this->successResponse($responseData, 'Booking created successfully and seats marked as sold', 201);

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to create booking: '.$e->getMessage(), 500);
        }
    }

    /**
     * Display the specified Booking.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $booking = Booking::with([
                'customer',
                'boarding',
                'dropping',
                'route',
                'bookingDetails',
                'bookingDetails.seat',
            ])->find($id);

            if (! $booking) {
                return $this->errorResponse('Booking not found', 404);
            }

            // Get trip details
            $tripInstance = TripInstance::findAcrossPartitions($booking->trip_id);
            if ($tripInstance) {
                $tripInstance->load([
                    'coach', 'bus', 'schedule', 'seatPlan', 'route',
                    'driver', 'supervisor', 'boardingDroppings',
                ]);
            }

            // Get boarding and dropping point details from booking
            $boardingInfo = null;
            $droppingInfo = null;

            if ($booking->boarding_id) {
                $boardingPoint = \DB::table('trip_boarding_droppings')
                    ->leftJoin('counters', 'trip_boarding_droppings.counter_id', '=', 'counters.id')
                    ->leftJoin('districts', 'counters.district_id', '=', 'districts.id')
                    ->where('trip_boarding_droppings.id', $booking->boarding_id)
                    ->select('trip_boarding_droppings.*', 'counters.address as counter_name', 'counters.land_mark as counter_location', 'districts.name as district_name')
                    ->first();

                if ($boardingPoint) {
                    $boardingInfo = [
                        'id' => $boardingPoint->id,
                        'trip_id' => $boardingPoint->trip_id,
                        'time' => $boardingPoint->time,
                        'counter_id' => $boardingPoint->counter_id,
                        'counter_name' => $boardingPoint->counter_name,
                        'counter_location' => $boardingPoint->counter_location,
                        'district_name' => $boardingPoint->district_name,
                    ];
                }
            }

            if ($booking->dropping_id) {
                $droppingPoint = \DB::table('trip_boarding_droppings')
                    ->leftJoin('counters', 'trip_boarding_droppings.counter_id', '=', 'counters.id')
                    ->leftJoin('districts', 'counters.district_id', '=', 'districts.id')
                    ->where('trip_boarding_droppings.id', $booking->dropping_id)
                    ->select('trip_boarding_droppings.*', 'counters.address as counter_name', 'counters.land_mark as counter_location', 'districts.name as district_name')
                    ->first();

                if ($droppingPoint) {
                    $droppingInfo = [
                        'id' => $droppingPoint->id,
                        'trip_id' => $droppingPoint->trip_id,
                        'time' => $droppingPoint->time,
                        'counter_id' => $droppingPoint->counter_id,
                        'counter_name' => $droppingPoint->counter_name,
                        'counter_location' => $droppingPoint->counter_location,
                        'district_name' => $boardingPoint->district_name,
                    ];
                }
            }

            $responseData = [
                'booking' => [
                    'id' => $booking->id,
                    'pnr_number' => $booking->pnr_number,
                    'trip_id' => $booking->trip_id,
                    'type' => $booking->type,
                    'date' => $booking->date,
                    'time' => $booking->time,
                    'note' => $booking->note,
                    'expire_date' => $booking->expire_date,
                    'expire_time' => $booking->expire_time,
                    'route_id' => $booking->route_id,
                    'boarding_id' => $booking->boarding_id,
                    'dropping_id' => $booking->dropping_id,
                    'total_price' => $booking->total_price,
                    'total_discount' => $booking->total_discount,
                    'total_amount' => $booking->total_amount,
                    'created_at' => $booking->created_at,
                ],
                'customer' => [
                    'id' => $booking->customer->id,
                    'name' => $booking->customer->name,
                    'mobile' => $booking->customer->mobile,
                    'email' => $booking->customer->email,
                    'gender' => $booking->customer->gender,
                    'age' => $booking->customer->age,
                    'address' => $booking->customer->address,
                    'passport_no' => $booking->customer->passport_no,
                    'nid' => $booking->customer->nid,
                    'nationality' => $booking->customer->nationality,
                ],
                'boarding_info' => $boardingInfo,
                'dropping_info' => $droppingInfo,
                'trip_details' => [
                    'trip_id' => $tripInstance->id,
                    'trip_date' => $tripInstance->trip_date->format('Y-m-d'),
                    'status' => $tripInstance->status,
                    'coach_type' => $tripInstance->coach_type,
                    'coach_type_name' => $tripInstance->coach_type_name,

                    // Coach details
                    'coach' => $tripInstance->coach ? [
                        'id' => $tripInstance->coach->id,
                        'coach_no' => $tripInstance->coach->coach_no,
                        'status' => $tripInstance->coach->status,
                    ] : null,

                    // Bus details
                    'bus' => $tripInstance->bus ? [
                        'id' => $tripInstance->bus->id,
                        'registration_number' => $tripInstance->bus->registration_number,
                        'manufacturer_company' => $tripInstance->bus->manufacturer_company,
                        'model_year' => $tripInstance->bus->model_year,
                    ] : null,

                    // Route details
                    'route' => $tripInstance->route ? [
                        'id' => $tripInstance->route->id,
                        'start_id' => $tripInstance->route->start_id,
                        'end_id' => $tripInstance->route->end_id,
                        'distance' => $tripInstance->route->distance,
                        'duration' => $tripInstance->route->duration,
                    ] : null,

                    // Schedule details
                    'schedule' => $tripInstance->schedule ? [
                        'id' => $tripInstance->schedule->id,
                        'name' => $tripInstance->schedule->name,
                    ] : null,

                    // Seat plan details
                    'seat_plan' => $tripInstance->seatPlan ? [
                        'id' => $tripInstance->seatPlan->id,
                        'name' => $tripInstance->seatPlan->name,
                        'floor' => $tripInstance->seatPlan->floor,
                        'rows' => $tripInstance->seatPlan->rows,
                        'cols' => $tripInstance->seatPlan->cols,
                    ] : null,
                    // Driver details
                    'driver' => $tripInstance->driver ? [
                        'id' => $tripInstance->driver->id,
                        'name' => $tripInstance->driver->name,
                        'contact' => $tripInstance->driver->contact,
                        'license' => $tripInstance->driver->license,
                    ] : null,

                    // Supervisor details
                    'supervisor' => $tripInstance->supervisor ? [
                        'id' => $tripInstance->supervisor->id,
                        'name' => $tripInstance->supervisor->name,
                        'contact' => $tripInstance->supervisor->contact,
                    ] : null,
                ],
                'booked_seats' => $booking->bookingDetails,
            ];

            return $this->successResponse($responseData, 'Booking retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve booking: '.$e->getMessage(), 500);
        }
    }

    /**
     * Update the specified Booking.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'gender' => 'nullable|string|max:255',
            'age' => 'nullable|numeric',
            'address' => 'nullable|string|max:255',
            'passport_no' => 'nullable|string|max:255',
            'nid' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',

            'trip_id' => 'required|integer',
            'type' => 'required|integer',
            'date' => 'required|date|date_format:Y-m-d',
            'time' => 'required|date_format:H:i:s',
            'route_id' => 'required|integer|exists:transport_routes,id',
            'boarding_id' => 'nullable|integer|exists:trip_boarding_droppings,id',
            'dropping_id' => 'nullable|integer|exists:trip_boarding_droppings,id',

            'booking_details' => 'required|array',
            'booking_details.*.seat_inventory_id' => 'required|integer',
            'booking_details.*.seat_id' => 'required|integer|exists:seats,id',
            'booking_details.*.price' => 'required|numeric',
            'booking_details.*.discount' => 'nullable|numeric',
            'booking_details.*.cancel_status' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $authUserId = auth()->user()->id;

            DB::beginTransaction();

            // Find the booking
            $booking = Booking::find($id);
            if (! $booking) {
                return $this->errorResponse('Booking not found', 404);
            }

            // Get trip instance with all relationships
            $tripInstance = TripInstance::findAcrossPartitions($request->input('trip_id'));
            if (! $tripInstance) {
                return $this->errorResponse('Trip not found', 404);
            }

            // Load trip relationships
            $tripInstance->load([
                'coach', 'bus', 'schedule', 'seatPlan', 'route',
                'driver', 'supervisor', 'boardingDroppings',
            ]);

            // Update customer information (find or create based on mobile)
            $customer = Customer::where('mobile', $request->input('mobile'))->first();

            if ($customer && $customer->id !== $booking->customer_id) {
                // Mobile number changed to existing customer
                $customer->update([
                    'name' => $request->input('name'),
                    'gender' => $request->input('gender'),
                    'age' => $request->input('age'),
                    'address' => $request->input('address'),
                    'passport_no' => $request->input('passport_no'),
                    'nid' => $request->input('nid'),
                    'nationality' => $request->input('nationality'),
                    'email' => $request->input('email'),
                    'updated_by' => $authUserId,
                ]);
            } elseif ($customer && $customer->id === $booking->customer_id) {
                // Same customer, just update details
                $customer->update([
                    'name' => $request->input('name'),
                    'gender' => $request->input('gender'),
                    'age' => $request->input('age'),
                    'address' => $request->input('address'),
                    'passport_no' => $request->input('passport_no'),
                    'nid' => $request->input('nid'),
                    'nationality' => $request->input('nationality'),
                    'email' => $request->input('email'),
                    'updated_by' => $authUserId,
                ]);
            } else {
                // New customer with this mobile number
                $customer = Customer::create([
                    'mobile' => $request->input('mobile'),
                    'name' => $request->input('name'),
                    'gender' => $request->input('gender'),
                    'age' => $request->input('age'),
                    'address' => $request->input('address'),
                    'passport_no' => $request->input('passport_no'),
                    'nid' => $request->input('nid'),
                    'nationality' => $request->input('nationality', 'Bangladeshi'),
                    'email' => $request->input('email'),
                    'status' => 1,
                    'created_by' => $authUserId,
                ]);
            }

            // Get existing booking details to release seats
            $existingBookingDetails = $booking->bookingDetails;

            // Release previously booked seats
            foreach ($existingBookingDetails as $existingDetail) {
                $existingSeatInventory = SeatInventory::forTrip($booking->trip_id)
                    ->where('id', $existingDetail->seat_inventory_id)
                    ->first();

                if ($existingSeatInventory && $existingSeatInventory->booking_id == $booking->id) {
                    $existingSeatInventory->update([
                        'booking_status' => SeatInventory::STATUS_AVAILABLE,
                        'booking_id' => null,
                        'blocked_until' => null,
                        'last_locked_user_id' => null,
                        'updated_by' => $authUserId,
                    ]);
                }
            }

            // Delete existing booking details
            $booking->bookingDetails()->delete();

            $total_tickets = 0;
            $total_price = 0;
            $total_discount = 0;
            $total_amount = 0;

            $allBookingDetailData = [];
            $seatDetails = [];

            // Process new booking details
            foreach ($request->input('booking_details') as $detail) {
                if ($detail['cancel_status'] != 1) {
                    $discount = $detail['discount'] ?? 0;
                    $amount = $detail['price'] - $discount;

                    // Get seat inventory with seat details
                    $seatInventory = SeatInventory::forTrip($request->input('trip_id'))
                        ->where('id', $detail['seat_inventory_id'])
                        ->with('seat')
                        ->first();

                    if (! $seatInventory) {
                        return $this->errorResponse('Seat inventory not found', 404);
                    }

                    if ($seatInventory->booking_status != SeatInventory::STATUS_AVAILABLE) {
                        return $this->errorResponse('Seat is not available for booking', 400);
                    }

                    // Collect seat details for response
                    $seatDetails[] = [
                        'seat_inventory_id' => $detail['seat_inventory_id'],
                        'seat_id' => $detail['seat_id'],
                        'seat_number' => $seatInventory->seat->seat_number ?? null,
                        'row_position' => $seatInventory->seat->row_position ?? null,
                        'col_position' => $seatInventory->seat->col_position ?? null,
                        'seat_type' => $seatInventory->seat->seat_type ?? null,
                        'price' => $detail['price'],
                        'discount' => $discount,
                        'amount' => $amount,
                    ];

                    $allBookingDetailData[] = [
                        'seat_inventory_id' => $detail['seat_inventory_id'],
                        'seat_id' => $detail['seat_id'],
                        'price' => $detail['price'],
                        'discount' => $discount,
                        'amount' => $amount,
                    ];

                    $total_price += $detail['price'];
                    $total_tickets += 1;
                    $total_discount += $discount;
                    $total_amount += $amount;
                }
            }

            // Update booking data
            $booking->update([
                'customer_id' => $customer->id,
                'trip_id' => $request->input('trip_id'),
                'type' => $request->input('type'),
                'date' => $request->input('date'),
                'time' => $request->input('time'),
                'route_id' => $request->input('route_id'),
                'boarding_id' => $request->input('boarding_id'),
                'dropping_id' => $request->input('dropping_id'),
                'note' => $request->input('note') ?? null,
                'total_price' => $total_price,
                'total_discount' => $total_discount,
                'total_amount' => $total_amount,
                'updated_by' => $authUserId,
            ]);

            // Calculate blocked_until based on status
            $blockedUntilCarbon = null;
            $actionMessage = '';

            // For booked status: block until trip_date + schedule - 1 hour
            $tripDateTime = Carbon::parse($tripInstance->trip_date);

            // Load schedule to get departure time
            if ($tripInstance->schedule && isset($tripInstance->schedule->name)) {
                $departureTime = Carbon::parse($tripInstance->schedule->name);
                $tripDateTime->setTime($departureTime->hour, $departureTime->minute);
            } else {
                // Default to 8:00 AM if no schedule time found
                $tripDateTime->setTime(8, 0);
            }

            if ($request->type == 2 || $request->type == 4) { // Booked
                // Block until 1 hour before departure
                $blockedUntilCarbon = $tripDateTime->subHour();
                $actionMessage = 'Seat booked/sold successfully until 1 hour before departure';

            } elseif ($request->type == 3) { // Blocked
                // For blocked status: block for 5 minutes (temporary)
                $blockedUntilCarbon = now()->addMinutes(5);
                $actionMessage = 'Seat blocked successfully';
            }

            // Create new booking details and update seat inventory
            foreach ($allBookingDetailData as $bookingDetailData) {
                $seatInventory = SeatInventory::forTrip($booking->trip_id)
                    ->where('id', $bookingDetailData['seat_inventory_id'])
                    ->first();

                if ($seatInventory && $seatInventory->booking_status == SeatInventory::STATUS_AVAILABLE) {
                    // Mark seat as SOLD/BOOKED based on type
                    $seatInventory->update([
                        'booking_status' => $request->type,
                        'booking_id' => $booking->id,
                        'blocked_until' => $blockedUntilCarbon,
                        'last_locked_user_id' => auth()->user()->id,
                        'updated_by' => $authUserId,
                    ]);
                } else {
                    DB::rollback();

                    return $this->errorResponse('Seat inventory is not available', 400);
                }

                $booking->bookingDetails()->create($bookingDetailData);
            }

            DB::commit();

            // Get boarding and dropping point details
            $boardingInfo = null;
            $droppingInfo = null;

            if ($booking->boarding_id) {
                $boardingPoint = \DB::table('trip_boarding_droppings')
                    ->leftJoin('counters', 'trip_boarding_droppings.counter_id', '=', 'counters.id')
                    ->leftJoin('districts', 'counters.district_id', '=', 'districts.id')
                    ->where('trip_boarding_droppings.id', $booking->boarding_id)
                    ->select('trip_boarding_droppings.*', 'counters.address as counter_name', 'counters.land_mark as counter_location', 'districts.name as district_name')
                    ->first();

                if ($boardingPoint) {
                    $boardingInfo = [
                        'id' => $boardingPoint->id,
                        'trip_id' => $boardingPoint->trip_id,
                        'time' => $boardingPoint->time,
                        'counter_id' => $boardingPoint->counter_id,
                        'counter_name' => $boardingPoint->counter_name,
                        'counter_location' => $boardingPoint->counter_location,
                        'district_name' => $boardingPoint->district_name,
                    ];
                }
            }

            if ($booking->dropping_id) {
                $droppingPoint = \DB::table('trip_boarding_droppings')
                    ->leftJoin('counters', 'trip_boarding_droppings.counter_id', '=', 'counters.id')
                    ->leftJoin('districts', 'counters.district_id', '=', 'districts.id')
                    ->where('trip_boarding_droppings.id', $booking->dropping_id)
                    ->select('trip_boarding_droppings.*', 'counters.address as counter_name', 'counters.land_mark as counter_location', 'districts.name as district_name')
                    ->first();

                if ($droppingPoint) {
                    $droppingInfo = [
                        'id' => $droppingPoint->id,
                        'trip_id' => $droppingPoint->trip_id,
                        'time' => $droppingPoint->time,
                        'counter_id' => $droppingPoint->counter_id,
                        'counter_name' => $droppingPoint->counter_name,
                        'counter_location' => $droppingPoint->counter_location,
                        'district_name' => $droppingPoint->district_name,
                    ];
                }
            }

            // Prepare response data
            $responseData = [
                'booking' => [
                    'id' => $booking->id,
                    'pnr_number' => $booking->pnr_number,
                    'trip_id' => $booking->trip_id,
                    'type' => $booking->type,
                    'date' => $booking->date,
                    'time' => $booking->time,
                    'route_id' => $booking->route_id,
                    'boarding_id' => $booking->boarding_id,
                    'dropping_id' => $booking->dropping_id,
                    'total_tickets' => $total_tickets,
                    'total_price' => $total_price,
                    'total_discount' => $total_discount,
                    'total_amount' => $total_amount,
                    'status' => 'updated',
                    'updated_at' => $booking->updated_at,
                ],
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'mobile' => $customer->mobile,
                    'email' => $customer->email,
                    'gender' => $customer->gender,
                    'age' => $customer->age,
                    'address' => $customer->address,
                    'passport_no' => $customer->passport_no,
                    'nid' => $customer->nid,
                    'nationality' => $customer->nationality,
                ],
                'boarding_info' => $boardingInfo,
                'dropping_info' => $droppingInfo,
                'trip_details' => [
                    'trip_id' => $tripInstance->id,
                    'trip_date' => $tripInstance->trip_date->format('Y-m-d'),
                    'status' => $tripInstance->status,
                    'coach_type' => $tripInstance->coach_type,
                    'coach_type_name' => $tripInstance->coach_type_name,

                    // Coach details
                    'coach' => $tripInstance->coach ? [
                        'id' => $tripInstance->coach->id,
                        'coach_no' => $tripInstance->coach->coach_no,
                        'status' => $tripInstance->coach->status,
                    ] : null,

                    // Bus details
                    'bus' => $tripInstance->bus ? [
                        'id' => $tripInstance->bus->id,
                        'registration_number' => $tripInstance->bus->registration_number,
                        'manufacturer_company' => $tripInstance->bus->manufacturer_company,
                        'model_year' => $tripInstance->bus->model_year,
                    ] : null,

                    // Route details
                    'route' => $tripInstance->route ? [
                        'id' => $tripInstance->route->id,
                        'start_id' => $tripInstance->route->start_id,
                        'end_id' => $tripInstance->route->end_id,
                        'distance' => $tripInstance->route->distance,
                        'duration' => $tripInstance->route->duration,
                    ] : null,

                    // Schedule details
                    'schedule' => $tripInstance->schedule ? [
                        'id' => $tripInstance->schedule->id,
                        'name' => $tripInstance->schedule->name,
                    ] : null,

                    // Seat plan details
                    'seat_plan' => $tripInstance->seatPlan ? [
                        'id' => $tripInstance->seatPlan->id,
                        'name' => $tripInstance->seatPlan->name,
                        'floor' => $tripInstance->seatPlan->floor,
                        'rows' => $tripInstance->seatPlan->rows,
                        'cols' => $tripInstance->seatPlan->cols,
                    ] : null,

                    // Driver details
                    'driver' => $tripInstance->driver ? [
                        'id' => $tripInstance->driver->id,
                        'name' => $tripInstance->driver->name,
                        'contact' => $tripInstance->driver->contact,
                        'license' => $tripInstance->driver->license,
                    ] : null,

                    // Supervisor details
                    'supervisor' => $tripInstance->supervisor ? [
                        'id' => $tripInstance->supervisor->id,
                        'name' => $tripInstance->supervisor->name,
                        'contact' => $tripInstance->supervisor->contact,
                    ] : null,
                ],
                'updated_seats' => $seatDetails,
                'seat_status_summary' => [
                    'total_seats_updated' => count($seatDetails),
                    'seat_status' => 'updated',
                    'payment_status' => 'completed',
                ],
            ];

            return $this->successResponse($responseData, 'Booking updated successfully', 200);

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update booking: '.$e->getMessage(), 500);
        }
    }

    /**
     * Generate a unique PNR number
     *
     * @param  int  $length
     * @param  string  $type  [numeric, alphanumeric]
     * @return string
     */
    private function generateUniquePNR($length = 10, $type = 'alphanumeric')
    {
        do {
            $pnr = $this->generatePNRNumber($length, $type);
            $exists = Booking::where('pnr_number', $pnr)->exists();
        } while ($exists);

        return $pnr;
    }

    /**
     * Generate a PNR number
     *
     * @param  int  $length
     * @param  string  $type  [numeric, alphanumeric]
     * @return string
     */
    private function generatePNRNumber($length = 15, $type = 'alphanumeric')
    {
        if ($type === 'numeric') {
            $characters = '0123456789';
        } else {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        }

        $pnr = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $pnr .= $characters[random_int(0, $maxIndex)];
        }

        return $pnr;
    }
}
