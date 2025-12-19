<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use App\Services\Traits\UserProfileTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    use UserProfileTrait;

    public function login(LoginRequest $request, AuthService $authService)
    {

        if (! $authService->loginAttempt($request)) {
            return ResponseService::unauthenticated(
                message: __('Unauthenticated.'),
                errors: ['authentication' => [__('Invalid credentials. Please try again.')]]
            );
        }

        $request->session()->regenerate();
        
        $userData = $this->userData(Auth::user());

        return ResponseService::success(
            message: __('Login successful.'),
            data: [
                'user' => $userData,
            ]
        );
    }

    public function register(RegisterRequest $request, AuthService $authService)
    {   
        $user = $authService->registerUser($request);

        // Auto-login after registration
        Auth::login($user);
        
        $userData = $this->userData($user);

        return ResponseService::created(
            message: __('Registration successful.'),
            data: [
                'user' => $userData,
            ]
        );
    }
    

    public function logout(Request $request)
    {
        Auth::logout();
 
        $request->session()->invalidate();
    
        $request->session()->regenerateToken();

        return ResponseService::success(
            message: __('Logged out successfully.')
        );
    }

    /**
     * Return current logged in user
     */
    public function me(Request $request, AuthService $authService)
    {
        $user = $request->user();

        $userData = $this->userData($user);
        
        return ResponseService::success(
            message: __('Authenticated user retrieved successfully.'),
            data: [
                'user' => $userData
            ]
        );
    }
}
