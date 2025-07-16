<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse; // Make sure the trait is imported

class FareController extends Controller
{
    use ApiResponse;  // Use the ApiResponse trait

    /**
     * Display a listing of fares.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            DB::beginTransaction();

            // Get all fares from the database with related data
            $fares = DB::table('fares')
                ->select(
                    'fares.id',
                    'fares.route_id',
                    'routes.start_id',
                    'routes.end_id',
                    'start.name as start_name',
                    'end.name as end_name',
                    'routes.distance',
                    'routes.duration',
                    'routes.status as route_status',
                    'fares.seat_plan_id',
                    'seat_plans.name as seat_plan_name',
                    'fares.coach_type',
                    'fares.from_date',
                    'fares.to_date',
                    'fares.status',
                    'fares.created_by',
                    'fares.updated_by',
                    'fares.created_at',
                    'fares.updated_at',
                    'fares.deleted_at'
                )
                ->join('routes', 'fares.route_id', '=', 'routes.id')
                ->join('districts as start', 'routes.start_id', '=', 'start.id')
                ->join('districts as end', 'routes.end_id', '=', 'end.id')
                ->join('seat_plans', 'fares.seat_plan_id', '=', 'seat_plans.id')
                ->whereNull('fares.deleted_at')
                ->get();

            DB::commit();

            return $this->successResponse($fares, 'Fares retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to retrieve fares: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created fare.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'route_id' => 'required|exists:routes,id',
            'seat_plan_id' => 'required|exists:seat_plans,id',
            'coach_type' => 'required|integer|in:1,2',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'status' => 'nullable|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $fareId = DB::table('fares')->insertGetId([
                'route_id' => $request->input('route_id'),
                'seat_plan_id' => $request->input('seat_plan_id'),
                'coach_type' => $request->input('coach_type'),
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
                'status' => $request->input('status', 1),
                'created_by' => auth()->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $fare = DB::table('fares')
                ->select(
                    'fares.id',
                    'fares.route_id',
                    'routes.start_id',
                    'routes.end_id',
                    'start.name as start_name',
                    'end.name as end_name',
                    'routes.distance',
                    'routes.duration',
                    'routes.status as route_status',
                    'fares.seat_plan_id',
                    'seat_plans.name as seat_plan_name',
                    'fares.coach_type',
                    'fares.from_date',
                    'fares.to_date',
                    'fares.status',
                    'fares.created_by',
                    'fares.updated_by',
                    'fares.created_at',
                    'fares.updated_at',
                    'fares.deleted_at'
                )
                ->join('routes', 'fares.route_id', '=', 'routes.id')
                ->join('districts as start', 'routes.start_id', '=', 'start.id')
                ->join('districts as end', 'routes.end_id', '=', 'end.id')
                ->join('seat_plans', 'fares.seat_plan_id', '=', 'seat_plans.id')
                ->where('fares.id', $fareId)
                ->first();

            DB::commit();

            return $this->successResponse(['data' => $fare], 'Fare created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to create fare: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified fare.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            DB::beginTransaction();

            $fare = DB::table('fares')
                ->select(
                    'fares.id',
                    'fares.route_id',
                    'routes.start_id',
                    'routes.end_id',
                    'start.name as start_name',
                    'end.name as end_name',
                    'routes.distance',
                    'routes.duration',
                    'routes.status as route_status',
                    'fares.seat_plan_id',
                    'seat_plans.name as seat_plan_name',
                    'fares.coach_type',
                    'fares.from_date',
                    'fares.to_date',
                    'fares.status',
                    'fares.created_by',
                    'fares.updated_by',
                    'fares.created_at',
                    'fares.updated_at',
                    'fares.deleted_at'
                )
                ->join('routes', 'fares.route_id', '=', 'routes.id')
                ->join('districts as start', 'routes.start_id', '=', 'start.id')
                ->join('districts as end', 'routes.end_id', '=', 'end.id')
                ->join('seat_plans', 'fares.seat_plan_id', '=', 'seat_plans.id')
                ->where('fares.id', $id)
                ->whereNull('fares.deleted_at')
                ->first();

            if (!$fare) {
                return $this->errorResponse('Fare not found', 404);
            }

            DB::commit();

            return $this->successResponse($fare, 'Fare retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to retrieve fare: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified fare.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'route_id' => 'required|exists:routes,id',
            'seat_plan_id' => 'required|exists:seat_plans,id',
            'coach_type' => 'required|integer|in:1,2',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'status' => 'nullable|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Update the fare
            $updated = DB::table('fares')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'route_id' => $request->input('route_id'),
                    'seat_plan_id' => $request->input('seat_plan_id'),
                    'coach_type' => $request->input('coach_type'),
                    'from_date' => $request->input('from_date'),
                    'to_date' => $request->input('to_date'),
                    'status' => $request->input('status', 1),
                    'updated_by' => auth()->user()->id,
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                return $this->errorResponse('Fare not found', 404);
            }

            $fare = DB::table('fares')
                ->select(
                    'fares.id',
                    'fares.route_id',
                    'routes.start_id',
                    'routes.end_id',
                    'start.name as start_name',
                    'end.name as end_name',
                    'routes.distance',
                    'routes.duration',
                    'routes.status as route_status',
                    'fares.seat_plan_id',
                    'seat_plans.name as seat_plan_name',
                    'fares.coach_type',
                    'fares.from_date',
                    'fares.to_date',
                    'fares.status',
                    'fares.created_by',
                    'fares.updated_by',
                    'fares.created_at',
                    'fares.updated_at',
                    'fares.deleted_at'
                )
                ->join('routes', 'fares.route_id', '=', 'routes.id')
                ->join('districts as start', 'routes.start_id', '=', 'start.id')
                ->join('districts as end', 'routes.end_id', '=', 'end.id')
                ->join('seat_plans', 'fares.seat_plan_id', '=', 'seat_plans.id')
                ->where('fares.id', $id)
                ->first();

            DB::commit();

            return $this->successResponse($fare, 'Fare updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to update fare: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified fare.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Soft delete the fare
            $deleted = DB::table('fares')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => now(),
                    'updated_by' => auth()->user()->id,
                    'updated_at' => now(),
                ]);

            if ($deleted === 0) {
                return $this->errorResponse('Fare not found', 404);
            }

            DB::commit();

            return $this->successResponse(null, 'Fare deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to delete fare: ' . $e->getMessage(), 500);
        }
    }
}
