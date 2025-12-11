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
    public function index(Request $request)
    {
        $page = (int) $request->page;
        $perPage = (int) $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);

        $users = User::query()
            ->with('profile');

        if ($search) {
            $users->where(function ($query) use ($search) {
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
                $users->where(function ($query) use ($nameFilter) {
                    $query->whereHas('profile', function ($q) use ($nameFilter) {
                            $q->where('full_name', 'like', '%' . $nameFilter . '%');
                        });
                });
            }
        }

        $paginated = $users->paginate($perPage, ['*'], 'page', $page);
        
        $users = $paginated->getCollection()->map(function (User $user) {

            return app(UserService::class)->userData($user);
        });

        return ResponseService::success(
            data: [
                'users' => $users,
                'total' => $paginated->total(),
            ]
        );
    }

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

    private function resolveFilters($filters): array
    {
        if (is_array($filters)) {
            return $filters;
        }

        if (is_string($filters)) {
            $decoded = json_decode($filters, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
