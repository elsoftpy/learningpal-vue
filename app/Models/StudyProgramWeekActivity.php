<?php

namespace App\Models;

use App\Enums\StudyProgramActivityTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class StudyProgramWeekActivity extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\StudyProgramWeekActivityFactory> */
    use HasFactory, InteractsWithMedia;

    protected $table = 'study_program_week_activities';

    protected $fillable = [
        'study_program_week_id',
        'level_content_id',
        'free_content',
        'activity_name',
        'type',
        'links',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => StudyProgramActivityTypeEnum::class,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('study-materials')->singleFile();
    }

    public function studyProgramWeek(): BelongsTo
    {
        return $this->belongsTo(StudyProgramWeek::class);
    }

    public function levelContent(): BelongsTo
    {
        return $this->belongsTo(LevelContent::class);
    }

    public function distanceActivityDetails(): HasMany
    {
        return $this->hasMany(DistanceActivityDetail::class);
    }
}
