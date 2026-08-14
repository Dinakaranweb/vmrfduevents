<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\SystemSetting;
use App\Models\SystemAuditLog;
use App\Models\EmailLog;
use App\Models\NotificationTemplate;
use App\Models\ActivitySubmission;
use App\Models\ActivityMedia;
use App\Models\User;
use App\Services\NotificationService;

class SuperAdminEmailAndLogsController extends Controller
{
    /**
     * Ensure the authenticated user is a Super Admin.
     */
    private function checkSuperAdminAccess($requiredPermission = null)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }
        
        if ($user->role === 'super_admin') {
            return true;
        }

        if ($requiredPermission && $user->hasPermission($requiredPermission)) {
            return true;
        }

        abort(403, "Permission Denied: Your assigned role ('" . ($user->role_title ?? $user->role) . "') does not have the required explicit privilege ('{$requiredPermission}') to perform this action.");
    }

    /**
     * Log administrative actions to the immutable audit trail.
     */
    private function logAudit($eventType, $targetType, $targetId, $description, $old = null, $new = null)
    {
        try {
            SystemAuditLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'System',
                'user_role' => Auth::user()->role ?? 'super_admin',
                'event_type' => $eventType,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'description' => $description,
                'old_values' => $old ? json_encode($old) : null,
                'new_values' => $new ? json_encode($new) : null,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("SystemAuditLog failed: " . $e->getMessage());
        }
    }

    // =========================================================================
    // 1. COMPREHENSIVE LOGS MANAGEMENT MODULE (/super-admin/logs-hub)
    // =========================================================================
    public function logsIndex(Request $request)
    {
        $this->checkSuperAdminAccess('audits.view');

        $activeTab = $request->get('tab', 'user_activity'); // user_activity, system_activity, file_uploads, errors, emails

        // Tab 1: User Activity Logs (SystemAuditLog)
        $auditQuery = SystemAuditLog::latest();
        if ($request->event_type) {
            $auditQuery->where('event_type', $request->event_type);
        }
        if ($request->user_id) {
            $auditQuery->where('user_id', $request->user_id);
        }
        $auditLogs = $auditQuery->paginate(20, ['*'], 'audit_page')->appends($request->all());

        // Tab 2: System Activity Logs (Parsed from laravel.log or DB events)
        $systemLogEntries = $this->parseLaravelLogEntries(100, false);

        // Tab 3: File Upload Logs (ActivityMedia & Attachments)
        $mediaQuery = ActivityMedia::with('submission.user')->latest();
        if ($request->media_category) {
            $mediaQuery->where('category', $request->media_category);
        }
        $fileLogs = $mediaQuery->paginate(20, ['*'], 'file_page')->appends($request->all());

        // Tab 4: Error Logs (Parsed from laravel.log where level = ERROR/CRITICAL/ALERT/EMERGENCY)
        $errorLogEntries = $this->parseLaravelLogEntries(100, true);

        // Tab 5: Email Logs (EmailLog)
        $emailQuery = EmailLog::with('user')->orderBy('created_at', 'desc');
        if ($request->reference_id) {
            $emailQuery->where('reference_id', 'like', "%{$request->reference_id}%");
        }
        if ($request->recipient_email) {
            $emailQuery->where('recipient_email', 'like', "%{$request->recipient_email}%");
        }
        if ($request->status) {
            $emailQuery->where('status', $request->status);
        }
        if ($request->subject) {
            $emailQuery->where('subject', 'like', "%{$request->subject}%");
        }
        $emailLogs = $emailQuery->paginate(20, ['*'], 'email_page')->appends($request->all());

        $users = User::orderBy('name')->get();

        return view('super_admin.logs_hub', compact(
            'activeTab',
            'auditLogs',
            'systemLogEntries',
            'fileLogs',
            'errorLogEntries',
            'emailLogs',
            'users'
        ));
    }

    /**
     * Parse storage/logs/laravel.log cleanly for System Activity and Error Logs tabs.
     */
    private function parseLaravelLogEntries(int $limit = 100, bool $errorsOnly = false): array
    {
        $logPath = storage_path('logs/laravel.log');
        if (!File::exists($logPath)) {
            return [];
        }

        $lines = array_reverse(file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        $entries = [];
        $pattern = '/^\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\]\s(\w+)\.(\w+):\s(.*)/';

        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $timestamp = $matches[1];
                $env = $matches[2];
                $level = strtoupper($matches[3]);
                $message = $matches[4];

                if ($errorsOnly && !in_array($level, ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'])) {
                    continue;
                }

                $entries[] = [
                    'timestamp' => $timestamp,
                    'env' => $env,
                    'level' => $level,
                    'message' => substr($message, 0, 300) . (strlen($message) > 300 ? '...' : ''),
                ];

                if (count($entries) >= $limit) {
                    break;
                }
            }
        }

        return $entries;
    }

    /**
     * Download or view raw system log content.
     */
    public function getSystemLogContent()
    {
        $this->checkSuperAdminAccess('audits.view');
        $logPath = storage_path('logs/laravel.log');
        if (!File::exists($logPath)) {
            return response('Log file does not exist.', 404);
        }
        return response(File::get($logPath))->header('Content-Type', 'text/plain');
    }

    /**
     * Preview full email body HTML in a modal or standalone view.
     */
    public function previewEmail($id)
    {
        $this->checkSuperAdminAccess('emails.view');
        $log = EmailLog::findOrFail($id);
        return response($log->body_html)->header('Content-Type', 'text/html');
    }

    // =========================================================================
    // 2. DEDICATED DYNAMIC EMAIL MANAGEMENT MODULE (/super-admin/email-hub)
    // =========================================================================
    public function emailHubIndex(Request $request)
    {
        $this->checkSuperAdminAccess('emails.view');
        $activeTab = $request->get('tab', 'branding'); // branding, templates, test, logs

        // Fetch dynamic branding settings
        $settings = [
            'portal_logo' => SystemSetting::getValue('portal_logo'),
            'portal_name' => SystemSetting::getValue('portal_name', "VINAYAKA MISSION'S RESEARCH FOUNDATION"),
            'portal_tagline' => SystemSetting::getValue('portal_tagline', "(Deemed to be University) • Salem • Chennai • Puducherry"),
            'email_header_text' => SystemSetting::getValue('email_header_text', "VMRFDU Official Portal"),
            'email_header_subtext' => SystemSetting::getValue('email_header_subtext', "Centralized Activity Collection & Media Broadcast System"),
            'email_primary_color' => SystemSetting::getValue('email_primary_color', '#1e1b4b'),
            'email_accent_color' => SystemSetting::getValue('email_accent_color', '#f59e0b'),
            'email_contact_email' => SystemSetting::getValue('email_contact_email', 'support@vmrfdu.edu.in'),
            'email_contact_phone' => SystemSetting::getValue('email_contact_phone', '+91 427 2398999'),
            'email_contact_address' => SystemSetting::getValue('email_contact_address', 'NH-44, Sankari Main Road, Ariyanoor, Salem - 636308, Tamil Nadu'),
            'email_signature' => SystemSetting::getValue('email_signature', "Best regards,<br><strong>VMRFDU Administration</strong>"),
            'email_social_facebook' => SystemSetting::getValue('email_social_facebook', 'https://facebook.com/vmrfdu'),
            'email_social_twitter' => SystemSetting::getValue('email_social_twitter', 'https://twitter.com/vmrfdu'),
            'email_social_instagram' => SystemSetting::getValue('email_social_instagram', 'https://instagram.com/vmrfdu'),
            'email_social_linkedin' => SystemSetting::getValue('email_social_linkedin', 'https://linkedin.com/school/vmrfdu'),
            'email_footer_text' => SystemSetting::getValue('email_footer_text', "© 2026 Vinayaka Mission's Research Foundation (DU). All rights reserved."),
            'email_logo_embed_mode' => SystemSetting::getValue('email_logo_embed_mode', 'cid'), // cid or url
            'email_greeting_prefix' => SystemSetting::getValue('email_greeting_prefix', 'Dear'),
            'email_single_intro_text' => SystemSetting::getValue('email_single_intro_text', 'We are writing to notify you regarding an official status update and institutional action recorded for Activity Reference ID:'),
            'email_consolidated_intro_text' => SystemSetting::getValue('email_consolidated_intro_text', 'Multiple updates and institutional actions have been recorded for your submission during this processing cycle. To keep your inbox organized, we have consolidated all related updates into this single comprehensive report below.'),
            'email_overview_heading' => SystemSetting::getValue('email_overview_heading', 'Submission Overview & Identifiers'),
            'email_label_ref_id' => SystemSetting::getValue('email_label_ref_id', 'Reference ID:'),
            'email_label_title' => SystemSetting::getValue('email_label_title', 'Activity Title:'),
            'email_label_college' => SystemSetting::getValue('email_label_college', 'College / Campus:'),
            'email_label_date' => SystemSetting::getValue('email_label_date', 'Event Date:'),
            'email_label_category' => SystemSetting::getValue('email_label_category', 'Category:'),
            'email_label_stage' => SystemSetting::getValue('email_label_stage', 'Current Stage:'),
            'email_timeline_heading' => SystemSetting::getValue('email_timeline_heading', 'Recorded Actions & Timeline'),
            'email_remarks_heading' => SystemSetting::getValue('email_remarks_heading', 'Authority / Editor Guidance Notes:'),
            'email_cta_button_text' => SystemSetting::getValue('email_cta_button_text', 'View Official Activity Record & All Updates →'),
            'email_cta_help_text' => SystemSetting::getValue('email_cta_help_text', 'Click the button above to access full activity attachments, media packages, and official action logs.'),
            'email_footer_system_text' => SystemSetting::getValue('email_footer_system_text', 'Official Institutional Activity Collection & Media Broadcasting System'),
        ];

        // Fetch templates
        $templates = NotificationTemplate::orderBy('event_key')->get();

        // Ensure default key templates exist if empty
        if ($templates->isEmpty()) {
            $this->seedDefaultTemplates();
            $templates = NotificationTemplate::orderBy('event_key')->get();
        }

        // Fetch email logs with filtering
        $emailQuery = EmailLog::with('user')->orderBy('created_at', 'desc');
        if ($request->reference_id) {
            $emailQuery->where('reference_id', 'like', "%{$request->reference_id}%");
        }
        if ($request->recipient_email) {
            $emailQuery->where('recipient_email', 'like', "%{$request->recipient_email}%");
        }
        if ($request->status) {
            $emailQuery->where('status', $request->status);
        }
        if ($request->subject) {
            $emailQuery->where('subject', 'like', "%{$request->subject}%");
        }
        $emailLogs = $emailQuery->paginate(15, ['*'], 'email_page')->appends($request->all());

        return view('super_admin.email_hub', compact('activeTab', 'settings', 'templates', 'emailLogs'));
    }

    /**
     * Update dynamic email branding, logo, colors, contact details, and signature.
     */
    public function updateBranding(Request $request)
    {
        $this->checkSuperAdminAccess('settings.edit');

        // Handle logo file upload
        if ($request->hasFile('portal_logo_file')) {
            $request->validate([
                'portal_logo_file' => 'image|mimes:jpeg,png,jpg,webp|max:3072'
            ]);

            $file = $request->file('portal_logo_file');
            $filename = 'portal_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/portal'), $filename);
            
            $relativePath = 'uploads/portal/' . $filename;
            SystemSetting::setValue('portal_logo', $relativePath, 'branding', 'Organization Header Logo');
        }

        // Save all other branding fields
        $fields = [
            'portal_name' => 'Organization Name',
            'portal_tagline' => 'Organization Tagline',
            'email_header_text' => 'Header Title Text',
            'email_header_subtext' => 'Header Subtitle Text',
            'email_primary_color' => 'Theme Primary Color',
            'email_accent_color' => 'Theme Accent Color',
            'email_contact_email' => 'Contact Email Address',
            'email_contact_phone' => 'Contact Phone Number',
            'email_contact_address' => 'Physical Address Details',
            'email_signature' => 'Email Signature HTML',
            'email_social_facebook' => 'Facebook URL',
            'email_social_twitter' => 'Twitter/X URL',
            'email_social_instagram' => 'Instagram URL',
            'email_social_linkedin' => 'LinkedIn URL',
            'email_footer_text' => 'Footer Attribution & Copyright',
            'email_logo_embed_mode' => 'Logo Rendering Mode (cid/url)',
            'email_greeting_prefix' => 'Email Salutation Greeting Prefix',
            'email_single_intro_text' => 'Single Update Intro Paragraph',
            'email_consolidated_intro_text' => 'Consolidated Updates Intro Paragraph',
            'email_overview_heading' => 'Submission Overview Heading',
            'email_label_ref_id' => 'Reference ID Label',
            'email_label_title' => 'Activity Title Label',
            'email_label_college' => 'College/Campus Label',
            'email_label_date' => 'Event Date Label',
            'email_label_category' => 'Category Label',
            'email_label_stage' => 'Current Stage Label',
            'email_timeline_heading' => 'Timeline Heading',
            'email_remarks_heading' => 'Editor Guidance Notes Heading',
            'email_cta_button_text' => 'CTA Button Text',
            'email_cta_help_text' => 'CTA Footnote Text',
            'email_footer_system_text' => 'Footer System Description Text',
        ];

        foreach ($fields as $key => $desc) {
            if ($request->has($key)) {
                SystemSetting::setValue($key, $request->get($key), 'email_branding', $desc);
            }
        }

        $this->logAudit('general', 'SystemSetting', 0, "Updated dynamic organization branding and email template configurations.");

        return redirect()->route('superadmin.emailHub', ['tab' => 'branding'])->with('success', 'Organization branding, logo, and email theme settings updated successfully!');
    }

    /**
     * Create a new custom notification/email template.
     */
    public function storeTemplate(Request $request)
    {
        $this->checkSuperAdminAccess('settings.edit');

        $validated = $request->validate([
            'event_key' => 'required|string|unique:notification_templates,event_key|max:255',
            'name' => 'required|string|max:255',
            'email_subject' => 'required|string',
            'email_body' => 'required|string',
            'system_title' => 'nullable|string',
            'system_message' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $template = NotificationTemplate::create($validated);
        $this->logAudit('general', 'NotificationTemplate', $template->id, "Created dynamic email template: {$template->name}");

        return redirect()->route('superadmin.emailHub', ['tab' => 'templates'])->with('success', "Template '{$template->name}' created successfully.");
    }

    /**
     * Update an existing notification/email template.
     */
    public function updateTemplate(Request $request, $id)
    {
        $this->checkSuperAdminAccess('settings.edit');
        $template = NotificationTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email_subject' => 'required|string',
            'email_body' => 'required|string',
            'system_title' => 'nullable|string',
            'system_message' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $template->update($validated);
        $this->logAudit('general', 'NotificationTemplate', $template->id, "Updated dynamic email template: {$template->name}");

        return redirect()->route('superadmin.emailHub', ['tab' => 'templates'])->with('success', "Template '{$template->name}' updated successfully.");
    }

    /**
     * Delete a notification/email template.
     */
    public function deleteTemplate($id)
    {
        $this->checkSuperAdminAccess('settings.edit');
        $template = NotificationTemplate::findOrFail($id);
        $name = $template->name;
        $template->delete();

        $this->logAudit('general', 'NotificationTemplate', $id, "Deleted email template: {$name}");

        return redirect()->route('superadmin.emailHub', ['tab' => 'templates'])->with('success', "Template '{$name}' deleted successfully.");
    }

    /**
     * Preview what a template looks like with current dynamic branding.
     */
    public function previewTemplate($id)
    {
        $this->checkSuperAdminAccess('settings.edit');
        $template = NotificationTemplate::findOrFail($id);

        // Dummy sample data for preview
        $sampleSub = new ActivitySubmission();
        $sampleSub->reference_id = 'VMRF-2026-PREVIEW';
        $sampleSub->title = 'Annual National Academic & Research Symposium 2026';
        $sampleSub->event_category = 'Academic Symposium / Conference';
        $sampleSub->current_stage = 'Registrar Approved';
        $sampleSub->activity_date = now();

        $recipient = (object)[
            'id' => Auth::id(),
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
        ];

        // Replace placeholders in template body
        $bodyText = str_replace(
            ['{reference_id}', '{title}', '{stage}', '{remarks}', '{recipient_name}', '{college_name}', '{activity_date}', '{category}', '{action_url}', '{contact_email}', '{contact_phone}', '{portal_name}'],
            [
                $sampleSub->reference_id,
                $sampleSub->title,
                $sampleSub->current_stage,
                'Excellence in research verified. Proceed with institutional social media broadcast.',
                $recipient->name,
                'Salem Main Campus',
                now()->format('d M, Y'),
                $sampleSub->event_category,
                route('home'),
                SystemSetting::getValue('email_contact_email', 'support@vmrfdu.edu.in'),
                SystemSetting::getValue('email_contact_phone', '+91 427 2398999'),
                SystemSetting::getValue('portal_name', "VINAYAKA MISSION'S RESEARCH FOUNDATION")
            ],
            $template->email_body
        );

        $updates = [
            [
                'title' => $template->name,
                'stage' => $template->event_key,
                'message' => $bodyText,
                'stageColor' => SystemSetting::getValue('email_primary_color', '#1e1b4b'),
                'remarks' => 'Excellence in research verified. Proceed with institutional social media broadcast.',
                'actionUrl' => route('home'),
                'timestamp' => now()->format('h:i A, d M Y')
            ]
        ];

        // Use reflection or helper to call NotificationService private buildConsolidatedHtmlEmail or build dynamic HTML
        $htmlBody = $this->renderDynamicBrandedHtmlEmail($recipient, $sampleSub, $updates, 1, $template->email_subject);

        return response($htmlBody)->header('Content-Type', 'text/html');
    }

    /**
     * Test live email delivery with CID inline logo embedding.
     */
    public function sendTestEmail(Request $request)
    {
        $this->checkSuperAdminAccess('settings.edit');

        $request->validate([
            'test_recipient_email' => 'required|email',
            'test_template_id' => 'nullable|integer',
            'test_remarks' => 'nullable|string',
        ]);

        $recipientEmail = $request->test_recipient_email;
        $remarks = $request->test_remarks ?: 'This is an official test notification generated from the VMRFDU Super Admin Email Management Hub to verify SMTP configuration and inline CID logo rendering.';

        $sampleSub = ActivitySubmission::with('college', 'user')->first();
        if (!$sampleSub) {
            $sampleSub = new ActivitySubmission();
            $sampleSub->reference_id = 'VMRF-TEST-8888';
            $sampleSub->title = 'VMRFDU Institutional Test Event 2026';
            $sampleSub->event_category = 'Institutional Audit & Testing';
            $sampleSub->current_stage = 'Verification & Delivery Check';
            $sampleSub->activity_date = now();
        }

        $recipient = (object)[
            'id' => Auth::id(),
            'name' => Auth::user()->name ?: 'VMRFDU Administrator',
            'email' => $recipientEmail,
        ];

        $subject = "[VMRFDU Portal] TEST DELIVERY - Official Branding & SMTP Verification";
        $messageText = "We are sending this test transmission to confirm that all system-generated emails, branding variables, and dynamic templates are delivered correctly across major email clients.";

        if ($request->test_template_id) {
            $template = NotificationTemplate::find($request->test_template_id);
            if ($template) {
                $subject = "[TEST] " . $template->email_subject;
                $messageText = str_replace(
                    ['{reference_id}', '{title}', '{stage}', '{remarks}', '{recipient_name}', '{college_name}', '{activity_date}', '{category}', '{action_url}', '{contact_email}', '{contact_phone}', '{portal_name}'],
                    [
                        $sampleSub->reference_id,
                        $sampleSub->title,
                        $sampleSub->current_stage,
                        $remarks,
                        $recipient->name,
                        $sampleSub->college ? $sampleSub->college->name : 'Salem Main Campus',
                        now()->format('d M, Y'),
                        $sampleSub->event_category,
                        route('home'),
                        SystemSetting::getValue('email_contact_email', 'support@vmrfdu.edu.in'),
                        SystemSetting::getValue('email_contact_phone', '+91 427 2398999'),
                        SystemSetting::getValue('portal_name', "VINAYAKA MISSION'S RESEARCH FOUNDATION")
                    ],
                    $template->email_body
                );
            }
        }

        $updates = [
            [
                'title' => 'System SMTP & Dynamic Branding Audit',
                'stage' => 'TEST_TRANSMISSION',
                'message' => $messageText,
                'stageColor' => SystemSetting::getValue('email_primary_color', '#1e1b4b'),
                'remarks' => $remarks,
                'actionUrl' => route('home'),
                'timestamp' => now()->format('h:i A, d M Y')
            ]
        ];

        $htmlBody = $this->renderDynamicBrandedHtmlEmail($recipient, $sampleSub, $updates, 1, $subject);

        $status = 'sent';
        $errorMessage = null;

        try {
            Mail::html($htmlBody, function ($msg) use ($recipientEmail, $subject) {
                $msg->to($recipientEmail, 'Test Recipient')
                    ->subject($subject);
            });
        } catch (\Throwable $e) {
            $status = 'failed';
            $errorMessage = $e->getMessage();
            Log::error("Test email dispatch failed to {$recipientEmail}: " . $errorMessage);
        }

        // Record in email_logs
        try {
            EmailLog::create([
                'sender' => SystemSetting::getValue('portal_name', "VINAYAKA MISSION'S RESEARCH FOUNDATION") . ' Notification Desk',
                'recipient_user_id' => Auth::id(),
                'recipient_email' => $recipientEmail,
                'reference_id' => $sampleSub->reference_id,
                'subject' => $subject,
                'stage' => 'TEST_DELIVERY',
                'body_html' => $htmlBody,
                'status' => $status,
                'error_message' => $errorMessage,
            ]);
        } catch (\Throwable $e) {
            Log::warning("Could not create email_log for test email: " . $e->getMessage());
        }

        if ($status === 'sent') {
            return redirect()->route('superadmin.emailHub', ['tab' => 'test'])->with('success', "Test email successfully delivered to {$recipientEmail}! Check your inbox (or spam/junk folder) and the Email Logs tab.");
        } else {
            return redirect()->route('superadmin.emailHub', ['tab' => 'test'])->with('error', "Test email delivery failed: {$errorMessage}. A failure record has been saved in Email Logs.");
        }
    }

    /**
     * Render dynamic, fully branded HTML email respecting all SystemSetting variables and logo modes.
     */
    private function renderDynamicBrandedHtmlEmail($recipient, ActivitySubmission $sub, array $updates, int $updateCount, string $subject): string
    {
        $dateStr = $sub->activity_date ? $sub->activity_date->format('d M, Y') : 'N/A';
        $colCode = $sub->college ? $sub->college->code : 'Central';
        $colName = $sub->college ? $sub->college->name : 'Central Administration';
        $actionUrl = $updates[0]['actionUrl'] ?? route('home');

        // Retrieve dynamic branding
        $logoSetting = SystemSetting::getValue('portal_logo');
        $portalName = SystemSetting::getValue('portal_name', "VINAYAKA MISSION'S RESEARCH FOUNDATION");
        $portalTagline = SystemSetting::getValue('portal_tagline', "(Deemed to be University) • Salem • Chennai • Puducherry");
        $primaryColorVal = SystemSetting::getValue('email_primary_color');
        $primaryColor = !empty($primaryColorVal) ? $primaryColorVal : '#1e1b4b';
        $accentColorVal = SystemSetting::getValue('email_accent_color');
        $accentColor = !empty($accentColorVal) ? $accentColorVal : '#f59e0b';
        $contactEmail = SystemSetting::getValue('email_contact_email');
        $contactPhone = SystemSetting::getValue('email_contact_phone');
        $contactAddress = SystemSetting::getValue('email_contact_address');
        $signatureHtml = SystemSetting::getValue('email_signature');
        $footerText = SystemSetting::getValue('email_footer_text');

        // Retrieve fully dynamic email text, labels & headings
        $greetingPrefix = SystemSetting::getValue('email_greeting_prefix', 'Dear');
        $singleIntroTpl = SystemSetting::getValue('email_single_intro_text', 'We are writing to notify you regarding an official status update and institutional action recorded for Activity Reference ID:');
        $consolidatedIntroTpl = SystemSetting::getValue('email_consolidated_intro_text', 'Multiple updates and institutional actions have been recorded for your submission during this processing cycle. To keep your inbox organized, we have consolidated all related updates into this single comprehensive report below.');
        $overviewHeading = SystemSetting::getValue('email_overview_heading', 'Submission Overview & Identifiers');
        $labelRefId = SystemSetting::getValue('email_label_ref_id', 'Reference ID:');
        $labelTitle = SystemSetting::getValue('email_label_title', 'Activity Title:');
        $labelCollege = SystemSetting::getValue('email_label_college', 'College / Campus:');
        $labelDate = SystemSetting::getValue('email_label_date', 'Event Date:');
        $labelCategory = SystemSetting::getValue('email_label_category', 'Category:');
        $labelStage = SystemSetting::getValue('email_label_stage', 'Current Stage:');
        $timelineHeading = SystemSetting::getValue('email_timeline_heading', 'Recorded Actions & Timeline');
        $remarksHeading = SystemSetting::getValue('email_remarks_heading', 'Authority / Editor Guidance Notes:');
        $ctaButtonText = SystemSetting::getValue('email_cta_button_text', 'View Official Activity Record & All Updates →');
        $ctaHelpText = SystemSetting::getValue('email_cta_help_text', 'Click the button above to access full activity attachments, media packages, and official action logs.');
        $footerSystemText = SystemSetting::getValue('email_footer_system_text', 'Official Institutional Activity Collection & Media Broadcasting System');

        // Logo HTML generation with clean spacing and no trailing linebreaks
        $logoHtml = '';
        if (!empty($logoSetting)) {
            $logoUrl = url($logoSetting);
            if (file_exists(public_path($logoSetting))) {
                $logoHtml = "<div style=\"margin-bottom: 10px;\"><img src=\"{$logoUrl}\" alt=\"{$portalName} Logo\" style=\"max-height: 65px; max-width: 230px; object-fit: contain; display: inline-block;\"></div>";
            } else {
                $logoHtml = "<div style=\"margin-bottom: 10px;\"><img src=\"{$logoUrl}\" alt=\"{$portalName} Logo\" style=\"max-height: 65px; max-width: 230px; object-fit: contain; display: inline-block;\"></div>";
            }
        }

        $bannerColor = $updateCount === 1 ? ($updates[0]['stageColor'] ?? $primaryColor) : '#4338ca';
        $bannerText = $updateCount === 1
            ? "OFFICIAL NOTIFICATION: " . strtoupper(str_replace('_', ' ', $updates[0]['stage']))
            : "⚡ CONSOLIDATED REPORT ({$updateCount} COMBINED UPDATES FOR REF ID: {$sub->reference_id})";

        $updatesListHtml = '';
        foreach ($updates as $idx => $item) {
            $itemColor = $item['stageColor'] ?? $primaryColor;
            $itemRemarksHtml = '';
            if (!empty($item['remarks'])) {
                $itemRemarksHtml = <<<HTML
                <div style="margin-top: 10px; background-color: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid {$accentColor}; padding: 10px 12px; border-radius: 6px; font-size: 13px; color: #92400e; line-height: 1.5;">
                    <strong style="color: #78350f;">{$remarksHeading}</strong><br>
                    {$item['remarks']}
                </div>
HTML;
            }

            $updatesListHtml .= <<<HTML
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-left: 5px solid {$itemColor}; border-radius: 8px; padding: 14px 16px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 6px;">
                    <tr>
                        <td style="font-size: 14px; font-weight: 700; color: #0f172a; font-family: 'Helvetica Neue', Arial, sans-serif;">
                            {$item['title']}
                        </td>
                        <td align="right" style="font-size: 11px; color: #64748b; font-weight: 600; font-family: 'Helvetica Neue', Arial, sans-serif;">
                            {$item['timestamp']}
                        </td>
                    </tr>
                </table>
                <div style="margin-bottom: 6px;">
                    <span style="display: inline-block; background-color: {$itemColor}; color: #ffffff; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                        Stage: {$item['stage']}
                    </span>
                </div>
                <div style="font-size: 13px; color: #334155; line-height: 1.5; font-family: 'Helvetica Neue', Arial, sans-serif;">
                    {$item['message']}
                </div>
                {$itemRemarksHtml}
            </div>
HTML;
        }

        $introText = $updateCount === 1
            ? trim($singleIntroTpl) . " <strong>{$sub->reference_id}</strong>."
            : trim($consolidatedIntroTpl) . " (Ref ID: <strong>{$sub->reference_id}</strong> - {$updateCount} updates combined).";

        $signatureBoxHtml = '';
        if (!empty(trim((string)$signatureHtml))) {
            $signatureBoxHtml = <<<HTML
            <!-- Signature Box -->
            <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #e2e8f0; font-size: 14px; color: #334155; font-family: 'Helvetica Neue', Arial, sans-serif;">
                {$signatureHtml}
            </div>
HTML;
        }

        $footerTopBlocks = [];
        if (!empty(trim((string)$portalName))) {
            $footerTopBlocks[] = '<div style="font-weight: 700; color: #334155; font-size: 13px; margin-bottom: 4px;">' . trim((string)$portalName) . '</div>';
        }
        $contactParts = [];
        if (!empty(trim((string)$contactAddress))) $contactParts[] = trim((string)$contactAddress);
        if (!empty(trim((string)$contactEmail))) $contactParts[] = "Email: " . trim((string)$contactEmail);
        if (!empty(trim((string)$contactPhone))) $contactParts[] = "Tel: " . trim((string)$contactPhone);
        if (!empty($contactParts)) {
            $footerTopBlocks[] = '<div style="color: #64748b; font-size: 12px; margin-bottom: 4px;">' . implode(' &bull; ', $contactParts) . '</div>';
        }
        if (!empty(trim((string)$footerSystemText))) {
            $footerTopBlocks[] = '<div style="color: #64748b; font-size: 12px; margin-bottom: 4px;">' . trim((string)$footerSystemText) . '</div>';
        }
        $footerTopHtml = implode('', $footerTopBlocks);

        $footerNoticeHtml = '';
        if (!empty(trim((string)$footerText))) {
            $borderStyle = !empty($footerTopBlocks) ? 'border-top: 1px solid #e2e8f0; margin-top: 12px; padding-top: 12px;' : '';
            $footerNoticeHtml = <<<HTML
            <div style="{$borderStyle} font-size: 11px; color: #94a3b8; line-height: 1.5;">
                {$footerText}
            </div>
HTML;
        }

        $footerSectionHtml = '';
        if (!empty($footerTopBlocks) || !empty(trim((string)$footerText))) {
            $footerSectionHtml = <<<HTML
                    <!-- Footer Section -->
                    <tr>
                        <td align="center" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 22px 20px; text-align: center; font-size: 12px; color: #64748b; line-height: 1.5; font-family: 'Helvetica Neue', Arial, sans-serif;">
                            {$footerTopHtml}
                            {$footerNoticeHtml}
                        </td>
                    </tr>
HTML;
        }

        return <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{$subject}</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 20px; color: #0f172a; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f1f5f9; padding: 12px 0;">
        <tr>
            <td align="center">
                <!-- Main Card -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 620px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #cbd5e1; margin: 0 auto; text-align: left;">
                    
                    <!-- Header with Branding & Logo -->
                    <tr>
                        <td align="center" style="background: {$primaryColor}; background: linear-gradient(135deg, {$primaryColor} 0%, #311b92 100%); padding: 24px 20px; text-align: center; border-bottom: 3px solid {$accentColor};">
                            {$logoHtml}
                            <div style="font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 20px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px; margin: 0; text-transform: uppercase;">{$portalName}</div>
                            <div style="font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 13px; font-weight: 600; color: #f8fafc; opacity: 0.9; margin-top: 4px; letter-spacing: 0.5px;">{$portalTagline}</div>
                            <div style="display: inline-block; background-color: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 20px; padding: 3px 12px; font-size: 11px; font-weight: 700; color: {$accentColor}; margin-top: 10px; text-transform: uppercase; letter-spacing: 0.8px;">Centralized Activity &amp; Media Portal</div>
                        </td>
                    </tr>

                    <!-- Status Banner -->
                    <tr>
                        <td align="center" style="background-color: {$bannerColor}; color: #ffffff; padding: 10px 20px; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 0.8px; font-family: 'Helvetica Neue', Arial, sans-serif;">
                            {$bannerText}
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 22px 20px;">
                            <div style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 10px; font-family: 'Helvetica Neue', Arial, sans-serif;">
                                {$greetingPrefix} {$recipient->name},
                            </div>
                            <p style="font-size: 14px; line-height: 1.6; color: #334155; margin: 0 0 20px 0; font-family: 'Helvetica Neue', Arial, sans-serif;">
                                {$introText}
                            </p>

                            <!-- Overview Table Card -->
                            <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                                <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 10px; font-family: 'Helvetica Neue', Arial, sans-serif;">
                                    {$overviewHeading}
                                </div>
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px; font-family: 'Helvetica Neue', Arial, sans-serif;">
                                    <tr>
                                        <td style="padding: 5px 0; width: 140px; font-weight: 600; color: #64748b;">{$labelRefId}</td>
                                        <td style="padding: 5px 0; color: #0f172a;">
                                            <span style="background-color: #e2e8f0; color: #0f172a; font-weight: 700; font-family: monospace; padding: 2px 8px; border-radius: 4px; font-size: 13px;">{$sub->reference_id}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; font-weight: 600; color: #64748b;">{$labelTitle}</td>
                                        <td style="padding: 5px 0; font-weight: 700; color: #0f172a;">{$sub->title}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; font-weight: 600; color: #64748b;">{$labelCollege}</td>
                                        <td style="padding: 5px 0; color: #334155;">{$colName} ({$colCode} Campus)</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; font-weight: 600; color: #64748b;">{$labelDate}</td>
                                        <td style="padding: 5px 0; color: #334155;">{$dateStr}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; font-weight: 600; color: #64748b;">{$labelCategory}</td>
                                        <td style="padding: 5px 0; color: #334155;">{$sub->event_category}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; font-weight: 600; color: #64748b;">{$labelStage}</td>
                                        <td style="padding: 5px 0; font-weight: 700; color: {$primaryColor};">{$sub->current_stage}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Consolidated Updates Log -->
                            <div style="margin-bottom: 20px;">
                                <div style="font-size: 13px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 12px; font-family: 'Helvetica Neue', Arial, sans-serif;">
                                    {$timelineHeading} ({$updateCount})
                                </div>
                                {$updatesListHtml}
                            </div>

                            {$signatureBoxHtml}

                            <!-- CTA Button -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 22px 0 12px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{$actionUrl}" target="_blank" style="display: inline-block; background-color: {$primaryColor}; color: #ffffff !important; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 14px; font-family: 'Helvetica Neue', Arial, sans-serif; box-shadow: 0 4px 8px rgba(30, 27, 75, 0.25);">
                                            {$ctaButtonText}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p align="center" style="font-size: 12px; color: #94a3b8; margin: 6px 0 0 0; font-family: 'Helvetica Neue', Arial, sans-serif;">
                                {$ctaHelpText}
                            </p>
                        </td>
                    </tr>

                    {$footerSectionHtml}

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    /**
     * Seed initial default email templates if empty.
     */
    private function seedDefaultTemplates()
    {
        $defaults = [
            [
                'event_key' => 'submitted',
                'name' => 'New Activity Submission Notification',
                'email_subject' => 'New Activity Submitted for Verification: {reference_id}',
                'email_body' => "Activity {reference_id} ({title}) has been submitted by {college_name} on {activity_date} under category '{category}'. Please log in to verify and approve.",
                'system_title' => 'New Activity Submitted',
                'system_message' => "Activity {reference_id} ({title}) submitted for institutional verification.",
                'is_active' => true,
            ],
            [
                'event_key' => 'approved',
                'name' => 'Activity Approved & Forwarded to Media Desk',
                'email_subject' => 'APPROVED & FORWARDED TO MEDIA DESK: {reference_id}',
                'email_body' => "Activity {reference_id} ({title}) has been formally verified and approved by Institutional Authority. It has been automatically forwarded to the Media Studio Desk for editing.",
                'system_title' => 'Activity Approved',
                'system_message' => "Activity {reference_id} approved. Forwarded to Media Studio Desk.",
                'is_active' => true,
            ],
            [
                'event_key' => 'marked for discussion',
                'name' => 'Activity Returned for Correction / Discussion',
                'email_subject' => 'CORRECTION REQUIRED: {reference_id}',
                'email_body' => "Activity {reference_id} ({title}) has been returned for correction or discussion with guidance notes: \"{remarks}\". Please review and resubmit.",
                'system_title' => 'Returned for Correction',
                'system_message' => "Activity {reference_id} returned for correction with notes: {remarks}",
                'is_active' => true,
            ],
            [
                'event_key' => 'rejected',
                'name' => 'Activity Submission Rejected',
                'email_subject' => 'ACTIVITY REJECTED: {reference_id}',
                'email_body' => "We regret to inform you that Activity {reference_id} ({title}) has been formally rejected by Institutional Authority. Remarks: {remarks}",
                'system_title' => 'Activity Rejected',
                'system_message' => "Activity {reference_id} formally rejected.",
                'is_active' => true,
            ],
            [
                'event_key' => 'approved by Office of the Registrar',
                'name' => 'Registrar Approved - Ready to Publish',
                'email_subject' => 'REGISTRAR APPROVED (Ready to Publish): {reference_id}',
                'email_body' => "The final media package for Activity {reference_id} ({title}) has been formally approved by the Office of the Registrar. Remarks: {remarks}. Media Editor may now publish to official channels.",
                'system_title' => 'Registrar Approved Media Pack',
                'system_message' => "Media package for {reference_id} approved by Registrar.",
                'is_active' => true,
            ],
            [
                'event_key' => 'published',
                'name' => 'Activity Live Broadcast Notification',
                'email_subject' => 'BROADCAST LIVE ON SOCIAL MEDIA: {reference_id}',
                'email_body' => "Congratulations! Activity {reference_id} ({title}) has been officially published across VMRFDU institutional social media channels.",
                'system_title' => 'Activity Live on Social Media',
                'system_message' => "Activity {reference_id} formally published to institutional channels!",
                'is_active' => true,
            ],
        ];

        foreach ($defaults as $t) {
            NotificationTemplate::updateOrCreate(['event_key' => $t['event_key']], $t);
        }
    }
}
