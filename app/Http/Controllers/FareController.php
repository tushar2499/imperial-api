<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Make sure the trait is imported

class FareController extends Controller
{
    use ApiResponse;

// Use the ApiResponse trait

    /**
     * Display a listing of fares.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage    = min((int) $request->get('per_page', 15), 1000); // Cap at 1000
            $page       = max((int) $request->get('page', 1), 1); // Minimum page 1
            $searchTerm = $request->get('search');

            $query = DB::table('fares')
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
                    'fares.seat_type',
                    'fares.from_date',
                    'fares.to_date',
                    'fares.amount',
                    'fares.status',
                    'fares.created_by',
                    'fares.updated_by',
                    'fares.created_at',
                    'fares.updated_at'
                )
                ->join('routes', 'fares.route_id', '=', 'routes.id')
                ->join('districts as start', 'routes.start_id', '=', 'start.id')
                ->join('districts as end', 'routes.end_id', '=', 'end.id')
                ->join('seat_plans', 'fares.seat_plan_id', '=', 'seat_plans.id')
                ->whereNull('fares.deleted_at')
                ->where(function ($query) use ($searchTerm) {
                    $query->where('fares.amount', 'like', "%{$searchTerm}%")
                        ->orWhere('start.name', 'like', "%{$searchTerm}%")
                        ->orWhere('end.name', 'like', "%{$searchTerm}%");
                })
                ->orderBy('fares.created_at', 'desc');
            $fares = $query->paginate($perPage, ['*'], 'page', $page);

            return $this->successResponse($fares, 'Fares retrieved successfully');
        } catch (\Exception $e) {
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
            'route_id'     => 'required|exists:routes,id',
            'seat_plan_id' => 'required|exists:seat_plans,id',
            'coach_type'   => 'required|integer|in:1,2',
            'seat_type'    => 'required|string|in:Suite Class,Business Class,Sleeper,Economy',
            'from_date'    => 'nullable|date',
            'to_date'      => 'nullable|date|after_or_equal:from_date',
            'amount'       => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $fareId = DB::table('fares')->insertGetId([
                'route_id'     => $request->input('route_id'),
                'seat_plan_id' => $request->input('seat_plan_id'),
                'coach_type'   => $request->input('coach_type'),
                'seat_type'    => $request->input('seat_type'),
                'from_date'    => $request->input('from_date'),
                'to_date'      => $request->input('to_date'),
                'amount'       => $request->input('amount'),
                'status'       => $request->input('status', 1),
                'created_by'   => auth()->user()->id,
                'created_at'   => now(),
                'updated_at'   => now(),
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
                    'fares.seat_type',
                    'fares.from_date',
                    'fares.to_date',
                    'fares.amount',
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
                    'fares.seat_type',
                    'fares.from_date',
                    'fares.to_date',
                    'fares.amount',
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
            'route_id'     => 'required|exists:routes,id',
            'seat_plan_id' => 'required|exists:seat_plans,id',
            'coach_type'   => 'required|integer|in:1,2',
            'seat_type'    => 'required|string|in:Suite Class,Business Class,Sleeper,Economy',
            'from_date'    => 'nullable|date',
            'to_date'      => 'nullable|date|after_or_equal:from_date',
            'amount'       => 'required|integer',
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
                    'route_id'     => $request->input('route_id'),
                    'seat_plan_id' => $request->input('seat_plan_id'),
                    'coach_type'   => $request->input('coach_type'),
                    'seat_type'    => $request->input('seat_type'),
                    'from_date'    => $request->input('from_date'),
                    'to_date'      => $request->input('to_date'),
                    'amount'       => $request->input('amount'),
                    'status'       => $request->input('status', 1),
                    'updated_by'   => auth()->user()->id,
                    'updated_at'   => now(),
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
                    'fares.seat_type',
                    'fares.from_date',
                    'fares.to_date',
                    'fares.amount',
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

    /**
     * Get available seat types
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSeatTypes()
    {
        try {
            $seatTypes = [
                'Suite Class',
                'Business Class',
                'Sleeper',
                'Economy',
            ];

            return $this->successResponse($seatTypes, 'Seat types retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve seat types: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Get fares by route and coach type
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFaresByRoute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'route_id'   => 'required|exists:routes,id',
            'coach_type' => 'sometimes|integer|in:1,2',
            'seat_type'  => 'sometimes|string|in:Suite Class,Business Class,Sleeper,Economy',
            'date'       => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $query = DB::table('fares')
                ->select(
                    'fares.id',
                    'fares.route_id',
                    'fares.seat_plan_id',
                    'seat_plans.name as seat_plan_name',
                    'fares.coach_type',
                    'fares.seat_type',
                    'fares.amount',
                    'fares.from_date',
                    'fares.to_date',
                    'fares.status'
                )
                ->join('seat_plans', 'fares.seat_plan_id', '=', 'seat_plans.id')
                ->where('fares.route_id', $request->route_id)
                ->where('fares.status', 1)
                ->whereNull('fares.deleted_at');

            if ($request->filled('coach_type')) {
                $query->where('fares.coach_type', $request->coach_type);
            }

            if ($request->filled('seat_type')) {
                $query->where('fares.seat_type', $request->seat_type);
            }

            if ($request->filled('date')) {
                $date = $request->date;
                $query->where(function ($q) use ($date) {
                    $q->where(function ($subQuery) use ($date) {
                        $subQuery->whereNull('fares.from_date')
                            ->orWhere('fares.from_date', '<=', $date);
                    })
                        ->where(function ($subQuery) use ($date) {
                            $subQuery->whereNull('fares.to_date')
                                ->orWhere('fares.to_date', '>=', $date);
                        });
                });
            }

            $fares = $query->get();

            DB::commit();

            return $this->successResponse($fares, 'Fares retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve fares: ' . $e->getMessage(), 500);
        }

    }

}
