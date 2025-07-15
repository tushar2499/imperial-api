<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse; // Ensure the ApiResponse trait is imported

class CoachController extends Controller
{
    use ApiResponse;  // Use the ApiResponse trait

    /**
     * Display a listing of all coaches.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            DB::beginTransaction();

            // Get all coaches from the database
            $coaches = DB::table('coaches')->get();

            DB::commit();

            return $this->successResponse($coaches, 'Coaches retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to retrieve coaches: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created coach.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'coach_no' => 'required|string|max:255|unique:coaches',
            'registration_number' => 'nullable|string|max:255',
            'manufacturer_company' => 'nullable|string|max:255',
            'model_year' => 'nullable|integer',
            'chasis_no' => 'nullable|string|max:255',
            'engine_number' => 'nullable|string|max:255',
            'country_of_origin' => 'nullable|string|max:255',
            'lc_code_number' => 'nullable|string|max:255',
            'delivery_to_dipo' => 'nullable|string|max:255',
            'delivery_date' => 'nullable|date',
            'color' => 'nullable|string|max:255',
            'seat_plan_id' => 'required|exists:seat_plans,id',
            'coach_type' => 'required|in:1,2',
            'financed_by' => 'nullable|string|max:255',
            'tennure_of_the_terms' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Insert coach into the database
            $coachId = DB::table('coaches')->insertGetId([
                'coach_no' => $request->input('coach_no'),
                'registration_number' => $request->input('registration_number'),
                'manufacturer_company' => $request->input('manufacturer_company'),
                'model_year' => $request->input('model_year'),
                'chasis_no' => $request->input('chasis_no'),
                'engine_number' => $request->input('engine_number'),
                'country_of_origin' => $request->input('country_of_origin'),
                'lc_code_number' => $request->input('lc_code_number'),
                'delivery_to_dipo' => $request->input('delivery_to_dipo'),
                'delivery_date' => $request->input('delivery_date'),
                'color' => $request->input('color'),
                'seat_plan_id' => $request->input('seat_plan_id'),
                'coach_type' => $request->input('coach_type'),
                'financed_by' => $request->input('financed_by'),
                'tennure_of_the_terms' => $request->input('tennure_of_the_terms'),
                'created_by' => auth()->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $coach = DB::table('coaches')->where('id', $coachId)->first();

            DB::commit();

            return $this->successResponse(['data' => $coach], 'Coach created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to create coach: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified coach.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            DB::beginTransaction();

            // Get the coach by id
            $coach = DB::table('coaches')->where('id', $id)->first();

            if (!$coach) {
                return $this->errorResponse('Coach not found', 404);
            }

            DB::commit();

            return $this->successResponse($coach, 'Coach retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to retrieve coach: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified coach.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'coach_no' => 'required|string|max:255|unique:coaches,coach_no,' . $id,
            'registration_number' => 'nullable|string|max:255',
            'manufacturer_company' => 'nullable|string|max:255',
            'model_year' => 'nullable|integer',
            'chasis_no' => 'nullable|string|max:255',
            'engine_number' => 'nullable|string|max:255',
            'country_of_origin' => 'nullable|string|max:255',
            'lc_code_number' => 'nullable|string|max:255',
            'delivery_to_dipo' => 'nullable|string|max:255',
            'delivery_date' => 'nullable|date',
            'color' => 'nullable|string|max:255',
            'seat_plan_id' => 'required|exists:seat_plans,id',
            'coach_type' => 'required|in:1,2',
            'financed_by' => 'nullable|string|max:255',
            'tennure_of_the_terms' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Update coach details
            $updated = DB::table('coaches')->where('id', $id)->update([
                'coach_no' => $request->input('coach_no'),
                'registration_number' => $request->input('registration_number'),
                'manufacturer_company' => $request->input('manufacturer_company'),
                'model_year' => $request->input('model_year'),
                'chasis_no' => $request->input('chasis_no'),
                'engine_number' => $request->input('engine_number'),
                'country_of_origin' => $request->input('country_of_origin'),
                'lc_code_number' => $request->input('lc_code_number'),
                'delivery_to_dipo' => $request->input('delivery_to_dipo'),
                'delivery_date' => $request->input('delivery_date'),
                'color' => $request->input('color'),
                'seat_plan_id' => $request->input('seat_plan_id'),
                'coach_type' => $request->input('coach_type'),
                'financed_by' => $request->input('financed_by'),
                'tennure_of_the_terms' => $request->input('tennure_of_the_terms'),
                'updated_by' => auth()->user()->id,
                'updated_at' => now(),
            ]);

            if ($updated === 0) {
                return $this->errorResponse('Coach not found', 404);
            }

            $coach = DB::table('coaches')->where('id', $id)->first();

            DB::commit();

            return $this->successResponse($coach, 'Coach updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to update coach: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified coach.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Soft delete the coach
            $deleted = DB::table('coaches')->where('id', $id)->delete();

            if ($deleted === 0) {
                return $this->errorResponse('Coach not found', 404);
            }

            DB::commit();

            return $this->successResponse(null, 'Coach deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to delete coach: ' . $e->getMessage(), 500);
        }
    }
}
