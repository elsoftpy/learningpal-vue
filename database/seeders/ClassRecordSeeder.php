<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatusEnum;
use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassRecord;
use App\Models\ClassRecordDetail;
use App\Models\ClassRecordDetailStudents;
use App\Models\ClassScheduleDetail;
use App\Models\ClassRecordStudent;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClassRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eligibleScheduleDetails = ClassScheduleDetail::query()
            ->with([
                'classSchedule.course.teachers.profile.user',
                'classSchedule.course.students',
                'classSchedule.course.languageLevel.levelContents',
            ])
            ->whereNotIn('status', [
                ClassScheduleStatusEnum::PENDING->value,
                ClassScheduleStatusEnum::REPROGRAMED->value,
            ])
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->get();

        $adminUserId = User::query()->where('email', 'admin@example.com')->value('id');

        foreach ($eligibleScheduleDetails as $index => $scheduleDetail) {
            $course = $scheduleDetail->classSchedule?->course;
            $teacher = $course?->teachers?->first();

            if (!$course || !$teacher) {
                continue;
            }

            $createdByUserId = $teacher->profile?->user?->id ?? $adminUserId;

            if (!$createdByUserId) {
                continue;
            }

            $attendance = match ($index % 3) {
                0 => AttendanceStatusEnum::PRESENT->value,
                1 => AttendanceStatusEnum::LATE->value,
                default => AttendanceStatusEnum::ABSENT->value,
            };

            $classRecord = ClassRecord::query()->updateOrCreate(
                ['class_schedule_detail_id' => $scheduleDetail->id],
                [
                    'course_id' => $course->id,
                    'teacher_id' => $teacher->id,
                    'user_id' => $createdByUserId,
                    'date' => $scheduleDetail->session_date,
                    'start_time' => $scheduleDetail->start_time,
                    'end_time' => $scheduleDetail->end_time,
                    'duration_minutes' => $scheduleDetail->estimated_duration_minutes,
                    'attendance' => $attendance,
                    'comments' => sprintf('Lesson record generated from %s', $scheduleDetail->classSchedule?->name ?? 'schedule'),
                    'mode' => $index % 2 === 0 ? 'online' : 'in-person',
                ]
            );

            $courseStudents = $course->students;
            $studentIds = $courseStudents->pluck('id')->all();

            foreach ($courseStudents as $student) {
                $studentStatus = match (($student->id + $index) % 3) {
                    0 => 1,
                    1 => 2,
                    default => 0,
                };

                ClassRecordStudent::query()->updateOrCreate(
                    [
                        'class_record_id' => $classRecord->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'status' => $studentStatus,
                        'status_date' => $studentStatus === 0 ? null : $scheduleDetail->end_time,
                    ]
                );
            }

            ClassRecordStudent::query()
                ->where('class_record_id', $classRecord->id)
                ->whereNotIn('student_id', $studentIds)
                ->delete();

            $levelContents = $course->languageLevel?->levelContents?->values() ?? collect();
            $contentCount = max($levelContents->count(), 1);

            $detailRows = [
                [
                    'content_id' => $levelContents->get($index % $contentCount)?->id,
                    'free_content' => null,
                    'activity' => 'Warm-up and vocabulary check',
                    'links' => null,
                ],
                [
                    'content_id' => $levelContents->get(($index + 1) % $contentCount)?->id,
                    'free_content' => 'Short pair-work production related to the lesson topic.',
                    'activity' => 'Guided speaking and wrap-up',
                    'links' => 'https://example.com/class-materials',
                ],
            ];

            foreach ($detailRows as $detailData) {
                $recordDetail = ClassRecordDetail::query()->updateOrCreate(
                    [
                        'class_record_id' => $classRecord->id,
                        'activity' => $detailData['activity'],
                    ],
                    [
                        'content_id' => $detailData['content_id'],
                        'free_content' => $detailData['free_content'],
                        'links' => $detailData['links'],
                    ]
                );

                foreach ($courseStudents as $student) {
                    $completed = (($student->id + $index) % 3) === 0 ? 1 : 0;

                    ClassRecordDetailStudents::query()->updateOrCreate(
                        [
                            'class_record_detail_id' => $recordDetail->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'completed' => $completed,
                            'completed_at' => $completed ? $scheduleDetail->end_time : null,
                        ]
                    );
                }

                ClassRecordDetailStudents::query()
                    ->where('class_record_detail_id', $recordDetail->id)
                    ->whereNotIn('student_id', $studentIds)
                    ->delete();
            }

            ClassRecordDetail::query()
                ->where('class_record_id', $classRecord->id)
                ->whereNotIn('activity', collect($detailRows)->pluck('activity')->all())
                ->delete();
        }

        ClassRecord::query()
            ->whereNotIn('class_schedule_detail_id', $eligibleScheduleDetails->pluck('id')->all())
            ->delete();
    }
}
