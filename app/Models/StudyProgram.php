<?php

namespace App\Models;

use App\Enums\StudyProgramStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyProgram extends Model
{
    /** @use HasFactory<\Database\Factories\StudyProgramFactory> */
    use HasFactory;

    protected $table = 'study_programs';

    protected $fillable = [
        'language_level_id',
        'title',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => StudyProgramStatusEnum::class,
        ];
    }

    public function languageLevel(): BelongsTo
    {
        return $this->belongsTo(LanguageLevel::class);
    }

    public function weeks(): HasMany
    {
        return $this->hasMany(StudyProgramWeek::class);
    }
}
