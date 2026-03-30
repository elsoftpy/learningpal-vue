<?php

namespace App\Http\Controllers\Academics\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\Profile;
use App\Models\Student;
use App\Services\Academics\Settings\StudentService;
use App\Services\Academics\Settings\TeacherService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Traits\SortResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    use FilterResolverTrait, SortResolverTrait;

    public function index(Request $request)
    {
        $page = (int) $request->page;
        $perPage = (int) $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);
        [$sortField, $sortOrder] = $this->resolveSort($request, ['id', 'full_name', 'status'], 'full_name');

        $studentsQuery = Student::query()
            ->with('profile');

        $user = $request->user();
        $studentsQuery = (new TeacherService())->applyTeacherCoursesFilter(
            user: $user, 
            query: $studentsQuery,
            relation: 'courses'
        );

        if ($search) {
            $studentsQuery->where(function ($query) use ($search) {
                $query->whereHas('profile', function ($q) use ($search) {
                    $q->where('full_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            });
        }

        if ($filters) {
            // Apply filters to the query
        }

        if ($sortField === 'full_name') {
            $studentsQuery->orderBy(
                Profile::query()
                    ->select('full_name')
                    ->whereColumn('profiles.id', 'students.profile_id')
                    ->limit(1),
                $sortOrder
            );
        } else {
            $studentsQuery->orderBy($sortField, $sortOrder);
        }

        $paginated = $studentsQuery->paginate($perPage, ['*'], 'page', $page);

        $students = $paginated->getCollection()->map(function ($student) {
            return (new StudentService())->studentData($student);
        });

        return ResponseService::success(
            data: [
                'students' => $students,
                'total' => $paginated->total(),
            ]
        );
    }

    public function store(StudentRequest $request, StudentService $studentService)
    {
        $profileData = $request->except(['status', 'courses']);
        $studentData = $request->only(['status', 'courses']);
        $canEditExistingProfile = $request->user()?->can('edit profiles') || $request->user()?->can('edit students');

        $student = null;

        $student = DB::transaction(function () use ($profileData, $studentData, $studentService, $canEditExistingProfile) {
            $student = $studentService->createStudent($studentData, $profileData, $canEditExistingProfile);
            
            return $student;
        });

        return ResponseService::success(
            message: __('Student saved successfully.'),
            data: [
                'student' => $studentService->studentData($student),
            ]
        );
    }

    public function userData(Student $student, StudentService $studentService)
    {
        return ResponseService::success(
            data: [
                'student' => $studentService->studentData($student),
            ]
        );
    }

    public function update(StudentRequest $request, Student $student, StudentService $studentService)
    {
        $profileData = $request->except(['status', 'courses']);
        $studentData = $request->only(['status', 'courses']);
        
        DB::transaction(function () use ($student, $profileData, $studentData, $studentService) {
        
            $studentService->updateStudentProfile($student, $profileData);
            $student->update([
                'status' => $studentData['status'] ?? $student->status,
            ]);
            $studentService->syncCourses($student, $studentData['courses'] ?? []);
        });
        
        return ResponseService::success(
            message: __('Student profile updated successfully.'),
            data: [
                'student' => $studentService->studentData($student),
            ]
        );
    }

    public function destroy(Student $student)
    {
        DB::transaction(function () use ($student) {
            $profile = $student->profile;
            $profile->clearMediaCollection('avatar');
            $student->delete();
            $profile->delete();
        });

        return ResponseService::success(
            message: __('Student deleted successfully.')
        );
    }
}
