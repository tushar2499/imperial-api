<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Auth\UpdatePasswordRequest;
use App\Http\Requests\Api\Auth\UpdatePhotoRequest;
use App\Http\Requests\Api\Auth\UpdateProfileRequest;
use App\Http\Resources\AuthenticateUserResource;
use App\Services\ProfileService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthenticateUserController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ProfileService $profileService) {}

    /**
     * Return the currently authenticated user.
     *
     * @return JsonResponse
     */
    public function authenticateUser(): JsonResponse
    {
        return $this->successResponse(
            new AuthenticateUserResource(auth()->user()),
            'Authenticated user retrieved successfully'
        );
    }

    /**
     * Return the authenticated user's profile.
     *
     * @return JsonResponse
     */
    public function showProfile(): JsonResponse
    {
        return $this->successResponse(
            new AuthenticateUserResource(auth()->user()),
            'Profile retrieved successfully'
        );
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param  UpdateProfileRequest  $request
     * @return JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profileService->updateProfile(auth()->id(), $request->validated());

        return $this->successResponse(new AuthenticateUserResource($user), 'Profile updated successfully');
    }

    /**
     * Replace the authenticated user's profile photo.
     *
     * @param  UpdatePhotoRequest  $request
     * @return JsonResponse
     */
    public function updatePhoto(UpdatePhotoRequest $request): JsonResponse
    {
        $user = $this->profileService->updatePhoto(auth()->id(), $request->validated()['photo']);

        return $this->successResponse(new AuthenticateUserResource($user), 'Profile photo updated successfully');
    }

    /**
     * Update the authenticated user's password.
     *
     * @param  UpdatePasswordRequest  $request
     * @return JsonResponse
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $this->profileService->updatePassword(auth()->id(), $request->validated()['password']);

        return $this->successResponse([], 'Password updated successfully');
    }
}
