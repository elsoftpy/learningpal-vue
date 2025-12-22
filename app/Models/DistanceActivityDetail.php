<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistanceActivityDetail extends Model
{
    /** @use HasFactory<\Database\Factories\DistanceActivityDetailFactory> */
    use HasFactory;

    protected $table = 'distance_activity_details';

    protected $fillable = [
        'distance_activity_id',
        'content_id',
        'free_content',
        'activity',
        'links',
        'file_path',
        'file_name',
    ];

    public function distanceActivity(): BelongsTo
    {
        return $this->belongsTo(DistanceActivity::class, 'distance_activity_id');
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(LevelContent::class, 'content_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(DistanceActivityDetailStudent::class, 'distance_activity_detail_id');
    }
}
