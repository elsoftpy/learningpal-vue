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
        Schema::table('distance_activity_detail_students', function (Blueprint $table) {
            $table->json('link_opened_at_map')->nullable()->after('video_opened_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distance_activity_detail_students', function (Blueprint $table) {
            $table->dropColumn('link_opened_at_map');
        });
    }
};
