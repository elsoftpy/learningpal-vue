<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassReminderAction extends Model
{
    use HasFactory;

    protected $table = 'class_reminder_actions';

    protected $fillable = [
        'class_schedule_detail_id',
        'student_id',
        'action_type',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }
}
