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
        Schema::create('class_program_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_program_id')->constrained();
            $table->date('session_date');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('estimated_duration_minutes');
            $table->string('topic');
            $table->string('activity');
            $table->integer('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_program_details');
    }
};
