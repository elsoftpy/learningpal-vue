<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassRecord extends Model
{
    /** @use HasFactory<\Database\Factories\ClassRecordFactory> */
    use HasFactory;

    protected $table = 'class_records';

    protected $fillable = [
        'course_id',
        'teacher_id',
        'class_schedule_detail_id',
        'user_id',
        'date',
        'start_time',
        'end_time',
        'duration_minutes',
        'attendance',
        'comments',
        'mode',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(ClassRecordDetail::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(ClassRecordStudent::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classScheduleDetail(): BelongsTo
    {
        return $this->belongsTo(ClassScheduleDetail::class);
    }
}
