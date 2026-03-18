<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ClassRecord extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ClassRecordFactory> */
    use HasFactory;
    use InteractsWithMedia;

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

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'attendance' => 'decimal:2',
        ];
    }

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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('student-production');
    }
}
