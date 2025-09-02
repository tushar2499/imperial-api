<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\SeatInventory;
use App\Models\TripInstance;
use App\Traits\ApiResponse;
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
    public function index()
    {
        try {
            $bookings = Booking::with([
                'customer',
                'boarding',
                'dropping',
                'route',
                'bookingDetails',
                'bookingDetails.seat',
            ])->get();

            return $this->successResponse($bookings, 'Bookings retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve bookings: ' . $e->getMessage(), 500);
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
            'mobile'                              => 'required|string|max:20', // Changed from mobile_number
            'name'                                => 'required|string|max:255',
            'gender'                              => 'nullable|string|max:255',
            'age'                                 => 'nullable|numeric',
            'address'                             => 'nullable|string|max:255',
            'passport_no'                         => 'nullable|string|max:255',
            'nid'                                 => 'nullable|string|max:50', // Added nid
            'nationality'                         => 'nullable|string|max:255',
            'email'                               => 'nullable|string|max:255',

            'trip_id'                             => 'required|integer',
            'date'                                => 'required|date|date_format:Y-m-d',
            'time'                                => 'required|date_format:H:i:s',
            'route_id'                            => 'required|integer|exists:routes,id',
            'boarding_id'                         => 'nullable|integer|exists:trip_boarding_droppings,id',
            'dropping_id'                         => 'nullable|integer|exists:trip_boarding_droppings,id',

            'booking_details'                     => 'required|array',
            'booking_details.*.seat_inventory_id' => 'required|integer',
            'booking_details.*.seat_id'           => 'required|integer|exists:seats,id',
            'booking_details.*.price'             => 'required|numeric',
            'booking_details.*.discount'          => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $authUserId = auth()->user()->id;

            DB::beginTransaction();

            // Get trip instance with all relationships for comprehensive response
            $tripInstance = TripInstance::findAcrossPartitions($request->input('trip_id'));
            if (!$tripInstance) {
                return $this->errorResponse('Trip not found', 404);
            }

            // Load trip relationships including fare
            $tripInstance->load([
                'coach', 'bus', 'schedule', 'seatPlan', 'route',
                'driver', 'supervisor', 'fare', 'boardingDroppings'
            ]);

            // Find or create customer (using mobile instead of mobile_number)
            $customer = Customer::where('mobile', $request->input('mobile'))->first();

            if ($customer) {
                $customer->update([
                    'name'        => $request->input('name'),
                    'gender'      => $request->input('gender'),
                    'age'         => $request->input('age'),
                    'address'     => $request->input('address'),
                    'passport_no' => $request->input('passport_no'),
                    'nid'         => $request->input('nid'),
                    'nationality' => $request->input('nationality'),
                    'email'       => $request->input('email'),
                    'updated_by'  => $authUserId,
                ]);
                $customer->refresh();
            } else {
                $customer = Customer::create([
                    'mobile'      => $request->input('mobile'), // Changed from mobile_number
                    'name'        => $request->input('name'),
                    'gender'      => $request->input('gender'),
                    'age'         => $request->input('age'),
                    'address'     => $request->input('address'),
                    'passport_no' => $request->input('passport_no'),
                    'nid'         => $request->input('nid'),
                    'nationality' => $request->input('nationality', 'Bangladeshi'),
                    'email'       => $request->input('email'),
                    'status'      => 1,
                    'created_by'  => $authUserId,
                ]);
            }

            $total_tickets  = 0;
            $total_price    = 0;
            $total_discount = 0;
            $total_amount   = 0;

            // Prepare booking data
            $bookingData = [
                'customer_id'    => $customer->id,
                'pnr_number'     => $this->generateUniquePNR(),
                'trip_id'        => $request->input('trip_id'),
                'date'           => $request->input('date'),
                'time'           => $request->input('time'),
                'route_id'       => $request->input('route_id'),
                'boarding_id'    => $request->input('boarding_id'),
                'dropping_id'    => $request->input('dropping_id'),
                'total_price'    => 0,
                'total_discount' => 0,
                'total_amount'   => 0,
                'created_by'     => $authUserId,
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

                if (!$seatInventory) {
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
                    'seat_id'           => $detail['seat_id'],
                    'price'             => $detail['price'],
                    'discount'          => $discount,
                    'amount'            => $amount,
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

            // Create booking details and update seat inventory
            foreach ($allBookingDetailData as $bookingDetailData) {
                $seatInventory = SeatInventory::forTrip($booking->trip_id)
                    ->where('id', $bookingDetailData['seat_inventory_id'])
                    ->first();

                if ($seatInventory && $seatInventory->booking_status == SeatInventory::STATUS_AVAILABLE) {
                    $seatInventory->update([
                        'booking_status' => SeatInventory::STATUS_BOOKED, 
                        'booking_id' => $booking->id,
                        'blocked_until' => null, // Clear any blocking
                        'last_locked_user_id' => null,
                    ]);
                } else {
                    DB::rollback(); 
                    return $this->errorResponse('Seat inventory is not available', 400);
                }

                $booking->bookingDetails()->create($bookingDetailData);
            }

            // Update customer statistics if these fields exist
            if ($customer->hasAttribute('total_trips') && $customer->hasAttribute('total_tickets')) {
                $customer->update([
                    'total_trips'   => $customer->total_trips + 1,
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
                    ->where('trip_boarding_droppings.id', $booking->boarding_id)
                    ->select('trip_boarding_droppings.*', 'counters.name as counter_name', 'counters.location as counter_location')
                    ->first();

                if ($boardingPoint) {
                    $boardingInfo = [
                        'id' => $boardingPoint->id,
                        'trip_id' => $boardingPoint->trip_id,
                        'counter_id' => $boardingPoint->counter_id,
                        'point_type' => $boardingPoint->point_type,
                        'counter_name' => $boardingPoint->counter_name,
                        'counter_location' => $boardingPoint->counter_location,
                    ];
                }
            }

            if ($booking->dropping_id) {
                $droppingPoint = \DB::table('trip_boarding_droppings')
                    ->leftJoin('counters', 'trip_boarding_droppings.counter_id', '=', 'counters.id')
                    ->where('trip_boarding_droppings.id', $booking->dropping_id)
                    ->select('trip_boarding_droppings.*', 'counters.name as counter_name', 'counters.location as counter_location')
                    ->first();

                if ($droppingPoint) {
                    $droppingInfo = [
                        'id' => $droppingPoint->id,
                        'trip_id' => $droppingPoint->trip_id,
                        'counter_id' => $droppingPoint->counter_id,
                        'point_type' => $droppingPoint->point_type,
                        'counter_name' => $droppingPoint->counter_name,
                        'counter_location' => $droppingPoint->counter_location,
                    ];
                }
            }

            // Prepare comprehensive response data
            $responseData = [
                'booking' => [
                    'id' => $booking->id,
                    'pnr_number' => $booking->pnr_number,
                    'trip_id' => $booking->trip_id,
                    'date' => $booking->date,
                    'time' => $booking->time,
                    'route_id' => $booking->route_id,
                    'boarding_id' => $booking->boarding_id,
                    'dropping_id' => $booking->dropping_id,
                    'total_tickets' => $total_tickets,
                    'total_price' => $total_price,
                    'total_discount' => $total_discount,
                    'total_amount' => $total_amount,
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

                    // Fare details
                    'fare' => $tripInstance->fare ? [
                        'id' => $tripInstance->fare->id,
                        'amount' => $tripInstance->fare->amount ?? null,
                        'coach_type' => $tripInstance->fare->coach_type_name,
                        'status' => $tripInstance->fare->status_name,
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
                'booked_seats' => $seatDetails,
            ];

            return $this->successResponse($responseData, 'Booking created successfully', 201);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to create booking: ' . $e->getMessage(), 500);
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

            if (!$booking) {
                return $this->errorResponse('Booking not found', 404);
            }

            // Get trip details
            $tripInstance = TripInstance::findAcrossPartitions($booking->trip_id);
            if ($tripInstance) {
                $tripInstance->load([
                    'coach', 'bus', 'schedule', 'seatPlan', 'route',
                    'driver', 'supervisor', 'fare', 'boardingDroppings'
                ]);
            }

            // Enhanced response with complete information
            $enhancedBooking = $booking->toArray();
            $enhancedBooking['trip_details'] = $tripInstance ? [
                'trip_id' => $tripInstance->id,
                'trip_date' => $tripInstance->trip_date->format('Y-m-d'),
                'status' => $tripInstance->status,
                'coach_type' => $tripInstance->coach_type,
                'coach_type_name' => $tripInstance->coach_type_name,
                'coach' => $tripInstance->coach,
                'bus' => $tripInstance->bus,
                'route' => $tripInstance->route,
                'schedule' => $tripInstance->schedule,
                'seat_plan' => $tripInstance->seatPlan,
                'fare' => $tripInstance->fare,
                'driver' => $tripInstance->driver,
                'supervisor' => $tripInstance->supervisor,
            ] : null;

            return $this->successResponse($enhancedBooking, 'Booking retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve booking: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate a unique PNR number
     *
     * @param integer $length
     * @param string $type  [numeric, alphanumeric]
     * @return string
     */
    private function generateUniquePNR($length = 10, $type = 'alphanumeric')
    {
        do {
            $pnr    = $this->generatePNRNumber($length, $type);
            $exists = Booking::where('pnr_number', $pnr)->exists();
        } while ($exists);

        return $pnr;
    }

    /**
     * Generate a PNR number
     *
     * @param integer $length
     * @param string $type [numeric, alphanumeric]
     * @return string
     */
    private function generatePNRNumber($length = 15, $type = 'alphanumeric')
    {
        if ($type === 'numeric') {
            $characters = '0123456789';
        } else {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        }

        $pnr      = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $pnr .= $characters[random_int(0, $maxIndex)];
        }

        return $pnr;
    }
}
