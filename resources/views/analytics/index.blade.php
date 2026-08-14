@extends('layouts.app')

@section('title', 'Institutional Analytics & Reports Engine')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 p-4 bg-white rounded-4 shadow-sm border">
    <div>
        <span class="badge bg-primary px-3 py-1 mb-2 rounded-pill shadow-sm"><i class="bi bi-bar-chart-steps me-1"></i> Performance Intelligence</span>
        <h3 class="fw-bold mb-1 text-dark"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Institutional Activity Analytics & Reports</h3>
        <p class="text-muted mb-0 small">Evaluate campus submission performance, publication velocity, and workflow stage distribution across 28 institutions</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('analytics.export.csv') }}" class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-file-earmark-excel-fill"></i> Export CSV
        </a>
        <a href="{{ route('analytics.print') }}" target="_blank" class="btn btn-light border px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-printer-fill text-dark"></i> Print PDF
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Monthly Trend Chart -->
    <div class="col-12 col-lg-7">
        <div class="portal-card p-4 p-md-5 bg-white border shadow-sm">
            <div class="mb-4">
                <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Submission & Publication Velocity</h5>
                <p class="text-muted small mb-0">Monthly activity volume submitted across VMRFDU constituent colleges (Last 6 Months)</p>
            </div>
            <div style="height: 300px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Status Distribution Pie -->
    <div class="col-12 col-lg-5">
        <div class="portal-card p-4 p-md-5 bg-white border shadow-sm">
            <div class="mb-4">
                <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-pie-chart-fill text-warning me-2"></i>Workflow Pipeline Distribution</h5>
                <p class="text-muted small mb-0">Verification stage breakdown across all institutional submissions</p>
            </div>
            <div style="height: 300px; display: flex; justify-content: center; align-items: center;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- College Master Summary Table -->
<div class="portal-card p-4 p-md-5 bg-white border shadow-sm">
    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-buildings text-primary me-2"></i>Constituent Campus Submission Summary</h5>
            <p class="text-muted small mb-0">Individual college publication metrics and performance benchmarks</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="input-group input-group-sm" style="max-width: 200px;">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control table-search-input border-start-0" placeholder="Filter campus...">
            </div>
            <span class="badge bg-light text-dark border px-3 py-2 fw-bold">{{ $colleges->count() }} Institutions</span>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-portal align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>College Name</th>
                    <th>Location</th>
                    <th class="text-center">Total Submitted</th>
                    <th class="text-center">Pending Review</th>
                    <th class="text-center">Published Live</th>
                    <th style="min-width: 150px;">Publication Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($colleges as $col)
                    @php
                        $percent = $col->submissions_count > 0 ? round(($col->published_count / $col->submissions_count) * 100) : 0;
                        $perfColor = $percent >= 70 ? 'success' : ($percent >= 40 ? 'warning' : 'danger');
                    @endphp
                <tr>
                    <td class="fw-bold text-primary font-monospace">{{ $col->code }}</td>
                    <td class="fw-semibold text-dark">{{ $col->name }}</td>
                    <td class="small text-muted fw-medium">{{ $col->location }}</td>
                    <td class="text-center"><span class="badge bg-dark text-white px-3 py-1 rounded-pill fw-bold">{{ $col->submissions_count }}</span></td>
                    <td class="text-center"><span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">{{ $col->pending_count }}</span></td>
                    <td class="text-center"><span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold">{{ $col->published_count }}</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1 rounded-pill" style="height: 8px; background: #e5e7eb;">
                                <div class="progress-bar bg-{{ $perfColor }} rounded-pill" role="progressbar" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="small fw-bold text-{{ $perfColor }} text-nowrap" style="min-width: 38px;">{{ $percent }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Monthly trend chart
        const ctxM = document.getElementById('monthlyChart').getContext('2d');
        const gradM = ctxM.createLinearGradient(0, 0, 0, 300);
        gradM.addColorStop(0, 'rgba(37, 99, 235, 0.3)');
        gradM.addColorStop(1, 'rgba(37, 99, 235, 0.01)');

        new Chart(ctxM, {
            type: 'line',
            data: {
                labels: {!! json_encode($months) !!},
                datasets: [{
                    label: 'Activities Submitted',
                    data: {!! json_encode($monthlyCounts) !!},
                    borderColor: '#2563eb',
                    backgroundColor: gradM,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2563eb',
                    pointRadius: 5,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.04)' } }
                }
            }
        });

        // Status doughnut chart
        const ctxS = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxS, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($statusBreakdown)) !!},
                datasets: [{
                    data: {!! json_encode(array_values($statusBreakdown)) !!},
                    backgroundColor: ['#10b981', '#06b6d4', '#f59e0b', '#64748b', '#ef4444', '#3b82f6'],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } }
                }
            }
        });
    });
</script>
@endpush
