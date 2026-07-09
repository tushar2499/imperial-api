<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\TripInstanceResource;
use App\Services\TripInstanceService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class TripInstanceController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TripInstanceService $tripInstanceService) {}

    public function show(int $id): JsonResponse
    {
        try {
            $tripInstance = $this->tripInstanceService->showById($id);

            return $this->successResponse(new TripInstanceResource($tripInstance), 'Trip instance retrieved successfully');
        } catch (ModelNotFoundException) {
            return $this->notFoundResponse('Trip instance not found');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve trip instance: '.$e->getMessage(), 500);
        }
    }
}
