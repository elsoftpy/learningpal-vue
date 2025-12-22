<?php

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
        Schema::create('class_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained();
            $table->foreignId('teacher_id')->constrained();
            $table->foreignId('class_schedule_detail_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->date('date');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->decimal('attendance', 5, 2)->default(0);
            $table->string('comments')->nullable();
            $table->string('mode')->default('online')->comment('Valid values: online, in-person');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_records');
    }
};
