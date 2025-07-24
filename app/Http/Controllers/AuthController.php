<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ApiResponse;

// Use the ApiResponse trait

    /**
     * Register a new user with DB transaction and error handling
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'user_name' => 'required|string|max:255|unique:users',
                'password'  => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = User::create([
                'user_name'  => $request->user_name,
                'first_name' => $request->user_name,
                'password'   => Hash::make($request->password),
            ]);

            $token = JWTAuth::fromUser($user);

            DB::commit();

            return $this->successResponse([
                'user'  => $user,
                'token' => $token,
            ], 'User registered successfully');

        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse('Registration failed: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Login the user and return JWT token
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {

        try {
            // Validate login data
            $validator = Validator::make($request->all(), [
                'user_name' => 'required|string',
                'password'  => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Attempt to authenticate using user_name and password
            if (!$token = JWTAuth::attempt(['user_name' => $request->user_name, 'password' => $request->password])) {
                return $this->unauthorizedResponse('Unauthorized', 401);
            }

            // Retrieve the authenticated user
            $user = JWTAuth::user();

            // Return success response with user data and token

            return $this->successResponse([
                'user'  => $user, // Send user data
                'token' => $token, // Send the generated token
            ], 'Login successful');

        } catch (Exception $e) {
            // Return error response if something goes wrong
            return $this->errorResponse('Login failed: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Logout the user by invalidating the JWT token
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // Invalidate the JWT token
            JWTAuth::invalidate(JWTAuth::getToken());

            return $this->successResponse(null, 'Successfully logged out');
        } catch (Exception $e) {
            // Return error response if something goes wrong
            return $this->errorResponse('Logout failed: ' . $e->getMessage(), 500);
        }

    }

}
