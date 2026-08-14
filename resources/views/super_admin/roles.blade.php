@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark"><i class="bi bi-person-badge text-primary me-2"></i>Dynamic Role & Permission Engine</h1>
            <p class="text-muted mb-0">Create new roles, configure data scopes (Global vs College vs Self), and assign CRUD/approval privileges.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
            <i class="bi bi-plus-circle me-1"></i>Create New Role
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted text-xxs text-uppercase">
                    <tr>
                        <th class="ps-4">Slug</th>
                        <th>Role Name</th>
                        <th>Description</th>
                        <th>Data Scope</th>
                        <th>Type</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td class="ps-4 font-monospace text-xs text-muted">{{ $role->slug }}</td>
                        <td class="fw-bold text-dark">{{ $role->name }}</td>
                        <td class="text-sm text-muted">{{ $role->description }}</td>
                        <td>
                            <span class="badge bg-{{ $role->data_scope === 'all' ? 'warning text-dark' : 'primary' }} text-uppercase">
                                {{ $role->data_scope === 'all' ? 'Universal / Global' : ($role->data_scope === 'college' ? 'College Scoped' : 'Self Only') }}
                            </span>
                        </td>
                        <td>
                            @if($role->is_system)
                                <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i>Core System</span>
                            @else
                                <span class="badge bg-success">Custom Role</span>
                            @endif
                        </td>
                        <td class="text-end pe-4 d-flex justify-content-end align-items-center gap-2">
                            @if($role->slug !== 'super_admin')
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPermissionsModal{{ $role->id }}">
                                    <i class="bi bi-shield-lock me-1"></i>Configure Permissions
                                </button>
                            @else
                                <span class="badge bg-danger text-white"><i class="bi bi-star-fill me-1"></i>All CRUD Rights Granted</span>
                            @endif

                            @if(!$role->is_system)
                                <form action="{{ route('superadmin.roles.delete', $role->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this role?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Role"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>

                    <!-- Configure Permissions Modal -->
                    @if($role->slug !== 'super_admin')
                    <div class="modal fade" id="editPermissionsModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <form action="{{ route('superadmin.roles.update', $role->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="name" value="{{ $role->name }}">
                                <input type="hidden" name="description" value="{{ $role->description }}">
                                <input type="hidden" name="data_scope" value="{{ $role->data_scope }}">
                                
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom bg-light">
                                        <div>
                                            <h5 class="modal-title fw-bold mb-0">Configure Permissions: {{ $role->name }}</h5>
                                            <p class="text-xs text-muted mb-0 font-monospace">{{ $role->slug }} &bull; Scope: {{ strtoupper($role->data_scope) }}</p>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="alert alert-info py-2 px-3 text-xs mb-4 rounded-3">
                                            <i class="bi bi-info-circle-fill me-1"></i> Select the specific modules and CRUD actions explicitly granted to users assigned to this role.
                                        </div>

                                        @php
                                            $currentRolePerms = is_array($role->permissions) && !empty($role->permissions)
                                                ? $role->permissions
                                                : \App\Models\Role::getDefaultPermissions($role->slug);
                                        @endphp

                                        <div class="row g-3">
                                            @foreach($availablePermissions as $moduleKey => $module)
                                            <div class="col-md-6">
                                                <div class="card border h-100 rounded-3 shadow-sm">
                                                    <div class="card-header bg-light py-2 px-3 border-bottom">
                                                        <span class="fw-bold text-dark text-sm"><i class="bi bi-folder2-open text-primary me-2"></i>{{ $module['label'] }}</span>
                                                    </div>
                                                    <div class="card-body p-3">
                                                        @foreach($module['actions'] as $permKey => $permLabel)
                                                        @php
                                                            $isChecked = in_array('*', $currentRolePerms) || in_array($permKey, $currentRolePerms) || in_array(explode('.', $permKey)[0] . '.*', $currentRolePerms);
                                                        @endphp
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permKey }}" id="perm_{{ $role->id }}_{{ str_replace('.', '_', $permKey) }}" {{ $isChecked ? 'checked' : '' }}>
                                                            <label class="form-check-label text-sm text-dark" for="perm_{{ $role->id }}_{{ str_replace('.', '_', $permKey) }}">
                                                                {{ $permLabel }}
                                                                <br><small class="text-muted font-monospace" style="font-size: 0.7rem;">({{ $permKey }})</small>
                                                            </label>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top bg-light">
                                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary btn-sm px-4"><i class="bi bi-save me-1"></i>Save Permissions</button>
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

    <!-- Create Role Modal -->
    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form action="{{ route('superadmin.roles.store') }}" method="POST">
                @csrf
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold">Create Dynamic Role</h5>
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

                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-key me-2"></i>Initial Permission Grants</h6>
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
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permKey }}" id="create_perm_{{ str_replace('.', '_', $permKey) }}">
                                            <label class="form-check-label text-sm text-dark" for="create_perm_{{ str_replace('.', '_', $permKey) }}">
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
