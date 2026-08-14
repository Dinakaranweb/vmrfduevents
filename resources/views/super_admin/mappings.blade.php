@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark"><i class="bi bi-grid-3x3-gap-fill text-info me-2"></i>Data Mapping Matrix</h1>
            <p class="text-muted mb-0">Re-map user accounts to specific constituent colleges and functional roles.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted text-xxs text-uppercase">
                    <tr>
                        <th class="ps-4">User</th>
                        <th>Current Role</th>
                        <th>Mapped College</th>
                        <th class="text-end pe-4">Update Matrix</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $user->name }}</div>
                            <div class="text-xs text-muted">{{ $user->email }}</div>
                        </td>
                        <td><span class="badge bg-primary">{{ str_replace('_', ' ', $user->role) }}</span></td>
                        <td><span class="badge bg-light text-dark">{{ $user->college ? $user->college->name : 'Global / None' }}</span></td>
                        <td class="text-end pe-4">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#mappingModal{{ $user->id }}">
                                <i class="bi bi-sliders me-1"></i>Remap
                            </button>
                        </td>
                    </tr>

                    <!-- Remap Modal -->
                    <div class="modal fade" id="mappingModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('superadmin.mappings.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title fw-bold">Update Mapping: {{ $user->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-start">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">Role Assignment</label>
                                            <select name="role" class="form-select" required>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->slug }}" {{ $user->role == $role->slug ? 'selected' : '' }}>{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-sm">Constituent College Mapping</label>
                                            <select name="college_id" class="form-select">
                                                <option value="">Global / University Authority</option>
                                                @foreach($colleges as $col)
                                                    <option value="{{ $col->id }}" {{ $user->college_id == $col->id ? 'selected' : '' }}>{{ $col->code }} - {{ $col->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Mapping</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
