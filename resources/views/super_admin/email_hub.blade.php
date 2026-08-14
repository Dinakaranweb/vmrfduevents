@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 font-sans">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">
                <i class="bi bi-envelope-check text-indigo me-2" style="color: #4f46e5;"></i>Dynamic Email &amp; Notification Engine Hub
            </h1>
            <p class="text-muted mb-0 text-sm">
                Real-time configuration of organization branding, HTML email templates, SMTP verification, and delivery logs without source code modifications.
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary rounded-pill btn-sm font-sans shadow-sm" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                <i class="bi bi-plus-lg me-1"></i>New Email Template
            </button>
            <a href="{{ route('superadmin.logsHub', ['tab' => 'emails']) }}" class="btn btn-outline-dark btn-sm rounded-pill font-sans">
                <i class="bi bi-journal-text me-1"></i>Detailed Audit Trail
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-3"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-x-circle-fill me-2"></i>Please resolve configuration issues:</div>
            <ul class="mb-0 text-sm">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills nav-fill bg-white border rounded-4 p-2 mb-4 shadow-sm font-sans gap-2" id="emailHubTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link rounded-3 py-2 px-3 {{ $activeTab == 'branding' ? 'active fw-bold shadow-sm' : 'text-dark' }}" 
               href="{{ route('superadmin.emailHub', ['tab' => 'branding']) }}">
                <i class="bi bi-palette-fill me-2"></i>1. Branding &amp; Theme Configuration
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link rounded-3 py-2 px-3 {{ $activeTab == 'templates' ? 'active fw-bold shadow-sm' : 'text-dark' }}" 
               href="{{ route('superadmin.emailHub', ['tab' => 'templates']) }}">
                <i class="bi bi-file-earmark-code-fill me-2"></i>2. Dynamic HTML Templates
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link rounded-3 py-2 px-3 {{ $activeTab == 'test' ? 'active fw-bold shadow-sm' : 'text-dark' }}" 
               href="{{ route('superadmin.emailHub', ['tab' => 'test']) }}">
                <i class="bi bi-send-check-fill me-2"></i>3. Live SMTP Test Transmission
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link rounded-3 py-2 px-3 {{ $activeTab == 'logs' ? 'active fw-bold shadow-sm' : 'text-dark' }}" 
               href="{{ route('superadmin.emailHub', ['tab' => 'logs']) }}">
                <i class="bi bi-clock-history me-2"></i>4. Email Dispatch History
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="emailHubContent">

        <!-- ========================================================= -->
        <!-- TAB 1: BRANDING & THEME SETTINGS -->
        <!-- ========================================================= -->
        <div class="tab-pane fade {{ $activeTab == 'branding' ? 'show active' : '' }}" id="brandingPane">
            <form action="{{ route('superadmin.emailHub.branding') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-4">
                    <!-- Left Column: Logo & Visual Identity -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                            <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">
                                <i class="bi bi-image text-primary me-2"></i>Organization Logo &amp; Visual Identity
                            </h5>

                            <!-- Logo Preview & Upload -->
                            <div class="mb-4 text-center bg-light border rounded-3 p-4">
                                <div class="text-xs text-muted text-uppercase fw-bold mb-2">Current Header Logo Preview</div>
                                @if(!empty($settings['portal_logo']))
                                    <img src="{{ asset($settings['portal_logo']) }}" alt="Portal Logo" class="img-fluid rounded mb-2 shadow-sm" style="max-height: 80px; background: #fff; padding: 6px; border: 1px solid #ddd;">
                                    <div class="text-xxs text-muted font-monospace">{{ $settings['portal_logo'] }}</div>
                                @else
                                    <div class="py-3 text-muted font-sans text-sm">No custom logo uploaded. Default text header will be rendered.</div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-sm">Upload New Organization Logo File (.PNG / .JPG / .WEBP)</label>
                                <input type="file" name="portal_logo_file" class="form-control text-sm" accept="image/*">
                                <div class="form-text text-xxs">Recommended height: 70px to 100px. Transparent PNG looks best on colored gradients.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-sm">Organization Name (`portal_name`)</label>
                                <input type="text" name="portal_name" class="form-control text-sm fw-bold" value="{{ $settings['portal_name'] }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-sm">Organization Tagline / Campus Details</label>
                                <input type="text" name="portal_tagline" class="form-control text-sm" value="{{ $settings['portal_tagline'] }}">
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-sm">Header Title Text</label>
                                    <input type="text" name="email_header_text" class="form-control text-sm" value="{{ $settings['email_header_text'] }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-sm">Header Subtitle Text</label>
                                    <input type="text" name="email_header_subtext" class="form-control text-sm" value="{{ $settings['email_header_subtext'] }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Colors, Contact & Signatures -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                            <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">
                                <i class="bi bi-sliders text-success me-2"></i>Theme Palette, Contact &amp; Footer Attribution
                            </h5>

                            <!-- Color Pickers -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-sm">Primary Brand Color (Header / Buttons)</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color border" id="primaryColorPicker" value="{{ $settings['email_primary_color'] ?: '#1e1b4b' }}" title="Choose primary color">
                                        <input type="text" name="email_primary_color" id="primaryColorHex" class="form-control text-sm font-monospace" value="{{ $settings['email_primary_color'] }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-sm">Accent Brand Color (Borders / Badges)</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color border" id="accentColorPicker" value="{{ $settings['email_accent_color'] ?: '#f59e0b' }}" title="Choose accent color">
                                        <input type="text" name="email_accent_color" id="accentColorHex" class="form-control text-sm font-monospace" value="{{ $settings['email_accent_color'] }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Details -->
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-sm">Contact Email Address</label>
                                    <input type="text" name="email_contact_email" class="form-control text-sm" value="{{ $settings['email_contact_email'] }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-sm">Contact Telephone / Desk Phone</label>
                                    <input type="text" name="email_contact_phone" class="form-control text-sm" value="{{ $settings['email_contact_phone'] }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-sm">Physical Headquarters / Campus Address</label>
                                <input type="text" name="email_contact_address" class="form-control text-sm" value="{{ $settings['email_contact_address'] }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-sm">Email Signature (HTML Allowed)</label>
                                <textarea name="email_signature" rows="2" class="form-control text-sm font-monospace">{{ $settings['email_signature'] }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-sm">Footer Attribution &amp; Copyright Notice</label>
                                <textarea name="email_footer_text" rows="2" class="form-control text-sm font-monospace">{{ $settings['email_footer_text'] }}</textarea>
                            </div>

                            <!-- Social Links Accordion -->
                            <div class="accordion mb-3" id="socialLinksAcc">
                                <div class="accordion-item border rounded-3">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2 text-sm fw-bold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSocial">
                                            <i class="bi bi-share text-primary me-2"></i>Social Media Channel Links (Optional)
                                        </button>
                                    </h2>
                                    <div id="collapseSocial" class="accordion-collapse collapse" data-bs-parent="#socialLinksAcc">
                                        <div class="accordion-body p-3 text-sm">
                                            <div class="mb-2"><label class="form-label text-xxs mb-1">Facebook URL</label><input type="text" name="email_social_facebook" class="form-control text-xs" value="{{ $settings['email_social_facebook'] }}"></div>
                                            <div class="mb-2"><label class="form-label text-xxs mb-1">Twitter / X URL</label><input type="text" name="email_social_twitter" class="form-control text-xs" value="{{ $settings['email_social_twitter'] }}"></div>
                                            <div class="mb-2"><label class="form-label text-xxs mb-1">Instagram URL</label><input type="text" name="email_social_instagram" class="form-control text-xs" value="{{ $settings['email_social_instagram'] }}"></div>
                                            <div><label class="form-label text-xxs mb-1">LinkedIn URL</label><input type="text" name="email_social_linkedin" class="form-control text-xs" value="{{ $settings['email_social_linkedin'] }}"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                    <i class="bi bi-check-circle-fill me-2"></i>Save Theme Palette
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Full Width: Fully Dynamic Email Text & Labels Configuration -->
                    <div class="col-12 mt-4">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1 text-dark">
                                        <i class="bi bi-fonts text-primary me-2"></i>Fully Dynamic Email Text &amp; Labels Configuration
                                    </h5>
                                    <span class="text-muted text-xs">Every single word, heading, label, intro text, and button text displayed in system emails can be customized below.</span>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill font-monospace text-xs">100% Dynamic Text Engine</span>
                            </div>

                            <div class="row g-4">
                                <!-- Group 1: Salutation & Intros -->
                                <div class="col-md-6">
                                    <div class="card border h-100 rounded-3 p-3 bg-white">
                                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 text-sm">
                                            <i class="bi bi-chat-left-text text-info me-2"></i>Salutation &amp; Paragraph Intros
                                        </h6>
                                        <div class="mb-3">
                                            <label class="form-label text-xs fw-bold text-muted mb-1">Salutation Greeting Prefix (`email_greeting_prefix`)</label>
                                            <input type="text" name="email_greeting_prefix" class="form-control text-sm" value="{{ $settings['email_greeting_prefix'] }}" placeholder="Dear">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-xs fw-bold text-muted mb-1">Single Update Intro Paragraph (`email_single_intro_text`)</label>
                                            <textarea name="email_single_intro_text" rows="2" class="form-control text-sm">{{ $settings['email_single_intro_text'] }}</textarea>
                                        </div>
                                        <div>
                                            <label class="form-label text-xs fw-bold text-muted mb-1">Consolidated Updates Intro Paragraph (`email_consolidated_intro_text`)</label>
                                            <textarea name="email_consolidated_intro_text" rows="3" class="form-control text-sm">{{ $settings['email_consolidated_intro_text'] }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Group 2: Section Headings & CTA -->
                                <div class="col-md-6">
                                    <div class="card border h-100 rounded-3 p-3 bg-white">
                                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 text-sm">
                                            <i class="bi bi-layout-text-window text-warning me-2"></i>Section Headings &amp; Call-To-Action (CTA)
                                        </h6>
                                        <div class="row g-2 mb-3">
                                            <div class="col-sm-6">
                                                <label class="form-label text-xs fw-bold text-muted mb-1">Overview Card Heading</label>
                                                <input type="text" name="email_overview_heading" class="form-control text-sm" value="{{ $settings['email_overview_heading'] }}">
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label text-xs fw-bold text-muted mb-1">Timeline Heading</label>
                                                <input type="text" name="email_timeline_heading" class="form-control text-sm" value="{{ $settings['email_timeline_heading'] }}">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-xs fw-bold text-muted mb-1">Editor Guidance Notes Heading (`email_remarks_heading`)</label>
                                            <input type="text" name="email_remarks_heading" class="form-control text-sm" value="{{ $settings['email_remarks_heading'] }}">
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-12">
                                                <label class="form-label text-xs fw-bold text-muted mb-1">CTA Button Label (`email_cta_button_text`)</label>
                                                <input type="text" name="email_cta_button_text" class="form-control text-sm fw-bold text-primary" value="{{ $settings['email_cta_button_text'] }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label text-xs fw-bold text-muted mb-1">CTA Footnote Text (`email_cta_help_text`)</label>
                                                <input type="text" name="email_cta_help_text" class="form-control text-xs" value="{{ $settings['email_cta_help_text'] }}">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="form-label text-xs fw-bold text-muted mb-1">Footer System Line (`email_footer_system_text`)</label>
                                            <input type="text" name="email_footer_system_text" class="form-control text-xs" value="{{ $settings['email_footer_system_text'] }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Group 3: Overview Table Field Labels -->
                                <div class="col-12">
                                    <div class="card border rounded-3 p-3 bg-white">
                                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 text-sm">
                                            <i class="bi bi-list-columns-reverse text-success me-2"></i>Overview Table Field Labels (Customizable Table Headers)
                                        </h6>
                                        <div class="row g-2">
                                            <div class="col-sm-4 col-md-2">
                                                <label class="form-label text-xxs fw-bold text-muted mb-1">Reference ID Label</label>
                                                <input type="text" name="email_label_ref_id" class="form-control text-xs" value="{{ $settings['email_label_ref_id'] }}">
                                            </div>
                                            <div class="col-sm-4 col-md-2">
                                                <label class="form-label text-xxs fw-bold text-muted mb-1">Activity Title Label</label>
                                                <input type="text" name="email_label_title" class="form-control text-xs" value="{{ $settings['email_label_title'] }}">
                                            </div>
                                            <div class="col-sm-4 col-md-2">
                                                <label class="form-label text-xxs fw-bold text-muted mb-1">College/Campus Label</label>
                                                <input type="text" name="email_label_college" class="form-control text-xs" value="{{ $settings['email_label_college'] }}">
                                            </div>
                                            <div class="col-sm-4 col-md-2">
                                                <label class="form-label text-xxs fw-bold text-muted mb-1">Event Date Label</label>
                                                <input type="text" name="email_label_date" class="form-control text-xs" value="{{ $settings['email_label_date'] }}">
                                            </div>
                                            <div class="col-sm-4 col-md-2">
                                                <label class="form-label text-xxs fw-bold text-muted mb-1">Category Label</label>
                                                <input type="text" name="email_label_category" class="form-control text-xs" value="{{ $settings['email_label_category'] }}">
                                            </div>
                                            <div class="col-sm-4 col-md-2">
                                                <label class="form-label text-xxs fw-bold text-muted mb-1">Current Stage Label</label>
                                                <input type="text" name="email_label_stage" class="form-control text-xs" value="{{ $settings['email_label_stage'] }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Main Global Save Button -->
                            <div class="text-end mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                                    <i class="bi bi-check-circle-fill me-2"></i>Save &amp; Apply All Dynamic Branding &amp; Text
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================================================= -->
        <!-- TAB 2: DYNAMIC HTML TEMPLATES -->
        <!-- ========================================================= -->
        <div class="tab-pane fade {{ $activeTab == 'templates' ? 'show active' : '' }}" id="templatesPane">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-code-square text-primary me-2"></i>System &amp; Email Notification Templates</h6>
                        <span class="text-muted text-xxs">Automated consolidation triggers when multiple stage updates share the same Reference ID (`reference_id`).</span>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill font-sans" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                        <i class="bi bi-plus-circle me-1"></i>Add Custom Stage Template
                    </button>
                </div>

                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0 text-sm">
                        <thead class="bg-light text-dark text-xxs text-uppercase font-sans">
                            <tr>
                                <th class="ps-4 py-3">Event Trigger Key (`event_key`)</th>
                                <th>Template Display Name</th>
                                <th>Subject Pattern</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $tpl)
                            <tr>
                                <td class="ps-4 font-monospace text-xs fw-bold text-primary">{{ $tpl->event_key }}</td>
                                <td class="fw-bold text-dark">{{ $tpl->name }}</td>
                                <td class="font-sans text-xs text-muted">{{ $tpl->email_subject }}</td>
                                <td>
                                    @if($tpl->is_active)
                                        <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Active</span>
                                    @else
                                        <span class="badge bg-secondary px-2 py-1">Disabled</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-info" 
                                                onclick="previewTemplateModal({{ $tpl->id }}, '{{ addslashes($tpl->name) }}')" title="Live HTML Preview">
                                            <i class="bi bi-eye-fill"></i> Preview
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" 
                                                onclick="openEditTemplateModal({{ $tpl->id }}, '{{ addslashes($tpl->event_key) }}', '{{ addslashes($tpl->name) }}', '{{ addslashes($tpl->email_subject) }}', '{{ addslashes($tpl->email_body) }}', '{{ addslashes($tpl->system_title ?? '') }}', '{{ addslashes($tpl->system_message ?? '') }}', {{ $tpl->is_active ? 'true' : 'false' }})" title="Edit Template">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                        <form action="{{ route('superadmin.emailHub.templates.delete', $tpl->id) }}" method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete template {{ addslashes($tpl->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete Template">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No templates found. Default system templates will be automatically re-seeded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- TAB 3: TEST EMAIL DELIVERY -->
        <!-- ========================================================= -->
        <div class="tab-pane fade {{ $activeTab == 'test' ? 'show active' : '' }}" id="testPane">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <div class="d-flex align-items-center mb-3 border-bottom pb-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                                <i class="bi bi-send-check-fill fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">Verify SMTP Delivery &amp; Inline Logo Embedding</h5>
                                <p class="text-muted text-xs mb-0">Send an official test transmission with current dynamic branding to any stakeholder or administrator email.</p>
                            </div>
                        </div>

                        <form action="{{ route('superadmin.emailHub.test') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold text-sm">Test Recipient Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="test_recipient_email" class="form-control form-control-lg text-sm font-monospace" placeholder="e.g. admin@vmrfdu.edu.in or your personal email..." value="{{ Auth::user()->email }}" required>
                                <div class="form-text text-xxs">An actual email with complete HTML formatting, inline logo attachment (CID), and signature will be dispatched immediately.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-sm">Select Template / Transmission Mode</label>
                                <select name="test_template_id" class="form-select text-sm">
                                    <option value="">⚡ Test Consolidated Multi-Update Report Mode (Default Recommendation)</option>
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->id }}">Single Stage Template: {{ $tpl->name }} (`{{ $tpl->event_key }}`)</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-sm">Custom Test Remarks / Guidance Notes (Optional)</label>
                                <textarea name="test_remarks" rows="3" class="form-control text-sm font-monospace" placeholder="Enter sample remarks to verify how guidance notes render inside the yellow callout box..."></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow font-sans fw-bold">
                                    <i class="bi bi-send-fill me-2"></i>Send Test Transmission Now
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- TAB 4: COMPLETE EMAIL LOGS -->
        <!-- ========================================================= -->
        <div class="tab-pane fade {{ $activeTab == 'logs' ? 'show active' : '' }}" id="logsPane">
            <div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-light">
                <form method="GET" action="{{ route('superadmin.emailHub') }}" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="logs">
                    <div class="col-md-3">
                        <input type="text" name="reference_id" class="form-control text-sm font-monospace" placeholder="Filter by Reference ID..." value="{{ request('reference_id') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="recipient_email" class="form-control text-sm" placeholder="Filter by Recipient Email..." value="{{ request('recipient_email') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select text-sm">
                            <option value="">All Statuses</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent Successfully</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed Delivery</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 text-sm"><i class="bi bi-filter me-1"></i>Filter</button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('superadmin.emailHub', ['tab' => 'logs']) }}" class="btn btn-outline-secondary w-100 text-sm">Reset</a>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0 text-sm">
                        <thead class="bg-dark text-white text-xxs text-uppercase">
                            <tr>
                                <th class="ps-4 py-3">Timestamp</th>
                                <th>Recipient</th>
                                <th>Reference ID</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($emailLogs as $log)
                            <tr>
                                <td class="ps-4 text-xs font-monospace text-muted whitespace-nowrap">{{ $log->created_at->format('d M, Y H:i:s') }}</td>
                                <td class="fw-bold">{{ $log->recipient_email }}</td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary border font-monospace">{{ $log->reference_id ?? 'General' }}</span></td>
                                <td class="text-break" style="max-width: 300px;">{{ $log->subject }}</td>
                                <td>
                                    @if($log->status === 'sent')
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Sent</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Failed</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" 
                                            onclick="previewEmailModal({{ $log->id }}, '{{ addslashes($log->subject) }}', '{{ $log->recipient_email }}')">
                                        <i class="bi bi-eye-fill me-1"></i>Preview
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No email logs found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top p-3 font-sans">
                    {{ $emailLogs->links() }}
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- MODALS SECTION -->
<!-- ========================================================================= -->

<!-- Create Template Modal -->
<div class="modal fade" id="createTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary text-white px-4 py-3">
                <h5 class="modal-title fw-bold font-sans"><i class="bi bi-plus-circle me-2"></i>Create New Notification Template</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('superadmin.emailHub.templates.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 font-sans text-sm">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Event Key (`event_key`) <span class="text-danger">*</span></label>
                            <input type="text" name="event_key" class="form-control text-sm font-monospace" placeholder="e.g. dean_approved" required>
                            <div class="form-text text-xxs">Must exactly match the submission stage slug or workflow trigger.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Template Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control text-sm" placeholder="e.g. Dean Verification Notice" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Subject Pattern <span class="text-danger">*</span></label>
                        <input type="text" name="email_subject" class="form-control text-sm" placeholder="[VMRFDU Portal] Activity Status Update: {reference_id}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Body HTML / Text <span class="text-danger">*</span></label>
                        <textarea name="email_body" rows="6" class="form-control text-sm font-monospace" placeholder="Activity {reference_id} ({title}) has reached stage {stage}. Remarks: {remarks}..." required></textarea>
                        <div class="form-text text-xxs text-primary fw-bold mt-1">
                            Available variables: <code>{reference_id}</code>, <code>{title}</code>, <code>{stage}</code>, <code>{remarks}</code>, <code>{recipient_name}</code>, <code>{college_name}</code>, <code>{activity_date}</code>, <code>{category}</code>, <code>{action_url}</code>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">In-App Portal Notification Title</label>
                            <input type="text" name="system_title" class="form-control text-sm" placeholder="Activity Status Updated">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">In-App Message Text</label>
                            <input type="text" name="system_message" class="form-control text-sm" placeholder="Activity {reference_id} moved to {stage}.">
                        </div>
                    </div>

                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="newTplActive" checked value="1">
                        <label class="form-check-label fw-bold" for="newTplActive">Enable this template automatically</label>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill btn-sm font-sans" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill btn-sm font-sans px-4 shadow">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Template Modal -->
<div class="modal fade" id="editTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-dark text-white px-4 py-3">
                <h5 class="modal-title fw-bold font-sans" id="editModalHeaderTitle"><i class="bi bi-pencil-square me-2"></i>Edit Notification Template</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTemplateForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4 font-sans text-sm">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Event Key (`event_key`)</label>
                            <input type="text" id="editEventKey" class="form-control text-sm font-monospace bg-light" disabled>
                            <div class="form-text text-xxs">Trigger key is immutable to preserve system stage bindings.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Template Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName" class="form-control text-sm" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Subject Pattern <span class="text-danger">*</span></label>
                        <input type="text" name="email_subject" id="editSubject" class="form-control text-sm" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Body HTML / Text <span class="text-danger">*</span></label>
                        <textarea name="email_body" id="editBody" rows="6" class="form-control text-sm font-monospace" required></textarea>
                        <div class="form-text text-xxs text-primary fw-bold mt-1">
                            Available variables: <code>{reference_id}</code>, <code>{title}</code>, <code>{stage}</code>, <code>{remarks}</code>, <code>{recipient_name}</code>, <code>{college_name}</code>, <code>{activity_date}</code>, <code>{category}</code>, <code>{action_url}</code>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">In-App Portal Notification Title</label>
                            <input type="text" name="system_title" id="editSysTitle" class="form-control text-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">In-App Message Text</label>
                            <input type="text" name="system_message" id="editSysMsg" class="form-control text-sm">
                        </div>
                    </div>

                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="editActive" value="1">
                        <label class="form-check-label fw-bold" for="editActive">Enable this template</label>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill btn-sm font-sans" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill btn-sm font-sans px-4 shadow">Update Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template & Email Body Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary text-white px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold mb-0 font-sans" id="previewModalTitle"><i class="bi bi-eye me-2"></i>Live Template Preview</h5>
                    <div class="text-xxs text-light opacity-75 font-monospace" id="previewModalSub">Rendered with dynamic organization branding &amp; logo</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-light" style="min-height: 550px;">
                <iframe id="previewIframe" style="width: 100%; height: 600px; border: none; display: block;"></iframe>
            </div>
            <div class="modal-footer bg-white px-4 py-2">
                <button type="button" class="btn btn-secondary rounded-pill btn-sm font-sans px-4" data-bs-dismiss="modal">Close Preview</button>
            </div>
        </div>
    </div>
</div>

<script>
// Color Picker sync
document.getElementById('primaryColorPicker').addEventListener('input', function() {
    document.getElementById('primaryColorHex').value = this.value;
});
document.getElementById('primaryColorHex').addEventListener('input', function() {
    document.getElementById('primaryColorPicker').value = this.value;
});
document.getElementById('accentColorPicker').addEventListener('input', function() {
    document.getElementById('accentColorHex').value = this.value;
});
document.getElementById('accentColorHex').addEventListener('input', function() {
    document.getElementById('accentColorPicker').value = this.value;
});

// Edit Template Modal Trigger
function openEditTemplateModal(id, key, name, subject, body, sysTitle, sysMsg, isActive) {
    const form = document.getElementById('editTemplateForm');
    form.action = "{{ url('/super-admin/email-hub/templates') }}/" + id;
    
    document.getElementById('editModalHeaderTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Editing: ' + name;
    document.getElementById('editEventKey').value = key;
    document.getElementById('editName').value = name;
    document.getElementById('editSubject').value = subject;
    document.getElementById('editBody').value = body;
    document.getElementById('editSysTitle').value = sysTitle;
    document.getElementById('editSysMsg').value = sysMsg;
    document.getElementById('editActive').checked = isActive;
    
    new bootstrap.Modal(document.getElementById('editTemplateModal')).show();
}

// Template Preview Modal
function previewTemplateModal(id, title) {
    document.getElementById('previewModalTitle').innerHTML = '<i class="bi bi-eye me-2"></i>Live Preview: ' + title;
    document.getElementById('previewIframe').src = "{{ url('/super-admin/email-hub/templates') }}/" + id + "/preview";
    new bootstrap.Modal(document.getElementById('previewModal')).show();
}

// Email Body Log Preview Modal
function previewEmailModal(id, subject, recipient) {
    document.getElementById('previewModalTitle').innerHTML = '<i class="bi bi-envelope-open me-2"></i>' + subject;
    document.getElementById('previewModalSub').innerText = 'Transmitted to: ' + recipient;
    document.getElementById('previewIframe').src = "{{ url('/super-admin/logs-hub/email-preview') }}/" + id;
    new bootstrap.Modal(document.getElementById('previewModal')).show();
}
</script>
@endsection
