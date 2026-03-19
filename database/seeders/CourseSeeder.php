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

        $courseConfigs = [
            [
                'name' => 'English A1 Demo Course 1',
                'chat_room_link' => 'https://meet.example.com/english-a1-demo-course-1',
                'teacher_email' => 'teacher@example.com',
                'student_emails' => collect(range(1, 10))
                    ->map(fn ($index) => sprintf('student%02d@example.com', $index))
                    ->all(),
            ],
            [
                'name' => 'English A1 Demo Course 2',
                'chat_room_link' => 'https://meet.example.com/english-a1-demo-course-2',
                'teacher_email' => 'teacher2@example.com',
                'student_emails' => collect(range(11, 20))
                    ->map(fn ($index) => sprintf('student%02d@example.com', $index))
                    ->all(),
            ],
        ];

        foreach ($courseConfigs as $config) {
            $course = Course::query()->updateOrCreate(
                ['name' => $config['name']],
                [
                    'language_id' => $languageLevel->language_id,
                    'language_level_id' => $languageLevel->id,
                    'chat_room_link' => $config['chat_room_link'],
                    'status' => 'active',
                ]
            );

            $teacher = Teacher::query()
                ->whereHas('profile.user', fn ($query) => $query->where('email', $config['teacher_email']))
                ->first();

            $students = Student::query()
                ->whereHas('profile.user', fn ($query) => $query->whereIn('email', $config['student_emails']))
                ->get();

            if ($teacher) {
                $course->teachers()->sync([$teacher->id]);
            }

            if ($students->isNotEmpty()) {
                $course->students()->sync($students->pluck('id')->all());
            }

            $teacherUser = User::query()->where('email', $config['teacher_email'])->first();
            if ($teacherUser) {
                $course->distanceActivities()
                    ->with(['details.students', 'students'])
                    ->get()
                    ->each(function ($activity) {
                        $activity->details->each(function ($detail) {
                            $detail->students()->delete();
                            $detail->delete();
                        });

                        $activity->students()->delete();
                        $activity->delete();
                    });

                (new StudyProgramReplicationService())->replicateToCourse($course, $teacherUser);
            }

            foreach ($students as $student) {
                (new DistanceActivityEnrollmentService())->syncStudentEnrollments($student, [$course->id]);
            }
        }
    }
}
