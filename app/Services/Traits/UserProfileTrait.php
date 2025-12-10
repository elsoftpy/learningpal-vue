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
        return [
            'id' => $user->id,
            'name' => $user->name,
            'type' => $user->profile->type ?? null,
            'personal_id' => $user->profile->personal_id ?? null,
            'first_name' => $user->profile->first_name ?? null,
            'last_name' => $user->profile->last_name ?? null,
            'company_name' => $user->profile->company_name ?? null,
            'ruc' => $user->profile->ruc ?? null,
            'phone' => $user->profile->phone ?? null,
            'address' => $user->profile->address ?? null,
            'gender' => $user->profile->gender ?? null,
            'birth_date' => $user->profile->birth_date ?? null,
            'full_name' => $user->profile->full_name ?? null,
            'email' => $user->email,
            'status' => $user->status,
            'roles' => $roles,
            'avatar_url' => $user->profile->getFirstMediaUrl('avatar') ?: null,
        ];
    }
}