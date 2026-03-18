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
        Schema::create('class_record_detail_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_record_detail_id')->constrained();
            $table->foreignId('student_id')->constrained();
            $table->integer('completed')->default(0)->comment('1 if completed, 0 otherwise');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_record_detail_students');
    }
};
