<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\LanguageLevel;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Academics\Settings\DistanceActivityEnrollmentService;
use App\Services\Academics\Settings\StudyProgramReplicationService;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languageLevel = LanguageLevel::query()
            ->whereHas('studyProgram.weeks.activities')
            ->where('level', 'A1')
            ->first();

        if (!$languageLevel) {
            $languageLevel = LanguageLevel::query()
                ->whereHas('studyProgram.weeks.activities')
                ->orderBy('id')
                ->first();
        }

        if (!$languageLevel) {
            return;
        }

        $course = Course::query()->updateOrCreate(
            ['name' => 'English A1 Demo Course'],
            [
                'language_id' => $languageLevel->language_id,
                'language_level_id' => $languageLevel->id,
                'chat_room_link' => 'https://meet.example.com/english-a1-demo-course',
                'status' => 'active',
            ]
        );

        $teacher = Teacher::query()
            ->whereHas('profile.user', fn ($query) => $query->where('email', 'teacher@example.com'))
            ->first();

        $students = Student::query()
            ->whereHas('profile.user', fn ($query) => $query->whereIn('email', [
                'student@example.com',
                'annualstudent@example.com',
            ]))
            ->get();

        if ($teacher) {
            $course->teachers()->sync([$teacher->id]);
        }

        if ($students->isNotEmpty()) {
            $course->students()->sync($students->pluck('id')->all());
        }

        $teacherUser = User::query()->where('email', 'teacher@example.com')->first();
        if ($teacherUser) {
            $course->distanceActivities()->delete();
            (new StudyProgramReplicationService())->replicateToCourse($course, $teacherUser);
        }

        foreach ($students as $student) {
            (new DistanceActivityEnrollmentService())->syncStudentEnrollments($student, [$course->id]);
        }
    }
}
