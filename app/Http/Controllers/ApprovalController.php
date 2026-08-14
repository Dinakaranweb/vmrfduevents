<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivitySubmission;
use App\Models\ApprovalLog;
use App\Models\ActivityDiscussion;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    private function checkAuthorityAccess($user, $requiredPermission = null)
    {
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->role === 'super_admin') {
            return true;
        }

        if ($requiredPermission && $user->hasPermission($requiredPermission)) {
            return true;
        }

        if (!$requiredPermission && ($user->hasPermission('approvals.view') || $user->hasPermission('approvals.approve') || $user->hasPermission('approvals.reject') || $user->hasPermission('approvals.discuss') || in_array($user->role, ['registrar', 'office_registrar', 'vc', 'pro_vc']))) {
            return true;
        }

        abort(403, 'Permission Denied: Your assigned role does not have permission (' . ($requiredPermission ?: 'approvals.view') . ') to access the Approvals Authority Desk.');
    }

    public function index(Request $request)
    {
        $this->checkAuthorityAccess(Auth::user());
        return $this->pending($request);
    }

    public function pending(Request $request)
    {
        $currentUser = Auth::user();
        $this->checkAuthorityAccess($currentUser);
        if ($currentUser && $currentUser->role === 'office_registrar') {
            $targetStatuses = ['registrar_approval'];
        } elseif ($currentUser && $currentUser->isSuperAdmin()) {
            $targetStatuses = ['verification_pending', 'approval_pending', 'registrar_approval'];
        } else {
            // Stage 1 verification authorities (Registrar, VC, Pro VC) only review initial stage
            $targetStatuses = ['verification_pending', 'approval_pending'];
        }

        $pendingSubmissions = ActivitySubmission::with(['college', 'user', 'media'])
            ->whereIn('status', $targetStatuses)
            ->orderBy('created_at', 'asc')
            ->get();
        $reviewedSubmissions = collect();
        $activeTab = 'pending';
        return view('approvals.index', compact('pendingSubmissions', 'reviewedSubmissions', 'currentUser', 'activeTab'));
    }

    public function approved(Request $request)
    {
        $currentUser = Auth::user();
        $this->checkAuthorityAccess($currentUser);
        $pendingSubmissions = collect();

        $targetStatuses = ['media_review', 'editor_discuss', 'ready_to_publish', 'scheduled', 'published'];
        if ($currentUser && $currentUser->role === 'office_registrar') {
            // Office of Registrar only sees final media approvals authorized for publication
            $targetStatuses = ['ready_to_publish', 'scheduled', 'published'];
        }

        $reviewedSubmissions = ActivitySubmission::with(['college', 'user', 'media'])
            ->whereIn('status', $targetStatuses)
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
        $activeTab = 'approved';
        return view('approvals.index', compact('pendingSubmissions', 'reviewedSubmissions', 'currentUser', 'activeTab'));
    }

    public function rejected(Request $request)
    {
        $currentUser = Auth::user();
        $this->checkAuthorityAccess($currentUser);
        $pendingSubmissions = collect();

        $query = ActivitySubmission::with(['college', 'user', 'media'])
            ->where('status', 'rejected');

        if ($currentUser && $currentUser->role === 'office_registrar') {
            $query->where('current_stage', 'like', '%Office of the Registrar%');
        }

        $reviewedSubmissions = $query->orderBy('updated_at', 'desc')->paginate(15);
        $activeTab = 'rejected';
        return view('approvals.index', compact('pendingSubmissions', 'reviewedSubmissions', 'currentUser', 'activeTab'));
    }

    public function processAction(Request $request, $id)
    {
        $currentUser = Auth::user();
        $this->checkAuthorityAccess($currentUser);
        $submission = ActivitySubmission::findOrFail($id);

        $request->validate([
            'action' => 'required|in:approve,discuss,send_back,reject',
            'remarks' => 'nullable|string',
        ]);

        $oldStatus = $submission->status;

        // Stage security validation
        if ($oldStatus === 'registrar_approval' && !$currentUser->isSuperAdmin() && $currentUser->role !== 'office_registrar') {
            abort(403, 'Unauthorized action: Final Media Package approval after editing is reserved exclusively for the PA to Registrar (Office of the Registrar).');
        }
        if (in_array($oldStatus, ['verification_pending', 'approval_pending']) && $currentUser->role === 'office_registrar') {
            abort(403, 'Unauthorized action: Initial Institutional Verification is performed by the Registrar ID.');
        }

        $action = $request->action;
        
        // Granular Permission Validation
        if (!$currentUser->isSuperAdmin() && !in_array($currentUser->role, ['registrar', 'office_registrar', 'vc', 'pro_vc'])) {
            if ($action === 'approve' && !$currentUser->hasPermission('approvals.approve')) {
                abort(403, 'Permission Denied: You do not have permission to approve submissions.');
            }
            if ($action === 'reject' && !$currentUser->hasPermission('approvals.reject')) {
                abort(403, 'Permission Denied: You do not have permission to reject submissions.');
            }
            if (in_array($action, ['discuss', 'send_back']) && !$currentUser->hasPermission('approvals.discuss')) {
                abort(403, 'Permission Denied: You do not have permission to return submissions for discussion.');
            }
        }

        $remarks = $request->filled('remarks') ? $request->input('remarks') : ('Reviewed by ' . ($currentUser ? $currentUser->role_title : 'Authority'));

        // Check if this action is from the Office of the Registrar Approval stage
        if ($oldStatus === 'registrar_approval') {
            if ($action === 'approve') {
                $submission->status = 'ready_to_publish';
                $submission->current_stage = 'Approved by PA to Registrar — Ready for Publication';
                $submission->admin_remarks = $remarks;
                $actionName = 'approved by PA to Registrar (Office of the Registrar)';
            } elseif ($action === 'discuss' || $action === 'send_back') {
                $submission->status = 'editor_discuss';
                $submission->current_stage = 'Returned by PA to Registrar for Editor Correction';
                $submission->admin_remarks = $remarks;
                $actionName = 'marked for discussion by PA to Registrar (Office of the Registrar)';

                ActivityDiscussion::create([
                    'activity_submission_id' => $submission->id,
                    'user_id' => $currentUser ? $currentUser->id : null,
                    'comment' => "[Registrar Discuss - Editor Correction Needed]: " . $remarks,
                    'is_internal' => false,
                ]);
            } else { // reject
                $submission->status = 'rejected';
                $submission->current_stage = 'Rejected by Office of the Registrar';
                $submission->admin_remarks = $remarks;
                $actionName = 'rejected by Office of the Registrar';
            }
        } else {
            // Standard verification stage (verification_pending / approval_pending)
            if ($action === 'approve') {
                // Forward to Media Editor desk
                $submission->status = 'media_review';
                $submission->current_stage = 'Media Editor Desk - Photo & Caption Editing';
                $submission->admin_remarks = $remarks;
                $actionName = 'approved';
            } elseif ($action === 'discuss' || $action === 'send_back') {
                $submission->status = 'discuss';
                $submission->current_stage = 'Returned for Correction (Editable Once)';
                $submission->admin_remarks = $remarks;
                $actionName = 'marked for discussion';

                ActivityDiscussion::create([
                    'activity_submission_id' => $submission->id,
                    'user_id' => $currentUser ? $currentUser->id : null,
                    'comment' => "[$actionName]: " . $remarks,
                    'is_internal' => false,
                ]);
            } else { // reject
                $submission->status = 'rejected';
                $submission->current_stage = 'Closed / Rejected';
                $submission->admin_remarks = $remarks;
                $actionName = 'rejected';
            }
        }

        $submission->save();

        ApprovalLog::create([
            'activity_submission_id' => $submission->id,
            'user_id' => $currentUser ? $currentUser->id : null,
            'action' => $actionName,
            'from_status' => $oldStatus,
            'to_status' => $submission->status,
            'remarks' => $remarks,
        ]);

        NotificationService::notifyStageChange($submission, $actionName, $remarks);

        return redirect()->route('submissions.show', $submission->id)
            ->with('success', "Action '{$actionName}' successfully processed!");
    }
}
