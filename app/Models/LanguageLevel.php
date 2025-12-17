<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LanguageLevel extends Model
{
    /** @use HasFactory<\Database\Factories\LanguageLevelFactory> */
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


}
