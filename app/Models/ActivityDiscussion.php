<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityDiscussion extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_submission_id',
        'user_id',
        'author_name_snapshot',
        'comment',
        'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if ($model->user && empty($model->author_name_snapshot)) {
                $model->author_name_snapshot = $model->user->name;
            }
        });
    }

    public function getAuthorNameAttribute()
    {
        return $this->author_name_snapshot ?: ($this->user ? $this->user->name : 'Unknown User');
    }

    public function activitySubmission()
    {
        return $this->belongsTo(ActivitySubmission::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
