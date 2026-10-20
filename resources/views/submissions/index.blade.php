@extends('layouts.app')

@section('title', 'Institutional Activity Directory - VMRFDU')

@section('content')
@php
    $pageTitle = 'Institutional Activity Submission Directory';
    $pageSubtitle = 'Centralized tracking repository of academic, research, outreach, and cultural events submitted across 28 constituent campuses';
    $pageIcon = 'bi-folder2-open text-primary';
    
    if (request('status') === 'published') {
        $pageTitle = 'Published Live Archive';
        $pageSubtitle = 'Registry of all activities that have been officially broadcasted on social media and other platforms.';
        $pageIcon = 'bi-check-circle-fill text-success';
    } elseif (request('status') === 'verification_pending') {
        $pageTitle = 'Pending Verification Queue';
        $pageSubtitle = 'Activities awaiting initial verification from authorities.';
        $pageIcon = 'bi-hourglass-split text-warning';
    } elseif (request('status') === 'ready_to_publish') {
        $pageTitle = 'Media Ready Queue';
        $pageSubtitle = 'Activities that have been approved and are ready for broadcast.';
        $pageIcon = 'bi-broadcast text-info';
    }
@endphp
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark"><i class="bi {{ $pageIcon }} me-2"></i>{{ $pageTitle }}</h3>
        <p class="text-muted mb-0">{{ $pageSubtitle }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if(!$currentUser || !$currentUser->hasRole(['media_editor', 'registrar', 'office_registrar']))
            <a href="{{ route('submissions.create') }}" class="btn btn-portal-primary px-4 py-2 d-flex align-items-center gap-2 shadow">
                <i class="bi bi-plus-circle-fill"></i> Submit New Activity
            </a>
        @endif
    </div>
</div>

<!-- Tabs for College Admin with view_approved_all permission -->
@if($currentUser && $currentUser->hasPermission('activities.view_approved_all') && !$currentUser->isSuperAdmin())
<ul class="nav nav-tabs mb-4" style="border-bottom: 2px solid #e2e8f0;">
    <li class="nav-item">
        <a class="nav-link fw-bold {{ request('tab', 'my_college') !== 'approved_all' ? 'active text-primary border-primary border-bottom-0' : 'text-muted border-transparent' }}" href="{{ route('submissions.index', ['tab' => 'my_college']) }}" style="{{ request('tab', 'my_college') !== 'approved_all' ? 'border-top: 2px solid #3b82f6;' : '' }}">
            <i class="bi bi-building me-1"></i> My College Activities
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link fw-bold {{ request('tab') === 'approved_all' ? 'active text-primary border-primary border-bottom-0' : 'text-muted border-transparent' }}" href="{{ route('submissions.index', ['tab' => 'approved_all']) }}" style="{{ request('tab') === 'approved_all' ? 'border-top: 2px solid #3b82f6;' : '' }}">
            <i class="bi bi-globe me-1"></i> Constituent College Activities
        </a>
    </li>
</ul>
@endif

<!-- Date-Wise Tracking & Filtering Bar -->
<div class="portal-card p-4 mb-4 shadow-sm bg-white">
    <form action="{{ route('submissions.index') }}" method="GET">
        <input type="hidden" name="tab" value="{{ request('tab', 'my_college') }}">
        <div class="row g-3 align-items-end">
            @if($currentUser && ($currentUser->isSuperAdmin() || $currentUser->role === 'super_admin'))
            <div class="col-12 col-md-4 col-xl-3">
                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Constituent College</label>
                <select name="college_id" class="form-select">
                    <option value="all">All Constituent Campuses</option>
                    @foreach($colleges as $col)
                        <option value="{{ $col->id }}" {{ request('college_id') == $col->id ? 'selected' : '' }}>{{ $col->code }} - {{ Str::limit($col->name, 35) }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-12 col-md-4 col-xl-2">
                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Approval Status</label>
                <select name="status" class="form-select">
                    <option value="all">All Statuses</option>
                    <option value="verification_pending" {{ request('status') == 'verification_pending' ? 'selected' : '' }}>Verification Pending</option>
                    <option value="approval_pending" {{ request('status') == 'approval_pending' ? 'selected' : '' }}>Approval Pending</option>
                    <option value="media_review" {{ request('status') == 'media_review' ? 'selected' : '' }}>Approved (Media Desk)</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled for Media</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published Live</option>
                    <option value="discuss" {{ request('status') == 'discuss' ? 'selected' : '' }}>Discuss (Needs Correction)</option>
                    <option value="sent_back" {{ request('status') == 'sent_back' ? 'selected' : '' }}>Returned / Query</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Closed / Rejected</option>
                </select>
            </div>

            <div class="col-12 col-md-2 col-xl-2">
                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>

            <div class="col-12 col-md-2 col-xl-2">
                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>

            <div class="col-12 col-md-12 col-xl-3">
                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Search Keyword / VMRF ID</label>
                <input type="text" name="search" class="form-control" placeholder="Search title, description, ID..." value="{{ request('search') }}">
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end pt-2 border-top mt-3">
                @if(request()->hasAny(['college_id', 'status', 'from_date', 'to_date', 'search']))
                    <a href="{{ route('submissions.index', ['tab' => request('tab', 'my_college')]) }}" class="btn btn-light border px-4" title="Reset Filters"><i class="bi bi-x-circle me-1"></i> Clear Filters</a>
                @endif
                <button type="submit" class="btn btn-portal-primary px-4 fw-bold shadow-sm"><i class="bi bi-funnel-fill me-1"></i> Apply Filter</button>
            </div>
        </div>
    </form>
</div>

<!-- Activity Directory Table -->
<div class="portal-card p-4 mb-5 bg-white border shadow-sm">
    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-table text-primary me-2"></i>{{ request('status') === 'published' ? 'Published Activities Table' : 'Institutional Activity Directory Table' }}</h5>
            <p class="text-muted small mb-0">Tabular index of records matching the current filter</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="input-group input-group-sm" style="max-width: 240px;">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control table-search-input border-start-0" data-table-target="#submissionsTable tbody tr" placeholder="Quick instant filter...">
            </div>
            <span class="badge bg-light text-dark border px-3 py-2 fw-bold">{{ $submissions->total() ?? $submissions->count() }} Submissions</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-portal align-middle mb-0" id="submissionsTable">
            <thead>
                <tr>
                    <th style="width: 70px;">Media</th>
                    <th>Ref ID</th>
                    <th>Activity Details</th>
                    <th>Campus</th>
                    <th>Activity Date</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $sub)
                <tr>
                    <td>
                        @if($sub->primaryImage)
                            <img src="{{ upload_url($sub->primaryImage->file_path) }}" alt="{{ $sub->title }}" class="rounded-3 shadow-sm object-fit-cover border" style="width: 50px; height: 50px;">
                        @else
                            <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 50px;">
                                <i class="bi bi-camera-fill fs-5 opacity-50"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-dark text-white font-monospace px-2 py-1 shadow-sm">{{ $sub->reference_id }}</span>
                    </td>
                    <td>
                        <a href="{{ route('submissions.show', $sub->id) }}" class="text-decoration-none">
                            <div class="fw-bold text-primary text-sm mb-1 text-truncate" style="max-width: 340px;">{{ $sub->title }}</div>
                        </a>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-light text-muted border text-xxs">{{ $sub->event_category }}</span>
                            @if($sub->location)
                                <span class="text-muted text-xxs"><i class="bi bi-geo-alt me-1"></i>{{ Str::limit($sub->location, 30) }}</span>
                            @endif
                            <span class="text-muted text-xxs"><i class="bi bi-person me-1"></i>{{ $sub->submitter_name }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold text-primary text-sm">{{ $sub->college ? $sub->college->code : 'Central' }}</div>
                        <div class="text-muted text-xxs text-truncate" style="max-width: 170px;">{{ $sub->college ? $sub->college->name : 'VMRF University' }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark text-sm">{{ $sub->activity_date ? $sub->activity_date->format('d M, Y') : 'N/A' }}</div>
                        <div class="text-muted text-xxs">{{ $sub->created_at ? 'Sub: ' . $sub->created_at->format('d/m/y') : '' }}</div>
                    </td>
                    <td>
                        {!! $sub->status_badge !!}
                    </td>
                    <td class="text-end">
                        <a href="{{ route('submissions.show', $sub->id) }}" class="btn btn-sm btn-light border px-3 py-1 shadow-sm fw-semibold text-primary d-inline-flex align-items-center gap-1">
                            View <i class="bi bi-arrow-right"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="bg-light rounded-circle mx-auto p-4 d-inline-flex mb-3 text-muted">
                            <i class="bi bi-folder-x fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark">No institutional activities found</h5>
                        <p class="text-muted small mb-3">Try clearing your filter parameters or record a new institutional activity.</p>
                        <a href="{{ route('submissions.create') }}" class="btn btn-sm btn-portal-primary px-4 py-2">Submit New Activity</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center">
    {{ $submissions->links() }}
</div>
@endsection
