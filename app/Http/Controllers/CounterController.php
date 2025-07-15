<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse; // Ensure the ApiResponse trait is imported

class CounterController extends Controller
{
    use ApiResponse;  // Use the ApiResponse trait

    /**
     * Display a listing of counters.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            DB::beginTransaction();

            // Get all counters from the database
            $counters = DB::table('counters')->get();

            DB::commit();

            return $this->successResponse($counters, 'Counters retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to retrieve counters: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created counter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:1,2,3', // 1: Own Counter, 2: Commission Counter, 3: Head Office
            'address' => 'required|string|max:255',
            'land_mark' => 'nullable|string|max:255',
            'location_url' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'primary_contact_no' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'district_id' => 'required|exists:districts,id', // Assuming a district exists in districts table
            'booking_allowed_status' => 'required|in:1,2,3', // 1: Coach wise, 2: Route wise, 3: Both
            'booking_allowed_class' => 'required|in:1,2,3,4', // 1: B Class, 2: E Class, 3: All, 4: Sleeper
            'no_of_boarding_allowed' => 'nullable|integer',
            'sms_status' => 'nullable|in:1,2', // Whether SMS is enabled
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Insert counter into the database
            $counterId = DB::table('counters')->insertGetId([
                'type' => $request->input('type'),
                'address' => $request->input('address'),
                'land_mark' => $request->input('land_mark'),
                'location_url' => $request->input('location_url'),
                'phone' => $request->input('phone'),
                'mobile' => $request->input('mobile'),
                'email' => $request->input('email'),
                'primary_contact_no' => $request->input('primary_contact_no'),
                'country' => $request->input('country'),
                'district_id' => $request->input('district_id'),
                'booking_allowed_status' => $request->input('booking_allowed_status'),
                'booking_allowed_class' => $request->input('booking_allowed_class'),
                'no_of_boarding_allowed' => $request->input('no_of_boarding_allowed'),
                'sms_status' => $request->input('sms_status'),
                'created_by' => auth()->user()->id,
                'created_at' => now(),
            ]);

            $counter = DB::table('counters')->where('id', $counterId)->first();

            DB::commit();

            return $this->successResponse(['data' => $counter], 'Counter created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to create counter: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified counter.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            DB::beginTransaction();

            // Get the counter by id
            $counter = DB::table('counters')->where('id', $id)->first();

            if (!$counter) {
                return $this->errorResponse('Counter not found', 404);
            }

            DB::commit();

            return $this->successResponse($counter, 'Counter retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to retrieve counter: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified counter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:1,2,3',
            'address' => 'required|string|max:255',
            'land_mark' => 'nullable|string|max:255',
            'location_url' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'primary_contact_no' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'district_id' => 'required|exists:districts,id',
            'booking_allowed_status' => 'required|in:1,2,3',
            'booking_allowed_class' => 'required|in:1,2,3,4',
            'no_of_boarding_allowed' => 'nullable|integer',
            'sms_status' => 'nullable|in:1,2',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Update the counter
            $updated = DB::table('counters')->where('id', $id)->update([
                'type' => $request->input('type'),
                'address' => $request->input('address'),
                'land_mark' => $request->input('land_mark'),
                'location_url' => $request->input('location_url'),
                'phone' => $request->input('phone'),
                'mobile' => $request->input('mobile'),
                'email' => $request->input('email'),
                'primary_contact_no' => $request->input('primary_contact_no'),
                'country' => $request->input('country'),
                'district_id' => $request->input('district_id'),
                'booking_allowed_status' => $request->input('booking_allowed_status'),
                'booking_allowed_class' => $request->input('booking_allowed_class'),
                'no_of_boarding_allowed' => $request->input('no_of_boarding_allowed'),
                'sms_status' => $request->input('sms_status'),
                'updated_by' => auth()->user()->id,
                'updated_at' => now(),
            ]);

            if ($updated === 0) {
                return $this->errorResponse('Counter not found', 404);
            }

            $counter = DB::table('counters')->where('id', $id)->first();

            DB::commit();

            return $this->successResponse($counter, 'Counter updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to update counter: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified counter.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Soft delete the counter
            $deleted = DB::table('counters')->where('id', $id)->delete();

            if ($deleted === 0) {
                return $this->errorResponse('Counter not found', 404);
            }

            DB::commit();

            return $this->successResponse(null, 'Counter deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to delete counter: ' . $e->getMessage(), 500);
        }
    }
}
