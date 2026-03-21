<?php

return [
    'class_records' => [
        // Grace minutes to subtract from class record duration only when all students are absent.
        'absent_duration_grace_minutes' => (int) env('CLASS_RECORD_ABSENT_DURATION_GRACE_MINUTES', 10),
    ],
];
