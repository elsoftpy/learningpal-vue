<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassProgram extends Model
{
    /** @use HasFactory<\Database\Factories\ClassProgramFactory> */
    use HasFactory;

    protected $table = 'class_programs';

    protected $fillable = [
        'course_id',
        'name',
        'program_month',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(ClassProgramDetail::class);
    }
}
