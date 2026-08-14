@extends('layouts.app')

@section('title', 'Event Categories Master Management - VMRFDU Portal')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark"><i class="bi bi-tags-fill me-2 text-primary"></i>Event Categories Master</h1>
            <p class="text-muted mb-0 small">Manage institutional activity classification categories loaded dynamically across the entire submission portal.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                <i class="bi bi-plus-circle-fill"></i> Add New Category
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> Please check the errors below and try again.
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-primary"></i>Configured Activity Categories ({{ $categories->count() }})</h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">Active Categories populate dropdowns automatically</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3" style="width: 80px;"># Order</th>
                            <th class="py-3">Category Name</th>
                            <th class="py-3">Slug / Identifier</th>
                            <th class="py-3">Description & Scope</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="ps-4 fw-bold text-muted">{{ $category->sort_order }}</td>
                                <td>
                                    <div class="fw-bold text-dark fs-6">{{ $category->name }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark font-monospace border">{{ $category->slug }}</span></td>
                                <td class="text-muted small">{{ $category->description ?: 'No description provided.' }}</td>
                                <td class="text-center">
                                    <form action="{{ route('superadmin.categories.toggle', $category->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $category->is_active ? 'btn-success' : 'btn-secondary' }} rounded-pill px-3 shadow-sm" title="Click to {{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                            @if($category->is_active)
                                                <i class="bi bi-check-circle-fill me-1"></i> Active
                                            @else
                                                <i class="bi bi-x-circle-fill me-1"></i> Disabled
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        <!-- Edit Button -->
                                        <button class="btn btn-sm btn-outline-primary rounded-3" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editCategoryModal"
                                                data-id="{{ $category->id }}"
                                                data-name="{{ $category->name }}"
                                                data-description="{{ $category->description }}"
                                                data-sort_order="{{ $category->sort_order }}"
                                                data-is_active="{{ $category->is_active ? '1' : '0' }}"
                                                onclick="populateEditModal(this)">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                        <!-- Delete Button -->
                                        <form action="{{ route('superadmin.categories.delete', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this event category? Any existing activities with this category will retain their saved category text.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-tags fs-1 d-block mb-3 text-secondary"></i>
                                    <h5>No Event Categories Found</h5>
                                    <p class="small">Get started by creating institutional categories for your activities.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Create New Event Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('superadmin.categories.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Workshop & Hands-on Training" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ $categories->count() + 1 }}" min="1">
                        <div class="form-text small">Order in which this category appears in the submission dropdown.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Description & Scope</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief scope of activities under this category..."></textarea>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="createCategoryActive" checked value="1">
                        <label class="form-check-label fw-bold" for="createCategoryActive">Enable Immediately for New Submissions</label>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Event Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort_order" class="form-control" min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Description & Scope</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="edit_is_active" value="1">
                        <label class="form-check-label fw-bold" for="edit_is_active">Active for New Submissions</label>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function populateEditModal(btn) {
    const id = btn.getAttribute('data-id');
    const name = btn.getAttribute('data-name');
    const description = btn.getAttribute('data-description');
    const sortOrder = btn.getAttribute('data-sort_order');
    const isActive = btn.getAttribute('data-is_active') === '1';

    const form = document.getElementById('editCategoryForm');
    form.action = `/super-admin/categories/${id}`;

    document.getElementById('edit_name').value = name || '';
    document.getElementById('edit_description').value = description || '';
    document.getElementById('edit_sort_order').value = sortOrder || '1';
    document.getElementById('edit_is_active').checked = isActive;
}
</script>
@endsection
