<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $table = 'email_logs';

    protected $fillable = [
        'email_destino',
        'greeting',
        'hora',
        'url',
        'estado',
        'error',
        'class_schedule_detail_id',
        'course_id',
        'course_name',
        'session_date',
        'start_time',
        'rescheduled_date',
        'rescheduled_start_time',
        'teacher_name',
        'student_name',
        'action_type',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'rescheduled_date' => 'date',
        ];
    }
}
