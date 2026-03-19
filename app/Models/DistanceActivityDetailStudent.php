<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DistanceActivityDetailStudent extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\DistanceActivityDetailStudentFactory> */
    use HasFactory, InteractsWithMedia;

    protected $table = 'distance_activity_detail_students';

    protected $fillable = [
        'distance_activity_detail_id',
        'student_id',
        'completed',
        'completed_at',
        'video_opened_at',
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'completed_at' => 'datetime',
            'video_opened_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('student-production');
    }

    public function distanceActivityDetail(): BelongsTo
    {
        return $this->belongsTo(DistanceActivityDetail::class, 'distance_activity_detail_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
