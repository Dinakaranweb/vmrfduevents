<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublishedPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_submission_id',
        'media_editor_id',
        'platforms',
        'published_caption',
        'scheduled_at',
        'published_at',
        'post_url_instagram',
        'post_url_linkedin',
        'post_url_twitter',
        'post_url_facebook',
        'package_zip_path',
    ];

    protected function casts(): array
    {
        return [
            'platforms' => 'array',
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function submission()
    {
        return $this->belongsTo(ActivitySubmission::class, 'activity_submission_id');
    }

    public function mediaEditor()
    {
        return $this->belongsTo(User::class, 'media_editor_id');
    }
}
