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
        Schema::create('study_program_week_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_program_week_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_content_id')->nullable()->constrained('level_contents');
            $table->text('free_content')->nullable();
            $table->string('activity_name');
            $table->string('type')
                ->comment('Activity type: '.implode(', ', StudyProgramActivityTypeEnum::values()));
            $table->text('links')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_program_week_activities');
    }
};
