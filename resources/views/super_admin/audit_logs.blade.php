@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark"><i class="bi bi-journal-text text-success me-2"></i>Immutable System Audit Trail</h1>
            <p class="text-muted mb-0">Read-only, tamper-proof audit log tracking all user logins, data mutations, approvals, and system configurations.</p>
        </div>
        <span class="badge bg-success bg-opacity-10 text-success border border-success p-2"><i class="bi bi-shield-lock-fill me-1"></i>Tamper-Proof & Immutable</span>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-light">
        <form method="GET" action="{{ route('superadmin.auditLogs') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <select name="event_type" class="form-select">
                    <option value="">Filter by Event Category (All Events)</option>
                    <option value="login" {{ request('event_type') == 'login' ? 'selected' : '' }}>User Logins</option>
                    <option value="activity_create" {{ request('event_type') == 'activity_create' ? 'selected' : '' }}>Activity Creation</option>
                    <option value="activity_update" {{ request('event_type') == 'activity_update' ? 'selected' : '' }}>Activity Modifications</option>
                    <option value="status_change" {{ request('event_type') == 'status_change' ? 'selected' : '' }}>Status Overrides & Changes</option>
                    <option value="user_management" {{ request('event_type') == 'user_management' ? 'selected' : '' }}>User Management Mutations</option>
                    <option value="college_management" {{ request('event_type') == 'college_management' ? 'selected' : '' }}>College Management Mutations</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i>Filter Logs</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('superadmin.auditLogs') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0 font-monospace text-xs">
                <thead class="bg-dark text-white text-xxs text-uppercase font-sans">
                    <tr>
                        <th class="ps-4 py-3">Timestamp</th>
                        <th>Event Type</th>
                        <th>User / Actor</th>
                        <th>Target Record</th>
                        <th>Description / Mutation Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="ps-4 text-muted">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <span class="badge bg-{{ $log->event_type === 'status_change' || str_contains($log->event_type, 'delete') ? 'danger' : 'primary' }} text-uppercase">
                                {{ $log->event_type }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark font-sans">{{ $log->user_name }}</div>
                            <div class="text-xxs text-muted text-uppercase">{{ $log->user_role }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $log->target_type ?? 'System' }} #{{ $log->target_id }}</span></td>
                        <td class="font-sans text-sm text-dark">{{ $log->description }}</td>
                        <td class="text-muted">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted font-sans">No audit events recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top p-3 font-sans">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
