<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassScheduleDetailStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'class_schedule_detail_id',
        'changed_by_user_id',
        'changed_by_type',
        'old_status',
        'new_status',
        'action_type',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function classScheduleDetail(): BelongsTo
    {
        return $this->belongsTo(ClassScheduleDetail::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
