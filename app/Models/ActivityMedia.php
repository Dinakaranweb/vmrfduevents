<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_submission_id',
        'file_path',
        'file_name',
        'file_type',
        'media_category',
        'file_size',
        'mime_type',
        'is_primary',
        'dimensions',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    public function submission()
    {
        return $this->belongsTo(ActivitySubmission::class, 'activity_submission_id');
    }
}
