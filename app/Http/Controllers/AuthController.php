<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->createdResponse($result, 'User registered successfully');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login($request->validated());

        if (! $token) {
            return $this->unauthenticatedResponse('Invalid credentials');
        }

        $user = JWTAuth::user();

        return $this->successResponse(['user' => $user, 'token' => $token], 'Login successful');
    }

    public function refreshToken(): JsonResponse
    {
        $token = $this->authService->refreshToken();

        return $this->successResponse(['token' => $token], 'Token refreshed successfully');
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return $this->successResponse([], 'Successfully logged out');
    }
}
