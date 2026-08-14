@extends('layouts.app')

@section('title', 'Institutional Media Gallery & Published Archive')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold mb-1">Media Gallery & Published Post Archive</h3>
        <p class="text-muted mb-0">Explore verified photography, videos, and official social media releases from VMRFDU campuses</p>
    </div>
</div>

<!-- Filter Bar -->
<div class="portal-card p-4 mb-4">
    <form method="GET" action="{{ route('gallery.index') }}" class="row g-3 align-items-center">
        <div class="col-12 col-md-5">
            <label class="form-label small fw-semibold">Filter by Campus</label>
            <select name="college_id" class="form-select">
                <option value="all">All Constituent Colleges</option>
                @foreach($colleges as $col)
                    <option value="{{ $col->id }}" {{ request('college_id') == $col->id ? 'selected' : '' }}>{{ $col->code }} - {{ $col->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label small fw-semibold">Event Category</label>
            <select name="category" class="form-select">
                <option value="all">All Categories</option>
                <option value="Conference" {{ request('category') == 'Conference' ? 'selected' : '' }}>Conference</option>
                <option value="Workshop / Hackathon" {{ request('category') == 'Workshop / Hackathon' ? 'selected' : '' }}>Workshop / Hackathon</option>
                <option value="Extension/CSR" {{ request('category') == 'Extension/CSR' ? 'selected' : '' }}>Extension/CSR</option>
            </select>
        </div>
        <div class="col-12 col-md-3 d-flex gap-2 pt-md-3">
            <button type="submit" class="btn btn-primary w-100 fw-semibold">Filter Gallery</button>
            @if(request()->hasAny(['college_id', 'category']))
                <a href="{{ route('gallery.index') }}" class="btn btn-light border"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>
</div>

<!-- Gallery Grid -->
<div class="row g-4 mb-4">
    @forelse($items as $item)
        @php
            $photo = $item->media->where('file_type', 'image')->first();
        @endphp
        @if($photo)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="portal-card h-100 d-flex flex-column justify-content-between shadow-sm border">
                <div>
                    <div class="gallery-item" data-bs-toggle="modal" data-bs-target="#galleryModal" onclick="showGalleryModal('{{ $photo->file_path }}', '{{ addslashes($item->title) }}', '{{ addslashes($item->social_media_caption) }}')">
                        <img src="{{ $photo->file_path }}" alt="{{ $item->title }}" class="w-100">
                        <div class="gallery-overlay d-flex flex-column justify-content-end">
                            <span class="badge bg-primary w-auto align-self-start mb-1">{{ $item->college ? $item->college->code : '' }}</span>
                            <div class="fw-bold small lh-sm text-truncate">{{ $item->title }}</div>
                        </div>
                    </div>
                    <div class="p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-light text-dark border">{{ $item->event_category }}</span>
                            <small class="text-muted text-xs">{{ $item->activity_date->format('d M, Y') }}</small>
                        </div>
                        <h6 class="fw-bold text-dark text-truncate small mb-2">{{ $item->title }}</h6>
                        <p class="text-muted text-xs mb-0" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $item->description }}</p>
                    </div>
                </div>
                
                <div class="p-3 pt-0 d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                    <a href="{{ route('submissions.show', $item->id) }}" class="small text-decoration-none fw-semibold">View Post <i class="bi bi-arrow-right"></i></a>
                    <a href="{{ $photo->file_path }}" target="_blank" download class="btn btn-sm btn-light border text-xs" title="Download High-Res"><i class="bi bi-download"></i></a>
                </div>
            </div>
        </div>
        @endif
    @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-images fs-1 d-block mb-2"></i>
            No published gallery items found.
        </div>
    @endforelse
</div>

<div class="mt-3">
    {{ $items->links() }}
</div>

<!-- Modal for Gallery Full View -->
<div class="modal fade" id="galleryModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-dark text-white">
            <div class="modal-header bg-dark border-bottom border-secondary border-opacity-50 p-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-image-fill text-primary"></i>
                    <span id="galModalTitle" class="text-truncate">Activity Photo</span>
                </h6>
                <button type="button" class="btn btn-danger btn-sm rounded-pill px-4 py-1.5 fw-bold d-flex align-items-center gap-1 shadow" data-bs-dismiss="modal" title="Close Preview">
                    <i class="bi bi-x-lg"></i> Close Preview
                </button>
            </div>
            <div class="modal-body p-4 text-center d-flex align-items-center justify-content-center" style="min-height: 400px; background: #0f172a;">
                <img src="" id="galModalImg" class="img-fluid rounded-3 shadow-lg border border-secondary border-opacity-25" style="max-height: 75vh; object-fit: contain;">
            </div>
            <div class="modal-footer bg-dark border-top border-secondary border-opacity-50 p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <span class="text-xs fw-bold text-light d-block">Published Caption:</span>
                    <small class="text-muted text-xs" id="galModalCaption"></small>
                </div>
                <button type="button" class="btn btn-secondary px-4 py-1.5 fw-bold shadow-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Close Window
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showGalleryModal(img, title, caption) {
        document.getElementById('galModalImg').src = img;
        document.getElementById('galModalTitle').textContent = title;
        document.getElementById('galModalCaption').textContent = caption || 'No caption available.';
    }
</script>
@endpush
