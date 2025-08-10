<?php

namespace App\Http\Controllers;

use App\Models\CoachConfiguration;
use App\Models\CoachBoardingDropping;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CoachConfigurationController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of coach configurations
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            DB::beginTransaction();

            $query = CoachConfiguration::with([
                'coach',
                'schedule',
                'bus',
                'seatPlan',
                'route',
                'boardingDroppings.counter'
            ]);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('coach_type')) {
                $query->where('coach_type', $request->coach_type);
            }

            if ($request->filled('coach_id')) {
                $query->where('coach_id', $request->coach_id);
            }

            if ($request->filled('schedule_id')) {
                $query->where('schedule_id', $request->schedule_id);
            }

            if ($request->filled('route_id')) {
                $query->where('route_id', $request->route_id);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $configurations = $query->paginate($perPage);

            DB::commit();

            return $this->successResponse($configurations, 'Coach configurations retrieved successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve coach configurations: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created coach configuration with boarding/dropping points
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coach_id' => 'required|exists:coaches,id',
            'schedule_id' => 'required|exists:schedules,id',
            'bus_id' => 'required|exists:buses,id',
            'seat_plan_id' => 'required|exists:seat_plans,id',
            'route_id' => 'required|exists:routes,id',
            'coach_type' => 'required|in:1,2',
            'status' => 'sometimes|in:0,1',

            // Boarding/Dropping points validation
            'boarding_dropping_points' => 'required|array|min:1',
            'boarding_dropping_points.*.counter_id' => 'required|exists:counters,id',
            'boarding_dropping_points.*.type' => 'required|in:1,2',
            'boarding_dropping_points.*.time' => 'required|date_format:H:i',
            'boarding_dropping_points.*.starting_point_status' => 'sometimes|boolean',
            'boarding_dropping_points.*.ending_point_status' => 'sometimes|boolean',
            'boarding_dropping_points.*.status' => 'sometimes|in:0,1'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Create coach configuration
            $configuration = CoachConfiguration::create([
                'coach_id' => $request->input('coach_id'),
                'schedule_id' => $request->input('schedule_id'),
                'bus_id' => $request->input('bus_id'),
                'seat_plan_id' => $request->input('seat_plan_id'),
                'route_id' => $request->input('route_id'),
                'coach_type' => $request->input('coach_type'),
                'status' => $request->input('status', 1),
                'created_by' => auth()->user()->id,
            ]);

            // Create boarding/dropping points
            foreach ($request->input('boarding_dropping_points') as $point) {
                CoachBoardingDropping::create([
                    'coach_configuration_id' => $configuration->id,
                    'counter_id' => $point['counter_id'],
                    'type' => $point['type'],
                    'time' => $point['time'],
                    'starting_point_status' => $point['starting_point_status'] ?? 0,
                    'ending_point_status' => $point['ending_point_status'] ?? 0,
                    'status' => $point['status'] ?? 1,
                    'created_by' => auth()->user()->id,
                ]);
            }

            // Load relationships for response
            $configuration->load([
                'coach',
                'schedule',
                'bus',
                'seatPlan',
                'route',
                'boardingDroppings.counter'
            ]);

            DB::commit();

            return $this->successResponse(['data' => $configuration], 'Coach configuration created successfully', 201);

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to create coach configuration: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified coach configuration
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $coachConfiguration = CoachConfiguration::with([
                'coach',
                'schedule',
                'bus',
                'seatPlan',
                'route',
                'boardingDroppings.counter'
            ])->where('id', $id)->firstOrFail();

            return $this->successResponse($coachConfiguration, 'Coach configuration retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve coach configuration: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified coach configuration with boarding/dropping points
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'coach_id' => 'sometimes|exists:coaches,id',
            'schedule_id' => 'sometimes|exists:schedules,id',
            'bus_id' => 'sometimes|exists:buses,id',
            'seat_plan_id' => 'sometimes|exists:seat_plans,id',
            'route_id' => 'sometimes|exists:routes,id',
            'coach_type' => 'sometimes|in:1,2',
            'status' => 'sometimes|in:0,1',

            // Boarding/Dropping points validation
            'boarding_dropping_points' => 'sometimes|array',
            'boarding_dropping_points.*.id' => 'sometimes|exists:coach_boarding_droppings,id',
            'boarding_dropping_points.*.counter_id' => 'required|exists:counters,id',
            'boarding_dropping_points.*.type' => 'required|in:1,2',
            'boarding_dropping_points.*.time' => 'required|date_format:H:i',
            'boarding_dropping_points.*.starting_point_status' => 'sometimes|boolean',
            'boarding_dropping_points.*.ending_point_status' => 'sometimes|boolean',
            'boarding_dropping_points.*.status' => 'sometimes|in:0,1'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $coachConfiguration = CoachConfiguration::where('id', $id)->firstOrFail();

            // Update coach configuration
            $configurationData = [];
            if ($request->filled('coach_id')) $configurationData['coach_id'] = $request->input('coach_id');
            if ($request->filled('schedule_id')) $configurationData['schedule_id'] = $request->input('schedule_id');
            if ($request->filled('bus_id')) $configurationData['bus_id'] = $request->input('bus_id');
            if ($request->filled('seat_plan_id')) $configurationData['seat_plan_id'] = $request->input('seat_plan_id');
            if ($request->filled('route_id')) $configurationData['route_id'] = $request->input('route_id');
            if ($request->filled('coach_type')) $configurationData['coach_type'] = $request->input('coach_type');
            if ($request->filled('status')) $configurationData['status'] = $request->input('status');

            if (!empty($configurationData)) {
                $configurationData['updated_by'] = auth()->user()->id;
                $configurationData['updated_at'] = now();
                $coachConfiguration->update($configurationData);
            }

            // Handle boarding/dropping points if provided
            if ($request->filled('boarding_dropping_points')) {
                // Get existing point IDs
                $existingIds = $coachConfiguration->boardingDroppings()->pluck('id')->toArray();
                $updatedIds = [];

                foreach ($request->input('boarding_dropping_points') as $point) {
                    if (isset($point['id'])) {
                        // Update existing point
                        $boardingDropping = CoachBoardingDropping::find($point['id']);
                        if ($boardingDropping && $boardingDropping->coach_configuration_id == $coachConfiguration->id) {
                            $boardingDropping->update([
                                'counter_id' => $point['counter_id'],
                                'type' => $point['type'],
                                'time' => $point['time'],
                                'starting_point_status' => $point['starting_point_status'] ?? 0,
                                'ending_point_status' => $point['ending_point_status'] ?? 0,
                                'status' => $point['status'] ?? 1,
                                'updated_by' => auth()->user()->id,
                                'updated_at' => now(),
                            ]);
                            $updatedIds[] = $point['id'];
                        }
                    } else {
                        // Create new point
                        $newPoint = CoachBoardingDropping::create([
                            'coach_configuration_id' => $coachConfiguration->id,
                            'counter_id' => $point['counter_id'],
                            'type' => $point['type'],
                            'time' => $point['time'],
                            'starting_point_status' => $point['starting_point_status'] ?? 0,
                            'ending_point_status' => $point['ending_point_status'] ?? 0,
                            'status' => $point['status'] ?? 1,
                            'created_by' => auth()->user()->id,
                        ]);
                        $updatedIds[] = $newPoint->id;
                    }
                }

                // Delete points that were not included in the update
                $idsToDelete = array_diff($existingIds, $updatedIds);
                if (!empty($idsToDelete)) {
                    CoachBoardingDropping::whereIn('id', $idsToDelete)->delete();
                }
            }

            // Load relationships for response
            $coachConfiguration = $coachConfiguration->fresh()->load([
                'coach',
                'schedule',
                'bus',
                'seatPlan',
                'route',
                'boardingDroppings.counter'
            ]);

            DB::commit();

            return $this->successResponse($coachConfiguration, 'Coach configuration updated successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update coach configuration: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified coach configuration and its boarding/dropping points
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $deleted = CoachConfiguration::where('id', $id)->delete();

            if ($deleted === 0) {
                return $this->errorResponse('Coach configuration not found', 404);
            }

            DB::commit();

            return $this->successResponse(null, 'Coach configuration deleted successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to delete coach configuration: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get coach configurations by schedule
     *
     * @param int $scheduleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBySchedule($scheduleId)
    {
        try {
            DB::beginTransaction();

            $configurations = CoachConfiguration::with([
                'coach',
                'bus',
                'seatPlan',
                'route',
                'boardingDroppings.counter'
            ])->where('schedule_id', $scheduleId)->get();

            DB::commit();

            return $this->successResponse($configurations, 'Coach configurations retrieved successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve coach configurations: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get coach configurations by coach
     *
     * @param int $coachId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCoach($coachId)
    {
        try {
            DB::beginTransaction();

            $configurations = CoachConfiguration::with([
                'schedule',
                'bus',
                'seatPlan',
                'route',
                'boardingDroppings.counter'
            ])->where('coach_id', $coachId)->get();

            DB::commit();

            return $this->successResponse($configurations, 'Coach configurations retrieved successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve coach configurations: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get coach configurations by route
     *
     * @param int $routeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByRoute($routeId)
    {
        try {
            DB::beginTransaction();

            $configurations = CoachConfiguration::with([
                'coach',
                'schedule',
                'bus',
                'seatPlan',
                'boardingDroppings.counter'
            ])->where('route_id', $routeId)->get();

            DB::commit();

            return $this->successResponse($configurations, 'Coach configurations retrieved successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve coach configurations: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Toggle the status of a coach configuration
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus($id)
    {
        try {
            DB::beginTransaction();

            $coachConfiguration = CoachConfiguration::where('id', $id)->firstOrFail();

            $newStatus = $coachConfiguration->status === 1 ? 0 : 1;
            $coachConfiguration->update([
                'status' => $newStatus,
                'updated_by' => auth()->user()->id,
                'updated_at' => now(),
            ]);

            $coachConfiguration = $coachConfiguration->refresh();

            DB::commit();

            return $this->successResponse($coachConfiguration, 'Coach configuration status updated successfully');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update coach configuration status: ' . $e->getMessage(), 500);
        }
    }

}
