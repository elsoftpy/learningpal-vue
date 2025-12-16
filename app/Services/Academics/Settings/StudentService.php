<?php 

namespace App\Services\Academics\Settings;

use App\Enums\ProfileTypeEnum;
use App\Models\Profile;
use App\Models\Student;
use App\Services\Traits\UserProfileTrait;
use Illuminate\Support\Collection;

class StudentService
{
    use UserProfileTrait;

    public function createStudent(array $studentData, array $profileData): Student
    {
        $fullName = $this->getFullName(
            type: ProfileTypeEnum::PERSON->value, // user cannot be a company
            firstName: $profileData['first_name'] ?? null,
            lastName: $profileData['last_name'] ?? null,
            companyName: null,
        );
        
        $profileData['full_name'] = $fullName;

        $profile = Profile::create($profileData);
        $student = $profile->student()->create($studentData);

        $student->courses()->sync($studentData['courses'] ?? []);

        return $student;
    }

    public function updateStudentProfile($student, array $profileData): void
    {
        $profile = $student->profile;

        $fullName = $this->getFullName(
            type: ProfileTypeEnum::PERSON->value, // user cannot be a company
            firstName: $profileData['first_name'] ?? $student->profile->first_name,
            lastName: $profileData['last_name'] ?? $student->profile->last_name,
            companyName: null,
        );
        $profileData['full_name'] = $fullName;

        $profile->update($profileData);
    }

    public function studentData(Student $student)
    {
        $profile = $student->profile;

        $courses = $student->courses;   
        $coursesData = $courses->pluck('id');
        $coursesDisplayNames = $this->getCoursesDisplayNames($courses);

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

    protected function getCoursesDisplayNames(Collection $courses): array
    {
        return $courses->map(function($course) {
            return (new CourseService())->getCourseDisplayName($course);
        })->toArray();
    }
}