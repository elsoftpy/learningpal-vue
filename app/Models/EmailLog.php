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

    /**
     * @param  array{
     *     class_schedule_detail_id?: int,
     *     course_id?: int,
     *     course_name?: string,
     *     session_date?: string,
     *     start_time?: string,
     *     rescheduled_date?: string,
     *     rescheduled_start_time?: string,
     *     teacher_name?: string,
     *     student_name?: string,
     *     action_type?: string
     * }  $context
     */
    public static function recordNotification(
        string $emailDestino,
        string $greeting,
        string $hora,
        string $estado,
        ?string $url = null,
        ?string $error = null,
        array $context = []
    ): self {
        return self::query()->create([
            'email_destino' => $emailDestino,
            'greeting' => $greeting,
            'hora' => $hora,
            'url' => $url,
            'estado' => $estado,
            'error' => $error,
        ] + $context);
    }
}
