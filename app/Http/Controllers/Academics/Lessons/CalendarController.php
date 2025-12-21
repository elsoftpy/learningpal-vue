<?php

namespace App\Http\Controllers\Academics\Lessons;

use App\Enums\ClassScheduleStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\CalendarSessionRequest;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Services\Academics\Lessons\ClassScheduleDetailService;
use App\Services\Academics\Settings\CourseService;
use App\Services\Academics\Settings\TeacherService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function calendarSessions(CalendarSessionRequest $request)
    {
        $scheduledSessionsQuery = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereBetween('session_date', [
                $request->start_date->startOfDay(), 
                $request->end_date->endOfDay(),
            ])
            ->where('status', ClassScheduleStatusEnum::SCHEDULED->value); 

        $reprogramedSessionsQuery = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereBetween('rescheduled_date', [
                $request->start_date->startOfDay(), 
                $request->end_date->endOfDay(),
            ])
            ->where('status', ClassScheduleStatusEnum::REPROGRAMED->value);

        $user = $request->user();
        $scheduledSessionsQuery = (new TeacherService())->applyTeacherCoursesFilter(
            user: $user,
            query: $scheduledSessionsQuery,
            relation: 'classSchedule'
        );

        $reprogramedSessionsQuery = (new TeacherService())->applyTeacherCoursesFilter(
            user: $user,
            query: $reprogramedSessionsQuery,
            relation: 'classSchedule'
        );

        $sessionsQuery = $scheduledSessionsQuery->unionAll($reprogramedSessionsQuery);
        
        $sessions = $sessionsQuery->get()
            ->map(function (ClassScheduleDetail $detail) {
                return (new ClassScheduleDetailService())->sessionData($detail);
            });
        
        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    public function scheduledCalendarCourses(CalendarSessionRequest $request)
    {
        $scheduledSessions = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereBetween('session_date', [
                $request->start_date->startOfDay(), 
                $request->end_date->endOfDay(),
            ])
            ->where('status', ClassScheduleStatusEnum::SCHEDULED->value);

        $reprogramedSessions = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereBetween('rescheduled_date', [
                $request->start_date->startOfDay(), 
                $request->end_date->endOfDay(),
            ])
            ->where('status', ClassScheduleStatusEnum::REPROGRAMED->value);

        $coursesIds = $scheduledSessions->unionAll($reprogramedSessions)
           ->get()
           ->pluck('classSchedule.course.id')
           ->unique()
           ->toArray();

        $courses = Course::query()
            ->whereIn('id', $coursesIds)
            ->get(); 

        $caledarColors = $this->calendarColors($coursesIds);
        $courseService = new CourseService();
        $calendars = $courses->map(function ($course) use ($caledarColors, $courseService) {
            $displayName =  $courseService->getCourseDisplayName($course);

            return [
                $displayName => [
                    'course_id' => $course->id,
                    'lightColors' => $caledarColors[$course->id]['light'],
                    'darkColors' => $caledarColors[$course->id]['dark'],
                ]
            ];
        });

        return response()->json([
            'calendars' => $calendars,
        ]);
    }

    public function ongoingAndPendingSessions(Request $request)
    {
        $ongoingAndPendingSessions = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereIn('status', [
                ClassScheduleStatusEnum::ONGOING->value, 
                ClassScheduleStatusEnum::PENDING->value
            ])
            ->get()
            ->map(function (ClassScheduleDetail $detail) {
                return (new ClassScheduleDetailService())->sessionData($detail);
            });
        
        return response()->json([
            'ongoing_and_pending_sessions' => $ongoingAndPendingSessions,
        ]);
    }

    protected function calendarColors(array $courseIds)
    {
        $colors = [];
        $colorPalette = [
            [
                'light' => [
                    'main' => "#2563eb",
                    'container' => "#dbeafe",
                    'onContainer' => "#065f46",
                ],
                'dark' => [
                    'main' => "#3b82f6",
                    'container' => "#1e3a8a",
                    'onContainer' => "#dbeafe",    
                ],
            ],
            [
                'light' => [
                    'main' => "#10b981",
                    'container' => "#d1fae5",
                    'onContainer' => "#831843",
                ],
                'dark' => [
                    'main' => "#34d399",
                    'container' => "#065f46",
                    'onContainer' => "#d1fae5",    
                ],
            ],
            [
                'light' => [
                    'main' => "#f97316",
                    'container' => "#ffedd5",
                    'onContainer' => "#7c2d12",
                ],
                'dark' => [
                    'main' => "#fb923c",
                    'container' => "#7c2d12",
                    'onContainer' => "#ffedd5",
                ],
            ],
            [
                'light' => [
                    'main' => "#6366f1",
                    'container' => "#e0e7ff",
                    'onContainer' => "#312e81",
                ],
                'dark' => [
                    'main' => "#818cf8",
                    'container' => "#312e81",
                    'onContainer' => "#e0e7ff",
                ],
            ],
            [
                'light' => [
                    'main' => "#0d9488",
                    'container' => "#ccfbf1",
                    'onContainer' => "#134e4a",
                ],
                'dark' => [
                    'main' => "#2dd4bf",
                    'container' => "#134e4a",
                    'onContainer' => "#ccfbf1",
                ],
            ],
            [
                'light' => [
                    'main' => "#d946ef",
                    'container' => "#fae8ff",
                    'onContainer' => "#701a75",
                ],
                'dark' => [
                    'main' => "#f472b6",
                    'container' => "#701a75",
                    'onContainer' => "#fae8ff",
                ],
            ],
            [
                'light' => [
                    'main' => "#f43f5e",
                    'container' => "#ffe4e6",
                    'onContainer' => "#881337",
                ],
                'dark' => [
                    'main' => "#fb7185",
                    'container' => "#881337",
                    'onContainer' => "#ffe4e6",
                ],
            ],
            [
                'light' => [
                    'main' => "#06b6d4",
                    'container' => "#cffafe",
                    'onContainer' => "#083344",
                ],
                'dark' => [
                    'main' => "#22d3ee",
                    'container' => "#083344",
                    'onContainer' => "#cffafe",
                ],
            ],
            [
                'light' => [
                    'main' => "#84cc16",
                    'container' => "#ecfccb",
                    'onContainer' => "#365314",
                ],
                'dark' => [
                    'main' => "#a3e635",
                    'container' => "#365314",
                    'onContainer' => "#ecfccb",
                ],
            ],
            [
                'light' => [
                    'main' => "#8b5cf6",
                    'container' => "#ede9fe",
                    'onContainer' => "#4c1d95",
                ],
                'dark' => [
                    'main' => "#a78bfa",
                    'container' => "#4c1d95",
                    'onContainer' => "#ede9fe",
                ],
            ],
            [
                'light' => [
                    'main' => "#fbbf24",
                    'container' => "#fef3c7",
                    'onContainer' => "#78350f",
                ],
                'dark' => [
                    'main' => "#fcd34d",
                    'container' => "#78350f",
                    'onContainer' => "#fef3c7",
                ],
            ],
            [
                'light' => [
                    'main' => "#0ea5e9",
                    'container' => "#e0f2fe",
                    'onContainer' => "#0c4a6e",
                ],
                'dark' => [
                    'main' => "#38bdf8",
                    'container' => "#0c4a6e",
                    'onContainer' => "#e0f2fe",
                ],
            ],
            [
                'light' => [
                    'main' => "#475569",
                    'container' => "#e2e8f0",
                    'onContainer' => "#1e293b",
                ],
                'dark' => [
                    'main' => "#94a3b8",
                    'container' => "#1e293b",
                    'onContainer' => "#e2e8f0",
                ],
            ],
            [
                'light' => [
                    'main' => "#15803d",
                    'container' => "#dcfce7",
                    'onContainer' => "#14532d",
                ],
                'dark' => [
                    'main' => "#22c55e",
                    'container' => "#14532d",
                    'onContainer' => "#dcfce7",
                ],
            ],
            [
                'light' => [
                    'main' => "#fb7185",
                    'container' => "#ffe4e6",
                    'onContainer' => "#7f1d1d",
                ],
                'dark' => [
                    'main' => "#fda4af",
                    'container' => "#7f1d1d",
                    'onContainer' => "#ffe4e6",
                ],
            ],
            [
                'light' => [
                    'main' => "#a21caf",
                    'container' => "#f5d0fe",
                    'onContainer' => "#581c87",
                ],
                'dark' => [
                    'main' => "#c026d3",
                    'container' => "#581c87",
                    'onContainer' => "#f5d0fe",
                ],
            ],
            [
                'light' => [
                    'main' => "#be185d",
                    'container' => "#fce7f3",
                    'onContainer' => "#831843",
                ],
                'dark' => [
                    'main' => "#ec4899",
                    'container' => "#831843",
                    'onContainer' => "#fce7f3",
                ],
            ],
            [
                'light' => [
                    'main' => "#1e3a8a",
                    'container' => "#e0e7ff",
                    'onContainer' => "#0f172a",
                ],
                'dark' => [
                    'main' => "#3b82f6",
                    'container' => "#111827",
                    'onContainer' => "#e0e7ff",
                ],
            ],
            [
                'light' => [
                    'main' => "#b45309",
                    'container' => "#ffedd5",
                    'onContainer' => "#431407",
                ],
                'dark' => [
                    'main' => "#f97316",
                    'container' => "#431407",
                    'onContainer' => "#ffedd5",
                ],
            ],
            [
                'light' => [
                    'main' => "#4ade80",
                    'container' => "#dcfce7",
                    'onContainer' => "#166534",
                ],
                'dark' => [
                    'main' => "#86efac",
                    'container' => "#166534",
                    'onContainer' => "#dcfce7",
                ],
            ],
        ];

        foreach ($courseIds as $index => $courseId) {
            $paletteIndex = $index % count($colorPalette);
            $colors[$courseId] = $colorPalette[$paletteIndex];
        }

        return $colors;
    }
}