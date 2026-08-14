@extends('layouts.app')

@section('title', 'Dispatched Email Notifications Log - VMRFDU')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-envelope-check-fill text-primary me-2"></i>Dispatched Email Notifications Audit Log</h3>
        <p class="text-muted mb-0">Live tracking of all automated HTML emails sent to College DEOs, Registrar, VC, Pro VC, and Media Editor</p>
    </div>
    <span class="badge bg-success px-3 py-2 fs-6"><i class="bi bi-broadcast me-1"></i> Multi-Stage Dispatch Engine Active</span>
</div>

<div class="portal-card p-4 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>Recipient</th>
                    <th>Stage Trigger</th>
                    <th>Email Subject</th>
                    <th>Delivery Status</th>
                    <th class="text-end">Preview HTML</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="small text-muted">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                    <td>
                        <div class="fw-bold text-dark">{{ $log->user ? $log->user->name : 'External Officer' }}</div>
                        <div class="text-xs text-muted">{{ $log->recipient_email }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $log->stage === 'SUBMITTED' ? 'bg-warning text-dark' : ($log->stage === 'APPROVED' ? 'bg-success' : ($log->stage === 'PUBLISHED' ? 'bg-info text-dark' : 'bg-secondary')) }}">
                            {{ $log->stage }}
                        </span>
                    </td>
                    <td class="fw-semibold text-truncate" style="max-width: 320px;">{{ $log->subject }}</td>
                    <td><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="bi bi-check-circle me-1"></i> Dispatched</span></td>
                    <td class="text-end">
                        <a href="{{ route('emails.show', $log->id) }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-eye"></i> View Email Body
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                        No automated email logs recorded yet. Submit or approve an activity to trigger email dispatches!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
