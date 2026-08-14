@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Multi-dimensional Analytics Reports & Exporter</h1>
            <p class="text-muted mb-0">Filter university activity data across constituent colleges, date ranges, and statuses, and export to CSV/Excel.</p>
        </div>
        <a href="{{ route('superadmin.reports.export', request()->all()) }}" class="btn btn-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Export Filtered CSV
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-light">
        <form method="GET" action="{{ route('superadmin.reports') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="college_id" class="form-select">
                    <option value="">All Colleges</option>
                    @foreach($colleges as $col)
                        <option value="{{ $col->id }}" {{ request('college_id') == $col->id ? 'selected' : '' }}>{{ $col->code }} - {{ $col->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st->slug }}" {{ request('status') == $st->slug ? 'selected' : '' }}>{{ $st->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="Start Date">
            </div>
            <div class="col-md-2">
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="End Date">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('superadmin.reports') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted text-xxs text-uppercase">
                    <tr>
                        <th class="ps-4">Reference ID</th>
                        <th>Activity Title</th>
                        <th>College</th>
                        <th>Submitted By</th>
                        <th>Activity Date</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $act)
                    <tr>
                        <td class="ps-4 font-monospace text-xs text-muted">{{ $act->reference_id }}</td>
                        <td class="fw-bold text-dark">{{ $act->title }}</td>
                        <td><span class="badge bg-light text-dark">{{ $act->college ? $act->college->code : 'UNIV' }}</span></td>
                        <td class="text-sm">{{ $act->submitter_name }}</td>
                        <td class="text-sm">{{ $act->activity_date }}</td>
                        <td><span class="badge bg-{{ $act->status === 'published' ? 'success' : 'primary' }}">{{ strtoupper($act->status) }}</span></td>
                        <td class="text-xs text-muted">{{ $act->created_at->format('Y-m-d') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No activities found matching criteria.</td>
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
