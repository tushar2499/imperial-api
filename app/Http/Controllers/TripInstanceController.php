<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\TripInstance\TripInstanceAllActiveRequest;
use App\Http\Requests\Api\TripInstance\TripInstanceDestroyRequest;
use App\Http\Requests\Api\TripInstance\TripInstanceIndexRequest;
use App\Http\Requests\Api\TripInstance\TripInstanceMigrateRequest;
use App\Http\Requests\Api\TripInstance\TripInstanceShowRequest;
use App\Http\Requests\Api\TripInstance\TripInstanceStoreRequest;
use App\Http\Requests\Api\TripInstance\TripInstanceUpdateRequest;
use App\Http\Resources\TripInstanceResource;
use App\Services\TripInstanceService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripInstanceController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TripInstanceService $tripInstanceService) {}

    public function index(TripInstanceIndexRequest $request): JsonResponse
    {
        try {
            $data = $this->tripInstanceService->paginate($request->validated());

            return $this->successResponse($data, 'Trip instances retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve trip instances: '.$e->getMessage(), 500);
        }
    }

    public function store(TripInstanceStoreRequest $request): JsonResponse
    {
        try {
            $result = $this->tripInstanceService->store($request->validated());

            return $this->createdResponse($result, 'Trip instance created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create trip instance: '.$e->getMessage(), 500);
        }
    }

    public function show(TripInstanceShowRequest $request, int $id): JsonResponse
    {
        try {
            $data = $this->tripInstanceService->showById($id);

            return $this->successResponse($data, 'Trip instance retrieved successfully');
        } catch (ModelNotFoundException) {
            return $this->notFoundResponse('Trip instance not found');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve trip instance: '.$e->getMessage(), 500);
        }
    }

    public function update(TripInstanceUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $tripInstance = $this->tripInstanceService->update($id, $request->validated());

            return $this->successResponse($tripInstance, 'Trip instance updated successfully');
        } catch (ModelNotFoundException) {
            return $this->notFoundResponse('Trip instance not found');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update trip instance: '.$e->getMessage(), 500);
        }
    }

    public function destroy(TripInstanceDestroyRequest $request, int $id): JsonResponse
    {
        try {
            $this->tripInstanceService->destroy($id);

            return $this->successResponse(null, 'Trip instance deleted successfully');
        } catch (ModelNotFoundException) {
            return $this->notFoundResponse('Trip instance not found');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete trip instance: '.$e->getMessage(), 500);
        }
    }

    public function migrate(TripInstanceMigrateRequest $request, int $id): JsonResponse
    {
        try {
            $tripInstance = $this->tripInstanceService->migrate($id, $request->validated());

            return $this->successResponse($tripInstance, 'Trip instance migrated successfully');
        } catch (ModelNotFoundException) {
            return $this->notFoundResponse('Trip instance not found');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to migrate trip instance: '.$e->getMessage(), 500);
        }
    }

    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $tripInstance = $this->tripInstanceService->toggleStatus($id);

            return $this->successResponse($tripInstance, 'Trip instance status updated successfully');
        } catch (ModelNotFoundException) {
            return $this->notFoundResponse('Trip instance not found');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update trip instance status: '.$e->getMessage(), 500);
        }
    }

    public function allActive(TripInstanceAllActiveRequest $request): JsonResponse
    {
        $tripInstances = $this->tripInstanceService->allActive($request->validated()['trip_date'] ?? null);

        return $this->successResponse(
            TripInstanceResource::collection($tripInstances)->resolve(),
            'All active trip instances retrieved successfully'
        );
    }

    public function active(int $id, Request $request): JsonResponse
    {
        try {
            $tripInstance = $this->tripInstanceService->activeById($id, $request->query('trip_date'));

            return $this->successResponse(new TripInstanceResource($tripInstance), 'Trip instance activated successfully');
        } catch (ModelNotFoundException) {
            return $this->notFoundResponse('Trip instance not found');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function inactive(int $id, Request $request): JsonResponse
    {
        try {
            $tripInstance = $this->tripInstanceService->inactiveById($id, $request->query('trip_date'));

            return $this->successResponse(new TripInstanceResource($tripInstance), 'Trip instance deactivated successfully');
        } catch (ModelNotFoundException) {
            return $this->notFoundResponse('Trip instance not found');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}
