<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\WorkflowStage;
use App\Models\ActivityStatus;
use App\Models\NotificationTemplate;
use App\Models\SystemSetting;

class SuperAdminDynamicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Roles
        $roles = [
            ['slug' => 'super_admin', 'name' => 'Super Admin', 'description' => 'Unrestricted University System Controller', 'is_system' => true, 'data_scope' => 'all'],
            ['slug' => 'admin', 'name' => 'Admin', 'description' => 'Global University Administrator', 'is_system' => true, 'data_scope' => 'all'],
            ['slug' => 'editor', 'name' => 'Editor', 'description' => 'Media Studio Content Editor', 'is_system' => true, 'data_scope' => 'all'],
            ['slug' => 'office_registrar', 'name' => 'Office of Registrar', 'description' => 'Office of the Registrar Final Approval Authority', 'is_system' => true, 'data_scope' => 'all'],
            ['slug' => 'registrar', 'name' => 'Registrar', 'description' => 'University Registrar Verification Authority', 'is_system' => true, 'data_scope' => 'all'],
            ['slug' => 'vc', 'name' => 'Vice Chancellor (VC)', 'description' => 'Honorable Vice Chancellor Leadership Authority', 'is_system' => true, 'data_scope' => 'all'],
            ['slug' => 'pro_vc', 'name' => 'Pro Vice Chancellor (Pro-VC)', 'description' => 'Pro Vice Chancellor Leadership Authority', 'is_system' => true, 'data_scope' => 'all'],
            ['slug' => 'media_editor', 'name' => 'Media Team', 'description' => 'Broadcasting & Social Media Publication Team', 'is_system' => true, 'data_scope' => 'all'],
            ['slug' => 'college_admin', 'name' => 'College User', 'description' => 'Constituent College Institutional Coordinator', 'is_system' => true, 'data_scope' => 'college'],
            ['slug' => 'viewer', 'name' => 'Viewer', 'description' => 'Read-only Institutional Observer', 'is_system' => false, 'data_scope' => 'college'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['slug' => $r['slug']], $r);
        }

        // 2. Workflow Stages
        $stages = [
            ['stage_order' => 1, 'name' => 'College User Verification', 'role_slug' => 'college_admin', 'is_mandatory' => true, 'description' => 'Initial institutional activity entry and verification'],
            ['stage_order' => 2, 'name' => 'Media Studio Editor', 'role_slug' => 'media_editor', 'is_mandatory' => true, 'description' => 'Media processing, caption drafting, and enhancement'],
            ['stage_order' => 3, 'name' => 'Office of Registrar Review', 'role_slug' => 'office_registrar', 'is_mandatory' => true, 'description' => 'Final compliance and publication authorization'],
            ['stage_order' => 4, 'name' => 'Registrar Verification', 'role_slug' => 'registrar', 'is_mandatory' => true, 'description' => 'Official authority verification level'],
            ['stage_order' => 5, 'name' => 'VC / Pro-VC Leadership Oversight', 'role_slug' => 'vc', 'is_mandatory' => false, 'description' => 'Optional executive leadership review level'],
            ['stage_order' => 6, 'name' => 'Media Team Broadcast Desk', 'role_slug' => 'media_editor', 'is_mandatory' => true, 'description' => 'Final social media broadcasting and archiving'],
        ];

        foreach ($stages as $s) {
            WorkflowStage::updateOrCreate(['stage_order' => $s['stage_order']], $s);
        }

        // 3. Activity Statuses
        $statuses = [
            ['slug' => 'draft', 'name' => 'Draft', 'badge_color' => 'secondary', 'is_system' => true],
            ['slug' => 'verification_pending', 'name' => 'Submitted / Pending Verification', 'badge_color' => 'warning', 'is_system' => true],
            ['slug' => 'approval_pending', 'name' => 'Under Authority Review', 'badge_color' => 'info', 'is_system' => true],
            ['slug' => 'registrar_approval', 'name' => 'Pending Office of Registrar', 'badge_color' => 'primary', 'is_system' => true],
            ['slug' => 'ready_to_publish', 'name' => 'Approved (Ready to Publish)', 'badge_color' => 'success', 'is_system' => true],
            ['slug' => 'editor_discuss', 'name' => 'Under Discussion', 'badge_color' => 'dark', 'is_system' => true],
            ['slug' => 'rejected', 'name' => 'Rejected', 'badge_color' => 'danger', 'is_system' => true],
            ['slug' => 'scheduled', 'name' => 'Scheduled for Broadcast', 'badge_color' => 'info', 'is_system' => true],
            ['slug' => 'published', 'name' => 'Published Live', 'badge_color' => 'success', 'is_system' => true],
        ];

        foreach ($statuses as $st) {
            ActivityStatus::updateOrCreate(['slug' => $st['slug']], $st);
        }

        // 4. Notification Templates
        $templates = [
            ['event_key' => 'new_submission', 'name' => 'New Activity Submission', 'email_subject' => '[VMRFDU Portal] New Activity Submitted: {title}', 'system_title' => 'New Submission Received', 'system_message' => 'An activity "{title}" was submitted by {user_name} for review.'],
            ['event_key' => 'approval', 'name' => 'Activity Approved', 'email_subject' => '[VMRFDU Portal] Activity Approved: {title}', 'system_title' => 'Submission Approved', 'system_message' => 'Your activity "{title}" has been authorized for media broadcasting.'],
            ['event_key' => 'discussion', 'name' => 'Activity Sent for Discussion', 'email_subject' => '[VMRFDU Portal] Action Required: Discussion on {title}', 'system_title' => 'Correction Requested', 'system_message' => 'Reviewer marked "{title}" for discussion. Please address remarks and resubmit.'],
            ['event_key' => 'rejection', 'name' => 'Activity Rejected', 'email_subject' => '[VMRFDU Portal] Submission Declined: {title}', 'system_title' => 'Submission Declined', 'system_message' => 'Activity "{title}" was declined by authority.'],
            ['event_key' => 'publishing', 'name' => 'Activity Published Live', 'email_subject' => '[VMRFDU Portal] Live Broadcast: {title}', 'system_title' => 'Published Live', 'system_message' => 'Your activity "{title}" is now live on social media channels.'],
        ];

        foreach ($templates as $tmpl) {
            NotificationTemplate::updateOrCreate(['event_key' => $tmpl['event_key']], $tmpl);
        }

        // 5. System Master Settings
        SystemSetting::updateOrCreate(
            ['key' => 'portal_title'],
            ['value' => 'Vinayaka Mission\'s Research Foundation (Deemed to be University) Institutional Activity Portal', 'group' => 'general']
        );
    }
}
