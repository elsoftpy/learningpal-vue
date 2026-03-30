<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\ClassScheduleFactory> */
    use HasFactory;

    protected $table = 'class_schedules';

    protected $fillable = [
        'course_id',
        'name',
        'schedule_month',
        'feedback',
    ];

    protected function casts(): array
    {
        return [
            'schedule_month' => 'date',
        ];
    }

    public function setScheduleMonthAttribute($value): void
    {
        $this->attributes['schedule_month'] = $value
            ? Carbon::parse($value)->startOfMonth()->toDateString()
            : null;
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(ClassScheduleDetail::class);
    }
}
