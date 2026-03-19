<?php

namespace App\Services\Auth;

use App\Enums\StatusEnum;
use App\Models\User;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Settings\Users\ProfileService;
use App\Services\Settings\Users\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function loginAttempt(LoginRequest $request): bool
    {
        $credentials = [
            'name' => $request->input('name'),
            'password' => $request->input('password'),
            'status' => [StatusEnum::ACTIVE->value, StatusEnum::PENDING->value],
        ];
        
        if (! Auth::attempt($credentials, )) {
             
            return false;
        }

        return true;
    }

    public function registerUser(RegisterRequest $request): User
    {
        return DB::transaction(function () use ($request) {
            $user = (new UserService())->createUser(
                userData: $request->only(['name', 'email', 'password']),
                profileData: $request->except(['name', 'password']),
            );

            $user->assignRole('student');

            return $user;
        });
    }
}