<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function stages()
    {
        return $this->hasMany(WorkflowStage::class)->orderBy('stage_order');
    }

    public function rules()
    {
        return $this->hasMany(WorkflowRule::class)->orderBy('priority', 'desc');
    }

    /**
     * Duplicate an existing workflow along with its stages.
     */
    public function duplicate($newName = null)
    {
        $newWorkflow = self::create([
            'name' => $newName ?: ($this->name . ' (Copy)'),
            'description' => $this->description,
            'is_active' => true,
            'is_default' => false,
        ]);

        foreach ($this->stages as $stage) {
            WorkflowStage::create([
                'workflow_id' => $newWorkflow->id,
                'stage_order' => $stage->stage_order,
                'name' => $stage->name,
                'role_slug' => $stage->role_slug,
                'user_ids' => $stage->user_ids,
                'college_id' => $stage->college_id,
                'designation' => $stage->designation,
                'is_mandatory' => $stage->is_mandatory,
                'allow_parallel' => $stage->allow_parallel,
                'allowed_actions' => $stage->allowed_actions,
                'approval_condition' => $stage->approval_condition,
                'status_on_approve' => $stage->status_on_approve,
                'status_on_reject' => $stage->status_on_reject,
                'status_on_discuss' => $stage->status_on_discuss,
                'description' => $stage->description,
                'is_active' => $stage->is_active,
            ]);
        }

        return $newWorkflow;
    }
}
