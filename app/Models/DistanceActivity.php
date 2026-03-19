<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistanceActivity extends Model
{
    /** @use HasFactory<\Database\Factories\DistanceActivityFactory> */
    use HasFactory;

    protected $table = 'distance_activities';

    protected $fillable = [
        'course_id',
        'study_program_week_id',
        'teacher_id',
        'user_id',
        'title',
        'comments',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DistanceActivityDetail::class, 'distance_activity_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(DistanceActivityStudent::class, 'distance_activity_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function studyProgramWeek(): BelongsTo
    {
        return $this->belongsTo(StudyProgramWeek::class);
    }
}
