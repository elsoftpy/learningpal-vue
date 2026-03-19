<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistanceActivityStudent extends Model
{
    /** @use HasFactory<\Database\Factories\DistanceActivityStudentFactory> */
    use HasFactory;

    protected $table = 'distance_activity_students';

    protected $fillable = [
        'distance_activity_id',
        'student_id',
        'completed',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function distanceActivity(): BelongsTo
    {
        return $this->belongsTo(DistanceActivity::class, 'distance_activity_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
