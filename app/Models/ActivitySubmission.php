<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivitySubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_id',
        'user_id',
        'submitter_name_snapshot',
        'college_id',
        'activity_type',
        'title',
        'report_type',
        'description',
        'activity_date',
        'event_category',
        'tags',
        'resource_person_name',
        'outcome',
        'location',
        'target_audience',
        'faculty_coordinator_name',
        'faculty_coordinator_email',
        'faculty_coordinator_phone',
        'social_media_caption',
        'status',
        'current_stage',
        'workflow_id',
        'current_stage_id',
        'admin_remarks',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if ($model->user && empty($model->submitter_name_snapshot)) {
                $model->submitter_name_snapshot = $model->user->name;
            }
        });
    }

    public function getSubmitterNameAttribute()
    {
        return $this->submitter_name_snapshot ?: ($this->user ? $this->user->name : 'Unknown User');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function currentWorkflowStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'current_stage_id');
    }

    public function media()
    {
        return $this->hasMany(ActivityMedia::class);
    }

    public function approvalLogs()
    {
        return $this->hasMany(ApprovalLog::class)->orderBy('created_at', 'desc');
    }

    public function publishedPost()
    {
        return $this->hasOne(PublishedPost::class);
    }

    public function discussions()
    {
        return $this->hasMany(ActivityDiscussion::class)->orderBy('created_at', 'asc');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'verification_pending', 'approval_pending' => '<span class="badge bg-warning text-dark shadow-sm"><i class="bi bi-clock-history me-1"></i>Pending Verification</span>',
            'media_review' => '<span class="badge bg-info text-white shadow-sm"><i class="bi bi-camera-reels-fill me-1"></i>Media Editor Editing</span>',
            'registrar_approval' => '<span class="badge bg-primary text-white shadow-sm"><i class="bi bi-shield-check me-1"></i>Office of the Registrar Review</span>',
            'editor_discuss' => '<span class="badge bg-warning text-dark border border-dark shadow-sm"><i class="bi bi-chat-left-dots-fill me-1"></i>Registrar Discuss (Editor Correction)</span>',
            'ready_to_publish' => '<span class="badge bg-success text-white shadow-sm"><i class="bi bi-check-circle-fill me-1"></i>Registrar Approved (Ready to Publish)</span>',
            'scheduled' => '<span class="badge bg-purple text-white shadow-sm" style="background-color: #6f42c1;"><i class="bi bi-calendar-check me-1"></i>Scheduled</span>',
            'published' => '<span class="badge bg-dark text-white shadow-sm"><i class="bi bi-broadcast me-1"></i>Published Live</span>',
            'discuss', 'sent_back' => '<span class="badge bg-warning text-dark border border-dark shadow-sm"><i class="bi bi-chat-dots-fill me-1"></i>Discuss (Needs College Resubmission)</span>',
            'rejected' => '<span class="badge bg-danger text-white shadow-sm"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>',
            default => '<span class="badge bg-light text-dark">' . ucfirst(str_replace('_', ' ', $this->status)) . '</span>',
        };
    }
}
