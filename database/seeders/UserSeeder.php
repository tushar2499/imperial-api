<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::forceCreate([
            'id' => 1,
            'user_name' => 'john_doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password'),
            'role' => 'super-admin',
            'type' => 1,
            'access_type' => 3,
            'account_type' => 1,
            'status' => 1,
        ]);
    }
}
