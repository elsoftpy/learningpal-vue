<?php

namespace App\Services\Academics\Lessons;

use App\Enums\AttendanceStatusEnum;
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
        unset($data['details']);

        $classRecord = ClassRecord::create($data);

        $courseStudents = $this->loadCourseStudents($data['course_id'] ?? null);

        $classRecord->students()->createMany(
            $courseStudents->map(fn ($s) => ['student_id' => $s->id, 'status' => 0])->all()
        );

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
        unset($data['details']);

        $classRecord->update($data);

        $courseStudents = $this->loadCourseStudents($data['course_id'] ?? $classRecord->course_id);

        $existingStudentIds = $classRecord->students()->pluck('student_id')->all();
        $newStudents = $courseStudents->filter(fn ($s) => ! in_array($s->id, $existingStudentIds));
        if ($newStudents->isNotEmpty()) {
            $classRecord->students()->createMany(
                $newStudents->map(fn ($s) => ['student_id' => $s->id, 'status' => 0])->all()
            );
        }

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
            'attendance' => number_format((float) $classRecord->attendance, 1, '.', ''),
            'attendance_label' => AttendanceStatusEnum::label((string) $classRecord->attendance),
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
}