<?php

namespace App\Http\Controllers\Academics\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRequest;
use App\Models\Teacher;
use App\Services\Academics\Settings\TeacherService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    use FilterResolverTrait;

    public function index(Request $request)
    {
        $page = (int) $request->page;
        $perPage = (int) $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);

        $teachersQuery = Teacher::query()
            ->with('profile');

        if ($search) {
            $teachersQuery->where(function ($query) use ($search) {
                $query->whereHas('profile', function ($q) use ($search) {
                    $q->where('full_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            });
        }

        if ($filters) {
            // Apply filters to the query
        }

        $paginated = $teachersQuery->paginate($perPage, ['*'], 'page', $page);

        $teachers = $paginated->getCollection()->map(function ($teacher) {
            return (new TeacherService())->teacherData($teacher);
        });

        return ResponseService::success(
            data: [
                'teachers' => $teachers,
                'total ' => $paginated->total(),
            ]
        );
    }

    public function store(TeacherRequest $request, TeacherService $teacherService)
    {
        $profileData = $request->except(['status']);
        $teacherData = $request->only(['status', 'courses']);

        $teacher = null;

        $teacher = DB::transaction(function () use ($profileData, $teacherData, $teacherService) {
            $teacher = $teacherService->createTeacher($teacherData, $profileData);
            
            return $teacher;
        });

        return ResponseService::success(
            message: __('Teacher saved successfully.'),
            data: [
                'teacher' => $teacherService->teacherData($teacher),
            ]
        );
    }

    public function userData(Teacher $teacher, TeacherService $teacherService)
    {
        return ResponseService::success(
            data: [
                'teacher' => $teacherService->teacherData($teacher),
            ]
        );
    }

    public function update(TeacherRequest $request, Teacher $teacher, TeacherService $teacherService)
    {
        $profileData = $request->except(['status']);
        $teacherData = $request->only(['status']);
        $teacher->courses()->sync($request->courses ?? []);
        
        DB::transaction(function () use ($teacher, $profileData, $teacherData, $teacherService) {
        
            $teacherService->updateTeacherProfile($teacher, $profileData);
            $teacher->update($teacherData);
        });
        
        return ResponseService::success(
            message: __('Teacher profile updated successfully.'),
            data: [
                'teacher' => $teacherService->teacherData($teacher),
            ]
        );
    }

    public function destroy(Teacher $teacher)
    {
        DB::transaction(function () use ($teacher) {
            $profile = $teacher->profile;
            $profile->clearMediaCollection('avatar');
            $teacher->delete();
            $profile->delete();
        });

        return ResponseService::success(
            message: __('Teacher deleted successfully.')
        );
    }
}
