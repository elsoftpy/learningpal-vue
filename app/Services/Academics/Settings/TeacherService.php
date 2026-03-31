<?php 

namespace App\Services\Academics\Settings;

use App\Models\Teacher;
use App\Models\User;
use App\Services\Settings\Users\ProfileService;
use Illuminate\Database\Eloquent\Builder;

class TeacherService
{

    public function createTeacher(array $teacherData, array $profileData, bool $canEditExistingProfile = false): Teacher
    {
        $profile = (new ProfileService())->resolveProfile($profileData, $canEditExistingProfile);

        $teacher = $profile->teacher()->create($teacherData);

        $teacher->courses()->sync($teacherData['courses'] ?? []);

        return $teacher;
    }

    public function updateTeacherProfile($teacher, array $profileData): void
    {
        $profile = $teacher->profile;

        (new ProfileService())->updateProfile($profile, $profileData);
    }

    public function applyTeacherCoursesFilter(User $user, Builder $query, string $relation): Builder
    {
        if (!$user->can('view all students')) {    
            $courses = $user->profile?->teacher?->courses->pluck('id')->toArray() ?? [];
                $query->whereHas($relation, function ($q) use ($courses) {
                    $q->whereIn('course_id', $courses);
            });
        }

        return $query;
    }

    public function assignedCoursesArray(Teacher $teacher): array
    {
        return $teacher->courses->pluck('id')->toArray();
    }

    public function teacherData(Teacher $teacher)
    {
        $profile = $teacher->profile;

        $courses = $teacher->courses;

        $coursesData = $courses->pluck('id');
        $coursesDisplayNames = (new CourseService())->getCoursesDisplayNames($courses);

        return [
            'id' => $teacher->id,
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
            'email_alt' => $profile->email_alt ?? null,
            'status' => $teacher->status,
            'display_status' => ucfirst(__($teacher->status)),
            'courses' => $coursesData,
            'display_courses' => $coursesDisplayNames,
        ];
    }
}
