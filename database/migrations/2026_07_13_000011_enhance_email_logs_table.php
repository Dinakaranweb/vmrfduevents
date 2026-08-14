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
        if (Schema::hasTable('email_logs')) {
            Schema::table('email_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('email_logs', 'reference_id')) {
                    $table->string('reference_id')->nullable()->after('recipient_email')->index();
                }
                if (!Schema::hasColumn('email_logs', 'sender')) {
                    $table->string('sender')->nullable()->default('VMRFDU Portal Notification Desk')->after('id');
                }
                if (!Schema::hasColumn('email_logs', 'error_message')) {
                    $table->text('error_message')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('email_logs')) {
            Schema::table('email_logs', function (Blueprint $table) {
                if (Schema::hasColumn('email_logs', 'reference_id')) {
                    $table->dropColumn('reference_id');
                }
                if (Schema::hasColumn('email_logs', 'sender')) {
                    $table->dropColumn('sender');
                }
                if (Schema::hasColumn('email_logs', 'error_message')) {
                    $table->dropColumn('error_message');
                }
            });
        }
    }
};
