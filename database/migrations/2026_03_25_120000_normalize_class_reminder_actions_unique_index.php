<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $uniqueIndexes = $this->uniqueIndexes('class_reminder_actions');
        $singleColumnUniqueName = collect($uniqueIndexes)
            ->first(fn (array $columns): bool => $columns === ['class_schedule_detail_id']);

        if (! is_string($singleColumnUniqueName)) {
            return;
        }

        Schema::table('class_reminder_actions', function (Blueprint $table): void {
            $table->index('class_schedule_detail_id', 'class_reminder_actions_detail_idx');
            $table->index('student_id', 'class_reminder_actions_student_idx');
            $table->dropUnique($singleColumnUniqueName);
            $table->unique(['class_schedule_detail_id', 'student_id'], 'class_reminder_actions_unique');
        });
    }

    public function down(): void
    {
        //
    }

    private function uniqueIndexes(string $table): array
    {
        return match (DB::getDriverName()) {
            'mysql' => $this->mysqlUniqueIndexes($table),
            'sqlite' => $this->sqliteUniqueIndexes($table),
            default => [],
        };
    }

    private function mysqlUniqueIndexes(string $table): array
    {
        $rows = DB::select(
            'select
                index_name as index_name_value,
                non_unique as non_unique_value,
                column_name as column_name_value,
                seq_in_index as seq_in_index_value
             from information_schema.statistics
             where table_schema = database()
               and table_name = ?
             order by index_name, seq_in_index',
            [$table]
        );

        $indexes = [];

        foreach ($rows as $row) {
            if ((int) $row->non_unique_value !== 0) {
                continue;
            }

            $indexes[$row->index_name_value][] = $row->column_name_value;
        }

        return $indexes;
    }

    private function sqliteUniqueIndexes(string $table): array
    {
        $indexes = [];
        $indexList = DB::select("PRAGMA index_list('{$table}')");

        foreach ($indexList as $index) {
            if ((int) $index->unique !== 1) {
                continue;
            }

            $indexName = $index->name;
            $indexInfo = DB::select("PRAGMA index_info('{$indexName}')");
            $indexes[$indexName] = array_map(
                fn (object $column): string => $column->name,
                $indexInfo
            );
        }

        return $indexes;
    }
};
