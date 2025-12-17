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
        Schema::create('class_schedule_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_schedule_id')->constrained();
            $table->date('session_date');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('estimated_duration_minutes');
            $table->date('rescheduled_date')->nullable();
            $table->dateTime('rescheduled_start_time')->nullable();
            $table->dateTime('rescheduled_end_time')->nullable();
            $table->integer('rescheduled_estimated_duration_minutes')->nullable();
            $table->integer('reschedule_count')->default(0);
            $table->string('topic')->nullable();
            $table->string('activity')->nullable();
            $table->text('comments')->nullable();
            $table->integer('order');
            $table->string('status')->default('scheduled')->comment('Values: scheduled, completed, canceled, rescheduled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_schedule_details');
    }
};
