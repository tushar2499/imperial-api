<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerAuthService
{
    /**
     * Register a new customer and return a JWT token.
     *
     * @param  array  $data
     * @return array{customer: Customer, token: string}
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::create([
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
            ]);

            $token = auth('customer')->login($customer);

            return ['customer' => $customer, 'token' => $token];
        });
    }

    /**
     * Attempt to authenticate a customer by email or mobile and return a JWT token, or null on failure.
     *
     * @param  array  $credentials
     * @return string|null
     */
    public function login(array $credentials): ?string
    {
        $field = isset($credentials['email']) ? 'email' : 'mobile';

        $customer = Customer::where($field, $credentials[$field])
            ->where('status', 1)
            ->first();

        if (! $customer || ! Hash::check($credentials['password'], $customer->password)) {
            return null;
        }

        return auth('customer')->login($customer);
    }

    /**
     * Refresh the current customer JWT token.
     *
     * @return string
     */
    public function refreshToken(): string
    {
        return auth('customer')->refresh();
    }

    /**
     * Invalidate the current customer JWT token.
     *
     * @return void
     */
    public function logout(): void
    {
        auth('customer')->logout();
    }
}
