@extends('layouts.app')

@section('title', 'Institutional Analytics Dashboard - VMRFDU Portal')

@section('content')
<style>
    /* Premium Dashboard Styles */
    .dashboard-header-card {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
        color: #ffffff;
        border-radius: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.4);
    }
    .dashboard-header-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 350px;
        height: 350px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, rgba(99, 102, 241, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .dashboard-header-card::after {
        content: '';
        position: absolute;
        bottom: -40%;
        left: 20%;
        width: 280px;
        height: 280px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .stat-card-modern {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
    }
    .stat-card-modern:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.03);
        border-color: #cbd5e1;
    }
    .stat-icon-wrapper {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        transition: transform 0.3s ease;
    }
    .stat-card-modern:hover .stat-icon-wrapper {
        transform: scale(1.1) rotate(-5deg);
    }
    .stat-icon-blue { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
    .stat-icon-amber { background: rgba(245, 158, 11, 0.12); color: #d97706; }
    .stat-icon-cyan { background: rgba(6, 182, 212, 0.12); color: #0891b2; }
    .stat-icon-emerald { background: rgba(16, 185, 129, 0.12); color: #059669; }

    .stat-card-accent-blue { border-top: 4px solid #3b82f6; }
    .stat-card-accent-amber { border-top: 4px solid #f59e0b; }
    .stat-card-accent-cyan { border-top: 4px solid #06b6d4; }
    .stat-card-accent-emerald { border-top: 4px solid #10b981; }

    .chart-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        padding: 1.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
        display: flex;
        flex-direction: column;
    }
    .chart-container {
        position: relative;
        flex-grow: 1;
        min-height: 310px;
    }
    .table-portal-modern tbody tr {
        transition: background-color 0.15s ease;
    }
    .table-portal-modern tbody tr:hover {
        background-color: #f8fafc;
    }
    .status-badge-modern {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 50rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>

<!-- Executive Welcome Banner -->
<div class="dashboard-header-card p-4 p-md-5 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-4">
    <div class="d-flex align-items-center gap-4 position-relative z-1">
        <div class="bg-white bg-opacity-10 p-3 rounded-4 border border-white border-opacity-20 d-flex align-items-center justify-content-center text-white shadow-inner" style="width: 68px; height: 68px;">
            <i class="bi bi-grid-1x2-fill fs-2 text-info"></i>
        </div>
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 px-3 py-1 rounded-pill text-xxs font-monospace">
                    <i class="bi bi-shield-check me-1"></i> VMRFDU PORTAL
                </span>
                <span class="text-white-50 small">|</span>
                <span class="text-white-50 text-xs"><i class="bi bi-calendar3 me-1"></i> {{ \Carbon\Carbon::now()->format('l, d M Y') }}</span>
            </div>
            <h2 class="fw-bold mb-1 text-white tracking-tight">Welcome back, {{ $currentUser ? $currentUser->name : 'Administrator' }} 👋</h2>
            <p class="text-white-50 mb-0 small">
                @if($currentUser && $currentUser->college)
                    <span class="text-info fw-semibold"><i class="bi bi-building me-1"></i> {{ $currentUser->college->name }}</span>
                @endif
            </p>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap position-relative z-1">
        @if(!$currentUser || !$currentUser->hasRole(['media_editor']))
        <a href="{{ route('submissions.create') }}" class="btn btn-primary px-4 py-2 rounded-pill fw-bold d-flex align-items-center gap-2 shadow-lg" style="background: linear-gradient(135deg, #3b82f6, #2563eb); border: none;">
            <i class="bi bi-plus-circle-fill fs-5"></i> Submit New Activity
        </a>
        @endif
        <a href="{{ route('analytics.index') }}" class="btn btn-outline-light px-4 py-2 rounded-pill fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-graph-up-arrow"></i> Full Analytics Suite
        </a>
    </div>
</div>

<!-- Role Specific Action Banners -->
@if($currentUser && $currentUser->hasRole(['registrar', 'office_registrar', 'vc', 'pro_vc']))
<div class="portal-card p-4 mb-4 rounded-4 border-0 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 6px solid #f59e0b !important;">
    <div class="d-flex align-items-center gap-3">
        <div class="bg-warning text-dark rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
            <i class="bi bi-shield-lock-fill fs-3"></i>
        </div>
        <div>
            @if($currentUser->role === 'office_registrar')
                <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                    Office of the Registrar Action Required
                    <span class="badge bg-danger text-white rounded-pill px-2 py-1 text-xxs">{{ $pendingApprovals }} Pending</span>
                </h5>
                <p class="text-dark text-opacity-75 mb-0 small">Edited media packages and AI captions submitted by the Media Studio are waiting for your final verification before broadcasting.</p>
            @else
                <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                    Authority Review Queue
                    <span class="badge bg-danger text-white rounded-pill px-2 py-1 text-xxs">{{ $pendingApprovals }} Pending</span>
                </h5>
                <p class="text-dark text-opacity-75 mb-0 small">As **{{ $currentUser->role_title }}**, constituent college activities are pending your Stage 1 verification before media studio processing.</p>
            @endif
        </div>
    </div>
    <a href="{{ route('approvals.index') }}" class="btn btn-dark fw-bold px-4 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2">
        <span>Open Review Desk</span> <i class="bi bi-arrow-right-circle-fill text-warning"></i>
    </a>
</div>
@elseif($currentUser && $currentUser->hasRole('media_editor'))
<div class="portal-card p-4 mb-4 rounded-4 border-0 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-left: 6px solid #3b82f6 !important;">
    <div class="d-flex align-items-center gap-3">
        <div class="bg-primary text-white rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
            <i class="bi bi-palette-fill fs-3"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                Media Studio Desk Queue
                <span class="badge bg-primary text-white rounded-pill px-2 py-1 text-xxs">{{ $mediaReady }} Ready</span>
            </h5>
            <p class="text-dark text-opacity-75 mb-0 small">Verified institutional activities are ready for photo cropping, banner refinement, and social media scheduling.</p>
        </div>
    </div>
    <a href="{{ route('media.dashboard') }}" class="btn btn-primary fw-bold px-4 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2">
        <span>Launch Media Desk</span> <i class="bi bi-arrow-right-circle-fill"></i>
    </a>
</div>
@endif

<!-- High Impact Metrics Row (All Cards Clickable with Stretched Links) -->
<div class="row g-4 mb-4">
    <!-- Stat 1: Total Activities -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card-modern stat-card-accent-blue h-100 d-flex flex-column justify-content-between position-relative">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="text-xs fw-bold text-muted text-uppercase tracking-wider">Total Activities</div>
                    <div class="fs-2 fw-bold text-dark tracking-tight mt-1 counter-value" data-target="{{ $totalSubmissions }}">{{ $totalSubmissions }}</div>
                </div>
                <div class="stat-icon-wrapper stat-icon-blue">
                    <i class="bi bi-folder2-open"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between text-xs text-muted border-top pt-3 mt-2">
                <span class="d-flex align-items-center"><i class="bi bi-building text-primary me-1"></i> Scope: {{ $currentUser && $currentUser->college_id ? $currentUser->college->code : 'University' }}</span>
                @if($currentUser && in_array($currentUser->role, ['office_registrar', 'registrar', 'vc', 'pro_vc']))
                    <a href="{{ route('approvals.index') }}" class="fw-bold text-primary text-decoration-none stretched-link">Open Queue <i class="bi bi-chevron-right text-xxs"></i></a>
                @elseif($currentUser && $currentUser->role === 'media_editor')
                    <a href="{{ route('media.dashboard') }}" class="fw-bold text-primary text-decoration-none stretched-link">Open Studio <i class="bi bi-chevron-right text-xxs"></i></a>
                @else
                    <a href="{{ route('submissions.index') }}" class="fw-bold text-primary text-decoration-none stretched-link">Open Registry <i class="bi bi-chevron-right text-xxs"></i></a>
                @endif
            </div>
        </div>
    </div>

    <!-- Stat 2: Verification Queue -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card-modern stat-card-accent-amber h-100 d-flex flex-column justify-content-between position-relative">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="text-xs fw-bold text-muted text-uppercase tracking-wider">
                        @if($currentUser && $currentUser->role === 'office_registrar') Pending Registrar Review
                        @else Verification Queue @endif
                    </div>
                    <div class="fs-2 fw-bold text-dark tracking-tight mt-1 counter-value" data-target="{{ $pendingApprovals }}">{{ $pendingApprovals }}</div>
                </div>
                <div class="stat-icon-wrapper stat-icon-amber">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between text-xs text-muted border-top pt-3 mt-2">
                <span class="d-flex align-items-center"><i class="bi bi-shield-exclamation text-warning me-1"></i> Status</span>
                @if($currentUser && $currentUser->hasRole(['registrar', 'office_registrar', 'vc', 'pro_vc', 'super_admin']))
                    <a href="{{ route('approvals.index') }}" class="fw-bold text-warning text-decoration-none stretched-link">Review Queue <i class="bi bi-chevron-right text-xxs"></i></a>
                @else
                    <a href="{{ route('submissions.index') }}?status=verification_pending" class="fw-bold text-warning text-decoration-none stretched-link">View Pending <i class="bi bi-chevron-right text-xxs"></i></a>
                @endif
            </div>
        </div>
    </div>

    <!-- Stat 3: Media Studio Desk -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card-modern stat-card-accent-cyan h-100 d-flex flex-column justify-content-between position-relative">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="text-xs fw-bold text-muted text-uppercase tracking-wider">
                        @if($currentUser && $currentUser->role === 'office_registrar') Approved & Media Ready
                        @else Media Studio Desk @endif
                    </div>
                    <div class="fs-2 fw-bold text-dark tracking-tight mt-1 counter-value" data-target="{{ $mediaReady }}">{{ $mediaReady }}</div>
                </div>
                <div class="stat-icon-wrapper stat-icon-cyan">
                    <i class="bi bi-share-fill"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between text-xs text-muted border-top pt-3 mt-2">
                <span class="d-flex align-items-center"><i class="bi bi-broadcast text-info me-1"></i> Stage</span>
                @if($currentUser && $currentUser->hasRole(['media_editor', 'super_admin']))
                    <a href="{{ route('media.dashboard') }}" class="fw-bold text-info text-decoration-none stretched-link">Open Studio <i class="bi bi-chevron-right text-xxs"></i></a>
                @else
                    <a href="{{ route('submissions.index') }}?status=ready_to_publish" class="fw-bold text-info text-decoration-none stretched-link">View Media <i class="bi bi-chevron-right text-xxs"></i></a>
                @endif
            </div>
        </div>
    </div>

    <!-- Stat 4: Published Live -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card-modern stat-card-accent-emerald h-100 d-flex flex-column justify-content-between position-relative">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="text-xs fw-bold text-muted text-uppercase tracking-wider">Published Live</div>
                    <div class="fs-2 fw-bold text-dark tracking-tight mt-1 counter-value" data-target="{{ $publishedCount }}">{{ $publishedCount }}</div>
                </div>
                <div class="stat-icon-wrapper stat-icon-emerald">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between text-xs text-muted border-top pt-3 mt-2">
                <span class="d-flex align-items-center"><i class="bi bi-globe text-success me-1"></i> Broadcast</span>
                @if($currentUser && $currentUser->hasRole(['media_editor', 'super_admin']))
                    <a href="{{ route('media.approved') }}" class="fw-bold text-success text-decoration-none stretched-link">Live Archive <i class="bi bi-chevron-right text-xxs"></i></a>
                @else
                    <a href="{{ route('submissions.index') }}?status=published" class="fw-bold text-success text-decoration-none stretched-link">View Published <i class="bi bi-chevron-right text-xxs"></i></a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Comprehensive Modern Chart Suite Grid (All-to-All Analytics) -->
<div class="row g-4 mb-4">
    <!-- Chart 1: Activity Volume Across Constituent Institutions -->
    <div class="col-12 col-xl-8">
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-fill text-primary fs-4"></i> Institutional Activity Volume Distribution
                    </h5>
                    <p class="text-muted small mb-0">Comparative breakdown of activities across VMRFDU constituent colleges and campuses</p>
                </div>
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill font-monospace text-xs">Top Active Campuses</span>
            </div>
            <div class="chart-container">
                <canvas id="collegeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 2: Workflow Status Breakdown (Doughnut Chart) -->
    <div class="col-12 col-xl-4">
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-info fs-4"></i> Workflow Statuses
                    </h5>
                    <p class="text-muted small mb-0">Live stage distribution</p>
                </div>
                <span class="badge bg-info bg-opacity-10 text-info px-2 py-1 rounded text-xxs fw-bold">Live Status</span>
            </div>
            <div class="chart-container d-flex align-items-center justify-content-center" style="min-height: 310px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 3: 6-Month Submission Growth Trend -->
    <div class="col-12 col-xl-6">
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-graph-up text-success fs-4"></i> 6-Month Activity Growth Trend
                    </h5>
                    <p class="text-muted small mb-0">Monthly progression of institutional activity submissions over time</p>
                </div>
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill text-xs fw-bold">Trend Line</span>
            </div>
            <div class="chart-container" style="min-height: 280px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 4: Event Category Spread -->
    <div class="col-12 col-xl-6">
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-tags-fill text-warning fs-4"></i> Activity Category Breakdown
                    </h5>
                    <p class="text-muted small mb-0">Classification of submitted academic, research, and cultural events</p>
                </div>
                <span class="badge bg-warning bg-opacity-10 text-dark px-3 py-1 rounded-pill text-xs fw-bold">Categories</span>
            </div>
            <div class="chart-container" style="min-height: 280px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Submissions Modern Table -->
<div class="chart-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-3">
        <div>
            @if($currentUser && $currentUser->role === 'college_admin')
                <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2"><i class="bi bi-clock-history text-primary fs-4"></i> Recent College Activities</h5>
                <p class="text-muted small mb-0">Latest institutional activities recorded within {{ $currentUser->college ? $currentUser->college->code : 'your college' }}</p>
            @else
                <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2"><i class="bi bi-clock-history text-primary fs-4"></i> Recent Activity Directory Feed</h5>
                <p class="text-muted small mb-0">Real-time live feed of recent activity submissions recorded across VMRFDU campuses</p>
            @endif
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="input-group input-group-sm rounded-pill border overflow-hidden shadow-sm" style="max-width: 260px;">
                <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="dashboardTableSearch" class="form-control border-0 pe-3" placeholder="Filter activities...">
            </div>
            <a href="{{ route('submissions.index') }}" class="btn btn-sm btn-dark px-4 py-2 rounded-pill fw-bold text-nowrap shadow-sm d-flex align-items-center gap-1">
                <span>View All Records</span> <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-portal-modern align-middle small mb-0" id="recentActivitiesTable">
            <thead class="bg-light">
                <tr>
                    <th class="py-3 ps-3 text-uppercase text-xs fw-bold text-muted tracking-wider">Ref ID</th>
                    <th class="py-3 text-uppercase text-xs fw-bold text-muted tracking-wider">Activity Title & Category</th>
                    <th class="py-3 text-uppercase text-xs fw-bold text-muted tracking-wider">Constituent Campus</th>
                    <th class="py-3 text-uppercase text-xs fw-bold text-muted tracking-wider">Submission Date</th>
                    <th class="py-3 text-uppercase text-xs fw-bold text-muted tracking-wider">Current Status</th>
                    <th class="py-3 text-uppercase text-xs fw-bold text-muted tracking-wider">Workflow Stage</th>
                    <th class="py-3 pe-3 text-end text-uppercase text-xs fw-bold text-muted tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentSubmissions as $sub)
                <tr>
                    <td class="ps-3 fw-bold text-primary font-monospace">{{ $sub->reference_id }}</td>
                    <td>
                        <div class="fw-bold text-truncate mb-1" style="max-width: 280px;">
                            <a href="{{ route('submissions.show', $sub->id) }}" class="text-decoration-none text-dark hover-primary" title="{{ $sub->title }}">{{ $sub->title }}</a>
                        </div>
                        <span class="badge bg-light text-secondary border text-xxs">{{ $sub->event_category ?? 'General Activity' }}</span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark d-flex align-items-center gap-1">
                            <i class="bi bi-building text-primary text-xs"></i> {{ $sub->college ? $sub->college->code : 'N/A' }}
                        </div>
                        <div class="text-muted text-xs text-truncate" style="max-width: 200px;">{{ $sub->college ? $sub->college->name : '' }}</div>
                    </td>
                    <td class="fw-semibold text-muted">{{ $sub->activity_date ? $sub->activity_date->format('d M, Y') : 'N/A' }}</td>
                    <td>{!! $sub->status_badge !!}</td>
                    <td>
                        <span class="badge bg-light text-dark border px-2 py-1 text-xxs fw-semibold">{{ $sub->current_stage }}</span>
                    </td>
                    <td class="pe-3 text-end">
                        <a href="{{ route('submissions.show', $sub->id) }}" class="btn btn-sm btn-light border px-3 py-1 rounded-pill shadow-sm fw-bold d-inline-flex align-items-center gap-1 hover-primary">
                            <i class="bi bi-eye-fill text-primary"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <div class="py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                            <h6 class="fw-bold text-dark">No Recent Activities Found</h6>
                            <p class="text-muted small mb-0">When new institutional activities are submitted, they will appear right here in real-time.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Animated Counters
        const counters = document.querySelectorAll('.counter-value');
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            if (target > 0) {
                let count = 0;
                const speed = Math.max(10, Math.floor(1000 / target));
                const updateCount = () => {
                    const inc = Math.max(1, Math.ceil(target / 40));
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

        // Live Table Search Filter
        const searchInput = document.getElementById('dashboardTableSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll('#recentActivitiesTable tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        }

        // --- Chart 1: Institutional Activity Volume (Bar Chart) ---
        const collegeCtx = document.getElementById('collegeChart');
        if (collegeCtx) {
            const ctx1 = collegeCtx.getContext('2d');
            const gradientBar = ctx1.createLinearGradient(0, 0, 0, 320);
            gradientBar.addColorStop(0, 'rgba(59, 130, 246, 0.9)');
            gradientBar.addColorStop(1, 'rgba(37, 99, 235, 0.2)');

            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($collegeNames ?? []) !!},
                    datasets: [{
                        label: 'Activities Submitted',
                        data: {!! json_encode($collegeCounts ?? []) !!},
                        backgroundColor: gradientBar,
                        borderColor: '#2563eb',
                        borderWidth: 1.5,
                        borderRadius: 8,
                        hoverBackgroundColor: '#1d4ed8'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { weight: '600', size: 11 } } },
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } }
                    }
                }
            });
        }

        // --- Chart 2: Workflow Status Breakdown (Doughnut Chart) ---
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($statusLabels ?? ['Stage 1 Review', 'Media Desk', 'PA to Registrar', 'Ready/Scheduled', 'Published Live', 'Returned']) !!},
                    datasets: [{
                        data: {!! json_encode($statusData ?? [1, 1, 1, 1, 1, 0]) !!},
                        backgroundColor: [
                            '#f59e0b', // Stage 1 Review (Amber)
                            '#3b82f6', // Media Desk (Blue)
                            '#8b5cf6', // PA to Registrar (Purple)
                            '#06b6d4', // Ready/Scheduled (Cyan)
                            '#10b981', // Published Live (Emerald)
                            '#ef4444'  // Returned (Red)
                        ],
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 11, weight: '600' },
                                boxWidth: 12,
                                padding: 12,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            cornerRadius: 8
                        }
                    }
                }
            });
        }

        // --- Chart 3: 6-Month Activity Growth Trend (Line/Area Chart) ---
        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            const ctx3 = trendCtx.getContext('2d');
            const gradientArea = ctx3.createLinearGradient(0, 0, 0, 280);
            gradientArea.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
            gradientArea.addColorStop(1, 'rgba(16, 185, 129, 0.01)');

            new Chart(ctx3, {
                type: 'line',
                data: {
                    labels: {!! json_encode($monthlyLabels ?? ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul']) !!},
                    datasets: [{
                        label: 'Monthly Submissions',
                        data: {!! json_encode($monthlyData ?? [0, 0, 0, 0, 0, 0]) !!},
                        borderColor: '#10b981',
                        borderWidth: 3,
                        backgroundColor: gradientArea,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10b981',
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

        // --- Chart 4: Activity Category Spread (Bar/Polar Chart) ---
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx) {
            new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($categoryLabels ?? ['Academic', 'Research', 'Cultural', 'Outreach']) !!},
                    datasets: [{
                        label: 'Event Count',
                        data: {!! json_encode($categoryData ?? [1, 1, 1, 1]) !!},
                        backgroundColor: [
                            'rgba(99, 102, 241, 0.85)',
                            'rgba(245, 158, 11, 0.85)',
                            'rgba(14, 165, 233, 0.85)',
                            'rgba(236, 72, 153, 0.85)',
                            'rgba(139, 92, 246, 0.85)'
                        ],
                        borderRadius: 6,
                        borderWidth: 0
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: '#0f172a', padding: 12, cornerRadius: 8 }
                    },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        y: { grid: { display: false }, ticks: { font: { weight: '600', size: 11 } } }
                    }
                }
            });
        }
    });
</script>
@endpush
