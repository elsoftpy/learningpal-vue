<?php

return [
    'class_records' => [
        // Grace minutes to subtract from class record duration only when all students are absent.
        'absent_duration_grace_minutes' => (int) env('CLASS_RECORD_ABSENT_DURATION_GRACE_MINUTES', 10),
    ],
    'distance_activities' => [
        'video_completion_lock_minutes' => (int) env('DISTANCE_ACTIVITY_VIDEO_COMPLETION_LOCK_MINUTES', 1),
    ],
];
