@extends('layouts.app')

@section('title', 'System Settings & API Configurations')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1"><i class="bi bi-gear-fill text-primary me-2"></i>Portal System Configurations</h3>
    <p class="text-muted mb-0">Configure social media API keys, notification gateways, and automated PR caption generation rules</p>
</div>

<div class="row g-4">
    <div class="col-12 col-md-6">
        <div class="portal-card p-4 h-100">
            <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-share text-primary me-2"></i>Social Media API Integrations</h5>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Instagram Graph API Token</label>
                <input type="password" class="form-control font-monospace" value="IGQVJXQ1pZAX3ZAlNmR1...">
                <span class="form-text text-success"><i class="bi bi-check-circle-fill"></i> Connected to official handle @vmrfdu_official</span>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">LinkedIn Organization ID</label>
                <input type="text" class="form-control" value="urn:li:organization:9847123">
                <span class="form-text text-success"><i class="bi bi-check-circle-fill"></i> Verified University Feed</span>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Twitter / X Bearer Token</label>
                <input type="password" class="form-control font-monospace" value="AAAAAAAAAAAAAAAAAAAAAKG...">
                <span class="form-text text-success"><i class="bi bi-check-circle-fill"></i> Connected</span>
            </div>

            <button class="btn btn-primary w-100 fw-semibold mt-2" onclick="alert('API credentials updated and verified!')">Save API Settings</button>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="portal-card p-4 h-100">
            <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-sliders text-primary me-2"></i>AI Caption & Workflow Policies</h5>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="aiAuto" checked>
                <label class="form-check-label fw-semibold" for="aiAuto">Enable AI Auto-Suggestion on Submission Form</label>
                <div class="small text-muted">Automatically generates optimized PR text using event titles and tags.</div>
            </div>

            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" id="notifEmail" checked>
                <label class="form-check-label fw-semibold" for="notifEmail">Email / SMS Alert Notifications</label>
                <div class="small text-muted">Sends instant notification to VC and Registrar when urgent activities are submitted.</div>
            </div>

            <button class="btn btn-outline-primary w-100 fw-semibold" onclick="alert('Workflow policy saved.')">Update Policies</button>
        </div>
    </div>
</div>
@endsection
