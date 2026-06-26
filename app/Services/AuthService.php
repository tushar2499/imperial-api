<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'user_name' => $data['user_name'],
                'first_name' => $data['user_name'],
                'password' => Hash::make($data['password']),
            ]);

            $token = JWTAuth::fromUser($user);

            return ['user' => $user, 'token' => $token];
        });
    }

    public function login(array $credentials): ?string
    {
        return JWTAuth::attempt([
            'user_name' => $credentials['user_name'],
            'password' => $credentials['password'],
        ]) ?: null;
    }

    public function refreshToken(): string
    {
        return JWTAuth::refresh(JWTAuth::getToken());
    }

    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }
}
