<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivitySubmission;
use App\Models\College;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser) {
            return redirect()->route('login');
        }

        if ($currentUser->role === 'media_editor') {
            return redirect()->route('media.dashboard');
        }

        // Role-scoped base query
        $baseQuery = ActivitySubmission::query();
        $canViewAllColleges = $currentUser->isSuperAdmin() || in_array($currentUser->role, ['super_admin', 'registrar', 'office_registrar', 'vc', 'pro_vc', 'media_editor']);
        if (!$canViewAllColleges) {
            if ($currentUser->college_id) {
                $baseQuery->where('college_id', $currentUser->college_id);
            } else {
                $baseQuery->where('user_id', $currentUser->id);
            }
        }
        if ($currentUser->role === 'office_registrar') {
            // Office of Registrar only tracks activities submitted for Final Media Approval
            $baseQuery->whereIn('status', ['registrar_approval', 'ready_to_publish', 'scheduled', 'published', 'editor_discuss']);
        }

        $statusCounts = (clone $baseQuery)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')->toArray();
        $countFor = fn($statuses) => array_sum(array_intersect_key($statusCounts, array_flip((array)$statuses)));

        $totalSubmissions = array_sum($statusCounts);

        // Customize metrics based on user roles and permissions
        if (!$canViewAllColleges) {
            $pendingApprovals = $countFor(['verification_pending', 'approval_pending', 'registrar_approval', 'discuss', 'sent_back']);
            $mediaReady = $countFor(['media_review', 'editor_discuss', 'ready_to_publish', 'scheduled']);
            $publishedCount = $countFor('published');
        } elseif ($currentUser->role === 'registrar') {
            $pendingApprovals = $countFor(['verification_pending', 'approval_pending']);
            $mediaReady = $countFor(['media_review', 'editor_discuss']);
            $publishedCount = $countFor('published');
        } elseif ($currentUser->role === 'office_registrar') {
            $pendingApprovals = $countFor('registrar_approval');
            $mediaReady = $countFor(['ready_to_publish', 'scheduled']);
            $publishedCount = $countFor('published');
        } elseif ($currentUser->role === 'media_editor') {
            $pendingApprovals = $countFor(['media_review', 'editor_discuss']);
            $mediaReady = $countFor(['ready_to_publish', 'scheduled']);
            $publishedCount = $countFor('published');
        } elseif (in_array($currentUser->role, ['vc', 'pro_vc'])) {
            $pendingApprovals = $countFor(['verification_pending', 'approval_pending']);
            $mediaReady = $countFor(['media_review', 'editor_discuss', 'ready_to_publish', 'scheduled']);
            $publishedCount = $countFor('published');
        } else {
            // Super Admin or other viewers
            $pendingApprovals = $countFor(['verification_pending', 'approval_pending', 'registrar_approval', 'discuss', 'sent_back']);
            $mediaReady = $countFor(['media_review', 'editor_discuss', 'ready_to_publish', 'scheduled']);
            $publishedCount = $countFor('published');
        }

        // Recent activity feed strictly scoped by assigned college and role - only Super Admin can view all colleges
        $recentQuery = ActivitySubmission::with(['college', 'user', 'media', 'publishedPost'])->orderBy('updated_at', 'desc');
        if (!$canViewAllColleges) {
            if ($currentUser->college_id) {
                $recentQuery->where('college_id', $currentUser->college_id);
            } else {
                $recentQuery->where('user_id', $currentUser->id);
            }
        }
        if ($currentUser->role === 'registrar') {
            $recentQuery->whereIn('status', ['verification_pending', 'approval_pending', 'media_review', 'editor_discuss']);
        } elseif ($currentUser->role === 'office_registrar') {
            $recentQuery->whereIn('status', ['registrar_approval', 'ready_to_publish', 'scheduled', 'published', 'editor_discuss']);
        } elseif ($currentUser->role === 'media_editor') {
            $recentQuery->whereIn('status', ['media_review', 'editor_discuss', 'registrar_approval', 'ready_to_publish', 'scheduled', 'published']);
        }
        $recentSubmissions = $recentQuery->take(8)->get();

        // Chart Data Scoped by Role
        if (!$canViewAllColleges && $currentUser->college_id) {
            $colleges = College::where('id', $currentUser->college_id)->withCount('submissions')->get();
            $collegeNames = $colleges->pluck('code')->toArray();
            $collegeCounts = $colleges->pluck('submissions_count')->toArray();
        } else {
            $colleges = College::withCount('submissions')->orderByDesc('submissions_count')->take(12)->get();
            $collegeNames = $colleges->pluck('code')->toArray();
            $collegeCounts = $colleges->pluck('submissions_count')->toArray();
        }

        // Status Breakdown Distribution
        $statusBreakdown = [
            'Stage 1 Review' => $countFor(['verification_pending', 'approval_pending']),
            'Media Desk' => $countFor(['media_review', 'editor_discuss']),
            'PA to Registrar' => $countFor('registrar_approval'),
            'Ready/Scheduled' => $countFor(['ready_to_publish', 'scheduled']),
            'Published Live' => $countFor('published'),
            'Returned/Discuss' => $countFor(['discuss', 'sent_back']),
        ];
        $statusLabels = array_keys($statusBreakdown);
        $statusData = array_values($statusBreakdown);

        // Monthly Trend (Last 6 Months)
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        $monthlyCounts = (clone $baseQuery)->where('created_at', '>=', $sixMonthsAgo)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, count(*) as count')
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(function($item) {
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            });

        $monthlyLabels = [];
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $key = $monthDate->format('Y-m');
            $monthlyLabels[] = $monthDate->format('M Y');
            $monthlyData[] = isset($monthlyCounts[$key]) ? $monthlyCounts[$key]->count : 0;
        }

        // Category Breakdown
        $categoryBreakdown = (clone $baseQuery)
            ->selectRaw('COALESCE(event_category, "Other/General") as category_name, count(*) as count')
            ->groupBy('category_name')
            ->pluck('count', 'category_name')->toArray();
        $categoryLabels = array_keys($categoryBreakdown);
        $categoryData = array_values($categoryBreakdown);
        if (empty($categoryLabels)) {
            $categoryLabels = ['Academic Event', 'Symposium', 'Workshop', 'Outreach'];
            $categoryData = [0, 0, 0, 0];
        }

        return view('dashboard', compact(
            'currentUser',
            'totalSubmissions',
            'pendingApprovals',
            'mediaReady',
            'publishedCount',
            'recentSubmissions',
            'collegeNames',
            'collegeCounts',
            'statusLabels',
            'statusData',
            'monthlyLabels',
            'monthlyData',
            'categoryLabels',
            'categoryData'
        ));
    }
}
