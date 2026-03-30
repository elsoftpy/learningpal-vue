<?php

namespace App\Http\Controllers\Selectable;

use App\Enums\ProfileTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Services\Settings\Users\ProfileService;
use Illuminate\Http\Request;

class ProfileListController extends Controller
{
    public function __invoke(Request $request)
    {
        $search = trim((string) ($request->input('search') ?? $request->input('params.search', '')));
        $type = $request->input('type') ?? $request->input('params.type');

        $query = Profile::query();

        if (is_string($type) && in_array($type, ProfileTypeEnum::values(), true)) {
            $query->where('type', $type);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('full_name', 'like', '%'.$search.'%')
                    ->orWhere('company_name', 'like', '%'.$search.'%')
                    ->orWhere('personal_id', 'like', '%'.$search.'%')
                    ->orWhere('ruc', 'like', '%'.$search.'%');
            });
        }

        $profileService = new ProfileService();

        return $query
            ->orderBy('full_name')
            ->limit(10)
            ->get()
            ->map(function (Profile $profile) use ($profileService) {
                return [
                    'id' => $profile->id,
                    'label' => $profileService->selectionLabel($profile),
                    'profile' => $profileService->profileData($profile),
                ];
            });
    }
}
