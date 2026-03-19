<?php

namespace App\Models;

use App\Enums\StudyProgramStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyProgramWeek extends Model
{
    /** @use HasFactory<\Database\Factories\StudyProgramWeekFactory> */
    use HasFactory;

    protected $table = 'study_program_weeks';

    protected $fillable = [
        'study_program_id',
        'week_number',
        'title',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => StudyProgramStatusEnum::class,
        ];
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(StudyProgramWeekActivity::class);
    }

    public function distanceActivities(): HasMany
    {
        return $this->hasMany(DistanceActivity::class);
    }
}
