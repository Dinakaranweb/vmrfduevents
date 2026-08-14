<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'sender',
        'recipient_user_id',
        'recipient_email',
        'reference_id',
        'subject',
        'stage',
        'body_html',
        'status',
        'error_message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
