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
        Schema::create('class_schedule_detail_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_schedule_detail_id');
            $table->foreign('class_schedule_detail_id', 'csdsh_detail_id_fk')
                ->references('id')->on('class_schedule_details')->cascadeOnDelete();
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->foreign('changed_by_user_id', 'csdsh_user_id_fk')
                ->references('id')->on('users')->nullOnDelete();
            $table->string('changed_by_type')->default('system'); // student, teacher, admin, system
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('action_type')->nullable(); // pending, upload_task
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_schedule_detail_status_histories');
    }
};
