<?php

namespace App\Services\Academics\Lessons;

use App\Models\ClassRecordDetail;

class ClassRecordDetailService
{
    public function classRecordDetailData(ClassRecordDetail $detail)
    {
        $attachment = $detail->getFirstMedia('attachment');

        return [
            'id' => $detail->id,
            'class_record_id' => $detail->class_record_id,
            'content_id' => $detail->content_id,
            'free_content' => $detail->free_content,
            'content_name' => $detail->content_id ? $detail->content->content : $detail->free_content,
            'activity' => $detail->activity,
            'links' => $detail->links,
            'attachment_url' => $attachment?->getUrl(),
            'attachment_name' => $attachment?->file_name,
        ];
    }
}