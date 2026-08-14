@extends('layouts.app')

@section('title', 'Enterprise Access Control & Permission Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-shield-lock-fill text-warning me-2"></i>Enterprise Access Control Hub</h4>
            <p class="text-muted text-sm mb-0">Centralized dynamic permission engine covering Role Management, User Overrides, Menu Gatekeeping, and Institutional Scopes.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#copyPermissionsModal">
                <i class="bi bi-files me-1"></i>Copy Role Permissions
            </button>
            <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                <i class="bi bi-plus-lg me-1"></i>Create Dynamic Role
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $activeTab = request()->input('tab', 'roles');
    @endphp

    <!-- Top Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2 bg-white p-2 rounded-4 shadow-sm border" id="accessControlTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'roles' ? 'active' : '' }} rounded-3 fw-bold px-4" href="{{ route('superadmin.accessControl', ['tab' => 'roles']) }}">
                <i class="bi bi-person-badge me-2"></i>1. Roles & CRUD Matrix
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'users' ? 'active' : '' }} rounded-3 fw-bold px-4" href="{{ route('superadmin.accessControl', ['tab' => 'users']) }}">
                <i class="bi bi-person-gear me-2"></i>2. Admin & User Overrides
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'modules' ? 'active' : '' }} rounded-3 fw-bold px-4" href="{{ route('superadmin.accessControl', ['tab' => 'modules']) }}">
                <i class="bi bi-layout-sidebar me-2"></i>3. Module & Menu Gatekeeper
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'workflows' ? 'active' : '' }} rounded-3 fw-bold px-4" href="{{ route('superadmin.accessControl', ['tab' => 'workflows']) }}">
                <i class="bi bi-diagram-3 me-2"></i>4. Workflow & Status Gates
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'scopes' ? 'active' : '' }} rounded-3 fw-bold px-4" href="{{ route('superadmin.accessControl', ['tab' => 'scopes']) }}">
                <i class="bi bi-building me-2"></i>5. College & Dept Scope
            </a>
        </li>
    </ul>

    <div class="tab-content" id="accessControlTabContent">
        
        <!-- ========================================================= -->
        <!-- TAB 1: ROLES & CRUD MATRIX -->
        <!-- ========================================================= -->
        <div class="tab-pane fade {{ $activeTab === 'roles' ? 'show active' : '' }}" id="tab-roles" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-1">Role Management & Role-wise Permission Mapping</h6>
                        <p class="text-xs text-muted mb-0">Configure granular CRUD permissions (Create, Read, Update, Delete) across system modules for each defined role.</p>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-xxs text-uppercase text-muted">
                                <tr>
                                    <th class="ps-4 py-3">ID</th>
                                    <th>Role Name & Slug</th>
                                    <th>Description</th>
                                    <th>Data Visibility Scope</th>
                                    <th>Assigned Users</th>
                                    <th>CRUD Matrix Summary</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @foreach($roles as $role)
                                @php
                                    $userCount = \App\Models\User::where('role', $role->slug)->count();
                                    $currentPerms = is_array($role->permissions) ? $role->permissions : [];
                                    $permCount = in_array('*', $currentPerms) ? 'ALL' : count($currentPerms);
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">#{{ $role->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-light text-primary fw-bold me-3">
                                                {{ substr($role->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block text-sm">{{ $role->name }}</span>
                                                <code class="text-xs bg-light px-2 py-0.5 rounded">{{ $role->slug }}</code>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-sm text-muted">{{ $role->description ?: 'No description provided.' }}</td>
                                    <td>
                                        @if($role->data_scope === 'all')
                                            <span class="badge bg-purple-soft text-purple border"><i class="bi bi-globe me-1"></i>Universal / Global</span>
                                        @elseif($role->data_scope === 'college')
                                            <span class="badge bg-blue-soft text-blue border"><i class="bi bi-building me-1"></i>College Scoped</span>
                                        @else
                                            <span class="badge bg-secondary-soft text-dark border"><i class="bi bi-person me-1"></i>Self Scoped</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark fw-bold border">{{ $userCount }} Users</span>
                                    </td>
                                    <td>
                                        @if($permCount === 'ALL')
                                            <span class="badge bg-danger text-white"><i class="bi bi-star-fill me-1"></i>Unrestricted Bypass</span>
                                        @else
                                            <span class="badge bg-success-soft text-success border fw-bold">{{ $permCount }} Actions Granted</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4 d-flex justify-content-end align-items-center gap-2">
                                        @if($role->slug !== 'super_admin')
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPermModal{{ $role->id }}">
                                                <i class="bi bi-shield-lock me-1"></i>Configure CRUD Permissions
                                            </button>
                                        @else
                                            <span class="badge bg-danger text-white">System Core</span>
                                        @endif

                                        @if(!$role->is_system)
                                            <form action="{{ route('superadmin.roles.delete', $role->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete role {{ $role->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Role"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Configure Role CRUD Modal -->
                                @if($role->slug !== 'super_admin')
                                <div class="modal fade" id="editPermModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                        <form action="{{ route('superadmin.roles.update', $role->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="name" value="{{ $role->name }}">
                                            <input type="hidden" name="description" value="{{ $role->description }}">
                                            <input type="hidden" name="data_scope" value="{{ $role->data_scope }}">
                                            
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-bottom bg-light p-4">
                                                    <div>
                                                        <h5 class="modal-title fw-bold mb-0">Role CRUD Permission Mapping: {{ $role->name }}</h5>
                                                        <p class="text-xs text-muted mb-0 font-monospace">{{ $role->slug }} &bull; Current Scope: {{ strtoupper($role->data_scope) }}</p>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="alert alert-primary py-2 px-3 text-xs mb-4 rounded-3 d-flex align-items-center">
                                                        <i class="bi bi-info-circle-fill fs-5 me-3"></i>
                                                        <div>
                                                            <strong>Dynamic CRUD Control:</strong> Check or uncheck permissions below to instantly modify Create, Read, Update, and Delete capabilities for all users assigned to this role.
                                                        </div>
                                                    </div>

                                                    <div class="row g-3">
                                                        @foreach($availablePermissions as $moduleKey => $module)
                                                        <div class="col-md-6 col-lg-4">
                                                            <div class="card border h-100 rounded-3 shadow-sm">
                                                                <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                                                                    <span class="fw-bold text-dark text-sm"><i class="bi bi-folder2-open text-primary me-2"></i>{{ $module['label'] }}</span>
                                                                </div>
                                                                <div class="card-body p-3">
                                                                    @foreach($module['actions'] as $permKey => $permLabel)
                                                                    @php
                                                                        $isChecked = in_array('*', $currentPerms) || in_array($permKey, $currentPerms) || in_array(explode('.', $permKey)[0] . '.*', $currentPerms);
                                                                    @endphp
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permKey }}" id="role_{{ $role->id }}_{{ str_replace('.', '_', $permKey) }}" {{ $isChecked ? 'checked' : '' }}>
                                                                        <label class="form-check-label text-sm text-dark" for="role_{{ $role->id }}_{{ str_replace('.', '_', $permKey) }}">
                                                                            {{ $permLabel }}
                                                                            <br><small class="text-muted font-monospace" style="font-size: 0.65rem;">{{ $permKey }}</small>
                                                                        </label>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top bg-light p-3">
                                                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary btn-sm px-4"><i class="bi bi-save me-1"></i>Save Role Permissions</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- TAB 2: ADMIN & USER OVERRIDES -->
        <!-- ========================================================= -->
        <div class="tab-pane fade {{ $activeTab === 'users' ? 'show active' : '' }}" id="tab-users" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-1"><i class="bi bi-person-badge-fill text-primary me-2"></i>Select User / Admin</h6>
                            <p class="text-xs text-muted mb-0">Select an individual Admin or User to inspect or assign individual permission overrides.</p>
                        </div>
                        <div class="card-body p-4">
                            <form method="GET" action="{{ route('superadmin.accessControl') }}">
                                <input type="hidden" name="tab" value="users">
                                <div class="mb-3">
                                    <label class="form-label text-xs fw-bold text-uppercase text-muted">Filter by Institutional User</label>
                                    <select name="user_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">-- Choose User / Admin --</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}" {{ ($selectedUser && $selectedUser->id == $u->id) ? 'selected' : '' }}>
                                                {{ $u->name }} [Role: {{ strtoupper($u->role) }}] ({{ $u->college ? $u->college->short_name : 'Global' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>

                            @if($selectedUser)
                            <div class="p-3 bg-light rounded-3 mt-3 border">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar-circle bg-primary text-white fw-bold me-3">
                                        {{ substr($selectedUser->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">{{ $selectedUser->name }}</h6>
                                        <span class="text-xs text-muted">{{ $selectedUser->email }}</span>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between text-xs mb-1">
                                    <span class="text-muted">Assigned Base Role:</span>
                                    <span class="fw-bold text-dark">{{ strtoupper($selectedUser->role) }}</span>
                                </div>
                                <div class="d-flex justify-content-between text-xs">
                                    <span class="text-muted">College Mapping:</span>
                                    <span class="fw-bold text-dark">{{ $selectedUser->college ? $selectedUser->college->name : 'Global / Universal' }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    @if($selectedUser)
                        @if($selectedUser->role === 'super_admin')
                            <div class="card border-0 shadow-sm rounded-4 bg-danger-subtle p-5 text-center">
                                <i class="bi bi-shield-lock-fill text-danger fs-1 mb-3"></i>
                                <h5 class="fw-bold text-danger mb-2">Super Administrator Unrestricted Access</h5>
                                <p class="text-dark text-sm mb-0">This account holds the supreme university root authority. All permissions are permanently bypassed and cannot be restricted or overridden.</p>
                            </div>
                        @else
                            <form action="{{ route('superadmin.accessControl.updateUser', $selectedUser->id) }}" method="POST">
                                @csrf
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-bold mb-1">User & Admin Permission Management: {{ $selectedUser->name }}</h6>
                                            <p class="text-xs text-muted mb-0">
                                                Assign individual permission overrides specifically for this user.
                                                @if(is_array($selectedUser->custom_permissions) && !empty($selectedUser->custom_permissions))
                                                    <span class="badge bg-warning-soft text-dark border ms-1"><i class="bi bi-exclamation-triangle me-1"></i>Custom Overrides Active</span>
                                                @else
                                                    <span class="badge bg-success-soft text-success border ms-1"><i class="bi bi-shield-check me-1"></i>Inheriting 100% Dynamically from Role ({{ $selectedUser->role }})</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            @if(is_array($selectedUser->custom_permissions) && !empty($selectedUser->custom_permissions))
                                                <button type="submit" name="reset_overrides" value="1" class="btn btn-outline-danger btn-sm px-3" onclick="return confirm('Clear custom overrides and inherit 100% dynamically from role {{ $selectedUser->role }}?');"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset to Role Default</button>
                                            @endif
                                            <button type="submit" class="btn btn-primary btn-sm px-4"><i class="bi bi-check2-circle me-1"></i>Save User Overrides</button>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        @php
                                            $userCustom = is_array($selectedUser->custom_permissions) ? $selectedUser->custom_permissions : [];
                                            $baseRolePerms = \App\Models\Role::where('slug', $selectedUser->role)->first()?->permissions ?? [];
                                        @endphp

                                        <div class="alert alert-warning py-2 px-3 text-xs mb-4 rounded-3">
                                            <i class="bi bi-lightning-charge-fill me-1"></i> <strong>Live Permission Preview:</strong> Check boxes below to grant explicit overrides to this individual Admin user. Uncheck items to rely on their base role.
                                        </div>

                                        <div class="row g-3">
                                            @foreach($availablePermissions as $moduleKey => $module)
                                            <div class="col-md-6">
                                                <div class="card border h-100 rounded-3 shadow-sm">
                                                    <div class="card-header bg-light py-2 px-3 border-bottom">
                                                        <span class="fw-bold text-dark text-sm">{{ $module['label'] }}</span>
                                                    </div>
                                                    <div class="card-body p-3">
                                                        @foreach($module['actions'] as $permKey => $permLabel)
                                                        @php
                                                            $inRole = in_array('*', $baseRolePerms) || in_array($permKey, $baseRolePerms) || in_array(explode('.', $permKey)[0] . '.*', $baseRolePerms);
                                                            $isOverridden = in_array($permKey, $userCustom);
                                                        @endphp
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" name="custom_permissions[]" value="{{ $permKey }}" id="user_perm_{{ str_replace('.', '_', $permKey) }}" {{ ($inRole || $isOverridden) ? 'checked' : '' }}>
                                                            <label class="form-check-label text-sm text-dark" for="user_perm_{{ str_replace('.', '_', $permKey) }}">
                                                                {{ $permLabel }}
                                                                @if($inRole)
                                                                    <span class="badge bg-light text-muted border ms-1 text-xxs">Inherited from Role</span>
                                                                @elseif($isOverridden)
                                                                    <span class="badge bg-primary text-white ms-1 text-xxs">User Override</span>
                                                                @endif
                                                            </label>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @endif
                    @else
                        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                            <i class="bi bi-person-lines-fill text-muted fs-1 mb-3"></i>
                            <h6 class="fw-bold text-dark">No User Selected</h6>
                            <p class="text-sm text-muted mb-0">Please select an Admin or User account from the left dropdown menu to manage individual permission overrides.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- TAB 3: MODULE & MENU GATEKEEPER -->
        <!-- ========================================================= -->
        <div class="tab-pane fade {{ $activeTab === 'modules' ? 'show active' : '' }}" id="tab-modules" role="tabpanel">
            <div class="alert alert-success border-0 shadow-sm rounded-4 p-4 mb-4 d-flex align-items-center">
                <i class="bi bi-magic fs-2 text-success me-3 flex-shrink-0"></i>
                <div>
                    <h6 class="fw-bold text-dark mb-1">100% Dynamic Menu Rendering Active</h6>
                    <p class="text-sm text-dark mb-0">The application navigation sidebar (`layouts/app.blade.php`) is fully coupled to your permission engine. Granting a module permission below immediately causes that menu item to appear for authorized roles or individual users upon login/refresh. Removing the permission instantly hides and locks out access without any code changes or deployments.</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-1"><i class="bi bi-window-sidebar text-info me-2"></i>Dynamic Menu Visibility Rules</h6>
                            <p class="text-xs text-muted mb-0">Live mapping between navigation sidebar sections and required permission grants.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark text-sm d-block">Dashboard Navigation Hub</span>
                                        <small class="text-muted text-xs">Executive Overview & Institutional Dashboard</small>
                                    </div>
                                    <span class="badge bg-success">All Authenticated Roles</span>
                                </div>
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark text-sm d-block">Activity Capture Desk Menu</span>
                                        <small class="text-muted text-xs">Submit New Activity, Submission History & All College Activities</small>
                                    </div>
                                    <span class="badge bg-primary font-monospace">activities.view / create / view_approved_all</span>
                                </div>
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark text-sm d-block">Approval Authority Desk Menu</span>
                                        <small class="text-muted text-xs">Pending Verification & Approved/Rejected Archives</small>
                                    </div>
                                    <span class="badge bg-warning text-dark font-monospace">approvals.view / approve / reject / discuss</span>
                                </div>
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark text-sm d-block">Media Studio & Broadcasting Menu</span>
                                        <small class="text-muted text-xs">Stage 1 Editing & Stage 2 Ready to Post Queues</small>
                                    </div>
                                    <span class="badge bg-purple text-purple bg-purple-soft border font-monospace">media.view / edit / publish</span>
                                </div>
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark text-sm d-block">Administration Desk Menu</span>
                                        <small class="text-muted text-xs">Constituent Colleges & Users Matrix</small>
                                    </div>
                                    <span class="badge bg-blue text-blue bg-blue-soft border font-monospace">colleges.view / users.view</span>
                                </div>
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark text-sm d-block">Super Admin Control Panel Menu</span>
                                        <small class="text-muted text-xs">Access Control Hub, Workflows, Audit Trail, Master Settings</small>
                                    </div>
                                    <span class="badge bg-danger font-monospace">roles.view / workflows.view</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph text-success me-2"></i>Report & Notification Access Gatekeeper</h6>
                            <p class="text-xs text-muted mb-0">Govern access to exportable analytics, institutional reports, and automated notification triggers.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark text-sm d-block">Analytics & Intelligence Hub Menu</span>
                                        <small class="text-muted text-xs">Permits accessing institutional data visualizations</small>
                                    </div>
                                    <span class="badge bg-info text-dark font-monospace">analytics.view</span>
                                </div>
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark text-sm d-block">Executive CSV Export Engine</span>
                                        <small class="text-muted text-xs">Permits generating downloadable CSV activity reports</small>
                                    </div>
                                    <span class="badge bg-success font-monospace">analytics.export</span>
                                </div>
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark text-sm d-block">Notification & Email Broadcast Logs</span>
                                        <small class="text-muted text-xs">Permits inspecting automated email transmission logs</small>
                                    </div>
                                    <span class="badge bg-secondary font-monospace">emails.view</span>
                                </div>
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark text-sm d-block">Immutable Audit Trail Access</span>
                                        <small class="text-muted text-xs">Permits inspecting forensic security activity logs</small>
                                    </div>
                                    <span class="badge bg-dark font-monospace">audits.view</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- TAB 4: WORKFLOW & STATUS GATES -->
        <!-- ========================================================= -->
        <div class="tab-pane fade {{ $activeTab === 'workflows' ? 'show active' : '' }}" id="tab-workflows" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-1"><i class="bi bi-diagram-3-fill text-primary me-2"></i>Workflow & Approval Status Gatekeeper</h6>
                    <p class="text-xs text-muted mb-0">Configure status transition permissions and stage verification authority across multi-tiered workflows.</p>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-4 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-check2-square text-success me-2"></i>Approval Stage Execution Management</h6>
                                <p class="text-sm text-muted mb-3">Governs who can verify, endorse, or approve activity submissions at each sequential workflow stage.</p>
                                <ul class="list-unstyled text-sm mb-0">
                                    <li class="mb-2"><i class="bi bi-arrow-right-circle text-primary me-2"></i><strong>Registrar Verification Stage:</strong> Requires `approvals.approve` + assigned stage role mapping.</li>
                                    <li class="mb-2"><i class="bi bi-arrow-right-circle text-primary me-2"></i><strong>VC / Pro-VC Final Endorsement:</strong> Requires leadership role authorization.</li>
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i><strong>Return for Discussion:</strong> Unlocks submissions for revisions (Requires `approvals.discuss`).</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-sliders text-danger me-2"></i>Super Admin Status Overrides</h6>
                                <p class="text-sm text-muted mb-3">Super Admin holds universal bypass authority to override status locks or force re-submission.</p>
                                <a href="{{ route('superadmin.activities') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                    <i class="bi bi-gear-fill me-1"></i>Open Activity Override Manager
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- TAB 5: COLLEGE & DEPT SCOPE -->
        <!-- ========================================================= -->
        <div class="tab-pane fade {{ $activeTab === 'scopes' ? 'show active' : '' }}" id="tab-scopes" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-1"><i class="bi bi-building-fill text-primary me-2"></i>College-wise & Department-wise Permission Mapping</h6>
                    <p class="text-xs text-muted mb-0">Govern institutional data visibility boundaries across VMRF-DU's constituent colleges and administrative departments.</p>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 text-sm">
                            <thead class="bg-light text-xxs text-uppercase text-muted">
                                <tr>
                                    <th>Constituent College / Institution</th>
                                    <th>Assigned Admin Role</th>
                                    <th>Data Scope Enforcement Policy</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($colleges as $c)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $c->name }} <code class="ms-1 text-xs">{{ $c->code }}</code></td>
                                    <td><span class="badge bg-blue-soft text-blue border">College Admin / DEO</span></td>
                                    <td><span class="text-xs text-muted"><i class="bi bi-lock-fill me-1 text-warning"></i>Strictly bounded to College ID #{{ $c->id }} records</span></td>
                                    <td><span class="badge bg-success">Active Enforcement</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Copy Role Permissions Modal -->
    <div class="modal fade" id="copyPermissionsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('superadmin.accessControl.copyRole') }}" method="POST">
                @csrf
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold">Copy Role Permissions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-sm text-muted mb-3">Replicate all configured CRUD and module permissions from a source role to a target role.</p>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-sm">Source Role (Copy From)</label>
                            <select name="source_role_id" class="form-select" required>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->slug }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-sm">Target Role (Overwrite To)</label>
                            <select name="target_role_id" class="form-select" required>
                                @foreach($roles as $r)
                                    @if($r->slug !== 'super_admin')
                                        <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->slug }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-files me-1"></i>Copy Permissions</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Role Modal -->
    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form action="{{ route('superadmin.roles.store') }}" method="POST">
                @csrf
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold">Create Dynamic Role & Default Permissions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-sm">Role Slug <span class="text-danger">*</span></label>
                                <input type="text" name="slug" class="form-control" placeholder="e.g. associate_dean" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-sm">Role Display Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Associate Dean" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-sm">Description</label>
                                <input type="text" name="description" class="form-control" placeholder="Responsibilities overview">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-sm">Data Visibility Scope <span class="text-danger">*</span></label>
                                <select name="data_scope" class="form-select" required>
                                    <option value="college">College Scoped (Only own constituent college)</option>
                                    <option value="all">Universal / Global (All university activities)</option>
                                    <option value="self">Self Only (Only personal submissions)</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-key me-2"></i>Set Default Initial Permission Grants</h6>
                        <div class="row g-3">
                            @foreach($availablePermissions as $moduleKey => $module)
                            <div class="col-md-6">
                                <div class="card border h-100 rounded-3 shadow-sm">
                                    <div class="card-header bg-light py-2 px-3 border-bottom">
                                        <span class="fw-bold text-dark text-sm">{{ $module['label'] }}</span>
                                    </div>
                                    <div class="card-body p-3">
                                        @foreach($module['actions'] as $permKey => $permLabel)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permKey }}" id="create_access_perm_{{ str_replace('.', '_', $permKey) }}">
                                            <label class="form-check-label text-sm text-dark" for="create_access_perm_{{ str_replace('.', '_', $permKey) }}">
                                                {{ $permLabel }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Create Role</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
