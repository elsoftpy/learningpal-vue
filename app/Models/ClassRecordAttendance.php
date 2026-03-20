<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassRecordAttendance extends Model
{
    /** @use HasFactory<\Database\Factories\ClassRecordAttendanceFactory> */
    use HasFactory;

    protected $table = 'class_record_attendances';

    protected $fillable = [
        'class_record_id',
        'student_id',
        'attendance',
    ];

    protected function casts(): array
    {
        return [
            'attendance' => 'decimal:2',
        ];
    }

    public function classRecord(): BelongsTo
    {
        return $this->belongsTo(ClassRecord::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
