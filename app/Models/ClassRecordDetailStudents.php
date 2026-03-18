<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassRecordDetailStudents extends Model
{
    /** @use HasFactory<\Database\Factories\ClassRecordDetailStudentsFactory> */
    use HasFactory;

    protected $table = 'class_record_detail_students';

    protected $fillable = [
        'class_record_detail_id',
        'student_id',
        'completed',
        'completed_at',
    ];

    public function classRecordDetail(): BelongsTo
    {
        return $this->belongsTo(ClassRecordDetail::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
