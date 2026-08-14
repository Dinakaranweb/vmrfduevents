@extends('layouts.app')

@section('title', 'Constituent College Master')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-buildings-fill text-primary me-2"></i>Constituent College Master Management</h3>
        <p class="text-muted mb-0">Manage institutional directory, campus deans, and portal integration across VMRFDU</p>
    </div>
    <button class="btn btn-portal-primary shadow-sm" onclick="alert('New college onboarding form opened.')"><i class="bi bi-plus-circle me-1"></i> Add Constituent College</button>
</div>

<div class="row g-4">
    @foreach($colleges as $col)
    <div class="col-12 col-md-6 col-xl-4">
        <div class="portal-card p-4 h-100 d-flex flex-column justify-content-between border">
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-primary px-3 py-1 fs-6">{{ $col->code }}</span>
                    <span class="badge {{ $col->is_active ? 'bg-success' : 'bg-danger' }}">{{ $col->is_active ? 'Active Campus' : 'Inactive' }}</span>
                </div>
                <h5 class="fw-bold text-dark mb-1">{{ $col->name }}</h5>
                <p class="text-muted small mb-3"><i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $col->location }}</p>

                <div class="bg-light p-3 rounded-3 border small mb-3">
                    <div class="mb-1"><strong>Dean / Principal:</strong> {{ $col->dean_name ?: 'N/A' }}</div>
                    <div class="mb-1"><strong>Contact Email:</strong> {{ $col->contact_email ?: 'N/A' }}</div>
                    <div><strong>Website:</strong> <a href="{{ $col->website }}" target="_blank" class="text-decoration-none">{{ $col->website }}</a></div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center border-top pt-3 small text-muted">
                <div><i class="bi bi-geo-alt me-1"></i> Constituent Campus</div>
                <div><i class="bi bi-people me-1"></i> {{ $col->users_count }} Users</div>
                <div><i class="bi bi-journal-text me-1"></i> {{ $col->submissions_count }} Submissions</div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
