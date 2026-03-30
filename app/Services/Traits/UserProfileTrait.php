<?php

namespace App\Services\Traits;

use App\Models\User;
use App\Services\Utilities\DateTimeService;

trait UserProfileTrait
{
    protected function mediaUrlWithVersion($media): ?string
    {
        if (! $media) {
            return null;
        }

        return $media->getUrl().'?v='.$media->updated_at?->timestamp;
    }

    public function userData(User $user): array
    {
        $roles = $user->getRoleNames()->toArray();
        $translatedRoles = array_map(fn($role) => ucfirst(__($role)), $roles);
        $profile = $user->profile;
        $avatar = $profile?->getFirstMedia('avatar');
        $paymentReceipt = $profile?->getFirstMedia('payment_receipt');

        $permissions = $user->getAllPermissions()->pluck('name'); 

        return [
            'id' => $user->id,
            'profile_id' => $profile->id ?? null,
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
            'birth_date' => DateTimeService::formatDate($profile->birth_date),
            'full_name' => $profile->full_name ?? null,
            'email' => $user->email,
            'status' => $user->status,
            'display_status' => ucfirst(__($user->status)),
            'roles' => $roles,
            'display_roles' => $translatedRoles,
            'avatar_url' => $this->mediaUrlWithVersion($avatar),
            'payment_receipt' => $this->mediaUrlWithVersion($paymentReceipt),
            'permissions' => $permissions,
        ];
    }
}
