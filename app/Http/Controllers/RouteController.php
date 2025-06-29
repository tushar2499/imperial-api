<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse; // Make sure to import the ApiResponse trait

class RouteController extends Controller
{
    use ApiResponse;  // Use the ApiResponse trait

    /**
     * Display a listing of routes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Begin DB transaction
            DB::beginTransaction();

            // Get all routes from the database
            $routes = DB::table('routes')
                        ->join('districts as start', 'routes.start_id', '=', 'start.id')
                        ->join('districts as end', 'routes.end_id', '=', 'end.id')
                        ->select('routes.id', 'start.name as start_name', 'end.name as end_name', 'routes.distance', 'routes.duration', 'routes.status', 'routes.created_at', 'routes.updated_at')
                        ->get();

            // Commit transaction
            DB::commit();

            return $this->successResponse($routes, 'Routes retrieved successfully');
        } catch (\Exception $e) {
            // Rollback transaction if anything goes wrong
            DB::rollback();
            return $this->errorResponse('Failed to retrieve routes: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created route.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'start_id' => 'required|exists:districts,id',
            'end_id' => 'required|exists:districts,id',
            'distance' => 'required|numeric',
            'duration' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            // Begin DB transaction
            DB::beginTransaction();

            // Insert route into the database
            $routeId = DB::table('routes')->insertGetId([
                'start_id' => $request->input('start_id'),
                'end_id' => $request->input('end_id'),
                'distance' => $request->input('distance'),
                'duration' => $request->input('duration'),
                'created_by' => auth()->user()->id, // Assuming the user is authenticated
                'created_at' => now()
            ]);

            $route = DB::table('routes')
                ->join('districts as start', 'routes.start_id', '=', 'start.id')
                ->join('districts as end', 'routes.end_id', '=', 'end.id')
                ->select('routes.id', 'start.name as start_name', 'end.name as end_name', 'routes.distance', 'routes.duration', 'routes.status', 'routes.created_at', 'routes.updated_at')
                ->where('routes.id', $routeId)
                ->first();

            // Commit transaction
            DB::commit();

            return $this->successResponse(['data' => $route], 'Route created successfully', 201);
        } catch (\Exception $e) {
            // Rollback transaction if something goes wrong
            DB::rollback();
            return $this->errorResponse('Failed to create route: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified route.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // Begin DB transaction
            DB::beginTransaction();

            // Get the route by ID
            $route = DB::table('routes')
                        ->join('districts as start', 'routes.start_id', '=', 'start.id')
                        ->join('districts as end', 'routes.end_id', '=', 'end.id')
                        ->select('routes.id', 'start.name as start_name', 'end.name as end_name', 'routes.distance', 'routes.duration', 'routes.status', 'routes.created_at', 'routes.updated_at')
                        ->where('routes.id', $id)
                        ->first();

            if (!$route) {
                return $this->errorResponse('Route not found', 404);
            }

            // Commit transaction
            DB::commit();

            return $this->successResponse($route, 'Route retrieved successfully');
        } catch (\Exception $e) {
            // Rollback transaction if something goes wrong
            DB::rollback();
            return $this->errorResponse('Failed to retrieve route: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified route.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'start_id' => 'required|exists:districts,id',
            'end_id' => 'required|exists:districts,id',
            'distance' => 'required|numeric',
            'duration' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            // Begin DB transaction
            DB::beginTransaction();

            // Update the route
            $updated = DB::table('routes')
                ->where('id', $id)
                ->update([
                    'start_id' => $request->input('start_id'),
                    'end_id' => $request->input('end_id'),
                    'distance' => $request->input('distance'),
                    'duration' => $request->input('duration'),
                    'updated_by' => auth()->user()->id, // Assuming the user is authenticated
                    'updated_at' => now(),
                ]);

            $route = DB::table('routes')
                ->join('districts as start', 'routes.start_id', '=', 'start.id')
                ->join('districts as end', 'routes.end_id', '=', 'end.id')
                ->select('routes.id', 'start.name as start_name', 'end.name as end_name', 'routes.distance', 'routes.duration', 'routes.status', 'routes.created_at', 'routes.updated_at')
                ->where('routes.id', $id)
                ->first();

            if ($updated === 0) {
                return $this->errorResponse('Route not found', 404);
            }

            // Commit transaction
            DB::commit();

            return $this->successResponse($route, 'Route updated successfully', 200);
        } catch (\Exception $e) {
            // Rollback transaction if something goes wrong
            DB::rollback();
            return $this->errorResponse('Failed to update route: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified route.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // Begin DB transaction
            DB::beginTransaction();

            // Delete the route
            $deleted = DB::table('routes')->where('id', $id)->delete();

            if ($deleted === 0) {
                return $this->errorResponse('Route not found', 404);
            }

            // Commit transaction
            DB::commit();

            return $this->successResponse(null, 'Route deleted successfully');
        } catch (\Exception $e) {
            // Rollback transaction if something goes wrong
            DB::rollback();
            return $this->errorResponse('Failed to delete route: ' . $e->getMessage(), 500);
        }
    }
}
