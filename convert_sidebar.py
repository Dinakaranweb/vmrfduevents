import re

with open('resources/views/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace(
    '''<div class="nav-section-title mt-3">Activity Capture Desk</div>
                    @if(->isSuperAdmin() || ->hasPermission('activities.create') || in_array(->role, ['college_admin', 'user']))''',
    '''@php ${sec2Active} = request()->routeIs('submissions.*'); @endphp
                    <div class="nav-section-title mt-3 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseSec2" aria-expanded="{{ ${sec2Active} ? 'true' : 'false' }}" style="cursor: pointer;">
                        <span>Activity Capture Desk</span>
                        <i class="bi bi-chevron-down nav-accordion-icon transition-transform"></i>
                    </div>
                    <div class="collapse {{ ${sec2Active} ? 'show' : '' }}" id="collapseSec2">
                    @if(->isSuperAdmin() || ->hasPermission('activities.create') || in_array(->role, ['college_admin', 'user']))'''
)

content = content.replace(
    '''                    @endif
                @endif

                <!-- 3. APPROVAL AUTHORITY & VERIFICATION MODULE -->''',
    '''                    @endif
                    </div>
                @endif

                <!-- 3. APPROVAL AUTHORITY & VERIFICATION MODULE -->'''
)

content = content.replace(
    '''<div class="nav-section-title mt-3">Approval Authority Desk</div>
                    <a href="{{ route('approvals.pending') }}"''',
    '''@php ${sec3Active} = request()->routeIs('approvals.*'); @endphp
                    <div class="nav-section-title mt-3 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseSec3" aria-expanded="{{ ${sec3Active} ? 'true' : 'false' }}" style="cursor: pointer;">
                        <span>Approval Authority Desk</span>
                        <i class="bi bi-chevron-down nav-accordion-icon transition-transform"></i>
                    </div>
                    <div class="collapse {{ ${sec3Active} ? 'show' : '' }}" id="collapseSec3">
                    <a href="{{ route('approvals.pending') }}"'''
)

content = content.replace(
    '''                    </a>
                @endif

                <!-- 4. MEDIA BROADCASTING DESK MODULE -->''',
    '''                    </a>
                    </div>
                @endif

                <!-- 4. MEDIA BROADCASTING DESK MODULE -->'''
)

content = content.replace(
    '''<div class="nav-section-title mt-3">Media Studio & Broadcast</div>
                    <a href="{{ route('media.dashboard') }}"''',
    '''@php ${sec4Active} = request()->routeIs('media.*'); @endphp
                    <div class="nav-section-title mt-3 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseSec4" aria-expanded="{{ ${sec4Active} ? 'true' : 'false' }}" style="cursor: pointer;">
                        <span>Media Studio & Broadcast</span>
                        <i class="bi bi-chevron-down nav-accordion-icon transition-transform"></i>
                    </div>
                    <div class="collapse {{ ${sec4Active} ? 'show' : '' }}" id="collapseSec4">
                    <a href="{{ route('media.dashboard') }}"'''
)

content = content.replace(
    '''                    </a>
                @endif

                <!-- 5. ANALYTICS & REPORTS MODULE -->''',
    '''                    </a>
                    </div>
                @endif

                <!-- 5. ANALYTICS & REPORTS MODULE -->'''
)

content = content.replace(
    '''<div class="nav-section-title mt-3">Analytics & Intelligence</div>
                    <a href="{{ route('analytics.index') }}"''',
    '''@php ${sec5Active} = request()->routeIs('analytics.*'); @endphp
                    <div class="nav-section-title mt-3 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseSec5" aria-expanded="{{ ${sec5Active} ? 'true' : 'false' }}" style="cursor: pointer;">
                        <span>Analytics & Intelligence</span>
                        <i class="bi bi-chevron-down nav-accordion-icon transition-transform"></i>
                    </div>
                    <div class="collapse {{ ${sec5Active} ? 'show' : '' }}" id="collapseSec5">
                    <a href="{{ route('analytics.index') }}"'''
)

content = content.replace(
    '''                    </a>
                @endif

                <!-- 6. INSTITUTIONAL ADMINISTRATION MODULE -->''',
    '''                    </a>
                    </div>
                @endif

                <!-- 6. INSTITUTIONAL ADMINISTRATION MODULE -->'''
)

content = content.replace(
    '''<div class="nav-section-title mt-3">Administration Desk</div>
                    @if(->isSuperAdmin() || ->hasPermission('colleges.view'))''',
    '''@php ${sec6Active} = request()->routeIs('superadmin.colleges') || request()->routeIs('admin.colleges') || request()->routeIs('superadmin.categories') || request()->routeIs('superadmin.users') || request()->routeIs('admin.users'); @endphp
                    <div class="nav-section-title mt-3 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseSec6" aria-expanded="{{ ${sec6Active} ? 'true' : 'false' }}" style="cursor: pointer;">
                        <span>Administration Desk</span>
                        <i class="bi bi-chevron-down nav-accordion-icon transition-transform"></i>
                    </div>
                    <div class="collapse {{ ${sec6Active} ? 'show' : '' }}" id="collapseSec6">
                    @if(->isSuperAdmin() || ->hasPermission('colleges.view'))'''
)

content = content.replace(
    '''                    @endif
                @endif

                <!-- 7. SUPER ADMIN & GOVERNANCE CONTROL PANEL -->''',
    '''                    @endif
                    </div>
                @endif

                <!-- 7. SUPER ADMIN & GOVERNANCE CONTROL PANEL -->'''
)

content = content.replace(
    '''<div class="nav-section-title text-warning mt-3"><i class="bi bi-shield-lock-fill me-1"></i> Control Panel</div>
                    @if(->isSuperAdmin() || ->hasPermission('roles.view'))''',
    '''@php ${sec7Active} = request()->routeIs('superadmin.*') && !request()->routeIs('superadmin.colleges') && !request()->routeIs('superadmin.categories') && !request()->routeIs('superadmin.users'); @endphp
                    <div class="nav-section-title text-warning mt-3 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseSec7" aria-expanded="{{ ${sec7Active} ? 'true' : 'false' }}" style="cursor: pointer;">
                        <span><i class="bi bi-shield-lock-fill me-1"></i> Control Panel</span>
                        <i class="bi bi-chevron-down nav-accordion-icon transition-transform"></i>
                    </div>
                    <div class="collapse {{ ${sec7Active} ? 'show' : '' }}" id="collapseSec7">
                    @if(->isSuperAdmin() || ->hasPermission('roles.view'))'''
)

content = content.replace(
    '''                    @endif
                @endif
            @endauth
        </div>''',
    '''                    @endif
                    </div>
                @endif
            @endauth
        </div>'''
)

content = content.replace(
    '''        .portal-card { border-radius: var(--radius-md) !important; border-color: var(--portal-border) !important; }
    </style>''',
    '''        .portal-card { border-radius: var(--radius-md) !important; border-color: var(--portal-border) !important; }
        .nav-accordion-icon { transition: transform 0.3s ease; font-size: 0.85rem; }
        .nav-section-title[aria-expanded="true"] .nav-accordion-icon { transform: rotate(180deg); }
    </style>'''
)

with open('resources/views/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
