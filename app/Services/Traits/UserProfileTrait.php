<?php

namespace App\Services\Traits;

use App\Models\User;
use Transliterator;

trait UserProfileTrait
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

    public function userData(User $user): array
    {
        $roles = $user->getRoleNames()->toArray();
        $translatedRoles = array_map(fn($role) => ucfirst(__($role)), $roles);
        $profile = $user->profile;

        $permissions = $user->getAllPermissions()->pluck('name'); 

        return [
            'id' => $user->id,
            'name' => $user->name,
            'type' => $profile->type ?? null,
            'personal_id' => $profile->personal_id ?? null,
            'first_name' => $profile->first_name ?? null,
            'last_name' => $profile->last_name ?? null,
            'full_name' => $profile->full_name ?? null,
            'company_name' => $profile->company_name ?? null,
            'ruc' => $profile->ruc ?? null,
            'phone' => $profile->phone ?? null,
            'address' => $profile->address ?? null,
            'gender' => $profile->gender ?? null,
            'birth_date' => $profile->birth_date?->format(match(app()->getLocale()) {
                        'es', 'pt' => 'd/m/Y',
                        'en' => 'm-d-Y',
                        default => 'Y-m-d',
                    }) ?? null,
            'full_name' => $profile->full_name ?? null,
            'email' => $user->email,
            'status' => $user->status,
            'display_status' => ucfirst(__($user->status)),
            'roles' => $roles,
            'display_roles' => $translatedRoles,
            'avatar_url' => $profile->getFirstMediaUrl('avatar') ?: null,
            'payment_receipt' => $profile->getFirstMediaUrl('payment_receipt') ?: null,
            'permissions' => $permissions,
        ];
    }
}