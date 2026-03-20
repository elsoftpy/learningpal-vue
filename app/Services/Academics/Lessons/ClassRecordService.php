<?php

namespace App\Services\Academics\Lessons;

use App\Enums\AttendanceStatusEnum;
use App\Models\ClassRecordAttendance;
use App\Models\ClassRecord;
use App\Models\ClassRecordDetail;
use App\Models\Course;
use App\Services\Academics\Settings\CourseService;
use App\Services\Utilities\DateTimeService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ClassRecordService
{
    public function createClassRecord(array $data, array $files = [], array $recordMediaFiles = []): ClassRecord
    {
        $details = $data['details'] ?? [];
        $studentAttendances = $data['student_attendances'] ?? [];
        unset($data['details']);
        unset($data['student_attendances']);

        $classRecord = ClassRecord::create($data);

        $courseStudents = $this->loadCourseStudents($data['course_id'] ?? null);

        $classRecord->students()->createMany(
            $courseStudents->map(fn ($s) => ['student_id' => $s->id, 'status' => 0])->all()
        );

        $this->syncAttendances($classRecord, $courseStudents, is_array($studentAttendances) ? $studentAttendances : []);

        if (is_array($details)) {
            foreach ($details as $index => $detailData) {
                $detailModel = $classRecord->details()->create([
                    'content_id' => $detailData['content_id'] ?? null,
                    'free_content' => $detailData['free_content'] ?? null,
                    'activity' => $detailData['activity'] ?? null,
                    'links' => $detailData['links'] ?? null,
                ]);

                if (isset($files[$index])) {
                    $detailModel->addMedia($files[$index])->toMediaCollection('attachment');
                }

                $this->syncDetailStudents($detailModel, $courseStudents);
            }
        }

        $this->syncStudentProductionMedia($classRecord, $recordMediaFiles);

        return $classRecord;
    }

    public function updateClassRecord(ClassRecord $classRecord, array $data, array $files = [], array $recordMediaFiles = []): ClassRecord
    {
        $details = $data['details'] ?? [];
        $studentAttendances = $data['student_attendances'] ?? [];
        unset($data['details']);
        unset($data['student_attendances']);

        $classRecord->update($data);

        $courseStudents = $this->loadCourseStudents($data['course_id'] ?? $classRecord->course_id);

        $existingStudentIds = $classRecord->students()->pluck('student_id')->all();
        $newStudents = $courseStudents->filter(fn ($s) => ! in_array($s->id, $existingStudentIds));
        if ($newStudents->isNotEmpty()) {
            $classRecord->students()->createMany(
                $newStudents->map(fn ($s) => ['student_id' => $s->id, 'status' => 0])->all()
            );
        }

        $this->syncAttendances($classRecord, $courseStudents, is_array($studentAttendances) ? $studentAttendances : []);

        if (is_array($details)) {
            $incomingIds = collect($details)
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values();

            $detailsToDelete = $classRecord->details()
                ->when($incomingIds->isNotEmpty(), function ($query) use ($incomingIds) {
                    $query->whereNotIn('id', $incomingIds->all());
                }, function ($query) {
                    $query->whereNotNull('id');
                })
                ->get();

            $detailsToDelete->each(function (ClassRecordDetail $detail) {
                $detail->detailStudents()->delete();
                $detail->clearMediaCollection('attachment');
                $detail->delete();
            });

            foreach ($details as $index => $detailData) {
                $payload = [
                    'content_id' => $detailData['content_id'] ?? null,
                    'free_content' => $detailData['free_content'] ?? null,
                    'activity' => $detailData['activity'] ?? null,
                    'links' => $detailData['links'] ?? null,
                ];

                if (!empty($detailData['id'])) {
                    $existingDetail = $classRecord->details()->where('id', (int) $detailData['id'])->first();
                    if ($existingDetail) {
                        $existingDetail->update($payload);
                        if (isset($files[$index])) {
                            $existingDetail->clearMediaCollection('attachment');
                            $existingDetail->addMedia($files[$index])->toMediaCollection('attachment');
                        }
                        $this->syncDetailStudents($existingDetail, $courseStudents);
                    }
                    continue;
                }

                $newDetail = $classRecord->details()->create($payload);
                if (isset($files[$index])) {
                    $newDetail->addMedia($files[$index])->toMediaCollection('attachment');
                }
                $this->syncDetailStudents($newDetail, $courseStudents);
            }
        }

        $this->syncStudentProductionMedia($classRecord, $recordMediaFiles);

        return $classRecord;
    }

    public function classRecordData(ClassRecord $classRecord)
    {
        $course = $classRecord->course;
        $courseName = (new CourseService())->getCourseDisplayName($course);
        $classScheduleDetail = $classRecord->classScheduleDetail;
        $attendances = $classRecord->relationLoaded('attendances')
            ? $classRecord->attendances
            : $classRecord->attendances()->with('student.profile')->get();

        $normalizedAttendanceValues = $attendances
            ->map(fn (ClassRecordAttendance $attendance) => number_format((float) $attendance->attendance, 1, '.', ''));

        $presentCount = $normalizedAttendanceValues->filter(fn (string $value) => $value === AttendanceStatusEnum::PRESENT->value)->count();
        $lateCount = $normalizedAttendanceValues->filter(fn (string $value) => $value === AttendanceStatusEnum::LATE->value)->count();
        $absentCount = $normalizedAttendanceValues->filter(fn (string $value) => $value === AttendanceStatusEnum::ABSENT->value)->count();
        $averageAttendance = $normalizedAttendanceValues->isNotEmpty()
            ? number_format($normalizedAttendanceValues->sum(fn (string $value) => (float) $value) / $normalizedAttendanceValues->count(), 1, '.', '')
            : null;

        $attendanceSummary = sprintf(
            '%s: %d | %s: %d | %s: %d',
            __('Present'),
            $presentCount,
            __('Late'),
            $lateCount,
            __('Absent'),
            $absentCount,
        );

        $detailLabel = null;
        if ($classScheduleDetail?->classSchedule) {
            $detailLabel = $classScheduleDetail->classSchedule->name.' - '.$classScheduleDetail->session_date?->format('d/m/Y');
        }

        return [
            'id' => $classRecord->id,
            'date' => DateTimeService::formatDate($classRecord->date),
            'start_time' => $classRecord->start_time instanceof Carbon ? $classRecord->start_time->format('H:i') : null,
            'end_time' => $classRecord->end_time instanceof Carbon ? $classRecord->end_time->format('H:i') : null,
            'duration_minutes' => $classRecord->duration_minutes,
            'attendance' => $averageAttendance,
            'attendance_label' => $attendanceSummary,
            'attendance_summary' => $attendanceSummary,
            'course_id' => $course->id,
            'course' => $courseName,
            'teacher_id' => $classRecord->teacher_id,
            'teacher' => $classRecord->teacher?->profile?->full_name,
            'user_id' => $classRecord->user_id,
            'user' => $classRecord->user?->name,
            'class_schedule_detail_id' => $classRecord->class_schedule_detail_id,
            'class_schedule_detail_label' => $detailLabel,
            'class_schedule_detail' => $classScheduleDetail ? (new ClassScheduleDetailService())->classScheduleDetailData($classScheduleDetail) : null,
            'comments' => $classRecord->comments,
            'mode' => $classRecord->mode,
            'student_attendances' => $attendances
                ->map(fn (ClassRecordAttendance $attendance) => [
                    'student_id' => $attendance->student_id,
                    'student_name' => $attendance->student?->profile?->full_name,
                    'attendance' => number_format((float) $attendance->attendance, 1, '.', ''),
                    'attendance_label' => AttendanceStatusEnum::label((string) $attendance->attendance),
                ])
                ->values(),
            'student_production_media' => $classRecord->getMedia('student-production')
                ->map(fn ($media) => [
                    'id' => $media->id,
                    'name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'mime_type' => $media->mime_type,
                    'media_type' => $media->getCustomProperty('media_type'),
                ])
                ->values(),
            'details' => $classRecord->details()
                ->with('media')
                ->orderBy('id')
                ->get()
                ->map(function ($detail) {
                    return (new ClassRecordDetailService())->classRecordDetailData($detail);
                }),
        ];
    }

    public function saveStudentProductionMedia(ClassRecord $classRecord, array $recordMediaFiles = []): ClassRecord
    {
        $this->syncStudentProductionMedia($classRecord, $recordMediaFiles);

        return $classRecord->refresh();
    }

    private function loadCourseStudents(?int $courseId): Collection
    {
        if (! $courseId) {
            return collect();
        }

        return Course::find($courseId)
            ?->students()
            ->get() ?? collect();
    }

    private function syncDetailStudents(ClassRecordDetail $detail, Collection $students): void
    {
        $existingStudentIds = $detail->detailStudents()->pluck('student_id')->all();

        $newRows = $students
            ->filter(fn ($student) => ! in_array($student->id, $existingStudentIds))
            ->map(fn ($student) => ['student_id' => $student->id, 'completed' => 0])
            ->values()
            ->all();

        if (! empty($newRows)) {
            $detail->detailStudents()->createMany($newRows);
        }
    }

    private function syncStudentProductionMedia(ClassRecord $classRecord, array $recordMediaFiles): void
    {
        $mediaInputs = [
            'student_production_file' => 'file',
            'student_production_audio' => 'audio',
        ];

        foreach ($mediaInputs as $inputKey => $mediaType) {
            $uploadedFile = $recordMediaFiles[$inputKey] ?? null;
            if (! $uploadedFile) {
                continue;
            }

            $classRecord->getMedia('student-production')
                ->filter(fn ($media) => $media->getCustomProperty('media_type') === $mediaType)
                ->each(fn ($media) => $media->delete());

            $classRecord
                ->addMedia($uploadedFile)
                ->withCustomProperties(['media_type' => $mediaType])
                ->toMediaCollection('student-production');
        }
    }

    private function syncAttendances(ClassRecord $classRecord, Collection $students, array $studentAttendances): void
    {
        $courseStudentIds = $students->pluck('id')->map(fn ($id) => (int) $id)->all();

        $incomingByStudent = collect($studentAttendances)
            ->filter(fn ($item) => is_array($item) && isset($item['student_id']))
            ->mapWithKeys(function ($item) {
                $studentId = (int) $item['student_id'];
                $attendanceValue = number_format((float) ($item['attendance'] ?? AttendanceStatusEnum::ABSENT->value), 1, '.', '');

                return [$studentId => $attendanceValue];
            });

        foreach ($courseStudentIds as $studentId) {
            $attendanceValue = $incomingByStudent->get($studentId, AttendanceStatusEnum::ABSENT->value);

            $classRecord->attendances()->updateOrCreate(
                ['student_id' => $studentId],
                ['attendance' => $attendanceValue]
            );
        }

        if (!empty($courseStudentIds)) {
            $classRecord->attendances()
                ->whereNotIn('student_id', $courseStudentIds)
                ->delete();
        }
    }
}