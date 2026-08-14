@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark"><i class="bi bi-bell-fill text-warning me-2"></i>Dynamic Notification Manager</h1>
            <p class="text-muted mb-0">Customize automated email subject lines, email bodies, and system dashboard alert templates.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        @foreach($templates as $tmpl)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <form action="{{ route('superadmin.notifications.update', $tmpl->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">{{ $tmpl->name }}</h6>
                        <span class="badge bg-secondary font-monospace text-xxs">{{ $tmpl->event_key }}</span>
                    </div>
                    <div class="card-body p-4">
                        <input type="hidden" name="name" value="{{ $tmpl->name }}">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-xs text-muted text-uppercase">Email Subject Line</label>
                            <input type="text" name="email_subject" class="form-control text-sm" value="{{ $tmpl->email_subject }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-xs text-muted text-uppercase">System Alert Title</label>
                            <input type="text" name="system_title" class="form-control text-sm" value="{{ $tmpl->system_title }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-xs text-muted text-uppercase">System Alert Message Body</label>
                            <textarea name="system_message" class="form-control text-sm" rows="3">{{ $tmpl->system_message }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-top p-3 text-end">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Save Template</button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
