<?php

namespace App\Services\Auth;

use App\Enums\StatusEnum;
use App\Models\User;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserService
{
    protected function getFullName(string $type, ?string $firstName, ?string $lastName, ?string $companyName): string
    {
        if ($type === 'person') {
            return trim($firstName . ' ' . $lastName);
        } 
        return trim($companyName);
    }

    protected function getUsername(string $fullName): string
    { 
        return str_replace(' ', '', trim($fullName));
    }

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

    public function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'type' => $user->profile->type ?? null,
            'first_name' => $user->profile->first_name ?? null,
            'last_name' => $user->profile->last_name ?? null,
            'company_name' => $user->profile->company_name ?? null,
            'email' => $user->email,
        ];
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