<?php

namespace App\Http\Controllers\Academics\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\Student;
use App\Services\Academics\Settings\StudentService;
use App\Services\Academics\Settings\TeacherService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    use FilterResolverTrait;

    public function index(Request $request)
    {
        $page = (int) $request->page;
        $perPage = (int) $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);

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
        $profileData = $request->except(['status']);
        $studentData = $request->only(['status', 'courses']);

        $student = null;

        $student = DB::transaction(function () use ($profileData, $studentData, $studentService) {
            $student = $studentService->createStudent($studentData, $profileData);
            
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
        $profileData = $request->except(['status']);
        $studentData = $request->only(['status']);
        $student->courses()->sync($request->courses ?? []);
        
        DB::transaction(function () use ($student, $profileData, $studentData, $studentService) {
        
            $studentService->updateStudentProfile($student, $profileData);
            $student->update($studentData);
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
