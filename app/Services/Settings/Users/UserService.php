<?php

namespace App\Services\Settings\Users;

use App\Enums\StatusEnum;
use App\Models\User;
use Transliterator;

class UserService
{
    protected function getUsername(string $fullName): string
    {
        $userName =  str_replace(' ', '', strtolower(trim($fullName)));

        $existingUserCount = User::where('name', 'like', $userName . '%')->count();
        
        $userName = $this->normalizeString($userName);

        if ($existingUserCount > 0) {
            $userName .= str_pad($existingUserCount + 1, 3, '0', STR_PAD_LEFT);
        }

        return $userName;
    }

    protected function normalizeString(?string $value): ?string
    {
        $transliterator = Transliterator::create('Any-Latin; Latin-ASCII');
        
        return $transliterator->transliterate($value);
    }

    public function createUser(array $userData, array $profileData): User
    {
        $profile = (new ProfileService())->createProfile($profileData);

        if (empty($userData['name'])) {
            $userData['name'] = $this->getUsername($profile->full_name ?? 'user');
        
        }
        
        $userData['status'] = StatusEnum::PENDING->value;

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
        (new ProfileService())->updateProfile($profile, $profileData);

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