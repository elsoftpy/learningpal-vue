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
        Schema::create('class_record_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_record_id')->constrained();
            $table->foreignId('student_id')->constrained();
            $table->integer('status')->default(0);
            $table->dateTime('status_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_record_students');
    }
};
