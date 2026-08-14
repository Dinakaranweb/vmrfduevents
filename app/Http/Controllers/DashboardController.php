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

        $totalSubmissions = (clone $baseQuery)->count();

        // Customize metrics based on user roles and permissions
        if (!$canViewAllColleges) {
            $pendingApprovals = (clone $baseQuery)->whereIn('status', ['verification_pending', 'approval_pending', 'registrar_approval', 'discuss', 'sent_back'])->count();
            $mediaReady = (clone $baseQuery)->whereIn('status', ['media_review', 'editor_discuss', 'ready_to_publish', 'scheduled'])->count();
            $publishedCount = (clone $baseQuery)->where('status', 'published')->count();
        } elseif ($currentUser->role === 'registrar') {
            // Registrar handles Stage 1 verification
            $pendingApprovals = ActivitySubmission::whereIn('status', ['verification_pending', 'approval_pending'])->count();
            $mediaReady = ActivitySubmission::whereIn('status', ['media_review', 'editor_discuss'])->count();
            $publishedCount = ActivitySubmission::where('status', 'published')->count();
        } elseif ($currentUser->role === 'office_registrar') {
            // Office of Registrar handles Stage 2 final media approval
            $pendingApprovals = ActivitySubmission::where('status', 'registrar_approval')->count();
            $mediaReady = ActivitySubmission::whereIn('status', ['ready_to_publish', 'scheduled'])->count();
            $publishedCount = ActivitySubmission::where('status', 'published')->count();
        } elseif ($currentUser->role === 'media_editor') {
            $pendingApprovals = ActivitySubmission::whereIn('status', ['media_review', 'editor_discuss'])->count();
            $mediaReady = ActivitySubmission::whereIn('status', ['ready_to_publish', 'scheduled'])->count();
            $publishedCount = ActivitySubmission::where('status', 'published')->count();
        } elseif (in_array($currentUser->role, ['vc', 'pro_vc'])) {
            $pendingApprovals = ActivitySubmission::whereIn('status', ['verification_pending', 'approval_pending'])->count();
            $mediaReady = ActivitySubmission::whereIn('status', ['media_review', 'editor_discuss', 'ready_to_publish', 'scheduled'])->count();
            $publishedCount = ActivitySubmission::where('status', 'published')->count();
        } else {
            // Super Admin or other viewers
            $pendingApprovals = ActivitySubmission::whereIn('status', ['verification_pending', 'approval_pending', 'registrar_approval', 'discuss', 'sent_back'])->count();
            $mediaReady = ActivitySubmission::whereIn('status', ['media_review', 'editor_discuss', 'ready_to_publish', 'scheduled'])->count();
            $publishedCount = ActivitySubmission::where('status', 'published')->count();
        }

        // Recent activity feed strictly scoped by assigned college and role - only Super Admin can view all colleges
        $recentQuery = ActivitySubmission::with(['college', 'user'])->orderBy('updated_at', 'desc');
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
            'Stage 1 Review' => (clone $baseQuery)->whereIn('status', ['verification_pending', 'approval_pending'])->count(),
            'Media Desk' => (clone $baseQuery)->whereIn('status', ['media_review', 'editor_discuss'])->count(),
            'PA to Registrar' => (clone $baseQuery)->where('status', 'registrar_approval')->count(),
            'Ready/Scheduled' => (clone $baseQuery)->whereIn('status', ['ready_to_publish', 'scheduled'])->count(),
            'Published Live' => (clone $baseQuery)->where('status', 'published')->count(),
            'Returned/Discuss' => (clone $baseQuery)->whereIn('status', ['discuss', 'sent_back'])->count(),
        ];
        $statusLabels = array_keys($statusBreakdown);
        $statusData = array_values($statusBreakdown);

        // Monthly Trend (Last 6 Months)
        $monthlyLabels = [];
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $monthDate->format('M Y');
            $monthlyData[] = (clone $baseQuery)->whereYear('created_at', $monthDate->year)
                                               ->whereMonth('created_at', $monthDate->month)
                                               ->count();
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
