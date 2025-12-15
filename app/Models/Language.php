<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    /** @use HasFactory<\Database\Factories\LanguageFactory> */
    use HasFactory;

    protected $table = 'languages';

    protected $fillable = [
        'name',
    ];

    public function levels(): HasMany
    {
        return $this->hasMany(LanguageLevel::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
