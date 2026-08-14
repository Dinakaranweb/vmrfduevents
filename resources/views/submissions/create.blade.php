@extends('layouts.app')

@section('title', 'Submit New Activity - VMRFDU')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <span class="badge bg-primary px-3 py-1 mb-2 rounded-pill shadow-sm"><i class="bi bi-shield-check me-1"></i> Institutional Submission Desk</span>
        <h3 class="fw-bold mb-1 text-dark">Submit New Institutional Activity</h3>
        <p class="text-muted mb-0">Record academic symposiums, research breakthroughs, or campus events for official verification and media broadcasting</p>
    </div>
    <a href="{{ url()->previous() }}" class="btn btn-light border px-4 py-2 shadow-sm d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Back to Directory
    </a>
</div>

<form action="{{ route('submissions.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    @if($errors->any())
        <div class="alert alert-danger shadow-sm rounded-4 p-4 mb-4">
            <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Please correct the following errors:</h6>
            <ul class="mb-0 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Unified Executive Form Card -->
    <div class="portal-card p-4 p-md-5 shadow-lg bg-white border">
        
        <!-- Section 1: Event & Institutional Allocation -->
        <div class="mb-5">
            <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-4">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px;">1</div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Activity & Institutional Allocation</h5>
                    <span class="text-muted text-xs">Assign the activity to your campus and specify event classification</span>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-12 col-md-8">
                    <label class="form-label fw-bold small text-uppercase tracking-wider">Activity / Event Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="activityTitle" class="form-control form-control-lg" required placeholder="e.g., International Conference on Autonomous AI Systems 2026" value="{{ old('title') }}">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold small text-uppercase tracking-wider">Constituent College <span class="text-danger">*</span></label>
                    @if($currentUser && $currentUser->college_id)
                        <input type="hidden" name="college_id" value="{{ $currentUser->college_id }}">
                        <select id="collegeSelect" class="form-select form-select-lg bg-light text-muted" disabled>
                            @foreach($colleges as $col)
                                <option value="{{ $col->id }}" data-code="{{ $col->code }}" {{ $currentUser->college_id == $col->id ? 'selected' : '' }}>
                                    {{ $col->code }} - {{ $col->name }} (Fixed)
                                </option>
                            @endforeach
                        </select>
                    @else
                        <select name="college_id" id="collegeSelect" class="form-select form-select-lg" required>
                            @foreach($colleges as $col)
                                <option value="{{ $col->id }}" data-code="{{ $col->code }}">
                                    {{ $col->code }} - {{ $col->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold small text-uppercase tracking-wider">Activity Type <span class="text-danger">*</span></label>
                    <select name="activity_type" id="activityType" class="form-select py-2" required>
                        <option value="Past Event" {{ old('activity_type') == 'Past Event' ? 'selected' : '' }}>Past Event</option>
                        <option value="Future Event" {{ old('activity_type') == 'Future Event' ? 'selected' : '' }}>Future Event</option>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold small text-uppercase tracking-wider">Event Category <span class="text-danger">*</span></label>
                    <select name="event_category" id="eventCategory" class="form-select py-2" required>
                        <option value="" disabled selected>-- Select Event Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" {{ old('event_category') == $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold small text-uppercase tracking-wider">Activity Date <span class="text-danger">*</span></label>
                    <input type="date" name="activity_date" id="activityDate" class="form-control py-2" required value="{{ old('activity_date', date('Y-m-d')) }}">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold small text-uppercase tracking-wider">Venue / Campus Location <span class="text-danger">*</span></label>
                    <input type="text" name="location" class="form-control py-2" required placeholder="e.g., Main Auditorium / Seminar Hall" value="{{ old('location') }}">
                </div>

                <div class="col-12" id="participantsContainer">
                    <label class="form-label fw-bold small text-uppercase tracking-wider">Participants Attended (e.g., Faculty, Students) <span class="text-danger">*</span></label>
                    <div class="form-text text-muted mb-2 fst-italic">Note: Please provide the total number of participants, attendees, or faculty members who attended the event.</div>
                    <input type="text" id="targetAudience" name="target_audience" class="form-control py-2" required placeholder="e.g., 45 Faculty Members, 180 Undergraduate Students attended" value="{{ old('target_audience') }}">
                </div>

                <div class="col-12 col-md-6" id="resourcePersonContainer">
                    <label class="form-label fw-bold small text-uppercase tracking-wider">Resource Person Name & Designation <span class="text-muted">(Optional)</span></label>
                    <input type="text" name="resource_person_name" class="form-control py-2" placeholder="e.g., Dr. Jane Doe, Chief Scientist at XYZ" value="{{ old('resource_person_name') }}">
                </div>

                <div class="col-12 col-md-6" id="outcomeContainer">
                    <label class="form-label fw-bold small text-uppercase tracking-wider">Outcome of the Program/Event <span class="text-danger">*</span></label>
                    <input type="text" id="outcome" name="outcome" class="form-control py-2" required placeholder="e.g., Participants gained practical insights, 2 MoUs signed..." value="{{ old('outcome') }}">
                </div>

                <div class="col-12 col-md-6 mb-3" id="reportTypeContainer">
                    <label class="form-label fw-bold small text-uppercase tracking-wider">Event Report Type <span class="text-danger">*</span></label>
                    <select name="report_type" id="reportTypeSelect" class="form-select py-2" required>
                        <option value="" disabled selected>Select Report Type</option>
                        <option value="By Text" {{ old('report_type') == 'By Text' ? 'selected' : '' }}>By Text</option>
                        <option value="By Attach File" {{ old('report_type') == 'By Attach File' ? 'selected' : '' }}>By Attach File</option>
                    </select>
                </div>

                <div class="col-12 mb-3" id="textReportContainer" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <label class="form-label fw-bold small text-uppercase tracking-wider mb-0">Event Report Description <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#aiPhrasingModal" data-target-input="#textDescription">
                            <i class="bi bi-magic"></i> AI Generation Phrasing
                        </button>
                    </div>
                    <textarea id="textDescription" rows="6" class="form-control" placeholder="Provide a thorough overview of the event, keynote speakers, key achievements, or click AI Generation Phrasing above to select a template...">{{ old('report_type') == 'By Text' ? old('description') : '' }}</textarea>
                </div>

                <div class="col-12 mb-3" id="fileReportContainer" style="display: none;">
                    <label class="form-label fw-bold small text-uppercase tracking-wider mb-2">Upload Event Report <span class="text-danger">*</span></label>
                    <input type="file" id="reportDocument" class="form-control py-2" accept=".pdf,.doc,.docx">
                    <div class="form-text mt-2"><i class="bi bi-file-earmark-pdf me-1"></i> Upload the report as PDF or Word Document. <span class="text-danger">Max 20 MB.</span></div>
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
                <textarea name="social_media_caption" id="socialMediaCaption" rows="4" class="form-control font-monospace small bg-white" placeholder="Click 'AI Generate Caption' or 'Select Template Library' above...">{{ old('social_media_caption') }}</textarea>
            </div>
        </div>

        <!-- Section 3: Media Attachments -->
        <div class="mb-5">
            <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-4">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px;">3</div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Media Attachments (High-Resolution Photos & Video Links)</h5>
                    <span class="text-muted text-xs">Upload event imagery to accompany media review and social publication</span>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-12 col-md-6" id="imagesContainer">
                    <div class="p-4 bg-light rounded-4 border border-2 border-dashed text-center h-100 d-flex flex-column justify-content-center">
                        <i class="bi bi-cloud-arrow-up-fill fs-1 text-primary mb-2 d-block"></i>
                        <label class="form-label fw-bold fs-6 text-dark d-block mb-2">Media Attachments: High-Resolution Photos <span class="text-danger">*</span></label>
                        <div class="alert alert-warning border-warning shadow-sm mx-auto p-2 mb-3 text-start d-flex justify-content-between align-items-center flex-wrap gap-2" style="max-width: 550px;">
                            <div><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i><strong>Mandatory:</strong> Please upload both <strong>Stage Photo</strong> and <strong>Participants Photo</strong>.</div>
                            <span class="badge bg-danger">Max 20 MB per file</span>
                        </div>
                        <input type="file" name="images[]" id="imagesInput" class="form-control form-control-lg mx-auto" style="max-width: 550px;" multiple accept="image/*" required>
                        <div id="imagePreviewContainer" class="d-flex flex-wrap justify-content-center gap-3 mt-4"></div>
                    </div>
                </div>

                <div class="col-12 col-md-6" id="geoTaggedContainer">
                    <div class="p-4 bg-light rounded-4 border border-2 border-dashed text-center h-100 d-flex flex-column justify-content-center">
                        <i class="bi bi-geo-alt-fill fs-1 text-success mb-2 d-block"></i>
                        <label class="form-label fw-bold fs-6 text-dark d-block mb-2">Geo-Tagged Photo</label>
                        <div class="text-center mb-3">
                            <span class="badge bg-danger">Max 20 MB per file</span>
                        </div>
                        <input type="file" name="geo_tagged_photo[]" id="geoTaggedInput" class="form-control form-control-lg mx-auto" style="max-width: 550px;" multiple accept="image/*">
                        <div id="geoPreviewContainer" class="d-flex flex-wrap justify-content-center gap-3 mt-4"></div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold small text-uppercase tracking-wider">Photo Link <span class="text-muted text-normal">(Optional)</span></label>
                    <input type="text" name="photo_links" class="form-control py-2" placeholder="e.g., Google Drive Album / High-Res Folder Link" value="{{ old('photo_links') }}">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold small text-uppercase tracking-wider">Video Clip Link <span class="text-muted text-normal">(Optional)</span></label>
                    <input type="text" name="video_url" class="form-control py-2" placeholder="https://youtube.com/watch?v=... or Drive link" value="{{ old('video_url') }}">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold small text-uppercase tracking-wider">Supporting Documents <span class="text-muted text-normal">(PDF, JPG, Excel, etc. - <span class="text-danger fw-bold">Max 20 MB each</span>)</span></label>
                    <input type="file" name="support_documents[]" id="supportDocsInput" class="form-control py-2" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.csv" multiple>
                    <div id="supportDocsPreviewContainer" class="d-flex flex-wrap gap-3 mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Upload Progress & Processing Feedback -->
        <div id="uploadProgressBanner" class="d-none alert alert-info border-info shadow-sm rounded-4 mb-4 p-4">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;"></div>
                <div>
                    <h6 id="uploadProgressText" class="fw-bold mb-1 text-dark">Uploading High-Resolution Media & Processing Record...</h6>
                    <p class="mb-0 text-muted small">Please do not close or refresh this window until the submission is verified and saved.</p>
                </div>
            </div>
            <div class="progress mt-3" style="height: 8px;">
                <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 10%"></div>
            </div>
        </div>

        <!-- Action Footer -->
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 pt-4 border-top">
            <div class="d-flex align-items-center gap-2 text-muted small">
                <i class="bi bi-shield-lock-fill text-primary fs-5"></i>
                <span>Submitted records immediately enter institutional authority verification.</span>
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('submissions.index') }}" class="btn btn-outline-secondary px-4 py-2">Cancel</a>
                <button type="submit" id="submitSubmissionBtn" class="btn btn-portal-primary px-5 py-2 fw-bold shadow-sm">
                    <i class="bi bi-send-fill me-2"></i> Submit Institutional Record
                </button>
            </div>
        </div>
    </div>
</form>

@include('partials.ai-phrasing-modal')
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const generateBtn = document.getElementById('generateAiBtn');
        const captionBox = document.getElementById('socialMediaCaption');
        const titleInput = document.getElementById('activityTitle');
        const categorySelect = document.getElementById('eventCategory');
        const collegeSelect = document.getElementById('collegeSelect');
        const tagsInput = document.getElementById('activityTags');

        if(generateBtn && captionBox) {
            generateBtn.addEventListener('click', function() {
                const title = (titleInput && titleInput.value) ? titleInput.value : "Institutional Symposium & Innovation Meet";
                const category = (categorySelect && categorySelect.options[categorySelect.selectedIndex]) ? categorySelect.options[categorySelect.selectedIndex].text : "Academic Event";
                const college = (collegeSelect && collegeSelect.options[collegeSelect.selectedIndex]) ? collegeSelect.options[collegeSelect.selectedIndex].text : "Vinayaka Mission's Research Foundation";
                const tags = (tagsInput && tagsInput.value) ? tagsInput.value : "Excellence, Research, VMRFDU";

                generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating...';
                generateBtn.disabled = true;

                fetch("{{ route('api.ai.captions') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ title, category, college, tags })
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
                reportDocument.required = true;
                
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
                    const imgInp = document.getElementById('imagesInput');
                    const geoInp = document.getElementById('geoTaggedInput');
                    
                    if(tgtAud) tgtAud.required = true;
                    if(outInp) outInp.required = true;
                    if(imgInp) imgInp.required = true;
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
                    const imgInp = document.getElementById('imagesInput');
                    const geoInp = document.getElementById('geoTaggedInput');
                    
                    if(tgtAud) tgtAud.required = false;
                    if(outInp) outInp.required = false;
                    if(imgInp) imgInp.required = false;
                    if(geoInp) geoInp.required = false;
                    if(reportTypeSelect) reportTypeSelect.required = false;

                    toggleReportFields();
                }
            }
            
            activityType.addEventListener('change', updateDateConstraint);
            updateDateConstraint(); // Initial call
        }

        const imagesInput = document.getElementById('imagesInput');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        
        if (imagesInput && imagePreviewContainer) {
            imagesInput.addEventListener('change', function() {
                renderPreviews(imagesInput, imagePreviewContainer);
            });
        }
        
        const geoInput = document.getElementById('geoTaggedInput');
        const geoPreviewContainer = document.getElementById('geoPreviewContainer');
        if (geoInput && geoPreviewContainer) {
            geoInput.addEventListener('change', function() {
                renderPreviews(geoInput, geoPreviewContainer);
            });
        }
        
        function renderPreviews(inputElement, containerElement) {
            containerElement.innerHTML = ''; // Clear previous previews
            Array.from(inputElement.files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const url = URL.createObjectURL(file);
                    
                    const wrapper = document.createElement('div');
                    wrapper.className = 'position-relative d-inline-block m-1';
                    
                    const img = document.createElement('img');
                    img.src = url;
                    img.className = 'rounded-3 shadow-sm border object-fit-cover';
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.cursor = 'pointer';
                    img.title = 'Click to view full size';
                    img.onclick = () => window.open(url, '_blank');
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle d-flex align-items-center justify-content-center shadow p-0';
                    removeBtn.style.width = '22px';
                    removeBtn.style.height = '22px';
                    removeBtn.style.transform = 'translate(30%, -30%)';
                    removeBtn.innerHTML = '<i class="bi bi-x"></i>';
                    removeBtn.onclick = (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        const dt = new DataTransfer();
                        Array.from(inputElement.files)
                            .filter((f, i) => i !== index)
                            .forEach(f => dt.items.add(f));
                        inputElement.files = dt.files;
                        renderPreviews(inputElement, containerElement);
                    };
                    
                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    containerElement.appendChild(wrapper);
                }
            });
        }

        const createForm = document.querySelector('form[action*="submissions"]');
        if (createForm) {
            createForm.addEventListener('submit', async function(e) {
                if (createForm.dataset.submitting === "true") {
                    e.preventDefault();
                    return;
                }
                e.preventDefault();
                const imagesInput = document.getElementById('imagesInput');
                const success = await window.VMRFDU_ImageOptimizer.optimizeFileList(
                    imagesInput,
                    'uploadProgressBanner',
                    'uploadProgressText',
                    'uploadProgressBar',
                    'submitSubmissionBtn'
                );
                if (success) {
                    createForm.dataset.submitting = "true";
                    createForm.submit();
                }
            });
        }
    });
</script>
@include('partials.multi-file-uploader')
@include('partials.client-image-optimizer')
@endpush
