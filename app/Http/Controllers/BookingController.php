<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    use ApiResponse;

    /**
     * Store a newly created customer.
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

            'pnr_number'                          => 'nullable|string|unique:bookings,pnr_number',
            'trip_id'                             => 'required|integer',
            'trip_date'                           => 'required|date|date_format:Y-m-d',
            'trip_time'                           => 'required|date_format:H:i:s',
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

            DB::beginTransaction();

            $customer = Customer::where('mobile_number', $request->input('mobile_number'))->first();

            if ($customer) {
                $customer->update([
                    'name'        => $request->input('name'),
                    'gender'      => $request->input('gender'),
                    'age'         => $request->input('age'),
                    'address'     => $request->input('address'),
                    'passport_no' => $request->input('passport_no'),
                    'nationality' => $request->input('nationality'),
                    'email'       => $request->input('email'),
                ]);

                $customer->refresh();
            } else {
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
                ]);
            }

            $booking = Booking::create([
                'customer_id' => $customer->id,
                'pnr_number'  => $request->input('pnr_number') ?? $this->generateUniquePNR(),
                'trip_id'     => $request->input('trip_id'),
                'trip_date'   => $request->input('trip_date'),
                'trip_time'   => $request->input('trip_time'),
                'route_id'    => $request->input('route_id'),
                'boarding_id' => $request->input('boarding_id'),
                'dropping_id' => $request->input('dropping_id'),
            ]);

            foreach ($request->input('booking_details') as $detail) {
                $discount          = isset($detail['discount']) ? $detail['discount'] : 0;
                $bookingDetailData = [
                    'seat_inventory_id' => $detail['seat_inventory_id'],
                    'seat_id'           => $detail['seat_id'],
                    'price'             => $detail['price'],
                    'discount'          => $discount,
                    'amount'            => $detail['price'] - $discount,
                ];

                $booking->bookingDetails()->create($bookingDetailData);
            }

            DB::commit();

            $booking  = $booking->load('bookingDetails');

            return $this->successResponse(['data' => $booking], 'Booking created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to create booking: ' . $e->getMessage(), 500);
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
