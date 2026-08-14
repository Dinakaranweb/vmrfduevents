<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowStage extends Model
{
    protected $fillable = [
        'workflow_id',
        'stage_order',
        'name',
        'role_slug',
        'user_ids',
        'college_id',
        'designation',
        'is_mandatory',
        'allow_parallel',
        'allowed_actions',
        'approval_condition',
        'status_on_approve',
        'status_on_reject',
        'status_on_discuss',
        'description',
        'is_active',
    ];

    protected $casts = [
        'workflow_id' => 'integer',
        'stage_order' => 'integer',
        'college_id' => 'integer',
        'is_mandatory' => 'boolean',
        'allow_parallel' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    /**
     * Get array of roles if multiple approvers are configured.
     */
    public function getApproverRolesAttribute()
    {
        if (empty($this->role_slug)) {
            return [];
        }
        $decoded = json_decode($this->role_slug, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return array_map('trim', explode(',', $this->role_slug));
    }

    /**
     * Set role_slug cleanly from string or array.
     */
    public function setRoleSlugAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['role_slug'] = json_encode(array_values($value));
        } else {
            $this->attributes['role_slug'] = $value;
        }
    }

    /**
     * Get array of specific assigned user IDs.
     */
    public function getAssignedUserIdsAttribute()
    {
        if (empty($this->user_ids)) {
            return [];
        }
        $decoded = json_decode($this->user_ids, true);
        if (is_array($decoded)) {
            return array_map('intval', $decoded);
        }
        return array_map('intval', explode(',', $this->user_ids));
    }

    /**
     * Get array of allowed actions (approve, discuss, reject, return, forward, hold, reassign).
     */
    public function getAllowedActionsListAttribute()
    {
        if (empty($this->allowed_actions)) {
            return ['approve', 'discuss', 'reject', 'return', 'forward', 'hold', 'reassign'];
        }
        $decoded = json_decode($this->allowed_actions, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return array_map('trim', explode(',', $this->allowed_actions));
    }

    /**
     * Check if a specific user has authorization to approve at this stage.
     */
    public function canUserApprove(User $user, ActivitySubmission $submission = null)
    {
        // Super Admin can always approve any stage
        if ($user->role === 'super_admin') {
            return true;
        }

        // Check explicit User IDs mapping
        $assignedIds = $this->assigned_user_ids;
        if (!empty($assignedIds)) {
            if (in_array($user->id, $assignedIds)) {
                return true;
            }
            // If explicit user IDs are set and user is not among them, restrict
            return false;
        }

        // Check college constraint if configured on stage
        if ($this->college_id && $user->college_id != $this->college_id) {
            return false;
        }

        // Check submission college scoping
        if ($submission && $user->college_id && $user->college_id != $submission->college_id) {
            // Exceptions: central roles like registrar, vc, pro_vc, media_editor can approve across colleges
            if (!in_array($user->role, ['registrar', 'vc', 'pro_vc', 'media_editor'])) {
                return false;
            }
        }

        // Check role match
        $roles = $this->approver_roles;
        if (!empty($roles) && in_array($user->role, $roles)) {
            return true;
        }

        return false;
    }
}
