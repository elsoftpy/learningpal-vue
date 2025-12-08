<?php

namespace App\Http\Controllers\Selectable;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $locale = app()->getLocale();

        $query = Role::query();
        
        
        if ($request?->search === '') {
            return response()->json([
                'data' => $this->getUnpaginatedRoles($query),
            ]);
        }
        
        $searchedRoles = $this->getTranslatedRoleName($request->search, $locale);
        $query->whereIn('name', $searchedRoles);

        if ($request->has('per_page')) {
            $roles = $query->paginate((int) $request->per_page)
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => __($role->name),
                    ];
                });

            return response()->json([
                'data' => $roles->items(),
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'total' => $roles->total(),
                
            ]);
        }
        return response()->json([
            'data' => $this->getUnpaginatedRoles($query),
        ]);
    }

    protected function getTranslatedRoleName($roleName, $locale)
    {
        $translated = collect([
            [
                'locale' => 'es', 
                'roles' => [
                    'admin' => 'Administrador',
                    'student' => 'Estudiante',
                    'teacher' => 'Profesor',
                ]
            ],
            [
                'locale' => 'pt', 
                'roles' => [
                    'admin' => 'admin',
                    'student' => 'Estudante',
                    'teacher' => 'Professor',
                ]
            ],
            [
                'locale' => 'en', 
                'roles' => [
                    'admin' => 'admin',
                    'student' => 'student',
                    'teacher' => 'teacher',
                ]
            ],
        ]);

        $localeRoles = collect($translated->where('locale', $locale)->first());
        $roles = collect(array_filter(
            $localeRoles->get('roles'),
            function($role) use($roleName) {
                return str_contains(strtolower($role), strtolower($roleName));
            }
        ));
        
        return $roles->keys()->all();
    }

    protected function getUnpaginatedRoles(Builder $query)
    {
        return  $query->limit(10)
                ->get()
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => ucfirst(__($role->name)),
                    ];
                });
    }
}
