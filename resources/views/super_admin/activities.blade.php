@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark"><i class="bi bi-sliders text-danger me-2"></i>Unrestricted Institutional Activity Override Manager</h1>
            <p class="text-muted mb-0">Complete command over all activity submissions. Force override status, reassign college owner, force resubmit, restore deleted items, or remove records.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-light">
        <form method="GET" action="{{ route('superadmin.activities') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <select name="college_id" class="form-select">
                    <option value="">Filter by Constituent College (All)</option>
                    @foreach($colleges as $col)
                        <option value="{{ $col->id }}" {{ request('college_id') == $col->id ? 'selected' : '' }}>{{ $col->code }} - {{ $col->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">Filter by Activity Status (All)</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st->slug }}" {{ request('status') == $st->slug ? 'selected' : '' }}>{{ $st->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i>Apply Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('superadmin.activities') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>

    <!-- Activities Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted text-xxs text-uppercase">
                    <tr>
                        <th class="ps-4">Reference & Title</th>
                        <th>College</th>
                        <th>Submitted By</th>
                        <th>Current Status & Stage</th>
                        <th>State</th>
                        <th class="text-end pe-4">Super Admin Override Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                    <tr class="{{ $activity->trashed() ? 'bg-danger bg-opacity-10' : '' }}">
                        <td class="ps-4">
                            <div class="fw-bold text-dark text-sm">{{ $activity->title }}</div>
                            <div class="text-muted text-xxs font-monospace">{{ $activity->reference_id }} | {{ $activity->activity_date }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $activity->college ? $activity->college->code : 'UNIV' }}</span>
                        </td>
                        <td>
                            <div class="text-sm fw-bold">{{ $activity->submitter_name }}</div>
                            <div class="text-xxs text-muted">{{ $activity->user ? $activity->user->email : '' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $activity->status === 'published' ? 'success' : ($activity->status === 'rejected' ? 'danger' : 'warning') }} mb-1">
                                {{ str_replace('_', ' ', strtoupper($activity->status)) }}
                            </span>
                            <div class="text-xxs text-muted">{{ $activity->current_stage }}</div>
                        </td>
                        <td>
                            @if($activity->trashed())
                                <span class="badge bg-danger"><i class="bi bi-trash me-1"></i>Deleted</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                @if($activity->trashed())
                                    <form action="{{ route('superadmin.activities.restore', $activity->id) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Restore Activity">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                        </button>
                                    </form>
                                @else
                                    <!-- Status Override -->
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#overrideModal{{ $activity->id }}" title="Force Status Override">
                                        <i class="bi bi-sliders"></i>
                                    </button>
                                    <!-- Reassign Owner -->
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#reassignModal{{ $activity->id }}" title="Reassign Owner">
                                        <i class="bi bi-person-gear"></i>
                                    </button>
                                    <!-- Force Resubmit -->
                                    <form action="{{ route('superadmin.activities.forceResubmit', $activity->id) }}" method="POST" class="m-0" onsubmit="return confirm('Force reopen and resubmit this activity?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Force Resubmit">
                                            <i class="bi bi-bootstrap-reboot"></i>
                                        </button>
                                    </form>
                                    <!-- Delete -->
                                    <form action="{{ route('superadmin.activities.delete', $activity->id) }}" method="POST" class="m-0" onsubmit="return confirm('Move activity to trash?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Activity">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Override Modal -->
                    <div class="modal fade" id="overrideModal{{ $activity->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('superadmin.activities.override', $activity->id) }}" method="POST">
                                @csrf
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title fw-bold">Super Admin Override: {{ $activity->reference_id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">Force New Status</label>
                                            <select name="status" class="form-select" required>
                                                @foreach($statuses as $st)
                                                    <option value="{{ $st->slug }}" {{ $activity->status == $st->slug ? 'selected' : '' }}>{{ $st->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">Force Current Stage Label</label>
                                            <input type="text" name="stage" class="form-control" value="{{ $activity->current_stage }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">Super Admin Remarks</label>
                                            <textarea name="remarks" class="form-control" rows="3" placeholder="Reason for status override...">{{ $activity->admin_remarks }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Execute Override</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Reassign Modal -->
                    <div class="modal fade" id="reassignModal{{ $activity->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('superadmin.activities.reassign', $activity->id) }}" method="POST">
                                @csrf
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title fw-bold">Reassign Activity Owner</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">Select New Owner</label>
                                            <select name="new_user_id" class="form-select" required>
                                                @foreach($users as $usr)
                                                    <option value="{{ $usr->id }}" {{ $activity->user_id == $usr->id ? 'selected' : '' }}>{{ $usr->name }} ({{ $usr->role }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Reassign Owner</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No activities found matching criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top p-3">
            {{ $activities->links() }}
        </div>
    </div>
</div>
@endsection
