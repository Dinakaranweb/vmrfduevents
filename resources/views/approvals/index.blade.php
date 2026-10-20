@extends('layouts.app')

@section('title', 'Officials Verification & Approval Desk')

@section('content')
@php
    $activeTab = $activeTab ?? 'pending';
@endphp

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 p-4 bg-white rounded-4 shadow-sm border">
    <div>
        <span class="badge bg-dark text-white px-3 py-1 mb-2 rounded-pill shadow-sm">
            <i class="bi bi-shield-check me-1"></i> Authority Review Desk
        </span>
        <h3 class="fw-bold mb-1 text-dark">
            @if($currentUser && $currentUser->role === 'office_registrar')
                @if($activeTab === 'approved') Final Approved Media Releases
                @elseif($activeTab === 'rejected') Rejected Final Media Packages
                @else Office of the Registrar — Final Media Approval Queue @endif
            @else
                @if($activeTab === 'approved') Approved Activities & Media Releases
                @elseif($activeTab === 'rejected') Rejected Activity Records
                @else Officials Institutional Verification & Approval Queue @endif
            @endif
        </h3>
        <p class="text-muted mb-0 small">
            @if($currentUser && $currentUser->role === 'office_registrar')
                @if($activeTab === 'approved') Final media content authorized by Office of the Registrar and forwarded for broadcast
                @elseif($activeTab === 'rejected') Media packages returned or declined during final verification
                @else Review finalized media packages submitted by Media Editor and authorize official publication @endif
            @else
                @if($activeTab === 'approved') Activities authorized by institutional leadership and forwarded to the Media Broadcasting Desk
                @elseif($activeTab === 'rejected') Records that did not meet verification criteria or were officially declined
                @else Verify compliance, review institutional submissions, and take formal authority approval actions @endif
            @endif
        </p>
    </div>
    @if($activeTab === 'pending')
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-warning text-dark px-4 py-2 fs-6 shadow-sm rounded-pill d-flex align-items-center gap-2">
            <i class="bi bi-clock-fill"></i> {{ $pendingSubmissions->count() }} Awaiting Final Review
        </span>
    </div>
    @endif
</div>

<!-- Status Navigation Tabs -->
<div class="portal-card p-2 mb-4 bg-white border shadow-sm">
    <div class="d-flex gap-1 flex-wrap">
        <a href="{{ route('approvals.pending') }}" class="btn {{ $activeTab === 'pending' ? 'btn-dark' : 'btn-light border' }} px-4 py-2 fw-bold rounded-3 d-flex align-items-center gap-2">
            @if($currentUser && $currentUser->role === 'office_registrar')
                <i class="bi bi-clock-fill text-warning"></i> Final Media Approvals
                @php $pCount = \App\Models\ActivitySubmission::where('status', 'registrar_approval')->count(); @endphp
            @else
                <i class="bi bi-clock-fill text-warning"></i> Pending Verification
                @php $pCount = \App\Models\ActivitySubmission::whereIn('status', ['verification_pending', 'approval_pending'])->count(); @endphp
            @endif
            @if($pCount > 0)
                <span class="badge bg-warning text-dark rounded-pill">{{ $pCount }}</span>
            @endif
        </a>
        <a href="{{ route('approvals.approved') }}" class="btn {{ $activeTab === 'approved' ? 'btn-dark' : 'btn-light border' }} px-4 py-2 fw-bold rounded-3 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill text-success"></i> Approved Media Releases
        </a>
        <a href="{{ route('approvals.rejected') }}" class="btn {{ $activeTab === 'rejected' ? 'btn-dark' : 'btn-light border' }} px-4 py-2 fw-bold rounded-3 d-flex align-items-center gap-2">
            <i class="bi bi-x-circle-fill text-danger"></i> Rejected Records
        </a>
    </div>
</div>

@if($activeTab === 'pending')
<!-- Pending Verification Queue -->
@if($pendingSubmissions->isEmpty())
    <div class="portal-card p-5 text-center bg-white border shadow-sm">
        <div class="bg-success bg-opacity-10 text-success rounded-circle p-4 d-inline-flex mb-4 align-items-center justify-content-center" style="width: 80px; height: 80px;">
            <i class="bi bi-check-all fs-1"></i>
        </div>
        <h4 class="fw-bold text-dark mb-2">All Submissions Reviewed!</h4>
        <p class="text-muted small">There are currently no institutional activities pending authority approval.</p>
    </div>
@else
    <div class="row g-4">
        @foreach($pendingSubmissions as $sub)
        <div class="col-12 col-xl-6">
            <div class="portal-card p-4 h-100 d-flex flex-column justify-content-between bg-white border portal-card-hover shadow-sm">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-dark text-white font-monospace px-3 py-1 shadow-sm">{{ $sub->reference_id }}</span>
                            {!! $sub->status_badge !!}
                        </div>
                        <small class="text-muted text-xs fw-semibold"><i class="bi bi-clock me-1"></i>{{ $sub->created_at->diffForHumans() }}</small>
                    </div>

                    <h5 class="fw-bold text-dark mb-2">{{ $sub->title }}</h5>
                    <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">{{ $sub->description }}</p>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 bg-light rounded-3 border d-flex align-items-center gap-2 text-xs fw-semibold text-dark">
                                <i class="bi bi-building text-primary"></i>
                                <span class="text-truncate">{{ $sub->college ? $sub->college->code : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded-3 border d-flex align-items-center gap-2 text-xs fw-semibold text-dark">
                                <i class="bi bi-calendar-event text-primary"></i>
                                <span>{{ $sub->activity_date ? $sub->activity_date->format('d M, Y') : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-2 bg-light rounded-3 border d-flex align-items-center gap-2 text-xs fw-semibold text-dark">
                                <i class="bi bi-person-badge text-primary"></i>
                                <span class="text-truncate">Submitted By: {{ $sub->submitter_name }}</span>
                            </div>
                        </div>
                    </div>

                    @if($sub->media && $sub->media->count() > 0)
                    @php
                        $editedMediaList = $sub->media->where('media_category', 'edited_social');
                        $originalMediaList = $sub->media->where('media_category', '!=', 'edited_social');
                    @endphp

                    @if($editedMediaList->count() > 0)
                    <div class="mb-2 p-2 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25">
                        <div class="text-xxs fw-bold text-primary mb-1 d-flex align-items-center gap-1">
                            <i class="bi bi-magic"></i> Edited Photos for Social Media ({{ $editedMediaList->count() }}):
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($editedMediaList as $m)
                                <a href="{{ upload_url($m->file_path) }}" target="_blank" class="badge bg-white text-dark border p-1 d-flex align-items-center gap-1 shadow-sm text-decoration-none" title="Click to view/download">
                                    <i class="bi bi-image text-primary"></i> {{ Str::limit($m->file_name, 20) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($originalMediaList->count() > 0)
                    <div class="mb-3 p-2 bg-light rounded-3 border">
                        <div class="text-xs fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                            <i class="bi bi-paperclip text-primary"></i> Attached Original Files ({{ $originalMediaList->count() }}):
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($originalMediaList as $m)
                                <a href="{{ upload_url($m->file_path) }}" target="_blank" class="badge bg-white text-dark border p-1 d-flex align-items-center gap-1 shadow-sm text-decoration-none" title="Click to view/download">
                                    @if($m->file_type === 'image')
                                        <i class="bi bi-image text-info"></i> {{ Str::limit($m->file_name, 20) }}
                                    @elseif($m->file_type === 'document')
                                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i> {{ Str::limit($m->file_name, 20) }}
                                    @else
                                        <i class="bi bi-play-circle-fill text-primary"></i> Video Link
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @endif
                </div>

                <div class="d-flex align-items-center justify-content-between pt-3 border-top flex-wrap gap-2">
                    <a href="{{ route('submissions.show', $sub->id) }}" class="btn btn-light border px-4 py-2 shadow-sm d-flex align-items-center gap-2">
                        <i class="bi bi-eye-fill text-primary"></i> Review Full Details
                    </a>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success fw-bold px-3 py-2 d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#quickApproveModal" onclick="setupQuickModal({{ $sub->id }}, 'approve', '{{ addslashes($sub->title) }}', '{{ $sub->status }}')">
                            <i class="bi bi-check-circle-fill"></i> Approve
                        </button>
                        <button type="button" class="btn btn-warning fw-bold text-dark px-3 py-2 d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#quickApproveModal" onclick="setupQuickModal({{ $sub->id }}, 'discuss', '{{ addslashes($sub->title) }}', '{{ $sub->status }}')">
                            <i class="bi bi-chat-dots-fill"></i> Discuss
                        </button>
                        <button type="button" class="btn btn-danger fw-bold px-3 py-2 d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#quickApproveModal" onclick="setupQuickModal({{ $sub->id }}, 'reject', '{{ addslashes($sub->title) }}', '{{ $sub->status }}')">
                            <i class="bi bi-x-circle-fill"></i> Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endif

@if($activeTab === 'approved' || $activeTab === 'rejected')
<!-- Filtered Records Table -->
<div class="portal-card p-4 bg-white border shadow-sm">
    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            @if($activeTab === 'approved') <i class="bi bi-check-circle-fill text-success"></i> Approved Activities (Forwarded to Media Desk)
            @else <i class="bi bi-x-circle-fill text-danger"></i> Rejected Activity Records @endif
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="input-group input-group-sm" style="max-width: 220px;">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control table-search-input border-start-0" placeholder="Search records...">
            </div>
            <span class="badge bg-light text-dark border px-3 py-2">{{ $reviewedSubmissions->total() ?? $reviewedSubmissions->count() }} Records</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-portal align-middle small mb-0">
            <thead>
                <tr>
                    <th>Ref ID</th>
                    <th>Activity Title</th>
                    <th>Constituent Campus</th>
                    <th>Workflow Stage</th>
                    <th>Status</th>
                    <th>Reviewed On</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviewedSubmissions as $rev)
                <tr>
                    <td class="fw-bold text-primary font-monospace">{{ $rev->reference_id }}</td>
                    <td class="fw-semibold text-dark text-truncate" style="max-width: 280px;">{{ $rev->title }}</td>
                    <td>
                        <div class="fw-bold text-dark">{{ $rev->college ? $rev->college->code : 'N/A' }}</div>
                        <div class="text-muted text-xs">{{ $rev->college ? Str::limit($rev->college->name, 35) : '' }}</div>
                    </td>
                    <td class="text-muted small fw-medium">{{ $rev->current_stage }}</td>
                    <td>{!! $rev->status_badge !!}</td>
                    <td class="text-muted small fw-semibold">{{ $rev->updated_at->format('d M Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('submissions.show', $rev->id) }}" class="btn btn-sm btn-light border px-3 rounded-pill shadow-sm">
                            <i class="bi bi-arrow-right text-primary"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-folder-x fs-2 d-block mb-2 opacity-50"></i>
                        @if($activeTab === 'approved') No approved records found.
                        @else No rejected records found. @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if(method_exists($reviewedSubmissions, 'links'))
        <div class="mt-4 d-flex justify-content-center">
            {{ $reviewedSubmissions->links() }}
        </div>
        @endif
    </div>
</div>
@endif

<!-- Quick Action Modal -->
<div class="modal fade" id="quickApproveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form id="quickModalForm" method="POST" action="">
                @csrf
                <div class="modal-header bg-dark text-white p-4" id="quickModalHeaderBg">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="quickModalHeader">Review Action</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" id="quickModalAction">
                    <p class="small text-muted bg-light p-3 rounded-3 border mb-4 fw-medium" id="quickModalSubTitle"></p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark text-uppercase tracking-wider">Official Remarks / Decision Rationale</label>
                        <textarea name="remarks" rows="4" class="form-control border-2" placeholder="Enter official review notes, correction guidance, or rejection rationale..."></textarea>
                        <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i> For 'Discuss', remarks will be sent to the College Representative as actionable correction guidance.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4 py-2 shadow-sm" id="quickModalSubmitBtn">Confirm Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function setupQuickModal(id, action, title, status = 'verification_pending') {
        const form = document.getElementById('quickModalForm');
        form.action = `/approvals/${id}/action`;
        document.getElementById('quickModalAction').value = action;
        
        const header = document.getElementById('quickModalHeader');
        const subTitle = document.getElementById('quickModalSubTitle');
        const btn = document.getElementById('quickModalSubmitBtn');

        subTitle.textContent = `Activity: ${title}`;

        if (status === 'registrar_approval') {
            if (action === 'approve') {
                header.textContent = 'Approve & Return to Editor for Publication';
                btn.className = 'btn btn-success fw-bold px-4 py-2 shadow-sm';
                btn.textContent = 'Approve & Send to Editor';
            } else if (action === 'discuss') {
                header.textContent = 'Discuss (Return to Editor for Correction)';
                btn.className = 'btn btn-warning fw-bold text-dark px-4 py-2 shadow-sm';
                btn.textContent = 'Return to Editor for Correction';
            } else {
                header.textContent = 'Reject Media Package';
                btn.className = 'btn btn-danger fw-bold px-4 py-2 shadow-sm';
                btn.textContent = 'Confirm Rejection';
            }
        } else {
            if (action === 'approve') {
                header.textContent = 'Approve & Forward to Media Desk';
                btn.className = 'btn btn-success fw-bold px-4 py-2 shadow-sm';
                btn.textContent = 'Approve & Forward to Media';
            } else if (action === 'discuss') {
                header.textContent = 'Mark as Discuss (Return for Correction)';
                btn.className = 'btn btn-warning fw-bold text-dark px-4 py-2 shadow-sm';
                btn.textContent = 'Confirm Discuss & Return';
            } else {
                header.textContent = 'Reject Institutional Activity';
                btn.className = 'btn btn-danger fw-bold px-4 py-2 shadow-sm';
                btn.textContent = 'Confirm Rejection';
            }
        }
    }
</script>
@endpush
