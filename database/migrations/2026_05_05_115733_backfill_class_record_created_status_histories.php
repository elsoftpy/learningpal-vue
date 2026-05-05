<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill a class_record_created history entry for every completed
     * class_schedule_detail that does not already have one.
     * The assumed previous status is 'scheduled', and the user_id is sourced
     * from the linked class_record when available.
     */
    public function up(): void
    {
        $now = now();

        $alreadyBackfilledIds = DB::table('class_schedule_detail_status_histories')
            ->where('action_type', 'class_record_created')
            ->pluck('class_schedule_detail_id')
            ->all();

        DB::table('class_schedule_details')
            ->where('status', 'completed')
            ->when($alreadyBackfilledIds, fn ($q) => $q->whereNotIn('id', $alreadyBackfilledIds))
            ->pluck('id')
            ->chunk(200)
            ->each(function ($detailIds) use ($now): void {
                $detailIds = $detailIds->all();

                $userIdByDetail = DB::table('class_records')
                    ->whereIn('class_schedule_detail_id', $detailIds)
                    ->select('class_schedule_detail_id', DB::raw('MAX(user_id) as user_id'))
                    ->groupBy('class_schedule_detail_id')
                    ->pluck('user_id', 'class_schedule_detail_id')
                    ->all();

                $rows = array_map(fn ($detailId) => [
                    'class_schedule_detail_id' => $detailId,
                    'changed_by_user_id' => $userIdByDetail[$detailId] ?? null,
                    'changed_by_type' => 'class_record',
                    'old_status' => 'scheduled',
                    'new_status' => 'completed',
                    'action_type' => 'class_record_created',
                    'created_at' => $now,
                ], $detailIds);

                DB::table('class_schedule_detail_status_histories')->insert($rows);
            });
    }

    public function down(): void
    {
        DB::table('class_schedule_detail_status_histories')
            ->where('action_type', 'class_record_created')
            ->whereNotExists(function ($query): void {
                $query->select(DB::raw(1))
                    ->from('class_records as cr')
                    ->whereColumn('cr.class_schedule_detail_id', 'class_schedule_detail_status_histories.class_schedule_detail_id');
            })
            ->delete();
    }
};
