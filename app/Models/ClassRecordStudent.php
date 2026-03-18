<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassRecordStudent extends Model
{
    /** @use HasFactory<\Database\Factories\ClassRecordStudentsFactory> */
    use HasFactory;

    protected $table = 'class_record_students';

    protected $fillable = [
        'class_record_id',
        'student_id',
        'status',
        'status_date',
    ];

    public function classRecord(): BelongsTo
    {
        return $this->belongsTo(ClassRecord::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
