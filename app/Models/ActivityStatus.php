<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityStatus extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'badge_color',
        'description',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];
}
