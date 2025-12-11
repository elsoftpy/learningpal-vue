<?php

namespace App\Services\Traits;

use App\Models\User;

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
        return str_replace(' ', '', trim($fullName));
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
            'birth_date' => $profile->birth_date ?? null,
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