<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'event_key',
        'name',
        'email_subject',
        'email_body',
        'system_title',
        'system_message',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
