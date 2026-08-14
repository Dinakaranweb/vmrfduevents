<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivitySubmission;
use App\Models\College;
use Illuminate\Support\Facades\Auth;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        $query = ActivitySubmission::with(['college', 'media', 'publishedPost'])
            ->whereIn('status', ['published', 'scheduled', 'media_review']);

        if ($request->filled('college_id') && $request->college_id !== 'all') {
            $query->where('college_id', $request->college_id);
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('event_category', $request->category);
        }

        $items = $query->orderBy('activity_date', 'desc')->paginate(12);
        $colleges = College::where('is_active', true)->get();

        return view('gallery.index', compact('items', 'colleges', 'currentUser'));
    }
}
