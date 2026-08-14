@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark"><i class="bi bi-building text-primary me-2"></i>Constituent College Management (CRUD)</h1>
            <p class="text-muted mb-0">Create, edit, assign college codes, and manage activation status across Vinayaka Mission's Research Foundation.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCollegeModal">
            <i class="bi bi-plus-circle me-1"></i>Create New College
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Colleges Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted text-xxs text-uppercase">
                    <tr>
                        <th class="ps-4">Code</th>
                        <th>College Name</th>
                        <th>Location</th>
                        <th>Users</th>
                        <th>Submissions</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($colleges as $college)
                    <tr>
                        <td class="ps-4 fw-bold text-primary">{{ $college->code }}</td>
                        <td class="fw-bold text-dark">{{ $college->name }}</td>
                        <td class="text-muted text-sm">{{ $college->location ?? 'Not Specified' }}</td>
                        <td><span class="badge bg-info bg-opacity-10 text-info">{{ $college->users_count }} Users</span></td>
                        <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $college->submissions_count }} Activities</span></td>
                        <td>
                            @if($college->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Deactivated</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCollegeModal{{ $college->id }}" title="Edit College">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('superadmin.colleges.toggle', $college->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $college->is_active ? 'warning' : 'success' }}" title="{{ $college->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-power"></i>
                                    </button>
                                </form>
                                <form action="{{ route('superadmin.colleges.delete', $college->id) }}" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this college?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete College">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editCollegeModal{{ $college->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('superadmin.colleges.update', $college->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title fw-bold">Edit College Information</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">College Code <span class="text-danger">*</span></label>
                                            <input type="text" name="code" class="form-control" value="{{ $college->code }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">College Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" value="{{ $college->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">Location / Campus</label>
                                            <input type="text" name="location" class="form-control" value="{{ $college->location }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No colleges found. Click Create New College to start.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createCollegeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('superadmin.colleges.store') }}" method="POST">
                @csrf
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold">Create New Constituent College</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-sm">College Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. VMCPL" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-sm">College Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Full Official College Name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-sm">Location / Campus</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Salem Campus">
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create College</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
