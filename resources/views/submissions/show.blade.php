@extends('layouts.app')

@section('title', $submission->title . ' - Activity Detail')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 p-4 bg-white rounded-4 shadow-sm border">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            <span class="badge bg-dark text-white font-monospace px-3 py-1 fs-6 shadow-sm">{{ $submission->reference_id }}</span>
            {!! $submission->status_badge !!}
            <span class="badge bg-light text-primary border px-3 py-1 fw-bold">{{ $submission->event_category }}</span>
        </div>
        <h2 class="fw-bold mb-1 text-dark">{{ $submission->title }}</h2>
        <p class="text-muted mb-0 small">
            Submitted by <strong class="text-dark">{{ $submission->submitter_name }}</strong> on {{ $submission->created_at->format('d M Y, h:i A') }}
        </p>
    </div>
    
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <a href="{{ url()->previous() }}" class="btn btn-light border px-4 py-2 shadow-sm d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i> Back to Directory
        </a>

        @if(($currentUser && ($submission->user_id === $currentUser->id || $currentUser->role === 'college_admin')) && in_array($submission->status, ['discuss', 'sent_back']))
            <a href="{{ route('submissions.edit', $submission->id) }}" class="btn btn-warning text-dark fw-bold px-4 py-2 shadow d-flex align-items-center gap-2">
                <i class="bi bi-pencil-square fs-5"></i> Edit & Resubmit Activity
            </a>
        @endif

        @if($currentUser && ($currentUser->hasRole(['office_registrar']) || $currentUser->isSuperAdmin()) && $submission->status === 'registrar_approval')
            @if($currentUser->isSuperAdmin() || $currentUser->hasPermission('approvals.approve'))
            <button type="button" class="btn btn-success fw-bold px-4 py-2 shadow d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#actionModal" onclick="setAction('approve', 'registrar_approval')">
                <i class="bi bi-shield-check fs-5"></i> Approve & Send to Editor
            </button>
            @endif
            @if($currentUser->isSuperAdmin() || $currentUser->hasPermission('approvals.discuss'))
            <button type="button" class="btn btn-warning fw-bold text-dark px-4 py-2 shadow d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#actionModal" onclick="setAction('discuss', 'registrar_approval')">
                <i class="bi bi-chat-dots-fill fs-5"></i> Discuss (Editor Correction)
            </button>
            @endif
            @if($currentUser->isSuperAdmin() || $currentUser->hasPermission('approvals.reject'))
            <button type="button" class="btn btn-danger fw-bold px-4 py-2 shadow d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#actionModal" onclick="setAction('reject', 'registrar_approval')">
                <i class="bi bi-x-circle-fill fs-5"></i> Reject
            </button>
            @endif
        @elseif($currentUser && $currentUser->hasRole(['registrar', 'vc', 'pro_vc', 'super_admin']) && in_array($submission->status, ['verification_pending', 'approval_pending']))
            @if($currentUser->isSuperAdmin() || $currentUser->hasPermission('approvals.approve'))
            <button type="button" class="btn btn-success fw-bold px-4 py-2 shadow d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#actionModal" onclick="setAction('approve', 'verification_pending')">
                <i class="bi bi-check-circle-fill fs-5"></i> Approve & Forward to Media
            </button>
            @endif
            @if($currentUser->isSuperAdmin() || $currentUser->hasPermission('approvals.discuss'))
            <button type="button" class="btn btn-warning fw-bold text-dark px-4 py-2 shadow d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#actionModal" onclick="setAction('discuss', 'verification_pending')">
                <i class="bi bi-chat-dots-fill fs-5"></i> Discuss
            </button>
            @endif
            @if($currentUser->isSuperAdmin() || $currentUser->hasPermission('approvals.reject'))
            <button type="button" class="btn btn-danger fw-bold px-4 py-2 shadow d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#actionModal" onclick="setAction('reject', 'verification_pending')">
                <i class="bi bi-x-circle-fill fs-5"></i> Reject
            </button>
            @endif
        @elseif($currentUser && $currentUser->hasRole(['media_editor', 'super_admin']))
            <a href="{{ route('media.downloadPack', $submission->id) }}" class="btn btn-outline-dark fw-bold px-4 py-2 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-zip-fill text-warning fs-5"></i> Download Media Pack (ZIP)
            </a>
            <a href="{{ route('media.dashboard') }}" class="btn btn-info text-white fw-bold px-4 py-2 shadow d-flex align-items-center gap-2">
                <i class="bi bi-broadcast fs-5"></i> Open in Media Editor Desk
            </a>
        @endif
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Main Detail Column -->
    <div class="col-12 col-lg-8">
        <!-- Event Overview Card -->
        <div class="portal-card p-4 p-md-5 mb-4 bg-white border">
            <h5 class="fw-bold mb-4 border-bottom pb-3 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-text-fill text-primary"></i> Event Overview & Institutional Parameters
            </h5>
            
            <div class="row mb-4 g-3 bg-light p-4 rounded-4 border mx-0">
                <div class="col-12 col-md-6">
                    <span class="text-muted text-xs text-uppercase fw-bold tracking-wider d-block mb-1">Constituent Campus / College</span>
                    <strong class="text-dark fs-6">{{ $submission->college ? $submission->college->code . ' - ' . $submission->college->name : 'N/A' }}</strong>
                </div>
                <div class="col-12 col-md-4">
                    <span class="text-muted text-xs text-uppercase fw-bold tracking-wider d-block mb-1">Activity Type</span>
                    <strong class="text-dark fs-6">{{ $submission->activity_type ?: 'Past Event' }}</strong>
                </div>
                <div class="col-12 col-md-4">
                    <span class="text-muted text-xs text-uppercase fw-bold tracking-wider d-block mb-1">Activity Date</span>
                    <strong class="text-dark fs-6">{{ $submission->activity_date ? $submission->activity_date->format('l, d F Y') : 'N/A' }}</strong>
                </div>
                <div class="col-12 col-md-4">
                    <span class="text-muted text-xs text-uppercase fw-bold tracking-wider d-block mb-1">Venue / Location</span>
                    <strong class="text-dark fs-6">{{ $submission->location ?: 'N/A' }}</strong>
                </div>
                <div class="col-12 col-md-4">
                    <span class="text-muted text-xs text-uppercase fw-bold tracking-wider d-block mb-1">Target Audience</span>
                    <strong class="text-dark fs-6">{{ $submission->target_audience ?: 'N/A' }}</strong>
                </div>
                <div class="col-12 col-md-8">
                    <span class="text-muted text-xs text-uppercase fw-bold tracking-wider d-block mb-1">Resource Person Name & Designation</span>
                    <strong class="text-dark fs-6">{{ $submission->resource_person_name ?: 'N/A' }}</strong>
                </div>
                <div class="col-12 mt-3">
                    <span class="text-muted text-xs text-uppercase fw-bold tracking-wider d-block mb-1">Outcome of the Program/Event</span>
                    <div class="p-3 bg-white rounded-3 text-dark mt-1 border shadow-sm">
                        {{ $submission->outcome ?: 'N/A' }}
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <span class="text-muted text-xs text-uppercase fw-bold tracking-wider d-block mb-2">Full Description & Highlights</span>
                <div class="p-4 bg-light rounded-4 border text-dark lh-lg" style="white-space: pre-line; font-size: 0.98rem;">
                    {{ $submission->description }}
                </div>
            </div>

            @if($submission->admin_remarks)
            <div class="alert alert-warning p-4 rounded-4 mb-0 border-warning d-flex align-items-start gap-3 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill fs-3 text-warning flex-shrink-0"></i>
                <div>
                    <h6 class="fw-bold text-dark mb-1">Official Review Authority Remark:</h6>
                    <p class="mb-0 text-dark font-monospace small">{{ $submission->admin_remarks }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Forwarded Social Media Caption Suggestion Accordion -->
        <div class="accordion mb-4 shadow-sm rounded-4 overflow-hidden border" id="captionAccordion">
            <div class="accordion-item border-0 bg-white" style="border-left: 4px solid #3b82f6 !important;">
                <h2 class="accordion-header" id="headingCaption">
                    <button class="accordion-button collapsed fw-bold p-4 bg-white text-dark d-flex align-items-center justify-content-between shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCaption" aria-expanded="false" aria-controls="collapseCaption" style="font-size: 1.05rem;">
                        <div class="d-flex align-items-center flex-wrap gap-2 pe-3">
                            <i class="bi bi-broadcast text-primary fs-4"></i>
                            <span>Forwarded Social Media Caption Suggestion</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1 font-monospace text-xxs">AI / PR Drafted</span>
                        </div>
                    </button>
                </h2>
                <div id="collapseCaption" class="accordion-collapse collapse" aria-labelledby="headingCaption" data-bs-parent="#captionAccordion">
                    <div class="accordion-body p-4 p-md-5 pt-0 border-top bg-white">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 my-3 pb-3 border-bottom">
                            <span class="text-muted small"><i class="bi bi-info-circle text-primary me-1"></i> Tailored phrasing forwarded along with media attachments for institutional review and broadcast.</span>
                            <button type="button" class="btn btn-outline-primary btn-sm px-3 py-1.5 fw-bold d-flex align-items-center gap-2 shadow-sm" onclick="copyCaptionToClipboard('showCaptionText', this)">
                                <i class="bi bi-clipboard"></i> Copy Caption Brief
                            </button>
                        </div>

                        <div class="p-4 bg-light rounded-4 border font-monospace text-dark lh-lg shadow-inner" id="showCaptionText" style="white-space: pre-line; font-size: 0.92rem; background-color: #f8fafc !important;">{{ $submission->social_media_caption ?: \App\Http\Controllers\ActivitySubmissionController::generateDefaultCaptionForSubmission($submission) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $originalMedia = $submission->media->filter(fn($m) => $m->media_category !== 'edited_social');
            $editedMedia = $submission->media->filter(fn($m) => $m->media_category === 'edited_social');
            $editedMediaGroups = $editedMedia->sortBy('created_at')->groupBy(fn($m) => $m->created_at->format('Y-m-d H:i'))->values();
            
            $galleryMedia = $originalMedia->filter(function($m) {
                return in_array($m->file_type, ['image', 'geo_image', 'video', 'link']);
            });
            $supportingDocs = $originalMedia->filter(function($m) {
                return in_array($m->file_type, ['document', 'supporting_document', 'report_document']);
            });
        @endphp

        <!-- Attached Media Gallery Card (Original Submissions) -->
        <div class="portal-card p-4 p-md-5 mb-4 bg-white border">
            <h5 class="fw-bold mb-4 border-bottom pb-3 text-dark d-flex align-items-center justify-content-between">
                <span><i class="bi bi-images text-primary me-2"></i>Original image uploaded from user</span>
                <span class="badge bg-light text-dark border px-3 py-1">{{ $galleryMedia->count() }} Files Attached</span>
            </h5>
            
            <div class="row g-4">
                @forelse($galleryMedia as $m)
                    @php
                        $isImageDoc = in_array($m->file_type, ['document', 'supporting_document', 'report_document']) && preg_match('/(image|jpg|jpeg|png|webp)/i', $m->mime_type . $m->file_name);
                    @endphp
                    @if($m->file_type === 'image' || $isImageDoc)
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="gallery-item border rounded-4 shadow-sm overflow-hidden bg-light position-relative">
                            <img src="{{ $m->file_path }}" alt="{{ $m->file_name }}" class="w-100 object-fit-cover" style="height: 220px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImageModal('{{ $m->file_path }}', '{{ addslashes($m->file_name) }}')">
                            <div class="gallery-overlay p-3">
                                <div class="fw-bold text-xs text-white text-truncate">{{ $m->file_name }}</div>
                                <span class="badge bg-primary mt-1 text-xxs">{{ $m->file_type === 'image' ? ($m->is_primary ? 'Primary Post Photo' : 'Gallery Image') : 'Supporting Image' }}</span>
                            </div>
                        </div>
                    </div>
                    @elseif($m->file_type === 'geo_image')
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="gallery-item border rounded-4 shadow-sm overflow-hidden position-relative" style="background-color: #f0fdf4; border-color: #22c55e !important;">
                            <img src="{{ $m->file_path }}" alt="{{ $m->file_name }}" class="w-100 object-fit-cover" style="height: 220px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImageModal('{{ $m->file_path }}', '{{ addslashes($m->file_name) }}')">
                            <div class="gallery-overlay p-3 d-flex flex-column justify-content-end">
                                <div class="fw-bold text-xs text-white text-truncate">{{ $m->file_name }}</div>
                                <span class="badge bg-success mt-1 text-xxs align-self-start"><i class="bi bi-geo-alt-fill me-1"></i>Geo-Tagged Photo</span>
                            </div>
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="col-12 text-center py-5 bg-light rounded-4 text-muted">
                        <i class="bi bi-images fs-1 d-block mb-2 opacity-50"></i>
                        <span>No original media attachments found for this institutional activity.</span>
                    </div>
                @endforelse
            </div>
        </div>

        @if($supportingDocs->count() > 0)
        <!-- Supporting Documents Card -->
        <div class="portal-card p-4 p-md-5 mb-4 bg-white border shadow-sm">
            <h5 class="fw-bold mb-4 border-bottom pb-3 text-dark d-flex align-items-center justify-content-between">
                <span><i class="bi bi-file-earmark-text text-danger me-2"></i>Supporting Documents & Reports</span>
                <span class="badge bg-light text-dark border px-3 py-1">{{ $supportingDocs->count() }} Documents Attached</span>
            </h5>
            
            <div class="row g-4">
                @foreach($supportingDocs as $m)
                    @php
                        $isImageDoc = preg_match('/(image|jpg|jpeg|png|webp)/i', $m->mime_type . $m->file_name);
                    @endphp
                    @if($isImageDoc)
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="gallery-item border rounded-4 shadow-sm overflow-hidden bg-light position-relative">
                            <img src="{{ $m->file_path }}" alt="{{ $m->file_name }}" class="w-100 object-fit-cover" style="height: 220px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImageModal('{{ $m->file_path }}', '{{ addslashes($m->file_name) }}')">
                            <div class="gallery-overlay p-3">
                                <div class="fw-bold text-xs text-white text-truncate">{{ $m->file_name }}</div>
                                <span class="badge bg-primary mt-1 text-xxs">Supporting Image</span>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="gallery-item border rounded-4 shadow-sm overflow-hidden position-relative bg-light">
                            @php
                                $isPdf = str_contains(strtolower($m->file_name), '.pdf') || str_contains(strtolower($m->mime_type), 'pdf') ? 'true' : 'false';
                            @endphp
                            <div class="d-flex align-items-center justify-content-center w-100 bg-white" style="height: 220px; cursor: pointer;" onclick="showDocumentModal('{{ $m->file_path }}', '{{ addslashes($m->file_name) }}', {{ $isPdf }})" data-bs-toggle="modal" data-bs-target="#documentModal">
                                @php
                                    $iconClass = 'bi-file-earmark text-secondary';
                                    if(str_contains(strtolower($m->file_name), '.pdf') || str_contains(strtolower($m->mime_type), 'pdf')) $iconClass = 'bi-file-earmark-pdf-fill text-danger';
                                    elseif(str_contains(strtolower($m->file_name), '.doc') || str_contains(strtolower($m->mime_type), 'word')) $iconClass = 'bi-file-earmark-word-fill text-primary';
                                    elseif(str_contains(strtolower($m->file_name), '.xls') || str_contains(strtolower($m->mime_type), 'excel')) $iconClass = 'bi-file-earmark-excel-fill text-success';
                                @endphp
                                <i class="bi {{ $iconClass }}" style="font-size: 5rem;"></i>
                            </div>
                            <div class="gallery-overlay p-3 d-flex flex-column justify-content-end">
                                <div class="fw-bold text-xs text-white text-truncate" title="{{ $m->file_name }}">{{ $m->file_name }}</div>
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <span class="badge bg-secondary text-xxs">{{ $m->file_size ? number_format($m->file_size / 1024, 1) . ' KB' : 'Document' }}</span>
                                    <a href="javascript:void(0)" onclick="showDocumentModal('{{ $m->file_path }}', '{{ addslashes($m->file_name) }}', {{ $isPdf }})" data-bs-toggle="modal" data-bs-target="#documentModal" class="btn btn-sm btn-light py-0 px-2 text-xxs fw-bold"><i class="bi bi-box-arrow-up-right me-1"></i>Preview</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif


        @if($editedMedia->count() > 0)
        <!-- Edited Media Package - Media Team Section -->
        <div class="portal-card p-4 p-md-5 mb-4 border shadow-sm" style="background: #f8fafc; border-color: #6366f1 !important;">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-magic text-primary fs-4"></i> Edited image uploaded from editor
                        <span class="badge bg-primary text-white text-xxs font-monospace">Media Studio Desk Package</span>
                    </h5>
                    <p class="text-muted small mb-0">High-resolution cropped and refined social media banners prepared specifically for institutional broadcast.</p>
                </div>
                <span class="badge bg-dark text-white px-3 py-2 rounded-pill shadow-sm">{{ $editedMedia->count() }} Edited Photo(s)</span>
            </div>
            
            @foreach($editedMediaGroups as $index => $group)
                <div class="mb-4">
                    <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2">
                        @if($index === 0)
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> 1st Submitted Image(s)
                        @else
                            <i class="bi bi-arrow-repeat me-1"></i> Reworked image(s) after resubmit
                        @endif
                        <span class="badge bg-light text-muted ms-2 fw-normal text-xxs">{{ $group->first()->created_at->format('d M Y, h:i A') }}</span>
                    </h6>
                    <div class="row g-4">
                        @foreach($group as $m)
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="gallery-item border rounded-4 shadow-sm overflow-hidden bg-white position-relative" style="border-color: #818cf8 !important;">
                                    <img src="{{ $m->file_path }}" alt="{{ $m->file_name }}" class="w-100 object-fit-cover" style="height: 240px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImageModal('{{ $m->file_path }}', '{{ addslashes($m->file_name) }}')">
                                    <div class="gallery-overlay p-3 d-flex flex-column justify-content-end">
                                        <div class="fw-bold text-xs text-white text-truncate">{{ $m->file_name }}</div>
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <span class="badge bg-success text-xxs"><i class="bi bi-check-circle me-1"></i>Social Ready</span>
                                            <a href="{{ $m->file_path }}" download class="btn btn-sm btn-light py-0 px-2 text-xxs fw-bold"><i class="bi bi-download me-1"></i>Download</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        @endif
        <!-- Social Media Publication Details -->
        @if($submission->publishedPost)
        <div class="portal-card p-4 p-md-5 mb-4 bg-white border shadow-sm">
            <h5 class="fw-bold mb-4 border-bottom pb-3 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-broadcast text-success"></i> Social Media Publication Details
            </h5>
            
            <div class="row g-4">
                @if(in_array('Instagram', $submission->publishedPost->platforms ?? []))
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-4 border d-flex align-items-center gap-3">
                        <i class="bi bi-instagram fs-3 text-danger"></i>
                        <div>
                            <div class="fw-bold text-dark mb-1">Instagram</div>
                            @if($submission->publishedPost->post_url_instagram)
                                <a href="{{ $submission->publishedPost->post_url_instagram }}" target="_blank" class="small text-decoration-none text-primary fw-bold">View Post <i class="bi bi-box-arrow-up-right ms-1"></i></a>
                            @else
                                <span class="small text-muted">Link not provided</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
                @if(in_array('Facebook', $submission->publishedPost->platforms ?? []))
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-4 border d-flex align-items-center gap-3">
                        <i class="bi bi-facebook fs-3 text-primary"></i>
                        <div>
                            <div class="fw-bold text-dark mb-1">Facebook</div>
                            @if($submission->publishedPost->post_url_facebook)
                                <a href="{{ $submission->publishedPost->post_url_facebook }}" target="_blank" class="small text-decoration-none text-primary fw-bold">View Post <i class="bi bi-box-arrow-up-right ms-1"></i></a>
                            @else
                                <span class="small text-muted">Link not provided</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
                @if(in_array('LinkedIn', $submission->publishedPost->platforms ?? []))
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-4 border d-flex align-items-center gap-3">
                        <i class="bi bi-linkedin fs-3" style="color: #0077b5;"></i>
                        <div>
                            <div class="fw-bold text-dark mb-1">LinkedIn</div>
                            @if($submission->publishedPost->post_url_linkedin)
                                <a href="{{ $submission->publishedPost->post_url_linkedin }}" target="_blank" class="small text-decoration-none text-primary fw-bold">View Post <i class="bi bi-box-arrow-up-right ms-1"></i></a>
                            @else
                                <span class="small text-muted">Link not provided</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
                @if(in_array('X (Twitter)', $submission->publishedPost->platforms ?? []))
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-4 border d-flex align-items-center gap-3">
                        <i class="bi bi-twitter-x fs-3 text-dark"></i>
                        <div>
                            <div class="fw-bold text-dark mb-1">X (Twitter)</div>
                            @if($submission->publishedPost->post_url_twitter)
                                <a href="{{ $submission->publishedPost->post_url_twitter }}" target="_blank" class="small text-decoration-none text-primary fw-bold">View Post <i class="bi bi-box-arrow-up-right ms-1"></i></a>
                            @else
                                <span class="small text-muted">Link not provided</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="mt-4 p-4 bg-light rounded-4 border">
                <h6 class="fw-bold text-dark mb-2 text-uppercase tracking-wider small">Approved Broadcast Caption</h6>
                <div class="font-monospace text-muted small" style="white-space: pre-wrap;">{{ $submission->publishedPost->published_caption }}</div>
            </div>
        </div>
        @endif

        <!-- Collaborative Discussion & Remarks Desk -->
        <div class="portal-card p-4 p-md-5 mb-4 bg-white border shadow-sm">
            <h5 class="fw-bold mb-4 border-bottom pb-3 text-dark d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span><i class="bi bi-chat-dots-fill text-primary me-2"></i>Institutional Discussion Desk</span>
                <span class="badge bg-info text-white px-3 py-1 rounded-pill">{{ $submission->discussions->count() }} Remarks & Queries</span>
            </h5>

            <div class="discussion-thread mb-4 pe-2" style="max-height: 420px; overflow-y: auto;">
                @forelse($submission->discussions as $disc)
                    @if($disc->is_internal && $currentUser && $currentUser->role === 'college_admin')
                        @continue
                    @endif
                    <div class="d-flex gap-3 mb-3 p-4 rounded-4 {{ $disc->user_id === ($currentUser ? $currentUser->id : 0) ? 'bg-primary bg-opacity-10 border border-primary border-opacity-25' : 'bg-light border' }}">
                        <div class="flex-shrink-0">
                            @php
                                $discAvatar = ($disc->user && $disc->user->avatar) ? $disc->user->avatar : (!empty($globalDefaultAvatar) ? $globalDefaultAvatar : null);
                            @endphp
                            @if($discAvatar)
                                <img src="{{ asset($discAvatar) }}" alt="Avatar" class="rounded-circle shadow-sm border border-secondary" style="width: 44px; height: 44px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 44px; height: 44px; background: linear-gradient(135deg, #0f172a 0%, #3b82f6 100%) !important;">
                                    {{ substr($disc->author_name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
                                <div>
                                    <span class="fw-bold fs-6 text-dark">{{ $disc->author_name }}</span>
                                    @if($disc->user)
                                        <span class="badge bg-secondary ms-1 text-xxs">{{ $disc->user->role_title }}</span>
                                    @endif
                                    @if($disc->is_internal)
                                        <span class="badge bg-danger ms-1 text-xxs"><i class="bi bi-lock-fill"></i> Internal Authority Note</span>
                                    @endif
                                </div>
                                <small class="text-muted text-xs">{{ $disc->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="text-dark small mt-2 font-monospace p-2 bg-white rounded border">{{ $disc->comment }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 bg-light rounded-4 text-muted small border border-dashed">
                        <i class="bi bi-chat-square-text fs-2 d-block mb-2 opacity-50"></i>
                        No discussion notes or queries posted yet. Start the institutional dialogue below.
                    </div>
                @endforelse
            </div>

            <!-- Add Remark Form -->
            <form action="{{ route('submissions.discussions.store', $submission->id) }}" method="POST" class="p-4 bg-light rounded-4 border">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Post a discussion remark, instruction, or query regarding this activity:</label>
                    <textarea name="comment" rows="3" class="form-control border-2 bg-white" required placeholder="Type institutional feedback, correction request, or confirmation advice..."></textarea>
                </div>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    @if($currentUser && $currentUser->role !== 'college_admin')
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="internalCheck">
                            <label class="form-check-label small fw-bold text-danger" for="internalCheck">
                                <i class="bi bi-shield-lock-fill"></i> Internal Note (Hidden from College Representative)
                            </label>
                        </div>
                    @else
                        <div></div>
                    @endif
                    <button type="submit" class="btn btn-portal-primary px-4 py-2 fw-bold shadow-sm">
                        <i class="bi bi-send-fill me-1"></i> Post Remark
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Side Information Column -->
    <div class="col-12 col-lg-4">
        
        @if($currentUser && $currentUser->role === 'media')
        <!-- Media Desk Actions -->
        <div class="portal-card p-4 bg-white border mb-4 border-primary border-top border-4 shadow-sm">
            <h5 class="fw-bold mb-3 pb-2 text-dark d-flex align-items-center gap-2 border-bottom">
                <i class="bi bi-lightning-charge-fill text-warning"></i> Media Desk Actions
            </h5>
            
            <div class="d-flex flex-column gap-3 mt-3">
                <a href="{{ route('media.downloadPack', $submission->id) }}" class="btn btn-outline-dark fw-bold shadow-sm d-flex justify-content-between align-items-center px-3 py-2">
                    <span><i class="bi bi-file-earmark-zip-fill text-warning fs-5 me-2"></i> Download Pack (ZIP)</span>
                    <i class="bi bi-download"></i>
                </a>

                @if($submission->status === 'registrar_approved')
                <button type="button" class="btn btn-success fw-bold shadow d-flex justify-content-between align-items-center px-3 py-2" data-bs-toggle="modal" data-bs-target="#publishModal" data-bs-id="{{ $submission->id }}" data-bs-title="{{ $submission->title }}" data-bs-caption="{{ $submission->social_media_caption }}">
                    <span><i class="bi bi-rocket-takeoff-fill fs-5 me-2"></i> Option A: Reviewed & Published</span>
                    <i class="bi bi-arrow-right-circle-fill"></i>
                </button>
                @endif
                
                @if(in_array($submission->status, ['editor_queue', 'editor_discuss']))
                <button type="button" class="btn btn-primary fw-bold shadow-sm d-flex justify-content-between align-items-center px-3 py-2" data-bs-toggle="modal" data-bs-target="#submitRegistrarModal" data-bs-id="{{ $submission->id }}" data-bs-title="{{ $submission->title }}" data-bs-caption="{{ $submission->social_media_caption ?: $submission->title }}" data-bs-images-count="{{ $submission->media->where('file_type', 'image')->count() }}">
                    <span><i class="bi bi-pencil-square fs-5 me-2"></i> Option B: Submit Corrections</span>
                    <i class="bi bi-arrow-right-circle-fill"></i>
                </button>
                @endif
            </div>
        </div>
        @endif

        <!-- Workflow Audit Trail -->
        <div class="portal-card p-4 bg-white border">
            <h5 class="fw-bold mb-3 border-bottom pb-3 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i> Workflow Audit Trail
            </h5>
            <div class="timeline mt-4">
                @forelse($submission->approvalLogs as $log)
                <div class="timeline-item pb-4">
                    <div class="timeline-dot"></div>
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark text-capitalize">{{ str_replace('_', ' ', $log->action) }}</span>
                            <small class="text-muted text-xxs">{{ $log->created_at->format('d M, H:i') }}</small>
                        </div>
                        <div class="small text-muted mb-2">
                            By <strong class="text-dark">{{ $log->actor_name }}</strong>
                            @if($log->user && $log->user->designation)
                                <span class="text-xs text-muted">({{ $log->user->designation }})</span>
                            @elseif($log->user)
                                <span class="text-xs text-muted">({{ $log->user->role_title }})</span>
                            @endif
                        </div>
                        @if($log->remarks)
                            <div class="p-2 bg-white rounded border small text-dark font-monospace text-xs">{{ $log->remarks }}</div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted small">No audit logs recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Authority Action Review Modal -->
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('approvals.action', $submission->id) }}" method="POST">
                @csrf
                <input type="hidden" name="action" id="actionInput" value="">
                <div class="modal-header bg-dark text-white p-4">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="actionModalHeader">Process Activity Submission</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark text-uppercase tracking-wider">Review Remarks / Decision Rationale</label>
                        <textarea name="remarks" rows="4" class="form-control" placeholder="Enter official remarks, verification notes, or required correction queries..."></textarea>
                        <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i> For 'Discuss', your remark will be sent to the responsible party as actionable guidance for correction.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-portal-primary px-4 fw-bold" id="actionModalSubmitBtn">Confirm Review Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Lightbox Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-dark text-white">
            <div class="modal-header bg-dark border-bottom border-secondary border-opacity-50 p-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-image-fill text-primary"></i>
                    <span id="imageModalTitle" class="text-truncate">Attached Media Preview</span>
                </h6>
                <button type="button" class="btn btn-danger btn-sm rounded-pill px-4 py-1.5 fw-bold d-flex align-items-center gap-1 shadow" data-bs-dismiss="modal" title="Close Image Preview">
                    <i class="bi bi-x-lg"></i> Close Preview
                </button>
            </div>
            <div class="modal-body p-4 text-center d-flex align-items-center justify-content-center" style="min-height: 400px; background: #0f172a;">
                <img src="" id="imageModalImg" class="img-fluid rounded-3 shadow-lg border border-secondary border-opacity-25" style="max-height: 75vh; object-fit: contain;">
            </div>
            <div class="modal-footer bg-dark border-top border-secondary border-opacity-50 p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <a id="imageModalDownload" href="#" target="_blank" class="btn btn-outline-light btn-sm px-3 d-flex align-items-center gap-2">
                    <i class="bi bi-box-arrow-up-right"></i> Open Full Resolution
                </a>
                <button type="button" class="btn btn-secondary px-4 py-1.5 fw-bold shadow-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Close Window
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div class="modal fade" id="documentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-white">
            <div class="modal-header bg-light border-bottom p-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-text text-danger"></i>
                    <span id="documentModalTitle" class="text-truncate">Document Preview</span>
                </h6>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-1.5 fw-bold d-flex align-items-center gap-1 shadow-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i> Close
                </button>
            </div>
            <div class="modal-body p-0 text-center d-flex align-items-center justify-content-center" style="height: 75vh; background: #f8fafc; position: relative;">
                <iframe src="" id="documentModalIframe" class="w-100 h-100 border-0 d-none"></iframe>
                <div id="documentModalFallback" class="d-none flex-column align-items-center justify-content-center p-5 w-100 h-100">
                    <i class="bi bi-file-earmark-x text-muted mb-3" style="font-size: 5rem;"></i>
                    <h5 class="fw-bold text-dark">Preview Not Available</h5>
                    <p class="text-muted">This document format cannot be previewed directly in the browser.</p>
                    <a id="documentModalDownloadBtn" href="#" target="_blank" class="btn btn-primary mt-3 px-4 py-2 fw-bold rounded-pill shadow-sm">
                        <i class="bi bi-download me-2"></i> Download Document Instead
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if($currentUser && $currentUser->role === 'media')
    @include('partials.media-action-modals')
    @include('partials.ai-phrasing-modal')
@endif

@push('scripts')
@if($currentUser && $currentUser->role === 'media')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        @include('partials.media-action-scripts')
    });
</script>
    @include('partials.client-image-optimizer')
@endif
<script>
    function showImageModal(src, title) {
        document.getElementById('imageModalImg').src = src;
        document.getElementById('imageModalTitle').textContent = title || 'Attached Media Preview';
        const downloadBtn = document.getElementById('imageModalDownload');
        if (downloadBtn) downloadBtn.href = src;
    }

    function showDocumentModal(src, title, isPdf) {
        document.getElementById('documentModalTitle').textContent = title || 'Document Preview';
        const iframe = document.getElementById('documentModalIframe');
        const fallback = document.getElementById('documentModalFallback');
        const dlBtn = document.getElementById('documentModalDownloadBtn');
        
        if (isPdf) {
            iframe.src = src;
            iframe.classList.remove('d-none');
            fallback.classList.add('d-none');
            fallback.classList.remove('d-flex');
        } else {
            iframe.src = '';
            iframe.classList.add('d-none');
            fallback.classList.remove('d-none');
            fallback.classList.add('d-flex');
            dlBtn.href = src;
        }
    }

    function setAction(type, status = 'verification_pending') {
        const input = document.getElementById('actionInput');
        const header = document.getElementById('actionModalHeader');
        const btn = document.getElementById('actionModalSubmitBtn');
        input.value = type;

        if (status === 'registrar_approval') {
            if (type === 'approve') {
                header.textContent = 'Approve & Return to Editor for Publication';
                btn.className = 'btn btn-success fw-bold px-4 py-2';
                btn.textContent = 'Approve & Send to Editor';
            } else if (type === 'discuss' || type === 'send_back') {
                header.textContent = 'Discuss (Return to Editor for Correction)';
                btn.className = 'btn btn-warning fw-bold text-dark px-4 py-2';
                btn.textContent = 'Return to Editor for Correction';
            } else {
                header.textContent = 'Reject Media Submission';
                btn.className = 'btn btn-danger fw-bold px-4 py-2';
                btn.textContent = 'Confirm Rejection';
            }
        } else {
            if (type === 'approve') {
                header.textContent = 'Approve Activity & Forward to Media Desk';
                btn.className = 'btn btn-success fw-bold px-4 py-2';
                btn.textContent = 'Approve & Forward to Media';
            } else if (type === 'discuss' || type === 'send_back') {
                header.textContent = 'Mark as Discuss (Return for User Correction)';
                btn.className = 'btn btn-warning fw-bold text-dark px-4 py-2';
                btn.textContent = 'Confirm Discuss & Return';
            } else {
                header.textContent = 'Reject Activity Submission';
                btn.className = 'btn btn-danger fw-bold px-4 py-2';
                btn.textContent = 'Reject Submission';
            }
        }
    }

    function copyCaptionToClipboard(elementId, btn) {
        const textElement = document.getElementById(elementId);
        if (!textElement) return;
        const text = textElement.innerText;
        navigator.clipboard.writeText(text).then(() => {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        }).catch(err => {
            alert('Could not copy text: ' + err);
        });
    }
</script>
@endpush
