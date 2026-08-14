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
        if (Schema::hasTable('activity_submissions')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                if (!Schema::hasColumn('activity_submissions', 'submitter_name_snapshot')) {
                    $table->string('submitter_name_snapshot')->nullable()->after('user_id');
                }
            });
        }

        if (Schema::hasTable('approval_logs')) {
            Schema::table('approval_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('approval_logs', 'actor_name_snapshot')) {
                    $table->string('actor_name_snapshot')->nullable()->after('user_id');
                }
            });
        }

        if (Schema::hasTable('system_audit_logs')) {
            Schema::table('system_audit_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('system_audit_logs', 'actor_name_snapshot')) {
                    $table->string('actor_name_snapshot')->nullable()->after('user_id');
                }
            });
        }

        if (Schema::hasTable('activity_discussions')) {
            Schema::table('activity_discussions', function (Blueprint $table) {
                if (!Schema::hasColumn('activity_discussions', 'author_name_snapshot')) {
                    $table->string('author_name_snapshot')->nullable()->after('user_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('activity_submissions') && Schema::hasColumn('activity_submissions', 'submitter_name_snapshot')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                $table->dropColumn('submitter_name_snapshot');
            });
        }

        if (Schema::hasTable('approval_logs') && Schema::hasColumn('approval_logs', 'actor_name_snapshot')) {
            Schema::table('approval_logs', function (Blueprint $table) {
                $table->dropColumn('actor_name_snapshot');
            });
        }

        if (Schema::hasTable('system_audit_logs') && Schema::hasColumn('system_audit_logs', 'actor_name_snapshot')) {
            Schema::table('system_audit_logs', function (Blueprint $table) {
                $table->dropColumn('actor_name_snapshot');
            });
        }

        if (Schema::hasTable('activity_discussions') && Schema::hasColumn('activity_discussions', 'author_name_snapshot')) {
            Schema::table('activity_discussions', function (Blueprint $table) {
                $table->dropColumn('author_name_snapshot');
            });
        }
    }
};
