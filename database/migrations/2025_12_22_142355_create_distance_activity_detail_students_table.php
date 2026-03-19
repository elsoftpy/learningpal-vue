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
        Schema::create('distance_activity_detail_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distance_activity_detail_id')->constrained(indexName: 'distance_activity_det_students_distance_activity_det_id_foreign');
            $table->foreignId('student_id')->constrained();
            $table->boolean('completed')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('video_opened_at')->nullable();
            $table->timestamps();

            $table->unique(['distance_activity_detail_id', 'student_id'], 'distance_activity_detail_student_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distance_activity_detail_students');
    }
};
