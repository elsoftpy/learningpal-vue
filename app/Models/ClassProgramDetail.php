<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassProgramDetail extends Model
{
    /** @use HasFactory<\Database\Factories\ClassProgramDetailFactory> */
    use HasFactory;

    protected $table = 'class_program_details';

    protected $fillable = [
        'class_program_id',
        'session_date',
        'start_time',
        'end_time',
        'estimated_duration_minutes',
        'topic',
        'activity',
        'order',
    ];

    public function classProgram(): BelongsTo
    {
        return $this->belongsTo(ClassProgram::class);
    }
}
