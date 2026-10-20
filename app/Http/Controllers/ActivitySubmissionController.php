<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\UploadStorage;
use App\Models\ActivitySubmission;
use App\Models\ActivityMedia;
use App\Models\College;
use App\Models\EventCategory;
use App\Models\ApprovalLog;
use App\Models\ActivityDiscussion;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ActivitySubmissionController extends Controller
{
    private function checkSubmissionAccess($user, $submission)
    {
        if ($user && !in_array($user->role, ['super_admin', 'registrar', 'office_registrar', 'vc', 'pro_vc', 'media_editor'])) {
            if ($user->college_id && $submission->college_id !== $user->college_id) {
                // Check if user has global approved view permission
                $isApprovedStatus = in_array($submission->status, ['media_review', 'scheduled', 'published']);
                if ($user->hasPermission('activities.view_approved_all') && $isApprovedStatus) {
                    return; // Allow access
                }

                // Check if user has enterprise-wide approvals or activities view permission
                if ($user->hasPermission('approvals.view') || $user->hasPermission('activities.view')) {
                    return; // Allow access for enterprise roles
                }

                abort(403, 'Unauthorized access: You are only permitted to view and manage activities belonging to your assigned constituent college or institution.');
            }
        }
    }

    public function index(Request $request)
    {
        $currentUser = Auth::user();

        $query = ActivitySubmission::with(['college', 'user']);
        $activeTab = $request->input('tab', 'my_college');

        // Strict Role & Institution-based scope: Super Admin and University Officials can view activities from all colleges
        $canViewAllColleges = $currentUser && ($currentUser->isSuperAdmin() || in_array($currentUser->role, ['super_admin', 'registrar', 'office_registrar', 'vc', 'pro_vc', 'media_editor']));
        
        if ($activeTab === 'approved_all' && $currentUser && $currentUser->hasPermission('activities.view_approved_all')) {
            $query->whereIn('status', ['media_review', 'scheduled', 'published']);
            if ($request->filled('college_id') && $request->college_id !== 'all') {
                $query->where('college_id', $request->college_id);
            }
        } else {
            if (!$canViewAllColleges) {
                if ($currentUser && $currentUser->college_id) {
                    $query->where('college_id', $currentUser->college_id);
                } elseif ($currentUser) {
                    $query->where('user_id', $currentUser->id);
                }
            } elseif ($request->filled('college_id') && $request->college_id !== 'all') {
                // College Filtering for Super Admin
                $query->where('college_id', $request->college_id);
            }

            // Status Filtering
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }
        }
        // Category Filtering
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('event_category', $request->category);
        }
        // Date-Wise Submission & Tracking Filters
        if ($request->filled('from_date')) {
            $query->whereDate('activity_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('activity_date', '<=', $request->to_date);
        }
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('reference_id', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Date-wise tracking order
        $submissions = $query->orderBy('activity_date', 'desc')->paginate(12);
        
        $colleges = Cache::remember('active_colleges', 3600, function () {
            return College::where('is_active', true)->orderBy('name')->get();
        });
        $categories = Cache::remember('active_categories', 3600, function () {
            return EventCategory::where('is_active', true)->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();
        });

        return view('submissions.index', compact('submissions', 'colleges', 'categories', 'currentUser'));
    }

    public function create()
    {
        $currentUser = Auth::user();
        $colleges = Cache::remember('active_colleges', 3600, function () {
            return College::where('is_active', true)->orderBy('name')->get();
        });
        $categories = Cache::remember('active_categories', 3600, function () {
            return EventCategory::where('is_active', true)->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();
        });
        return view('submissions.create', compact('colleges', 'categories', 'currentUser'));
    }

    /**
     * AJAX endpoint: receives a single photo, moves it to a temp holding area, and
     * returns a token referencing it so the form can attach it on final submission.
     * This lets the browser show real per-file upload progress via XHR.
     */
    public function uploadMediaPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:20480',
        ]);

        $photo = $request->file('photo');
        $token = (string) Str::uuid();
        $filename = $token . '.' . $photo->getClientOriginalExtension();
        $storedPath = UploadStorage::put($photo, UploadStorage::path('tmp'), $filename);

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'file_name' => $photo->getClientOriginalName(),
            'url' => UploadStorage::url($storedPath),
        ]);
    }

    private function movePendingMediaPhotos(ActivitySubmission $submission, array $tokens)
    {
        foreach ($tokens as $token) {
            if (!preg_match('/^[a-f0-9\-]{36}$/i', $token)) {
                continue;
            }
            $matches = UploadStorage::findByPrefix(UploadStorage::path('tmp'), $token);
            if (empty($matches)) {
                continue;
            }
            $tmpPath = $matches[0];
            $extension = pathinfo($tmpPath, PATHINFO_EXTENSION);
            $filename = time() . '_media_' . Str::random(8) . '.' . $extension;
            $finalPath = UploadStorage::move($tmpPath, UploadStorage::path('images') . '/' . $filename);

            ActivityMedia::create([
                'activity_submission_id' => $submission->id,
                'file_type' => 'image',
                'media_category' => 'event_photo',
                'file_path' => $finalPath,
                'file_name' => basename($tmpPath),
                'mime_type' => UploadStorage::mimeType($finalPath),
                'file_size' => UploadStorage::size($finalPath),
                'dimensions' => null,
                'is_primary' => false,
            ]);
        }
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();

        $mediaTokens = array_values(array_filter((array) $request->input('media_photo_tokens', [])));

        $rules = [
            'title' => 'required|string|max:255',
            'activity_type' => 'required|in:Past Event,Future Event',
            'event_category' => 'required|string',
            'activity_date' => 'required|date',
            'resource_person_name' => 'nullable|string|max:255',
            'college_id' => 'required|exists:colleges,id',
            'location' => 'required|string|max:255',
            'video_url' => 'nullable|string|max:1000',
            'photo_links' => 'nullable|string|max:1000',
            'support_documents' => 'nullable|array',
            'support_documents.*' => 'file|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,csv|max:20480',
            'report_document' => 'nullable|file|mimes:pdf,doc,docx|max:20480',
            'media_photo_tokens' => $request->activity_type === 'Past Event' ? 'required|array|min:1' : 'nullable|array',
        ];

        if ($request->activity_type === 'Past Event') {
            $rules['outcome'] = 'required|string';
            $rules['target_audience'] = 'required|string|max:255';
            $rules['description'] = 'nullable|string';
            $rules['geo_tagged_photo'] = 'nullable|array';
            $rules['geo_tagged_photo.*'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:20480';
        } else {
            $rules['outcome'] = 'nullable|string';
            $rules['target_audience'] = 'nullable|string|max:255';
            $rules['description'] = 'nullable|string';
            $rules['geo_tagged_photo'] = 'nullable|array';
            $rules['geo_tagged_photo.*'] = 'image|mimes:jpeg,png,jpg,webp|max:20480';
        }

        $request->validate($rules, [
            'media_photo_tokens.required' => 'At least one High-Resolution Photo is mandatory for every past activity submission.',
            'document.max' => 'The document/brochure file size must not exceed 20 MB.'
        ]);

        $year = date('Y');
        $latestSubmission = ActivitySubmission::withTrashed()
            ->where('reference_id', 'like', "VMRF-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latestSubmission && preg_match('/VMRF-\d{4}-(\d+)/', $latestSubmission->reference_id, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = ActivitySubmission::withTrashed()->where('reference_id', 'like', "VMRF-{$year}-%")->count() + 1;
        }

        do {
            $refId = sprintf("VMRF-%s-%04d", $year, $nextNumber);
            $nextNumber++;
        } while (ActivitySubmission::withTrashed()->where('reference_id', $refId)->exists());

        $assignedCollegeId = ($currentUser && $currentUser->college_id) ? $currentUser->college_id : $request->college_id;

        $duplicate = ActivitySubmission::where('title', trim($request->title))
            ->where('activity_date', $request->activity_date)
            ->where('college_id', $assignedCollegeId)
            ->where('user_id', $currentUser ? $currentUser->id : 1)
            ->first();

        if ($duplicate && $request->input('submission_action') !== 'draft') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'errors' => ['title' => ['An activity with this identical title and date has already been submitted. Please check your Drafts or Submission History.']]]);
            }
            return redirect()->back()->withInput()->withErrors(['title' => 'An activity with this identical title and date has already been submitted. Please check your Drafts or Submission History.']);
        }

        $desc = $request->description;
        if ($request->activity_type === 'Future Event') {
            $desc = 'N/A - Future Event';
            $outcome = 'N/A - Future Event';
            $target_audience = 'N/A - Future Event';
            $report_type = null;
            $resource_person_name = null;
        } else {
            $desc = $request->description;
            if (empty($desc)) {
                $desc = ($request->report_type === 'By Attach File') ? 'Event report attached as a document.' : 'N/A';
            }
            $outcome = $request->outcome;
            $target_audience = $request->target_audience;
            $report_type = $request->report_type;
            $resource_person_name = $request->resource_person_name;
        }

        $submission = ActivitySubmission::create([
            'workflow_id' => 1,
            'reference_id' => $refId,
            'user_id' => $currentUser ? $currentUser->id : 1,
            'college_id' => $assignedCollegeId,
            'activity_type' => $request->activity_type,
            'title' => $request->title,
            'report_type' => $report_type,
            'description' => $desc,
            'activity_date' => $request->activity_date,
            'event_category' => $request->event_category,
            'resource_person_name' => $resource_person_name,
            'outcome' => $outcome,
            'tags' => null,
            'location' => $request->location,
            'target_audience' => $target_audience,
            'social_media_caption' => $request->filled('social_media_caption') ? trim($request->social_media_caption) : null,
            'status' => 'verification_pending',
            'current_stage' => 'Submitted for Verification',
        ]);

        if (empty($submission->social_media_caption)) {
            $submission->social_media_caption = self::generateDefaultCaptionForSubmission($submission);
            $submission->save();
        }

        // Attach previously AJAX-uploaded High-Resolution Photos
        $this->movePendingMediaPhotos($submission, $mediaTokens);

        // Mark the first attached media photo as primary for downstream display
        $firstMedia = $submission->media()->where('file_type', 'image')->orderBy('id')->first();
        if ($firstMedia) {
            $firstMedia->update(['is_primary' => true]);
        }

        if ($request->hasFile('geo_tagged_photo')) {
            foreach ($request->file('geo_tagged_photo') as $imageFile) {
                $originalName = $imageFile->getClientOriginalName();
                $mimeType = $imageFile->getClientMimeType();
                $fileSize = $imageFile->getSize() ?: 0;
                $filename = time() . '_geo_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $imageFile->getClientOriginalExtension();
                $storedPath = UploadStorage::put($imageFile, UploadStorage::path('images'), $filename);

                ActivityMedia::create([
                    'activity_submission_id' => $submission->id,
                    'file_type' => 'geo_image',
                    'media_category' => 'original',
                    'file_path' => $storedPath,
                    'file_name' => 'Geo-Tagged: ' . $originalName,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize,
                    'dimensions' => null,
                    'is_primary' => false,
                ]);
            }
        }

        if ($request->filled('photo_links')) {
            ActivityMedia::create([
                'activity_submission_id' => $submission->id,
                'file_type' => 'link',
                'media_category' => 'original',
                'file_path' => $request->photo_links,
                'file_name' => 'Event Photo Link (External)',
                'is_primary' => false,
            ]);
        }

        if ($request->filled('video_url')) {
            ActivityMedia::create([
                'activity_submission_id' => $submission->id,
                'file_type' => 'video',
                'media_category' => 'original',
                'file_path' => $request->video_url,
                'file_name' => 'Event Video Link',
                'is_primary' => false,
            ]);
        }

        if ($request->hasFile('support_documents')) {
            foreach ($request->file('support_documents') as $docFile) {
                $originalName = $docFile->getClientOriginalName();
                $mimeType = $docFile->getClientMimeType();
                $fileSize = $docFile->getSize() ?: 0;
                $filename = time() . '_support_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $docFile->getClientOriginalExtension();
                $storedPath = UploadStorage::put($docFile, UploadStorage::path('docs'), $filename);

                ActivityMedia::create([
                    'activity_submission_id' => $submission->id,
                    'file_type' => 'supporting_document',
                    'media_category' => 'original',
                    'file_path' => $storedPath,
                    'file_name' => $originalName,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize,
                    'is_primary' => false,
                ]);
            }
        }

        if ($request->hasFile('report_document')) {
            $repDocFile = $request->file('report_document');
            $originalName = $repDocFile->getClientOriginalName();
            $mimeType = $repDocFile->getClientMimeType();
            $fileSize = $repDocFile->getSize() ?: 0;
            $filename = time() . '_report_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $repDocFile->getClientOriginalExtension();
            $storedPath = UploadStorage::put($repDocFile, UploadStorage::path('docs'), $filename);

            ActivityMedia::create([
                'activity_submission_id' => $submission->id,
                'file_type' => 'report_document',
                'media_category' => 'original',
                'file_path' => $storedPath,
                'file_name' => $originalName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'is_primary' => false,
            ]);
        }

        ApprovalLog::create([
            'activity_submission_id' => $submission->id,
            'user_id' => $currentUser ? $currentUser->id : null,
            'action' => 'submitted',
            'from_status' => null,
            'to_status' => 'verification_pending',
            'remarks' => 'Activity submitted successfully into institutional review queue.',
        ]);

        // Dispatch Email & In-App Notifications
        NotificationService::notifyStageChange($submission, 'submitted', 'New activity submitted by College Admin.');

        $successMessage = "Activity successfully recorded under reference ID {$refId}! Email notifications dispatched.";

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'redirect' => route('submissions.show', $submission->id),
                'message' => $successMessage,
            ]);
        }

        return redirect()->route('submissions.show', $submission->id)
            ->with('success', $successMessage);
    }

    public function show($id)
    {
        $currentUser = Auth::user();
        $submission = ActivitySubmission::with(['college', 'user', 'media', 'approvalLogs.user', 'publishedPost', 'discussions.user'])->findOrFail($id);
        $this->checkSubmissionAccess($currentUser, $submission);
        return view('submissions.show', compact('submission', 'currentUser'));
    }

    public function edit($id)
    {
        $currentUser = Auth::user();
        $submission = ActivitySubmission::with(['college', 'media'])->findOrFail($id);
        $this->checkSubmissionAccess($currentUser, $submission);

        if ($submission->status === 'registrar_approval') {
            return redirect()->route('submissions.show', $submission->id)
                ->with('error', 'LOCKED: This activity is currently under review by the Office of the Registrar (officeregistrar@vmrfdu.edu.in) and cannot be edited.');
        }

        if (!in_array($submission->status, ['discuss', 'sent_back'])) {
            return redirect()->route('submissions.show', $submission->id)
                ->with('error', 'Only submissions marked for Discussion / Correction can be edited.');
        }

        $colleges = Cache::remember('active_colleges', 3600, function () {
            return College::where('is_active', true)->orderBy('name')->get();
        });
        $categories = Cache::remember('active_categories', 3600, function () {
            return EventCategory::where('is_active', true)->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();
        });

        return view('submissions.edit', compact('submission', 'colleges', 'categories', 'currentUser'));
    }

    public function update(Request $request, $id)
    {
        $currentUser = Auth::user();
        $submission = ActivitySubmission::findOrFail($id);
        $this->checkSubmissionAccess($currentUser, $submission);

        if ($submission->status === 'registrar_approval') {
            return redirect()->route('submissions.show', $submission->id)
                ->with('error', 'LOCKED: This activity is currently under review by the Office of the Registrar (officeregistrar@vmrfdu.edu.in) and cannot be edited.');
        }

        if (!in_array($submission->status, ['discuss', 'sent_back'])) {
            return redirect()->route('submissions.show', $submission->id)
                ->with('error', 'Only submissions marked for Discussion / Correction can be edited.');
        }

        $mediaTokens = array_values(array_filter((array) $request->input('new_media_photo_tokens', [])));

        $existingImageCount = $submission->media()->where('file_type', 'image')->count();
        $imagesToRemove = 0;
        if ($request->has('remove_media_ids') && is_array($request->remove_media_ids)) {
            $imagesToRemove = $submission->media()
                ->whereIn('id', $request->remove_media_ids)
                ->where('file_type', 'image')
                ->count();
        }
        $remainingImageCount = $existingImageCount - $imagesToRemove + count($mediaTokens);

        $rules = [
            'college_id' => 'required|exists:colleges,id',
            'title' => 'required|string|max:255',
            'activity_type' => 'required|in:Past Event,Future Event',
            'event_category' => 'required|string|max:100',
            'resource_person_name' => 'nullable|string|max:255',
            'activity_date' => 'required|date',
            'location' => 'required|string|max:255',
            'video_url' => 'nullable|string|max:1000',
            'photo_links' => 'nullable|string|max:1000',
            'support_documents' => 'nullable|array',
            'support_documents.*' => 'file|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,csv|max:20480',
            'report_document' => 'nullable|file|mimes:pdf,doc,docx|max:20480',
            'new_media_photo_tokens' => $remainingImageCount > 0 ? 'nullable|array' : 'required|array|min:1',
        ];

        $existingGeoImageCount = $submission->media()->where('file_type', 'geo_image')->count();
        $geoToRemove = 0;
        if ($request->has('remove_media_ids') && is_array($request->remove_media_ids)) {
            $geoToRemove = $submission->media()
                ->whereIn('id', $request->remove_media_ids)
                ->where('file_type', 'geo_image')
                ->count();
        }
        $remainingGeoCount = $existingGeoImageCount - $geoToRemove;

        if ($request->activity_type === 'Past Event') {
            $rules['outcome'] = 'required|string';
            $rules['target_audience'] = 'required|string|max:255';
            $rules['description'] = 'nullable|string';
            $rules['new_geo_tagged_photo'] = 'nullable|array';
            $rules['new_geo_tagged_photo.*'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:20480';
        } else {
            $rules['outcome'] = 'nullable|string';
            $rules['target_audience'] = 'nullable|string|max:255';
            $rules['description'] = 'nullable|string';
            $rules['new_geo_tagged_photo'] = 'nullable|array';
            $rules['new_geo_tagged_photo.*'] = 'image|mimes:jpeg,png,jpg,webp|max:20480';
        }

        $request->validate($rules, [
            'new_media_photo_tokens.required' => 'At least one High-Resolution Photo is mandatory for every past activity submission.',
            'document.max' => 'The document/brochure file size must not exceed 20 MB.'
        ]);

        $oldStatus = $submission->status;
        if (!$currentUser || !$currentUser->college_id) {
            $submission->college_id = $request->college_id;
        }
        $submission->title = $request->title;
        $submission->activity_type = $request->activity_type;
        $submission->event_category = $request->event_category;
        if ($request->activity_type === 'Future Event') {
            $submission->resource_person_name = null;
            $submission->outcome = 'N/A - Future Event';
            $submission->target_audience = 'N/A - Future Event';
            $submission->report_type = null;
            
            $desc = $request->description ?: 'N/A - Future Event';
            $submission->description = $desc;
        } else {
            $submission->resource_person_name = $request->resource_person_name;
            $submission->outcome = $request->outcome;
            $submission->target_audience = $request->target_audience;
            $submission->report_type = $request->report_type;
            
            $desc = $request->description;
            if (empty($desc)) {
                $desc = ($request->report_type === 'By Attach File') ? 'Event report attached as a document.' : 'N/A';
            }
            $submission->description = $desc;
        }

        $submission->activity_date = Carbon::parse($request->activity_date);
        $submission->location = $request->location;
        $submission->tags = null;

        $submission->social_media_caption = $request->filled('social_media_caption') ? trim($request->social_media_caption) : null;
        if (empty($submission->social_media_caption)) {
            $submission->social_media_caption = self::generateDefaultCaptionForSubmission($submission);
        }

        // Transition status back to pending verification once resubmitted
        $submission->status = 'verification_pending';
        $submission->current_stage = 'Resubmitted for Verification';
        $submission->save();

        // Remove media marked for deletion
        if ($request->has('remove_media_ids') && is_array($request->remove_media_ids)) {
            $mediaToRemove = $submission->media()->whereIn('id', $request->remove_media_ids)->get();
            foreach ($mediaToRemove as $mediaItem) {
                UploadStorage::delete($mediaItem->file_path);
                $mediaItem->delete();
            }
        }

        // Ensure at least one primary image exists if any image exists
        $hasPrimary = $submission->media()->where('file_type', 'image')->where('is_primary', true)->exists();
        if (!$hasPrimary) {
            $firstImg = $submission->media()->where('file_type', 'image')->first();
            if ($firstImg) {
                $firstImg->update(['is_primary' => true]);
                $hasPrimary = true;
            }
        }

        // Handle video link update/removal
        if ($request->has('video_url')) {
            $videoMedia = $submission->media()->where('file_type', 'video')->first();
            if ($request->filled('video_url')) {
                if ($videoMedia) {
                    $videoMedia->update(['file_path' => $request->video_url]);
                } else {
                    ActivityMedia::create([
                        'activity_submission_id' => $submission->id,
                        'file_type' => 'video',
                        'media_category' => 'original',
                        'file_path' => $request->video_url,
                        'file_name' => 'Event Video Link',
                        'is_primary' => false,
                    ]);
                }
            } elseif ($videoMedia) {
                $videoMedia->delete();
            }
        }

        if ($request->has('photo_links')) {
            $linkMedia = $submission->media()->where('file_type', 'link')->first();
            if ($request->filled('photo_links')) {
                if ($linkMedia) {
                    $linkMedia->update(['file_path' => $request->photo_links]);
                } else {
                    ActivityMedia::create([
                        'activity_submission_id' => $submission->id,
                        'file_type' => 'link',
                        'media_category' => 'original',
                        'file_path' => $request->photo_links,
                        'file_name' => 'Event Photo Link (External)',
                        'is_primary' => false,
                    ]);
                }
            } elseif ($linkMedia) {
                $linkMedia->delete();
            }
        }

        if ($request->hasFile('new_geo_tagged_photo')) {
            foreach ($request->file('new_geo_tagged_photo') as $geoImageFile) {
                $originalName = $geoImageFile->getClientOriginalName();
                $mimeType = $geoImageFile->getClientMimeType();
                $fileSize = $geoImageFile->getSize() ?: 0;
                $filename = time() . '_geo_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $geoImageFile->getClientOriginalExtension();
                $storedPath = UploadStorage::put($geoImageFile, UploadStorage::path('images'), $filename);

                ActivityMedia::create([
                    'activity_submission_id' => $submission->id,
                    'file_type' => 'geo_image',
                    'media_category' => 'original',
                    'file_path' => $storedPath,
                    'file_name' => 'Geo-Tagged: ' . $originalName,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize,
                    'dimensions' => null,
                    'is_primary' => false,
                ]);
            }
        }

        // Handle support_documents additions
        if ($request->hasFile('support_documents')) {
            foreach ($request->file('support_documents') as $docFile) {
                $originalName = $docFile->getClientOriginalName();
                $mimeType = $docFile->getClientMimeType();
                $fileSize = $docFile->getSize() ?: 0;
                $filename = time() . '_support_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $docFile->getClientOriginalExtension();
                $storedPath = UploadStorage::put($docFile, UploadStorage::path('docs'), $filename);

                ActivityMedia::create([
                    'activity_submission_id' => $submission->id,
                    'file_type' => 'supporting_document',
                    'media_category' => 'original',
                    'file_path' => $storedPath,
                    'file_name' => $originalName,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize,
                    'is_primary' => false,
                ]);
            }
        }

        if ($request->hasFile('report_document')) {
            $oldReports = $submission->media()->where('file_type', 'report_document')->get();
            foreach ($oldReports as $oldRep) {
                UploadStorage::delete($oldRep->file_path);
                $oldRep->delete();
            }

            $repDocFile = $request->file('report_document');
            $originalName = $repDocFile->getClientOriginalName();
            $mimeType = $repDocFile->getClientMimeType();
            $fileSize = $repDocFile->getSize() ?: 0;
            $filename = time() . '_report_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $repDocFile->getClientOriginalExtension();
            $storedPath = UploadStorage::put($repDocFile, UploadStorage::path('docs'), $filename);

            ActivityMedia::create([
                'activity_submission_id' => $submission->id,
                'file_type' => 'report_document',
                'media_category' => 'original',
                'file_path' => $storedPath,
                'file_name' => $originalName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'is_primary' => false,
            ]);
        }

        // Attach previously AJAX-uploaded High-Resolution Photos
        $this->movePendingMediaPhotos($submission, $mediaTokens);

        if (!$hasPrimary) {
            $firstImg = $submission->media()->where('file_type', 'image')->first();
            if ($firstImg) {
                $firstImg->update(['is_primary' => true]);
            }
        }

        ApprovalLog::create([
            'activity_submission_id' => $submission->id,
            'user_id' => Auth::id(),
            'action' => 'resubmitted',
            'from_status' => $oldStatus,
            'to_status' => $submission->status,
            'remarks' => $request->input('resubmission_remarks', 'Activity edited and resubmitted by user after addressing discuss notes.'),
        ]);

        ActivityDiscussion::create([
            'activity_submission_id' => $submission->id,
            'user_id' => Auth::id(),
            'comment' => "[Resubmitted]: " . $request->input('resubmission_remarks', 'Activity edited and resubmitted by user after addressing corrections.'),
            'is_internal' => false,
        ]);

        NotificationService::notifyStageChange($submission, 'resubmitted', 'Activity updated and resubmitted for verification.');

        $successMessage = 'Activity successfully updated and resubmitted for official verification.';

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'redirect' => route('submissions.show', $submission->id),
                'message' => $successMessage,
            ]);
        }

        return redirect()->route('submissions.show', $submission->id)
            ->with('success', $successMessage);
    }

    public function storeDiscussion(Request $request, $id)
    {
        $currentUser = Auth::user();
        $request->validate([
            'comment' => 'required|string|max:1500',
        ]);

        $submission = ActivitySubmission::findOrFail($id);
        $this->checkSubmissionAccess($currentUser, $submission);

        ActivityDiscussion::create([
            'activity_submission_id' => $submission->id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
            'is_internal' => $request->has('is_internal'),
        ]);

        NotificationService::notifyStageChange($submission, 'discussion_comment', "[Discussion Note by {$currentUser->name} ({$currentUser->role_title})]: " . $request->comment);

        return redirect()->back()->with('success', 'Discussion remark added successfully and notification emails dispatched to all particulars.');
    }

    public static function generateDefaultCaptionForSubmission($submission)
    {
        $title = $submission->title ?: 'Institutional Event';
        $category = $submission->event_category ?: 'Academic Event';
        $tags = $submission->tags ?: 'VMRFDU, Education, Excellence';
        $college = $submission->college ? $submission->college->name : "Vinayaka Mission's Research Foundation";
        $date = $submission->activity_date ? $submission->activity_date->format('d M Y') : date('d M Y');

        $tagList = collect(explode(',', $tags))->filter()->map(fn($t) => '#' . preg_replace('/[^A-Za-z0-9]/', '', trim($t)))->implode(' ');
        if (empty(trim($tagList))) {
            $tagList = '#VMRFDU #HigherEducation #AcademicExcellence';
        }

        $insta = "✨ Highlights from our recent {$category} at {$college}! 🎓\n\n" .
                 "We successfully hosted \"{$title}\" on {$date}, bringing together students, scholars, and institutional leaders to share insights and drive research excellence forward.\n\n" .
                 "📍 Venue: " . ($submission->location ?: $college) . "\n" .
                 "🌟 Category: {$category}\n\n" .
                 "#VMRFDU #HigherEducation #AcademicExcellence #ResearchFoundation {$tagList}";

        $linkedin = "We are pleased to share the highlights of the {$category} titled \"{$title}\", organized by {$college} on {$date}.\n\n" .
                    "At Vinayaka Mission's Research Foundation (Deemed to be University), our continuous commitment is centered around interdisciplinary academic leadership, skill development, and impactful institutional dialogue.\n\n" .
                    "Congratulations to the organizing committee and participants for this commendable initiative.\n\n" .
                    "#VMRFDU #UniversityUpdate #AcademicLeadership #Innovation #Research {$tagList}";

        return "📱 INSTAGRAM SUGGESTION:\n" . $insta . "\n\n----------------------------------------\n\n💼 LINKEDIN / OFFICIAL PR SUGGESTION:\n" . $linkedin;
    }

    public function generateAiCaption(Request $request)
    {
        $title = $request->input('title', 'Institutional Event');
        $category = $request->input('category', 'Academic Event');
        $tags = $request->input('tags', 'VMRFDU, Education, Excellence');
        $college = $request->input('college', "Vinayaka Mission's Research Foundation");

        $tagList = collect(explode(',', $tags))->map(fn($t) => '#' . preg_replace('/[^A-Za-z0-9]/', '', trim($t)))->implode(' ');

        $insta = "✨ Exciting highlights from our recent {$category} at {$college}! 🎓\n\n" .
                 "We successfully hosted \"{$title}\", bringing together students, faculty, and industry leaders to share insights and drive meaningful innovation. Let's continue pushing boundaries in education and research!\n\n" .
                 "📍 Campus: {$college}\n" .
                 "🌟 Category: {$category}\n\n" .
                 "#VMRFDU #HigherEducation #AcademicExcellence #ResearchFoundation {$tagList}";

        $linkedin = "We are pleased to announce the successful conclusion of the {$category} titled \"{$title}\" organized at {$college}.\n\n" .
                    "At Vinayaka Mission's Research Foundation (Deemed to be University), our institutional focus remains centered on fostering interdisciplinary research, professional competency, and collaborative learning environments.\n\n" .
                    "We express our gratitude to our organizers, guest dignitaries, and student participants for their exemplary dedication.\n\n" .
                    "#VMRFDU #UniversityUpdate #AcademicLeadership #Innovation #Research {$tagList}";

        $combined = "📱 INSTAGRAM SUGGESTION:\n" . $insta . "\n\n----------------------------------------\n\n💼 LINKEDIN / OFFICIAL PR SUGGESTION:\n" . $linkedin;

        return response()->json([
            'status' => 'success',
            'caption' => $combined,
            'captions' => [
                'instagram' => $insta,
                'linkedin' => $linkedin
            ]
        ]);
    }
}
