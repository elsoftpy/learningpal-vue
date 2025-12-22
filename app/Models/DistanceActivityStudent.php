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
        'status',
        'status_date',
    ];

    public function distanceActivity(): BelongsTo
    {
        return $this->belongsTo(DistanceActivity::class, 'distance_activity_id');
    }
}
