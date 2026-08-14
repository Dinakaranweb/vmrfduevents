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
        // 1. Create Workflows Master Table
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Insert default Standard Institutional Workflow
        $defaultWorkflowId = DB::table('workflows')->insertGetId([
            'name' => 'Standard Institutional Workflow',
            'description' => 'Default 6-stage institutional approval chain for constituent colleges',
            'is_active' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Enhance workflow_stages table
        if (Schema::hasTable('workflow_stages')) {
            Schema::table('workflow_stages', function (Blueprint $table) use ($defaultWorkflowId) {
                if (!Schema::hasColumn('workflow_stages', 'workflow_id')) {
                    $table->unsignedBigInteger('workflow_id')->default($defaultWorkflowId)->after('id');
                }
                if (!Schema::hasColumn('workflow_stages', 'user_ids')) {
                    $table->longText('user_ids')->nullable()->after('role_slug'); // JSON array of user IDs
                }
                if (!Schema::hasColumn('workflow_stages', 'college_id')) {
                    $table->unsignedBigInteger('college_id')->nullable()->after('user_ids');
                }
                if (!Schema::hasColumn('workflow_stages', 'designation')) {
                    $table->string('designation')->nullable()->after('college_id');
                }
                if (!Schema::hasColumn('workflow_stages', 'allowed_actions')) {
                    $table->longText('allowed_actions')->nullable()->after('allow_parallel'); // JSON array of allowed actions
                }
                if (!Schema::hasColumn('workflow_stages', 'approval_condition')) {
                    $table->string('approval_condition')->default('any_one')->after('allowed_actions'); // any_one, all_must
                }
                if (!Schema::hasColumn('workflow_stages', 'status_on_approve')) {
                    $table->string('status_on_approve')->nullable()->after('approval_condition');
                }
                if (!Schema::hasColumn('workflow_stages', 'status_on_reject')) {
                    $table->string('status_on_reject')->nullable()->after('status_on_approve');
                }
                if (!Schema::hasColumn('workflow_stages', 'status_on_discuss')) {
                    $table->string('status_on_discuss')->nullable()->after('status_on_reject');
                }
            });

            // Update existing workflow stages to belong to default workflow and have default allowed actions
            DB::table('workflow_stages')->update([
                'workflow_id' => $defaultWorkflowId,
                'allowed_actions' => json_encode(['approve', 'discuss', 'reject', 'return', 'forward', 'hold', 'reassign']),
                'approval_condition' => 'any_one'
            ]);
        }

        // 3. Create Workflow Assignment Rules Table
        Schema::create('workflow_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            $table->unsignedBigInteger('college_id')->nullable();
            $table->string('category')->nullable();
            $table->string('activity_type')->nullable();
            $table->string('role_slug')->nullable();
            $table->integer('priority')->default(0); // Higher number = higher priority matching rule
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Enhance activity_submissions table
        if (Schema::hasTable('activity_submissions')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                if (!Schema::hasColumn('activity_submissions', 'workflow_id')) {
                    $table->unsignedBigInteger('workflow_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('activity_submissions', 'current_stage_id')) {
                    $table->unsignedBigInteger('current_stage_id')->nullable()->after('workflow_id');
                }
            });
        }

        // 5. Enhance approval_logs table
        if (Schema::hasTable('approval_logs')) {
            Schema::table('approval_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('approval_logs', 'workflow_stage_id')) {
                    $table->unsignedBigInteger('workflow_stage_id')->nullable()->after('activity_submission_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('approval_logs')) {
            Schema::table('approval_logs', function (Blueprint $table) {
                $table->dropColumn(['workflow_stage_id']);
            });
        }

        if (Schema::hasTable('activity_submissions')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                $table->dropColumn(['workflow_id', 'current_stage_id']);
            });
        }

        Schema::dropIfExists('workflow_rules');

        if (Schema::hasTable('workflow_stages')) {
            Schema::table('workflow_stages', function (Blueprint $table) {
                $table->dropColumn([
                    'workflow_id',
                    'user_ids',
                    'college_id',
                    'designation',
                    'allowed_actions',
                    'approval_condition',
                    'status_on_approve',
                    'status_on_reject',
                    'status_on_discuss',
                ]);
            });
        }

        Schema::dropIfExists('workflows');
    }
};
