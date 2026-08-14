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
        // Add soft deletes to activity_submissions for Super Admin restore capability
        if (Schema::hasTable('activity_submissions') && !Schema::hasColumn('activity_submissions', 'deleted_at')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add account lock/status columns to users if missing
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'is_locked')) {
                    $table->boolean('is_locked')->default(false);
                }
            });
        }

        // 1. Dynamic Roles & Permissions Table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // e.g. super_admin, college_admin, editor, office_registrar, registrar, vc, pro_vc, viewer
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->longText('permissions')->nullable(); // JSON: modules, crud, approval, visibility
            $table->string('data_scope')->default('college'); // all, college, self
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Dynamic Workflow Stages Table
        Schema::create('workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->integer('stage_order')->default(1);
            $table->string('name'); // e.g. College Verifier, Registrar, Office of Registrar, VC / Pro-VC, Media Team
            $table->string('role_slug'); // Role responsible for this stage
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('allow_parallel')->default(false);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Dynamic Activity Statuses Table
        Schema::create('activity_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // e.g. draft, verification_pending, approval_pending, registrar_approval, ready_to_publish, scheduled, published, editor_discuss, rejected
            $table->string('name');
            $table->string('badge_color')->default('primary'); // primary, success, warning, danger, info, secondary, dark
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Notification Templates Table
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique(); // new_submission, approval, discussion, rejection, resubmission, publishing
            $table->string('name');
            $table->string('email_subject')->nullable();
            $table->text('email_body')->nullable();
            $table->string('system_title')->nullable();
            $table->text('system_message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Immutable Audit Log Table
        Schema::create('system_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();
            $table->string('event_type'); // login, activity_create, activity_update, activity_delete, approval_action, discussion_comment, status_change, workflow_change, user_management, college_management
            $table->string('target_type')->nullable(); // ActivitySubmission, User, College, Role, WorkflowStage
            $table->string('target_id')->nullable();
            $table->text('description')->nullable();
            $table->longText('old_values')->nullable(); // JSON
            $table->longText('new_values')->nullable(); // JSON
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        // 6. Dynamic Master Settings Table
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable(); // JSON or plain string
            $table->string('group')->default('general'); // general, social_media, categories, platforms
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('system_audit_logs');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('activity_statuses');
        Schema::dropIfExists('workflow_stages');
        Schema::dropIfExists('roles');

        if (Schema::hasTable('activity_submissions') && Schema::hasColumn('activity_submissions', 'deleted_at')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_locked')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_locked');
            });
        }
    }
};
