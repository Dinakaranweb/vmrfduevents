<!-- AI Phrasing & Institutional Template Library Modal (>10 Templates) -->
<div class="modal fade" id="aiPhrasingModal" tabindex="-1" aria-labelledby="aiPhrasingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-dark text-white p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-25 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-magic fs-3"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-1" id="aiPhrasingModalLabel">AI Phrasing & Institutional Template Library</h5>
                        <p class="text-white-50 text-xs mb-0">Select from over 10 professionally crafted institutional templates or generate instant AI phrasing</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Navigation Tabs -->
            <div class="bg-light px-4 pt-3 border-bottom">
                <ul class="nav nav-tabs nav-tabs-portal border-0 gap-3" id="aiPhrasingTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold px-4 py-2" id="templates-tab" data-bs-toggle="tab" data-bs-target="#templates-pane" type="button" role="tab">
                            <i class="bi bi-collection-fill text-primary me-2"></i>Professional Templates Library (12+)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold px-4 py-2" id="ai-gen-tab" data-bs-toggle="tab" data-bs-target="#ai-gen-pane" type="button" role="tab">
                            <i class="bi bi-cpu-fill text-info me-2"></i>Live AI Phrasing Generator
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-light">
                <div class="tab-content" id="aiPhrasingTabsContent">
                    <!-- Tab 1: Professional Templates Library (>10 templates) -->
                    <div class="tab-pane fade show active" id="templates-pane" role="tabpanel">
                        <!-- Category Filter Pills -->
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 bg-white p-3 rounded-3 border shadow-sm">
                            <div class="d-flex flex-wrap gap-2" id="templateCategoryFilters">
                                <button type="button" class="btn btn-sm btn-dark active filter-btn fw-bold px-3 py-1" data-filter="all">All Templates (12)</button>
                                <button type="button" class="btn btn-sm btn-outline-dark filter-btn px-3 py-1" data-filter="academic">Academic & Symposium</button>
                                <button type="button" class="btn btn-sm btn-outline-dark filter-btn px-3 py-1" data-filter="research">Research & Workshop</button>
                                <button type="button" class="btn btn-sm btn-outline-dark filter-btn px-3 py-1" data-filter="outreach">Health & Outreach</button>
                                <button type="button" class="btn btn-sm btn-outline-dark filter-btn px-3 py-1" data-filter="industry">MoU & Industry</button>
                                <button type="button" class="btn btn-sm btn-outline-dark filter-btn px-3 py-1" data-filter="cultural">Sports & Cultural</button>
                            </div>
                            <span class="text-xs text-muted font-monospace"><i class="bi bi-info-circle me-1"></i>Click 'Use Template' to insert into your form</span>
                        </div>

                        <!-- Template Grid (>10 Templates) -->
                        <div class="row g-4" id="templateCardsContainer">
                            <!-- Template 1 -->
                            <div class="col-12 col-md-6 template-card-item" data-category="academic">
                                <div class="portal-card h-100 p-4 bg-white border rounded-4 shadow-sm d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1">Academic Symposium</span>
                                            <span class="text-xxs text-muted font-monospace">Template #01</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">National Conference & Symposium</h6>
                                        <p class="text-muted text-xs bg-light p-3 rounded-3 border font-monospace mb-3" style="min-height: 110px; line-height: 1.5;">We are proud to announce the successful conclusion of the National Conference on "[Event Title]" hosted by [Constituent College] at Vinayaka Mission's Research Foundation (Deemed to be University). Renowned keynote speakers and scholars across the nation engaged in stimulating discussions on emerging methodologies and interdisciplinary research.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold w-100 d-flex align-items-center justify-content-center gap-2 use-template-btn"
                                            data-template="We are proud to announce the successful conclusion of the National Conference on '{title}' hosted by {college} at Vinayaka Mission's Research Foundation (Deemed to be University). Renowned keynote speakers and scholars across the nation engaged in stimulating discussions on emerging methodologies and interdisciplinary research.">
                                        <i class="bi bi-check2-circle fs-6"></i> Use This Template
                                    </button>
                                </div>
                            </div>

                            <!-- Template 2 -->
                            <div class="col-12 col-md-6 template-card-item" data-category="research">
                                <div class="portal-card h-100 p-4 bg-white border rounded-4 shadow-sm d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-info bg-opacity-10 text-dark fw-bold px-3 py-1">International Workshop</span>
                                            <span class="text-xxs text-muted font-monospace">Template #02</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Hands-on Technical Workshop</h6>
                                        <p class="text-muted text-xs bg-light p-3 rounded-3 border font-monospace mb-3" style="min-height: 110px; line-height: 1.5;">[Constituent College] at VMRF(DU) organized a high-impact International Workshop on "[Event Title]". Participants gained invaluable hands-on training, industry insights, and practical skill enrichment guided by global subject matter experts and seasoned practitioners.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold w-100 d-flex align-items-center justify-content-center gap-2 use-template-btn"
                                            data-template="{college} at VMRF(DU) organized a high-impact International Workshop on '{title}'. Participants gained invaluable hands-on training, industry insights, and practical skill enrichment guided by global subject matter experts and seasoned practitioners.">
                                        <i class="bi bi-check2-circle fs-6"></i> Use This Template
                                    </button>
                                </div>
                            </div>

                            <!-- Template 3 -->
                            <div class="col-12 col-md-6 template-card-item" data-category="outreach">
                                <div class="portal-card h-100 p-4 bg-white border rounded-4 shadow-sm d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1">Health Outreach</span>
                                            <span class="text-xxs text-muted font-monospace">Template #03</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Community Medical & Screening Camp</h6>
                                        <p class="text-muted text-xs bg-light p-3 rounded-3 border font-monospace mb-3" style="min-height: 110px; line-height: 1.5;">In alignment with our institutional commitment to social responsibility and public welfare, [Constituent College] successfully conducted a Community Health & Medical Outreach Camp at [Location]. Beneficiaries received free diagnostic screenings, specialized medical consultations, and proactive health education.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold w-100 d-flex align-items-center justify-content-center gap-2 use-template-btn"
                                            data-template="In alignment with our institutional commitment to social responsibility and public welfare, {college} successfully conducted a Community Health & Medical Outreach Camp for '{title}'. Beneficiaries received free diagnostic screenings, specialized medical consultations, and proactive health education.">
                                        <i class="bi bi-check2-circle fs-6"></i> Use This Template
                                    </button>
                                </div>
                            </div>

                            <!-- Template 4 -->
                            <div class="col-12 col-md-6 template-card-item" data-category="research">
                                <div class="portal-card h-100 p-4 bg-white border rounded-4 shadow-sm d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-warning bg-opacity-10 text-dark fw-bold px-3 py-1">Guest Lecture</span>
                                            <span class="text-xxs text-muted font-monospace">Template #04</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Expert Talk & Research Colloquium</h6>
                                        <p class="text-muted text-xs bg-light p-3 rounded-3 border font-monospace mb-3" style="min-height: 110px; line-height: 1.5;">Fostering a culture of scientific inquiry and intellectual excellence, [Constituent College] organized an insightful Research Colloquium titled "[Event Title]". Distinguished speakers highlighted frontier research developments, methodological innovations, and technological advancements driving modern academia.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold w-100 d-flex align-items-center justify-content-center gap-2 use-template-btn"
                                            data-template="Fostering a culture of scientific inquiry and intellectual excellence, {college} organized an insightful Research Colloquium titled '{title}'. Distinguished speakers highlighted frontier research developments, methodological innovations, and technological advancements driving modern academia.">
                                        <i class="bi bi-check2-circle fs-6"></i> Use This Template
                                    </button>
                                </div>
                            </div>

                            <!-- Template 5 -->
                            <div class="col-12 col-md-6 template-card-item" data-category="industry">
                                <div class="portal-card h-100 p-4 bg-white border rounded-4 shadow-sm d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-dark text-white fw-bold px-3 py-1">MoU Signing</span>
                                            <span class="text-xxs text-muted font-monospace">Template #05</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Strategic Industry Partnership & MoU</h6>
                                        <p class="text-muted text-xs bg-light p-3 rounded-3 border font-monospace mb-3" style="min-height: 110px; line-height: 1.5;">Vinayaka Mission's Research Foundation (Deemed to be University) reached a significant institutional milestone by signing a strategic Memorandum of Understanding (MoU) during "[Event Title]". This landmark collaboration catalyzes joint research initiatives, collaborative internships, and advanced industrial training.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold w-100 d-flex align-items-center justify-content-center gap-2 use-template-btn"
                                            data-template="Vinayaka Mission's Research Foundation (Deemed to be University) reached a significant institutional milestone by signing a strategic Memorandum of Understanding (MoU) during '{title}'. This landmark collaboration catalyzes joint research initiatives, collaborative internships, and advanced industrial training.">
                                        <i class="bi bi-check2-circle fs-6"></i> Use This Template
                                    </button>
                                </div>
                            </div>

                            <!-- Template 6 -->
                            <div class="col-12 col-md-6 template-card-item" data-category="academic">
                                <div class="portal-card h-100 p-4 bg-white border rounded-4 shadow-sm d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1">Skill Development</span>
                                            <span class="text-xxs text-muted font-monospace">Template #06</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Value Added & Professional Skill Program</h6>
                                        <p class="text-muted text-xs bg-light p-3 rounded-3 border font-monospace mb-3" style="min-height: 110px; line-height: 1.5;">Empowering students with cutting-edge professional competencies, [Constituent College] initiated a comprehensive Value Added Skill Development Program titled "[Event Title]". Fusing theoretical foundations with intensive practical sessions, the course equipped participants for dynamic industry demands.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold w-100 d-flex align-items-center justify-content-center gap-2 use-template-btn"
                                            data-template="Empowering students with cutting-edge professional competencies, {college} initiated a comprehensive Value Added Skill Development Program titled '{title}'. Fusing theoretical foundations with intensive practical sessions, the course equipped participants for dynamic industry demands.">
                                        <i class="bi bi-check2-circle fs-6"></i> Use This Template
                                    </button>
                                </div>
                            </div>

                            <!-- Template 7 -->
                            <div class="col-12 col-md-6 template-card-item" data-category="cultural">
                                <div class="portal-card h-100 p-4 bg-white border rounded-4 shadow-sm d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-3 py-1">Sports Meet</span>
                                            <span class="text-xxs text-muted font-monospace">Template #07</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Annual Sports Championship</h6>
                                        <p class="text-muted text-xs bg-light p-3 rounded-3 border font-monospace mb-3" style="min-height: 110px; line-height: 1.5;">Celebrating athletic excellence, teamwork, and sportsmanship, VMRF(DU) hosted the Annual Sports Tournament "[Event Title]". Student-athletes across constituent campuses demonstrated remarkable athletic prowess, unity, and determination across track and field events.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold w-100 d-flex align-items-center justify-content-center gap-2 use-template-btn"
                                            data-template="Celebrating athletic excellence, teamwork, and sportsmanship, VMRF(DU) hosted the Annual Sports Tournament '{title}'. Student-athletes across constituent campuses demonstrated remarkable athletic prowess, unity, and determination across track and field events.">
                                        <i class="bi bi-check2-circle fs-6"></i> Use This Template
                                    </button>
                                </div>
                            </div>

                            <!-- Template 8 -->
                            <div class="col-12 col-md-6 template-card-item" data-category="cultural">
                                <div class="portal-card h-100 p-4 bg-white border rounded-4 shadow-sm d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-info bg-opacity-10 text-dark fw-bold px-3 py-1">Cultural Festival</span>
                                            <span class="text-xxs text-muted font-monospace">Template #08</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Institutional Cultural & Arts Extravaganza</h6>
                                        <p class="text-muted text-xs bg-light p-3 rounded-3 border font-monospace mb-3" style="min-height: 110px; line-height: 1.5;">Vibrancy, diversity, and artistic flair came alive at "[Event Title]", the grand institutional Cultural Festival organized by [Constituent College] at VMRF(DU). The spectacular event showcased creative talent across classical music, theatrical arts, dance, and literary expressions.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold w-100 d-flex align-items-center justify-content-center gap-2 use-template-btn"
                                            data-template="Vibrancy, diversity, and artistic flair came alive at '{title}', the grand institutional Cultural Festival organized by {college} at VMRF(DU). The spectacular event showcased creative talent across classical music, theatrical arts, dance, and literary expressions.">
                                        <i class="bi bi-check2-circle fs-6"></i> Use This Template
                                    </button>
                                </div>
                            </div>

                            <!-- Template 9 -->
                            <div class="col-12 col-md-6 template-card-item" data-category="academic">
                                <div class="portal-card h-100 p-4 bg-white border rounded-4 shadow-sm d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1">Faculty Development</span>
                                            <span class="text-xxs text-muted font-monospace">Template #09</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Faculty Development Program (FDP)</h6>
                                        <p class="text-muted text-xs bg-light p-3 rounded-3 border font-monospace mb-3" style="min-height: 110px; line-height: 1.5;">Strengthening pedagogical rigor and research mentorship, [Constituent College] conducted an intensive Faculty Development Program (FDP) on "[Event Title]". Academic leaders and professors explored modern curriculum design, outcome-based education, and high-impact research methodologies.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold w-100 d-flex align-items-center justify-content-center gap-2 use-template-btn"
                                            data-template="Strengthening pedagogical rigor and research mentorship, {college} conducted an intensive Faculty Development Program (FDP) on '{title}'. Academic leaders and professors explored modern curriculum design, outcome-based education, and high-impact research methodologies.">
                                        <i class="bi bi-check2-circle fs-6"></i> Use This Template
                                    </button>
                                </div>
                            </div>

                            <!-- Template 10 -->
                            <div class="col-12 col-md-6 template-card-item" data-category="industry">
                                <div class="portal-card h-100 p-4 bg-white border rounded-4 shadow-sm d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1">Alumni Connect</span>
                                            <span class="text-xxs text-muted font-monospace">Template #10</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Alumni Meet & Career Mentorship</h6>
                                        <p class="text-muted text-xs bg-light p-3 rounded-3 border font-monospace mb-3" style="min-height: 110px; line-height: 1.5;">Reconnecting past and present, [Constituent College] hosted an inspiring Alumni Meet & Mentorship Conclave titled "[Event Title]". Illustrious alumni shared valuable corporate insights, entrepreneurial journeys, and professional networking strategies with current students.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold w-100 d-flex align-items-center justify-content-center gap-2 use-template-btn"
                                            data-template="Reconnecting past and present, {college} hosted an inspiring Alumni Meet & Mentorship Conclave titled '{title}'. Illustrious alumni shared valuable corporate insights, entrepreneurial journeys, and professional networking strategies with current students.">
                                        <i class="bi bi-check2-circle fs-6"></i> Use This Template
                                    </button>
                                </div>
                            </div>

                            <!-- Template 11 -->
                            <div class="col-12 col-md-6 template-card-item" data-category="research">
                                <div class="portal-card h-100 p-4 bg-white border rounded-4 shadow-sm d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-dark text-white fw-bold px-3 py-1">Hackathon</span>
                                            <span class="text-xxs text-muted font-monospace">Template #11</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">24-Hour Innovation & Hackathon Challenge</h6>
                                        <p class="text-muted text-xs bg-light p-3 rounded-3 border font-monospace mb-3" style="min-height: 110px; line-height: 1.5;">Sparking entrepreneurial mindset and technological ingenuity, [Constituent College] organized a high-energy Hackathon and Innovation Challenge "[Event Title]". Student innovators prototyped novel tech solutions tackling critical societal, healthcare, and sustainability problems.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold w-100 d-flex align-items-center justify-content-center gap-2 use-template-btn"
                                            data-template="Sparking entrepreneurial mindset and technological ingenuity, {college} organized a high-energy Hackathon and Innovation Challenge '{title}'. Student innovators prototyped novel tech solutions tackling critical societal, healthcare, and sustainability problems.">
                                        <i class="bi bi-check2-circle fs-6"></i> Use This Template
                                    </button>
                                </div>
                            </div>

                            <!-- Template 12 -->
                            <div class="col-12 col-md-6 template-card-item" data-category="outreach">
                                <div class="portal-card h-100 p-4 bg-white border rounded-4 shadow-sm d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1">NSS / NCC Drive</span>
                                            <span class="text-xxs text-muted font-monospace">Template #12</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">NSS / NCC Environmental & Community Service</h6>
                                        <p class="text-muted text-xs bg-light p-3 rounded-3 border font-monospace mb-3" style="min-height: 110px; line-height: 1.5;">Demonstrating profound social commitment and environmental stewardship, the NSS/NCC units of [Constituent College] organized a Mega Green Outreach Drive during "[Event Title]". Volunteers actively engaged local communities in ecological preservation and civic awareness.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold w-100 d-flex align-items-center justify-content-center gap-2 use-template-btn"
                                            data-template="Demonstrating profound social commitment and environmental stewardship, the NSS/NCC units of {college} organized a Mega Green Outreach Drive during '{title}'. Volunteers actively engaged local communities in ecological preservation and civic awareness.">
                                        <i class="bi bi-check2-circle fs-6"></i> Use This Template
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Live AI Phrasing Generator -->
                    <div class="tab-pane fade" id="ai-gen-pane" role="tabpanel">
                        <div class="p-4 bg-white rounded-4 border shadow-sm">
                            <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-stars text-primary fs-5"></i> Instant AI Broadcast Phrasing Generator
                            </h6>
                            <p class="text-muted text-xs mb-4">Click below to generate dynamic, platform-ready phrasing tailored with relevant hashtags and institutional branding.</p>

                            <div class="d-flex gap-3 mb-4">
                                <button type="button" id="modalLiveAiBtn" class="btn btn-primary fw-bold px-4 py-2 shadow-sm d-flex align-items-center gap-2">
                                    <i class="bi bi-magic"></i> Generate AI Phrasing Now
                                </button>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase tracking-wider">Generated Result Preview:</label>
                                <textarea id="modalAiResult" rows="6" class="form-control font-monospace small bg-light border-2" readonly placeholder="Click 'Generate AI Phrasing Now' to create text..."></textarea>
                            </div>

                            <button type="button" id="modalApplyAiBtn" class="btn btn-success fw-bold px-4 py-2 shadow-sm w-100 d-flex align-items-center justify-content-center gap-2" disabled>
                                <i class="bi bi-check-lg"></i> Apply Generated Phrasing
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between align-items-center">
                <span class="text-xs text-muted">Over 10 professional templates included for VMRF(DU) institutional activities.</span>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let activeTargetInput = null;

    // Listen when modal opens to detect which input triggered it
    const modalEl = document.getElementById('aiPhrasingModal');
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (button && button.hasAttribute('data-target-input')) {
                const targetSelector = button.getAttribute('data-target-input');
                activeTargetInput = document.querySelector(targetSelector);
            }
        });
    }

    // Category Filter logic
    const filterBtns = document.querySelectorAll('#templateCategoryFilters .filter-btn');
    const templateCards = document.querySelectorAll('.template-card-item');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => {
                b.classList.remove('btn-dark', 'active');
                b.classList.add('btn-outline-dark');
            });
            this.classList.remove('btn-outline-dark');
            this.classList.add('btn-dark', 'active');

            const filter = this.getAttribute('data-filter');
            templateCards.forEach(card => {
                if (filter === 'all' || card.getAttribute('data-category') === filter) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Use Template button click
    const useTemplateBtns = document.querySelectorAll('.use-template-btn');
    useTemplateBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            let templateText = this.getAttribute('data-template');
            
            // Try to extract existing form title & college name
            const titleInput = document.querySelector('input[name="title"]');
            const collegeSelect = document.querySelector('select[name="college_id"]');
            
            let eventTitle = titleInput && titleInput.value.trim() ? titleInput.value.trim() : "Institutional Activity";
            let collegeName = "Vinayaka Mission's Research Foundation (Deemed to be University)";
            if (collegeSelect && collegeSelect.selectedIndex > 0) {
                collegeName = collegeSelect.options[collegeSelect.selectedIndex].text;
            }

            templateText = templateText.replace(/\{title\}/g, eventTitle).replace(/\{college\}/g, collegeName);

            const modalAiResult = document.getElementById('modalAiResult');
            const modalApplyAiBtn = document.getElementById('modalApplyAiBtn');
            if (modalAiResult) {
                modalAiResult.value = templateText;
                if (modalApplyAiBtn) modalApplyAiBtn.disabled = false;
                
                // Highlight the textarea to indicate it was updated
                modalAiResult.classList.add('border-primary');
                setTimeout(() => modalAiResult.classList.remove('border-primary'), 1000);
            }
        });
    });

    // Live AI Phrasing Generator inside Modal
    const modalLiveAiBtn = document.getElementById('modalLiveAiBtn');
    const modalAiResult = document.getElementById('modalAiResult');
    const modalApplyAiBtn = document.getElementById('modalApplyAiBtn');

    if (modalLiveAiBtn) {
        modalLiveAiBtn.addEventListener('click', function() {
            const titleInput = document.querySelector('input[name="title"]') || document.getElementById('regModalTitle');
            let eventTitle = titleInput ? (titleInput.value || titleInput.textContent || "Institutional Activity") : "Institutional Activity";

            modalLiveAiBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating...';
            modalLiveAiBtn.disabled = true;

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const token = csrfMeta ? csrfMeta.content : '';

            fetch('/api/ai-captions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    title: eventTitle,
                    category: 'Institutional Broadcast',
                    college: 'VMRF(DU)',
                    tags: 'Excellence, Research, University'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.caption) {
                    modalAiResult.value = data.caption;
                } else if (data.captions) {
                    modalAiResult.value = "✨ INSTAGRAM PHRASING:\n" + (data.captions.instagram || '') + "\n\n----------------------------------------\n\n💼 LINKEDIN PHRASING:\n" + (data.captions.linkedin || '');
                } else {
                    modalAiResult.value = `✨ Official Highlights: "${eventTitle}" organized at VMRF(DU).\n\n#VMRFDU #UniversityUpdate #Excellence`;
                }
                modalApplyAiBtn.disabled = false;
            })
            .catch(err => {
                modalAiResult.value = `✨ Official Highlights: "${eventTitle}" organized at VMRF(DU).\n\n#VMRFDU #UniversityUpdate #Excellence`;
                modalApplyAiBtn.disabled = false;
            })
            .finally(() => {
                modalLiveAiBtn.innerHTML = '<i class="bi bi-magic"></i> Generate AI Phrasing Now';
                modalLiveAiBtn.disabled = false;
            });
        });
    }

    if (modalApplyAiBtn) {
        modalApplyAiBtn.addEventListener('click', function() {
            if (!activeTargetInput) {
                activeTargetInput = document.querySelector('textarea[name="social_media_caption"]') || document.querySelector('textarea[name="description"]') || document.getElementById('regModalCaption');
            }
            if (activeTargetInput && modalAiResult.value) {
                activeTargetInput.value = modalAiResult.value;
                activeTargetInput.focus();
            }
            const bsModal = bootstrap.Modal.getInstance(modalEl);
            if (bsModal) bsModal.hide();
        });
    }
});
</script>
