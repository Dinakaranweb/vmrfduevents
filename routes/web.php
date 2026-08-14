<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivitySubmissionController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\MediaEditorController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SuperAdminEmailAndLogsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;

// Public Gallery
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Institutional Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Activity Submissions (Editable only once when sent back with 'discuss' status)
    Route::resource('submissions', ActivitySubmissionController::class)->only(['index', 'create', 'store', 'show', 'edit']);
    Route::match(['put', 'patch', 'post'], '/submissions/{submission}', [ActivitySubmissionController::class, 'update'])->name('submissions.update');
    Route::post('/submissions/{id}/discussions', [ActivitySubmissionController::class, 'storeDiscussion'])->name('submissions.discussions.store');
    Route::post('/api/ai-captions', [ActivitySubmissionController::class, 'generateAiCaption'])->name('api.ai.captions');

    // Approvals Queue & Processing (Verified by Registrar, VC, or Pro VC)
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('/approvals/pending', [ApprovalController::class, 'pending'])->name('approvals.pending');
    Route::get('/approvals/approved', [ApprovalController::class, 'approved'])->name('approvals.approved');
    Route::get('/approvals/rejected', [ApprovalController::class, 'rejected'])->name('approvals.rejected');
    Route::match(['get', 'post'], '/approvals/{id}/action', [ApprovalController::class, 'processAction'])->name('approvals.action');

    // Media Editor Desk (Forwarded automatically upon approval)
    Route::get('/media-editor', [MediaEditorController::class, 'dashboard'])->name('media.dashboard');
    Route::get('/media-editor/editing', [MediaEditorController::class, 'editingQueue'])->name('media.editing');
    Route::get('/media-editor/ready', [MediaEditorController::class, 'readyQueue'])->name('media.ready');
    Route::get('/media-editor/approved', [MediaEditorController::class, 'approved'])->name('media.approved');
    Route::match(['get', 'post'], '/media-editor/{id}/direct-approve', [MediaEditorController::class, 'approveDirectly'])->name('media.approveDirectly');
    Route::match(['get', 'post'], '/media-editor/{id}/submit-registrar', [MediaEditorController::class, 'submitToRegistrar'])->name('media.submitRegistrar');
    Route::match(['get', 'post'], '/media-editor/{id}/publish', [MediaEditorController::class, 'publish'])->name('media.publish');
    Route::get('/media-editor/{id}/download-pack', [MediaEditorController::class, 'downloadActivityPack'])->name('media.downloadPack');
    Route::get('/media-editor/export-queue', [MediaEditorController::class, 'downloadQueueCsv'])->name('media.exportQueue');


    // Reports & Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/export/csv', [AnalyticsController::class, 'exportCsv'])->name('analytics.export.csv');
    Route::get('/analytics/print', [AnalyticsController::class, 'printableReport'])->name('analytics.print');

    // Notification Center & Email Audit Log
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::get('/emails', [EmailLogController::class, 'index'])->name('emails.index');
    Route::get('/emails/{id}/preview', [EmailLogController::class, 'show'])->name('emails.show');

    // System Administration
    Route::get('/admin/colleges', [AdminController::class, 'colleges'])->name('admin.colleges');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');

    // Super Admin Control Panel Hub (Dynamic Enterprise Control)
    Route::prefix('super-admin')->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');
        
        // College Management CRUD
        Route::get('/colleges', [SuperAdminController::class, 'colleges'])->name('superadmin.colleges');
        Route::post('/colleges', [SuperAdminController::class, 'storeCollege'])->name('superadmin.colleges.store');
        Route::put('/colleges/{id}', [SuperAdminController::class, 'updateCollege'])->name('superadmin.colleges.update');
        Route::delete('/colleges/{id}', [SuperAdminController::class, 'deleteCollege'])->name('superadmin.colleges.delete');
        Route::post('/colleges/{id}/toggle', [SuperAdminController::class, 'toggleCollege'])->name('superadmin.colleges.toggle');

        // Event Categories Master CRUD
        Route::get('/categories', [SuperAdminController::class, 'categories'])->name('superadmin.categories');
        Route::post('/categories', [SuperAdminController::class, 'storeCategory'])->name('superadmin.categories.store');
        Route::put('/categories/{id}', [SuperAdminController::class, 'updateCategory'])->name('superadmin.categories.update');
        Route::delete('/categories/{id}', [SuperAdminController::class, 'deleteCategory'])->name('superadmin.categories.delete');
        Route::post('/categories/{id}/toggle', [SuperAdminController::class, 'toggleCategory'])->name('superadmin.categories.toggle');
        Route::post('/categories/reorder', [SuperAdminController::class, 'reorderCategories'])->name('superadmin.categories.reorder');

        // User Management CRUD
        Route::get('/users', [SuperAdminController::class, 'users'])->name('superadmin.users');
        Route::post('/users', [SuperAdminController::class, 'storeUser'])->name('superadmin.users.store');
        Route::put('/users/{id}', [SuperAdminController::class, 'updateUser'])->name('superadmin.users.update');
        Route::post('/users/{id}/reset-password', [SuperAdminController::class, 'resetPassword'])->name('superadmin.users.resetPassword');
        Route::post('/users/{id}/toggle-lock', [SuperAdminController::class, 'toggleLock'])->name('superadmin.users.toggleLock');
        Route::post('/users/{id}/change-status', [SuperAdminController::class, 'changeAccountStatus'])->name('superadmin.users.changeStatus');
        Route::post('/users/{id}/transfer', [SuperAdminController::class, 'transferUser'])->name('superadmin.users.transfer');

        // Enterprise Access Control Suite
        Route::get('/access-control', [SuperAdminController::class, 'accessControl'])->name('superadmin.accessControl');
        Route::post('/access-control/user-permissions/{id}', [SuperAdminController::class, 'updateUserPermissions'])->name('superadmin.accessControl.updateUser');
        Route::post('/access-control/copy-role', [SuperAdminController::class, 'copyRolePermissions'])->name('superadmin.accessControl.copyRole');

        // Role & Permission Management
        Route::get('/roles', [SuperAdminController::class, 'roles'])->name('superadmin.roles');
        Route::post('/roles', [SuperAdminController::class, 'storeRole'])->name('superadmin.roles.store');
        Route::put('/roles/{id}', [SuperAdminController::class, 'updateRole'])->name('superadmin.roles.update');
        Route::delete('/roles/{id}', [SuperAdminController::class, 'deleteRole'])->name('superadmin.roles.delete');

        // Unrestricted Institutional Activity Management
        Route::get('/activities', [SuperAdminController::class, 'activities'])->name('superadmin.activities');
        Route::post('/activities/{id}/override', [SuperAdminController::class, 'overrideStatus'])->name('superadmin.activities.override');
        Route::post('/activities/{id}/reassign', [SuperAdminController::class, 'reassignOwner'])->name('superadmin.activities.reassign');
        Route::post('/activities/{id}/force-resubmit', [SuperAdminController::class, 'forceResubmit'])->name('superadmin.activities.forceResubmit');
        Route::post('/activities/{id}/restore', [SuperAdminController::class, 'restoreActivity'])->name('superadmin.activities.restore');
        Route::delete('/activities/{id}/delete', [SuperAdminController::class, 'forceDeleteActivity'])->name('superadmin.activities.delete');

        // Approval Workflow & Status Management
        Route::get('/workflows', [SuperAdminController::class, 'workflows'])->name('superadmin.workflows');
        Route::post('/workflows/master', [SuperAdminController::class, 'storeWorkflowMaster'])->name('superadmin.workflows.master.store');
        Route::put('/workflows/master/{id}', [SuperAdminController::class, 'updateWorkflowMaster'])->name('superadmin.workflows.master.update');
        Route::delete('/workflows/master/{id}', [SuperAdminController::class, 'deleteWorkflowMaster'])->name('superadmin.workflows.master.delete');
        Route::post('/workflows/master/{id}/duplicate', [SuperAdminController::class, 'duplicateWorkflowMaster'])->name('superadmin.workflows.master.duplicate');
        Route::post('/workflows/master/{id}/default', [SuperAdminController::class, 'setDefaultWorkflowMaster'])->name('superadmin.workflows.master.default');

        // Stage Management
        Route::post('/workflows/stages', [SuperAdminController::class, 'storeWorkflowStage'])->name('superadmin.workflows.store');
        Route::put('/workflows/stages/{id}', [SuperAdminController::class, 'updateWorkflowStage'])->name('superadmin.workflows.update');
        Route::delete('/workflows/stages/{id}', [SuperAdminController::class, 'deleteWorkflowStage'])->name('superadmin.workflows.delete');
        Route::post('/workflows/stages/reorder', [SuperAdminController::class, 'reorderWorkflowStages'])->name('superadmin.workflows.reorder');
        Route::post('/workflows/stages/sync-visual', [SuperAdminController::class, 'syncVisualWorkflow'])->name('superadmin.workflows.syncVisual');
        Route::post('/workflows/seed-example', [SuperAdminController::class, 'seedExampleWorkflow'])->name('superadmin.workflows.seedExample');

        // Assignment Rules
        Route::post('/workflows/rules', [SuperAdminController::class, 'storeWorkflowRule'])->name('superadmin.workflows.rules.store');
        Route::delete('/workflows/rules/{id}', [SuperAdminController::class, 'deleteWorkflowRule'])->name('superadmin.workflows.rules.delete');

        Route::post('/statuses', [SuperAdminController::class, 'storeStatus'])->name('superadmin.statuses.store');

        // Data Mapping Matrix
        Route::get('/mappings', [SuperAdminController::class, 'mappings'])->name('superadmin.mappings');
        Route::post('/mappings', [SuperAdminController::class, 'updateMapping'])->name('superadmin.mappings.update');

        // Comprehensive Logs Management Hub
        Route::get('/logs-hub', [SuperAdminEmailAndLogsController::class, 'logsIndex'])->name('superadmin.logsHub');
        Route::get('/logs-hub/system-file', [SuperAdminEmailAndLogsController::class, 'getSystemLogContent'])->name('superadmin.logsHub.systemFile');
        Route::get('/logs-hub/email-preview/{id}', [SuperAdminEmailAndLogsController::class, 'previewEmail'])->name('superadmin.logsHub.emailPreview');

        // Dynamic Email Management Hub
        Route::get('/email-hub', [SuperAdminEmailAndLogsController::class, 'emailHubIndex'])->name('superadmin.emailHub');
        Route::post('/email-hub/branding', [SuperAdminEmailAndLogsController::class, 'updateBranding'])->name('superadmin.emailHub.branding');
        Route::post('/email-hub/templates', [SuperAdminEmailAndLogsController::class, 'storeTemplate'])->name('superadmin.emailHub.templates.store');
        Route::put('/email-hub/templates/{id}', [SuperAdminEmailAndLogsController::class, 'updateTemplate'])->name('superadmin.emailHub.templates.update');
        Route::delete('/email-hub/templates/{id}', [SuperAdminEmailAndLogsController::class, 'deleteTemplate'])->name('superadmin.emailHub.templates.delete');
        Route::get('/email-hub/templates/{id}/preview', [SuperAdminEmailAndLogsController::class, 'previewTemplate'])->name('superadmin.emailHub.templates.preview');
        Route::post('/email-hub/test-delivery', [SuperAdminEmailAndLogsController::class, 'sendTestEmail'])->name('superadmin.emailHub.test');

        // Notification Manager (Legacy redirect / alias to new Email Hub)
        Route::get('/notifications', function () {
            return redirect()->route('superadmin.emailHub', ['tab' => 'templates']);
        })->name('superadmin.notifications');
        Route::put('/notifications/{id}', [SuperAdminController::class, 'updateTemplate'])->name('superadmin.notifications.update');

        // Immutable Audit Trail (Legacy redirect / alias to new Logs Hub)
        Route::get('/audit-logs', function () {
            return redirect()->route('superadmin.logsHub', ['tab' => 'user_activity']);
        })->name('superadmin.auditLogs');

        // Multi-dimensional Reports & Export
        Route::get('/reports', [SuperAdminController::class, 'reports'])->name('superadmin.reports');
        Route::get('/reports/export', [SuperAdminController::class, 'exportReports'])->name('superadmin.reports.export');

        // Dynamic Master Settings
        Route::get('/settings', [SuperAdminController::class, 'masterSettings'])->name('superadmin.settings');
        Route::post('/settings', [SuperAdminController::class, 'updateSetting'])->name('superadmin.settings.update');
        Route::post('/settings/branding', [SuperAdminController::class, 'updateBrandingSettings'])->name('superadmin.settings.branding');
        Route::get('/settings/login', [SuperAdminController::class, 'loginSettings'])->name('superadmin.settings.login');
        Route::post('/settings/login', [SuperAdminController::class, 'updateLoginSettings'])->name('superadmin.settings.login.update');
        Route::post('/settings/ntp', [SuperAdminController::class, 'updateNtpSettings'])->name('superadmin.settings.ntp');
        Route::post('/settings/sidebar-icons', [SuperAdminController::class, 'updateSidebarIcons'])->name('superadmin.settings.sidebar_icons');
    });

    // User Profile & Security Self-Service
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});
