<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
// Ensure the ApiResponse trait is imported

class CoachController extends Controller
{
    use ApiResponse;
// Use the ApiResponse trait

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
            'coach_no'     => 'required|string|max:255|unique:coaches',
            'seat_plan_id' => 'required|exists:seat_plans,id',
            'coach_type'   => 'required|in:1,2',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Insert coach into the database
            $coachId = DB::table('coaches')->insertGetId([
                'coach_no'     => $request->input('coach_no'),
                'seat_plan_id' => $request->input('seat_plan_id'),
                'coach_type'   => $request->input('coach_type'),
                'created_by'   => auth()->user()->id,
                'created_at'   => now(),
                'updated_at'   => now(),
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
            'coach_no'             => 'required|string|max:255|unique:coaches,coach_no,' . $id,
            'seat_plan_id'         => 'required|exists:seat_plans,id',
            'coach_type'           => 'required|in:1,2',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Update coach details
            $updated = DB::table('coaches')->where('id', $id)->update([
                'coach_no'             => $request->input('coach_no'),
                'seat_plan_id'         => $request->input('seat_plan_id'),
                'coach_type'           => $request->input('coach_type'),
                'updated_by'           => auth()->user()->id,
                'updated_at'           => now(),
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
