<?php

namespace App\Services\Settings\Users;

use App\Models\User;
use Transliterator;

class UserService
{
    protected function getUsername(array $profileData): string
    {
        $userName = $this->buildBaseUsername($profileData);

        $existingUserCount = User::where('name', 'like', $userName . '%')->count();

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

    protected function buildBaseUsername(array $profileData): string
    {
        $firstName = trim((string) ($profileData['first_name'] ?? ''));
        $lastName = trim((string) ($profileData['last_name'] ?? ''));

        if (($profileData['type'] ?? null) === 'person' && $firstName !== '') {
            $baseUserName = $firstName;

            if ($lastName !== '') {
                $baseUserName .= mb_substr($lastName, 0, 1);
            }

            return strtolower(str_replace(' ', '', $this->normalizeString($baseUserName) ?? 'user'));
        }

        $fullName = trim((string) ($profileData['full_name'] ?? $profileData['company_name'] ?? 'user'));

        return strtolower(str_replace(' ', '', $this->normalizeString($fullName) ?? 'user'));
    }

    public function createUser(array $userData, array $profileData): User
    {
        $profile = (new ProfileService())->firstOrCreateProfile($profileData);

        if (empty($userData['name'])) {
            $userData['name'] = $this->getUsername([
                'type' => $profile->type,
                'first_name' => $profile->first_name,
                'last_name' => $profile->last_name,
                'company_name' => $profile->company_name,
                'full_name' => $profile->full_name,
            ]);
        
        }
        
        $userData['status'] = $userData['status'] ?? 'pending';

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
