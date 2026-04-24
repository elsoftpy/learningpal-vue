<?php

namespace App\Models;

use Database\Factories\ClassScheduleDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClassScheduleDetail extends Model
{
    /** @use HasFactory<ClassScheduleDetailFactory> */
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
            'rescheduled_date' => 'date',
            'rescheduled_start_time' => 'datetime',
            'rescheduled_end_time' => 'datetime',
        ];
    }

    public function classSchedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class);
    }

    public function classRecord(): HasOne
    {
        return $this->hasOne(ClassRecord::class, 'class_schedule_detail_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ClassScheduleDetailStatusHistory::class);
    }
}
