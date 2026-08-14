@extends('layouts.app')

@section('title', 'Super Admin Enterprise Control Panel - VMRFDU')

@section('content')
<style>
    /* Super Admin Accessible & Modern System Styles */
    .sa-header-banner {
        background: linear-gradient(135deg, #090d16 0%, #172033 60%, #24314c 100%);
        color: #ffffff;
        border-radius: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px -10px rgba(9, 13, 22, 0.5);
    }
    .sa-header-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 380px;
        height: 380px;
        background: radial-gradient(circle, rgba(245, 158, 11, 0.22) 0%, rgba(245, 158, 11, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .sa-header-banner::after {
        content: '';
        position: absolute;
        bottom: -40%;
        left: 20%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.18) 0%, rgba(59, 130, 246, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .sa-stat-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
    }
    .sa-stat-card:hover, .sa-stat-card:focus-within {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }
    .sa-icon-circle {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        transition: transform 0.3s ease;
    }
    .sa-stat-card:hover .sa-icon-circle {
        transform: scale(1.1) rotate(-6deg);
    }
    .sa-chart-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        padding: 1.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
        display: flex;
        flex-direction: column;
    }
    .sa-chart-container {
        position: relative;
        flex-grow: 1;
        min-height: 320px;
    }
    .sa-nav-pill {
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: 50rem;
        font-weight: 600;
        font-size: 0.825rem;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .sa-nav-pill:hover, .sa-nav-pill:focus {
        background: #ffffff;
        color: #0f172a;
        box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
        outline: 2px solid #3b82f6;
    }
    .sa-table tbody tr {
        transition: background-color 0.15s ease;
    }
    .sa-table tbody tr:hover {
        background-color: #f8fafc;
    }
    /* Accessible Focus Styles */
    a:focus-visible, button:focus-visible, input:focus-visible {
        outline: 3px solid #3b82f6 !important;
        outline-offset: 2px !important;
    }
</style>

<!-- Executive Control Banner -->
<div class="sa-header-banner p-4 p-md-5 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-4" role="region" aria-label="Super Admin Header and Navigation">
    <div class="d-flex align-items-center gap-4 position-relative z-1">
        <div class="bg-warning bg-opacity-20 p-3 rounded-4 border border-warning border-opacity-30 d-flex align-items-center justify-content-center text-warning shadow-inner" style="width: 72px; height: 72px;">
            <i class="bi bi-shield-lock-fill fs-1" aria-hidden="true"></i>
        </div>
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill text-xxs font-monospace fw-bold shadow-sm">
                    <i class="bi bi-lightning-charge-fill me-1" aria-hidden="true"></i> SUPER ADMIN AUTHORITY DESK
                </span>
                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-1 rounded-pill text-xxs font-monospace">
                    <i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i> SYSTEM HEALTH: 100% OPERATIONAL
                </span>
                <span class="text-white-50 text-xs ms-2"><i class="bi bi-clock me-1" aria-hidden="true"></i> {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }}</span>
            </div>
            <h1 class="h2 fw-bold mb-1 text-white tracking-tight">Super Admin Enterprise Control Panel</h1>
            <p class="text-white-50 mb-0 small">Centralized multi-campus governance, role-based security management, and university-wide analytics across 28 constituent institutions.</p>
        </div>
    </div>

    <!-- Accessible Navigation Shortcuts -->
    <nav class="d-flex gap-2 flex-wrap position-relative z-1" aria-label="Quick Control Shortcuts">
        <a href="{{ route('superadmin.colleges') }}" class="sa-nav-pill" aria-label="Manage Constituent Colleges">
            <i class="bi bi-building text-info" aria-hidden="true"></i> <span>Colleges ({{ $totalColleges }})</span>
        </a>
        <a href="{{ route('superadmin.users') }}" class="sa-nav-pill" aria-label="Manage Portal Users">
            <i class="bi bi-people text-success" aria-hidden="true"></i> <span>Users ({{ $totalUsers }})</span>
        </a>
        <a href="{{ route('superadmin.workflows') }}" class="sa-nav-pill" aria-label="Configure Workflows">
            <i class="bi bi-diagram-3 text-warning" aria-hidden="true"></i> <span>Workflows</span>
        </a>
        <a href="{{ route('superadmin.roles') }}" class="sa-nav-pill" aria-label="Role Permissions">
            <i class="bi bi-key text-danger" aria-hidden="true"></i> <span>Roles</span>
        </a>
        <a href="{{ route('superadmin.logsHub') }}" class="sa-nav-pill" style="background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4);" aria-label="View System & Email Logs Hub">
            <i class="bi bi-journal-check text-white" aria-hidden="true"></i> <span>Logs Hub</span>
        </a>
        <a href="{{ route('superadmin.emailHub') }}" class="sa-nav-pill" style="background: rgba(79, 70, 229, 0.35); border-color: rgba(79, 70, 229, 0.6);" aria-label="Manage Dynamic Email Templates & Branding">
            <i class="bi bi-envelope-check-fill text-white" aria-hidden="true"></i> <span>Email Hub</span>
        </a>
        <a href="{{ route('superadmin.settings') }}" class="sa-nav-pill" aria-label="System Settings">
            <i class="bi bi-gear-fill text-light" aria-hidden="true"></i> <span>Settings</span>
        </a>
    </nav>
</div>

<!-- Primary System Metrics Row (4 Cards) -->
<section aria-label="Primary System Metrics" class="row g-4 mb-4">
    <!-- Card 1: Constituent Colleges -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="sa-stat-card h-100 d-flex flex-column justify-content-between position-relative" style="border-top: 4px solid #3b82f6;">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="text-xs fw-bold text-muted text-uppercase tracking-wider">Constituent Colleges</span>
                    <div class="fs-2 fw-bold text-dark tracking-tight mt-1 counter-value" data-target="{{ $totalColleges }}" aria-label="Total Colleges: {{ $totalColleges }}">{{ $totalColleges }}</div>
                </div>
                <div class="sa-icon-circle bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-bank2" aria-hidden="true"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between text-xs text-muted border-top pt-3 mt-2">
                <span><i class="bi bi-geo-alt text-primary me-1" aria-hidden="true"></i> Campus Coverage</span>
                <a href="{{ route('superadmin.colleges') }}" class="fw-bold text-primary text-decoration-none stretched-link">View All <i class="bi bi-chevron-right text-xxs" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>

    <!-- Card 2: Total Portal Users -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="sa-stat-card h-100 d-flex flex-column justify-content-between position-relative" style="border-top: 4px solid #10b981;">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="text-xs fw-bold text-muted text-uppercase tracking-wider">Total Portal Users</span>
                    <div class="fs-2 fw-bold text-dark tracking-tight mt-1 counter-value" data-target="{{ $totalUsers }}" aria-label="Total Users: {{ $totalUsers }}">{{ $totalUsers }}</div>
                </div>
                <div class="sa-icon-circle bg-success bg-opacity-10 text-success">
                    <i class="bi bi-people-fill" aria-hidden="true"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between text-xs text-muted border-top pt-3 mt-2">
                <span><i class="bi bi-person-badge text-success me-1" aria-hidden="true"></i> Active Accounts</span>
                <a href="{{ route('superadmin.users') }}" class="fw-bold text-success text-decoration-none stretched-link">User Directory <i class="bi bi-chevron-right text-xxs" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>

    <!-- Card 3: University Activities -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="sa-stat-card h-100 d-flex flex-column justify-content-between position-relative" style="border-top: 4px solid #f59e0b;">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="text-xs fw-bold text-muted text-uppercase tracking-wider">University Activities</span>
                    <div class="fs-2 fw-bold text-dark tracking-tight mt-1 counter-value" data-target="{{ $totalActivities }}" aria-label="Total Activities: {{ $totalActivities }}">{{ $totalActivities }}</div>
                </div>
                <div class="sa-icon-circle bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-folder-check" aria-hidden="true"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between text-xs text-muted border-top pt-3 mt-2">
                <span><i class="bi bi-journal-text text-warning me-1" aria-hidden="true"></i> Repository Total</span>
                <a href="{{ route('superadmin.activities') }}" class="fw-bold text-warning text-decoration-none stretched-link">Master Registry <i class="bi bi-chevron-right text-xxs" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>

    <!-- Card 4: Social Media Live Posts -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="sa-stat-card h-100 d-flex flex-column justify-content-between position-relative" style="border-top: 4px solid #8b5cf6;">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="text-xs fw-bold text-muted text-uppercase tracking-wider">Social Media Live Posts</span>
                    <div class="fs-2 fw-bold text-dark tracking-tight mt-1 counter-value" data-target="{{ $publishedCount }}" aria-label="Live Posts: {{ $publishedCount }}">{{ $publishedCount }}</div>
                </div>
                <div class="sa-icon-circle bg-purple bg-opacity-10" style="color: #8b5cf6;">
                    <i class="bi bi-broadcast" aria-hidden="true"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between text-xs text-muted border-top pt-3 mt-2">
                <span><i class="bi bi-share text-purple me-1" aria-hidden="true"></i> Broadcast Count</span>
                <a href="{{ route('media.approved') }}" class="fw-bold text-decoration-none stretched-link" style="color: #8b5cf6;">Live on Channels <i class="bi bi-chevron-right text-xxs" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- Secondary Workflow Breakdown Row (All Clickable Cards) -->
<section aria-label="Workflow Status Breakdown" class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="portal-card p-3 rounded-4 bg-white border shadow-sm text-center d-flex flex-column justify-content-center position-relative h-100 hover-elevate">
            <span class="text-muted text-xs text-uppercase fw-bold"><i class="bi bi-hourglass-split text-warning me-1" aria-hidden="true"></i> Pending Verification Queue</span>
            <div class="fs-3 fw-bold text-warning mt-1 mb-0 counter-value" data-target="{{ $pendingApprovals }}">{{ $pendingApprovals }}</div>
            <a href="{{ route('approvals.pending') }}" class="stretched-link" aria-label="View Pending Verification Queue"></a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="portal-card p-3 rounded-4 bg-white border shadow-sm text-center d-flex flex-column justify-content-center position-relative h-100 hover-elevate">
            <span class="text-muted text-xs text-uppercase fw-bold"><i class="bi bi-check-circle-fill text-primary me-1" aria-hidden="true"></i> Approved / Media Studio Desk</span>
            <div class="fs-3 fw-bold text-primary mt-1 mb-0 counter-value" data-target="{{ $approvedActivities }}">{{ $approvedActivities }}</div>
            <a href="{{ route('media.dashboard') }}" class="stretched-link" aria-label="View Media Studio Desk"></a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="portal-card p-3 rounded-4 bg-white border shadow-sm text-center d-flex flex-column justify-content-center position-relative h-100 hover-elevate">
            <span class="text-muted text-xs text-uppercase fw-bold"><i class="bi bi-chat-dots-fill text-dark me-1" aria-hidden="true"></i> Under Discussion / Query</span>
            <div class="fs-3 fw-bold text-dark mt-1 mb-0 counter-value" data-target="{{ $discussActivities }}">{{ $discussActivities }}</div>
            <a href="{{ route('superadmin.activities', ['status' => 'editor_discuss']) }}" class="stretched-link" aria-label="View Under Discussion Activities"></a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="portal-card p-3 rounded-4 bg-white border shadow-sm text-center d-flex flex-column justify-content-center position-relative h-100 hover-elevate">
            <span class="text-muted text-xs text-uppercase fw-bold"><i class="bi bi-x-circle-fill text-danger me-1" aria-hidden="true"></i> Returned / Rejected</span>
            <div class="fs-3 fw-bold text-danger mt-1 mb-0 counter-value" data-target="{{ $rejectedActivities }}">{{ $rejectedActivities }}</div>
            <a href="{{ route('approvals.rejected') }}" class="stretched-link" aria-label="View Returned or Rejected Activities"></a>
        </div>
    </div>
</section>

<!-- Comprehensive Interactive Chart Suite Grid (Super Admin Enterprise Analytics) -->
<section aria-label="System Analytics Charts" class="row g-4 mb-4">
    <!-- Chart 1: College Performance Comparison -->
    <div class="col-12 col-xl-8">
        <div class="sa-chart-card">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                <div>
                    <h2 class="h5 fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-fill text-primary fs-4" aria-hidden="true"></i> College Activity Performance Comparison
                    </h2>
                    <p class="text-muted small mb-0">Multi-dimensional comparison of Total vs. Published vs. Pending submissions by campus</p>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill font-monospace text-xs">University-Wide Metrics</span>
            </div>
            <div class="sa-chart-container">
                <canvas id="saCollegeChart" aria-label="College Activity Performance Chart" role="img"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 2: User Role Breakdown (Doughnut) -->
    <div class="col-12 col-xl-4">
        <div class="sa-chart-card">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                <div>
                    <h2 class="h5 fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-success fs-4" aria-hidden="true"></i> Portal User Roles
                    </h2>
                    <p class="text-muted small mb-0">Account role distribution</p>
                </div>
                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded text-xxs fw-bold">Governance</span>
            </div>
            <div class="sa-chart-container d-flex align-items-center justify-content-center" style="min-height: 320px;">
                <canvas id="saRoleChart" aria-label="Portal User Role Distribution Chart" role="img"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 3: 6-Month University Growth Trajectory -->
    <div class="col-12 col-xl-6">
        <div class="sa-chart-card">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                <div>
                    <h2 class="h5 fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-graph-up text-info fs-4" aria-hidden="true"></i> 6-Month Activity Trajectory
                    </h2>
                    <p class="text-muted small mb-0">System-wide monthly activity volume trend across all colleges</p>
                </div>
                <span class="badge bg-info bg-opacity-10 text-info px-3 py-1 rounded-pill text-xs fw-bold">Trend Momentum</span>
            </div>
            <div class="sa-chart-container" style="min-height: 280px;">
                <canvas id="saTrendChart" aria-label="6-Month Activity Trajectory Chart" role="img"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 4: Activity Categories Breakdown -->
    <div class="col-12 col-xl-6">
        <div class="sa-chart-card">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                <div>
                    <h2 class="h5 fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-tags-fill text-warning fs-4" aria-hidden="true"></i> System Event Categories
                    </h2>
                    <p class="text-muted small mb-0">Breakdown of submitted academic, research, outreach, and cultural events</p>
                </div>
                <span class="badge bg-warning bg-opacity-10 text-dark px-3 py-1 rounded-pill text-xs fw-bold">Classification</span>
            </div>
            <div class="sa-chart-container" style="min-height: 280px;">
                <canvas id="saCategoryChart" aria-label="System Event Categories Chart" role="img"></canvas>
            </div>
        </div>
    </div>
</section>

<!-- College-wise Activity Report Table & Social Media Tracker -->
<section aria-label="Detailed System Tables" class="row g-4 mb-4">
    <!-- College Report Table -->
    <div class="col-lg-7">
        <div class="sa-chart-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                <div>
                    <h2 class="h5 fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-building text-primary fs-4" aria-hidden="true"></i> College-wise Activity Report
                    </h2>
                    <p class="text-muted small mb-0">Performance breakdown across constituent institutions</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm rounded-pill border overflow-hidden shadow-sm" style="max-width: 200px;">
                        <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted" aria-hidden="true"></i></span>
                        <input type="text" class="form-control border-0 pe-3" id="saCollegeTableSearch" placeholder="Filter college..." aria-label="Filter college report table">
                    </div>
                    <span class="badge bg-dark text-white rounded-pill px-3 py-1">{{ count($collegeReports) }} Campuses</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table sa-table align-middle small mb-0" id="saCollegeTable">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" class="py-3 ps-3 text-uppercase text-xs fw-bold text-muted tracking-wider">College Code & Name</th>
                            <th scope="col" class="py-3 text-center text-uppercase text-xs fw-bold text-muted tracking-wider">Total</th>
                            <th scope="col" class="py-3 text-center text-uppercase text-xs fw-bold text-muted tracking-wider">Published</th>
                            <th scope="col" class="py-3 pe-3 text-center text-uppercase text-xs fw-bold text-muted tracking-wider">Pending</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($collegeReports as $college)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('superadmin.activities', ['college_id' => $college->id]) }}" class="text-decoration-none text-primary fw-bold text-sm">{{ $college->code }} <i class="bi bi-box-arrow-in-up-right text-xxs"></i></a>
                                <div class="text-muted text-xxs text-truncate" style="max-width: 260px;" title="{{ $college->name }}">{{ $college->name }}</div>
                            </td>
                            <td class="text-center fw-bold text-primary">{{ $college->total_submissions }}</td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">{{ $college->published_submissions }}</span>
                            </td>
                            <td class="pe-3 text-center">
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1">{{ $college->pending_submissions }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">No college data recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Submissions Feed -->
    <div class="col-lg-5">
        <div class="sa-chart-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                <div>
                    <h2 class="h5 fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-secondary fs-4" aria-hidden="true"></i> Live Master Feed
                    </h2>
                    <p class="text-muted small mb-0">Latest activities across university</p>
                </div>
                <a href="{{ route('superadmin.activities') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-bold">All Activities</a>
            </div>

            <div class="table-responsive">
                <table class="table sa-table align-middle small mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" class="py-3 ps-3 text-uppercase text-xs fw-bold text-muted tracking-wider">Activity & Ref ID</th>
                            <th scope="col" class="py-3 text-uppercase text-xs fw-bold text-muted tracking-wider">College</th>
                            <th scope="col" class="py-3 pe-3 text-end text-uppercase text-xs fw-bold text-muted tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentActivities as $activity)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold text-sm text-truncate mb-1" style="max-width: 180px;" title="{{ $activity->title }}">
                                    <a href="{{ route('submissions.show', $activity->id) }}" class="text-decoration-none text-dark hover-primary">{{ $activity->title }}</a>
                                </div>
                                <span class="badge bg-light text-secondary border text-xxs font-monospace">{{ $activity->reference_id }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">{{ $activity->college ? $activity->college->code : 'UNIV' }}</span>
                            </td>
                            <td class="pe-3 text-end">
                                {!! $activity->status_badge !!}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">No activities submitted yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Social Media Posting Live Tracker -->
<section aria-label="Social Media Posting Live Tracker" class="sa-chart-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
        <div>
            <h2 class="h5 fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-share-fill fs-4" style="color:#8b5cf6;" aria-hidden="true"></i> Social Media Live Broadcast Tracker
            </h2>
            <p class="text-muted small mb-0">Full trace of all social media posts published directly by PR Media Studio</p>
        </div>
        <a href="{{ route('media.approved') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-bold">Broadcast Archive</a>
    </div>

    <div class="table-responsive">
        <table class="table sa-table align-middle small mb-0">
            <thead class="bg-light">
                <tr>
                    <th scope="col" class="py-3 ps-3 text-uppercase text-xs fw-bold text-muted tracking-wider">Activity Title</th>
                    <th scope="col" class="py-3 text-uppercase text-xs fw-bold text-muted tracking-wider">Target Channels</th>
                    <th scope="col" class="py-3 text-uppercase text-xs fw-bold text-muted tracking-wider">Broadcast Action</th>
                    <th scope="col" class="py-3 text-uppercase text-xs fw-bold text-muted tracking-wider">Media Studio Officer</th>
                    <th scope="col" class="py-3 pe-3 text-end text-uppercase text-xs fw-bold text-muted tracking-wider">Timestamp</th>
                </tr>
            </thead>
            <tbody>
                @forelse($socialMediaPosts as $post)
                <tr>
                    <td class="ps-3 fw-bold text-sm">
                        <a href="{{ $post->submission ? route('submissions.show', $post->submission->id) : '#' }}" class="text-decoration-none text-dark hover-primary">{{ $post->submission ? $post->submission->title : 'Activity #' . $post->activity_submission_id }}</a>
                    </td>
                    <td>
                        <span class="badge bg-info bg-opacity-10 text-dark border border-info border-opacity-25 px-2 py-1 text-uppercase fw-bold">{{ is_array($post->platforms) ? implode(', ', $post->platforms) : ($post->platforms ?: 'Instagram, LinkedIn') }}</span>
                    </td>
                    <td>
                        @php
                            $targetUrl = $post->post_url_instagram ?: ($post->post_url_linkedin ?: ($post->post_url_twitter ?: '#'));
                        @endphp
                        @if($targetUrl !== '#')
                        <a href="{{ $targetUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1" aria-label="View live post (opens in new window)">
                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> <span>View Live Post</span>
                        </a>
                        @else
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 fw-bold"><i class="bi bi-broadcast-pin me-1" aria-hidden="true"></i>Live Broadcast</span>
                        @endif
                    </td>
                    <td class="text-muted text-xs fw-semibold">{{ $post->mediaEditor ? $post->mediaEditor->name : 'PR Desk' }}</td>
                    <td class="pe-3 text-end text-muted text-xs fw-semibold">{{ $post->created_at->format('M d, Y h:i A') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-broadcast fs-2 d-block mb-2 opacity-50" aria-hidden="true"></i>
                        <span>No social media posts broadcasted yet.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Animated Stat Counters
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

        // Live College Report Table Filter
        const saSearchInput = document.getElementById('saCollegeTableSearch');
        if (saSearchInput) {
            saSearchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll('#saCollegeTable tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        }

        // --- Chart 1: College Performance Comparison (Grouped Bar Chart) ---
        const saCollegeCtx = document.getElementById('saCollegeChart');
        if (saCollegeCtx) {
            new Chart(saCollegeCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($collegeNamesArray ?? []) !!},
                    datasets: [
                        {
                            label: 'Total Submissions',
                            data: {!! json_encode($collegeTotalsArray ?? []) !!},
                            backgroundColor: 'rgba(59, 130, 246, 0.85)',
                            borderColor: '#2563eb',
                            borderWidth: 1,
                            borderRadius: 6
                        },
                        {
                            label: 'Published Live',
                            data: {!! json_encode($collegePublishedArray ?? []) !!},
                            backgroundColor: 'rgba(16, 185, 129, 0.85)',
                            borderColor: '#059669',
                            borderWidth: 1,
                            borderRadius: 6
                        },
                        {
                            label: 'Pending Review',
                            data: {!! json_encode($collegePendingArray ?? []) !!},
                            backgroundColor: 'rgba(245, 158, 11, 0.85)',
                            borderColor: '#d97706',
                            borderWidth: 1,
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { font: { weight: '600', size: 12 }, usePointStyle: true } },
                        tooltip: { backgroundColor: '#0f172a', padding: 12, cornerRadius: 8 }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { weight: '600', size: 11 } } },
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } }
                    }
                }
            });
        }

        // --- Chart 2: User Role Distribution (Doughnut Chart) ---
        const saRoleCtx = document.getElementById('saRoleChart');
        if (saRoleCtx) {
            new Chart(saRoleCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($roleLabelsArray ?? ['College Admin', 'Media Editor', 'Registrar', 'Staff']) !!},
                    datasets: [{
                        data: {!! json_encode($roleCountsArray ?? [1, 1, 1, 1]) !!},
                        backgroundColor: [
                            '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#ec4899', '#64748b'
                        ],
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

        // --- Chart 3: 6-Month University Trajectory (Line/Area Chart) ---
        const saTrendCtx = document.getElementById('saTrendChart');
        if (saTrendCtx) {
            const ctxT = saTrendCtx.getContext('2d');
            const gradientT = ctxT.createLinearGradient(0, 0, 0, 280);
            gradientT.addColorStop(0, 'rgba(6, 182, 212, 0.4)');
            gradientT.addColorStop(1, 'rgba(6, 182, 212, 0.01)');

            new Chart(saTrendCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($monthlyLabelsArray ?? ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul']) !!},
                    datasets: [{
                        label: 'University Submissions',
                        data: {!! json_encode($monthlyCountsArray ?? [0, 0, 0, 0, 0, 0]) !!},
                        borderColor: '#06b6d4',
                        borderWidth: 3,
                        backgroundColor: gradientT,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#06b6d4',
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

        // --- Chart 4: Event Categories Breakdown (Horizontal Bar) ---
        const saCatCtx = document.getElementById('saCategoryChart');
        if (saCatCtx) {
            new Chart(saCatCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($categoryLabelsArray ?? ['Academic', 'Research', 'Cultural', 'Outreach']) !!},
                    datasets: [{
                        label: 'Events',
                        data: {!! json_encode($categoryCountsArray ?? [1, 1, 1, 1]) !!},
                        backgroundColor: [
                            'rgba(245, 158, 11, 0.85)',
                            'rgba(59, 130, 246, 0.85)',
                            'rgba(16, 185, 129, 0.85)',
                            'rgba(139, 92, 246, 0.85)',
                            'rgba(236, 72, 153, 0.85)'
                        ],
                        borderRadius: 6
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
