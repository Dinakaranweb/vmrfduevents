<!-- Modal 1: Submit to Office of the Registrar -->
<div class="modal fade" id="submitRegistrarModal" tabindex="-1" aria-labelledby="registrarModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form id="registrarSubmitForm" method="POST" action="" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white p-4">
                    <h2 class="modal-title h5 fw-bold d-flex align-items-center gap-2" id="registrarModalTitle"><i class="bi bi-shield-check fs-4" aria-hidden="true"></i> Submit to Office of the Registrar for Approval</h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close modal"></button>
                </div>
                <div class="modal-body p-4">
                    <h3 class="h6 fw-bold text-dark mb-1 p-3 bg-light rounded-3 border mb-3" id="regModalTitle"></h3>
                    <p class="text-muted small mb-4">Edit or optimize the photo and refine the social media caption before forwarding to the Office of the Registrar for formal verification.</p>

                    <div class="mb-4" id="photoInputContainer">
                        <label class="form-label fw-bold small text-dark text-uppercase tracking-wider" id="regPhotoLabel" for="regPhotoInput">Edited / Optimized Photos for Social Media <span class="text-danger">*</span></label>
                        <input type="file" name="edited_photos[]" id="regPhotoInput" class="form-control" accept="image/*" multiple required>
                        <div class="form-text small" id="regPhotoHelper">Upload high-resolution cropped/optimized photos (`Edited Photos for Social Media`). <span class="badge bg-danger ms-1">Max 20 MB per file</span></div>
                        <div id="regPhotoWarning" class="alert alert-warning border-warning border-start border-4 mt-2 mb-0 py-2 px-3 small d-none" role="alert">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-1" aria-hidden="true"></i> <strong>Mandatory Image Upload:</strong> No image is currently attached to this activity. Please upload at least one image before submitting for approval.
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
                            <label class="form-label fw-bold small text-dark text-uppercase tracking-wider mb-0" for="regModalCaption">Refined Social Media Caption <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-dark fw-bold d-flex align-items-center gap-1 rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#aiPhrasingModal" data-target-input="#regModalCaption">
                                    <i class="bi bi-collection" aria-hidden="true"></i> Template Library (>10)
                                </button>
                                <button type="button" id="editorGenerateAiBtn" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 rounded-pill px-3">
                                    <i class="bi bi-magic" aria-hidden="true"></i> AI Generate Phrasing
                                </button>
                            </div>
                        </div>
                        <textarea name="social_media_caption" id="regModalCaption" rows="5" class="form-control font-monospace small" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark text-uppercase tracking-wider" for="editorNotesInput">Editor Notes / Rationale (Optional)</label>
                        <textarea name="editor_notes" id="editorNotesInput" rows="2" class="form-control small" placeholder="Optional notes for the Registrar reviewing this media package..."></textarea>
                    </div>

                    <div id="regUploadProgressBanner" class="d-none alert alert-info border-info shadow-sm rounded-3 mb-0 p-3" role="status">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <span id="regUploadProgressText" class="small fw-bold text-dark">Uploading Edited Package & Processing... Please wait...</span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div id="regUploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 10%"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light border px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="regSubmitBtn" class="btn btn-primary fw-bold px-5 py-2 rounded-pill shadow d-flex align-items-center gap-2">
                        <i class="bi bi-send-check-fill fs-5" aria-hidden="true"></i> Submit for Registrar Approval
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Publish / Schedule Modal -->
<div class="modal fade" id="publishModal" tabindex="-1" aria-labelledby="publishModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-success text-white p-4">
                    <h2 class="modal-title h5 fw-bold d-flex align-items-center gap-2" id="publishModalLabel"><i class="bi bi-rocket-takeoff-fill fs-4" aria-hidden="true"></i> Final Broadcast Publication – Media Package Reviewed & Published</h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close modal"></button>
                </div>
                <form id="publishForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-success d-flex align-items-center mb-4">
                            <i class="bi bi-broadcast fs-3 me-3"></i>
                            <div>
                                <h4 class="alert-heading h6 fw-bold mb-1">Activity Published – Social Media Details Updated</h4>
                                <p class="mb-0 small">The activity has been published on the selected social media platforms. The respective post links have been updated in the system for reference.</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="h6 fw-bold text-dark border-bottom pb-2 mb-3">Social Media Publication Details</h5>
                            <p class="text-muted small mb-3">The Media user must record the social media platforms where the activity was published.</p>

                            <label class="form-label fw-bold small text-dark text-uppercase tracking-wider">Target Broadcast Platforms <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded-4 border mb-3">
                                <div class="form-check">
                                    <input class="form-check-input platform-checkbox" type="checkbox" name="platforms[]" value="Instagram" id="platInsta">
                                    <label class="form-check-label fw-bold text-danger" for="platInsta"><i class="bi bi-instagram me-1" aria-hidden="true"></i> Instagram</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input platform-checkbox" type="checkbox" name="platforms[]" value="Facebook" id="platFb">
                                    <label class="form-check-label fw-bold text-primary" for="platFb"><i class="bi bi-facebook me-1" aria-hidden="true"></i> Facebook</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input platform-checkbox" type="checkbox" name="platforms[]" value="LinkedIn" id="platLinked">
                                    <label class="form-check-label fw-bold" style="color: #0077b5;" for="platLinked"><i class="bi bi-linkedin me-1" aria-hidden="true"></i> LinkedIn</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input platform-checkbox" type="checkbox" name="platforms[]" value="X (Twitter)" id="platX">
                                    <label class="form-check-label fw-bold text-dark" for="platX"><i class="bi bi-twitter-x me-1" aria-hidden="true"></i> X (Twitter)</label>
                                </div>
                            </div>
                        </div>

                        <div id="urlFieldsContainer">
                            <div class="mb-3 url-field-group d-none" id="groupInsta">
                                <label class="form-label fw-bold small text-dark" for="urlInsta">Post Link – Instagram <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-danger"><i class="bi bi-instagram"></i></span>
                                    <input type="url" name="post_url_instagram" id="urlInsta" class="form-control" placeholder="https://instagram.com/...">
                                </div>
                            </div>
                            <div class="mb-3 url-field-group d-none" id="groupFb">
                                <label class="form-label fw-bold small text-dark" for="urlFb">Post Link – Facebook <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-primary"><i class="bi bi-facebook"></i></span>
                                    <input type="url" name="post_url_facebook" id="urlFb" class="form-control" placeholder="https://facebook.com/...">
                                </div>
                            </div>
                            <div class="mb-3 url-field-group d-none" id="groupLinked">
                                <label class="form-label fw-bold small text-dark" for="urlLinked">Post Link – LinkedIn <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light" style="color: #0077b5;"><i class="bi bi-linkedin"></i></span>
                                    <input type="url" name="post_url_linkedin" id="urlLinked" class="form-control" placeholder="https://linkedin.com/...">
                                </div>
                            </div>
                            <div class="mb-3 url-field-group d-none" id="groupX">
                                <label class="form-label fw-bold small text-dark" for="urlX">Post Link – X (Twitter) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-dark"><i class="bi bi-twitter-x"></i></span>
                                    <input type="url" name="post_url_twitter" id="urlX" class="form-control" placeholder="https://x.com/...">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 mt-4">
                            <label class="form-label fw-bold small text-dark text-uppercase tracking-wider" for="publishRemarks">Remarks (Optional)</label>
                            <textarea name="remarks" id="publishRemarks" rows="3" class="form-control" placeholder="Any final remarks about the broadcast..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light border px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success fw-bold px-5 py-2 rounded-pill shadow d-flex align-items-center gap-2">
                            <i class="bi bi-cloud-arrow-up-fill fs-5" aria-hidden="true"></i> Save Publication Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

