<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_system',
        'permissions',
        'data_scope',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    public static function getAvailablePermissions(): array
    {
        return [
            'colleges' => [
                'label' => 'Constituent College Management',
                'actions' => [
                    'colleges.view' => 'View Colleges Directory & Stats',
                    'colleges.create' => 'Create New Constituent Colleges',
                    'colleges.edit' => 'Edit College Profiles & Settings',
                    'colleges.delete' => 'Delete / Deactivate Colleges',
                ]
            ],
            'users' => [
                'label' => 'User & Account Management',
                'actions' => [
                    'users.view' => 'View Users Directory & Roles',
                    'users.create' => 'Create New User Accounts',
                    'users.edit' => 'Edit User Profiles & Roles',
                    'users.delete' => 'Delete / Deactivate Accounts',
                    'users.reset_password' => 'Force Password Resets & Account Unlock',
                    'users.transfer' => 'Transfer Users Between Colleges',
                ]
            ],
            'activities' => [
                'label' => 'Unrestricted Activity Management',
                'actions' => [
                    'activities.view' => 'View Global Institutional Activities',
                    'activities.view_approved_all' => 'View Approved Activities from All Colleges',
                    'activities.create' => 'Submit New Activities on Behalf of Colleges',
                    'activities.edit' => 'Edit Any Submitted Activity',
                    'activities.delete' => 'Delete Activity Submissions',
                ]
            ],
            'workflows' => [
                'label' => 'Enterprise Approval Workflow Engine',
                'actions' => [
                    'workflows.view' => 'View Approval Workflows & Stages',
                    'workflows.manage' => 'Create, Edit & Reorder Approval Stages',
                ]
            ],
            'approvals' => [
                'label' => 'Approval Queue Processing',
                'actions' => [
                    'approvals.view' => 'Access Verification & Approval Queue',
                    'approvals.approve' => 'Approve Submissions',
                    'approvals.reject' => 'Reject Submissions',
                    'approvals.discuss' => 'Return Submissions for Discussion',
                ]
            ],
            'media' => [
                'label' => 'Media Studio & Broadcasting',
                'actions' => [
                    'media.view' => 'Access Media Studio Dashboard',
                    'media.edit' => 'Edit Activity Media Packs & Watermarks',
                    'media.publish' => 'Publish & Broadcast across Social Channels',
                ]
            ],
            'analytics' => [
                'label' => 'Analytics & Reporting',
                'actions' => [
                    'analytics.view' => 'View University Performance Dashboards',
                    'analytics.export' => 'Export CSV & Printable Executive Reports',
                ]
            ],
            'settings' => [
                'label' => 'Master Settings & Dynamic Branding',
                'actions' => [
                    'settings.view' => 'View Portal Configuration & NTP Status',
                    'settings.edit' => 'Update Portal Logo, Avatar & NTP Settings',
                ]
            ],
            'roles' => [
                'label' => 'Role & Permission Management',
                'actions' => [
                    'roles.view' => 'View Dynamic Role Matrix',
                    'roles.manage' => 'Configure Roles & Assign Permissions',
                ]
            ],
            'audits' => [
                'label' => 'Audit Trails & System Logs',
                'actions' => [
                    'audits.view' => 'View Enterprise Security Audit Logs',
                    'emails.view' => 'View Email Transmission & Notification Logs',
                ]
            ],
        ];
    }

    public static function getDefaultPermissions(string $slug): array
    {
        return match ($slug) {
            'super_admin' => ['*'],
            'admin' => [
                'colleges.view', 'colleges.create', 'colleges.edit',
                'users.view', 'users.create', 'users.edit', 'users.reset_password', 'users.transfer',
                'activities.view', 'activities.create', 'activities.edit',
                'workflows.view', 'workflows.manage',
                'approvals.view',
                'analytics.view', 'analytics.export',
                'settings.view', 'settings.edit',
                'roles.view',
                'audits.view', 'emails.view'
            ],
            'editor' => ['activities.view', 'activities.edit', 'media.view', 'media.edit'],
            'office_registrar', 'registrar', 'vc', 'pro_vc' => ['approvals.view', 'approvals.approve', 'approvals.reject', 'approvals.discuss', 'activities.view', 'analytics.view'],
            'media_editor' => ['media.view', 'media.edit', 'media.publish', 'activities.view'],
            'college_admin' => ['activities.create', 'activities.view', 'activities.view_approved_all', 'activities.edit'],
            'viewer' => ['activities.view', 'analytics.view'],
            default => []
        };
    }
}
