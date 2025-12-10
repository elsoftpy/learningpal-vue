<?php

namespace App\Services\Auth;

use App\Enums\StatusEnum;
use App\Models\User;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Profile;
use App\Services\Traits\UserProfileTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserService
{
   use UserProfileTrait;

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
            $fullName = $this->getFullName(
                type: $request->input('type', 'person'),
                firstName: $request->input('first_name'),
                lastName: $request->input('last_name'),
                companyName: $request->input('company_name', '')
            );

            $profile = Profile::create(array_merge(
                $request->except('password', 'password_confirmation'),
                [
                    'full_name' => $fullName,
                ]
            ));

            $user = $profile->user()->create([
                'name' => $this->getUsername($profile->full_name),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password')),
                'status' => StatusEnum::PENDING->value,
            ]);

            $user->assignRole('student');

            return $user;
        });
    }




}