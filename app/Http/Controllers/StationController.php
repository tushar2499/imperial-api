<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Make sure the trait is imported

class StationController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of stations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage    = min((int) $request->get('per_page', 15), 1000); // Cap at 1000
            $page       = max((int) $request->get('page', 1), 1); // Minimum page 1
            $searchTerm = $request->get('search');

            $query = DB::table('stations')
                ->select('stations.id', 'route_id', 'dis.name as district_name', 'district_id', 'stations.status', 'stations.created_by', 'stations.updated_by', 'stations.created_at', 'stations.updated_at', 'stations.deleted_at')
                ->join('districts as dis', 'stations.district_id', '=', 'dis.id')
                ->where(function ($query) use ($searchTerm) {
                    $query->where('dis.name', 'like', "%{$searchTerm}%");
                })
                ->orderBy('stations.created_at', 'desc');

            $stations = $query->paginate($perPage, ['*'], 'page', $page);

            return $this->successResponse($stations, 'Stations retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve stations: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Store a newly created station.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'route_id'      => 'required|exists:routes,id',
            'district_id'   => 'required|array|min:1',
            'district_id.*' => 'required|exists:districts,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $stations = [];

            foreach ($request->input('district_id') as $districtId) {
                $stationId = DB::table('stations')->insertGetId([
                    'route_id'    => $request->input('route_id'),
                    'district_id' => $districtId,
                    'status'      => 1,
                    'created_by'  => auth()->user()->id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                $station = DB::table('stations')
                    ->select('id', 'route_id', 'district_id', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at')
                    ->where('id', $stationId)
                    ->first();

                $stations[] = $station;
            }

            DB::commit();

            return $this->successResponse(['data' => $stations], 'Stations created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to create stations: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Display the specified station.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            DB::beginTransaction();

            $station = DB::table('stations')
                ->select('stations.id', 'route_id', 'dis.name as district_name', 'district_id', 'stations.status', 'stations.created_by', 'stations.updated_by', 'stations.created_at', 'stations.updated_at', 'stations.deleted_at')
                ->join('districts as dis', 'stations.district_id', '=', 'dis.id')
                ->where('stations.id', $id)
                ->first();

            if (!$station) {
                return $this->errorResponse('Station not found', 404);
            }

            DB::commit();

            return $this->successResponse($station, 'Station retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve station: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Update the specified station.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'route_id'    => 'required|exists:routes,id',
            'district_id' => 'required|exists:districts,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Update the station
            $updated = DB::table('stations')
                ->where('id', $id)
                ->update([
                    'route_id'    => $request->input('route_id'),
                    'district_id' => $request->input('district_id'),
                    'updated_by'  => auth()->user()->id,
                    'updated_at'  => now(),
                ]);

            if ($updated === 0) {
                return $this->errorResponse('Station not found', 404);
            }

            $station = DB::table('stations')
                ->select('id', 'route_id', 'district_id', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at')
                ->where('id', $id)
                ->first();

            DB::commit();

            return $this->successResponse($station, 'Station updated successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update station: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Remove the specified station.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Soft delete the station
            $deleted = DB::table('stations')->where('id', $id)->delete();

            if ($deleted === 0) {
                return $this->errorResponse('Station not found', 404);
            }

            DB::commit();

            return $this->successResponse(null, 'Station deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to delete station: ' . $e->getMessage(), 500);
        }

    }

}
