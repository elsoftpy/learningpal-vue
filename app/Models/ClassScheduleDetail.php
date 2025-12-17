<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassScheduleDetail extends Model
{
    /** @use HasFactory<\Database\Factories\ClassScheduleDetailFactory> */
    use HasFactory;

    protected $table = 'class_schedule_details';

    protected $fillable = [
        'class_schedule_id',
        'session_date',
        'start_time',
        'end_time',
        'estimated_duration_minutes',
        'rescheduled_date',
        'rescheduled_start_time',
        'rescheduled_end_time',
        'rescheduled_estimated_duration_minutes',
        'reschedule_count',
        'topic',
        'activity',
        'order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function classSchedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class);
    }
}
