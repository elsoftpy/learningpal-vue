<?php

namespace App\Models;

use App\Enums\StudyProgramActivityTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistanceActivityDetail extends Model
{
    /** @use HasFactory<\Database\Factories\DistanceActivityDetailFactory> */
    use HasFactory;

    protected $table = 'distance_activity_details';

    protected $fillable = [
        'distance_activity_id',
        'study_program_week_activity_id',
        'content_id',
        'free_content',
        'activity',
        'type',
        'links',
        'file_path',
        'file_name',
    ];

    protected function casts(): array
    {
        return [
            'type' => StudyProgramActivityTypeEnum::class,
        ];
    }

    public function distanceActivity(): BelongsTo
    {
        return $this->belongsTo(DistanceActivity::class, 'distance_activity_id');
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(LevelContent::class, 'content_id');
    }

    public function studyProgramWeekActivity(): BelongsTo
    {
        return $this->belongsTo(StudyProgramWeekActivity::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(DistanceActivityDetailStudent::class, 'distance_activity_detail_id');
    }
}
