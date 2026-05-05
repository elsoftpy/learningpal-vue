<?php

namespace App\Http\Controllers\Academics\Lessons;

use App\Enums\AttendanceStatusEnum;
use App\Enums\ClassScheduleStatusEnum;
use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClassRecordRequest;
use App\Models\ClassRecord;
use App\Models\ClassRecordDetail;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\LevelContent;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Academics\Lessons\ClassRecordDetailService;
use App\Services\Academics\Lessons\ClassRecordService;
use App\Services\Authorization\CourseVisibilityService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Traits\SortResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ClassRecordController extends Controller
{
    use FilterResolverTrait, SortResolverTrait;

    public function index(Request $request)
    {
        $visibility = new CourseVisibilityService;
        $page = $request->page;
        $perPage = $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);
        [$sortField, $sortOrder] = $this->resolveSort(
            $request,
            ['id', 'teacher', 'user', 'course', 'date', 'start_time', 'end_time'],
            'date',
            'desc'
        );

        $query = ClassRecord::query()
            ->with(['teacher.profile', 'course', 'user', 'classScheduleDetail.classSchedule', 'details.media', 'media', 'attendances.student.profile']);

        $visibility->applyCourseScope($query, $request->user());

        if ($search) {
            if (str_contains($search, '/')) {
                $searchArray = explode('/', $search);
                if (count($searchArray) === 3) {
                    $search = $searchArray[2].'-'.$searchArray[1].'-'.$searchArray[0];
                } elseif (count($searchArray) === 2) {
                    $search = $searchArray[1].'-'.$searchArray[0];
                }
            }

            $query->where(function (Builder $query) use ($search) {
                $query->where('date', $search)
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('course', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('teacher', function ($q) use ($search) {
                        $q->whereHas('profile', function ($teacherProfileQuery) use ($search) {
                            $teacherProfileQuery->where('full_name', 'like', '%'.$search.'%');
                        });
                    });
            });
        }

        // Apply filters if any
        if ($filters) {
            foreach ($filters as $field => $value) {
                $query->where($field, $value);
            }
        }

        // Pagination
        switch ($sortField) {
            case 'teacher':
                $query->orderBy(
                    Teacher::query()
                        ->select('profiles.full_name')
                        ->join('profiles', 'profiles.id', '=', 'teachers.profile_id')
                        ->whereColumn('teachers.id', 'class_records.teacher_id')
                        ->limit(1),
                    $sortOrder
                );
                break;
            case 'user':
                $query->orderBy(
                    User::query()
                        ->select('name')
                        ->whereColumn('users.id', 'class_records.user_id')
                        ->limit(1),
                    $sortOrder
                );
                break;
            case 'course':
                $query->orderBy(
                    Course::query()
                        ->select('name')
                        ->whereColumn('courses.id', 'class_records.course_id')
                        ->limit(1),
                    $sortOrder
                );
                break;
            default:
                $query->orderBy($sortField, $sortOrder);
                break;
        }

        $paginated = $query
            ->paginate($perPage, ['*'], 'page', $page);

        $classRecords = $paginated->getCollection()->map(function (ClassRecord $classRecord) {
            return (new ClassRecordService)->classRecordData($classRecord);
        });

        return ResponseService::success(
            message: __('Class records retrieved successfully.'),
            data: [
                'class_records' => $classRecords,
                'total' => $paginated->total(),
            ]
        );
    }

    public function formData(Request $request)
    {
        $lockedClassScheduleDetailId = $request->integer('class_schedule_detail_id') ?: null;

        return ResponseService::success(
            message: __('Class record form data loaded successfully.'),
            data: $this->buildFormData($request, null, $lockedClassScheduleDetailId)
        );
    }

    public function classRecordData(Request $request, ClassRecord $classRecord)
    {
        (new CourseVisibilityService)->authorizeCourseId($request->user(), $classRecord->course_id);

        $classRecord->loadMissing(['teacher.profile', 'course', 'user', 'classScheduleDetail.classSchedule', 'details.content', 'media', 'attendances.student.profile']);

        $lockedClassScheduleDetailId = $request->integer('class_schedule_detail_id') ?: null;
        $data = $this->buildFormData($request, $classRecord, $lockedClassScheduleDetailId);
        $data['class_record'] = (new ClassRecordService)->classRecordData($classRecord);

        return ResponseService::success(
            message: __('Class record retrieved successfully.'),
            data: $data
        );
    }

    public function classScheduleDetailContext(ClassScheduleDetail $detail)
    {
        $courseId = $detail->classSchedule?->course_id;
        (new CourseVisibilityService)->authorizeCourseId(request()->user(), $courseId);

        return ResponseService::success(
            message: __('Class schedule detail context loaded successfully.'),
            data: [
                'course_id' => $courseId,
                'level_contents' => $this->levelContentsOptions($courseId),
                'students' => $this->courseStudentsOptions($courseId),
            ]
        );
    }

    public function store(ClassRecordRequest $request)
    {
        $classScheduleDetail = ClassScheduleDetail::query()
            ->with('classSchedule')
            ->findOrFail($request->class_schedule_detail_id);

        (new CourseVisibilityService)->authorizeCourseId($request->user(), $classScheduleDetail->classSchedule?->course_id);

        $this->validateTeacherPermission($request, (int) $request->teacher_id);

        $payload = array_merge($request->validated(), [
            'course_id' => $classScheduleDetail->classSchedule->course_id,
            'user_id' => $request->user()->id,
            'mode' => 'online',
        ]);

        DB::transaction(function () use ($payload, $classScheduleDetail, $request) {
            (new ClassRecordService)->createClassRecord(
                $payload,
                $request->file('detail_files', []),
                [
                    'student_production_file' => $request->file('student_production_file'),
                    'student_production_audio' => $request->file('student_production_audio'),
                ]
            );
            $classScheduleDetail->update([
                'status' => ClassScheduleStatusEnum::COMPLETED->value,
            ]);
        });

        return ResponseService::success(
            message: __('Class record created successfully.'),
        );
    }

    public function update(ClassRecordRequest $request, ClassRecord $classRecord)
    {
        (new CourseVisibilityService)->authorizeCourseId($request->user(), $classRecord->course_id);

        $classScheduleDetail = ClassScheduleDetail::query()
            ->with('classSchedule')
            ->findOrFail($request->class_schedule_detail_id);

        (new CourseVisibilityService)->authorizeCourseId($request->user(), $classScheduleDetail->classSchedule?->course_id);

        $this->validateTeacherPermission($request, (int) $request->teacher_id);

        $payload = array_merge($request->validated(), [
            'course_id' => $classScheduleDetail->classSchedule->course_id,
            'user_id' => $request->user()->id,
            'mode' => 'online',
        ]);

        DB::transaction(function () use ($classRecord, $payload, $request) {
            (new ClassRecordService)->updateClassRecord(
                $classRecord,
                $payload,
                $request->file('detail_files', []),
                [
                    'student_production_file' => $request->file('student_production_file'),
                    'student_production_audio' => $request->file('student_production_audio'),
                ]
            );
        });

        return ResponseService::success(
            message: __('Class record updated successfully.'),
        );
    }

    public function destroy(ClassRecord $record)
    {
        (new CourseVisibilityService)->authorizeCourseId(request()->user(), $record->course_id);

        foreach ($record->details as $detail) {
            $detail->detailStudents()->delete();
        }
        $record->details()->delete();
        $record->students()->delete();
        $record->delete();

        return ResponseService::success(
            message: __('Class record deleted successfully.')
        );
    }

    public function saveStudentProduction(Request $request, ClassRecord $classRecord)
    {
        (new CourseVisibilityService)->authorizeCourseId($request->user(), $classRecord->course_id);

        if (! $request->user()?->can('upload own class record production')) {
            return ResponseService::error(
                message: __('You are not authorized to upload class record production.'),
                statusCode: 403
            );
        }

        $roleNames = $request->user()?->roles?->pluck('name') ?? collect();
        $isStudentRole = $roleNames->contains('student') || $roleNames->contains('annual_student');

        if (! $isStudentRole) {
            return ResponseService::error(
                message: __('Only students can upload class record production.'),
                statusCode: 403
            );
        }

        $validated = $request->validate(
            [
                'student_production_file' => [
                    'nullable',
                    'file',
                    'max:10240',
                    'mimes:pdf,doc,docx,xls,xlsx,txt,jpeg,jpg,png,webp',
                ],
                'student_production_audio' => [
                    'nullable',
                    'file',
                    'max:10240',
                    'mimetypes:audio/mpeg,audio/mp3,audio/webm,audio/webm;codecs=opus,audio/ogg,video/webm',
                ],
            ],
            [
                'student_production_audio.mimetypes' => 'El campo student production audio debe ser un archivo de tipo: audio/mpeg, audio/mp3, audio/webm, audio/ogg.',
            ]
        );

        $hasAnyFile = ! empty($validated['student_production_file']) || ! empty($validated['student_production_audio']);
        if (! $hasAnyFile) {
            return ResponseService::success(
                message: __('No student production files were provided.'),
                data: [
                    'class_record' => (new ClassRecordService)->classRecordData($classRecord),
                ]
            );
        }

        DB::transaction(function () use ($classRecord, $request) {
            (new ClassRecordService)->saveStudentProductionMedia(
                $classRecord,
                [
                    'student_production_file' => $request->file('student_production_file'),
                    'student_production_audio' => $request->file('student_production_audio'),
                ]
            );
        });

        return ResponseService::success(
            message: __('Student production saved successfully.'),
            data: [
                'class_record' => (new ClassRecordService)->classRecordData($classRecord),
            ]
        );
    }

    public function detailFormData(ClassRecordDetail $detail)
    {
        $detail->loadMissing(['classRecord.teacher.profile', 'classRecord.course', 'media', 'content']);

        $courseId = $detail->classRecord?->course_id;
        (new CourseVisibilityService)->authorizeCourseId(request()->user(), $courseId);

        return ResponseService::success(
            message: __('Class record detail form data loaded successfully.'),
            data: [
                'detail' => (new ClassRecordDetailService)->classRecordDetailData($detail),
                'level_contents' => $this->levelContentsOptions($courseId),
                'record_info' => [
                    'teacher' => $detail->classRecord?->teacher?->profile?->full_name ?? '',
                    'course' => $detail->classRecord?->course?->name ?? '',
                    'date' => $detail->classRecord?->date?->format('d/m/Y') ?? '',
                ],
            ]
        );
    }

    public function updateDetail(Request $request, ClassRecordDetail $detail)
    {
        (new CourseVisibilityService)->authorizeCourseId($request->user(), $detail->classRecord?->course_id);

        $validated = $request->validate([
            'activity' => 'required|string|max:1000',
            'content_id' => 'nullable|integer|exists:level_contents,id',
            'free_content' => 'nullable|string|max:1000',
            'links' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|max:20480',
        ]);

        $detail->update([
            'activity' => $validated['activity'],
            'content_id' => $validated['content_id'] ?? null,
            'free_content' => $validated['free_content'] ?? null,
            'links' => $validated['links'] ?? null,
        ]);

        if ($request->hasFile('attachment')) {
            $detail->clearMediaCollection('attachment');
            $detail->addMediaFromRequest('attachment')->toMediaCollection('attachment');
        }

        return ResponseService::success(
            message: __('Class record detail updated successfully.')
        );
    }

    public function destroyDetail(ClassRecordDetail $detail)
    {
        (new CourseVisibilityService)->authorizeCourseId(request()->user(), $detail->classRecord?->course_id);

        $detail->clearMediaCollection('attachment');
        $detail->detailStudents()->delete();
        $detail->delete();

        return ResponseService::success(
            message: __('Class record detail deleted successfully.')
        );
    }

    private function buildFormData(Request $request, ?ClassRecord $classRecord = null, ?int $lockedClassScheduleDetailId = null): array
    {
        $selectedClassScheduleDetailId = $lockedClassScheduleDetailId ?? $classRecord?->class_schedule_detail_id;
        $preferredTeacherId = $classRecord?->teacher_id ?? $this->resolveTeacherIdFromClassScheduleDetail($selectedClassScheduleDetailId);
        $selectedTeacherId = $preferredTeacherId;
        $selectedCourseId = $this->resolveCourseIdFromClassScheduleDetail($selectedClassScheduleDetailId) ?? $classRecord?->course_id;

        return [
            'class_schedule_details' => $this->classScheduleDetailsOptions($selectedClassScheduleDetailId),
            'teachers' => $this->teacherOptions($request, $selectedTeacherId),
            'level_contents' => $this->levelContentsOptions($selectedCourseId),
            'students' => $this->courseStudentsOptions($selectedCourseId),
            'attendances' => collect(AttendanceStatusEnum::cases())
                ->map(fn (AttendanceStatusEnum $status) => [
                    'value' => $status->value,
                    'label' => AttendanceStatusEnum::label($status->value),
                ])
                ->values(),
            'class_record_attendances' => $classRecord
                ? $classRecord->attendances()
                    ->with('student.profile')
                    ->get()
                    ->map(fn ($attendance) => [
                        'student_id' => $attendance->student_id,
                        'student_name' => $attendance->student?->profile?->full_name,
                        'attendance' => number_format((float) $attendance->attendance, 1, '.', ''),
                        'attendance_label' => AttendanceStatusEnum::label((string) $attendance->attendance),
                    ])
                    ->values()
                : collect([]),
            'locked_class_schedule_detail_id' => $lockedClassScheduleDetailId,
            'preferred_teacher_id' => $preferredTeacherId,
        ];
    }

    private function courseStudentsOptions(?int $courseId = null): Collection
    {
        if (! $courseId) {
            return collect([]);
        }

        if (! (new CourseVisibilityService)->canAccessCourseId(request()->user(), $courseId)) {
            return collect([]);
        }

        return Course::query()
            ->find($courseId)
            ?->students()
            ->with('profile')
            ->orderBy('id')
            ->get()
            ->map(fn ($student) => [
                'id' => $student->id,
                'name' => $student->profile?->full_name,
            ])
            ->values() ?? collect([]);
    }

    private function teacherOptions(Request $request, ?int $selectedTeacherId = null): Collection
    {
        $teachersQuery = Teacher::query()
            ->with('profile')
            ->where(function ($query) use ($selectedTeacherId) {
                $query->where('status', StatusEnum::ACTIVE->value);

                if ($selectedTeacherId) {
                    $query->orWhere('id', $selectedTeacherId);
                }
            });

        if ($request->user()->cannot('list other teachers')) {
            $teacherId = $request->user()->profile?->teacher?->id;

            if (! $teacherId) {
                throw new HttpException(403, __('Sorry, you\'re not a teacher, you can\'t create class records'));
            }

            $teachersQuery->where('id', $teacherId);
        }

        return $teachersQuery
            ->get()
            ->map(function (Teacher $teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->profile?->full_name,
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    private function levelContentsOptions(?int $courseId = null): Collection
    {
        if (! $courseId) {
            return collect([]);
        }

        if (! (new CourseVisibilityService)->canAccessCourseId(request()->user(), $courseId)) {
            return collect([]);
        }

        $languageLevelId = Course::query()->find($courseId)?->language_level_id;

        if (! $languageLevelId) {
            return collect([]);
        }

        return LevelContent::query()
            ->where('language_level_id', $languageLevelId)
            ->orderBy('id')
            ->get()
            ->map(fn (LevelContent $content) => [
                'id' => $content->id,
                'name' => $content->content,
            ])
            ->values();
    }

    private function resolveCourseIdFromClassScheduleDetail(?int $classScheduleDetailId = null): ?int
    {
        if (! $classScheduleDetailId) {
            return null;
        }

        $courseId = ClassScheduleDetail::query()
            ->with('classSchedule')
            ->find($classScheduleDetailId)?->classSchedule?->course_id;

        return (new CourseVisibilityService)->canAccessCourseId(request()->user(), $courseId) ? $courseId : null;
    }

    private function resolveTeacherIdFromClassScheduleDetail(?int $classScheduleDetailId = null): ?int
    {
        if (! $classScheduleDetailId) {
            return null;
        }

        $detail = ClassScheduleDetail::query()
            ->with(['classSchedule.course.teachers'])
            ->find($classScheduleDetailId);

        if (! (new CourseVisibilityService)->canAccessCourseId(request()->user(), $detail?->classSchedule?->course_id)) {
            return null;
        }

        return $detail?->classSchedule?->course?->teachers?->sortBy('id')->first()?->id;
    }

    private function classScheduleDetailsOptions(?int $selectedClassScheduleDetailId = null): Collection
    {
        $statuses = [
            ClassScheduleStatusEnum::SCHEDULED->value,
            ClassScheduleStatusEnum::PENDING->value,
            ClassScheduleStatusEnum::ONGOING->value,
            ClassScheduleStatusEnum::REPROGRAMED->value,
            ClassScheduleStatusEnum::CANCELED->value,
        ];

        return ClassScheduleDetail::query()
            ->with('classSchedule')
            ->where(function ($query) use ($statuses, $selectedClassScheduleDetailId) {
                $query->whereIn('status', $statuses);

                if ($selectedClassScheduleDetailId) {
                    $query->orWhere('id', $selectedClassScheduleDetailId);
                }
            })
            ->whereHas('classSchedule', function (Builder $query) {
                (new CourseVisibilityService)->applyCourseScope($query, request()->user());
            })
            ->orderByDesc('session_date')
            ->orderByDesc('start_time')
            ->get()
            ->map(function (ClassScheduleDetail $detail) {
                return [
                    'id' => $detail->id,
                    'name' => ($detail->classSchedule?->name ?? __('Schedule')).' - '.$detail->session_date?->format('d/m/Y'),
                    'session_date' => $detail->session_date?->toDateString(),
                    'start_time' => $detail->start_time,
                ];
            })
            ->values();
    }

    private function validateTeacherPermission(Request $request, int $teacherId): void
    {
        if ($request->user()->can('list other teachers')) {
            return;
        }

        $profileTeacher = $request->user()->profile?->teacher;

        if (! $profileTeacher) {
            throw new HttpException(403, __('Sorry, you\'re not a teacher, you can\'t create class records'));
        }

        if ((int) $profileTeacher->id !== $teacherId) {
            throw new HttpException(403, __('You do not have permission to select this teacher.'));
        }
    }
}
