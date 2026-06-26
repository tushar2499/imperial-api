<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\AuthenticateUserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AuthService $authService) {}

    /**
     * Register a new user and return a JWT token.
     *
     * @param  RegisterRequest  $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->createdResponse([
            'user' => new AuthenticateUserResource($result['user']),
            'token' => $result['token'],
        ], 'User registered successfully');
    }

    /**
     * Authenticate a user and return a JWT token.
     *
     * @param  LoginRequest  $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login($request->validated());

        if (! $token) {
            return $this->unauthenticatedResponse('Invalid credentials');
        }

        return $this->successResponse([
            'user' => new AuthenticateUserResource(JWTAuth::user()),
            'token' => $token,
        ], 'Login successful');
    }

    /**
     * Refresh the current JWT token and return a new one.
     *
     * @return JsonResponse
     */
    public function refreshToken(): JsonResponse
    {
        $token = $this->authService->refreshToken();

        return $this->successResponse(['token' => $token], 'Token refreshed successfully');
    }

    /**
     * Invalidate the current JWT token and log the user out.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return $this->successResponse([], 'Successfully logged out');
    }
}
