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
        Schema::table('activity_submissions', function (Blueprint $table) {
            $table->index('status');
            $table->index('college_id');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('activity_date');
        });

        Schema::table('activity_media', function (Blueprint $table) {
            $table->index('media_category');
            $table->index('file_type');
        });

        Schema::table('approval_logs', function (Blueprint $table) {
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_submissions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['college_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
            $table->dropIndex(['activity_date']);
        });

        Schema::table('activity_media', function (Blueprint $table) {
            $table->dropIndex(['media_category']);
            $table->dropIndex(['file_type']);
        });

        Schema::table('approval_logs', function (Blueprint $table) {
            $table->dropIndex(['action']);
            $table->dropIndex(['created_at']);
        });
    }
};
