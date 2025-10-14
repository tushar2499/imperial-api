<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Make sure to import the ApiResponse trait

class RouteController extends Controller
{
    use ApiResponse;

// Use the ApiResponse trait

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
                ->select('routes.id', 'routes.start_id', 'routes.end_id', 'start.name as start_name', 'end.name as end_name', 'routes.distance', 'routes.duration', 'routes.is_popular', 'routes.popular_position', 'routes.status', 'routes.created_at', 'routes.updated_at')
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
     * Display a listing of all popular routes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function allPopularRoutes()
    {
        try {
            // Get all routes from the database
            $routes = DB::table('routes')
                ->join('districts as start', 'routes.start_id', '=', 'start.id')
                ->join('districts as end', 'routes.end_id', '=', 'end.id')
                ->select('routes.id', 'routes.start_id', 'routes.end_id', 'start.name as start_name', 'end.name as end_name', 'routes.distance', 'routes.duration', 'routes.is_popular', 'routes.popular_position', 'routes.status', 'routes.created_at', 'routes.updated_at')
                ->where('routes.is_popular', true)
                ->orderBy('routes.popular_position', 'asc')
                ->get();

            return $this->successResponse($routes, 'Popular routes retrieved successfully');
        } catch (\Exception $e) {
            // Rollback transaction if anything goes wrong
            DB::rollback();

            return $this->errorResponse('Failed to retrieve popular routes: ' . $e->getMessage(), 500);
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
        $validator = Validator::make($request->all(), [
            'start_id'      => 'required|exists:districts,id',
            'end_id'        => 'required|exists:districts,id',
            'distance'      => 'required|numeric',
            'duration'      => 'required|string',
            'is_popular'    => 'nullable|numeric',
            'station_ids'   => 'nullable|array',
            'station_ids.*' => 'exists:districts,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();


            $routeId = DB::table('routes')->insertGetId([
                'start_id'   => $request->input('start_id'),
                'end_id'     => $request->input('end_id'),
                'distance'   => $request->input('distance'),
                'duration'   => $request->input('duration'),
                'is_popular'   => $request->input('is_popular') ?? false,
                'created_by' => auth()->user()->id,
                'created_at' => now(),
            ]);

            if ($request->has('station_ids')) {

                foreach ($request->input('station_ids') as $stationId) {
                    DB::table('stations')->insert([
                        'route_id'    => $routeId,
                        'district_id' => $stationId,
                        'created_by'  => auth()->user()->id,
                        'created_at'  => now(),
                    ]);
                }

            }

            $route = DB::table('routes')
                ->join('districts as start', 'routes.start_id', '=', 'start.id')
                ->join('districts as end', 'routes.end_id', '=', 'end.id')
                ->select('routes.id', 'start.name as start_name', 'end.name as end_name', 'routes.distance', 'routes.duration', 'routes.is_popular', 'routes.popular_position', 'routes.status', 'routes.created_at', 'routes.updated_at')
                ->where('routes.id', $routeId)
                ->first();

            DB::commit();

            return $this->successResponse(['data' => $route], 'Route created successfully', 201);
        } catch (\Exception $e) {
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
                ->select('routes.id', 'routes.start_id', 'routes.end_id', 'start.name as start_name', 'end.name as end_name', 'routes.distance', 'routes.duration', 'routes.is_popular', 'routes.popular_position', 'routes.status', 'routes.created_at', 'routes.updated_at')
                ->where('routes.id', $id)
                ->first();

            $stations = DB::table('stations')
                ->select('stations.id', 'route_id', 'dis.name as district_name', 'district_id', 'stations.status', 'stations.created_by', 'stations.updated_by', 'stations.created_at', 'stations.updated_at', 'stations.deleted_at')
                ->join('districts as dis', 'stations.district_id', '=', 'dis.id')
                ->where('stations.route_id', $id)
                ->where('stations.status', 1)
                ->get();

            $route->stations = $stations;

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
        $validator = Validator::make($request->all(), [
            'start_id'      => 'required|exists:districts,id',
            'end_id'        => 'required|exists:districts,id',
            'distance'      => 'required|numeric',
            'duration'      => 'required|string',
            'is_popular'    => 'nullable|numeric',
            'station_ids'   => 'nullable|array',
            'station_ids.*' => 'exists:districts,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            // Begin DB transaction
            DB::beginTransaction();


            $routeData = [
                'start_id'   => $request->input('start_id'),
                'end_id'     => $request->input('end_id'),
                'distance'   => $request->input('distance'),
                'duration'   => $request->input('duration'),
                'updated_by' => auth()->user()->id, // Assuming the user is authenticated
                'updated_at' => now(),
            ];

            if( $request->has('is_popular')){
                $routeData['is_popular'] = $request->input('is_popular');
            }

            // Update the route
            $updated = DB::table('routes')
                ->where('id', $id)
                ->update($routeData);

            DB::table('stations')->where('route_id', $id)->delete();

            if ($request->has('station_ids')) {

                foreach ($request->input('station_ids') as $stationId) {
                    DB::table('stations')->insert([
                        'route_id'    => $id,
                        'district_id' => $stationId,
                        'created_by'  => auth()->user()->id,
                        'created_at'  => now(),
                    ]);
                }

            }

            $route = DB::table('routes')
                ->join('districts as start', 'routes.start_id', '=', 'start.id')
                ->join('districts as end', 'routes.end_id', '=', 'end.id')
                ->select('routes.id', 'start.name as start_name', 'end.name as end_name', 'routes.distance', 'routes.duration', 'routes.is_popular', 'routes.popular_position', 'routes.status', 'routes.created_at', 'routes.updated_at')
                ->where('routes.id', $id)
                ->first();

            if ($updated === 0) {
                return $this->errorResponse('Route not found', 404);
            }

            DB::commit();

            return $this->successResponse($route, 'Route updated successfully', 200);
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update route: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Update popular route positions for multiple routes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePopularPositions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'popular_positions.*'                  => 'required|array',
            'popular_positions.*.id'               => 'required|integer|distinct|exists:routes,id',
            'popular_positions.*.popular_position' => 'required|integer|distinct',
        ], [
            'popular_positions.*.id.required'               => 'Route ID is required.',
            'popular_positions.*.id.exists'                 => 'Route not found.',
            'popular_positions.*.id.integer'                => 'Route ID must be an integer.',
            'popular_positions.*.id.distinct'               => 'Route ID must be unique.',
            'popular_positions.*.popular_position.required' => 'Popular position is required.',
            'popular_positions.*.popular_position.integer'  => 'Popular position must be an integer.',
            'popular_positions.*.popular_position.distinct' => 'Popular position must be unique.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            // Begin DB transaction
            DB::beginTransaction();

            $popular_positions = $request->popular_positions;

            foreach ($popular_positions as $popular_position) {
                $route = Route::findOrFail($popular_position['id']);
                $route->update(['popular_position' => $popular_position['popular_position']]);
            }

            DB::commit();

            return $this->successResponse([], 'Popular route position updated successfully', 200);
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update popular route position: ' . $e->getMessage(), 500);
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

    /**
     * Display the specified route wise counters
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function routeWiseCounters($id)
    {
        try {
            $route = DB::table('routes')
                ->join('districts as start', 'routes.start_id', '=', 'start.id')
                ->join('districts as end', 'routes.end_id', '=', 'end.id')
                ->select('routes.id', 'routes.start_id', 'routes.end_id', 'start.name as start_name', 'end.name as end_name', 'routes.distance', 'routes.duration', 'routes.status', 'routes.created_at', 'routes.updated_at')
                ->where('routes.id', $id)
                ->first();

            if (!$route) {
                return $this->errorResponse('Route not found', 404);
            }

            $counters = DB::table('counters')
                ->leftJoin('routes as route', function ($join) {
                    $join->on('counters.district_id', '=', 'route.start_id')
                        ->orOn('counters.district_id', '=', 'route.end_id');
                })
                ->leftJoin('stations', function ($join) {
                    $join->on('counters.district_id', '=', 'stations.district_id');
                })
                ->where('counters.status', 1)
                ->where(function ($query) use ($id) {
                    $query->where('route.id', $id)
                        ->orWhere('stations.route_id', $id);
                })
                ->select('counters.*')
                ->distinct()
                ->get();

            return $this->successResponse($counters, 'Route wise counters retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve route: ' . $e->getMessage(), 500);
        }

    }

}
