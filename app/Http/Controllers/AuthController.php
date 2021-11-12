<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $requestData = $request->json()->all();

        $validator = Validator::make($requestData, [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->createErrorResponse(400, __('api.login_data_invalid'), 'login_data_invalid', $validator->errors());
        }

        /** @var User $user */

        $user = User::where('email', $requestData['email'])->first();

        //@todo Check if $user is validated?

        if (! $token = auth('users')->attempt([
            'email' => $requestData['email'],
            'password' => $requestData['password']
        ])) {
            return $this->createErrorResponse(401, __('api.login_failed'), 'login_failed');
        }

        $responseData = [
            'token' => $token,
            'expires' => auth('users')->factory()->getTTL() * 60,
        ];

        return $this->createSuccessResponse(200, __('api.login_success'), 'login_success', $responseData)
            ->header('Authorization', "Bearer $token");
    }

    /**
     * Register a User.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $requestData = $request->json()->all();

        $validator = Validator::make($requestData, [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
            return $this->createErrorResponse(400, __('api.registration_data_invalid'), 'registration_data_invalid', $validator->errors());
        }

        $password = Hash::make($requestData['password']);

        $user = new User();

        $user->name = $requestData['name'];
        $user->email = $requestData['email'];

        // Verify email straight away
        $user->email_verified_at = Carbon::now('UTC');

        $user->password = $password;

        if ($user->save()) {
            // Send verification email?
            return $this->createSuccessResponse(200, __('api.registration_success'), 'registration_success');
        }

        return $this->createErrorResponse(500, __('api.registration_failed'), 'registration_failed', $validator->errors());
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(): \Illuminate\Http\JsonResponse
    {
        auth('users')->logout();

        return $this->createSuccessResponse(200, __('api.logout_success'), 'logout_success');
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(): \Illuminate\Http\JsonResponse
    {
        $authedUser = auth('users')->user();

        if ($authedUser) {
            return $this->createSuccessResponse(200, __('api.user_get_success'), 'user_get_success', $authedUser);
        }

        return $this->createErrorResponse(403, __('api.not_authorised'), 'not_authorised');
    }

}
