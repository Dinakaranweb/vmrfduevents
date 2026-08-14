@extends('layouts.app')

@section('title', 'User Access & Role Management')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-shield-lock-fill text-primary me-2"></i>User Access Control & Role Directory</h3>
        <p class="text-muted mb-0">Manage institutional official roles (Super Admin, Registrar, VC, Pro VC, Media Editor, College DEO)</p>
    </div>
    <button class="btn btn-portal-primary shadow-sm" onclick="alert('User account onboarding form opened.')"><i class="bi bi-person-plus-fill me-1"></i> Register Official Account</button>
</div>

<div class="portal-card p-4 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Official Name & Email</th>
                    <th>System Role</th>
                    <th>Assigned Campus</th>
                    <th>Contact Phone</th>
                    <th>Account Status</th>
                    <th class="text-end">Management</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                        <div class="d-flex align-items-center gap-3">
                            @php
                                $rowAvatar = $user->avatar ?: (!empty($globalDefaultAvatar) ? $globalDefaultAvatar : null);
                            @endphp
                            @if($rowAvatar)
                                <img src="{{ asset($rowAvatar) }}" alt="Avatar" class="rounded-circle border shadow-sm flex-shrink-0" style="width: 38px; height: 38px; object-fit: cover;">
                            @else
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <div class="fw-bold text-dark">{{ $user->name }}</div>
                                <div class="text-muted text-xs">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $user->role === 'super_admin' ? 'bg-danger' : (in_array($user->role, ['registrar', 'office_registrar', 'vc', 'pro_vc']) ? 'bg-warning text-dark' : 'bg-info text-dark') }}">
                            {{ $user->role_title }}
                        </span>
                    </td>
                    <td class="small">{{ $user->designation ?: 'Staff Officer' }}</td>
                    <td>
                        <div class="fw-semibold small">{{ $user->college ? $user->college->code : 'Central Administration HQ' }}</div>
                        <div class="text-muted text-xs">{{ $user->college ? $user->college->name : '' }}</div>
                    </td>
                    <td class="small">{{ $user->phone ?: 'N/A' }}</td>
                    <td><span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $user->is_active ? 'Active & Verified' : 'Locked' }}</span></td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-light border" onclick="alert('Edit permissions for {{ addslashes($user->name) }}')">
                            <i class="bi bi-pencil-square text-primary"></i> Edit Permissions
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
