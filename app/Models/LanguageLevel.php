<?php

namespace App\Models;

use Database\Factories\LanguageLevelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LanguageLevel extends Model
{
    /** @use HasFactory<LanguageLevelFactory> */
    use HasFactory;

    protected $table = 'language_levels';

    protected $fillable = [
        'language_id',
        'description',
        'level',
        'status',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function levelContents(): HasMany
    {
        return $this->hasMany(LevelContent::class);
    }

    public function studyProgram(): HasOne
    {
        return $this->hasOne(StudyProgram::class);
    }
}
