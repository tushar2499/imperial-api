<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthenticateUserController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of authenticate user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticateUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $auth = auth()->user();

        return response()->json($auth);
    }

    /**
     * Display a authenticate user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $authUser        = auth()->user();
        $authUser->photo = $authUser->photo ? asset($authUser->photo) : null;

        return $this->successResponse($authUser, 'Authenticate user profile retrieved successfully');
    }

    /**
     * Update the specified authenticate user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        try {
            $authUser = auth()->user();
            $user     = User::where('id', $authUser->id)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'user_name'     => 'required|string|max:255|unique:users,user_name,' . $user->id,
                'first_name'    => 'required|string|max:255',
                'last_name'     => 'nullable|string|max:255',
                'mobile'        => 'nullable|string|max:255',
                'email'         => 'nullable|email|max:255',
                'gender'        => 'nullable|string|max:30',
                'date_of_birth' => 'nullable|date|date_format:Y-m-d',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $attributes = $validator->validated();

            DB::beginTransaction();

            $updatedData = [
                'user_name'     => $attributes['user_name'],
                'first_name'    => $attributes['first_name'],
                'last_name'     => $attributes['last_name'] ?? null,
                'mobile'        => $attributes['mobile'] ?? null,
                'email'         => $attributes['email'] ?? null,
                'gender'        => $attributes['gender'] ?? null,
                'date_of_birth' => $attributes['date_of_birth'] ?? null,
            ];

            $user->update($updatedData);

            DB::commit();

            return $this->successResponse($user, 'Update authenticate user profile successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update profile: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Update the specified authenticate user profile photo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePhoto(Request $request)
    {
        try {
            $authUser = auth()->user();
            $user     = User::where('id', $authUser->id)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $attributes = $validator->validated();

            DB::beginTransaction();
            $photo = null;

            if (isset($attributes['photo'])) {
                $photo = file_uploaded($request->file('photo'), 'users');
                /**
                 * Unlink previous photo
                 */

                if ($user->photo && file_exists($user->photo)) {
                    unlink($user->photo);
                }

            } else {
                return $this->errorResponse('Photo is required', 400);
            }

            $updatedData = [
                'photo' => $photo,
            ];

            $user->update($updatedData);
            $user = $user->refresh();

            $userPhoto = $user->photo ? asset($user->photo) : null;

            DB::commit();

            return $this->successResponse($userPhoto, 'Update authenticate user profile photo successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update profile photo: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Update the specified authenticate user password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        try {
            $authUser = auth()->user();
            $user     = User::where('id', $authUser->id)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'current_password'      => 'required|string|min:6',
                'password'              => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $attributes = $validator->validated();

            if ($attributes['password'] !== $attributes['password_confirmation']) {
                return $this->errorResponse('Password and confirm password does not match', 400);
            }

            if (!Hash::check($attributes['current_password'], $user->password)) {
                return $this->errorResponse('Current password is incorrect', 400);
            }

            DB::beginTransaction();

            $updatedData = [
                'password' => bcrypt($attributes['password']),
            ];

            $user->update($updatedData);

            DB::commit();

            return $this->successResponse([], 'Update authenticate user password successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update password: ' . $e->getMessage(), 500);
        }

    }

}
