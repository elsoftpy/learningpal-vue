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
        Schema::create('study_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_level_id')->constrained();
            $table->string('title');
            $table->string('status')
                ->default(StudyProgramStatusEnum::ACTIVE->value)
                ->comment('Study program status: '.implode(', ', StudyProgramStatusEnum::values()));
            $table->timestamps();

            $table->unique('language_level_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_programs');
    }
};
