<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivitySubmission;
use App\Models\College;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        $hasGlobalScope = $currentUser && ($currentUser->isSuperAdmin() || $currentUser->role === 'super_admin');

        $collegesQuery = College::query();
        if (!$hasGlobalScope && $currentUser && $currentUser->college_id) {
            $collegesQuery->where('id', $currentUser->college_id);
        }

        $colleges = $collegesQuery->withCount([
            'submissions',
            'submissions as published_count' => function($q) {
                $q->where('status', 'published');
            },
            'submissions as pending_count' => function($q) {
                $q->whereIn('status', ['verification_pending', 'approval_pending']);
            }
        ])->get();

        // Scope submission query
        $subQuery = ActivitySubmission::query();
        if (!$hasGlobalScope && $currentUser && $currentUser->college_id) {
            $subQuery->where('college_id', $currentUser->college_id);
        }

        // Status breakdown
        $statusBreakdown = [
            'Published' => (clone $subQuery)->where('status', 'published')->count(),
            'Media Review' => (clone $subQuery)->whereIn('status', ['media_review', 'editor_discuss'])->count(),
            'Pending Approval' => (clone $subQuery)->whereIn('status', ['verification_pending', 'approval_pending', 'registrar_approval'])->count(),
            'Sent Back / Query' => (clone $subQuery)->where('status', 'sent_back')->count(),
            'Rejected' => (clone $subQuery)->where('status', 'rejected')->count(),
        ];

        // Monthly trends (Last 6 months)
        $months = [];
        $monthlyCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $months[] = $monthDate->format('M Y');
            $monthlyCounts[] = (clone $subQuery)->whereYear('activity_date', $monthDate->year)
                ->whereMonth('activity_date', $monthDate->month)
                ->count();
        }

        return view('analytics.index', compact(
            'colleges',
            'statusBreakdown',
            'months',
            'monthlyCounts',
            'currentUser'
        ));
    }

    public function exportCsv()
    {
        $currentUser = Auth::user();
        $hasGlobalScope = $currentUser && ($currentUser->isSuperAdmin() || in_array($currentUser->role, ['registrar', 'office_registrar', 'vc', 'pro_vc', 'media_editor']) || ($currentUser->hasPermission('analytics.export') && !$currentUser->college_id));

        $query = ActivitySubmission::with(['college', 'user'])->orderBy('created_at', 'desc');
        if (!$hasGlobalScope && $currentUser && $currentUser->college_id) {
            $query->where('college_id', $currentUser->college_id);
        }
        $submissions = $query->get();

        $filename = "vmrfdu_activity_report_" . date('Y_m_d_H_i') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($submissions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Reference ID', 'College Code', 'Title', 'Activity Date', 'Category', 'Status', 'Submitted By', 'Submitted At']);

            foreach ($submissions as $sub) {
                fputcsv($file, [
                    $sub->reference_id,
                    $sub->college ? $sub->college->code : 'N/A',
                    $sub->title,
                    $sub->activity_date ? $sub->activity_date->format('Y-m-d') : '',
                    $sub->event_category,
                    strtoupper($sub->status),
                    $sub->user ? $sub->user->name : '',
                    $sub->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printableReport()
    {
        $currentUser = Auth::user();
        $hasGlobalScope = $currentUser && ($currentUser->isSuperAdmin() || in_array($currentUser->role, ['registrar', 'office_registrar', 'vc', 'pro_vc', 'media_editor']) || ($currentUser->hasPermission('analytics.view') && !$currentUser->college_id));

        $collegesQuery = College::query();
        if (!$hasGlobalScope && $currentUser && $currentUser->college_id) {
            $collegesQuery->where('id', $currentUser->college_id);
        }
        $colleges = $collegesQuery->withCount(['submissions', 'submissions as published_count' => function($q) {
            $q->where('status', 'published');
        }])->get();

        $subQuery = ActivitySubmission::with(['college'])->orderBy('created_at', 'desc');
        if (!$hasGlobalScope && $currentUser && $currentUser->college_id) {
            $subQuery->where('college_id', $currentUser->college_id);
        }
        $submissions = $subQuery->take(30)->get();

        return view('analytics.print_report', compact('colleges', 'submissions'));
    }
}
