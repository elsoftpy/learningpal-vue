<?php 

namespace App\Services\Academics\Settings;

use App\Models\Student;
use App\Services\Settings\Users\ProfileService;

class StudentService
{
    public function createStudent(array $studentData, array $profileData, bool $canEditExistingProfile = false): Student
    {
        $profile = (new ProfileService())->resolveProfile($profileData, $canEditExistingProfile);

        $student = $profile->student()->create($studentData);

        $this->syncCourses($student, $studentData['courses'] ?? []);

        return $student;
    }

    public function syncCourses(Student $student, array $courseIds): void
    {
        $changes = $student->courses()->sync($courseIds);

        $enrollmentService = new DistanceActivityEnrollmentService();

        $detachedCourseIds = array_map('intval', $changes['detached'] ?? []);

        if (! empty($detachedCourseIds)) {
            $enrollmentService->removeStudentEnrollments($student, $detachedCourseIds);
        }

        $attachedCourseIds = array_map('intval', $changes['attached'] ?? []);

        if (! empty($attachedCourseIds)) {
            $enrollmentService->syncStudentEnrollments($student, $attachedCourseIds);
        }
    }

    public function updateStudentProfile($student, array $profileData): void
    {
        $profile = $student->profile;

        (new ProfileService())->updateProfile($profile, $profileData);
    }

    public function studentData(Student $student)
    {
        $profile = $student->profile;

        $courses = $student->courses;   
        $coursesData = $courses->pluck('id');
        $coursesDisplayNames = (new CourseService())->getCoursesDisplayNames($courses);

        return [
            'id' => $student->id,
            'profile_id' => $profile->id ?? null,
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
            'birth_date' => $profile->birth_date?->format(match(app()->getLocale()) {
                        'es', 'pt' => 'd/m/Y',
                        'en' => 'm-d-Y',
                        default => 'Y-m-d',
                    }) ?? null,
            'full_name' => $profile->full_name ?? null,
            'email' => $profile->email,
            'status' => $student->status,
            'display_status' => ucfirst(__($student->status)),
            'courses' => $coursesData,
            'display_courses' => $coursesDisplayNames,
        ];
    }
}
