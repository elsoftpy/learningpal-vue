<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassRecordDetail extends Model
{
    /** @use HasFactory<\Database\Factories\ClassRecordDetailFactory> */
    use HasFactory;

    protected $table = 'class_record_details';

    protected $fillable = [
        'class_record_id',
        'content_id',
        'free_content',
        'activity',
        'links',
        'file_path',
        'file_name',
    ];

    public function classRecord(): BelongsTo
    {
        return $this->belongsTo(ClassRecord::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(LevelContent::class, 'content_id');
    }
}
