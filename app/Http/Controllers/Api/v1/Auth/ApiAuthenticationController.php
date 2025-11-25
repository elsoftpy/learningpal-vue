<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Profile;
use App\Services\Auth\UserService;
use App\Services\Utilities\ResponseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ApiAuthenticationController extends Controller
{
    public function login (LoginRequest $request, UserService $userService)
    {
        if (! $userService->loginAttempt($request)) {
            return ResponseService::unauthenticated(
                message: __('Unauthenticated.'),
                errors: ['authentication' => [__('Invalid credentials. Please try again.')]]
            );
        }

        $user = Auth::user();
        
        $token = $user->createToken('auth_token')->plainTextToken;

        $userData = array_merge($userService->userData($user), ['token' => $token]);

        return ResponseService::success(
            message: __('Login successful.'), 
            data: [
                'user' => $userData,
            ],
        );
    }

    public function register(RegisterRequest $request, UserService $userService)
    {
        $user = $userService->registerUser($request);
        
        $token = $user->createToken('auth_token')->plainTextToken;

        $userData = array_merge($userService->userData($user), ['token' => $token]);

        
        return ResponseService::created(
            message: __('Registration successful.'), 
            data: [
                'user' => $userData,
            ]
        );
    }
}
