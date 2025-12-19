<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use App\Services\Traits\UserProfileTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Support\Facades\Auth;

class ApiAuthenticationController extends Controller
{
    use UserProfileTrait; 
    public function login (LoginRequest $request, AuthService $authService)
    {
        if (! $authService->loginAttempt($request)) {
            return ResponseService::unauthenticated(
                message: __('Unauthenticated.'),
                errors: ['authentication' => [__('Invalid credentials. Please try again.')]]
            );
        }

        $user = Auth::user();
        
        $token = $user->createToken('auth_token')->plainTextToken;

        $userData = array_merge($this->userData($user), ['token' => $token]);

        return ResponseService::success(
            message: __('Login successful.'), 
            data: [
                'user' => $userData,
            ],
        );
    }

    public function register(RegisterRequest $request, AuthService $authService)
    {
        $user = $authService->registerUser($request);
        
        $token = $user->createToken('auth_token')->plainTextToken;

        $userData = array_merge($this->userData($user), ['token' => $token]);
        
        return ResponseService::created(
            message: __('Registration successful.'), 
            data: [
                'user' => $userData,
            ]
        );
    }
}
