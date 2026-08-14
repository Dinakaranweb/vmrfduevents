<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PortalNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $notifications = $currentUser ? $currentUser->notifications()->paginate(15) : collect();
        return view('notifications.index', compact('notifications', 'currentUser'));
    }

    public function markAllRead()
    {
        $currentUser = Auth::user();
        if ($currentUser) {
            $currentUser->notifications()->update(['is_read' => true]);
        }
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
