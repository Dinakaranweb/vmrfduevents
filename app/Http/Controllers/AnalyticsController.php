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
        $statusCountsRaw = (clone $subQuery)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')->toArray();
        $countFor = fn($statuses) => array_sum(array_intersect_key($statusCountsRaw, array_flip((array)$statuses)));

        $statusBreakdown = [
            'Published' => $countFor('published'),
            'Media Review' => $countFor(['media_review', 'editor_discuss']),
            'Pending Approval' => $countFor(['verification_pending', 'approval_pending', 'registrar_approval']),
            'Sent Back / Query' => $countFor('sent_back'),
            'Rejected' => $countFor('rejected'),
        ];

        // Monthly trends (Last 6 months)
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth();
        $monthlyCountsRaw = (clone $subQuery)->where('activity_date', '>=', $sixMonthsAgo)
            ->selectRaw('YEAR(activity_date) as year, MONTH(activity_date) as month, count(*) as count')
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(function($item) {
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            });

        $months = [];
        $monthlyCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $key = $monthDate->format('Y-m');
            $months[] = $monthDate->format('M Y');
            $monthlyCounts[] = isset($monthlyCountsRaw[$key]) ? $monthlyCountsRaw[$key]->count : 0;
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
