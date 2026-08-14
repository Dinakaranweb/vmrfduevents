@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark"><i class="bi bi-people-fill text-primary me-2"></i>User & Role Lifecycle Management</h1>
            <p class="text-muted mb-0">Create users, edit profile details & assigned roles, reset passwords, lock/suspend accounts, mandate password resets, and transfer users.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="bi bi-person-plus me-1"></i>Create New User
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Users Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted text-xxs text-uppercase">
                    <tr>
                        <th class="ps-4">User Details</th>
                        <th>Role Assigned</th>
                        <th>Mapped College</th>
                        <th>Account Status</th>
                        <th>Security Controls</th>
                        <th class="text-end pe-4">Super Admin Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                @php
                                    $rowAvatar = $user->avatar ?: (!empty($globalDefaultAvatar) ? $globalDefaultAvatar : null);
                                @endphp
                                @if($rowAvatar)
                                    <img src="{{ asset($rowAvatar) }}" alt="Avatar" class="rounded-circle border shadow-sm flex-shrink-0" style="width: 38px; height: 38px; object-fit: cover;">
                                @else
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0 shadow-sm" style="width: 38px; height: 38px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $user->name }}</div>
                                    <div class="text-muted text-xs">{{ $user->email }}</div>
                                    @if($user->designation || $user->phone)
                                        <div class="text-xxs text-secondary mt-1"><i class="bi bi-person-badge me-1"></i>{{ $user->designation ?? 'Staff' }} | {{ $user->phone ?? 'No Phone' }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $user->role === 'super_admin' ? 'warning text-dark' : ($user->role === 'college_admin' ? 'primary' : 'info text-dark') }} text-uppercase">
                                {{ str_replace('_', ' ', $user->role) }}
                            </span>
                        </td>
                        <td>
                            @if($user->college)
                                <span class="badge bg-light text-dark mb-1">{{ $user->college->code }}</span>
                            @else
                                <span class="badge bg-secondary mb-1">Global / University</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_locked || $user->account_status === 'locked')
                                <span class="badge bg-danger"><i class="bi bi-lock-fill me-1"></i>Locked</span>
                            @elseif($user->account_status === 'suspended')
                                <span class="badge bg-dark"><i class="bi bi-slash-circle me-1"></i>Suspended</span>
                            @elseif($user->account_status === 'inactive' || !$user->is_active)
                                <span class="badge bg-warning text-dark">Inactive</span>
                            @else
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                            @endif
                        </td>
                        <td>
                            @if($user->force_password_reset)
                                <span class="badge bg-warning text-dark border border-warning"><i class="bi bi-exclamation-triangle me-1"></i>Reset Mandated</span>
                            @else
                                <span class="badge bg-light text-muted">Normal Session</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <!-- Edit Profile Details & Role -->
                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}" title="Edit Profile & Role">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <!-- Change Lifecycle Status -->
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#statusModal{{ $user->id }}" title="Change Account Status">
                                    <i class="bi bi-shield-lock"></i>
                                </button>
                                <!-- Transfer College -->
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#transferUserModal{{ $user->id }}" title="Transfer College">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                                <!-- Reset Password -->
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $user->id }}" title="Reset Password">
                                    <i class="bi bi-key"></i>
                                </button>
                                <!-- Quick Lock/Unlock -->
                                <form action="{{ route('superadmin.users.toggleLock', $user->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $user->is_locked ? 'success' : 'dark' }}" title="{{ $user->is_locked ? 'Unlock Account' : 'Lock Account' }}">
                                        <i class="bi bi-{{ $user->is_locked ? 'unlock' : 'lock' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit User Modal -->
                    <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <form action="{{ route('superadmin.users.update', $user->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title fw-bold">Edit User Profile & Role: {{ $user->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-start">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-sm">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-sm">Official Email Address <span class="text-danger">*</span></label>
                                                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                            </div>
                                        </div>
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-sm">Mobile / Phone Number</label>
                                                <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" placeholder="+91 9876543210">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-sm">Official Designation</label>
                                                <input type="text" name="designation" class="form-control" value="{{ $user->designation }}" placeholder="e.g. Dean / Professor">
                                            </div>
                                        </div>
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-sm">Assigned Role <span class="text-danger">*</span></label>
                                                <select name="role" class="form-select" required>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->slug }}" {{ $user->role == $role->slug ? 'selected' : '' }}>{{ $role->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-sm">Account Lifecycle Status</label>
                                                <select name="account_status" class="form-select" required>
                                                    <option value="active" {{ ($user->account_status ?? 'active') === 'active' ? 'selected' : '' }}>Active (Full Access)</option>
                                                    <option value="inactive" {{ ($user->account_status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive (Access Paused)</option>
                                                    <option value="locked" {{ ($user->account_status ?? 'active') === 'locked' || $user->is_locked ? 'selected' : '' }}>Locked (Security Lockout)</option>
                                                    <option value="suspended" {{ ($user->account_status ?? 'active') === 'suspended' ? 'selected' : '' }}>Suspended (Administrative Suspension)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">Constituent College</label>
                                            <select name="college_id" class="form-select">
                                                <option value="">Global / University Authority (No College)</option>
                                                @foreach($colleges as $col)
                                                    <option value="{{ $col->id }}" {{ $user->college_id == $col->id ? 'selected' : '' }}>{{ $col->code }} - {{ $col->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-check mt-3 border-top pt-3">
                                            <input class="form-check-input" type="checkbox" name="force_password_reset" value="1" id="forceResetEdit{{ $user->id }}" {{ $user->force_password_reset ? 'checked' : '' }}>
                                            <label class="form-check-label text-sm fw-bold" for="forceResetEdit{{ $user->id }}">
                                                Force password reset on user's next login
                                            </label>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Profile & Role Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Status Modal -->
                    <div class="modal fade" id="statusModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('superadmin.users.changeStatus', $user->id) }}" method="POST">
                                @csrf
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title fw-bold">Lifecycle Status: {{ $user->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-start">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">Select Account Lifecycle State</label>
                                            <select name="account_status" class="form-select" required>
                                                <option value="active" {{ ($user->account_status ?? 'active') === 'active' ? 'selected' : '' }}>Active (Full Access)</option>
                                                <option value="inactive" {{ ($user->account_status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive (Access Paused)</option>
                                                <option value="locked" {{ ($user->account_status ?? 'active') === 'locked' || $user->is_locked ? 'selected' : '' }}>Locked (Security Lockout)</option>
                                                <option value="suspended" {{ ($user->account_status ?? 'active') === 'suspended' ? 'selected' : '' }}>Suspended (Administrative Suspension)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update Lifecycle Status</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Transfer Modal -->
                    <div class="modal fade" id="transferUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('superadmin.users.transfer', $user->id) }}" method="POST">
                                @csrf
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title fw-bold">Transfer User College: {{ $user->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-start">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">Select Target College</label>
                                            <select name="new_college_id" class="form-select" required>
                                                @foreach($colleges as $col)
                                                    <option value="{{ $col->id }}" {{ $user->college_id == $col->id ? 'selected' : '' }}>{{ $col->code }} - {{ $col->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Transfer User</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Reset Password Modal -->
                    <div class="modal fade" id="resetPasswordModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('superadmin.users.resetPassword', $user->id) }}" method="POST">
                                @csrf
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title fw-bold">Reset Password: {{ $user->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-start">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">New Password <span class="text-danger">*</span></label>
                                            <input type="text" name="new_password" class="form-control" required minlength="6" placeholder="Enter new password">
                                        </div>
                                        <div class="form-check mt-3">
                                            <input class="form-check-input" type="checkbox" name="force_password_reset" value="1" id="forceReset{{ $user->id }}" checked>
                                            <label class="form-check-label text-sm fw-bold" for="forceReset{{ $user->id }}">
                                                Force password reset on next login
                                            </label>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Reset Password</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('superadmin.users.store') }}" method="POST">
                @csrf
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold">Create New Portal Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 text-start">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-sm">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-sm">Official Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-sm">Mobile / Phone Number</label>
                                <input type="text" name="phone" class="form-control" placeholder="+91 9876543210">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-sm">Official Designation</label>
                                <input type="text" name="designation" class="form-control" placeholder="e.g. Dean / Professor">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-sm">Initial Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-sm">Initial Status <span class="text-danger">*</span></label>
                                <select name="account_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="locked">Locked</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-sm">Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->slug }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-sm">Assign College</label>
                                <select name="college_id" class="form-select">
                                    <option value="">Global / University Authority (No College)</option>
                                    @foreach($colleges as $col)
                                        <option value="{{ $col->id }}">{{ $col->code }} - {{ $col->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-check mt-3 border-top pt-3">
                            <input class="form-check-input" type="checkbox" name="force_password_reset" value="1" id="forceResetCreate" checked>
                            <label class="form-check-label text-sm fw-bold" for="forceResetCreate">
                                Force password reset on user's first login
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create User Account</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
