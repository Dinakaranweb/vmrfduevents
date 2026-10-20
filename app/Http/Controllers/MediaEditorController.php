<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\UploadStorage;
use App\Models\ActivitySubmission;
use App\Models\ActivityMedia;
use App\Models\ActivityDiscussion;
use App\Models\PublishedPost;
use App\Models\ApprovalLog;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MediaEditorController extends Controller
{
    private function checkMediaEditorAccess($user, $requiredPermission = null)
    {
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->role === 'super_admin') {
            return true;
        }

        if ($requiredPermission && $user->hasPermission($requiredPermission)) {
            return true;
        }

        if (!$requiredPermission && ($user->hasPermission('media.view') || $user->hasPermission('media.edit') || $user->hasPermission('media.publish') || $user->role === 'media_editor')) {
            return true;
        }

        abort(403, 'Permission Denied: Your assigned role does not have permission (' . ($requiredPermission ?: 'media.view') . ') to access the Media & Publication Desk.');
    }

    private function getMediaCounts()
    {
        return [
            'pendingEditingCount' => ActivitySubmission::whereIn('status', ['media_review', 'editor_discuss'])->count(),
            'lockedRegistrarCount' => ActivitySubmission::where('status', 'registrar_approval')->count(),
            'readyCount' => ActivitySubmission::where('status', 'ready_to_publish')->count(),
            'scheduledCount' => ActivitySubmission::where('status', 'scheduled')->count(),
            'publishedCount' => ActivitySubmission::where('status', 'published')->count(),
        ];
    }

    public function dashboard(Request $request)
    {
        $currentUser = Auth::user();
        $this->checkMediaEditorAccess($currentUser);

        $counts = $this->getMediaCounts();
        $activeTab = $request->input('tab', 'overview');

        // Stage 1: Items awaiting Editor action
        $pendingEditorQueue = ActivitySubmission::with(['college', 'media', 'discussions'])
            ->whereIn('status', ['media_review', 'editor_discuss'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Locked items submitted to Registrar
        $lockedRegistrarQueue = ActivitySubmission::with(['college', 'media', 'discussions'])
            ->where('status', 'registrar_approval')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Stage 2: Items ready for publication
        $readyForPublication = ActivitySubmission::with(['college', 'media'])
            ->where('status', 'ready_to_publish')
            ->orderBy('updated_at', 'desc')
            ->get();

        $scheduledPosts = ActivitySubmission::with(['college', 'publishedPost'])
            ->where('status', 'scheduled')
            ->orderBy('updated_at', 'desc')
            ->get();

        $recentlyPublished = ActivitySubmission::with(['college', 'publishedPost', 'media'])
            ->where('status', 'published')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        // Media Desk Stage Breakdown
        $mediaStageLabels = ['Pending Action', 'PA to Registrar', 'Ready to Publish', 'Scheduled', 'Published Live'];
        $mediaStageCounts = [
            $counts['pendingEditingCount'],
            $counts['lockedRegistrarCount'],
            $counts['readyCount'],
            $counts['scheduledCount'],
            $counts['publishedCount']
        ];

        // Social Media Platform Breakdown
        $platformCountsMap = ['Instagram' => 0, 'LinkedIn' => 0, 'Facebook' => 0, 'Twitter / X' => 0];
        $allPublishedPosts = PublishedPost::all();
        foreach ($allPublishedPosts as $pPost) {
            $plats = $pPost->platforms;
            if (is_array($plats)) {
                foreach ($plats as $platItem) {
                    $cleanPlat = trim(str_replace(['"', '[', ']', '\\'], '', $platItem));
                    if (stripos($cleanPlat, 'inst') !== false) $platformCountsMap['Instagram']++;
                    elseif (stripos($cleanPlat, 'link') !== false) $platformCountsMap['LinkedIn']++;
                    elseif (stripos($cleanPlat, 'face') !== false) $platformCountsMap['Facebook']++;
                    elseif (stripos($cleanPlat, 'twit') !== false || stripos($cleanPlat, 'x') !== false) $platformCountsMap['Twitter / X']++;
                    else $platformCountsMap['Instagram']++;
                }
            } elseif (is_string($plats) && !empty($plats)) {
                if (stripos($plats, 'inst') !== false) $platformCountsMap['Instagram']++;
                if (stripos($plats, 'link') !== false) $platformCountsMap['LinkedIn']++;
                if (stripos($plats, 'face') !== false) $platformCountsMap['Facebook']++;
                if (stripos($plats, 'twit') !== false || stripos($plats, 'x') !== false) $platformCountsMap['Twitter / X']++;
            } else {
                $platformCountsMap['Instagram']++;
            }
        }
        if (array_sum($platformCountsMap) === 0) {
            $platformCountsMap = ['Instagram' => 1, 'LinkedIn' => 1, 'Facebook' => 1, 'Twitter / X' => 1];
        }
        $platformLabels = array_keys($platformCountsMap);
        $platformCounts = array_values($platformCountsMap);

        // Monthly Published Output (Last 6 Months)
        $mediaMonthlyLabels = [];
        $mediaMonthlyCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $mediaMonthlyLabels[] = $month->format('M Y');
            $mediaMonthlyCounts[] = ActivitySubmission::where('status', 'published')
                ->whereYear('updated_at', $month->year)
                ->whereMonth('updated_at', $month->month)
                ->count();
        }

        return view('media.dashboard', array_merge($counts, compact(
            'pendingEditorQueue', 'lockedRegistrarQueue', 'readyForPublication', 'scheduledPosts', 'recentlyPublished', 'currentUser', 'activeTab',
            'mediaStageLabels', 'mediaStageCounts', 'platformLabels', 'platformCounts', 'mediaMonthlyLabels', 'mediaMonthlyCounts'
        )));
    }

    public function editingQueue(Request $request)
    {
        $request->merge(['tab' => 'editing']);
        return $this->dashboard($request);
    }

    public function readyQueue(Request $request)
    {
        $request->merge(['tab' => 'ready']);
        return $this->dashboard($request);
    }

    public function approved()
    {
        $request = request();
        $request->merge(['tab' => 'published']);
        return $this->dashboard($request);
    }

    public function submitToRegistrar(Request $request, $id)
    {
        $currentUser = Auth::user();
        $this->checkMediaEditorAccess($currentUser);
        $submission = ActivitySubmission::findOrFail($id);

        if ($submission->status === 'registrar_approval') {
            return redirect()->back()->with('error', 'LOCKED: This activity has already been submitted to the Office of the Registrar (officeregistrar@vmrfdu.edu.in) and cannot be modified until reviewed.');
        }

        if (!in_array($submission->status, ['media_review', 'editor_discuss'])) {
            return redirect()->back()->with('error', 'Only submissions in media review or returned for correction can be submitted to the Office of the Registrar.');
        }

        $request->validate([
            'social_media_caption' => 'required|string',
            'edited_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20480',
            'edited_photos' => 'required|array|min:1',
            'edited_photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:20480',
            'editor_notes' => 'nullable|string',
        ], [
            'edited_photo.max' => 'The file size must not exceed 20 MB.',
            'edited_photos.*.max' => 'Each file size must not exceed 20 MB.',
            'edited_photos.required' => 'You must upload at least one Edited / Optimized Photo for Social Media.',
        ]);

        $existingImageCount = $submission->media()->where('file_type', 'image')->count();
        if ($existingImageCount === 0 && !$request->hasFile('edited_photo') && !$request->hasFile('edited_photos')) {
            return redirect()->back()->with('error', 'Please upload at least one image before submitting for approval.');
        }

        $oldStatus = $submission->status;
        $submission->social_media_caption = $request->social_media_caption;

        // Collect all edited photos (single or multiple)
        $filesToProcess = [];
        if ($request->hasFile('edited_photo')) {
            $filesToProcess[] = $request->file('edited_photo');
        }
        if ($request->hasFile('edited_photos')) {
            foreach ($request->file('edited_photos') as $f) {
                $filesToProcess[] = $f;
            }
        }

        // If Editor uploads edited/optimized photos, save them under separate 'edited_social' section
        if (count($filesToProcess) > 0) {
            // Unset previous primary flag on all images so the newest edited photo becomes primary
            ActivityMedia::where('activity_submission_id', $submission->id)->update(['is_primary' => false]);

            $isFirstEdited = true;
            foreach ($filesToProcess as $imageFile) {
                $originalName = $imageFile->getClientOriginalName();
                $mimeType = $imageFile->getClientMimeType();
                $fileSize = $imageFile->getSize() ?: 0;
                $filename = time() . '_' . uniqid() . '_edited_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $imageFile->getClientOriginalExtension();
                $storedPath = UploadStorage::put($imageFile, UploadStorage::path('images'), $filename);

                ActivityMedia::create([
                    'activity_submission_id' => $submission->id,
                    'file_type' => 'image',
                    'media_category' => 'edited_social',
                    'file_path' => $storedPath,
                    'file_name' => 'Edited: ' . $originalName,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize,
                    'is_primary' => $isFirstEdited,
                ]);
                $isFirstEdited = false;
            }
        }

        $submission->status = 'registrar_approval';
        $submission->current_stage = 'Office of the Registrar Approval';
        $submission->save();

        $remarks = $request->filled('editor_notes') 
            ? $request->input('editor_notes') 
            : 'Edited and prepared by Media Editor. Submitted for final Registrar approval.';

        ApprovalLog::create([
            'activity_submission_id' => $submission->id,
            'user_id' => $currentUser ? $currentUser->id : null,
            'action' => 'submitted to Registrar',
            'from_status' => $oldStatus,
            'to_status' => 'registrar_approval',
            'remarks' => $remarks,
        ]);

        ActivityDiscussion::create([
            'activity_submission_id' => $submission->id,
            'user_id' => $currentUser ? $currentUser->id : null,
            'comment' => "[Submitted to Office of the Registrar]: " . $remarks,
            'is_internal' => false,
        ]);

        NotificationService::notifyStageChange($submission, 'submitted to Registrar', $remarks);

        return redirect()->route('media.dashboard')
            ->with('success', "Photo & caption submitted to the Office of the Registrar for approval!");
    }

    public function approveDirectly(Request $request, $id)
    {
        $currentUser = Auth::user();
        $this->checkMediaEditorAccess($currentUser);
        $submission = ActivitySubmission::findOrFail($id);

        if (!in_array($submission->status, ['media_review', 'editor_discuss'])) {
            return redirect()->back()->with('error', 'Only submissions in media review or returned for correction can be directly published.');
        }

        $request->validate([
            'platforms' => 'required|array',
            'platforms.*' => 'string',
            'post_url_instagram' => 'required_if:platforms.*,Instagram|url|nullable',
            'post_url_facebook' => 'required_if:platforms.*,Facebook|url|nullable',
            'post_url_linkedin' => 'required_if:platforms.*,LinkedIn|url|nullable',
            'post_url_twitter' => 'required_if:platforms.*,X (Twitter)|url|nullable',
            'remarks' => 'nullable|string'
        ]);

        $oldStatus = $submission->status;
        $submission->status = 'published';
        $submission->current_stage = 'Published Live';
        $submission->save();
        
        $caption = $submission->social_media_caption ?: \App\Http\Controllers\ActivitySubmissionController::generateDefaultCaptionForSubmission($submission);

        \App\Models\PublishedPost::updateOrCreate(
            ['activity_submission_id' => $submission->id],
            [
                'media_editor_id' => $currentUser ? $currentUser->id : null,
                'platforms' => $request->platforms,
                'published_caption' => $caption,
                'published_at' => now(),
                'post_url_instagram' => in_array('Instagram', $request->platforms) ? $request->post_url_instagram : null,
                'post_url_facebook' => in_array('Facebook', $request->platforms) ? $request->post_url_facebook : null,
                'post_url_linkedin' => in_array('LinkedIn', $request->platforms) ? $request->post_url_linkedin : null,
                'post_url_twitter' => in_array('X (Twitter)', $request->platforms) ? $request->post_url_twitter : null,
            ]
        );

        $remarks = 'Media Package Reviewed & Published';

        ApprovalLog::create([
            'activity_submission_id' => $submission->id,
            'user_id' => $currentUser ? $currentUser->id : null,
            'action' => 'published',
            'from_status' => $oldStatus,
            'to_status' => 'published',
            'remarks' => $remarks,
        ]);

        ActivityDiscussion::create([
            'activity_submission_id' => $submission->id,
            'user_id' => $currentUser ? $currentUser->id : null,
            'comment' => "[Media Desk Published]: " . $remarks,
            'is_internal' => false,
        ]);

        NotificationService::notifyStageChange($submission, 'published', $remarks);

        return redirect()->route('media.dashboard')
            ->with('success', "Activity published directly to social media channels!");
    }

    public function publish(Request $request, $id)
    {
        $currentUser = Auth::user();
        $this->checkMediaEditorAccess($currentUser);
        $submission = ActivitySubmission::findOrFail($id);

        // Require registrar approval before publishing
        if (!in_array($submission->status, ['ready_to_publish', 'scheduled'])) {
            return redirect()->back()->with('error', 'Access Control: Submissions must be approved by the Office of the Registrar before broadcasting to social media.');
        }

        $request->validate([
            'platforms' => 'required|array',
            'platforms.*' => 'string',
            'post_url_instagram' => 'required_if:platforms.*,Instagram|url|nullable',
            'post_url_facebook' => 'required_if:platforms.*,Facebook|url|nullable',
            'post_url_linkedin' => 'required_if:platforms.*,LinkedIn|url|nullable',
            'post_url_twitter' => 'required_if:platforms.*,X (Twitter)|url|nullable',
            'remarks' => 'nullable|string'
        ]);

        $newStatus = 'published';

        $submission->status = $newStatus;
        $submission->current_stage = 'Published Live';
        $submission->save();

        $caption = $submission->social_media_caption ?: \App\Http\Controllers\ActivitySubmissionController::generateDefaultCaptionForSubmission($submission);

        // Create or update PublishedPost
        $post = PublishedPost::updateOrCreate(
            ['activity_submission_id' => $submission->id],
            [
                'media_editor_id' => $currentUser ? $currentUser->id : null,
                'platforms' => $request->platforms,
                'published_caption' => $caption,
                'published_at' => now(),
                'post_url_instagram' => in_array('Instagram', $request->platforms) ? $request->post_url_instagram : null,
                'post_url_facebook' => in_array('Facebook', $request->platforms) ? $request->post_url_facebook : null,
                'post_url_linkedin' => in_array('LinkedIn', $request->platforms) ? $request->post_url_linkedin : null,
                'post_url_twitter' => in_array('X (Twitter)', $request->platforms) ? $request->post_url_twitter : null,
            ]
        );

        $remarks = 'Activity Published – Social Media Details Updated';

        ApprovalLog::create([
            'activity_submission_id' => $submission->id,
            'user_id' => $currentUser ? $currentUser->id : null,
            'action' => $newStatus,
            'from_status' => 'ready_to_publish',
            'to_status' => $newStatus,
            'remarks' => $remarks,
        ]);

        if ($request->filled('remarks')) {
            \App\Models\ActivityDiscussion::create([
                'activity_submission_id' => $submission->id,
                'user_id' => $currentUser ? $currentUser->id : null,
                'comment' => "[Social Media Desk Remarks]: " . $request->remarks,
                'is_internal' => false,
            ]);
        }

        NotificationService::notifyStageChange($submission, $newStatus, $remarks);

        return redirect()->route('submissions.show', $submission->id)
            ->with('success', "Activity successfully published! Social media details recorded.");
    }

    public function downloadActivityPack($id)
    {
        $currentUser = Auth::user();
        $this->checkMediaEditorAccess($currentUser);
        $submission = ActivitySubmission::with(['college', 'media'])->findOrFail($id);

        $zipFileName = 'Activity_Pack_' . $submission->reference_id . '_' . time() . '.zip';
        $tempPath = storage_path('app/' . $zipFileName);

        $zip = new \ZipArchive();
        if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            // Create summary text document
            $summaryContent = "=========================================================\n";
            $summaryContent .= "   VMRF(DU) INSTITUTIONAL ACTIVITY DATA SHEET\n";
            $summaryContent .= "=========================================================\n\n";
            $summaryContent .= "Reference ID     : " . $submission->reference_id . "\n";
            $summaryContent .= "Title            : " . $submission->title . "\n";
            $summaryContent .= "Constituent Unit : " . ($submission->college ? $submission->college->name . ' (' . $submission->college->code . ')' : 'N/A') . "\n";
            $summaryContent .= "Event Category   : " . $submission->event_category . "\n";
            $summaryContent .= "Activity Date    : " . $submission->activity_date->format('d M Y') . "\n";
            $summaryContent .= "Participants     : " . ($submission->participants_count ?: 'N/A') . "\n";
            $summaryContent .= "Current Stage    : " . $submission->current_stage . "\n";
            $summaryContent .= "Status           : " . strtoupper($submission->status) . "\n\n";
            $summaryContent .= "---------------------------------------------------------\n";
            $summaryContent .= "DESCRIPTION:\n";
            $summaryContent .= "---------------------------------------------------------\n";
            $summaryContent .= $submission->description . "\n\n";
            $summaryContent .= "---------------------------------------------------------\n";
            $summaryContent .= "SOCIAL MEDIA CAPTION / BROADCAST TEXT:\n";
            $summaryContent .= "---------------------------------------------------------\n";
            $summaryContent .= ($submission->social_media_caption ?: $submission->title) . "\n\n";

            if ($submission->admin_remarks) {
                $summaryContent .= "---------------------------------------------------------\n";
                $summaryContent .= "REGISTRAR / REMARKS:\n";
                $summaryContent .= "---------------------------------------------------------\n";
                $summaryContent .= $submission->admin_remarks . "\n\n";
            }

            $zip->addFromString('Activity_Data_Sheet_' . $submission->reference_id . '.txt', $summaryContent);

            // Add media files if they exist on server disk
            if ($submission->media && $submission->media->count() > 0) {
                foreach ($submission->media as $media) {
                    $contents = UploadStorage::get($media->file_path);
                    if ($contents !== null) {
                        $cleanFileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $media->file_name);
                        $zip->addFromString('media_files/' . $cleanFileName, $contents);
                    }
                }
            }

            $zip->close();
        }

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    public function downloadQueueCsv(Request $request)
    {
        $currentUser = Auth::user();
        $this->checkMediaEditorAccess($currentUser);

        $stage = $request->get('stage', 'editing');
        $query = ActivitySubmission::with(['college']);

        if ($stage === 'editing') {
            $query->whereIn('status', ['media_review', 'editor_discuss']);
            $filename = 'Stage1_Editing_Queue_' . date('Y-m-d') . '.csv';
        } elseif ($stage === 'ready') {
            $query->where('status', 'ready_to_publish');
            $filename = 'Stage2_Ready_Broadcast_Queue_' . date('Y-m-d') . '.csv';
        } else {
            $query->whereIn('status', ['published', 'scheduled']);
            $filename = 'Published_Broadcasts_Archive_' . date('Y-m-d') . '.csv';
        }

        $items = $query->orderBy('updated_at', 'desc')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Reference ID', 'Title', 'Constituent College', 'Category', 'Activity Date', 'Participants Count', 'Status', 'Social Media Caption', 'Description'];

        $callback = function() use ($items, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($items as $item) {
                fputcsv($file, [
                    $item->reference_id,
                    $item->title,
                    $item->college ? $item->college->code : 'N/A',
                    $item->event_category,
                    $item->activity_date ? $item->activity_date->format('Y-m-d') : '',
                    $item->participants_count,
                    $item->status,
                    $item->social_media_caption,
                    $item->description
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
