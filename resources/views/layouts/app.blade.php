<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VMRFDU Enterprise Portal')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom Portal Aesthetic CSS -->
    <link rel="stylesheet" href="{{ asset('css/portal-custom.css') }}?v={{ file_exists(public_path('css/portal-custom.css')) ? filemtime(public_path('css/portal-custom.css')) : time() }}">

    <!-- Dynamic Super Admin Theme Variables & Card Styling -->
    <style>
        :root {
            --portal-primary: {{ !empty($globalPrimaryColor) ? $globalPrimaryColor : '#1e293b' }} !important;
            --portal-accent: {{ !empty($globalAccentColor) ? $globalAccentColor : '#2563eb' }} !important;
            --radius-md: {{ !empty($globalCardRadius) ? $globalCardRadius : '16px' }} !important;
            --portal-border: {{ !empty($globalCardBorder) ? $globalCardBorder : '#e2e8f0' }} !important;
        }
        .sidebar { background: var(--portal-primary) !important; }
        .sidebar-link.active { background: var(--portal-accent) !important; }
        .btn-portal-primary { background: var(--portal-accent) !important; }
        .text-primary { color: var(--portal-accent) !important; }
        .border-primary { border-color: var(--portal-accent) !important; }
        .portal-card { border-radius: var(--radius-md) !important; border-color: var(--portal-border) !important; }
        .nav-accordion-icon { transition: transform 0.3s ease; font-size: 0.85rem; }
        .nav-section-title[aria-expanded="true"] .nav-accordion-icon { transform: rotate(180deg); }
    </style>
</head>
<body class="portal-body">

<!-- Sidebar Backdrop Overlay for Mobile & Tablet -->
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

<div class="app-wrapper">
    <!-- Sidebar Navigation (Black, White & Blue Executive Theme) -->
    <aside class="sidebar" id="portalSidebar">
        <script>
            if (window.innerWidth >= 1200 && localStorage.getItem('portal_sidebar_collapsed') === 'true') {
                document.getElementById('portalSidebar').classList.add('collapsed');
            }
        </script>
        <!-- Sidebar Brand Header -->
        <div class="sidebar-header d-flex align-items-center justify-content-between">
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-3 text-decoration-none text-white overflow-hidden">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow flex-shrink-0" style="width: 42px; height: 42px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important; overflow: hidden;">
                    @if(!empty($globalPortalLogo))
                        <img src="{{ upload_url($globalPortalLogo) }}" alt="Logo" class="w-100 h-100" style="object-fit: cover;">
                    @else
                        <i class="bi bi-bank2 fs-5"></i>
                    @endif
                </div>
                <div class="text-truncate">
                    <span class="fw-bold fs-5 tracking-tight d-block lh-1 text-white font-outfit text-truncate">{{ $globalPortalName ?? 'VMRFDU' }}</span>
                    <span class="text-xs text-white opacity-75 d-block mt-1 text-truncate">{{ $globalPortalTagline ?? 'Activity Portal' }}</span>
                </div>
            </a>
            <button class="btn btn-sm btn-link text-white d-xl-none p-0 flex-shrink-0" onclick="toggleSidebar()" title="Close Sidebar">
                <i class="bi bi-x-lg fs-5"></i>
            </button>
        </div>

        <!-- Sidebar Menu Items strictly controlled by logged-in User Role -->
        <!-- Dynamic Sidebar Menu Items strictly governed by Super Admin Permission Grants -->
        <div class="sidebar-menu" id="sidebarMenuContainer">
            @auth
                @php
                    $user = auth()->user();
                @endphp

                @php
                    $sidebarIcons = json_decode(\App\Models\SystemSetting::getValue('sidebar_icons', '{}'), true);
                @endphp

                <!-- 1. CORE EXECUTIVE DASHBOARD -->
                <div class="nav-section-title"><i class="bi {{ $sidebarIcons['group_nav']['icon'] ?? 'bi-compass' }} me-2" style="color: {{ $sidebarIcons['group_nav']['color'] ?? '#0B3B60' }};"></i> {{ $sidebarIcons['group_nav']['label'] ?? 'Navigation Hub' }}</div>
                @if($user->isSuperAdmin())
                    <a href="{{ route('superadmin.dashboard') }}" class="sidebar-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                        <i class="bi {{ $sidebarIcons['superadmin_dashboard']['icon'] ?? 'bi-grid-1x2-fill' }}" style="color: {{ $sidebarIcons['superadmin_dashboard']['color'] ?? '#C9A050' }}"></i>
                        <span>{{ $sidebarIcons['superadmin_dashboard']['label'] ?? 'Root System Dashboard' }}</span>
                    </a>
                @elseif($user->hasRole('media_editor'))
                    <a href="{{ route('media.dashboard') }}" class="sidebar-link {{ request()->routeIs('media.dashboard') ? 'active' : '' }}">
                        <i class="bi {{ $sidebarIcons['media_dashboard']['icon'] ?? 'bi-camera-reels-fill' }}" style="color: {{ $sidebarIcons['media_dashboard']['color'] ?? '#0B3B60' }}"></i>
                        <span>{{ $sidebarIcons['media_dashboard']['label'] ?? 'Media Studio Dashboard' }}</span>
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi {{ $sidebarIcons['user_dashboard']['icon'] ?? 'bi-house-door-fill' }}" style="color: {{ $sidebarIcons['user_dashboard']['color'] ?? '#0B3B60' }}"></i>
                        <span>{{ $sidebarIcons['user_dashboard']['label'] ?? 'My Dashboard' }}</span>
                    </a>
                @endif

                <!-- 2. INSTITUTIONAL ACTIVITY SUBMISSIONS MODULE -->
                @if($user->isSuperAdmin() || $user->hasPermission('activities.view') || $user->hasPermission('activities.create') || in_array($user->role, ['college_admin', 'user']))
                    @php $isActivityActive = request()->routeIs('submissions.*'); @endphp
                    <div class="nav-item mb-1">
                        <a href="#collapseActivity" class="nav-section-title d-flex justify-content-between align-items-center text-decoration-none w-100 mt-3" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isActivityActive ? 'true' : 'false' }}" aria-controls="collapseActivity">
                            <span><i class="bi {{ $sidebarIcons['group_activity']['icon'] ?? 'bi-journal-plus' }} me-2" style="color: {{ $sidebarIcons['group_activity']['color'] ?? '#15803d' }};"></i> {{ $sidebarIcons['group_activity']['label'] ?? 'Activity Capture Desk' }}</span>
                            <i class="bi bi-chevron-down nav-accordion-icon"></i>
                        </a>
                        <div class="collapse {{ $isActivityActive ? 'show' : '' }}" id="collapseActivity">
                            <div class="nav-submenu">
                                @if($user->isSuperAdmin() || $user->hasPermission('activities.create') || in_array($user->role, ['college_admin', 'user']))
                                    <a href="{{ route('submissions.create') }}" class="sidebar-link {{ request()->routeIs('submissions.create') ? 'active' : '' }}">
                                        <i class="bi {{ $sidebarIcons['submissions_create']['icon'] ?? 'bi-pencil-square' }}" style="color: {{ $sidebarIcons['submissions_create']['color'] ?? '#15803d' }}"></i>
                                        <span>{{ $sidebarIcons['submissions_create']['label'] ?? 'Submit New Activity' }}</span>
                                    </a>
                                @endif
                                @if($user->isSuperAdmin() || $user->hasPermission('activities.view') || in_array($user->role, ['college_admin', 'user']))
                                    <a href="{{ route('submissions.index') }}" class="sidebar-link {{ request()->routeIs('submissions.index') ? 'active' : '' }}">
                                        <i class="bi {{ $sidebarIcons['submissions_index']['icon'] ?? 'bi-archive-fill' }}" style="color: {{ $sidebarIcons['submissions_index']['color'] ?? '#C9A050' }}"></i>
                                        <span>{{ $sidebarIcons['submissions_index']['label'] ?? 'Submission History' }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 3. APPROVAL AUTHORITY & VERIFICATION MODULE -->
                @if($user->isSuperAdmin() || $user->hasPermission('approvals.view') || $user->hasPermission('approvals.approve') || $user->hasPermission('approvals.reject') || $user->hasPermission('approvals.discuss') || in_array($user->role, ['registrar', 'office_registrar', 'vc', 'pro_vc']))
                    @php $isApprovalActive = request()->routeIs('approvals.*'); @endphp
                    <div class="nav-item mb-1">
                        <a href="#collapseApproval" class="nav-section-title d-flex justify-content-between align-items-center text-decoration-none w-100 mt-3" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isApprovalActive ? 'true' : 'false' }}" aria-controls="collapseApproval">
                            <span><i class="bi {{ $sidebarIcons['group_approval']['icon'] ?? 'bi-check2-square' }} me-2" style="color: {{ $sidebarIcons['group_approval']['color'] ?? '#C9A050' }};"></i> {{ $sidebarIcons['group_approval']['label'] ?? 'Approval Authority Desk' }}</span>
                            <i class="bi bi-chevron-down nav-accordion-icon"></i>
                        </a>
                        <div class="collapse {{ $isApprovalActive ? 'show' : '' }}" id="collapseApproval">
                            <div class="nav-submenu">
                                <a href="{{ route('approvals.pending') }}" class="sidebar-link {{ request()->routeIs('approvals.pending') || (request()->routeIs('approvals.index') && request()->input('status', 'pending') == 'pending') ? 'active' : '' }}">
                                    <i class="bi {{ $sidebarIcons['approvals_pending']['icon'] ?? 'bi-hourglass-split' }}" style="color: {{ $sidebarIcons['approvals_pending']['color'] ?? '#C9A050' }}"></i>
                                    <span>{{ $user->role === 'office_registrar' ? ($sidebarIcons['approvals_pending_registrar']['label'] ?? 'Final Media Approval') : ($sidebarIcons['approvals_pending']['label'] ?? 'Pending Verification Queue') }}</span>
                                    @php
                                        if ($user->role === 'office_registrar') {
                                            $pendingQueryStatuses = ['registrar_approval'];
                                        } elseif ($user->isSuperAdmin()) {
                                            $pendingQueryStatuses = ['verification_pending', 'approval_pending', 'registrar_approval'];
                                        } else {
                                            $pendingQueryStatuses = ['verification_pending', 'approval_pending'];
                                        }
                                        $pendingCount = \App\Models\ActivitySubmission::whereIn('status', $pendingQueryStatuses)->count();
                                    @endphp
                                    @if($pendingCount > 0)
                                        <span class="badge bg-warning text-dark ms-auto rounded-pill fw-bold">{{ $pendingCount }}</span>
                                    @endif
                                </a>
                                <a href="{{ route('approvals.approved') }}" class="sidebar-link {{ request()->routeIs('approvals.approved') || request()->input('status') == 'approved' ? 'active' : '' }}">
                                    <i class="bi {{ $sidebarIcons['approvals_approved']['icon'] ?? 'bi-check2-all' }}" style="color: {{ $sidebarIcons['approvals_approved']['color'] ?? '#15803d' }}"></i>
                                    <span>{{ $sidebarIcons['approvals_approved']['label'] ?? 'Approved Activities' }}</span>
                                </a>
                                <a href="{{ route('approvals.rejected') }}" class="sidebar-link {{ request()->routeIs('approvals.rejected') || request()->input('status') == 'rejected' ? 'active' : '' }}">
                                    <i class="bi {{ $sidebarIcons['approvals_rejected']['icon'] ?? 'bi-x-octagon-fill' }}" style="color: {{ $sidebarIcons['approvals_rejected']['color'] ?? '#be123c' }}"></i>
                                    <span>{{ $sidebarIcons['approvals_rejected']['label'] ?? 'Rejected Archive' }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 4. MEDIA BROADCASTING DESK MODULE -->
                @if($user->isSuperAdmin() || $user->hasPermission('media.view') || $user->hasPermission('media.edit') || $user->hasPermission('media.publish') || $user->role === 'media_editor')
                    @php $isMediaActive = request()->routeIs('media.*'); @endphp
                    <div class="nav-item mb-1">
                        <a href="#collapseMedia" class="nav-section-title d-flex justify-content-between align-items-center text-decoration-none w-100 mt-3" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isMediaActive ? 'true' : 'false' }}" aria-controls="collapseMedia">
                            <span><i class="bi {{ $sidebarIcons['group_media']['icon'] ?? 'bi-camera-reels' }} me-2" style="color: {{ $sidebarIcons['group_media']['color'] ?? '#0f766e' }};"></i> {{ $sidebarIcons['group_media']['label'] ?? 'Media Studio & Broadcast' }}</span>
                            <i class="bi bi-chevron-down nav-accordion-icon"></i>
                        </a>
                        <div class="collapse {{ $isMediaActive ? 'show' : '' }}" id="collapseMedia">
                            <div class="nav-submenu">
                                <a href="{{ route('media.dashboard') }}" class="sidebar-link {{ request()->routeIs('media.dashboard') && request()->input('tab', 'overview') === 'overview' ? 'active' : '' }}">
                                    <i class="bi {{ $sidebarIcons['media_overview']['icon'] ?? 'bi-display' }}" style="color: {{ $sidebarIcons['media_overview']['color'] ?? '#0B3B60' }}"></i>
                                    <span>{{ $sidebarIcons['media_overview']['label'] ?? 'Media Desk Overview' }}</span>
                                </a>
                                @if($user->isSuperAdmin() || $user->hasPermission('media.edit') || $user->role === 'media_editor')
                                    <a href="{{ route('media.editing') }}" class="sidebar-link {{ request()->routeIs('media.editing') || request()->input('tab') === 'editing' ? 'active' : '' }}">
                                        <i class="bi {{ $sidebarIcons['media_editing']['icon'] ?? 'bi-images' }}" style="color: {{ $sidebarIcons['media_editing']['color'] ?? '#2563eb' }}"></i>
                                        <span>{{ $sidebarIcons['media_editing']['label'] ?? 'Stage 1: Photo Editing' }}</span>
                                        @php
                                            $editingCount = \App\Models\ActivitySubmission::whereIn('status', ['media_review', 'editor_discuss'])->count();
                                        @endphp
                                        @if($editingCount > 0)
                                            <span class="badge bg-primary ms-auto rounded-pill fw-bold">{{ $editingCount }}</span>
                                        @endif
                                    </a>
                                @endif
                                @if($user->isSuperAdmin() || $user->hasPermission('media.publish') || $user->role === 'media_editor')
                                    <a href="{{ route('media.ready') }}" class="sidebar-link {{ request()->routeIs('media.ready') || request()->input('tab') === 'ready' ? 'active' : '' }}">
                                        <i class="bi {{ $sidebarIcons['media_ready']['icon'] ?? 'bi-cloud-arrow-up-fill' }}" style="color: {{ $sidebarIcons['media_ready']['color'] ?? '#15803d' }}"></i>
                                        <span>{{ $sidebarIcons['media_ready']['label'] ?? 'Stage 2: Ready to Post' }}</span>
                                        @php
                                            $readyQueueCount = \App\Models\ActivitySubmission::where('status', 'ready_to_publish')->count();
                                        @endphp
                                        @if($readyQueueCount > 0)
                                            <span class="badge bg-success ms-auto rounded-pill fw-bold">{{ $readyQueueCount }}</span>
                                        @endif
                                    </a>
                                @endif
                                <a href="{{ route('media.approved') }}" class="sidebar-link {{ request()->routeIs('media.approved') || request()->input('tab') === 'published' ? 'active' : '' }}">
                                    <i class="bi {{ $sidebarIcons['media_published']['icon'] ?? 'bi-collection-play-fill' }}" style="color: {{ $sidebarIcons['media_published']['color'] ?? '#2563eb' }}"></i>
                                    <span>{{ $sidebarIcons['media_published']['label'] ?? 'Published Archive' }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 5. ANALYTICS & REPORTS MODULE -->
                @if($user->isSuperAdmin() || $user->hasPermission('analytics.view') || $user->hasPermission('analytics.export') || in_array($user->role, ['registrar', 'office_registrar', 'vc', 'pro_vc']))
                    @php $isAnalyticsActive = request()->routeIs('analytics.*'); @endphp
                    <div class="nav-item mb-1">
                        <a href="#collapseAnalytics" class="nav-section-title d-flex justify-content-between align-items-center text-decoration-none w-100 mt-3" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isAnalyticsActive ? 'true' : 'false' }}" aria-controls="collapseAnalytics">
                            <span><i class="bi {{ $sidebarIcons['group_analytics']['icon'] ?? 'bi-graph-up' }} me-2" style="color: {{ $sidebarIcons['group_analytics']['color'] ?? '#2563eb' }};"></i> {{ $sidebarIcons['group_analytics']['label'] ?? 'Analytics & Intelligence' }}</span>
                            <i class="bi bi-chevron-down nav-accordion-icon"></i>
                        </a>
                        <div class="collapse {{ $isAnalyticsActive ? 'show' : '' }}" id="collapseAnalytics">
                            <div class="nav-submenu">
                                <a href="{{ route('analytics.index') }}" class="sidebar-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                                    <i class="bi {{ $sidebarIcons['analytics_index']['icon'] ?? 'bi-graph-up-arrow' }}" style="color: {{ $sidebarIcons['analytics_index']['color'] ?? '#0f766e' }}"></i>
                                    <span>{{ $sidebarIcons['analytics_index']['label'] ?? 'Analytics & Reports Hub' }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 6. INSTITUTIONAL ADMINISTRATION MODULE -->
                @if($user->isSuperAdmin() || $user->hasPermission('colleges.view') || $user->hasPermission('users.view'))
                    @php $isAdminActive = request()->routeIs('superadmin.colleges') || request()->routeIs('admin.colleges') || request()->routeIs('superadmin.categories') || request()->routeIs('superadmin.users') || request()->routeIs('admin.users'); @endphp
                    <div class="nav-item mb-1">
                        <a href="#collapseAdmin" class="nav-section-title d-flex justify-content-between align-items-center text-decoration-none w-100 mt-3" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isAdminActive ? 'true' : 'false' }}" aria-controls="collapseAdmin">
                            <span><i class="bi {{ $sidebarIcons['group_admin']['icon'] ?? 'bi-building-gear' }} me-2" style="color: {{ $sidebarIcons['group_admin']['color'] ?? '#475569' }};"></i> {{ $sidebarIcons['group_admin']['label'] ?? 'Administration Desk' }}</span>
                            <i class="bi bi-chevron-down nav-accordion-icon"></i>
                        </a>
                        <div class="collapse {{ $isAdminActive ? 'show' : '' }}" id="collapseAdmin">
                            <div class="nav-submenu">
                                @if($user->isSuperAdmin() || $user->hasPermission('colleges.view'))
                                    <a href="{{ $user->isSuperAdmin() ? route('superadmin.colleges') : route('admin.colleges') }}" class="sidebar-link {{ request()->routeIs('superadmin.colleges') || request()->routeIs('admin.colleges') ? 'active' : '' }}">
                                        <i class="bi {{ $sidebarIcons['admin_colleges']['icon'] ?? 'bi-bank2' }}" style="color: {{ $sidebarIcons['admin_colleges']['color'] ?? '#475569' }}"></i>
                                        <span>{{ $sidebarIcons['admin_colleges']['label'] ?? 'Colleges Management' }}</span>
                                    </a>
                                @endif
                                @if($user->isSuperAdmin())
                                    <a href="{{ route('superadmin.categories') }}" class="sidebar-link {{ request()->routeIs('superadmin.categories') ? 'active' : '' }}">
                                        <i class="bi {{ $sidebarIcons['admin_categories']['icon'] ?? 'bi-bookmark-star-fill' }}" style="color: {{ $sidebarIcons['admin_categories']['color'] ?? '#C9A050' }}"></i>
                                        <span>{{ $sidebarIcons['admin_categories']['label'] ?? 'Event Categories Master' }}</span>
                                    </a>
                                @endif
                                @if($user->isSuperAdmin() || $user->hasPermission('users.view'))
                                    <a href="{{ $user->isSuperAdmin() ? route('superadmin.users') : route('admin.users') }}" class="sidebar-link {{ request()->routeIs('superadmin.users') || request()->routeIs('admin.users') ? 'active' : '' }}">
                                        <i class="bi {{ $sidebarIcons['admin_users']['icon'] ?? 'bi-person-lines-fill' }}" style="color: {{ $sidebarIcons['admin_users']['color'] ?? '#0B3B60' }}"></i>
                                        <span>{{ $sidebarIcons['admin_users']['label'] ?? 'Users & Roles Matrix' }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 7. SUPER ADMIN & GOVERNANCE CONTROL PANEL -->
                @if($user->isSuperAdmin() || $user->hasPermission('roles.view') || $user->hasPermission('workflows.view') || $user->hasPermission('settings.view') || $user->hasPermission('audits.view'))
                    @php $isControlActive = request()->routeIs('superadmin.accessControl') || request()->routeIs('superadmin.roles') || request()->routeIs('superadmin.workflows') || request()->routeIs('superadmin.activities') || request()->routeIs('superadmin.logsHub*') || request()->routeIs('superadmin.auditLogs*') || request()->routeIs('superadmin.emailHub*') || request()->routeIs('superadmin.notifications*') || request()->routeIs('superadmin.settings') || request()->routeIs('superadmin.settings.login'); @endphp
                    <div class="nav-item mb-1">
                        <a href="#collapseControl" class="nav-section-title d-flex justify-content-between align-items-center text-decoration-none w-100 mt-3" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isControlActive ? 'true' : 'false' }}" aria-controls="collapseControl">
                            <span><i class="bi {{ $sidebarIcons['group_control']['icon'] ?? 'bi-shield-lock-fill' }} me-2" style="color: {{ $sidebarIcons['group_control']['color'] ?? '#be123c' }};"></i> {{ $sidebarIcons['group_control']['label'] ?? 'Control Panel & Settings' }}</span>
                            <i class="bi bi-chevron-down nav-accordion-icon"></i>
                        </a>
                        <div class="collapse {{ $isControlActive ? 'show' : '' }}" id="collapseControl">
                            <div class="nav-submenu">
                                @if($user->isSuperAdmin() || $user->hasPermission('roles.view'))
                                    <a href="{{ route('superadmin.accessControl') }}" class="sidebar-link {{ request()->routeIs('superadmin.accessControl') || request()->routeIs('superadmin.roles') ? 'active' : '' }}">
                                        <i class="bi {{ $sidebarIcons['control_access']['icon'] ?? 'bi-shield-check' }}" style="color: {{ $sidebarIcons['control_access']['color'] ?? '#15803d' }}"></i>
                                        <span>{{ $sidebarIcons['control_access']['label'] ?? 'Access Control Hub' }}</span>
                                    </a>
                                @endif
                                @if($user->isSuperAdmin() || $user->hasPermission('workflows.view'))
                                    <a href="{{ route('superadmin.workflows') }}" class="sidebar-link {{ request()->routeIs('superadmin.workflows') ? 'active' : '' }}">
                                        <i class="bi {{ $sidebarIcons['control_workflows']['icon'] ?? 'bi-bezier2' }}" style="color: {{ $sidebarIcons['control_workflows']['color'] ?? '#2563eb' }}"></i>
                                        <span>{{ $sidebarIcons['control_workflows']['label'] ?? 'Enterprise Approval Workflow Engine' }}</span>
                                    </a>
                                @endif
                                @if($user->isSuperAdmin())
                                    <a href="{{ route('superadmin.activities') }}" class="sidebar-link {{ request()->routeIs('superadmin.activities') ? 'active' : '' }}">
                                        <i class="bi {{ $sidebarIcons['control_activities']['icon'] ?? 'bi-tools' }}" style="color: {{ $sidebarIcons['control_activities']['color'] ?? '#be123c' }}"></i>
                                        <span>{{ $sidebarIcons['control_activities']['label'] ?? 'Activity Override Manager' }}</span>
                                    </a>
                                @endif
                                @if($user->isSuperAdmin() || $user->hasPermission('audits.view'))
                                    <a href="{{ route('superadmin.logsHub') }}" class="sidebar-link {{ request()->routeIs('superadmin.logsHub*') || request()->routeIs('superadmin.auditLogs*') ? 'active' : '' }}">
                                        <i class="bi {{ $sidebarIcons['control_logs']['icon'] ?? 'bi-clipboard-data-fill' }}" style="color: {{ $sidebarIcons['control_logs']['color'] ?? '#0f766e' }}"></i>
                                        <span>{{ $sidebarIcons['control_logs']['label'] ?? 'System & Email Logs Hub' }}</span>
                                    </a>
                                @endif
                                @if($user->isSuperAdmin())
                                    <a href="{{ route('superadmin.emailHub') }}" class="sidebar-link {{ request()->routeIs('superadmin.emailHub*') || request()->routeIs('superadmin.notifications*') ? 'active' : '' }}">
                                        <i class="bi {{ $sidebarIcons['control_email']['icon'] ?? 'bi-envelope-paper-fill' }}" style="color: {{ $sidebarIcons['control_email']['color'] ?? '#2563eb' }}"></i>
                                        <span>{{ $sidebarIcons['control_email']['label'] ?? 'Dynamic Email Engine' }}</span>
                                    </a>
                                @endif
                                @if($user->isSuperAdmin() || $user->hasPermission('settings.view'))
                                    <a href="{{ route('superadmin.settings') }}" class="sidebar-link {{ request()->routeIs('superadmin.settings') ? 'active' : '' }}">
                                        <i class="bi {{ $sidebarIcons['control_settings']['icon'] ?? 'bi-ui-radios' }}" style="color: {{ $sidebarIcons['control_settings']['color'] ?? '#C9A050' }}"></i>
                                        <span>{{ $sidebarIcons['control_settings']['label'] ?? 'Master Settings' }}</span>
                                    </a>
                                    <a href="{{ route('superadmin.settings.login') }}" class="sidebar-link {{ request()->routeIs('superadmin.settings.login') ? 'active' : '' }}">
                                        <i class="bi bi-box-arrow-in-right" style="color: #6366f1"></i>
                                        <span>Login Customization</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 8. MY PROFILE & ACCOUNT -->
                <div class="nav-section-title mt-3"><i class="bi {{ $sidebarIcons['group_account']['icon'] ?? 'bi-person-gear' }} me-2" style="color: {{ $sidebarIcons['group_account']['color'] ?? '#475569' }};"></i> {{ $sidebarIcons['group_account']['label'] ?? 'Account settings' }}</div>
                <a href="{{ route('profile') }}" class="sidebar-link {{ request()->routeIs('profile') ? 'active' : '' }}">
                    <i class="bi {{ $sidebarIcons['profile']['icon'] ?? 'bi-person-circle' }}" style="color: {{ $sidebarIcons['profile']['color'] ?? '#475569' }}"></i>
                    <span>{{ $sidebarIcons['profile']['label'] ?? 'My Profile' }}</span>
                </a>
                
                <!-- SUPPORT HELPLINE -->
                <div class="nav-section-title mt-3"><i class="bi bi-info-circle me-2 text-primary"></i> Support & Help</div>
                <a href="tel:{{ !empty($globalHelplineNumber) ? $globalHelplineNumber : '1800-VMRF-HELP' }}" class="sidebar-link">
                    <i class="bi bi-headset text-primary"></i>
                    <span>{{ !empty($globalHelplineNumber) ? $globalHelplineNumber : '1800-VMRF-HELP' }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="sidebar-link active">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Sign In</span>
                </a>
            @endauth
        </div>
        <script>
            // Instant Scroll Restoration before DOM paint to prevent jump/flicker
            (function() {
                const el = document.getElementById('sidebarMenuContainer');
                if (el) {
                    const savedScroll = sessionStorage.getItem('portal_sidebar_scroll_top');
                    if (savedScroll !== null) {
                        el.scrollTop = parseInt(savedScroll, 10);
                    }
                }
            })();
        </script>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            @auth
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    @php
                        $sideAvatar = auth()->user()->avatar ?: (!empty($globalDefaultAvatar) ? $globalDefaultAvatar : null);
                    @endphp
                    @if($sideAvatar)
                        <img src="{{ upload_url($sideAvatar) }}" alt="Avatar" class="rounded-circle border border-secondary flex-shrink-0 shadow-sm" style="width: 36px; height: 36px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="bg-primary text-white rounded-circle align-items-center justify-content-center fw-bold text-xs flex-shrink-0 shadow-sm" style="width: 36px; height: 36px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important; display: none;">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @else
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold text-xs flex-shrink-0 shadow-sm" style="width: 36px; height: 36px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="text-truncate">
                        <div class="fw-bold text-xs text-white text-truncate">{{ auth()->user()->name }}</div>
                        <div class="text-xxs text-info text-truncate">{{ auth()->user()->role_title }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm text-danger p-1 border-0" title="Sign Out">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="main-content-wrapper">
        <!-- Top Sticky Header -->
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light border px-2 py-1 rounded shadow-sm text-dark d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;" type="button" onclick="toggleSidebar()" title="Toggle Sidebar Navigation">
                    <i class="bi bi-layout-sidebar fs-5"></i>
                </button>
                <div>
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        @if(request()->routeIs('dashboard')) <i class="bi bi-speedometer2 text-primary"></i> Institutional Portal Dashboard
                        @elseif(request()->routeIs('submissions.create')) <i class="bi bi-plus-circle-fill text-primary"></i> Submit New Activity
                        @elseif(request()->routeIs('submissions.*')) <i class="bi bi-folder2-open text-primary"></i> Activity Submissions & Tracking
                        @elseif(request()->routeIs('approvals.*')) <i class="bi bi-shield-check text-primary"></i> Officials Verification Desk
                        @elseif(request()->routeIs('media.*')) <i class="bi bi-broadcast text-primary"></i> Media Broadcasting Desk
                        @elseif(request()->routeIs('analytics.*')) <i class="bi bi-bar-chart-line-fill text-primary"></i> Institutional Analytics
                        @else <i class="bi bi-grid text-primary"></i> {{ !empty($globalHeaderTitle) ? $globalHeaderTitle : 'Institutional Activity Verification Desk' }} @endif
                    </h5>
                </div>
            </div>

            <!-- Right Controls -->
            <div class="d-flex align-items-center gap-3">
                <!-- Live Clock Display -->
                <div class="d-none d-md-flex align-items-center gap-2 bg-light px-3 py-1 rounded-pill border text-muted small">
                    <i class="bi bi-clock-fill text-primary"></i>
                    <span id="liveClock" class="fw-semibold"></span>
                </div>

                @auth
                <!-- Notification Bell -->
                <a href="{{ route('notifications.index') }}" class="btn btn-light position-relative border rounded-circle d-flex align-items-center justify-content-center p-2 shadow-sm" style="width: 40px; height: 40px;">
                    <i class="bi bi-bell-fill fs-6 text-dark"></i>
                    @php
                        $unread = auth()->user()->notifications()->where('is_read', false)->count();
                    @endphp
                    @if($unread > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                        {{ $unread }}
                    </span>
                    @endif
                </a>

                <!-- User Profile Pill -->
                <div class="dropdown">
                    <button class="btn btn-light border rounded-pill px-2 py-1 d-flex align-items-center gap-2 shadow-sm" type="button" data-bs-toggle="dropdown">
                        @php
                            $userAvatar = auth()->user()->avatar ?: $globalDefaultAvatar;
                        @endphp
                        @if($userAvatar)
                            <img src="{{ upload_url($userAvatar) }}" alt="Avatar" class="rounded-circle border" style="width: 28px; height: 28px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="bg-primary text-white rounded-circle align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem; display: none;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @else
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <span class="fw-bold small text-dark pe-1">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down text-muted small pe-1"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2 mt-2" style="min-width: 200px;">
                        <li><h6 class="dropdown-header text-xs text-uppercase fw-bold text-muted">{{ auth()->user()->role_title }}</h6></li>
                        <li><a class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2" href="{{ route('profile') }}"><i class="bi bi-person-badge text-primary"></i> Account Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item rounded-2 py-2 text-danger fw-bold d-flex align-items-center gap-2">
                                    <i class="bi bi-box-arrow-right"></i> Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endauth
            </div>
        </header>

        <!-- Page Content -->
        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success shadow rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between" role="alert" style="border-left: 5px solid #10b981;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                        <span class="fw-medium">{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger shadow rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between" role="alert" style="border-left: 5px solid #ef4444;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                        <span class="fw-medium">{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Dynamic Portal Footer -->
        <footer class="py-3 px-4 border-top bg-white mt-auto">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 text-xs text-muted">
                <div>
                    <strong class="text-dark">{{ !empty($globalPortalName) ? $globalPortalName : 'VMRFDU Activity Portal' }}</strong>
                    @if(!empty($globalFooterText))
                        &ndash; {{ $globalFooterText }}
                    @else
                        &ndash; &copy; {{ date('Y') }} All Rights Reserved.
                    @endif
                </div>
                <div>
                    <span class="fw-semibold text-secondary">{{ !empty($globalFooterSystemText) ? $globalFooterSystemText : 'Institutional Activity Verification Engine' }}</span>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Responsive Sidebar Toggle & Live Clock Logic -->
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('portalSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        
        if (window.innerWidth < 1200) {
            sidebar.classList.toggle('show');
            if (backdrop) backdrop.classList.toggle('show');
        } else {
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('portal_sidebar_collapsed', isCollapsed ? 'true' : 'false');
        }
    }

    // Initialize authoritative server time from NTP backend
    const serverTimeMs = {{ now()->getTimestampMs() }};
    const localStartMs = performance.now();

    function updateLiveClock() {
        const el = document.getElementById('liveClock');
        if (el) {
            // Calculate current exact time without relying on OS calendar Date
            const currentNtpMs = serverTimeMs + (performance.now() - localStartMs);
            const now = new Date(currentNtpMs);
            
            el.textContent = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' +
                             now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    }
    setInterval(updateLiveClock, 1000);
    updateLiveClock();

    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.getElementById('portalSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && window.innerWidth < 1200 && sidebar && sidebar.classList.contains('show')) {
                toggleSidebar();
            }
        });

        const sidebarMenu = document.getElementById('sidebarMenuContainer');
        if (sidebarMenu) {
            // Check if active menu item is visible inside the scrollable container
            const activeLink = sidebarMenu.querySelector('.sidebar-link.active');
            if (activeLink) {
                const menuRect = sidebarMenu.getBoundingClientRect();
                const linkRect = activeLink.getBoundingClientRect();
                const isSavedScrollValid = sessionStorage.getItem('portal_sidebar_scroll_top') !== null;
                
                // If active item is clipped below bottom or above top of visible area
                if (linkRect.bottom > menuRect.bottom || linkRect.top < menuRect.top) {
                    if (!isSavedScrollValid || linkRect.bottom > menuRect.bottom + 50 || linkRect.top < menuRect.top - 50) {
                        // Smoothly keep the selected item visible without resetting to top
                        activeLink.scrollIntoView({ block: 'nearest', behavior: 'instant' });
                        sessionStorage.setItem('portal_sidebar_scroll_top', sidebarMenu.scrollTop);
                    }
                }
            }

            // Save scroll position continuously during scrolling
            sidebarMenu.addEventListener('scroll', function() {
                sessionStorage.setItem('portal_sidebar_scroll_top', sidebarMenu.scrollTop);
            }, { passive: true });
        }

        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        sidebarLinks.forEach(link => {
            const spanText = link.querySelector('span')?.textContent?.trim();
            if (spanText && !link.getAttribute('title')) {
                link.setAttribute('title', spanText);
            }
            link.addEventListener('click', () => {
                if (sidebarMenu) {
                    sessionStorage.setItem('portal_sidebar_scroll_top', sidebarMenu.scrollTop);
                }
                if (window.innerWidth >= 1200) {
                    // Ensure sidebar stays expanded when a menu item is selected
                    localStorage.setItem('portal_sidebar_collapsed', 'false');
                    if (sidebar && sidebar.classList.contains('collapsed')) {
                        sidebar.classList.remove('collapsed');
                    }
                } else if (window.innerWidth < 1200 && sidebar && sidebar.classList.contains('show')) {
                    toggleSidebar();
                }
            });
        });

        // Permanently enforce crisp Light Mode and purge legacy dark theme settings
        document.documentElement.setAttribute("data-bs-theme", "light");
        localStorage.removeItem("portal_theme");

        // Global Instant Table Search & Filter Utility
        const searchInputs = document.querySelectorAll('.table-search-input');
        searchInputs.forEach(input => {
            input.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const targetSelector = this.getAttribute('data-table-target') || '.table-portal tbody tr';
                const rows = document.querySelectorAll(targetSelector);
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        });
    });
</script>

@auth
<!-- Session Timeout Warning Modal -->
<div class="modal fade" id="sessionTimeoutModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="sessionTimeoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
            <div class="modal-header bg-warning text-dark" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title fw-bold" id="sessionTimeoutModalLabel">
                    <i class="bi bi-clock-history me-2"></i> Session Expiring Soon
                </h5>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0 text-center" style="font-size: 1.1rem;">
                    You've been inactive for a while. For your security, you will be automatically logged out in <strong id="timeoutCountdown" class="text-danger">60</strong> seconds.
                </p>
                <p class="text-center text-muted mt-2 mb-0">Do you want to continue your session?</p>
            </div>
            <div class="modal-footer bg-light" style="border-radius: 0 0 12px 12px;">
                <form id="auto-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
                <button type="button" class="btn btn-outline-secondary px-4 fw-bold" onclick="document.getElementById('auto-logout-form').submit();">Logout</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" id="btnContinueSession">Continue Session</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Idle timeout configuration
        const sessionLifetimeMinutes = {{ config('session.lifetime') ?: 120 }};
        const logoutTime = sessionLifetimeMinutes * 60 * 1000;
        const warningTime = logoutTime > (60 * 1000) ? logoutTime - (60 * 1000) : Math.floor(logoutTime * 0.8); // warn 1 minute before or at 80% if short
        
        let warningTimer;
        let logoutTimer;
        let countdownInterval;
        let countdownSeconds = 60;
        let isModalVisible = false;
        
        const timeoutModalElement = document.getElementById('sessionTimeoutModal');
        const timeoutModal = new bootstrap.Modal(timeoutModalElement);
        
        // Reset timers on user activity
        function resetIdleTimer() {
            if (isModalVisible) return; // Do not reset if modal is currently showing

            clearTimeout(warningTimer);
            clearTimeout(logoutTimer);
            clearInterval(countdownInterval);
            
            // Set warning timer
            warningTimer = setTimeout(showWarningModal, warningTime);
            
            // Set actual logout timer
            logoutTimer = setTimeout(executeLogout, logoutTime);
        }
        
        // Show the warning modal
        function showWarningModal() {
            isModalVisible = true;
            countdownSeconds = (logoutTime - warningTime) / 1000;
            document.getElementById('timeoutCountdown').innerText = countdownSeconds;
            
            timeoutModal.show();
            
            // Start countdown timer for the modal UI
            countdownInterval = setInterval(() => {
                countdownSeconds--;
                if (countdownSeconds >= 0) {
                    document.getElementById('timeoutCountdown').innerText = countdownSeconds;
                }
            }, 1000);
        }
        
        // Execute automatic logout
        function executeLogout() {
            clearInterval(countdownInterval);
            document.getElementById('auto-logout-form').submit();
        }
        
        // Continue Session button handler
        document.getElementById('btnContinueSession').addEventListener('click', function() {
            timeoutModal.hide();
            isModalVisible = false;
            resetIdleTimer();
        });
        
        // Track activity events
        const activityEvents = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
        
        // Add listeners to document
        activityEvents.forEach(event => {
            document.addEventListener(event, resetIdleTimer, { passive: true });
        });
        
        // Initial setup
        resetIdleTimer();
    });
</script>

<script>
    // Cross-tab session guard: keeps every open tab in sync with the server-side
    // session so an expiry or logout in one tab cannot leave another tab showing
    // a stale, still-authenticated dashboard.
    (function() {
        const SIGNAL_KEY = 'portal_session_ended';
        const SESSION_CHECK_URL = '{{ route('session.check') }}';
        const LOGIN_URL = '{{ route('login') }}';
        let redirecting = false;

        function goToLogin() {
            if (redirecting) return;
            redirecting = true;
            try { localStorage.setItem(SIGNAL_KEY, String(Date.now())); } catch (e) {}
            window.location.replace(LOGIN_URL);
        }

        // Another tab signalled logout / expiry via localStorage.
        window.addEventListener('storage', function(e) {
            if (e.key === SIGNAL_KEY) {
                goToLogin();
            }
        });

        function verifySession() {
            if (redirecting) return;
            fetch(SESSION_CHECK_URL, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
                cache: 'no-store'
            }).then(function(res) {
                if (res.status === 401 || res.status === 419) {
                    goToLogin();
                    return;
                }
                return res.json();
            }).then(function(data) {
                if (data && data.authenticated === false) {
                    goToLogin();
                }
            }).catch(function() {
                // Network hiccup - do not force a logout on a transient failure.
            });
        }

        // Revalidate whenever this tab regains focus/visibility.
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                verifySession();
            }
        });
        window.addEventListener('focus', verifySession);

        // Revalidate when the page is restored from the browser back/forward
        // cache (bfcache), which can otherwise show a stale authenticated view.
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) {
                verifySession();
            }
        });

        // Periodic heartbeat so an idle-but-open tab notices expiry too.
        setInterval(verifySession, 30000);
    })();
</script>
@endauth

@stack('scripts')
</body>
</html>
