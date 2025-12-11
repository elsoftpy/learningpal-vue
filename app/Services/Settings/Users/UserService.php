<?php

namespace App\Services\Settings\Users;

use App\Enums\ProfileTypeEnum;
use App\Models\Profile;
use App\Models\User;
use App\Services\Traits\UserProfileTrait;

class UserService
{
    use UserProfileTrait;

    public function createUser(array $userData, array $profileData): User
    {
        $fullName = $this->getFullName(
            type: ProfileTypeEnum::PERSON->value, // user cannot be a company
            firstName: $profileData['first_name'] ?? null,
            lastName: $profileData['last_name'] ?? null,
            companyName: null,
        );
        
        $profileData['full_name'] = $fullName;

        $profile = Profile::create($profileData);
        $user = $profile->user()->create($userData);

        if (isset($userData['roles'])) {
            $user->assignRole($userData['roles']);
        }

        if (array_key_exists('avatar', $profileData) && $profileData['avatar'] !== null) {
            $profile->addMedia($profileData['avatar'])
                ->toMediaCollection('avatar');
        }

        if (array_key_exists('payment_receipt', $profileData) && $profileData['payment_receipt'] !== null) {
            $profile->addMedia($profileData['payment_receipt'])
                ->toMediaCollection('payment_receipt');
        }

        return $user;
    }

    public function updateUserProfile($user, array $profileData): void
    {
        $profile = $user->profile;

        $fullName = $this->getFullName(
            type: ProfileTypeEnum::PERSON->value, // user cannot be a company
            firstName: $profileData['first_name'] ?? $user->profile->first_name,
            lastName: $profileData['last_name'] ?? $user->profile->last_name,
            companyName: null,
        );
        $profileData['full_name'] = $fullName;

        if (array_key_exists('avatar', $profileData) && $profileData['avatar'] !== null) {
            $profile->addMedia($profileData['avatar'])
                ->toMediaCollection('avatar');

            unset($profileData['avatar']);
        }

        if (array_key_exists('payment_receipt', $profileData) && $profileData['payment_receipt'] !== null) {
            $profile->addMedia($profileData['payment_receipt'])
                ->toMediaCollection('payment_receipt');

            unset($profileData['payment_receipt']);
        }

        $profile->update($profileData);
    }

    public function updateUserData($user, array $userData): void
    {
        if (!empty($userData['password'])) {
            $userData['password'] = bcrypt($userData['password']);
        } else {
            unset($userData['password']);
        }
        
        $user->update($userData);

        if (isset($userData['roles'])) {
            $user->syncRoles($userData['roles']);
        }
    }
}