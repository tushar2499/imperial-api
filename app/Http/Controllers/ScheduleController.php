<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse; // Make sure the trait is imported

class ScheduleController extends Controller
{
    use ApiResponse;  // Use the ApiResponse trait

    /**
     * Display a listing of schedules.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            DB::beginTransaction();

            // Get all schedules from the database
            $schedules = DB::table('schedules')
                ->select('schedules.id', 'schedules.name', 'schedules.status', 'schedules.created_by', 'schedules.updated_by', 'schedules.created_at', 'schedules.updated_at', 'schedules.deleted_at')
                ->whereNull('schedules.deleted_at')
                ->get();

            DB::commit();

            return $this->successResponse($schedules, 'Schedules retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to retrieve schedules: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created schedule.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $scheduleId = DB::table('schedules')->insertGetId([
                'name' => $request->input('name'),
                'created_by' => auth()->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $schedule = DB::table('schedules')
                ->select('id', 'name', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at')
                ->where('id', $scheduleId)
                ->first();

            DB::commit();

            return $this->successResponse(['data' => $schedule], 'Schedule created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to create schedule: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified schedule.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            DB::beginTransaction();

            $schedule = DB::table('schedules')
                ->select('schedules.id', 'schedules.name', 'schedules.status', 'schedules.created_by', 'schedules.updated_by', 'schedules.created_at', 'schedules.updated_at', 'schedules.deleted_at')
                ->where('schedules.id', $id)
                ->whereNull('schedules.deleted_at')
                ->first();

            if (!$schedule) {
                return $this->errorResponse('Schedule not found', 404);
            }

            DB::commit();

            return $this->successResponse($schedule, 'Schedule retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to retrieve schedule: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified schedule.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Update the schedule
            $updated = DB::table('schedules')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'name' => $request->input('name'),
                    'updated_by' => auth()->user()->id,
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                return $this->errorResponse('Schedule not found', 404);
            }

            $schedule = DB::table('schedules')
                ->select('id', 'name', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at')
                ->where('id', $id)
                ->first();

            DB::commit();

            return $this->successResponse($schedule, 'Schedule updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to update schedule: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified schedule.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Soft delete the schedule
            $deleted = DB::table('schedules')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'status'     => 0,
                    'deleted_at' => now(),
                    'updated_by' => auth()->user()->id,
                    'updated_at' => now(),
                ]);

            if ($deleted === 0) {
                return $this->errorResponse('Schedule not found', 404);
            }

            DB::commit();

            return $this->successResponse(null, 'Schedule deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to delete schedule: ' . $e->getMessage(), 500);
        }
    }
}
