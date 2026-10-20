@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark"><i class="bi bi-gear-wide-connected text-dark me-2"></i>Dynamic Master Settings</h1>
            <p class="text-muted mb-0">Configure institutional branding (Logo & Avatar), portal titles, social media publishing credentials, and global constants.</p>
        </div>
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createSettingModal">
            <i class="bi bi-plus-circle me-1"></i>Add Variable Key
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- SECTION 1: PORTAL BRANDING & IDENTITY (LOGO & DEFAULT AVATAR) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-palette-fill text-primary me-2"></i>Portal Branding & Identity Settings</h5>
            <span class="badge bg-primary-subtle text-primary">Live Display Across Portal</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('superadmin.settings.branding') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-4 align-items-start">
                    <!-- Portal Logo Display & Upload -->
                    <div class="col-md-6 border-end-md">
                        <label class="form-label fw-bold text-dark mb-1"><i class="bi bi-building me-1"></i> Institutional Portal Logo</label>
                        <p class="text-muted small mb-3">Displayed in the main sidebar header and top navigation across all user dashboards.</p>
                        <div class="d-flex align-items-center gap-3 bg-light p-3 rounded-3 border mb-3">
                            <div class="d-flex align-items-center justify-content-center bg-dark rounded-3 shadow p-2" style="width: 70px; height: 70px;">
                                @if($portalLogo)
                                    <img src="{{ upload_url($portalLogo) }}" alt="Portal Logo" class="img-fluid" style="max-height: 55px; max-width: 55px; object-fit: contain;">
                                @else
                                    <i class="bi bi-bank2 text-white fs-2"></i>
                                @endif
                            </div>
                            <div>
                                <span class="d-block fw-semibold text-dark">{{ $portalName ?? 'VMRFDU' }}</span>
                                <span class="text-xs text-muted">{{ $portalLogo ? 'Custom logo active' : 'Using default system emblem' }}</span>
                                @if($portalLogo)
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="remove_portal_logo" value="1" id="remLogo">
                                        <label class="form-check-label text-danger text-xs fw-semibold" for="remLogo">Remove logo (Revert to default)</label>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <label class="form-label text-xs fw-bold text-muted">Upload New Portal Logo (PNG, JPG, SVG, WEBP)</label>
                        <input type="file" name="portal_logo_file" class="form-control form-control-sm" accept="image/*">
                    </div>

                    <!-- Default User Avatar Display & Upload -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark mb-1"><i class="bi bi-person-bounding-box me-1"></i> Default User Avatar</label>
                        <p class="text-muted small mb-3">Fallback profile image displayed for users who have not uploaded a personal profile photo.</p>
                        <div class="d-flex align-items-center gap-3 bg-light p-3 rounded-3 border mb-3">
                            <div class="d-flex align-items-center justify-content-center bg-white border rounded-circle shadow-sm" style="width: 70px; height: 70px; overflow: hidden;">
                                @if($defaultAvatar)
                                    <img src="{{ upload_url($defaultAvatar) }}" alt="Default Avatar" class="w-100 h-100" style="object-fit: cover;">
                                @else
                                    <i class="bi bi-person-fill text-primary fs-1"></i>
                                @endif
                            </div>
                            <div>
                                <span class="d-block fw-semibold text-dark">Default Portal Avatar</span>
                                <span class="text-xs text-muted">{{ $defaultAvatar ? 'Custom default avatar active' : 'Using system profile icon' }}</span>
                                @if($defaultAvatar)
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="remove_default_avatar" value="1" id="remAvatar">
                                        <label class="form-check-label text-danger text-xs fw-semibold" for="remAvatar">Remove custom avatar</label>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <label class="form-label text-xs fw-bold text-muted">Upload New Default Avatar (PNG, JPG, WEBP)</label>
                        <input type="file" name="default_avatar_file" class="form-control form-control-sm" accept="image/*">
                    </div>

                    <!-- Portal Titles & Dynamic Navigation Header -->
                    <div class="col-md-4 mt-4">
                        <label class="form-label fw-bold text-dark mb-1">Portal Title Identifier</label>
                        <input type="text" name="portal_name" class="form-control" value="{{ $portalName ?? 'VMRFDU Activity Portal' }}" placeholder="VMRFDU Activity Portal">
                    </div>
                    <div class="col-md-4 mt-4">
                        <label class="form-label fw-bold text-dark mb-1">Portal Subtitle / Tagline</label>
                        <input type="text" name="portal_tagline" class="form-control" value="{{ $portalTagline ?? 'Activity Verification & Tracking' }}" placeholder="Activity Verification & Tracking">
                    </div>
                    <div class="col-md-4 mt-4">
                        <label class="form-label fw-bold text-dark mb-1">Top Navbar Header Title</label>
                        <input type="text" name="portal_header_title" class="form-control" value="{{ $portalHeaderTitle ?? 'Institutional Activity Submissions & Verification Desk' }}" placeholder="Institutional Activity Submissions & Verification Desk">
                    </div>

                    <!-- Dynamic Footer Configuration -->
                    <div class="col-md-4 mt-3">
                        <label class="form-label fw-bold text-dark mb-1">Portal Footer Copyright Text</label>
                        <input type="text" name="portal_footer_text" class="form-control" value="{{ $portalFooterText ?? '© ' . date('Y') . ' Vinayaka Mission\'s Research Foundation (DU). All rights reserved.' }}" placeholder="© 2026 Vinayaka Mission's Research Foundation (DU). All rights reserved.">
                    </div>
                    <div class="col-md-4 mt-3">
                        <label class="form-label fw-bold text-dark mb-1">Footer System Engine Tagline</label>
                        <input type="text" name="portal_footer_system_text" class="form-control" value="{{ $portalFooterSystemText ?? 'Institutional Activity Verification Engine' }}" placeholder="Institutional Activity Verification Engine">
                    </div>
                    <div class="col-md-4 mt-3">
                        <label class="form-label fw-bold text-dark mb-1">Sidebar Support Helpline Number</label>
                        <input type="text" name="helpline_number" class="form-control" value="{{ $helplineNumber ?? '1800-VMRF-HELP' }}" placeholder="1800-VMRF-HELP">
                    </div>

                    <!-- Dynamic Theme Colors & Card Styling -->
                    <div class="col-12 mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-palette-fill text-primary me-2"></i>Dynamic Theme Colors & Card Design Customization</h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Primary Theme Color (Sidebar / Headers)</label>
                        <div class="input-group input-group-sm">
                            <input type="color" class="form-control form-control-color border" id="color_primary" value="{{ $portalPrimaryColor ?? '#1e293b' }}" title="Choose primary color" oninput="document.getElementById('text_primary').value = this.value">
                            <input type="text" class="form-control font-monospace" name="portal_primary_color" id="text_primary" value="{{ $portalPrimaryColor ?? '#1e293b' }}" oninput="document.getElementById('color_primary').value = this.value">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Accent Brand Color (Buttons / Active Items)</label>
                        <div class="input-group input-group-sm">
                            <input type="color" class="form-control form-control-color border" id="color_accent" value="{{ $portalAccentColor ?? '#2563eb' }}" title="Choose accent color" oninput="document.getElementById('text_accent').value = this.value">
                            <input type="text" class="form-control font-monospace" name="portal_accent_color" id="text_accent" value="{{ $portalAccentColor ?? '#2563eb' }}" oninput="document.getElementById('color_accent').value = this.value">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Card Border Radius (Corner Curvature)</label>
                        <select name="portal_card_radius" class="form-select form-select-sm">
                            <option value="8px" {{ ($portalCardRadius ?? '16px') == '8px' ? 'selected' : '' }}>8px (Sharp / Compact)</option>
                            <option value="12px" {{ ($portalCardRadius ?? '16px') == '12px' ? 'selected' : '' }}>12px (Standard Crisp)</option>
                            <option value="16px" {{ ($portalCardRadius ?? '16px') == '16px' ? 'selected' : '' }}>16px (Modern Executive Pill)</option>
                            <option value="24px" {{ ($portalCardRadius ?? '16px') == '24px' ? 'selected' : '' }}>24px (Soft Rounded Studio)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Card Border Color</label>
                        <div class="input-group input-group-sm">
                            <input type="color" class="form-control form-control-color border" id="color_border" value="{{ $portalCardBorder ?? '#e2e8f0' }}" title="Choose border color" oninput="document.getElementById('text_border').value = this.value">
                            <input type="text" class="form-control font-monospace" name="portal_card_border" id="text_border" value="{{ $portalCardBorder ?? '#e2e8f0' }}" oninput="document.getElementById('color_border').value = this.value">
                        </div>
                    </div>

                    <div class="col-12 text-end mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Save Master Branding & Theme Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- NEW SECTION: SIDEBAR NAVIGATION CONFIGURATION -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-list-nested text-primary me-2"></i>Sidebar Navigation Icons & Colors</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('superadmin.settings.sidebar_icons') }}" method="POST">
                @csrf
                @php
                $sidebarMenus = [
                    'group_nav' => [
                        'default_label' => 'Navigation Hub', 'default_icon' => 'bi-compass', 'default_color' => '#0B3B60',
                        'items' => [
                            'superadmin_dashboard' => ['default_label' => 'Root System Dashboard', 'default_icon' => 'bi-grid-1x2-fill', 'default_color' => '#C9A050'],
                            'media_dashboard' => ['default_label' => 'Media Studio Dashboard', 'default_icon' => 'bi-camera-reels-fill', 'default_color' => '#0B3B60'],
                            'user_dashboard' => ['default_label' => 'My Dashboard', 'default_icon' => 'bi-house-door-fill', 'default_color' => '#0B3B60'],
                        ]
                    ],
                    'group_activity' => [
                        'default_label' => 'Activity Capture Desk', 'default_icon' => 'bi-journal-plus', 'default_color' => '#15803d',
                        'items' => [
                            'submissions_create' => ['default_label' => 'Submit New Activity', 'default_icon' => 'bi-pencil-square', 'default_color' => '#15803d'],
                            'submissions_index' => ['default_label' => 'Submission History', 'default_icon' => 'bi-archive-fill', 'default_color' => '#C9A050'],
                        ]
                    ],
                    'group_approval' => [
                        'default_label' => 'Approval Authority Desk', 'default_icon' => 'bi-check2-square', 'default_color' => '#C9A050',
                        'items' => [
                            'approvals_pending' => ['default_label' => 'Pending Queue', 'default_icon' => 'bi-hourglass-split', 'default_color' => '#C9A050'],
                            'approvals_approved' => ['default_label' => 'Approved Activities', 'default_icon' => 'bi-check2-all', 'default_color' => '#15803d'],
                            'approvals_rejected' => ['default_label' => 'Rejected Archive', 'default_icon' => 'bi-x-octagon-fill', 'default_color' => '#be123c'],
                        ]
                    ],
                    'group_media' => [
                        'default_label' => 'Media Studio & Broadcast', 'default_icon' => 'bi-camera-reels', 'default_color' => '#0f766e',
                        'items' => [
                            'media_overview' => ['default_label' => 'Media Desk Overview', 'default_icon' => 'bi-display', 'default_color' => '#0B3B60'],
                            'media_editing' => ['default_label' => 'Stage 1: Photo Editing', 'default_icon' => 'bi-images', 'default_color' => '#2563eb'],
                            'media_ready' => ['default_label' => 'Stage 2: Ready to Post', 'default_icon' => 'bi-cloud-arrow-up-fill', 'default_color' => '#15803d'],
                            'media_published' => ['default_label' => 'Published Archive', 'default_icon' => 'bi-collection-play-fill', 'default_color' => '#2563eb'],
                        ]
                    ],
                    'group_analytics' => [
                        'default_label' => 'Analytics & Intelligence', 'default_icon' => 'bi-graph-up', 'default_color' => '#2563eb',
                        'items' => [
                            'analytics_index' => ['default_label' => 'Analytics & Reports Hub', 'default_icon' => 'bi-graph-up-arrow', 'default_color' => '#0f766e'],
                        ]
                    ],
                    'group_admin' => [
                        'default_label' => 'Administration Desk', 'default_icon' => 'bi-building-gear', 'default_color' => '#475569',
                        'items' => [
                            'admin_colleges' => ['default_label' => 'Colleges Management', 'default_icon' => 'bi-bank2', 'default_color' => '#475569'],
                            'admin_categories' => ['default_label' => 'Event Categories Master', 'default_icon' => 'bi-bookmark-star-fill', 'default_color' => '#C9A050'],
                            'admin_users' => ['default_label' => 'Users & Roles Matrix', 'default_icon' => 'bi-person-lines-fill', 'default_color' => '#0B3B60'],
                        ]
                    ],
                    'group_control' => [
                        'default_label' => 'Control Panel', 'default_icon' => 'bi-shield-lock-fill', 'default_color' => '#be123c',
                        'items' => [
                            'control_access' => ['default_label' => 'Access Control Hub', 'default_icon' => 'bi-shield-check', 'default_color' => '#15803d'],
                            'control_workflows' => ['default_label' => 'Workflow & Stages', 'default_icon' => 'bi-bezier2', 'default_color' => '#2563eb'],
                            'control_activities' => ['default_label' => 'Activity Override Manager', 'default_icon' => 'bi-tools', 'default_color' => '#be123c'],
                            'control_logs' => ['default_label' => 'System Logs Hub', 'default_icon' => 'bi-clipboard-data-fill', 'default_color' => '#0f766e'],
                            'control_email' => ['default_label' => 'Dynamic Email Engine', 'default_icon' => 'bi-envelope-paper-fill', 'default_color' => '#2563eb'],
                            'control_settings' => ['default_label' => 'Master Settings', 'default_icon' => 'bi-ui-radios', 'default_color' => '#C9A050'],
                        ]
                    ],
                    'group_account' => [
                        'default_label' => 'Account Settings', 'default_icon' => 'bi-person-gear', 'default_color' => '#475569',
                        'items' => [
                            'profile' => ['default_label' => 'My Profile', 'default_icon' => 'bi-person-circle', 'default_color' => '#475569'],
                        ]
                    ]
                ];
                @endphp

                <div class="accordion custom-accordion" id="sidebarConfigAccordion">
                    @foreach($sidebarMenus as $groupKey => $groupConfig)
                        @php
                            $savedGroupLabel = isset($sidebarIcons[$groupKey]['label']) ? $sidebarIcons[$groupKey]['label'] : $groupConfig['default_label'];
                            $savedGroupIcon = isset($sidebarIcons[$groupKey]['icon']) ? $sidebarIcons[$groupKey]['icon'] : $groupConfig['default_icon'];
                            $savedGroupColor = isset($sidebarIcons[$groupKey]['color']) ? $sidebarIcons[$groupKey]['color'] : $groupConfig['default_color'];
                        @endphp
                        <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm bg-light">
                            <h2 class="accordion-header" id="heading_{{ $groupKey }}">
                                <button class="accordion-button collapsed rounded-3 fw-bold bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $groupKey }}" aria-expanded="false" aria-controls="collapse_{{ $groupKey }}">
                                    <i class="bi {{ $savedGroupIcon }} me-3 fs-5" id="preview_group_btn_{{ $groupKey }}" style="color: {{ $savedGroupColor }}"></i>
                                    {{ $savedGroupLabel }} Configuration
                                </button>
                            </h2>
                            <div id="collapse_{{ $groupKey }}" class="accordion-collapse collapse" aria-labelledby="heading_{{ $groupKey }}" data-bs-parent="#sidebarConfigAccordion">
                                <div class="accordion-body p-4 bg-white border-top">
                                    
                                    <!-- Main Section Config -->
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-folder2-open me-2 text-primary"></i> Main Section Properties</h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-5">
                                            <label class="form-label text-muted small fw-semibold">Display Label</label>
                                            <input type="text" class="form-control" name="icons[{{ $groupKey }}][label]" value="{{ $savedGroupLabel }}">
                                        </div>
                                        <div class="col-md-7">
                                            <label class="form-label text-muted small fw-semibold">Section Icon & Color</label>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3 fs-4" style="width: 32px; text-align: center;">
                                                    <i class="bi {{ $savedGroupIcon }}" id="preview_icon_{{ $groupKey }}" style="color: {{ $savedGroupColor }}"></i>
                                                </div>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border">bi</span>
                                                    <input type="text" class="form-control font-monospace border" name="icons[{{ $groupKey }}][icon]" value="{{ $savedGroupIcon }}" placeholder="bi-..." oninput="document.getElementById('preview_icon_{{ $groupKey }}').className = 'bi ' + this.value; document.getElementById('preview_group_btn_{{ $groupKey }}').className = 'bi ' + this.value + ' me-3 fs-5';">
                                                    <input type="color" class="form-control form-control-color border px-1" style="max-width: 50px;" name="icons[{{ $groupKey }}][color]" value="{{ $savedGroupColor }}" title="Choose Color" oninput="document.getElementById('preview_icon_{{ $groupKey }}').style.color = this.value; document.getElementById('preview_group_btn_{{ $groupKey }}').style.color = this.value;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sub Menu Items Config -->
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 mt-4"><i class="bi bi-list-nested me-2 text-primary"></i> Sub-Menu Links Properties</h6>
                                    @foreach($groupConfig['items'] as $itemKey => $itemConfig)
                                        @php
                                            $savedItemLabel = isset($sidebarIcons[$itemKey]['label']) ? $sidebarIcons[$itemKey]['label'] : $itemConfig['default_label'];
                                            $savedItemIcon = isset($sidebarIcons[$itemKey]['icon']) ? $sidebarIcons[$itemKey]['icon'] : $itemConfig['default_icon'];
                                            $savedItemColor = isset($sidebarIcons[$itemKey]['color']) ? $sidebarIcons[$itemKey]['color'] : $itemConfig['default_color'];
                                        @endphp
                                        <div class="row g-3 mb-3 align-items-end p-3 rounded-3" style="background-color: #f8fafc;">
                                            <div class="col-md-5">
                                                <label class="form-label text-muted small fw-semibold">{{ $itemConfig['default_label'] }} (Default Name)</label>
                                                <input type="text" class="form-control form-control-sm" name="icons[{{ $itemKey }}][label]" value="{{ $savedItemLabel }}">
                                            </div>
                                            <div class="col-md-7">
                                                <label class="form-label text-muted small fw-semibold">Link Icon & Color</label>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3 fs-5" style="width: 24px; text-align: center;">
                                                        <i class="bi {{ $savedItemIcon }}" id="preview_icon_{{ $itemKey }}" style="color: {{ $savedItemColor }}"></i>
                                                    </div>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text bg-white border">bi</span>
                                                        <input type="text" class="form-control font-monospace border" name="icons[{{ $itemKey }}][icon]" value="{{ $savedItemIcon }}" placeholder="bi-..." oninput="document.getElementById('preview_icon_{{ $itemKey }}').className = 'bi ' + this.value">
                                                        <input type="color" class="form-control form-control-color border px-1" style="max-width: 45px;" name="icons[{{ $itemKey }}][color]" value="{{ $savedItemColor }}" title="Choose Color" oninput="document.getElementById('preview_icon_{{ $itemKey }}').style.color = this.value">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="col-12 text-end mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                        <i class="bi bi-save me-1"></i> Save Sidebar Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SECTION 2: NTP PROTOCOL SYNCHRONIZATION & CARBON TIMEZONE CONFIG -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-primary me-2"></i>NTP Protocol & Carbon Time Synchronization</h5>
            <span class="badge bg-success-subtle text-success border border-success border-opacity-25"><i class="bi bi-shield-check me-1"></i>Carbon Aligned (+05:30 IST)</span>
        </div>
        <div class="card-body p-4">
            <div class="row g-4 align-items-center">
                <!-- Status Box -->
                <div class="col-lg-5 border-end-lg">
                    <div class="bg-dark text-white p-4 rounded-4 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-secondary pb-2">
                            <span class="text-xs text-uppercase tracking-wider fw-bold text-info"><i class="bi bi-hdd-network me-1"></i> NTP Server Socket</span>
                            <span class="badge bg-success font-monospace">PORT {{ $ntpStatus['port'] ?? 123 }}</span>
                        </div>
                        <h4 class="font-monospace text-warning fw-bold mb-1">{{ $ntpStatus['synchronized_time'] ?? now()->format('Y-m-d H:i:s P') }}</h4>
                        <div class="text-xs text-light opacity-75 mb-3">Timezone: {{ $ntpStatus['timezone'] ?? 'Asia/Kolkata' }} (UTC+05:30 IST)</div>
                        
                        <div class="d-flex align-items-center justify-content-between bg-black bg-opacity-50 p-2 rounded text-xs font-monospace">
                            <span class="text-muted">Target Host:</span>
                            <span class="text-white">{{ $ntpStatus['server'] ?? 'time.google.com' }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between bg-black bg-opacity-50 p-2 rounded text-xs font-monospace mt-1">
                            <span class="text-muted">Clock Offset:</span>
                            <span class="{{ ($ntpStatus['offset_seconds'] ?? 0) === 0 ? 'text-success' : 'text-warning' }}">{{ $ntpStatus['status'] ?? 'Synchronized' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Settings Form -->
                <div class="col-lg-7">
                    <form action="{{ route('superadmin.settings.ntp') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark text-sm">NTP Protocol Host</label>
                                <input type="text" name="ntp_server" class="form-control font-monospace" value="{{ $ntpStatus['server'] ?? 'time.google.com' }}" required>
                                <div class="form-text text-xxs">Default: time.google.com</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark text-sm">NTP UDP Port</label>
                                <input type="number" name="ntp_port" class="form-control font-monospace" value="{{ $ntpStatus['port'] ?? 123 }}" required>
                                <div class="form-text text-xxs">Standard Network Time Port: 123</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark text-sm">Master Carbon Project Timezone</label>
                                <input type="text" name="timezone" class="form-control font-monospace" value="{{ $ntpStatus['timezone'] ?? 'Asia/Kolkata' }}" required>
                                <div class="form-text text-xxs">All Carbon date manipulations across the entire project inherit this configuration (+05:30 IST).</div>
                            </div>
                            <div class="col-12 text-end mt-3">
                                <button type="submit" class="btn btn-outline-primary px-4 fw-semibold">
                                    <i class="bi bi-arrow-repeat me-1"></i> Resynchronize NTP Clock & Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 3: ADVANCED SYSTEM SETTINGS & KEY-VALUE VARIABLES -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-sliders me-2"></i>Advanced Key-Value System Variables</h5>
            <span class="badge bg-secondary">System Config</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted text-xxs text-uppercase">
                    <tr>
                        <th class="ps-4">Setting Key</th>
                        <th>Value</th>
                        <th>Group</th>
                        <th>Description</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($settings as $set)
                    <tr>
                        <td class="ps-4 font-monospace text-xs fw-bold text-primary">{{ $set->key }}</td>
                        <td class="text-dark text-sm text-truncate" style="max-width: 300px;">{{ $set->value }}</td>
                        <td><span class="badge bg-secondary text-uppercase">{{ $set->group }}</span></td>
                        <td class="text-muted text-xs">{{ $set->description ?? 'System Variable' }}</td>
                        <td class="text-end pe-4">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSettingModal{{ $set->id }}">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade text-start" id="editSettingModal{{ $set->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('superadmin.settings.update') }}" method="POST">
                                @csrf
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title fw-bold">Edit Setting: {{ $set->key }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <input type="hidden" name="key" value="{{ $set->key }}">
                                        <input type="hidden" name="group" value="{{ $set->group }}">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">Value <span class="text-danger">*</span></label>
                                            <textarea name="value" class="form-control" rows="4" required>{{ $set->value }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">Description</label>
                                            <input type="text" name="description" class="form-control" value="{{ $set->description }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Setting</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No custom settings configured yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Setting Modal -->
    <div class="modal fade text-start" id="createSettingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('superadmin.settings.update') }}" method="POST">
                @csrf
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold">Add Master Setting Variable</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-sm">Key Identifier <span class="text-danger">*</span></label>
                            <input type="text" name="key" class="form-control" placeholder="e.g. max_upload_size_mb" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-sm">Value <span class="text-danger">*</span></label>
                            <input type="text" name="value" class="form-control" placeholder="e.g. 50" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-sm">Group</label>
                            <select name="group" class="form-select">
                                <option value="general">General</option>
                                <option value="social_media">Social Media API</option>
                                <option value="categories">Activity Categories</option>
                                <option value="platforms">Supported Platforms</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-sm">Description</label>
                            <input type="text" name="description" class="form-control" placeholder="Explanation of this setting">
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Variable</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
