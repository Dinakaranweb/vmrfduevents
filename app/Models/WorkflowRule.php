<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowRule extends Model
{
    protected $fillable = [
        'workflow_id',
        'college_id',
        'category',
        'activity_type',
        'role_slug',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'priority' => 'integer',
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
     * Dynamically resolve the most appropriate workflow for an activity submission.
     */
    public static function resolveWorkflow(ActivitySubmission $submission)
    {
        $rules = self::where('is_active', true)
            ->with('workflow')
            ->orderBy('priority', 'desc')
            ->get();

        foreach ($rules as $rule) {
            if (!$rule->workflow || !$rule->workflow->is_active) {
                continue;
            }

            // Check matching criteria
            if ($rule->college_id && $rule->college_id != $submission->college_id) {
                continue;
            }
            if ($rule->category && strcasecmp($rule->category, $submission->event_category) !== 0) {
                continue;
            }

            return $rule->workflow;
        }

        // Fallback to default active workflow
        return Workflow::where('is_active', true)->where('is_default', true)->first()
            ?: Workflow::where('is_active', true)->first();
    }
}
