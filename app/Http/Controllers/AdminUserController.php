<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of all admin users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $adminUsers = User::get()->map(function ($adminUser) {
                $adminUser->photo = $adminUser->photo ? asset($adminUser->photo) : null;

                return $adminUser;
            });

            return $this->successResponse($adminUsers, 'admin users retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve admin users: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Store a newly created admin user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name'      => 'required|string|max:255|unique:users,user_name',
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'nullable|string|max:255',
            'mobile'         => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'type'           => 'required|between:1,2',
            'password'       => 'nullable|string|min:6',
            'gender'         => 'nullable|string|max:30',
            'district_id'    => 'nullable|exists:districts,id',
            'date_of_birth'  => 'nullable|date|date_format:Y-m-d',
            'role'           => 'nullable|string|max:30',
            'counter_id'     => 'nullable|required_if:type,2|exists:counters,id',
            'access_type'    => 'required|between:1,2,3',
            'account_type'   => 'nullable|between:1,2',
            'front_date'     => 'nullable|numeric',
            'back_date'      => 'nullable|numeric',
            'identity_type'  => 'nullable|string|max:30',
            // 'identity_image' => 'nullable|required_with:identity_type|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'identity_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'sales_status'   => 'nullable|between:0,1',
            'booking_status' => 'nullable|between:0,1',
            'block_status'   => 'nullable|between:0,1',
            'cancel_status'  => 'nullable|between:0,1',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $attributes = $validator->validated();

        $data = [
            'user_name'     => $attributes['user_name'],
            'first_name'    => $attributes['first_name'],
            'last_name'     => $attributes['last_name'] ?? null,
            'mobile'        => $attributes['mobile'] ?? null,
            'email'         => $attributes['email'] ?? null,
            'type'          => $attributes['type'],
            'gender'        => $attributes['gender'] ?? null,
            'district_id'   => $attributes['district_id'] ?? null,
            'date_of_birth' => $attributes['date_of_birth'] ?? null,
            'role'          => $attributes['role'] ?? null,
            'access_type'   => $attributes['access_type'] ?? null,
            'account_type'  => $attributes['account_type'] ?? null,
            'front_date'    => $attributes['front_date'] ?? null,
            'back_date'     => $attributes['back_date'] ?? null,
            'identity_type' => $attributes['identity_type'] ?? null,
            'password'      => $attributes['password'] ? bcrypt($attributes['password']) : bcrypt(123456),
        ];

        if ($attributes['type'] == 2) {
            $data['counter_id'] = $attributes['counter_id'] ?? null;
        } else {
            $data['counter_id'] = null;
        }

        if (isset($attributes['sales_status'])) {
            $data['sales_status'] = $attributes['sales_status'] ?? null;
        } else {
            $data['sales_status'] = null;
        }

        if (isset($attributes['booking_status'])) {
            $data['booking_status'] = $attributes['booking_status'] ?? null;
        } else {
            $data['booking_status'] = null;
        }

        if (isset($attributes['block_status'])) {
            $data['block_status'] = $attributes['block_status'] ?? null;
        } else {
            $data['block_status'] = null;
        }

        if (isset($attributes['cancel_status'])) {
            $data['cancel_status'] = $attributes['cancel_status'] ?? null;
        } else {
            $data['cancel_status'] = null;
        }

        if (isset($attributes['identity_type']) && isset($attributes['identity_image'])) {
            $data['identity_image'] = file_uploaded($request->file('identity_image'), 'users/identity');
        } else {
            $data['identity_image'] = null;
        }

        try {
            DB::beginTransaction();

            $adminUser = User::create($data);

            DB::commit();

            return $this->successResponse(['data' => $adminUser], 'Admin user created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to create admin user: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Display the specified admin user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $adminUser = User::where('id', $id)->firstOrFail();

            return $this->successResponse($adminUser, 'Admin User retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve admin user: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Update the specified admin user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $adminUser = User::where('id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'user_name'      => 'required|string|max:255|unique:users,user_name,' . $adminUser->id,
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'nullable|string|max:255',
            'mobile'         => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'type'           => 'required|between:1,2',
            'password'       => 'nullable|string|min:6',
            'gender'         => 'nullable|string|max:30',
            'district_id'    => 'nullable|exists:districts,id',
            'date_of_birth'  => 'nullable|date|date_format:Y-m-d',
            'role'           => 'nullable|string|max:30',
            'counter_id'     => 'nullable|required_if:type,2|exists:counters,id',
            'access_type'    => 'required|between:1,2,3',
            'account_type'   => 'nullable|between:1,2',
            'front_date'     => 'nullable|numeric',
            'back_date'      => 'nullable|numeric',
            'identity_type'  => 'nullable|string|max:30',
            // 'identity_image' => 'nullable|required_with:identity_type|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'identity_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'sales_status'   => 'nullable|between:0,1',
            'booking_status' => 'nullable|between:0,1',
            'block_status'   => 'nullable|between:0,1',
            'cancel_status'  => 'nullable|between:0,1',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $attributes = $validator->validated();

        $data = [
            'user_name'     => $attributes['user_name'],
            'first_name'    => $attributes['first_name'],
            'last_name'     => $attributes['last_name'] ?? null,
            'mobile'        => $attributes['mobile'] ?? null,
            'email'         => $attributes['email'] ?? null,
            'type'          => $attributes['type'],
            'gender'        => $attributes['gender'] ?? null,
            'district_id'   => $attributes['district_id'] ?? null,
            'date_of_birth' => $attributes['date_of_birth'] ?? null,
            'role'          => $attributes['role'] ?? null,
            'access_type'   => $attributes['access_type'] ?? null,
            'account_type'  => $attributes['account_type'] ?? null,
            'front_date'    => $attributes['front_date'] ?? null,
            'back_date'     => $attributes['back_date'] ?? null,
            'identity_type' => $attributes['identity_type'] ?? null,
        ];

        if (isset($attributes['password'])) {
            $data['password'] = bcrypt($attributes['password']);
        }

        if ($attributes['type'] == 2) {
            $data['counter_id'] = $attributes['counter_id'] ?? null;
        } else {
            $data['counter_id'] = null;
        }

        if (isset($attributes['sales_status'])) {
            $data['sales_status'] = $attributes['sales_status'] ?? null;
        } else {
            $data['sales_status'] = null;
        }

        if (isset($attributes['booking_status'])) {
            $data['booking_status'] = $attributes['booking_status'] ?? null;
        } else {
            $data['booking_status'] = null;
        }

        if (isset($attributes['block_status'])) {
            $data['block_status'] = $attributes['block_status'] ?? null;
        } else {
            $data['block_status'] = null;
        }

        if (isset($attributes['cancel_status'])) {
            $data['cancel_status'] = $attributes['cancel_status'] ?? null;
        } else {
            $data['cancel_status'] = null;
        }

        if (isset($attributes['identity_type']) && isset($attributes['identity_image'])) {
            $data['identity_image'] = file_uploaded($request->file('identity_image'), 'users/identity');
        } else {
            $data['identity_image'] = null;
        }

        try {
            DB::beginTransaction();

            $adminUser->update($data);
            $adminUser->refresh();
            DB::commit();

            return $this->successResponse($adminUser, 'Admin user updated successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update admin user: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Remove the specified admin user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $auth_user_id = auth()->user()->id;

        if ($auth_user_id == $id) {
            return $this->errorResponse('You can not delete yourself', 500);
        }

        try {
            DB::beginTransaction();

            $adminUser = User::where('id', $id)->firstOrFail();

            $adminUser->delete();

            DB::commit();

            return $this->successResponse(null, 'Admin user deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to delete admin user: ' . $e->getMessage(), 500);
        }

    }

}
