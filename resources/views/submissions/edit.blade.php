@extends('layouts.app')

@section('title', 'Edit & Resubmit Activity')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-11">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <span class="badge bg-warning text-dark px-3 py-1 mb-2 shadow-sm rounded-pill"><i class="bi bi-pencil-square me-1"></i> Resubmission Correction Mode</span>
                <h3 class="fw-bold text-dark mb-1">Edit & Resubmit Activity</h3>
                <p class="text-muted mb-0">Update institutional activity parameters as requested by the verification authority</p>
            </div>
            <a href="{{ route('submissions.show', $submission->id) }}" class="btn btn-light border px-4 py-2 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Back to Submission
            </a>
        </div>

        @if($submission->admin_remarks)
            <div class="alert alert-warning border-warning shadow-sm rounded-4 p-4 mb-4 d-flex align-items-start gap-3">
                <i class="bi bi-exclamation-triangle-fill fs-3 text-warning flex-shrink-0"></i>
                <div>
                    <h6 class="fw-bold text-dark mb-1">Official Verification Authority Instruction:</h6>
                    <p class="mb-0 text-dark font-monospace small">{{ $submission->admin_remarks }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger shadow-sm rounded-4 p-4 mb-4">
                <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Please correct the following input errors:</h6>
                <ul class="mb-0 small">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Card -->
        <div class="portal-card p-4 p-md-5 shadow-lg bg-white border">
            <form action="{{ route('submissions.update', $submission->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-5">
                    <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-4">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px;">1</div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Institutional Information</h5>
                            <span class="text-muted text-xs">Verify campus classification and event category</span>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-uppercase tracking-wider">Constituent College / School <span class="text-danger">*</span></label>
                            @if($currentUser && $currentUser->college_id)
                                <input type="hidden" name="college_id" value="{{ $submission->college_id }}">
                                <select class="form-select border-2 py-2 bg-light text-muted" disabled>
                                    @foreach($colleges as $col)
                                        <option value="{{ $col->id }}" {{ $submission->college_id == $col->id ? 'selected' : '' }}>
                                            {{ $col->code }} - {{ $col->name }} (Fixed)
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <select name="college_id" class="form-select border-2 py-2" required>
                                    @foreach($colleges as $col)
                                        <option value="{{ $col->id }}" {{ $submission->college_id == $col->id ? 'selected' : '' }}>
                                            {{ $col->code }} - {{ $col->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-uppercase tracking-wider">Activity Category <span class="text-danger">*</span></label>
                            <select name="event_category" class="form-select border-2 py-2" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->name }}" {{ $submission->event_category == $cat->name ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-4">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px;">2</div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Activity Details & Parameters</h5>
                            <span class="text-muted text-xs">Correct activity title, date, audience, and comprehensive description</span>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-uppercase tracking-wider">Activity Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control border-2 py-2" value="{{ old('title', $submission->title) }}" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-uppercase tracking-wider">Activity Type <span class="text-danger">*</span></label>
                            <select name="activity_type" id="activityType" class="form-select border-2 py-2" required>
                                <option value="Past Event" {{ old('activity_type', $submission->activity_type) == 'Past Event' ? 'selected' : '' }}>Past Event</option>
                                <option value="Future Event" {{ old('activity_type', $submission->activity_type) == 'Future Event' ? 'selected' : '' }}>Future Event</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-uppercase tracking-wider">Activity Date <span class="text-danger">*</span></label>
                            <input type="date" name="activity_date" id="activityDate" class="form-control border-2 py-2" value="{{ old('activity_date', $submission->activity_date ? $submission->activity_date->format('Y-m-d') : '') }}" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-uppercase tracking-wider">Venue / Campus Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control border-2 py-2" required value="{{ old('location', $submission->location) }}" placeholder="e.g. Main Auditorium">
                        </div>

                        <div class="col-12 col-md-4" id="participantsContainer">
                            <label class="form-label fw-bold small text-uppercase tracking-wider">Participants Attended <span class="text-danger">*</span></label>
                            <div class="form-text text-muted mb-2 fst-italic">Note: Please provide the total number of participants, attendees, or faculty members who attended the event.</div>
                            <input type="text" id="targetAudience" name="target_audience" class="form-control border-2 py-2" required value="{{ old('target_audience', $submission->target_audience) }}" placeholder="e.g. 45 Faculty, 180 Students">
                        </div>

                        <div class="col-12 col-md-4" id="resourcePersonContainer">
                            <label class="form-label fw-bold small text-uppercase tracking-wider">Resource Person Name & Designation <span class="text-muted">(Optional)</span></label>
                            <input type="text" name="resource_person_name" class="form-control border-2 py-2" placeholder="e.g., Dr. Jane Doe" value="{{ old('resource_person_name', $submission->resource_person_name) }}">
                        </div>

                        <div class="col-12 col-md-4" id="outcomeContainer">
                            <label class="form-label fw-bold small text-uppercase tracking-wider">Outcome of the Program/Event <span class="text-danger">*</span></label>
                            <input type="text" id="outcome" name="outcome" class="form-control border-2 py-2" required placeholder="e.g., Participants gained practical insights, 2 MoUs signed..." value="{{ old('outcome', $submission->outcome) }}">
                        </div>

                        <div class="col-12 col-md-4" id="reportTypeContainer">
                            <label class="form-label fw-bold small text-uppercase tracking-wider">Event Report Type <span class="text-danger">*</span></label>
                            <select name="report_type" id="reportTypeSelect" class="form-select border-2 py-2" required>
                                <option value="" disabled>Select Report Type</option>
                                <option value="By Text" {{ old('report_type', $submission->report_type) == 'By Text' ? 'selected' : '' }}>By Text</option>
                                <option value="By Attach File" {{ old('report_type', $submission->report_type) == 'By Attach File' ? 'selected' : '' }}>By Attach File</option>
                            </select>
                        </div>

                        <div class="col-12" id="textReportContainer" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <label class="form-label fw-bold small text-uppercase tracking-wider mb-0">Event Report Description <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-sm btn-outline-primary fw-bold d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#aiPhrasingModal" data-target-input="#textDescription">
                                    <i class="bi bi-magic"></i> AI Generation Phrasing
                                </button>
                            </div>
                            <textarea id="textDescription" rows="6" class="form-control border-2" placeholder="Provide a thorough overview of the activity or click AI Generation Phrasing above...">{{ old('report_type', $submission->report_type) == 'By Text' ? old('description', $submission->description) : '' }}</textarea>
                        </div>

                        <div class="col-12" id="fileReportContainer" style="display: none;">
                            <label class="form-label fw-bold small text-uppercase tracking-wider mb-2">Upload Event Report <span class="text-danger">*</span></label>
                            <input type="file" id="reportDocument" class="form-control border-2 py-2" accept=".pdf,.doc,.docx">
                            <div class="form-text mt-2"><i class="bi bi-file-earmark-pdf me-1"></i> Upload the report as PDF or Word Document. <span class="text-danger">Max 20 MB.</span></div>
                            @php
                                $existingReport = $submission->media->where('file_type', 'report_document')->first();
                            @endphp
                            @if($existingReport)
                                <div class="mt-2 text-success small fw-bold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Existing report attached: <a href="{{ upload_url($existingReport->file_path) }}" target="_blank" class="text-success">{{ $existingReport->file_name }}</a>. Upload a new file to replace it.
                                </div>
                            @endif
                            <div id="reportDocumentPreviewContainer" class="d-flex flex-wrap gap-3 mt-3"></div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Social Media Caption & PR Desk -->
                <div class="mb-5 p-4 bg-light bg-opacity-75 rounded-4 border">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px;">2</div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Social Media Caption Suggestion</h5>
                                <span class="text-muted text-xs">AI-drafted phrasing tailored for university public broadcast channels</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-outline-dark px-3 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#aiPhrasingModal" data-target-input="#socialMediaCaption">
                                <i class="bi bi-collection"></i> Select Template Library
                            </button>
                            <button type="button" id="generateAiBtn" class="btn btn-portal-primary px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                                <i class="bi bi-magic"></i> AI Generate Caption
                            </button>
                        </div>
                    </div>

                    <div>
                        <textarea name="social_media_caption" id="socialMediaCaption" rows="4" class="form-control font-monospace small bg-white" placeholder="Click 'AI Generate Caption' or 'Select Template Library' above...">{{ old('social_media_caption', $submission->social_media_caption ?: \App\Http\Controllers\ActivitySubmissionController::generateDefaultCaptionForSubmission($submission)) }}</textarea>
                    </div>
                </div>

                <div class="mb-5">
                    <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-4">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px;">3</div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Media Gallery & Attachments Management</h5>
                            <span class="text-muted text-xs">Edit, replace, or remove uploaded photos, videos, and documents before resubmitting</span>
                        </div>
                    </div>

                    @php
                        $existingImages = $submission->media->where('file_type', 'image');
                        $existingLink = $submission->media->where('file_type', 'link')->first();
                        $existingVideo = $submission->media->where('file_type', 'video')->first();
                        $existingDocs = $submission->media->whereIn('file_type', ['document', 'supporting_document']);
                        $existingGeoImages = $submission->media->where('file_type', 'geo_image');
                    @endphp

                    <div class="row g-4">
                        <div class="col-12 col-md-6" id="imagesContainer">
                            <!-- 1. Existing Photos Management -->
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase tracking-wider">Existing Event Photos ({{ $existingImages->count() }} files):</label>
                                <p class="text-xs text-muted mb-2">Existing attachments remain visible until checked for removal or replaced during resubmission.</p>
                                <div class="row g-3 mt-1 mb-3">
                                    @forelse($existingImages as $m)
                                        <div class="col-6 col-sm-4 col-md-3" id="media_col_{{ $m->id }}">
                                            <div class="border rounded-4 p-2 bg-light text-center shadow-sm position-relative overflow-hidden h-100 d-flex flex-column justify-content-between transition-all" id="media_card_{{ $m->id }}">
                                                <div>
                                                    @if($m->is_primary)
                                                        <span class="badge bg-primary position-absolute top-0 start-0 m-2 text-xxs shadow">Primary</span>
                                                    @endif
                                                    <img src="{{ upload_url($m->file_path) }}" alt="{{ $m->file_name }}" class="rounded-3 w-100 object-fit-cover border" style="height: 110px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImageModal('{{ upload_url($m->file_path) }}', '{{ addslashes($m->file_name) }}')">
                                                    <small class="d-block text-truncate text-xs fw-semibold text-dark mt-2" title="{{ $m->file_name }}">{{ $m->file_name }}</small>
                                                </div>
                                                <div class="mt-2 pt-2 border-top">
                                                    <input type="checkbox" name="remove_media_ids[]" value="{{ $m->id }}" class="btn-check remove-media-toggle" id="remove_media_{{ $m->id }}" data-card="#media_card_{{ $m->id }}">
                                                    <label class="btn btn-sm btn-outline-danger w-100 text-xs fw-bold" for="remove_media_{{ $m->id }}">
                                                        <i class="bi bi-trash me-1"></i> Remove
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="alert alert-light border text-muted small py-3 mb-0">No existing photos found for this submission.</div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- 2. Upload Additional / Replacement Photos -->
                            <div class="row g-3 mb-4">
                                <div class="col-12" id="mediaAttachmentsContainer">
                                    <div class="p-4 bg-light rounded-4 border border-2 border-dashed">
                                        <div class="text-center mb-3">
                                            <i class="bi bi-images fs-1 text-primary mb-2 d-block"></i>
                                            <label class="form-label fw-bold fs-6 text-dark d-block mb-1">Media Attachments: High-Resolution Photos {{ $existingImages->count() === 0 ? '(Mandatory)' : '' }} {!! $existingImages->count() === 0 ? '<span class="text-danger">*</span>' : '' !!}</label>
                                            <span class="badge bg-danger">Max 20 MB per file</span>
                                        </div>
                                        <input type="file" id="mediaPhotosInput" class="form-control form-control-lg mx-auto" style="max-width: 550px;" multiple accept="image/*">
                                        <div id="mediaPhotosHiddenInputs"></div>
                                        <div id="mediaPhotosCards" class="d-flex flex-wrap justify-content-center gap-3 mt-4"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6" id="geoTaggedContainer">
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase tracking-wider">Existing Geo-Tagged Photos ({{ $existingGeoImages->count() }} files):</label>
                                @if($existingGeoImages->count() > 0)
                                    <div class="row g-3 mt-1 mb-3">
                                        @foreach($existingGeoImages as $geo)
                                            <div class="col-6 col-sm-4 col-md-3" id="media_col_{{ $geo->id }}">
                                                <div class="border rounded-4 p-2 bg-light text-center shadow-sm position-relative overflow-hidden h-100 d-flex flex-column justify-content-between transition-all" id="media_card_{{ $geo->id }}">
                                                    <div>
                                                        <img src="{{ upload_url($geo->file_path) }}" alt="{{ $geo->file_name }}" class="rounded-3 w-100 object-fit-cover border" style="height: 110px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImageModal('{{ upload_url($geo->file_path) }}', '{{ addslashes($geo->file_name) }}')">
                                                        <small class="d-block text-truncate text-xs fw-semibold text-dark mt-2" title="{{ $geo->file_name }}">{{ $geo->file_name }}</small>
                                                    </div>
                                                    <div class="mt-2 pt-2 border-top">
                                                        <input type="checkbox" name="remove_media_ids[]" value="{{ $geo->id }}" class="btn-check remove-media-toggle" id="remove_media_{{ $geo->id }}" data-card="#media_card_{{ $geo->id }}">
                                                        <label class="btn btn-sm btn-outline-danger w-100 text-xs fw-bold" for="remove_media_{{ $geo->id }}">
                                                            <i class="bi bi-trash me-1"></i> Remove
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-light border text-muted small py-3 mb-3">No existing Geo-Tagged photo found for this submission.</div>
                                @endif
                            </div>

                            <!-- Upload Additional / Replacement Geo-Tagged Photo -->
                            <div class="p-4 bg-light rounded-4 border border-2 border-dashed mb-4 text-center">
                                <i class="bi bi-geo-alt-fill fs-1 text-success mb-2 d-block"></i>
                                <label class="form-label fw-bold fs-6 text-dark d-block mb-2">Upload New / Replacement Geo-Tagged Photos</label>
                                <input type="file" name="new_geo_tagged_photo[]" id="geoTaggedInput" class="form-control form-control-lg mx-auto" style="max-width: 550px;" multiple accept="image/*">
                                <div class="form-text mt-2 text-primary fw-bold">Supported formats: JPG, PNG, WEBP.</div>
                                <div id="geoPreviewContainer" class="d-flex flex-wrap justify-content-center gap-3 mt-4"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Link & Document Management -->
                    <div class="row g-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-uppercase tracking-wider">Photo Link <span class="text-muted text-normal">(Optional)</span></label>
                            <input type="text" name="photo_links" class="form-control py-2" placeholder="e.g., Google Drive Album Link" value="{{ old('photo_links', $existingLink ? $existingLink->file_path : '') }}">
                            <div class="form-text text-xs">Clear the field to remove existing photo album link.</div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-uppercase tracking-wider">Video Clip Link <span class="text-muted text-normal">(Optional)</span></label>
                            <input type="text" name="video_url" class="form-control py-2" placeholder="https://youtube.com/watch?v=... or Google Drive link" value="{{ old('video_url', $existingVideo ? $existingVideo->file_path : '') }}">
                            <div class="form-text text-xs">Clear the field to remove existing video link.</div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-uppercase tracking-wider">Supporting Documents <span class="text-muted text-normal">(PDF, JPG, Excel, etc. - <span class="text-danger fw-bold">Max 20 MB each</span>)</span></label>
                            @if($existingDocs->count() > 0)
                                <div class="d-flex flex-column gap-2 mb-2">
                                @foreach($existingDocs as $doc)
                                    <div class="p-2 bg-white border rounded-3 d-flex align-items-center justify-content-between small">
                                        <span class="text-truncate text-dark fw-semibold" style="max-width: 200px;"><i class="bi bi-file-earmark-text text-primary me-1"></i><a href="{{ upload_url($doc->file_path) }}" target="_blank" class="text-decoration-none">{{ $doc->file_name }}</a></span>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="checkbox" name="remove_media_ids[]" value="{{ $doc->id }}" class="btn-check" id="remove_doc_{{ $doc->id }}">
                                            <label class="btn btn-sm btn-outline-danger text-xxs py-0 px-2" for="remove_doc_{{ $doc->id }}"><i class="bi bi-trash"></i> Remove</label>
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                            @endif
                            <input type="file" name="support_documents[]" id="supportDocsInput" class="form-control py-2" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.csv" multiple>
                            <div id="supportDocsPreviewContainer" class="d-flex flex-wrap gap-3 mt-3"></div>
                            <div class="form-text text-xs">You can upload multiple new supporting documents here.</div>
                        </div>
                    </div>
                </div>

                <div class="mb-5 p-4 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-25">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="bi bi-check-circle-fill text-primary fs-3"></i>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Resubmission Summary Notice</h6>
                            <span class="text-muted text-xs">Summarize how your modifications resolve the reviewer's query</span>
                        </div>
                    </div>
                    <textarea name="resubmission_remarks" rows="3" class="form-control bg-white" required placeholder="Explain briefly what corrections were made to address the verification notes..."></textarea>
                </div>

                <!-- Upload Progress & Processing Feedback -->
                <div id="uploadProgressBanner" class="d-none alert alert-info border-info shadow-sm rounded-4 mb-4 p-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;"></div>
                        <div>
                            <h6 id="uploadProgressText" class="fw-bold mb-1 text-dark">Uploading Corrections & Processing Resubmission...</h6>
                            <p class="mb-0 text-muted small">Please do not close or refresh this window until your changes are saved.</p>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 8px;">
                        <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 10%"></div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-3 border-top pt-4">
                    <a href="{{ route('submissions.show', $submission->id) }}" class="btn btn-light border px-4 py-3">Cancel</a>
                    <button type="submit" id="submitSubmissionBtn" class="btn btn-portal-primary px-5 py-3 fw-bold shadow">
                        <i class="bi bi-arrow-repeat me-2"></i> Save Corrections & Resubmit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('partials.ai-phrasing-modal')

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
@endsection

@push('scripts')
<script>
    function showImageModal(src, title) {
        document.getElementById('imageModalImg').src = src;
        document.getElementById('imageModalTitle').textContent = title || 'Attached Media Preview';
        const downloadBtn = document.getElementById('imageModalDownload');
        if (downloadBtn) downloadBtn.href = src;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('.remove-media-toggle');
        toggles.forEach(chk => {
            chk.addEventListener('change', function() {
                const card = document.querySelector(this.dataset.card);
                if (card) {
                    if (this.checked) {
                        card.style.opacity = '0.45';
                        card.style.border = '2px dashed #ef4444';
                    } else {
                        card.style.opacity = '1';
                        card.style.border = '';
                    }
                }
            });
        });

        const generateBtn = document.getElementById('generateAiBtn');
        const captionBox = document.getElementById('socialMediaCaption');
        const titleInput = document.querySelector('input[name="title"]');
        const categorySelect = document.querySelector('select[name="event_category"]');
        const collegeSelect = document.querySelector('select[name="college_id"]');

        if(generateBtn && captionBox) {
            generateBtn.addEventListener('click', function() {
                const title = (titleInput && titleInput.value) ? titleInput.value : "Institutional Symposium & Innovation Meet";
                const category = (categorySelect && categorySelect.options[categorySelect.selectedIndex]) ? categorySelect.options[categorySelect.selectedIndex].text : "Academic Event";
                const college = (collegeSelect && collegeSelect.options[collegeSelect.selectedIndex]) ? collegeSelect.options[collegeSelect.selectedIndex].text : "Vinayaka Mission's Research Foundation";

                generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating...';
                generateBtn.disabled = true;

                fetch("{{ route('api.ai.captions') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ title, category, college })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.caption) {
                        captionBox.value = data.caption;
                    } else if(data.captions) {
                        captionBox.value = "📱 INSTAGRAM SUGGESTION:\n" + (data.captions.instagram || '') + "\n\n----------------------------------------\n\n💼 LINKEDIN / OFFICIAL PR SUGGESTION:\n" + (data.captions.linkedin || '');
                    }
                })
                .catch(err => {
                    captionBox.value = `✨ Highlights from our recent ${category} at ${college}! 🎓\n\nWe successfully hosted "${title}", driving knowledge and research excellence forward.\n\n#VMRFDU #HigherEducation #ResearchExcellence`;
                })
                .finally(() => {
                    generateBtn.innerHTML = '<i class="bi bi-magic me-1"></i> AI Generate Caption';
                    generateBtn.disabled = false;
                });
            });
        }

        const activityType = document.getElementById('activityType');
        const activityDate = document.getElementById('activityDate');
        
        if (activityType && activityDate) {
            const today = new Date().toISOString().split('T')[0];
            
        const reportTypeSelect = document.getElementById('reportTypeSelect');
        const textReportContainer = document.getElementById('textReportContainer');
        const fileReportContainer = document.getElementById('fileReportContainer');
        const textDescription = document.getElementById('textDescription');
        const reportDocument = document.getElementById('reportDocument');

        function toggleReportFields() {
            if (!reportTypeSelect) return;
            if (activityType && activityType.value === 'Future Event') {
                textReportContainer.style.display = 'none';
                fileReportContainer.style.display = 'none';
                textDescription.name = '';
                reportDocument.name = '';
                textDescription.required = false;
                reportDocument.required = false;
                return;
            }

            if (reportTypeSelect.value === 'By Text') {
                textReportContainer.style.display = 'block';
                textDescription.name = 'description';
                textDescription.required = true;
                
                fileReportContainer.style.display = 'none';
                reportDocument.name = '';
                reportDocument.required = false;
            } else if (reportTypeSelect.value === 'By Attach File') {
                fileReportContainer.style.display = 'block';
                reportDocument.name = 'report_document';
                
                // If no existing report is present, we should make the upload mandatory.
                // But for edit form, we check if there's already an existing report text in UI
                const hasExistingReport = {!! $existingReport ? 'true' : 'false' !!};
                reportDocument.required = !hasExistingReport;
                
                textReportContainer.style.display = 'none';
                textDescription.name = '';
                textDescription.required = false;
            } else {
                textReportContainer.style.display = 'none';
                fileReportContainer.style.display = 'none';
                textDescription.name = '';
                reportDocument.name = '';
                textDescription.required = false;
                reportDocument.required = false;
            }
        }

        if (reportTypeSelect) {
            reportTypeSelect.addEventListener('change', toggleReportFields);
        }

        if (activityType && activityDate) {
            const today = new Date().toISOString().split('T')[0];
            
            function updateDateConstraint() {
                if (activityType.value === 'Past Event') {
                    activityDate.setAttribute('max', today);
                    if (activityDate.value > today) {
                        activityDate.value = today;
                    }

                    // Show Past Event fields
                    const resContainer = document.getElementById('resourcePersonContainer');
                    const partContainer = document.getElementById('participantsContainer');
                    const outContainer = document.getElementById('outcomeContainer');
                    const repContainer = document.getElementById('reportTypeContainer');
                    const imgContainer = document.getElementById('imagesContainer');
                    const geoContainer = document.getElementById('geoTaggedContainer');
                    
                    if(resContainer) resContainer.style.display = 'block';
                    if(partContainer) partContainer.style.display = 'block';
                    if(outContainer) outContainer.style.display = 'block';
                    if(repContainer) repContainer.style.display = 'block';
                    if(imgContainer) imgContainer.style.display = 'block';
                    if(geoContainer) geoContainer.style.display = 'block';

                    const tgtAud = document.getElementById('targetAudience');
                    const outInp = document.getElementById('outcome');

                    if(tgtAud) tgtAud.required = true;
                    if(outInp) outInp.required = true;
                    window.mediaPhotosRequired = {{ $existingImages->count() === 0 ? 'true' : 'false' }};
                    if(reportTypeSelect) reportTypeSelect.required = true;

                    toggleReportFields();
                } else {
                    activityDate.removeAttribute('max');

                    // Hide Past Event fields
                    const resContainer = document.getElementById('resourcePersonContainer');
                    const partContainer = document.getElementById('participantsContainer');
                    const outContainer = document.getElementById('outcomeContainer');
                    const repContainer = document.getElementById('reportTypeContainer');
                    const imgContainer = document.getElementById('imagesContainer');
                    const geoContainer = document.getElementById('geoTaggedContainer');
                    
                    if(resContainer) resContainer.style.display = 'none';
                    if(partContainer) partContainer.style.display = 'none';
                    if(outContainer) outContainer.style.display = 'none';
                    if(repContainer) repContainer.style.display = 'none';
                    if(imgContainer) imgContainer.style.display = 'none';
                    if(geoContainer) geoContainer.style.display = 'none';

                    const tgtAud = document.getElementById('targetAudience');
                    const outInp = document.getElementById('outcome');

                    if(tgtAud) tgtAud.required = false;
                    if(outInp) outInp.required = false;
                    window.mediaPhotosRequired = false;
                    if(reportTypeSelect) reportTypeSelect.required = false;

                    toggleReportFields();
                }
            }

            activityType.addEventListener('change', updateDateConstraint);
            updateDateConstraint(); // Initial call
        }
        }

        const geoInput = document.getElementById('geoTaggedInput');
        const geoPreviewContainer = document.getElementById('geoPreviewContainer');
        
        if (geoInput && geoPreviewContainer) {
            geoInput.addEventListener('change', function() {
                geoPreviewContainer.innerHTML = ''; // Clear previous previews
                Array.from(this.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const url = URL.createObjectURL(file);
                        const img = document.createElement('img');
                        img.src = url;
                        img.className = 'rounded-3 shadow-sm border object-fit-cover';
                        img.style.width = '100px';
                        img.style.height = '100px';
                        geoPreviewContainer.appendChild(img);
                    }
                });
            });
        }

        function renderSingleFilePreview(inputElement, containerElement) {
            containerElement.innerHTML = '';
            const file = inputElement.files && inputElement.files[0];
            if (!file) return;

            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            const isImage = file.type.startsWith('image/');

            const card = document.createElement('div');
            card.className = 'attachment-preview-card';
            card.title = 'Click to preview in a new tab';

            const fileUrl = URL.createObjectURL(file);
            card.onclick = function() {
                window.open(fileUrl, '_blank');
            };

            let visualEl;
            if (isImage) {
                visualEl = '<img src="' + fileUrl + '" class="attachment-preview-img" alt="Preview">';
            } else {
                let iconClass = 'bi-file-earmark';
                if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) iconClass = 'bi-file-earmark-pdf text-danger';
                else if (file.type.includes('word') || file.name.toLowerCase().endsWith('.doc') || file.name.toLowerCase().endsWith('.docx')) iconClass = 'bi-file-earmark-word text-primary';
                visualEl = '<div class="attachment-preview-icon"><i class="bi ' + iconClass + '"></i></div>';
            }

            card.innerHTML = visualEl +
                '<div class="attachment-preview-info">' +
                    '<div class="attachment-preview-name" title="' + file.name.replace(/"/g, '&quot;') + '">' + file.name + '</div>' +
                    '<div class="attachment-preview-size">' + sizeMB + ' MB &bull; Ready</div>' +
                '</div>';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'attachment-preview-remove';
            removeBtn.innerHTML = '<i class="bi bi-x"></i>';
            removeBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                inputElement.value = '';
                containerElement.innerHTML = '';
            };
            card.appendChild(removeBtn);

            containerElement.appendChild(card);
        }

        const reportDocumentInput = document.getElementById('reportDocument');
        const reportDocumentPreviewContainer = document.getElementById('reportDocumentPreviewContainer');
        if (reportDocumentInput && reportDocumentPreviewContainer) {
            reportDocumentInput.addEventListener('change', function() {
                renderSingleFilePreview(reportDocumentInput, reportDocumentPreviewContainer);
            });
        }

        // Real-time, non-blocking upload of Media Attachments via AJAX with per-photo progress.
        const mediaUploaderApi = window.VMRFDU_MediaUploader.init({
            inputId: 'mediaPhotosInput',
            cardsContainerId: 'mediaPhotosCards',
            hiddenContainerId: 'mediaPhotosHiddenInputs',
            tokenFieldName: 'new_media_photo_tokens[]',
            onPendingChange: function(pendingCount) {
                const btn = document.getElementById('submitSubmissionBtn');
                if (!btn) return;
                if (pendingCount > 0) {
                    if (!btn.dataset.originalHtml) btn.dataset.originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Photos uploading, please wait...';
                } else {
                    btn.disabled = false;
                    if (btn.dataset.originalHtml) btn.innerHTML = btn.dataset.originalHtml;
                }
            }
        });

        const editForm = document.querySelector('form[action*="submissions"]');
        if (editForm) {
            editForm.addEventListener('submit', async function(e) {
                if (editForm.dataset.submitting === "true") {
                    e.preventDefault();
                    return;
                }

                if (mediaUploaderApi && mediaUploaderApi.hasPending()) {
                    e.preventDefault();
                    alert('Please wait for all High-Resolution Photos to finish uploading before submitting.');
                    return;
                }

                if (window.mediaPhotosRequired && mediaUploaderApi && mediaUploaderApi.tokenCount() === 0) {
                    e.preventDefault();
                    alert('Please upload at least one High-Resolution Photo under Media Attachments before submitting.');
                    return;
                }

                e.preventDefault();
                const geoTaggedInput = document.getElementById('geoTaggedInput');
                let successGeo = true;
                if (geoTaggedInput && geoTaggedInput.files.length > 0) {
                    successGeo = await window.VMRFDU_ImageOptimizer.optimizeFileList(
                        geoTaggedInput,
                        'uploadProgressBanner',
                        'uploadProgressText',
                        'uploadProgressBar',
                        'submitSubmissionBtn'
                    );
                }

                if (successGeo) {
                    editForm.dataset.submitting = "true";
                    submitFormWithProgress(editForm);
                }
            });
        }
    });
</script>
@include('partials.submission-progress')
@include('partials.multi-file-uploader')
@include('partials.client-image-optimizer')
@include('partials.media-photo-uploader')
@include('partials.ai-phrasing-modal')
@endpush
