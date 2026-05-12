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
        Schema::table('email_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('class_schedule_detail_id')->nullable()->after('id');
            $table->unsignedBigInteger('course_id')->nullable()->after('class_schedule_detail_id');
            $table->string('course_name')->nullable()->after('course_id');
            $table->date('session_date')->nullable()->after('course_name');
            $table->time('start_time')->nullable()->after('session_date');
            $table->date('rescheduled_date')->nullable()->after('start_time');
            $table->time('rescheduled_start_time')->nullable()->after('rescheduled_date');
            $table->string('teacher_name')->nullable()->after('rescheduled_start_time');
            $table->string('student_name')->nullable()->after('teacher_name');
            $table->string('action_type')->nullable()->after('student_name');

            $table->foreign('class_schedule_detail_id')
                ->references('id')
                ->on('class_schedule_details')
                ->onDelete('set null');

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropForeignKey(['class_schedule_detail_id']);
            $table->dropForeignKey(['course_id']);
            $table->dropColumn([
                'class_schedule_detail_id',
                'course_id',
                'course_name',
                'session_date',
                'start_time',
                'rescheduled_date',
                'rescheduled_start_time',
                'teacher_name',
                'student_name',
                'action_type',
            ]);
        });
    }
};
