<?php

namespace App\Services\Settings\Users;

use App\Models\Profile;
use App\Services\Utilities\DateTimeService;

class ProfileService
{
    public function getFullName(string $type, ?string $firstName, ?string $lastName, ?string $companyName): string
    {
        if ($type === 'person') {
            return trim($firstName . ' ' . $lastName);
        } 
        return trim($companyName);
    }

    public function createProfile(array $profileData): Profile
    {
        $fullName = $this->getFullName(
            type: $profileData['type'],
            firstName: $profileData['first_name'] ?? null,
            lastName: $profileData['last_name'] ?? null,
            companyName: $profileData['company_name'] ?? null
        );
        
        $profileData['full_name'] = $fullName;

        return Profile::create($profileData);
    }

    public function firstOrCreateProfile(array $profileData): Profile
    {
        $profile = $this->findByEmail($profileData['email']);
        if (!$profile) {
            $profile = $this->createProfile($profileData);
        }
        return $profile;
    }

    public function updateProfile(Profile $profile, array $profileData): void
    {
        $fullName = $this->getFullName(
            type: $profileData['type'] ?? $profile->type,
            firstName: $profileData['first_name'] ?? $profile->first_name,
            lastName: $profileData['last_name'] ?? $profile->last_name,
            companyName: $profileData['company_name'] ?? $profile->company_name
        );

        $profileData['full_name'] = $fullName;

        $profile->update($profileData);
    }

    public function findByEmail(string $email): Profile|null
    {
        return Profile::where('email', $email)->first();
    }

    public function resolveProfile(array $profileData, bool $canEditExistingProfile = false): Profile
    {
        $profileId = $profileData['profile_id'] ?? null;

        if ($profileId) {
            $profile = Profile::findOrFail($profileId);

            if ($canEditExistingProfile) {
                $this->updateProfile($profile, $profileData);
            }

            return $profile;
        }

        return $this->firstOrCreateProfile($profileData);
    }

    public function selectionLabel(Profile $profile): string
    {
        $displayName = trim((string) ($profile->full_name ?: $profile->company_name ?: __('Unnamed profile')));
        $identifier = trim((string) ($profile->personal_id ?: $profile->ruc ?: ''));

        return $identifier !== ''
            ? "{$displayName} - {$identifier}"
            : $displayName;
    }

    public function profileData(Profile $profile): array
    {
        return [
            'id' => $profile->id,
            'type' => $profile->type,
            'personal_id' => $profile->personal_id,
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'full_name' => $profile->full_name,
            'company_name' => $profile->company_name,
            'ruc' => $profile->ruc,
            'email' => $profile->email,
            'phone' => $profile->phone,
            'address' => $profile->address,
            'gender' => $profile->gender,
            'birth_date' => DateTimeService::formatDate($profile->birth_date),
            'avatar_url' => $profile->getFirstMediaUrl('avatar') ?: null,
            'payment_receipt' => $profile->getFirstMediaUrl('payment_receipt') ?: null,
        ];
    }
}
