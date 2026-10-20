@extends('layouts.app')

@section('title', 'My Account & Security Profile - VMRFDU')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark"><i class="bi bi-person-badge-fill text-primary me-2"></i>User Profile & Security Control</h1>
            <p class="text-muted mb-0">Manage personal contact details and update your security credentials.</p>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary p-2 px-3 fs-6">
            <i class="bi bi-shield-check me-1"></i>Security Status: {{ strtoupper($currentUser->account_status ?? 'ACTIVE') }}
        </span>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm fw-bold" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-3 shadow-sm">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Sidebar: Profile Summary & System Assigned Fields -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center mb-4 bg-white">
                <div class="position-relative d-inline-block mx-auto mb-3">
                    @php
                        $displayAvatar = $currentUser->avatar ?: (!empty($globalDefaultAvatar) ? $globalDefaultAvatar : null);
                        $avatarUrl = $displayAvatar ? (str_starts_with($displayAvatar, 'http') ? $displayAvatar : upload_url($displayAvatar)) : null;
                    @endphp
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" class="rounded-circle shadow-sm object-fit-cover" style="width: 100px; height: 100px;" alt="Profile Avatar" onerror="this.style.display='none'; document.getElementById('profile-avatar-fallback').style.display='inline-flex';">
                        <div id="profile-avatar-fallback" class="text-white rounded-circle align-items-center justify-content-center fw-bold fs-1 shadow-sm" style="width: 100px; height: 100px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); display: none;">
                            {{ substr($currentUser->name, 0, 1) }}
                        </div>
                    @else
                        <div class="text-white rounded-circle d-inline-flex align-items-center justify-content-center fw-bold fs-1 shadow-sm" style="width: 100px; height: 100px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                            {{ substr($currentUser->name, 0, 1) }}
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-3 border-white" style="width: 22px; height: 22px;" title="Online"></span>
                </div>
                <h5 class="fw-bold text-dark mb-1">{{ $currentUser->name }}</h5>
                <p class="text-muted text-xs mb-3 font-monospace">{{ $currentUser->email }}</p>
                <span class="badge bg-dark text-white px-3 py-2 rounded-pill fw-bold text-uppercase mb-3">
                    {{ str_replace('_', ' ', $currentUser->role) }}
                </span>

                <div class="border-top pt-3 text-start">
                    <span class="text-xxs fw-bold text-muted text-uppercase d-block mb-1">Assigned Constituent College</span>
                    <div class="p-2 bg-light rounded-3 mb-3 text-sm fw-bold text-dark d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-building text-primary me-2"></i>{{ $currentUser->college ? $currentUser->college->name : 'Central University Authority' }}</span>
                        <i class="bi bi-lock-fill text-muted" title="Managed by Super Admin"></i>
                    </div>

                    <span class="text-xxs fw-bold text-muted text-uppercase d-block mb-1">Account Lifecycle Status</span>
                    <div class="p-2 bg-light rounded-3 mb-3 text-sm fw-bold text-dark d-flex align-items-center justify-content-between">
                        <span class="text-{{ $currentUser->account_status === 'locked' ? 'danger' : 'success' }} text-uppercase">
                            <i class="bi bi-circle-fill me-2 text-xs"></i>{{ $currentUser->account_status ?? 'Active' }}
                        </span>
                        <i class="bi bi-lock-fill text-muted" title="Managed by Super Admin"></i>
                    </div>

                    <span class="text-xxs fw-bold text-muted text-uppercase d-block mb-1">System Permissions</span>
                    <p class="text-xxs text-muted mb-0">
                        <i class="bi bi-info-circle me-1"></i>Certain institutional fields (Role, College, Account State) are system-controlled and strictly editable only by the Super Administrator.
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Content: Tabs for Update Profile, Change Password, and Audit History -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-0">
                    <ul class="nav nav-tabs nav-justified border-0" id="profileTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-bold py-3 text-dark border-0 border-bottom border-primary border-3" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit-pane" type="button" role="tab">
                                <i class="bi bi-pencil-square me-2"></i>Edit Profile Info
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold py-3 text-dark border-0" id="security-tab" data-bs-toggle="tab" data-bs-target="#security-pane" type="button" role="tab">
                                <i class="bi bi-key-fill me-2"></i>Change Password
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4 tab-content">
                    <!-- Tab 1: Edit Profile -->
                    <div class="tab-pane fade show active" id="edit-pane" role="tabpanel">
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold text-sm">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $currentUser->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-sm">Official Email Address <span class="text-muted">(System Protected)</span></label>
                                <input type="email" class="form-control bg-light" value="{{ $currentUser->email }}" disabled readonly>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-sm">Mobile / Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $currentUser->phone }}" placeholder="+91 9876543210">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-sm">Official Designation</label>
                                    <input type="text" name="designation" class="form-control" value="{{ $currentUser->designation }}" placeholder="e.g. Associate Professor">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-sm">Update Profile Photo / Avatar</label>
                                <input type="file" name="avatar_file" class="form-control accept-images">
                                <div class="form-text text-xxs">Supported formats: JPG, PNG. Maximum file size: 2MB.</div>
                            </div>
                            <div class="text-end border-top pt-3">
                                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Save Profile Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab 2: Change Password -->
                    <div class="tab-pane fade" id="security-pane" role="tabpanel">
                        <form action="{{ route('profile.password') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold text-sm">Current Password <span class="text-danger">*</span></label>
                                <input type="password" name="current_password" class="form-control" required placeholder="Enter current password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-sm">New Password <span class="text-danger">*</span></label>
                                <input type="password" name="new_password" class="form-control" required minlength="6" placeholder="Enter new secure password">
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-sm">Confirm New Password <span class="text-danger">*</span></label>
                                <input type="password" name="new_password_confirmation" class="form-control" required minlength="6" placeholder="Re-enter new password">
                            </div>
                            <div class="text-end border-top pt-3">
                                <button type="submit" class="btn btn-danger px-4"><i class="bi bi-shield-lock me-1"></i>Update Security Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
