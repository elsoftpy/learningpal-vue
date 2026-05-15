<?php

namespace App\Models;

use Database\Factories\LevelContentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LevelContent extends Model
{
    /** @use HasFactory<LevelContentFactory> */
    use HasFactory;

    protected $table = 'level_contents';

    protected $fillable = [
        'language_level_id',
        'content',
    ];

    public function languageLevel(): BelongsTo
    {
        return $this->belongsTo(LanguageLevel::class);
    }

    public function studyProgramWeekActivities(): HasMany
    {
        return $this->hasMany(StudyProgramWeekActivity::class);
    }
}
