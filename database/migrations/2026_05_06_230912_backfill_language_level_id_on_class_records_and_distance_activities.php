<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill language_level_id on class_records and distance_activities
     * from the associated course's language_level_id.
     */
    public function up(): void
    {
        DB::table('class_records')
            ->whereNull('language_level_id')
            ->orderBy('id')
            ->pluck('course_id', 'id')
            ->chunk(200)
            ->each(function ($chunk): void {
                $courseIds = $chunk->unique()->values()->all();

                $languageLevelByCourse = DB::table('courses')
                    ->whereIn('id', $courseIds)
                    ->pluck('language_level_id', 'id')
                    ->all();

                foreach ($chunk as $recordId => $courseId) {
                    $languageLevelId = $languageLevelByCourse[$courseId] ?? null;

                    if ($languageLevelId) {
                        DB::table('class_records')
                            ->where('id', $recordId)
                            ->update(['language_level_id' => $languageLevelId]);
                    }
                }
            });

        DB::table('distance_activities')
            ->whereNull('language_level_id')
            ->orderBy('id')
            ->pluck('course_id', 'id')
            ->chunk(200)
            ->each(function ($chunk): void {
                $courseIds = $chunk->unique()->values()->all();

                $languageLevelByCourse = DB::table('courses')
                    ->whereIn('id', $courseIds)
                    ->pluck('language_level_id', 'id')
                    ->all();

                foreach ($chunk as $activityId => $courseId) {
                    $languageLevelId = $languageLevelByCourse[$courseId] ?? null;

                    if ($languageLevelId) {
                        DB::table('distance_activities')
                            ->where('id', $activityId)
                            ->update(['language_level_id' => $languageLevelId]);
                    }
                }
            });
    }

    public function down(): void
    {
        DB::table('class_records')->update(['language_level_id' => null]);
        DB::table('distance_activities')->update(['language_level_id' => null]);
    }
};
