<?php

namespace App\Services\Settings\Users;

use App\Enums\ProfileTypeEnum;
use App\Services\Traits\UserProfileTrait;

class UserService
{
    use UserProfileTrait;

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