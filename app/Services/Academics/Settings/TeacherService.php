<?php 

namespace App\Services\Academics\Settings;

use App\Models\Teacher;
use App\Services\Settings\Users\ProfileService;

class TeacherService
{

    public function createTeacher(array $teacherData, array $profileData): Teacher
    {
        $profileService = new ProfileService();
        $profile = $profileService->findByEmail($profileData['email']);
        if (!$profile) {

            $profile = $profileService->createProfile($profileData);
        }
        $teacher = $profile->teacher()->create($teacherData);

        $teacher->courses()->sync($teacherData['courses'] ?? []);

        return $teacher;
    }

    public function updateTeacherProfile($teacher, array $profileData): void
    {
        $profile = $teacher->profile;

        (new ProfileService())->updateProfile($profile, $profileData);
    }

    public function teacherData(Teacher $teacher)
    {
        $profile = $teacher->profile;

        $courses = $teacher->courses;

        $coursesData = $courses->pluck('id');
        $coursesDisplayNames = (new CourseService())->getCoursesDisplayNames($courses);

        return [
            'id' => $teacher->id,
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
            'status' => $teacher->status,
            'display_status' => ucfirst(__($teacher->status)),
            'courses' => $coursesData,
            'display_courses' => $coursesDisplayNames,
        ];
    }
}