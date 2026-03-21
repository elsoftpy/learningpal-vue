<?php

namespace App\Services\Academics\Settings;

use App\Models\Course;
use App\Models\DistanceActivityDetailStudent;
use App\Models\DistanceActivityStudent;
use App\Models\Student;

class DistanceActivityEnrollmentService
{
    public function removeStudentEnrollments(Student $student, array $courseIds): void
    {
        if (empty($courseIds)) {
            return;
        }

        $distanceActivityIds = Course::query()
            ->whereIn('id', $courseIds)
            ->with('distanceActivities')
            ->get()
            ->pluck('distanceActivities.*.id')
            ->flatten()
            ->all();

        if (empty($distanceActivityIds)) {
            return;
        }

        DistanceActivityStudent::query()
            ->where('student_id', $student->id)
            ->whereIn('distance_activity_id', $distanceActivityIds)
            ->delete();

        $detailIds = DistanceActivityDetailStudent::query()
            ->where('student_id', $student->id)
            ->whereHas('distanceActivityDetail', function ($query) use ($distanceActivityIds) {
                $query->whereIn('distance_activity_id', $distanceActivityIds);
            })
            ->pluck('id');

        DistanceActivityDetailStudent::query()
            ->whereIn('id', $detailIds)
            ->delete();
    }

    public function syncStudentEnrollments(Student $student, array $courseIds): void
    {
        if (empty($courseIds)) {
            return;
        }

        $courses = Course::query()
            ->with('distanceActivities.details')
            ->whereIn('id', $courseIds)
            ->get();

        foreach ($courses as $course) {
            foreach ($course->distanceActivities as $distanceActivity) {
                DistanceActivityStudent::query()->firstOrCreate(
                    [
                        'distance_activity_id' => $distanceActivity->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'completed' => false,
                        'completed_at' => null,
                    ]
                );

                foreach ($distanceActivity->details as $detail) {
                    DistanceActivityDetailStudent::query()->firstOrCreate(
                        [
                            'distance_activity_detail_id' => $detail->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'completed' => false,
                            'completed_at' => null,
                        ]
                    );
                }
            }
        }
    }
}
