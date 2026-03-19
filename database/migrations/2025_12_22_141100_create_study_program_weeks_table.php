<?php

use App\Enums\StudyProgramStatusEnum;
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
        Schema::create('study_program_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_program_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('week_number');
            $table->string('title');
            $table->string('status')
                ->default(StudyProgramStatusEnum::ACTIVE->value)
                ->comment('Study program week status: '.implode(', ', StudyProgramStatusEnum::values()));
            $table->timestamps();

            $table->unique(['study_program_id', 'week_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_program_weeks');
    }
};
