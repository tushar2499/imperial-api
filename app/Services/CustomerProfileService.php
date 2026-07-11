<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerProfileService
{
    /**
     * Update the authenticated customer's profile fields.
     *
     * @param  int  $customerId
     * @param  array  $attributes
     * @return Customer
     */
    public function updateProfile(int $customerId, array $attributes): Customer
    {
        return DB::transaction(function () use ($customerId, $attributes) {
            $customer = Customer::find($customerId);

            $customer->update([
                'name' => $attributes['name'],
                'gender' => $attributes['gender'] ?? null,
                'age' => $attributes['age'] ?? null,
                'address' => $attributes['address'] ?? null,
                'nationality' => $attributes['nationality'] ?? null,
                'passport_no' => $attributes['passport_no'] ?? null,
                'nid' => $attributes['nid'] ?? null,
            ]);

            return $customer->fresh();
        });
    }

    /**
     * Replace the authenticated customer's profile photo.
     *
     * @param  int  $customerId
     * @param  UploadedFile  $photo
     * @return Customer
     */
    public function updatePhoto(int $customerId, UploadedFile $photo): Customer
    {
        return DB::transaction(function () use ($customerId, $photo) {
            $customer = Customer::find($customerId);

            delete_uploaded_file($customer->photo);

            $customer->update(['photo' => file_uploaded($photo, 'customers')]);

            return $customer->fresh();
        });
    }

    /**
     * Update the authenticated customer's password.
     *
     * @param  int  $customerId
     * @param  string  $newPassword
     * @return void
     */
    public function updatePassword(int $customerId, string $newPassword): void
    {
        DB::transaction(function () use ($customerId, $newPassword) {
            Customer::find($customerId)->update(['password' => Hash::make($newPassword)]);
        });
    }
}
