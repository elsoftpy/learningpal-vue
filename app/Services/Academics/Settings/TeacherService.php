<?php 

namespace App\Services\Academics\Settings;

use App\Enums\ProfileTypeEnum;
use App\Models\Course;
use App\Models\Profile;
use App\Models\Teacher;
use App\Services\Traits\UserProfileTrait;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeacherService
{
    use UserProfileTrait;

    public function createTeacher(array $teacherData, array $profileData): Teacher
    {
        $fullName = $this->getFullName(
            type: ProfileTypeEnum::PERSON->value, // user cannot be a company
            firstName: $profileData['first_name'] ?? null,
            lastName: $profileData['last_name'] ?? null,
            companyName: null,
        );
        
        $profileData['full_name'] = $fullName;

        $profile = Profile::create($profileData);
        $teacher = $profile->teacher()->create($teacherData);

        $teacher->courses()->sync($teacherData['courses'] ?? []);

        return $teacher;
    }

    public function updateTeacherProfile($teacher, array $profileData): void
    {
        $profile = $teacher->profile;

        $fullName = $this->getFullName(
            type: ProfileTypeEnum::PERSON->value, // user cannot be a company
            firstName: $profileData['first_name'] ?? $teacher->profile->first_name,
            lastName: $profileData['last_name'] ?? $teacher->profile->last_name,
            companyName: null,
        );
        $profileData['full_name'] = $fullName;

        $profile->update($profileData);
    }

    public function teacherData(Teacher $teacher)
    {
        $profile = $teacher->profile;

        $courses = $teacher->courses;

        $coursesData = $courses->pluck('id');
        $coursesDisplayNames = $this->getCoursesDisplayNames($courses);

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
            'birth_date' => $profile->birth_date 
                ? Carbon::parse($profile->birth_date)
                    ->format(match(app()->getLocale()) {
                        'es', 'pt' => 'd/m/Y',
                        'en' => 'm-d-Y',
                        default => 'Y-m-d',
                    }) 
                : null,
            'full_name' => $profile->full_name ?? null,
            'email' => $profile->email,
            'status' => $teacher->status,
            'display_status' => ucfirst(__($teacher->status)),
            'courses' => $coursesData,
            'display_courses' => $coursesDisplayNames,
        ];
    }

    protected function getCoursesDisplayNames(Collection $courses): array
    {
        return $courses->map(function($course) {
            return (new CourseService())->getCourseDisplayName($course);
        })->toArray();
    }
}