@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">
                <i class="bi bi-journal-check text-primary me-2"></i>Enterprise System &amp; Email Logs Hub
            </h1>
            <p class="text-muted mb-0 font-sans text-sm">
                Comprehensive diagnostic, user tracking, media storage, error monitoring, and email transmission audit logs.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('superadmin.emailHub', ['tab' => 'test']) }}" class="btn btn-outline-primary btn-sm rounded-pill font-sans">
                <i class="bi bi-send-check me-1"></i>Test Email Delivery
            </a>
            <a href="{{ route('superadmin.logsHub.systemFile') }}" target="_blank" class="btn btn-dark btn-sm rounded-pill font-sans">
                <i class="bi bi-download me-1"></i>Download raw laravel.log
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills nav-fill bg-white border rounded-4 p-2 mb-4 shadow-sm font-sans gap-2" id="logsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link rounded-3 py-2 px-3 {{ $activeTab == 'user_activity' ? 'active fw-bold shadow-sm' : 'text-dark' }}" 
               href="{{ route('superadmin.logsHub', ['tab' => 'user_activity']) }}">
                <i class="bi bi-person-lines-fill me-2"></i>User Activity &amp; Audit Trail
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link rounded-3 py-2 px-3 {{ $activeTab == 'system_activity' ? 'active fw-bold shadow-sm' : 'text-dark' }}" 
               href="{{ route('superadmin.logsHub', ['tab' => 'system_activity']) }}">
                <i class="bi bi-cpu-fill me-2"></i>System Activity Logs
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link rounded-3 py-2 px-3 {{ $activeTab == 'file_uploads' ? 'active fw-bold shadow-sm' : 'text-dark' }}" 
               href="{{ route('superadmin.logsHub', ['tab' => 'file_uploads']) }}">
                <i class="bi bi-cloud-arrow-up-fill me-2"></i>File Upload Logs
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link rounded-3 py-2 px-3 {{ $activeTab == 'errors' ? 'active fw-bold shadow-sm' : 'text-dark' }}" 
               href="{{ route('superadmin.logsHub', ['tab' => 'errors']) }}">
                <i class="bi bi-exclamation-octagon-fill me-2"></i>Error Logs
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link rounded-3 py-2 px-3 {{ $activeTab == 'emails' ? 'active fw-bold shadow-sm bg-indigo text-white' : 'text-dark' }}" 
               href="{{ route('superadmin.logsHub', ['tab' => 'emails']) }}" style="{{ $activeTab == 'emails' ? 'background-color: #4f46e5;' : '' }}">
                <i class="bi bi-envelope-paper-fill me-2"></i>Email Transmission Logs
            </a>
        </li>
    </ul>

    <!-- Tab Content Container -->
    <div class="tab-content" id="logsTabContent">

        <!-- ========================================================= -->
        <!-- TAB 1: USER ACTIVITY / AUDIT LOGS -->
        <!-- ========================================================= -->
        <div class="tab-pane fade {{ $activeTab == 'user_activity' ? 'show active' : '' }}" id="userActivityPane">
            <div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-light font-sans">
                <form method="GET" action="{{ route('superadmin.logsHub') }}" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="user_activity">
                    <div class="col-md-5">
                        <select name="event_type" class="form-select text-sm">
                            <option value="">Filter by Event Category (All Events)</option>
                            <option value="login" {{ request('event_type') == 'login' ? 'selected' : '' }}>User Logins</option>
                            <option value="activity_create" {{ request('event_type') == 'activity_create' ? 'selected' : '' }}>Activity Creation</option>
                            <option value="activity_update" {{ request('event_type') == 'activity_update' ? 'selected' : '' }}>Activity Modifications</option>
                            <option value="status_change" {{ request('event_type') == 'status_change' ? 'selected' : '' }}>Status Overrides &amp; Changes</option>
                            <option value="user_management" {{ request('event_type') == 'user_management' ? 'selected' : '' }}>User Management Mutations</option>
                            <option value="college_management" {{ request('event_type') == 'college_management' ? 'selected' : '' }}>College Management Mutations</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="user_id" class="form-select text-sm">
                            <option value="">Filter by Actor / User (All Users)</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 text-sm"><i class="bi bi-filter me-1"></i>Filter</button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('superadmin.logsHub', ['tab' => 'user_activity']) }}" class="btn btn-outline-secondary w-100 text-sm">Reset</a>
                    </div>
                </form>
            </div>

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
                            @forelse($auditLogs as $log)
                            <tr>
                                <td class="ps-4 text-muted">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <span class="badge bg-{{ $log->event_type === 'status_change' || str_contains($log->event_type, 'delete') ? 'danger' : 'primary' }} text-uppercase">
                                        {{ str_replace('_', ' ', $log->event_type) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark font-sans">{{ $log->user_name }}</div>
                                    <div class="text-xxs text-muted text-uppercase">{{ $log->user_role }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $log->target_type ?? 'System' }} #{{ $log->target_id }}</span></td>
                                <td class="font-sans text-sm text-dark">{{ $log->description }}</td>
                                <td class="text-muted">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted font-sans">No user activity audit events found matching the criteria.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top p-3 font-sans">
                    {{ $auditLogs->links() }}
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- TAB 2: SYSTEM ACTIVITY LOGS -->
        <!-- ========================================================= -->
        <div class="tab-pane fade {{ $activeTab == 'system_activity' ? 'show active' : '' }}" id="systemActivityPane">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3 px-4 font-sans">
                    <span class="fw-bold"><i class="bi bi-terminal me-2"></i>Latest System Application Events (`laravel.log`)</span>
                    <span class="badge bg-secondary">Showing last 100 entries</span>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0 font-monospace text-xs">
                        <thead class="bg-light text-dark text-xxs text-uppercase font-sans">
                            <tr>
                                <th class="ps-4 py-2">Timestamp</th>
                                <th>Environment</th>
                                <th>Severity Level</th>
                                <th>System Message / Event Trace</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($systemLogEntries as $entry)
                            <tr>
                                <td class="ps-4 text-muted whitespace-nowrap">{{ $entry['timestamp'] }}</td>
                                <td><span class="badge bg-secondary">{{ $entry['env'] }}</span></td>
                                <td>
                                    <span class="badge bg-{{ in_array($entry['level'], ['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT']) ? 'danger' : ($entry['level'] == 'WARNING' ? 'warning text-dark' : 'info') }}">
                                        {{ $entry['level'] }}
                                    </span>
                                </td>
                                <td class="font-sans text-xs text-break text-dark">{{ $entry['message'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted font-sans">No recent system activity events recorded in laravel.log.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- TAB 3: FILE UPLOAD LOGS -->
        <!-- ========================================================= -->
        <div class="tab-pane fade {{ $activeTab == 'file_uploads' ? 'show active' : '' }}" id="fileUploadsPane">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4 font-sans d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="bi bi-folder2-open text-info me-2"></i>Recent Media &amp; Attachment Uploads</span>
                    <span class="text-muted text-xs">Tracking all submitted media across campus nodes</span>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0 font-sans text-sm">
                        <thead class="bg-light text-dark text-xxs text-uppercase">
                            <tr>
                                <th class="ps-4 py-3">Preview / Type</th>
                                <th>File Name &amp; Storage Path</th>
                                <th>Uploader / User</th>
                                <th>Linked Reference ID</th>
                                <th>Media Category</th>
                                <th>Upload Timestamp</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fileLogs as $file)
                            <tr>
                                <td class="ps-4">
                                    @if($file->file_type === 'image')
                                        <img src="{{ asset($file->file_path) }}" alt="Thumb" class="rounded border" style="width: 45px; height: 45px; object-fit: cover;">
                                    @elseif($file->file_type === 'video')
                                        <div class="bg-dark text-white rounded d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;"><i class="bi bi-play-circle-fill text-lg"></i></div>
                                    @else
                                        <div class="bg-light text-primary rounded border d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;"><i class="bi bi-file-earmark-text text-lg"></i></div>
                                    @endif
                                </td>
                                <td class="font-monospace text-xs text-dark">
                                    <div>{{ $file->original_name ?? basename($file->file_path) }}</div>
                                    <div class="text-muted text-xxs">{{ $file->file_path }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $file->submission ? $file->submission->submitter_name : 'System Upload' }}</div>
                                    <div class="text-xs text-muted">{{ $file->submission && $file->submission->user ? $file->submission->user->email : '' }}</div>
                                </td>
                                <td>
                                    @if($file->submission)
                                        <a href="{{ route('submissions.show', $file->submission->id) }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary text-decoration-none font-monospace">
                                            {{ $file->submission->reference_id }}
                                        </a>
                                    @else
                                        <span class="badge bg-secondary">Unlinked</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-info text-dark text-xxs">{{ strtoupper($file->category ?? 'Attachment') }}</span></td>
                                <td class="text-muted text-xs font-monospace">{{ $file->created_at->format('d M, Y H:i:s') }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ asset($file->file_path) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>View File
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No media upload records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top p-3 font-sans">
                    {{ $fileLogs->links() }}
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- TAB 4: ERROR LOGS -->
        <!-- ========================================================= -->
        <div class="tab-pane fade {{ $activeTab == 'errors' ? 'show active' : '' }}" id="errorsPane">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden border-top border-4 border-danger">
                <div class="card-header bg-danger bg-opacity-10 text-danger py-3 px-4 font-sans d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>System Error &amp; Exception Logs (`laravel.log`)</span>
                    <span class="badge bg-danger">Critical Alerts Only</span>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0 font-monospace text-xs">
                        <thead class="bg-light text-dark text-xxs text-uppercase font-sans">
                            <tr>
                                <th class="ps-4 py-3">Timestamp</th>
                                <th>Level</th>
                                <th>Error / Exception Trace Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($errorLogEntries as $error)
                            <tr>
                                <td class="ps-4 text-muted whitespace-nowrap">{{ $error['timestamp'] }}</td>
                                <td><span class="badge bg-danger px-2 py-1">{{ $error['level'] }}</span></td>
                                <td class="font-sans text-xs text-dark text-break py-2">{{ $error['message'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-success font-sans fw-bold">
                                    <i class="bi bi-check-circle-fill text-lg d-block mb-2"></i>
                                    System clean! Zero error logs detected.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- TAB 5: EMAIL TRANSMISSION LOGS -->
        <!-- ========================================================= -->
        <div class="tab-pane fade {{ $activeTab == 'emails' ? 'show active' : '' }}" id="emailsPane">
            <div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-light font-sans">
                <form method="GET" action="{{ route('superadmin.logsHub') }}" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="emails">
                    <div class="col-md-3">
                        <input type="text" name="reference_id" class="form-control text-sm font-monospace" placeholder="Filter by Reference ID (e.g. VMRF-2026)..." value="{{ request('reference_id') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="recipient_email" class="form-control text-sm" placeholder="Filter by Recipient Email..." value="{{ request('recipient_email') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select text-sm">
                            <option value="">All Statuses</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent Successfully</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed Delivery</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Queued / Pending</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="subject" class="form-control text-sm" placeholder="Filter by Subject..." value="{{ request('subject') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100 text-sm"><i class="bi bi-search"></i></button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('superadmin.logsHub', ['tab' => 'emails']) }}" class="btn btn-outline-secondary w-100 text-sm">Clear</a>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0 font-sans text-sm">
                        <thead class="bg-dark text-white text-xxs text-uppercase">
                            <tr>
                                <th class="ps-4 py-3">Timestamp</th>
                                <th>Sender Authority</th>
                                <th>Recipient Particular</th>
                                <th>Reference ID</th>
                                <th>Subject &amp; Stage Badge</th>
                                <th>Delivery Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($emailLogs as $log)
                            <tr>
                                <td class="ps-4 text-muted text-xs font-monospace whitespace-nowrap">
                                    {{ $log->created_at->format('d M, Y H:i:s') }}
                                </td>
                                <td>
                                    <div class="fw-bold text-xs text-dark">{{ $log->sender ?? 'VMRFDU Portal Notification Desk' }}</div>
                                    <div class="text-xxs text-muted">System Automated</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $log->recipient_email }}</div>
                                    @if($log->user)
                                        <div class="text-xxs text-muted"><i class="bi bi-person me-1"></i>{{ $log->user->name }} ({{ $log->user->role }})</div>
                                    @endif
                                </td>
                                <td>
                                    @if($log->reference_id)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary font-monospace text-xs">
                                            {{ $log->reference_id }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted">General / N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark text-break" style="max-width: 250px;">{{ $log->subject }}</div>
                                    <span class="badge bg-secondary text-xxs mt-1 text-uppercase">{{ $log->stage }}</span>
                                </td>
                                <td>
                                    @if($log->status === 'sent')
                                        <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Sent</span>
                                    @elseif($log->status === 'failed')
                                        <span class="badge bg-danger px-2 py-1"><i class="bi bi-x-circle-fill me-1"></i>Failed</span>
                                        @if($log->error_message)
                                            <div class="text-danger text-xxs mt-1 font-monospace" style="max-width: 180px;">
                                                <i class="bi bi-exclamation-triangle me-1"></i>{{ $log->error_message }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-clock-history me-1"></i>{{ ucfirst($log->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill font-sans shadow-sm" 
                                            onclick="previewEmailModal({{ $log->id }}, '{{ addslashes($log->subject) }}', '{{ $log->recipient_email }}')">
                                        <i class="bi bi-eye-fill me-1"></i>View Body
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted font-sans">No email transmission records found matching the filters.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top p-3 font-sans">
                    {{ $emailLogs->links() }}
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Email Preview Modal -->
<div class="modal fade" id="emailBodyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-dark text-white px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold mb-0 font-sans" id="emailModalTitle"><i class="bi bi-envelope-open me-2"></i>Email Preview</h5>
                    <div class="text-xxs text-light opacity-75 font-monospace" id="emailModalSubtitle"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-light" style="min-height: 500px;">
                <iframe id="emailPreviewFrame" style="width: 100%; height: 600px; border: none; display: block;"></iframe>
            </div>
            <div class="modal-footer bg-white px-4 py-2">
                <button type="button" class="btn btn-secondary rounded-pill btn-sm font-sans px-4" data-bs-dismiss="modal">Close Preview</button>
            </div>
        </div>
    </div>
</div>

<script>
function previewEmailModal(id, subject, recipient) {
    document.getElementById('emailModalTitle').innerHTML = '<i class="bi bi-envelope-open me-2"></i>' + subject;
    document.getElementById('emailModalSubtitle').innerText = 'Transmitted to: ' + recipient;
    
    const iframe = document.getElementById('emailPreviewFrame');
    iframe.src = "{{ url('/super-admin/logs-hub/email-preview') }}/" + id;
    
    const modal = new bootstrap.Modal(document.getElementById('emailBodyModal'));
    modal.show();
}
</script>
@endsection
