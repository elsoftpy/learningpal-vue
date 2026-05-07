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
        Schema::table('distance_activities', function (Blueprint $table) {
            $table->foreignId('language_level_id')->nullable()->after('course_id')->constrained('language_levels')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distance_activities', function (Blueprint $table) {
            $table->dropForeign(['language_level_id']);
            $table->dropColumn('language_level_id');
        });
    }
};
