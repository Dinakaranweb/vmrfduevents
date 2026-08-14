<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Auth;

class EmailLogController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $logs = EmailLog::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('emails.index', compact('logs', 'currentUser'));
    }

    public function show($id)
    {
        $log = EmailLog::findOrFail($id);
        return response($log->body_html)->header('Content-Type', 'text/html');
    }
}
