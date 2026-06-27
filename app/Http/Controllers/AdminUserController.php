<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\AdminUser\AdminUserDestroyRequest;
use App\Http\Requests\Api\AdminUser\AdminUserIndexRequest;
use App\Http\Requests\Api\AdminUser\AdminUserShowRequest;
use App\Http\Requests\Api\AdminUser\AdminUserStoreRequest;
use App\Http\Requests\Api\AdminUser\AdminUserUpdateRequest;
use App\Http\Resources\AdminUserResource;
use App\Services\AdminUserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AdminUserController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AdminUserService $adminUserService) {}

    /**
     * Display a paginated listing of all admin users.
     *
     * @param  AdminUserIndexRequest  $request
     * @return JsonResponse
     */
    public function index(AdminUserIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $users = $this->adminUserService->pagination($attributes);

        return $this->successResponse(
            AdminUserResource::collection($users)->resolve(),
            'Admin users retrieved successfully',
            $this->preparePaginator($users)
        );
    }

    /**
     * Store a newly created admin user.
     *
     * @param  AdminUserStoreRequest  $request
     * @return JsonResponse
     */
    public function store(AdminUserStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $user = $this->adminUserService->store($attributes);

        return $this->createdResponse(new AdminUserResource($user), 'Admin user created successfully');
    }

    /**
     * Display the specified admin user.
     *
     * @param  AdminUserShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(AdminUserShowRequest $request, int $id): JsonResponse
    {
        $user = $this->adminUserService->findById($id);

        return $this->successResponse(new AdminUserResource($user), 'Admin user retrieved successfully');
    }

    /**
     * Update the specified admin user.
     *
     * @param  AdminUserUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(AdminUserUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $user = $this->adminUserService->update($id, $attributes);

        return $this->successResponse(new AdminUserResource($user), 'Admin user updated successfully');
    }

    /**
     * Set the specified admin user to active.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function active(int $id): JsonResponse
    {
        $user = $this->adminUserService->activeById($id);

        return $this->successResponse(new AdminUserResource($user), 'Admin user activated successfully');
    }

    /**
     * Set the specified admin user to inactive.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function inactive(int $id): JsonResponse
    {
        $user = $this->adminUserService->inactiveById($id);

        return $this->successResponse(new AdminUserResource($user), 'Admin user deactivated successfully');
    }

    /**
     * Remove the specified admin user.
     *
     * @param  AdminUserDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(AdminUserDestroyRequest $request, int $id): JsonResponse
    {
        $this->adminUserService->destroy($id);

        return $this->successResponse([], 'Admin user deleted successfully');
    }
}
