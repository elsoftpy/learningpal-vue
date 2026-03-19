<?php

use App\Enums\StudyProgramActivityTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('distance_activity_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distance_activity_id')->constrained();
            $table->foreignId('study_program_week_activity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('content_id')->nullable()->constrained('level_contents');
            $table->text('free_content')->nullable();
            $table->string('activity');
            $table->string('type')
                ->comment('Activity type snapshot: '.implode(', ', StudyProgramActivityTypeEnum::values()));
            $table->text('links')->nullable();
            $table->string('file_path')->nullable()->comment('Legacy field for backward compatibility');
            $table->string('file_name')->nullable()->comment('Legacy field for backward compatibility');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distance_activity_details');
    }
};
