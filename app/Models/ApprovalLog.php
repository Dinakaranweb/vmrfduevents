<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_submission_id',
        'user_id',
        'actor_name_snapshot',
        'action',
        'from_status',
        'to_status',
        'remarks',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if ($model->user && empty($model->actor_name_snapshot)) {
                $model->actor_name_snapshot = $model->user->name;
            }
        });
    }

    public function getActorNameAttribute()
    {
        return $this->actor_name_snapshot ?: ($this->user ? $this->user->name : 'System');
    }

    public function submission()
    {
        return $this->belongsTo(ActivitySubmission::class, 'activity_submission_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
