<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('activity_media', 'media_category')) {
            Schema::table('activity_media', function (Blueprint $table) {
                $table->string('media_category')->default('original')->after('file_type'); // 'original' vs 'edited_social'
            });

            // Migrate existing edited media if any
            DB::table('activity_media')
                ->where('file_name', 'like', 'Edited:%')
                ->orWhere('file_path', 'like', '%_edited_%')
                ->update(['media_category' => 'edited_social']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('activity_media', 'media_category')) {
            Schema::table('activity_media', function (Blueprint $table) {
                $table->dropColumn('media_category');
            });
        }
    }
};
