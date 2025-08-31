<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\SeatInventory;
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
            'mobile_number'                       => 'required|string|max:255',
            'name'                                => 'required|string|max:255',
            'gender'                              => 'nullable|string|max:255',
            'age'                                 => 'nullable|numeric',
            'address'                             => 'nullable|string|max:255',
            'passport_no'                         => 'nullable|string|max:255',
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

            $customer = Customer::where('mobile_number', $request->input('mobile_number'))->first();

            /**
             * If customer exists, then Update customer information
             */

            if ($customer) {
                $customer->update([
                    'name'        => $request->input('name'),
                    'gender'      => $request->input('gender'),
                    'age'         => $request->input('age'),
                    'address'     => $request->input('address'),
                    'passport_no' => $request->input('passport_no'),
                    'nationality' => $request->input('nationality'),
                    'email'       => $request->input('email'),
                    'updated_by'  => $authUserId,
                ]);

                $customer->refresh();
            } else {
                /**
                 * If customer does not exist, then create new customer
                 */
                $customer = Customer::create([
                    'mobile_number' => $request->input('mobile_number'),
                    'name'          => $request->input('name'),
                    'gender'        => $request->input('gender'),
                    'age'           => $request->input('age'),
                    'address'       => $request->input('address'),
                    'passport_no'   => $request->input('passport_no'),
                    'nationality'   => $request->input('nationality'),
                    'email'         => $request->input('email'),
                    'status'        => $request->input('status', 1),
                    'created_by'    => $authUserId,
                ]);
            }

            $total_tickets  = 0;
            $total_price    = 0;
            $total_discount = 0;
            $total_amount   = 0;

            /**
             * Prepare booking data
             */
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

            /**
             * Prepare booking detail data
             */

            foreach ($request->input('booking_details') as $detail) {

                $discount               = isset($detail['discount']) ? $detail['discount'] : 0;
                $amount                 = $detail['price'] - $discount;
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

            /**
             * Create booking
             */
            $booking = Booking::create($bookingData);

            /**
             * Create booking details
             */

            foreach ($allBookingDetailData as $bookingDetailData) {
                /**
                 * Update seat inventory
                 */
                $seatInventory = SeatInventory::forTrip($booking->trip_id)
                    ->where('id', $bookingDetailData['seat_inventory_id'])
                    ->first();

                if($seatInventory && $seatInventory->status == SeatInventory::STATUS_AVAILABLE) {
                    $seatInventory->update([
                        'status'     => SeatInventory::STATUS_BOOKED,
                        'booking_id' => $booking->id,
                    ]);
                }
                else {
                    return $this->errorResponse('Seat inventory is not available', 400);
                }

                $booking->bookingDetails()->create($bookingDetailData);
            }

            /**
             * Update customer total_trips and total_tickets
             */
            $customer->update([
                'total_trips'   => $customer->total_trips + 1,
                'total_tickets' => $customer->total_tickets + $total_tickets,
            ]);

            DB::commit();

            $booking = $booking->load([
                'customer',
                'boarding',
                'dropping',
                'route',
                'bookingDetails',
                'bookingDetails.seat',
            ]);

            return $this->successResponse(['data' => $booking], 'Booking created successfully', 201);
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
            DB::beginTransaction();

            $booking = Booking::with([
                'customer',
                'boarding',
                'dropping',
                'route',
                'bookingDetails',
                'bookingDetails.seat',
            ])
                ->where('id', $id)
                ->first();

            if (!$booking) {
                return $this->errorResponse('Booking not found', 404);
            }

            DB::commit();

            return $this->successResponse($booking, 'Booking retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

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
