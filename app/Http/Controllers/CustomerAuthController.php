<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Auth\CustomerLoginRequest;
use App\Http\Requests\Api\Auth\CustomerRegisterRequest;
use App\Http\Requests\Api\Auth\CustomerUpdatePasswordRequest;
use App\Http\Requests\Api\Auth\CustomerUpdatePhotoRequest;
use App\Http\Requests\Api\Auth\CustomerUpdateProfileRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerAuthService;
use App\Services\CustomerProfileService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CustomerAuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CustomerAuthService $customerAuthService,
        private readonly CustomerProfileService $customerProfileService,
    ) {}

    /**
     * Register a new customer and return a JWT token.
     */
    public function register(CustomerRegisterRequest $request): JsonResponse
    {
        $result = $this->customerAuthService->register($request->validated());

        return $this->createdResponse([
            'user' => new CustomerResource($result['customer']),
            'token' => $result['token'],
        ], 'Customer registered successfully');
    }

    /**
     * Authenticate a customer and return a JWT token.
     */
    public function login(CustomerLoginRequest $request): JsonResponse
    {
        $token = $this->customerAuthService->login($request->validated());

        if (! $token) {
            return $this->unauthenticatedResponse('Invalid credentials');
        }

        return $this->successResponse([
            'user' => new CustomerResource(auth('customer')->user()),
            'token' => $token,
        ], 'Login successful');
    }

    /**
     * Refresh the current customer JWT token.
     */
    public function refreshToken(): JsonResponse
    {
        $token = $this->customerAuthService->refreshToken();

        return $this->successResponse(['token' => $token], 'Token refreshed successfully');
    }

    /**
     * Invalidate the current customer JWT token.
     */
    public function logout(): JsonResponse
    {
        $this->customerAuthService->logout();

        return $this->successResponse([], 'Successfully logged out');
    }

    /**
     * Return the currently authenticated customer.
     */
    public function me(): JsonResponse
    {
        return $this->successResponse(
            new CustomerResource(auth('customer')->user()),
            'Authenticated user retrieved successfully'
        );
    }

    /**
     * Return the authenticated customer's profile.
     */
    public function showProfile(): JsonResponse
    {
        return $this->successResponse(
            new CustomerResource(auth('customer')->user()),
            'Profile retrieved successfully'
        );
    }

    /**
     * Update the authenticated customer's profile.
     */
    public function updateProfile(CustomerUpdateProfileRequest $request): JsonResponse
    {
        $customer = $this->customerProfileService->updateProfile(
            auth('customer')->id(),
            $request->validated()
        );

        return $this->successResponse(new CustomerResource($customer), 'Profile updated successfully');
    }

    /**
     * Replace the authenticated customer's profile photo.
     */
    public function updatePhoto(CustomerUpdatePhotoRequest $request): JsonResponse
    {
        $customer = $this->customerProfileService->updatePhoto(
            auth('customer')->id(),
            $request->validated()['photo']
        );

        return $this->successResponse(new CustomerResource($customer), 'Profile photo updated successfully');
    }

    /**
     * Update the authenticated customer's password.
     */
    public function updatePassword(CustomerUpdatePasswordRequest $request): JsonResponse
    {
        $this->customerProfileService->updatePassword(
            auth('customer')->id(),
            $request->validated()['password']
        );

        return $this->successResponse([], 'Password updated successfully');
    }
}
