@extends('layouts.app')

@section('title', 'Institutional Media Studio & PR Broadcasting Desk - VMRFDU')

@section('content')
<!-- Custom Media Studio Enterprise Styles -->
<style>
    .media-desk-header {
        background: linear-gradient(135deg, #090d16 0%, #172033 55%, #231f4e 100%);
        color: #ffffff;
        border-radius: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px -10px rgba(9, 13, 22, 0.5);
    }
    .media-desk-header::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -5%;
        width: 360px;
        height: 360px;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.25) 0%, rgba(139, 92, 246, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .media-desk-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: 15%;
        width: 280px;
        height: 280px;
        background: radial-gradient(circle, rgba(6, 182, 212, 0.2) 0%, rgba(6, 182, 212, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .ms-stat-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
    }
    .ms-stat-card:hover, .ms-stat-card:focus-within {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }
    .ms-icon-circle {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        transition: transform 0.3s ease;
    }
    .ms-stat-card:hover .ms-icon-circle {
        transform: scale(1.1) rotate(-6deg);
    }
    .nav-pills-custom .nav-link {
        border-radius: 50rem;
        transition: all 0.25s ease;
        border: 1px solid transparent;
        font-weight: 600;
        padding: 0.65rem 1.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }
    .nav-pills-custom .nav-link:hover:not(.active) {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    .nav-pills-custom .nav-link.active {
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.18);
    }
    .activity-card {
        border: 1px solid #e2e8f0;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        border-radius: 18px;
    }
    .activity-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
    }
    .file-pill {
        transition: all 0.2s ease;
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
    }
    .file-pill:hover {
        background-color: #eff6ff;
        border-color: #3b82f6;
        transform: scale(1.02);
    }
    /* Accessible Focus Ratios */
    a:focus-visible, button:focus-visible, input:focus-visible, textarea:focus-visible {
        outline: 3px solid #3b82f6 !important;
        outline-offset: 2px !important;
    }
</style>

<!-- Executive Studio Control Banner -->
<div class="media-desk-header p-4 p-md-5 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-4" role="region" aria-label="Media Studio Header and Controls">
    <div class="d-flex align-items-center gap-4 position-relative z-1">
        <div class="bg-info bg-opacity-20 p-3 rounded-4 border border-info border-opacity-30 d-flex align-items-center justify-content-center text-info shadow-inner" style="width: 72px; height: 72px;">
            <i class="bi bi-camera-reels-fill fs-1" aria-hidden="true"></i>
        </div>
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <span class="badge bg-info text-dark px-3 py-1 rounded-pill text-xxs font-monospace fw-bold shadow-sm">
                    <i class="bi bi-broadcast me-1" aria-hidden="true"></i> PR & SOCIAL MEDIA BROADCASTING DESK
                </span>
                <span class="badge bg-purple bg-opacity-25 text-white border border-light border-opacity-25 px-3 py-1 rounded-pill text-xxs font-monospace" style="background: rgba(139, 92, 246, 0.3);">
                    <i class="bi bi-layers-fill me-1" aria-hidden="true"></i> STAGE 1 & STAGE 2 ASSET MANAGEMENT
                </span>
                <span class="text-white-50 text-xs ms-2"><i class="bi bi-clock me-1" aria-hidden="true"></i> {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }}</span>
            </div>
            <h1 class="h2 fw-bold mb-1 text-white tracking-tight">Institutional Media Studio & PR Desk</h1>
            <p class="text-white-50 mb-0 small">Crop & optimize high-res photos, refine AI-generated social captions, download activity asset packs, and dispatch broadcast publications across 5 primary channels.</p>
        </div>
    </div>

    <!-- Quick Studio Operations -->
    <div class="d-flex flex-wrap gap-2 position-relative z-1">
        <a href="{{ route('media.exportQueue', ['stage' => $activeTab]) }}" class="btn btn-light fw-bold px-4 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2 text-dark" aria-label="Export current stage data to CSV">
            <i class="bi bi-file-earmark-spreadsheet-fill text-success fs-5" aria-hidden="true"></i> <span>Export Stage CSV</span>
        </a>
        <a href="{{ route('gallery.index') }}" class="btn btn-outline-light px-4 py-2 rounded-pill d-flex align-items-center gap-2 fw-semibold" aria-label="Open Public Gallery">
            <i class="bi bi-collection-fill" aria-hidden="true"></i> <span>Public Gallery</span>
        </a>
    </div>
</div>

<!-- Sub-Menu Navigation Tabs -->
<nav aria-label="Media Desk Stage Navigation">
    <ul class="nav nav-pills nav-pills-custom gap-2 mb-4 p-2 bg-white rounded-4 shadow-sm border flex-wrap">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'overview' ? 'active bg-info text-dark fw-bold' : 'text-dark' }}" href="{{ route('media.dashboard') }}" aria-label="Dashboard Overview">
                <i class="bi bi-speedometer2" aria-hidden="true"></i> <span>Studio Overview</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'editing' ? 'active bg-primary text-white' : 'text-dark' }}" href="{{ route('media.editing') }}" aria-label="Stage 1: Photo and Caption Editing Queue">
                <i class="bi bi-pencil-square" aria-hidden="true"></i> <span>Stage 1: Photo & Caption Editing</span>
                @if(isset($pendingEditingCount) && $pendingEditingCount > 0)
                    <span class="badge {{ $activeTab === 'editing' ? 'bg-white text-primary' : 'bg-primary text-white' }} ms-1 rounded-pill">{{ $pendingEditingCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'ready' ? 'active bg-success text-white' : 'text-dark' }}" href="{{ route('media.ready') }}" aria-label="Stage 2: Ready for Broadcast Queue">
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i> <span>Stage 2: Ready for Broadcast</span>
                @if(isset($readyCount) && $readyCount > 0)
                    <span class="badge {{ $activeTab === 'ready' ? 'bg-white text-success' : 'bg-success text-white' }} ms-1 rounded-pill">{{ $readyCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'published' ? 'active bg-dark text-white' : 'text-dark' }}" href="{{ route('media.approved') }}" aria-label="Published Broadcasts Archive">
                <i class="bi bi-broadcast" aria-hidden="true"></i> <span>Published Archive</span>
                @if(isset($publishedCount) && $publishedCount > 0)
                    <span class="badge {{ $activeTab === 'published' ? 'bg-white text-dark' : 'bg-dark text-white' }} ms-1 rounded-pill">{{ $publishedCount }}</span>
                @endif
            </a>
        </li>
    </ul>
</nav>

@if($activeTab === 'overview')
    <!-- STUDIO OVERVIEW SUB-MENU (With Stat Counters & Chart Suite) -->
    <section aria-label="Studio Primary Metrics" class="row g-4 mb-4">
        <!-- Card 1: Stage 1 Editing Queue -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ms-stat-card h-100 d-flex flex-column justify-content-between position-relative" style="border-top: 4px solid #3b82f6;">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="text-xs fw-bold text-muted text-uppercase tracking-wider">Stage 1 Editing Queue</span>
                        <div class="fs-2 fw-bold text-dark tracking-tight mt-1 counter-value" data-target="{{ $pendingEditingCount ?? $pendingEditorQueue->count() }}">{{ $pendingEditingCount ?? $pendingEditorQueue->count() }}</div>
                    </div>
                    <div class="ms-icon-circle bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between text-xs text-muted border-top pt-3 mt-2">
                    <span><i class="bi bi-clock text-primary me-1" aria-hidden="true"></i> Awaiting Editor Action</span>
                    <a href="{{ route('media.editing') }}" class="fw-bold text-primary text-decoration-none stretched-link">Open Stage 1 <i class="bi bi-chevron-right text-xxs" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>

        <!-- Card 2: Locked with PA to Registrar -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ms-stat-card h-100 d-flex flex-column justify-content-between position-relative" style="border-top: 4px solid #f59e0b;">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="text-xs fw-bold text-muted text-uppercase tracking-wider">Locked with PA to Registrar</span>
                        <div class="fs-2 fw-bold text-dark tracking-tight mt-1 counter-value" data-target="{{ $lockedRegistrarCount ?? $lockedRegistrarQueue->count() }}">{{ $lockedRegistrarCount ?? $lockedRegistrarQueue->count() }}</div>
                    </div>
                    <div class="ms-icon-circle bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between text-xs text-muted border-top pt-3 mt-2">
                    <span><i class="bi bi-lock-fill text-warning me-1" aria-hidden="true"></i> Under Verification</span>
                    <a href="{{ route('submissions.index') }}?status=verification_pending" class="fw-bold text-warning text-decoration-none stretched-link">Check Status <i class="bi bi-chevron-right text-xxs" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>

        <!-- Card 3: Stage 2 Ready for Broadcast -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ms-stat-card h-100 d-flex flex-column justify-content-between position-relative" style="border-top: 4px solid #10b981;">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="text-xs fw-bold text-muted text-uppercase tracking-wider">Stage 2 Ready for Broadcast</span>
                        <div class="fs-2 fw-bold text-dark tracking-tight mt-1 counter-value" data-target="{{ $readyCount ?? $readyForPublication->count() }}">{{ $readyCount ?? $readyForPublication->count() }}</div>
                    </div>
                    <div class="ms-icon-circle bg-success bg-opacity-10 text-success">
                        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between text-xs text-muted border-top pt-3 mt-2">
                    <span><i class="bi bi-check2-all text-success me-1" aria-hidden="true"></i> Approved by Registrar</span>
                    <a href="{{ route('media.ready') }}" class="fw-bold text-success text-decoration-none stretched-link">Open Stage 2 <i class="bi bi-chevron-right text-xxs" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>

        <!-- Card 4: Published Live Archive -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ms-stat-card h-100 d-flex flex-column justify-content-between position-relative" style="border-top: 4px solid #8b5cf6;">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="text-xs fw-bold text-muted text-uppercase tracking-wider">Published Live Archive</span>
                        <div class="fs-2 fw-bold text-dark tracking-tight mt-1 counter-value" data-target="{{ $publishedCount ?? $recentlyPublished->count() }}">{{ $publishedCount ?? $recentlyPublished->count() }}</div>
                    </div>
                    <div class="ms-icon-circle bg-purple bg-opacity-10" style="color: #8b5cf6;">
                        <i class="bi bi-broadcast-pin" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between text-xs text-muted border-top pt-3 mt-2">
                    <span><i class="bi bi-share text-purple me-1" aria-hidden="true"></i> Live on Channels</span>
                    <a href="{{ route('media.approved') }}" class="fw-bold text-decoration-none stretched-link" style="color: #8b5cf6;">View Archive <i class="bi bi-chevron-right text-xxs" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Studio Analytics Chart Suite (3 Modern Accessible Charts) -->
    <section aria-label="Media Studio Analytics Charts" class="row g-4 mb-4">
        <!-- Chart 1: Media Desk Stage Pipeline -->
        <div class="col-12 col-xl-6">
            <div class="portal-card p-4 bg-white border shadow-sm rounded-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                    <div>
                        <h2 class="h5 fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-funnel-fill text-primary fs-4" aria-hidden="true"></i> Media Studio Pipeline Stages
                        </h2>
                        <p class="text-muted small mb-0">Distribution of activity packages across editing and broadcasting phases</p>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill text-xs fw-bold">Live Flow</span>
                </div>
                <div class="position-relative flex-grow-1 d-flex align-items-center justify-content-center" style="min-height: 280px;">
                    <canvas id="msPipelineChart" aria-label="Media Studio Pipeline Chart" role="img"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 2: Social Media Platform Distribution -->
        <div class="col-12 col-xl-6">
            <div class="portal-card p-4 bg-white border shadow-sm rounded-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                    <div>
                        <h2 class="h5 fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-share-fill text-purple fs-4" style="color:#8b5cf6;" aria-hidden="true"></i> Broadcast Platform Distribution
                        </h2>
                        <p class="text-muted small mb-0">Share of published activities across institutional social channels</p>
                    </div>
                    <span class="badge px-3 py-1 rounded-pill text-xs fw-bold text-white shadow-sm" style="background: #8b5cf6;">5 Channels</span>
                </div>
                <div class="position-relative flex-grow-1 d-flex align-items-center justify-content-center" style="min-height: 280px;">
                    <canvas id="msPlatformChart" aria-label="Broadcast Platform Distribution Chart" role="img"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 3: 6-Month Media Output Trajectory -->
        <div class="col-12">
            <div class="portal-card p-4 bg-white border shadow-sm rounded-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                    <div>
                        <h2 class="h5 fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-graph-up-arrow text-info fs-4" aria-hidden="true"></i> 6-Month Broadcast Output Trajectory
                        </h2>
                        <p class="text-muted small mb-0">Monthly progression of finalized media packages broadcasted live</p>
                    </div>
                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-1 rounded-pill text-xs fw-bold">Studio Performance</span>
                </div>
                <div class="position-relative" style="min-height: 300px;">
                    <canvas id="msOutputChart" aria-label="6-Month Broadcast Output Trajectory Chart" role="img"></canvas>
                </div>
            </div>
        </div>
    </section>

    <!-- Studio Navigation Action Portals -->
    <section aria-label="Studio Action Portals" class="row g-4">
        <div class="col-12 col-md-4">
            <div class="portal-card p-4 bg-white border shadow-sm rounded-4 d-flex flex-column justify-content-between h-100">
                <div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3 d-inline-block mb-3">
                        <i class="bi bi-pencil-square fs-3" aria-hidden="true"></i>
                    </div>
                    <h3 class="h5 fw-bold text-dark">Stage 1: Photo & Caption Editing</h3>
                    <p class="text-muted small mb-4">Activities verified by the Registrar arrive here first. Download activity data packs, crop/edit photos (`Edited Photos for Social Media`), optimize social captions, and submit to the Office of the Registrar.</p>
                </div>
                <a href="{{ route('media.editing') }}" class="btn btn-primary fw-bold py-3 rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-2">
                    Open Editing Queue <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="portal-card p-4 bg-white border shadow-sm rounded-4 d-flex flex-column justify-content-between h-100">
                <div>
                    <div class="bg-success bg-opacity-10 text-success rounded-4 p-3 d-inline-block mb-3">
                        <i class="bi bi-check-circle-fill fs-3" aria-hidden="true"></i>
                    </div>
                    <h3 class="h5 fw-bold text-dark">Stage 2: Ready for Broadcast</h3>
                    <p class="text-muted small mb-4">Once approved by the Office of the Registrar, finalized packages move here. Download high-res media packs, broadcast live across platforms, or schedule timing.</p>
                </div>
                <a href="{{ route('media.ready') }}" class="btn btn-success fw-bold py-3 rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-2">
                    Open Broadcast Queue <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="portal-card p-4 bg-white border shadow-sm rounded-4 d-flex flex-column justify-content-between h-100">
                <div>
                    <div class="bg-purple bg-opacity-10 rounded-4 p-3 d-inline-block mb-3" style="color: #8b5cf6;">
                        <i class="bi bi-collection-fill fs-3" aria-hidden="true"></i>
                    </div>
                    <h3 class="h5 fw-bold text-dark">Published Broadcasts Archive</h3>
                    <p class="text-muted small mb-4">Explore all published social media broadcasts across Instagram, LinkedIn, Facebook, and Twitter/X, and download archival activity asset sheets.</p>
                </div>
                <a href="{{ route('media.approved') }}" class="btn text-white fw-bold py-3 rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-2" style="background: #8b5cf6;">
                    View Published Archive <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </section>

@elseif($activeTab === 'editing')
    <!-- STAGE 1 SUB-MENU: PHOTO & CAPTION EDITING -->
    <section aria-label="Stage 1 Photo and Caption Editing Queue" class="row g-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between mb-4 bg-white p-4 rounded-4 shadow-sm border flex-wrap gap-3">
                <div>
                    <h2 class="h4 fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square text-primary fs-3" aria-hidden="true"></i> Stage 1: Photo & Caption Editing Queue
                        <span class="badge bg-primary text-white rounded-pill px-3">{{ $pendingEditorQueue->count() }}</span>
                    </h2>
                    <p class="text-muted small mb-0">Review verified activities. <strong>Option A:</strong> Media Package Reviewed & Published directly. <strong>Option B:</strong> Edit photos/captions and submit to Registrar.</p>
                </div>
                <a href="{{ route('media.exportQueue', ['stage' => 'editing']) }}" class="btn btn-outline-primary fw-bold rounded-pill px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-download" aria-hidden="true"></i> Export Stage 1 CSV
                </a>
            </div>

            @if($pendingEditorQueue->isEmpty())
                <div class="portal-card p-5 bg-white border shadow-sm rounded-4 text-center my-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-4 d-inline-flex mb-3 align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-images fs-1" aria-hidden="true"></i>
                    </div>
                    <h3 class="h5 fw-bold text-dark mb-1">No Submissions Awaiting Editor Review</h3>
                    <p class="text-muted small mb-0">When new activities are verified by the Registrar, they will appear in this Stage 1 processing queue.</p>
                </div>
            @else
                <div class="d-flex flex-column gap-4">
                    @foreach($pendingEditorQueue as $item)
                        @php
                            $images = $item->media->filter(function($m) {
                                if (in_array($m->file_type, ['image', 'geo_image'])) return true;
                                if (in_array($m->file_type, ['document', 'supporting_document', 'report_document'])) {
                                    return preg_match('/(image|jpg|jpeg|png|webp)/i', $m->mime_type . $m->file_name);
                                }
                                return false;
                            })->values();
                            $primaryMedia = $item->media->first();
                        @endphp
                        <div class="portal-card activity-card p-4 bg-white shadow-sm rounded-4 border-primary border-opacity-50">
                            <div class="row g-4 align-items-start">
                                <div class="col-12 col-md-4">
                                    @if($images->count() > 1)
                                        <div id="carouselStage1_{{ $item->id }}" class="carousel slide rounded-4 overflow-hidden border shadow-sm" data-bs-ride="false" style="height: 220px; background: #0f172a;">
                                            <div class="carousel-inner h-100">
                                                @foreach($images as $index => $img)
                                                <div class="carousel-item h-100 {{ $index === 0 ? 'active' : '' }}">
                                                    <img src="{{ $img->file_path }}" alt="Image {{ $index + 1 }} for {{ $item->title }}" class="w-100 h-100 object-fit-cover">
                                                </div>
                                                @endforeach
                                            </div>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselStage1_{{ $item->id }}" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#carouselStage1_{{ $item->id }}" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                            <span class="badge bg-dark position-absolute bottom-0 end-0 m-2 text-xxs fw-bold font-monospace z-3">
                                                <i class="bi bi-images me-1" aria-hidden="true"></i>{{ $images->count() }} Photos
                                            </span>
                                        </div>
                                    @elseif($images->count() === 1)
                                        <div class="position-relative rounded-4 overflow-hidden border shadow-sm" style="height: 220px; background: #0f172a;">
                                            <img src="{{ $images->first()->file_path }}" alt="Activity Image" class="w-100 h-100 object-fit-cover">
                                            <span class="badge bg-dark position-absolute bottom-0 end-0 m-2 text-xxs fw-bold font-monospace">
                                                <i class="bi bi-image me-1" aria-hidden="true"></i>1 Photo
                                            </span>
                                        </div>
                                    @elseif($primaryMedia && $primaryMedia->file_type === 'document')
                                        <div class="bg-danger bg-opacity-10 border border-danger rounded-4 h-100 p-4 d-flex flex-column justify-content-center text-center" style="min-height: 220px;">
                                            <i class="bi bi-file-earmark-pdf-fill text-danger fs-1 mb-2" aria-hidden="true"></i>
                                            <div class="fw-bold text-dark text-xs text-truncate mb-2">{{ $primaryMedia->file_name }}</div>
                                            <a href="{{ $primaryMedia->file_path }}" download class="btn btn-danger btn-sm text-xxs fw-bold rounded-pill shadow-sm"><i class="bi bi-download me-1" aria-hidden="true"></i>Download PDF</a>
                                        </div>
                                    @elseif($primaryMedia && $primaryMedia->file_type === 'video')
                                        <div class="bg-primary bg-opacity-10 border border-primary rounded-4 h-100 p-4 d-flex flex-column justify-content-center text-center" style="min-height: 220px;">
                                            <i class="bi bi-play-circle-fill text-primary fs-1 mb-2" aria-hidden="true"></i>
                                            <div class="fw-bold text-dark text-xs text-truncate mb-2">{{ $primaryMedia->file_name }}</div>
                                            <a href="{{ $primaryMedia->file_path }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm text-xxs fw-bold rounded-pill shadow-sm"><i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>Watch Video</a>
                                        </div>
                                    @else
                                        <div class="bg-light rounded-4 h-100 d-flex align-items-center justify-content-center text-muted border" style="height: 220px;">
                                            <div class="text-center"><i class="bi bi-image fs-2 d-block mb-1 opacity-50" aria-hidden="true"></i><span class="text-xs">No media attached</span></div>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-12 col-md-8 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                            <span class="badge bg-dark text-white font-monospace px-3 py-1 shadow-sm">{{ $item->reference_id }}</span>
                                            {!! $item->status_badge !!}
                                        </div>
                                        <h3 class="h5 fw-bold text-dark mb-1">{{ $item->title }}</h3>
                                        <div class="text-xs text-muted mb-3">
                                            <i class="bi bi-building me-1" aria-hidden="true"></i>{{ $item->college ? $item->college->name : 'N/A' }} &bull; <i class="bi bi-calendar-event me-1" aria-hidden="true"></i>{{ $item->activity_date->format('d M Y') }}
                                        </div>

                                        @if($item->status === 'editor_discuss' && $item->admin_remarks)
                                            <div class="p-3 bg-warning bg-opacity-10 border border-warning rounded-4 mb-3 text-dark small">
                                                <strong class="text-warning-dark d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-1" aria-hidden="true"></i>Registrar Discuss Feedback:</strong>
                                                {{ $item->admin_remarks }}
                                            </div>
                                        @endif

                                        <p class="text-muted text-xs mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $item->description }}</p>

                                        <!-- Attached Media Download Block -->
                                        @if($item->media && $item->media->count() > 0)
                                        <div class="mb-3 p-3 bg-light rounded-4 border">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-xs fw-bold text-dark"><i class="bi bi-paperclip text-info me-1" aria-hidden="true"></i>Attached Media Files ({{ $item->media->count() }}):</span>
                                                <span class="text-xxs text-muted">Click asset to preview</span>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($item->media as $m)
                                                    @php
                                                        $isImage = false;
                                                        $isPdf = false;
                                                        $modalTarget = '';
                                                        $modalClick = '';
                                                        
                                                        if (in_array($m->file_type, ['image', 'geo_image'])) {
                                                            $pillIcon = 'bi-image text-info';
                                                            $isImage = true;
                                                        } elseif (in_array($m->file_type, ['document', 'supporting_document', 'report_document'])) {
                                                            if(str_contains(strtolower($m->file_name), '.pdf') || str_contains(strtolower($m->mime_type), 'pdf')) { $pillIcon = 'bi-file-earmark-pdf-fill text-danger'; $isPdf = true; }
                                                            elseif(str_contains(strtolower($m->file_name), '.doc') || str_contains(strtolower($m->mime_type), 'word')) { $pillIcon = 'bi-file-earmark-word-fill text-primary'; }
                                                            elseif(str_contains(strtolower($m->file_name), '.xls') || str_contains(strtolower($m->mime_type), 'excel')) { $pillIcon = 'bi-file-earmark-excel-fill text-success'; }
                                                            elseif(preg_match('/(image|jpg|jpeg|png|webp)/i', $m->mime_type . $m->file_name)) { $pillIcon = 'bi-image text-info'; $isImage = true; }
                                                            else { $pillIcon = 'bi-file-earmark-text text-secondary'; }
                                                        } else {
                                                            $pillIcon = 'bi-play-circle-fill text-primary';
                                                        }
                                                        
                                                        if($isImage) {
                                                            $modalTarget = '#imageModal';
                                                            $modalClick = "showImageModal('".asset($m->file_path)."', '".addslashes($m->file_name)."')";
                                                        } elseif(in_array($m->file_type, ['document', 'supporting_document', 'report_document'])) {
                                                            $modalTarget = '#documentModal';
                                                            $modalClick = "showDocumentModal('".asset($m->file_path)."', '".addslashes($m->file_name)."', " . ($isPdf ? 'true' : 'false') . ")";
                                                        }
                                                    @endphp
                                                    <a href="{{ $modalTarget ? 'javascript:void(0)' : asset($m->file_path) }}" {{ $modalTarget ? '' : 'download' }} {!! $modalTarget ? 'data-bs-toggle="modal" data-bs-target="'.$modalTarget.'" onclick="'.$modalClick.'"' : '' !!} class="badge file-pill text-dark py-2 px-3 rounded-pill d-inline-flex align-items-center gap-2 text-decoration-none shadow-sm" aria-label="Preview asset {{ $m->file_name }}" style="cursor: pointer;">
                                                        <i class="bi {{ $pillIcon }}" aria-hidden="true"></i>
                                                        <span class="text-truncate" style="max-width: 150px;">{{ $m->file_name }}</span>
                                                        <i class="bi {{ $modalTarget ? 'bi-box-arrow-up-right' : 'bi-download' }} text-primary ms-1" aria-hidden="true"></i>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Forwarded Social Media Caption Accordion -->
                                        <div class="accordion mb-3 shadow-sm rounded-4 border overflow-hidden" id="captionAccordion_{{ $item->id }}">
                                            <div class="accordion-item border-0 bg-primary bg-opacity-10">
                                                <h3 class="accordion-header" id="headingCaption_{{ $item->id }}">
                                                    <button class="accordion-button collapsed py-2 px-3 bg-primary bg-opacity-10 text-primary fw-bold text-xs shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCaption_{{ $item->id }}" aria-expanded="false" aria-controls="collapseCaption_{{ $item->id }}">
                                                        <i class="bi bi-broadcast me-2" aria-hidden="true"></i>Forwarded Social Media Caption Suggestion
                                                    </button>
                                                </h3>
                                                <div id="collapseCaption_{{ $item->id }}" class="accordion-collapse collapse" aria-labelledby="headingCaption_{{ $item->id }}" data-bs-parent="#captionAccordion_{{ $item->id }}">
                                                    <div class="accordion-body p-3 pt-2 bg-white border-top">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="text-xxs text-muted"><i class="bi bi-info-circle me-1" aria-hidden="true"></i>Pre-generated public broadcast phrasing</span>
                                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded text-xxs fw-bold" onclick="navigator.clipboard.writeText(document.getElementById('caption_preview_{{ $item->id }}').innerText); this.innerHTML='Copied!'; setTimeout(()=>this.innerHTML='Copy Brief',1500);" aria-label="Copy caption phrasing to clipboard">Copy Brief</button>
                                                        </div>
                                                        <div id="caption_preview_{{ $item->id }}" class="text-xxs text-dark font-monospace bg-light p-2 rounded border" style="white-space: pre-line; max-height: 180px; overflow-y: auto;">{{ $item->social_media_caption ?: \App\Http\Controllers\ActivitySubmissionController::generateDefaultCaptionForSubmission($item) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top">
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="{{ route('submissions.show', $item->id) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm px-3 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-1" aria-label="View activity details for {{ $item->title }} (opens in new window)">
                                                <i class="bi bi-eye-fill" aria-hidden="true"></i> View Activity
                                            </a>
                                            <a href="{{ route('media.downloadPack', $item->id) }}" class="btn btn-outline-dark btn-sm px-3 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-1" aria-label="Download data pack ZIP for {{ $item->title }}">
                                                <i class="bi bi-file-earmark-zip-fill text-warning fs-6" aria-hidden="true"></i> Download Pack (ZIP)
                                            </a>
                                        </div>

                                        <button type="button" class="btn btn-success btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#directApproveModal" data-bs-id="{{ $item->id }}" data-bs-title="{{ $item->title }}" aria-label="Direct Publish {{ $item->title }}">
                                            <i class="bi bi-rocket-takeoff" aria-hidden="true"></i>Option A: Media Package Reviewed & Published
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#submitRegistrarModal" data-bs-id="{{ $item->id }}" data-bs-title="{{ $item->title }}" data-bs-caption="{{ $item->social_media_caption ?: $item->title }}" data-bs-images-count="{{ $item->media->where('file_type', 'image')->count() }}" aria-label="Edit and Submit {{ $item->title }} to Registrar">
                                            <i class="bi bi-pencil-square" aria-hidden="true"></i> Option B: Submit Corrections to Registrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Locked Submissions Section -->
            @if(isset($lockedRegistrarQueue) && $lockedRegistrarQueue->isNotEmpty())
                <div class="mt-5">
                    <div class="d-flex align-items-center justify-content-between mb-3 bg-secondary bg-opacity-10 p-3 rounded-4 border">
                        <h3 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-lock-fill text-danger fs-5" aria-hidden="true"></i> Locked Submissions Pending Office of the Registrar Review
                            <span class="badge bg-secondary text-white rounded-pill px-3">{{ $lockedRegistrarQueue->count() }}</span>
                        </h3>
                        <span class="text-xs text-muted font-monospace">officeregistrar@vmrfdu.edu.in</span>
                    </div>

                    <div class="alert alert-warning border-warning shadow-sm rounded-4 d-flex align-items-center gap-3 mb-4">
                        <i class="bi bi-shield-lock-fill fs-3 text-warning" aria-hidden="true"></i>
                        <div class="small text-dark">
                            <strong>Content Editing Locked:</strong> Once submitted to the Office of the Registrar (<a href="mailto:officeregistrar@vmrfdu.edu.in" class="fw-bold text-dark">officeregistrar@vmrfdu.edu.in</a>), activities become locked to preserve review integrity. If the Registrar marks a submission as <strong>"Discuss"</strong>, it will automatically unlock and appear in your editing queue above.
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @foreach($lockedRegistrarQueue as $lockedItem)
                            <div class="portal-card p-4 bg-light shadow-sm rounded-4 border">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-dark text-white font-monospace px-3 py-1 rounded-pill">{{ $lockedItem->reference_id }}</span>
                                        <span class="badge bg-warning text-dark border border-warning rounded-pill fw-bold d-flex align-items-center gap-1">
                                            <i class="bi bi-lock-fill" aria-hidden="true"></i> LOCKED: Pending Registrar Review
                                        </span>
                                    </div>
                                    <span class="text-xs text-muted"><i class="bi bi-clock me-1" aria-hidden="true"></i>Submitted {{ $lockedItem->updated_at->diffForHumans() }}</span>
                                </div>
                                <h4 class="h6 fw-bold text-dark mb-1">{{ $lockedItem->title }}</h4>
                                <div class="text-xs text-muted mb-3">
                                    <i class="bi bi-building me-1" aria-hidden="true"></i>{{ $lockedItem->college ? $lockedItem->college->name : 'N/A' }} &bull; <i class="bi bi-calendar-event me-1" aria-hidden="true"></i>{{ $lockedItem->activity_date->format('d M Y') }}
                                </div>

                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top">
                                    <span class="text-xxs text-muted font-monospace"><i class="bi bi-info-circle me-1" aria-hidden="true"></i>Editing disabled until review decision is finalized.</span>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('submissions.show', $lockedItem->id) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm px-3 py-1 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-1">
                                            <i class="bi bi-eye-fill" aria-hidden="true"></i> View Activity
                                        </a>
                                        <a href="{{ route('media.downloadPack', $lockedItem->id) }}" class="btn btn-outline-dark btn-sm px-3 py-1 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-1">
                                            <i class="bi bi-file-earmark-zip-fill text-warning" aria-hidden="true"></i> Download Pack (ZIP)
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

@elseif($activeTab === 'ready')
    <!-- STAGE 2 SUB-MENU: READY FOR BROADCAST -->
    <section aria-label="Stage 2 Ready for Broadcast Queue" class="row g-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between mb-4 bg-white p-4 rounded-4 shadow-sm border flex-wrap gap-3">
                <div>
                    <h2 class="h4 fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-3" aria-hidden="true"></i> Stage 2: Registrar Approved Broadcast Queue
                        <span class="badge bg-success text-white rounded-pill px-3">{{ $readyForPublication->count() }}</span>
                    </h2>
                    <p class="text-muted small mb-0">Authorized by the Office of the Registrar. Select social channels (Instagram, LinkedIn, Facebook, Twitter/X) and broadcast immediately.</p>
                </div>
                <a href="{{ route('media.exportQueue', ['stage' => 'ready']) }}" class="btn btn-outline-success fw-bold rounded-pill px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-download" aria-hidden="true"></i> Export Stage 2 CSV
                </a>
            </div>

            @if($readyForPublication->isEmpty())
                <div class="portal-card p-5 bg-white border shadow-sm rounded-4 text-center my-4">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-4 d-inline-flex mb-3 align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-send-check-fill fs-1" aria-hidden="true"></i>
                    </div>
                    <h3 class="h5 fw-bold text-dark mb-1">No Broadcast Tasks Pending</h3>
                    <p class="text-muted small mb-0">Once the Office of the Registrar approves edited submissions, they will appear here ready to publish live across channels.</p>
                </div>
            @else
                <div class="d-flex flex-column gap-4">
                    @foreach($readyForPublication as $item)
                        @php
                            $primaryMedia = $item->media->where('is_primary', true)->first() ?: $item->media->first();
                        @endphp
                        <div class="portal-card activity-card p-4 bg-white shadow-sm rounded-4 border-success border-opacity-50">
                            <div class="row g-4 align-items-start">
                                <div class="col-12 col-md-4">
                                    @if($primaryMedia)
                                        @if($primaryMedia->file_type === 'image')
                                        <div class="position-relative rounded-4 overflow-hidden border shadow-sm" style="height: 220px; background: #0f172a;">
                                            <img src="{{ $primaryMedia->file_path }}" alt="Primary Image for {{ $item->title }}" class="w-100 h-100 object-fit-cover" id="imgPreview_{{ $item->id }}">
                                            <span class="badge bg-dark position-absolute bottom-0 end-0 m-2 text-xxs fw-bold font-monospace">
                                                <i class="bi bi-image me-1" aria-hidden="true"></i>{{ $primaryMedia->dimensions ?: 'Full Res' }}
                                            </span>
                                        </div>
                                        @elseif($primaryMedia->file_type === 'document')
                                        <div class="bg-danger bg-opacity-10 border border-danger rounded-4 h-100 p-4 d-flex flex-column justify-content-center text-center" style="min-height: 220px;">
                                            <i class="bi bi-file-earmark-pdf-fill text-danger fs-1 mb-2" aria-hidden="true"></i>
                                            <div class="fw-bold text-dark text-xs text-truncate mb-2">{{ $primaryMedia->file_name }}</div>
                                            <a href="{{ $primaryMedia->file_path }}" download class="btn btn-danger btn-sm text-xxs fw-bold rounded-pill shadow-sm"><i class="bi bi-download me-1" aria-hidden="true"></i>Download PDF</a>
                                        </div>
                                        @else
                                        <div class="bg-primary bg-opacity-10 border border-primary rounded-4 h-100 p-4 d-flex flex-column justify-content-center text-center" style="min-height: 220px;">
                                            <i class="bi bi-play-circle-fill text-primary fs-1 mb-2" aria-hidden="true"></i>
                                            <div class="fw-bold text-dark text-xs text-truncate mb-2">{{ $primaryMedia->file_name }}</div>
                                            <a href="{{ $primaryMedia->file_path }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm text-xxs fw-bold rounded-pill shadow-sm"><i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>Watch Video</a>
                                        </div>
                                        @endif
                                    @else
                                        <div class="bg-light rounded-4 h-100 d-flex align-items-center justify-content-center text-muted border" style="height: 220px;">
                                            <div class="text-center"><i class="bi bi-image fs-2 d-block mb-1 opacity-50" aria-hidden="true"></i><span class="text-xs">No photo attached</span></div>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-12 col-md-8 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                            <span class="badge bg-dark text-white font-monospace px-3 py-1 shadow-sm">{{ $item->reference_id }}</span>
                                            {!! $item->status_badge !!}
                                        </div>
                                        <h3 class="h5 fw-bold text-dark mb-1">{{ $item->title }}</h3>
                                        <div class="text-xs text-muted mb-3">
                                            <i class="bi bi-building me-1" aria-hidden="true"></i>{{ $item->college ? $item->college->name : 'N/A' }} &bull; <i class="bi bi-calendar-event me-1" aria-hidden="true"></i>{{ $item->activity_date->format('d M Y') }}
                                        </div>

                                        <!-- Attached Media Download Block -->
                                        @if($item->media && $item->media->count() > 0)
                                        <div class="mb-3 p-3 bg-light rounded-4 border">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-xs fw-bold text-dark"><i class="bi bi-paperclip text-info me-1" aria-hidden="true"></i>Attached Media Files ({{ $item->media->count() }}):</span>
                                                <span class="text-xxs text-muted">Click asset to preview</span>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($item->media as $m)
                                                    @php
                                                        $isImage = false;
                                                        $isPdf = false;
                                                        $modalTarget = '';
                                                        $modalClick = '';
                                                        
                                                        if (in_array($m->file_type, ['image', 'geo_image'])) {
                                                            $pillIcon = 'bi-image text-info';
                                                            $isImage = true;
                                                        } elseif (in_array($m->file_type, ['document', 'supporting_document', 'report_document'])) {
                                                            if(str_contains(strtolower($m->file_name), '.pdf') || str_contains(strtolower($m->mime_type), 'pdf')) { $pillIcon = 'bi-file-earmark-pdf-fill text-danger'; $isPdf = true; }
                                                            elseif(str_contains(strtolower($m->file_name), '.doc') || str_contains(strtolower($m->mime_type), 'word')) { $pillIcon = 'bi-file-earmark-word-fill text-primary'; }
                                                            elseif(str_contains(strtolower($m->file_name), '.xls') || str_contains(strtolower($m->mime_type), 'excel')) { $pillIcon = 'bi-file-earmark-excel-fill text-success'; }
                                                            elseif(preg_match('/(image|jpg|jpeg|png|webp)/i', $m->mime_type . $m->file_name)) { $pillIcon = 'bi-image text-info'; $isImage = true; }
                                                            else { $pillIcon = 'bi-file-earmark-text text-secondary'; }
                                                        } else {
                                                            $pillIcon = 'bi-play-circle-fill text-primary';
                                                        }
                                                        
                                                        if($isImage) {
                                                            $modalTarget = '#imageModal';
                                                            $modalClick = "showImageModal('".asset($m->file_path)."', '".addslashes($m->file_name)."')";
                                                        } elseif(in_array($m->file_type, ['document', 'supporting_document', 'report_document'])) {
                                                            $modalTarget = '#documentModal';
                                                            $modalClick = "showDocumentModal('".asset($m->file_path)."', '".addslashes($m->file_name)."', " . ($isPdf ? 'true' : 'false') . ")";
                                                        }
                                                    @endphp
                                                    <a href="{{ $modalTarget ? 'javascript:void(0)' : asset($m->file_path) }}" {{ $modalTarget ? '' : 'download' }} {!! $modalTarget ? 'data-bs-toggle="modal" data-bs-target="'.$modalTarget.'" onclick="'.$modalClick.'"' : '' !!} class="badge file-pill text-dark py-2 px-3 rounded-pill d-inline-flex align-items-center gap-2 text-decoration-none shadow-sm" aria-label="Preview asset {{ $m->file_name }}" style="cursor: pointer;">
                                                        <i class="bi {{ $pillIcon }}" aria-hidden="true"></i>
                                                        <span class="text-truncate" style="max-width: 150px;">{{ $m->file_name }}</span>
                                                        <i class="bi {{ $modalTarget ? 'bi-box-arrow-up-right' : 'bi-download' }} text-primary ms-1" aria-hidden="true"></i>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Forwarded Approved Caption Accordion -->
                                        <div class="accordion mb-3 shadow-sm rounded-4 border overflow-hidden" id="approvedCaptionAccordion_{{ $item->id }}">
                                            <div class="accordion-item border-0 bg-success bg-opacity-10">
                                                <h3 class="accordion-header" id="headingApprovedCaption_{{ $item->id }}">
                                                    <button class="accordion-button collapsed py-2 px-3 bg-success bg-opacity-10 text-success fw-bold text-xs shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseApprovedCaption_{{ $item->id }}" aria-expanded="false" aria-controls="collapseApprovedCaption_{{ $item->id }}">
                                                        <i class="bi bi-broadcast me-2" aria-hidden="true"></i>Approved Social Media Broadcast Caption Brief
                                                    </button>
                                                </h3>
                                                <div id="collapseApprovedCaption_{{ $item->id }}" class="accordion-collapse collapse" aria-labelledby="headingApprovedCaption_{{ $item->id }}" data-bs-parent="#approvedCaptionAccordion_{{ $item->id }}">
                                                    <div class="accordion-body p-3 pt-2 bg-white border-top">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="text-xxs text-muted"><i class="bi bi-check-circle-fill text-success me-1" aria-hidden="true"></i>Registrar Authorized Broadcast Phrasing</span>
                                                            <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 rounded text-xxs fw-bold" onclick="navigator.clipboard.writeText(document.getElementById('approved_caption_preview_{{ $item->id }}').innerText); this.innerHTML='Copied!'; setTimeout(()=>this.innerHTML='Copy Brief',1500);" aria-label="Copy approved caption to clipboard">Copy Brief</button>
                                                        </div>
                                                        <div id="approved_caption_preview_{{ $item->id }}" class="text-xxs text-dark font-monospace bg-light p-2 rounded border" style="white-space: pre-line; max-height: 180px; overflow-y: auto;">{{ $item->social_media_caption ?: \App\Http\Controllers\ActivitySubmissionController::generateDefaultCaptionForSubmission($item) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top">
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="{{ route('submissions.show', $item->id) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm px-3 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-1">
                                                <i class="bi bi-eye-fill" aria-hidden="true"></i> View Activity
                                            </a>
                                            <a href="{{ route('media.downloadPack', $item->id) }}" class="btn btn-outline-dark btn-sm px-3 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-1">
                                                <i class="bi bi-file-earmark-zip-fill text-warning fs-6" aria-hidden="true"></i> Download Pack (ZIP)
                                            </a>
                                        </div>

                                        <button type="button" class="btn btn-success btn-sm fw-bold px-4 py-2 rounded-pill shadow d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#publishModal" data-bs-id="{{ $item->id }}" data-bs-title="{{ $item->title }}" data-bs-caption="{{ $item->social_media_caption }}" aria-label="Publish or Schedule Broadcast for {{ $item->title }}">
                                            <i class="bi bi-broadcast" aria-hidden="true"></i> Publish / Schedule
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

@elseif($activeTab === 'published')
    <!-- PUBLISHED SUB-MENU: RECENTLY PUBLISHED ARCHIVE -->
    <section aria-label="Published Broadcasts Archive" class="portal-card p-4 bg-white border shadow-sm rounded-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3 flex-wrap gap-2">
            <h2 class="h4 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-check2-all text-success fs-3" aria-hidden="true"></i> Published Broadcasts Archive
                <span class="badge bg-success text-white rounded-pill px-3">{{ $recentlyPublished->count() }}</span>
            </h2>
            <a href="{{ route('media.exportQueue', ['stage' => 'published']) }}" class="btn btn-outline-dark fw-bold rounded-pill px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-download" aria-hidden="true"></i> Export Archive CSV
            </a>
        </div>

        <div class="d-flex flex-column gap-3">
            @forelse($recentlyPublished as $pub)
            <div class="activity-card d-flex align-items-center justify-content-between p-4 rounded-4 bg-white shadow-sm flex-wrap gap-3 border">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-dark text-white font-monospace px-3 py-1 rounded-pill">{{ $pub->reference_id }}</span>
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-3 py-1"><i class="bi bi-broadcast-pin me-1" aria-hidden="true"></i>Live Broadcast</span>
                    </div>
                    <h3 class="h6 fw-bold text-dark mb-1">{{ $pub->title }}</h3>
                    <span class="text-xs text-muted"><i class="bi bi-building me-1" aria-hidden="true"></i>{{ $pub->college ? $pub->college->name : 'N/A' }} &bull; Published on {{ $pub->updated_at->format('d M, Y h:i A') }}</span>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('media.downloadPack', $pub->id) }}" class="btn btn-outline-dark btn-sm px-4 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-1">
                        <i class="bi bi-file-earmark-zip-fill text-warning" aria-hidden="true"></i> Download Pack
                    </a>
                    <a href="{{ route('submissions.show', $pub->id) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm px-4 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-1 flex-shrink-0">
                        <i class="bi bi-eye-fill" aria-hidden="true"></i> View Activity
                    </a>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted small">
                <i class="bi bi-broadcast fs-1 d-block mb-2 opacity-30" aria-hidden="true"></i>
                No published broadcasts found in archive.
            </div>
            @endforelse
        </div>
    </section>
@endif

<!-- Modal for Option A: Direct Approve -->
<div class="modal fade" id="directApproveModal" tabindex="-1" aria-labelledby="directApproveModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form id="directApproveForm" method="POST" action="">
                @csrf
                <div class="modal-header bg-success text-white p-4">
                    <h2 class="modal-title h5 fw-bold d-flex align-items-center gap-2" id="directApproveModalTitle"><i class="bi bi-rocket-takeoff-fill fs-4" aria-hidden="true"></i> Media Package Reviewed & Published</h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-success d-flex align-items-center mb-4">
                        <i class="bi bi-rocket-takeoff fs-3 me-3"></i>
                        <div>
                            <h4 class="alert-heading h6 fw-bold mb-1" id="directApproveActivityTitle"></h4>
                            <p class="mb-0 small">You are about to publish this activity without any corrections. It will bypass the Office of the Registrar and be <strong>Published Live immediately</strong>.</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="h6 fw-bold text-dark border-bottom pb-2 mb-3">Social Media Publication Details</h5>
                        <p class="text-muted small mb-3">The Media user must record the social media platforms where the activity was published.</p>

                        <label class="form-label fw-bold small text-dark text-uppercase tracking-wider">Target Broadcast Platforms <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded-4 border mb-3">
                            <div class="form-check">
                                <input class="form-check-input da-platform-checkbox" type="checkbox" name="platforms[]" value="Instagram" id="daPlatInsta">
                                <label class="form-check-label fw-bold text-danger" for="daPlatInsta"><i class="bi bi-instagram me-1" aria-hidden="true"></i> Instagram</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input da-platform-checkbox" type="checkbox" name="platforms[]" value="Facebook" id="daPlatFb">
                                <label class="form-check-label fw-bold text-primary" for="daPlatFb"><i class="bi bi-facebook me-1" aria-hidden="true"></i> Facebook</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input da-platform-checkbox" type="checkbox" name="platforms[]" value="LinkedIn" id="daPlatLinked">
                                <label class="form-check-label fw-bold" style="color: #0077b5;" for="daPlatLinked"><i class="bi bi-linkedin me-1" aria-hidden="true"></i> LinkedIn</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input da-platform-checkbox" type="checkbox" name="platforms[]" value="X (Twitter)" id="daPlatX">
                                <label class="form-check-label fw-bold text-dark" for="daPlatX"><i class="bi bi-twitter-x me-1" aria-hidden="true"></i> X (Twitter)</label>
                            </div>
                        </div>
                    </div>

                    <div id="daUrlFieldsContainer">
                        <div class="mb-3 da-url-field-group d-none" id="daGroupInsta">
                            <label class="form-label fw-bold small text-dark" for="daUrlInsta">Post Link – Instagram <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-danger"><i class="bi bi-instagram"></i></span>
                                <input type="url" name="post_url_instagram" id="daUrlInsta" class="form-control" placeholder="https://instagram.com/...">
                            </div>
                        </div>
                        <div class="mb-3 da-url-field-group d-none" id="daGroupFb">
                            <label class="form-label fw-bold small text-dark" for="daUrlFb">Post Link – Facebook <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-primary"><i class="bi bi-facebook"></i></span>
                                <input type="url" name="post_url_facebook" id="daUrlFb" class="form-control" placeholder="https://facebook.com/...">
                            </div>
                        </div>
                        <div class="mb-3 da-url-field-group d-none" id="daGroupLinked">
                            <label class="form-label fw-bold small text-dark" for="daUrlLinked">Post Link – LinkedIn <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light" style="color: #0077b5;"><i class="bi bi-linkedin"></i></span>
                                <input type="url" name="post_url_linkedin" id="daUrlLinked" class="form-control" placeholder="https://linkedin.com/...">
                            </div>
                        </div>
                        <div class="mb-3 da-url-field-group d-none" id="daGroupX">
                            <label class="form-label fw-bold small text-dark" for="daUrlX">Post Link – X (Twitter) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-dark"><i class="bi bi-twitter-x"></i></span>
                                <input type="url" name="post_url_twitter" id="daUrlX" class="form-control" placeholder="https://x.com/...">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 mt-4">
                        <label class="form-label fw-bold small text-dark text-uppercase tracking-wider" for="daPublishRemarks">Remarks (Optional)</label>
                        <textarea name="remarks" id="daPublishRemarks" rows="3" class="form-control" placeholder="Any final remarks about the broadcast..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light border px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-5 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2">
                        <i class="bi bi-broadcast-pin" aria-hidden="true"></i> Save Publication Details
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('partials.media-action-modals')

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
                <button type="button" class="btn btn-danger btn-sm rounded-pill px-4 py-1.5 fw-bold d-flex align-items-center gap-1 shadow" data-bs-dismiss="modal">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    document.addEventListener("DOMContentLoaded", function() {
        // Animated Stat Counters
        const counters = document.querySelectorAll('.counter-value');
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            if (target > 0) {
                let count = 0;
                const speed = Math.max(10, Math.floor(1000 / target));
                const updateCount = () => {
                    const inc = Math.max(1, Math.ceil(target / 30));
                    if (count < target) {
                        count += inc;
                        if (count > target) count = target;
                        counter.innerText = count.toLocaleString();
                        setTimeout(updateCount, speed);
                    } else {
                        counter.innerText = target.toLocaleString();
                    }
                };
                updateCount();
            }
        });

        // --- Chart 1: Media Studio Pipeline Distribution ---
        const msPipeCtx = document.getElementById('msPipelineChart');
        if (msPipeCtx) {
            new Chart(msPipeCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($mediaStageLabels ?? ['Pending Action', 'PA to Registrar', 'Ready to Publish', 'Scheduled', 'Published Live']) !!},
                    datasets: [{
                        label: 'Packages in Stage',
                        data: {!! json_encode($mediaStageCounts ?? [1, 1, 1, 1, 1]) !!},
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.85)',
                            'rgba(245, 158, 11, 0.85)',
                            'rgba(16, 185, 129, 0.85)',
                            'rgba(139, 92, 246, 0.85)',
                            'rgba(6, 182, 212, 0.85)'
                        ],
                        borderRadius: 8,
                        borderWidth: 1,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: '#0f172a', padding: 12, cornerRadius: 8 }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { weight: '600', size: 11 } } },
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } }
                    }
                }
            });
        }

        // --- Chart 2: Social Media Platform Broadcast Distribution ---
        const msPlatCtx = document.getElementById('msPlatformChart');
        if (msPlatCtx) {
            new Chart(msPlatCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($platformLabels ?? ['Instagram', 'LinkedIn', 'Facebook', 'Twitter / X']) !!},
                    datasets: [{
                        data: {!! json_encode($platformCounts ?? [1, 1, 1, 1]) !!},
                        backgroundColor: ['#e1306c', '#0077b5', '#1877f2', '#14171a', '#ff0000'],
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { size: 11, weight: '600' }, boxWidth: 12, padding: 12, usePointStyle: true } },
                        tooltip: { backgroundColor: '#0f172a', padding: 12, cornerRadius: 8 }
                    }
                }
            });
        }

        // --- Chart 3: 6-Month Broadcast Output Trajectory ---
        const msOutCtx = document.getElementById('msOutputChart');
        if (msOutCtx) {
            const ctxM = msOutCtx.getContext('2d');
            const gradientM = ctxM.createLinearGradient(0, 0, 0, 300);
            gradientM.addColorStop(0, 'rgba(139, 92, 246, 0.45)');
            gradientM.addColorStop(1, 'rgba(139, 92, 246, 0.01)');

            new Chart(msOutCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($mediaMonthlyLabels ?? ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul']) !!},
                    datasets: [{
                        label: 'Finalized & Broadcast Packages',
                        data: {!! json_encode($mediaMonthlyCounts ?? [0, 0, 0, 0, 0, 0]) !!},
                        borderColor: '#8b5cf6',
                        borderWidth: 3,
                        backgroundColor: gradientM,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#8b5cf6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: '#0f172a', padding: 12, cornerRadius: 8 }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { weight: '600', size: 11 } } },
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } }
                    }
                }
            });
        }

        // Direct Approve Modal Handler (Option A)
        const directApproveModalEl = document.getElementById('directApproveModal');
        if (directApproveModalEl) {
            directApproveModalEl.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-bs-id');
                const title = button.getAttribute('data-bs-title') || '';

                const form = document.getElementById('directApproveForm');
                form.action = `/media-editor/${id}/direct-approve`;
                document.getElementById('directApproveActivityTitle').textContent = title;

                // Reset form state on open
                form.reset();
                document.querySelectorAll('.da-url-field-group').forEach(el => el.classList.add('d-none'));
                document.querySelectorAll('.da-url-field-group input').forEach(el => el.required = false);
            });

            // Handle dynamic URL fields
            const daPlatformMap = {
                'Instagram': { group: 'daGroupInsta', input: 'daUrlInsta' },
                'Facebook': { group: 'daGroupFb', input: 'daUrlFb' },
                'LinkedIn': { group: 'daGroupLinked', input: 'daUrlLinked' },
                'X (Twitter)': { group: 'daGroupX', input: 'daUrlX' }
            };

            document.querySelectorAll('.da-platform-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const mapping = daPlatformMap[this.value];
                    if (mapping) {
                        const groupEl = document.getElementById(mapping.group);
                        const inputEl = document.getElementById(mapping.input);
                        
                        if (this.checked) {
                            groupEl.classList.remove('d-none');
                            inputEl.required = true;
                        } else {
                            groupEl.classList.add('d-none');
                            inputEl.required = false;
                        }
                    }
                });
            });
        }
        @include('partials.media-action-scripts')
    });
</script>
@include('partials.client-image-optimizer')
@endpush
