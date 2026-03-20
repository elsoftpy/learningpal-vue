<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_reminder_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('class_schedule_detail_id')->constrained('class_schedule_details')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('action_type');
            $table->timestamp('processed_at');
            $table->timestamps();

            $table->unique(['class_schedule_detail_id', 'student_id'], 'class_reminder_actions_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_reminder_actions');
    }
};
