<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistanceActivityDetailStudent extends Model
{
    /** @use HasFactory<\Database\Factories\DistanceActivityDetailStudentFactory> */
    use HasFactory;

    protected $table = 'distance_activity_detail_students';

    protected $fillable = [
        'distance_activity_detail_id',
        'student_id',
        'completed',
        'completed_at',
    ];

    public function distanceActivityDetail(): BelongsTo
    {
        return $this->belongsTo(DistanceActivityDetail::class, 'distance_activity_detail_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
