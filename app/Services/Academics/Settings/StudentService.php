<?php 

namespace App\Services\Academics\Settings;

use App\Models\Student;
use App\Services\Settings\Users\ProfileService;

class StudentService
{
    public function createStudent(array $studentData, array $profileData): Student
    {
        $profileService = new ProfileService();

        $profile = $profileService->findByEmail($profileData['email']);

        if (!$profile) {

            $profile = $profileService->createProfile($profileData);
        }

        $student = $profile->student()->create($studentData);

        $student->courses()->sync($studentData['courses'] ?? []);

        return $student;
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