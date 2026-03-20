<?php

return [
    'class_records' => [
        // When attendance is marked absent, duration must be <= (session duration - grace minutes).
        'absent_duration_grace_minutes' => (int) env('CLASS_RECORD_ABSENT_DURATION_GRACE_MINUTES', 10),
    ],
];
