<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VMRFDU Official Activity & Publication Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; color: #111; }
        .header-box { border-bottom: 3px solid #311b92; padding-bottom: 15px; margin-bottom: 25px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print mb-4 d-flex justify-content-end gap-2">
    <button onclick="window.print()" class="btn btn-primary fw-bold"><i class="bi bi-printer"></i> Print / Save as PDF</button>
    <button onclick="window.close()" class="btn btn-secondary">Close</button>
</div>

<div class="header-box d-flex justify-content-between align-items-end">
    <div>
        <h2 class="fw-bold mb-1" style="color: #311b92;">Vinayaka Mission's Research Foundation</h2>
        <h5 class="text-muted mb-0">(Deemed to be University) &mdash; Salem, Tamil Nadu & Puducherry</h5>
        <div class="fw-bold mt-2">Centralized Institutional Activity & Media Publication Report</div>
    </div>
    <div class="text-end small text-muted">
        <div>Generated Date: {{ date('d F Y, h:i A') }}</div>
        <div>System Version: VMRFDU Portal</div>
    </div>
</div>

<h5 class="fw-bold mb-3 border-bottom pb-1">1. Constituent College Performance Summary</h5>
<table class="table table-bordered table-sm small mb-4">
    <thead class="table-light">
        <tr>
            <th>College Code</th>
            <th>Institution Name</th>
            <th class="text-center">Total Submitted</th>
            <th class="text-center">Published Posts</th>
        </tr>
    </thead>
    <tbody>
        @foreach($colleges as $col)
        <tr>
            <td class="fw-bold">{{ $col->code }}</td>
            <td>{{ $col->name }}</td>
            <td class="text-center">{{ $col->submissions_count }}</td>
            <td class="text-center">{{ $col->published_count }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h5 class="fw-bold mb-3 border-bottom pb-1">2. Detailed Submissions Log (Recent 30 Entries)</h5>
<table class="table table-bordered table-striped table-sm small">
    <thead class="table-light">
        <tr>
            <th>Ref ID</th>
            <th>Campus</th>
            <th>Title</th>
            <th>Category</th>
            <th>Date</th>
            <th>Status</th>
            <th>Submitted By</th>
        </tr>
    </thead>
    <tbody>
        @foreach($submissions as $sub)
        <tr>
            <td class="fw-bold">{{ $sub->reference_id }}</td>
            <td>{{ $sub->college ? $sub->college->code : '' }}</td>
            <td>{{ $sub->title }}</td>
            <td>{{ $sub->event_category }}</td>
            <td>{{ $sub->activity_date ? $sub->activity_date->format('Y-m-d') : '' }}</td>
            <td class="text-uppercase fw-semibold">{{ $sub->status }}</td>
            <td>{{ $sub->submitter_name }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="mt-5 pt-5 d-flex justify-content-between small text-muted">
    <div>
        <div class="border-top pt-2" style="width: 200px;">Compiled By: IQAC / PR Desk</div>
    </div>
    <div>
        <div class="border-top pt-2" style="width: 200px;">Registrar / VC Authorization</div>
    </div>
</div>

</body>
</html>
