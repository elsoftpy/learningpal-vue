<?php

namespace App\Http\Controllers\Settings\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserProfileRequest;
use App\Models\User;
use App\Services\Settings\Users\UserService;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserProfileController extends Controller
{
    public function update(UserProfileRequest $request, User $user, UserService $userService)
    {
        $profileData = $request->except(['name', 'password']);
        $userData = $request->only(['name', 'email', 'password', 'roles', 'status']);
        
        DB::transaction(function () use ($user, $profileData, $userData, $userService) {
        
            $userService->updateUserProfile($user, $profileData);
            $userService->updateUserData($user, $userData);
        });
        
        return ResponseService::success(
            message: __('User profile updated successfully.'),
            data: [
                'user' => $userService->userData($user),
            ]
        );
    }
}
