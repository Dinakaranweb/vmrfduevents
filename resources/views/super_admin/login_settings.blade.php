@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark tracking-tight"><i class="bi bi-box-arrow-in-right text-primary me-2"></i>Login Page Customization</h4>
            <p class="text-muted mb-0 small">Customize the branding, welcome message, and text layout on the unified login screen.</p>
        </div>
        <a href="{{ route('superadmin.settings') }}" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Back to Master Settings
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-fonts text-primary me-2"></i>Login Page Texts</h5>
            <span class="badge bg-primary-subtle text-primary">Live Updates</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('superadmin.settings.login.update') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Browser Tab Title</label>
                        <input type="text" class="form-control" name="login_title" value="{{ $loginTitle ?? 'Institutional Login - VMRFDU Centralized Portal' }}" placeholder="e.g. Institutional Login - VMRFDU Centralized Portal">
                    </div>
                    
                    <div class="col-12 mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-card-heading text-secondary me-2"></i>Left Banner Section</h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Banner Top Header</label>
                        <input type="text" class="form-control" name="login_banner_header" value="{{ $loginBannerHeader ?? 'VMRFDU Official Portal' }}" placeholder="e.g. VMRFDU Official Portal">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Banner Subheader</label>
                        <input type="text" class="form-control" name="login_banner_subheader" value="{{ $loginBannerSubheader ?? 'Central Administration' }}" placeholder="e.g. Central Administration">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Banner Main Heading</label>
                        <input type="text" class="form-control" name="login_banner_main_text" value="{{ $loginBannerMainText ?? 'Centralized Activity & Media Publication' }}" placeholder="e.g. Centralized Activity & Media Publication">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Banner Description Paragraph</label>
                        <textarea class="form-control" name="login_banner_paragraph" rows="3" placeholder="Descriptive text...">{{ $loginBannerParagraph ?? 'A unified institutional platform designed for constituent colleges across Salem, Chennai, and Puducherry to submit, verify, approve, and broadcast university events and research milestones.' }}</textarea>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-layout-text-window-reverse text-secondary me-2"></i>Banner Footer Area</h6>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Footer Main Text</label>
                        <input type="text" class="form-control" name="login_footer_text" value="{{ $loginFooterText ?? '' }}" placeholder="© 2026 Vinayaka Mission's Research Foundation (DU). All rights reserved.">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Footer System / Sub Text</label>
                        <input type="text" class="form-control" name="login_footer_system_text" value="{{ $loginFooterSystemText ?? '' }}" placeholder="Institutional Activity Verification Engine">
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-input-cursor-text text-secondary me-2"></i>Right Form Section</h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Login Form Title</label>
                        <input type="text" class="form-control" name="login_form_title" value="{{ $loginFormTitle ?? 'Sign In' }}" placeholder="e.g. Sign In">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Login Form Subtitle</label>
                        <input type="text" class="form-control" name="login_form_subtitle" value="{{ $loginFormSubtitle ?? 'Enter your institutional credentials to access the activity portal.' }}" placeholder="e.g. Enter your credentials...">
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-megaphone text-secondary me-2"></i>Announcements & Alerts</h6>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Announcement Text (Leave blank to hide)</label>
                        <input type="text" class="form-control" name="login_announcement" value="{{ $loginAnnouncement ?? '' }}" placeholder="e.g. Scheduled maintenance this weekend...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Alert Color Type</label>
                        <select class="form-select" name="login_announcement_type">
                            <option value="info" {{ ($loginAnnouncementType ?? 'info') == 'info' ? 'selected' : '' }}>Info (Blue)</option>
                            <option value="warning" {{ ($loginAnnouncementType ?? 'info') == 'warning' ? 'selected' : '' }}>Warning (Yellow)</option>
                            <option value="danger" {{ ($loginAnnouncementType ?? 'info') == 'danger' ? 'selected' : '' }}>Danger (Red)</option>
                            <option value="success" {{ ($loginAnnouncementType ?? 'info') == 'success' ? 'selected' : '' }}>Success (Green)</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-link-45deg text-secondary me-2"></i>Support & Footer Links</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Helpdesk / Documentation URL</label>
                        <input type="url" class="form-control" name="login_helpdesk_url" value="{{ $loginHelpdeskUrl ?? '' }}" placeholder="https://helpdesk.vmrfdu.edu.in">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark text-xs mb-1">Support Email Address</label>
                        <input type="email" class="form-control" name="login_support_email" value="{{ $loginSupportEmail ?? '' }}" placeholder="support@vmrfdu.edu.in">
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-toggle-on text-secondary me-2"></i>Component Visibility</h6>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch form-switch-md mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="showDirectoryToggle" name="login_show_directory" value="true" {{ ($loginShowDirectory ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold ms-2" for="showDirectoryToggle">
                                Show Official Access Directory (Autofill Box)
                            </label>
                        </div>
                        <p class="text-muted small mb-0 ms-1">If disabled, the user credential autofill box below the login form will be completely hidden from the login screen.</p>
                    </div>

                    <div class="col-12 text-end mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                            <i class="bi bi-save me-1"></i> Save Login Page Customizations
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
