<?php

namespace App\Services\Settings\Users;

use App\Enums\ProfileTypeEnum;
use App\Services\Traits\ProfileTrait;

class UserService
{
    use ProfileTrait;

    public function updateUserProfile($user, array $profileData): void
    {
        $fullName = $this->getFullName(
            type: ProfileTypeEnum::PERSON->value, // user cannot be a company
            firstName: $profileData['first_name'] ?? $user->profile->first_name,
            lastName: $profileData['last_name'] ?? $user->profile->last_name,
            companyName: null,
        );
        $profileData['full_name'] = $fullName;
        
        $user->profile->update($profileData);
    }

    public function updateUserData($user, array $userData): void
    {
        $user->update($userData);

        if (isset($userData['roles'])) {
            $user->syncRoles($userData['roles']);
        }
    }
}