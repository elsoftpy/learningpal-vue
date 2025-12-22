<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LevelContent extends Model
{
    /** @use HasFactory<\Database\Factories\LevelContentFactory> */
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
}
