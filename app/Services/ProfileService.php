<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    /**
     * Update the authenticated user's profile fields inside a database transaction.
     *
     * @param  int  $userId
     * @param  array  $attributes
     * @return User
     */
    public function updateProfile(int $userId, array $attributes): User
    {
        return DB::transaction(function () use ($userId, $attributes) {
            $user = User::find($userId);

            $user->update([
                'user_name' => $attributes['user_name'],
                'first_name' => $attributes['first_name'],
                'last_name' => $attributes['last_name'] ?? null,
                'mobile' => $attributes['mobile'] ?? null,
                'email' => $attributes['email'] ?? null,
                'gender' => $attributes['gender'] ?? null,
                'date_of_birth' => $attributes['date_of_birth'] ?? null,
            ]);

            return $user->fresh();
        });
    }

    /**
     * Replace the authenticated user's profile photo inside a database transaction.
     *
     * @param  int  $userId
     * @param  UploadedFile  $photo
     * @return User
     */
    public function updatePhoto(int $userId, UploadedFile $photo): User
    {
        return DB::transaction(function () use ($userId, $photo) {
            $user = User::find($userId);

            delete_uploaded_file($user->photo);

            $user->update(['photo' => file_uploaded($photo, 'users')]);

            return $user->fresh();
        });
    }

    /**
     * Update the authenticated user's password inside a database transaction.
     *
     * @param  int  $userId
     * @param  string  $newPassword
     * @return void
     */
    public function updatePassword(int $userId, string $newPassword): void
    {
        DB::transaction(function () use ($userId, $newPassword) {
            User::find($userId)->update(['password' => Hash::make($newPassword)]);
        });
    }
}
