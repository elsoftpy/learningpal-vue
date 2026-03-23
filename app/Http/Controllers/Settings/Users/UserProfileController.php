<?php

namespace App\Http\Controllers\Settings\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserProfileRequest;
use App\Models\Profile;
use App\Models\User;
use App\Services\Settings\Users\ProfileService;
use App\Services\Settings\Users\UserService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Traits\SortResolverTrait;
use App\Services\Traits\UserProfileTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserProfileController extends Controller
{
    use FilterResolverTrait, SortResolverTrait, UserProfileTrait;
    
    public function index(Request $request)
    {
        $page = (int) $request->page;
        $perPage = (int) $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);
        [$sortField, $sortOrder] = $this->resolveSort($request, ['id', 'full_name', 'status'], 'id');

        $usersQuery = User::query()
            ->with('profile');

        if ($search) {
            $usersQuery->where(function ($query) use ($search) {
                $query->whereHas('profile', function ($q) use ($search) {
                    $q->where('full_name', 'like', '%' . $search . '%')
                        ->orWhere('company_name', 'like', '%' . $search . '%');
                })
                ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if (array_key_exists('full_name', $filters)) {
            $nameFilter = trim((string) $filters['full_name']);
            if ($nameFilter !== '') {
                $usersQuery->where(function ($query) use ($nameFilter) {
                    $query->whereHas('profile', function ($q) use ($nameFilter) {
                            $q->where('full_name', 'like', '%' . $nameFilter . '%');
                        });
                });
            }
        }

        if (array_key_exists('birth_date', $filters)) {
            $birthDateFilter = trim((string) $filters['birth_date']);
            if ($birthDateFilter !== '') {
                $usersQuery->whereHas('profile', function ($q) use ($birthDateFilter) {
                    $q->whereDate('birth_date', $birthDateFilter);
                });
            }
        }

        if ($sortField === 'full_name') {
            $usersQuery->orderBy(
                Profile::query()
                    ->select('full_name')
                    ->whereColumn('profiles.id', 'users.profile_id')
                    ->limit(1),
                $sortOrder
            );
        } else {
            $usersQuery->orderBy($sortField, $sortOrder);
        }

        $paginated = $usersQuery->paginate($perPage, ['*'], 'page', $page);
        
        $users = $paginated->getCollection()->map(function (User $user) {

            return $this->userData($user);
        });

        return ResponseService::success(
            data: [
                'users' => $users,
                'total' => $paginated->total(),
            ]
        );
    }

    public function store(UserProfileRequest $request, UserService $userService)
    {
        $profileData = $request->except(['name', 'password']);
        $userData = $request->only(['name', 'email', 'password', 'roles', 'status']);

        if (array_key_exists('roles', $userData) && ! $request->user()?->can('change roles')) {
            throw new AuthorizationException(__('You do not have permission to change roles.'));
        }

        $user = null;

        $user = DB::transaction(function () use ($profileData, $userData, $userService) {
            $user = $userService->createUser($userData, $profileData);
            
            return $user;
        });

        return ResponseService::success(
            message: __('User saved successfully.'),
            data: [
                'user' => $this->userData($user),
            ]
        );
    }

    public function userDataResponse(User $user)
    {
        return ResponseService::success(
            data: [
                'user' => $this->userData($user),
            ]
        );
    }

    public function update(UserProfileRequest $request, User $user, UserService $userService)
    {
        $isEditingOwnProfile = $user->id === Auth::id();

        if (!$isEditingOwnProfile && $request->user()->cannot('edit users')) {
            return ResponseService::unauthorized(
                message: __('You do not have permission to edit this user.')
            );
        }

        $profileData = $request->except(['name', 'password']);
        $userData = $request->only(['name', 'email', 'password', 'roles', 'status']);

        if (array_key_exists('roles', $userData) && ! $request->user()?->can('change roles')) {
            $requestedRoles = collect((array) $userData['roles'])->sort()->values();
            $currentRoles = $user->getRoleNames()->sort()->values();

            if ($requestedRoles->toArray() !== $currentRoles->toArray()) {
                throw new AuthorizationException(__('You do not have permission to change roles.'));
            }

            unset($userData['roles']);
        }
        
        DB::transaction(function () use ($user, $profileData, $userData, $userService) {
        
            $userService->updateUserProfile($user, $profileData);
            $userService->updateUserData($user, $userData);
        });
        
        return ResponseService::success(
            message: __('User profile updated successfully.'),
            data: [
                'user' => $this->userData($user),
            ]
        );
    }

    public function destroy(User $user)
    {
        DB::transaction(function () use ($user) {
            $profile = $user->profile;
            $profile->clearMediaCollection('avatar');
            $profile->clearMediaCollection('payment_receipt');
            $user->delete();
            $profile->delete();
        });

        return ResponseService::success(
            message: __('User deleted successfully.')
        );
    }

    public function fetchByIdNumber(Request $request, ProfileService $profileService, string $idNumber)
    {
        $profile = Profile::query()
            ->where('personal_id', $idNumber)
            ->orWhere('ruc', $idNumber)
            ->first();

        if (! $profile) {
            return null;
        }

        return ResponseService::success(
            data: [
                'profile' => $profileService->profileData($profile),
            ]
        );
    }

}
