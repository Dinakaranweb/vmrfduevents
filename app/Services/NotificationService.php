<?php

namespace App\Services;

use App\Models\ActivitySubmission;
use App\Models\User;
use App\Models\PortalNotification;
use App\Models\EmailLog;
use App\Models\SystemSetting;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * In-memory queue to consolidate multiple notifications for the same Reference ID and recipient
     * during a single request lifecycle or batch process.
     */
    public static array $pendingEmailQueue = [];
    private static bool $shutdownRegistered = false;

    /**
     * Collect all relevant stakeholders (particulars) for an activity submission.
     */
    private static function getStakeholderParticulars(ActivitySubmission $submission, array $targetRoles = []): \Illuminate\Support\Collection
    {
        $recipients = collect();

        // 1. Always include the original submitter / creator
        if ($submission->user) {
            $recipients->push($submission->user);
        }

        // 2. If college exists, include college-level administrators / coordinators for that campus
        if ($submission->college_id) {
            $collegeAdmins = User::where('college_id', $submission->college_id)
                ->whereIn('role', ['college_admin', 'coordinator', 'institutional_coordinator', 'principal', 'dean'])
                ->get();
            $recipients = $recipients->merge($collegeAdmins);
        }

        // 3. Include target global authorities and review desks
        if (!empty($targetRoles)) {
            $roleUsers = User::whereIn('role', $targetRoles)->get();
            $recipients = $recipients->merge($roleUsers);
        }

        // Return unique recipients by email address
        return $recipients->filter(function($u) {
            return !empty($u->email);
        })->unique('email')->values();
    }

    /**
     * Dispatch multi-channel notifications (In-app + HTML Email) for activity state changes & comments to all particulars.
     * Multiple notifications belonging to the same Reference ID (Ref ID) are automatically combined into a single email.
     */
    public static function notifyStageChange(ActivitySubmission $submission, string $stage, ?string $remarks = null)
    {
        $remarks = $remarks ?? '';
        $actionUrl = route('submissions.show', $submission->id);
        $title = '';
        $message = '';
        $recipients = collect();
        $emailSubject = '';
        $stageColor = SystemSetting::getValue('email_primary_color', '#1e1b4b');

        // Check if a dynamic template exists in DB for this event_key
        $template = NotificationTemplate::where('event_key', $stage)->orWhere('name', $stage)->first();
        if ($template && $template->is_active) {
            $title = $template->system_title ?: ($template->name ?: 'Official Activity Update');
            $message = $template->system_message ?: "Activity {$submission->reference_id} updated to {$stage}.";
            $message = str_replace(['{reference_id}', '{title}', '{stage}', '{remarks}'], [$submission->reference_id, $submission->title, $stage, $remarks], $message);
            $emailSubject = str_replace(['{reference_id}', '{title}', '{stage}'], [$submission->reference_id, $submission->title, $stage], $template->email_subject ?: "[VMRFDU Portal] Activity Update: {$submission->reference_id}");
            
            // Assign default recipients by stage logic
            $recipients = self::getDefaultRecipientsForStage($submission, $stage);
        } else {
            switch ($stage) {
                case 'submitted':
                    $title = 'New Institutional Activity Submitted';
                    $message = "Activity {$submission->reference_id} ({$submission->title}) submitted for institutional verification.";
                    $emailSubject = "[VMRFDU Portal] New Activity Submitted: {$submission->reference_id}";
                    $stageColor = '#f59e0b'; // Gold
                    $recipients = self::getStakeholderParticulars($submission, ['office_registrar', 'registrar', 'vc', 'pro_vc', 'super_admin']);
                    break;

                case 'approved':
                    $title = 'Activity Approved & Forwarded to Media Editor Desk';
                    $message = "Activity {$submission->reference_id} approved by Institutional Authority" . ($remarks ? " ({$remarks})" : "") . ". Automatically forwarded to Media Studio Desk.";
                    $emailSubject = "[VMRFDU Portal] APPROVED & FORWARDED TO MEDIA DESK: {$submission->reference_id}";
                    $stageColor = '#10b981'; // Emerald
                    $recipients = self::getStakeholderParticulars($submission, ['media_editor', 'office_registrar', 'registrar', 'super_admin']);
                    break;

                case 'marked for discussion':
                case 'marked for discussion by PA to Registrar (Office of the Registrar)':
                case 'editor_discuss':
                case 'discuss':
                    $title = 'Activity Returned for Correction';
                    $message = "Activity {$submission->reference_id} returned for correction/discussion with guidance: \"" . ($remarks ?: 'Please review guidance notes in portal.') . "\"";
                    $emailSubject = "[VMRFDU Portal] CORRECTION REQUIRED: {$submission->reference_id}";
                    $stageColor = '#f97316'; // Orange
                    $recipients = self::getStakeholderParticulars($submission, ['media_editor', 'office_registrar', 'registrar']);
                    break;

                case 'rejected':
                case 'rejected by Office of the Registrar':
                    $title = 'Activity Submission Rejected';
                    $message = "Activity {$submission->reference_id} formally rejected by Institutional Authority" . ($remarks ? " ({$remarks})" : "") . ".";
                    $emailSubject = "[VMRFDU Portal] ACTIVITY REJECTED: {$submission->reference_id}";
                    $stageColor = '#ef4444'; // Red
                    $recipients = self::getStakeholderParticulars($submission, ['media_editor', 'office_registrar', 'registrar', 'super_admin']);
                    break;

                case 'resubmitted':
                    $title = 'Corrected Activity Resubmitted for Review';
                    $message = "Activity {$submission->reference_id} corrected and resubmitted by College Representative.";
                    $emailSubject = "[VMRFDU Portal] RESUBMITTED FOR VERIFICATION: {$submission->reference_id}";
                    $stageColor = '#6366f1'; // Indigo
                    $recipients = self::getStakeholderParticulars($submission, ['office_registrar', 'registrar', 'vc', 'pro_vc', 'super_admin']);
                    break;

                case 'submitted to Registrar':
                    $title = 'Media Package Submitted for Registrar Approval';
                    $message = "Edited media package for {$submission->reference_id} submitted by Media Editor for final verification.";
                    $emailSubject = "[VMRFDU Portal] Media Package Review Required: {$submission->reference_id}";
                    $stageColor = '#3b82f6'; // Blue
                    $recipients = self::getStakeholderParticulars($submission, ['media_editor', 'office_registrar', 'registrar', 'vc', 'pro_vc', 'super_admin']);
                    break;

                case 'approved by Office of the Registrar':
                case 'approved by PA to Registrar (Office of the Registrar)':
                    $title = 'Media Package Approved by Registrar (Ready to Publish)';
                    $message = "Media package for {$submission->reference_id} approved by Office of the Registrar" . ($remarks ? " ({$remarks})" : "") . ". Ready for broadcast.";
                    $emailSubject = "[VMRFDU Portal] REGISTRAR APPROVED - Ready to Publish: {$submission->reference_id}";
                    $stageColor = '#10b981'; // Emerald
                    $recipients = self::getStakeholderParticulars($submission, ['media_editor', 'office_registrar', 'registrar', 'super_admin']);
                    break;

                case 'scheduled':
                    $title = 'Media Package Scheduled for Social Media Broadcast';
                    $message = "Media package for {$submission->reference_id} has been scheduled for publication across official institutional channels.";
                    $emailSubject = "[VMRFDU Portal] BROADCAST SCHEDULED: {$submission->reference_id}";
                    $stageColor = '#06b6d4'; // Cyan
                    $recipients = self::getStakeholderParticulars($submission, ['media_editor', 'office_registrar', 'registrar', 'super_admin']);
                    break;

                case 'published':
                    $title = 'Activity Published – Social Media Details Updated';
                    $message = "The activity has been published on the selected social media platforms. The respective post links have been updated in the system for reference.";
                    $emailSubject = "[VMRFDU Portal] BROADCAST LIVE: {$submission->reference_id}";
                    $stageColor = '#8b5cf6'; // Purple
                    $recipients = self::getStakeholderParticulars($submission, ['media_editor', 'vc', 'pro_vc', 'office_registrar', 'registrar', 'super_admin']);
                    break;

                case 'discussion_comment':
                    $title = 'New Discussion & Guidance Note Added';
                    $message = "New note/comment on Activity {$submission->reference_id}: \"{$remarks}\"";
                    $emailSubject = "[VMRFDU Portal] New Discussion Note: {$submission->reference_id}";
                    $stageColor = '#0284c7'; // Sky blue
                    $recipients = self::getStakeholderParticulars($submission, ['media_editor', 'office_registrar', 'registrar']);
                    break;

                default:
                    $title = 'Official Activity Update: ' . ucfirst($stage);
                    $message = "Activity {$submission->reference_id} updated to status/stage: {$stage}." . ($remarks ? " Remarks: {$remarks}" : "");
                    $emailSubject = "[VMRFDU Portal] Activity Update ({$stage}): {$submission->reference_id}";
                    $stageColor = SystemSetting::getValue('email_primary_color', '#1e1b4b');
                    $recipients = self::getStakeholderParticulars($submission, ['office_registrar', 'registrar', 'media_editor', 'super_admin']);
                    break;
            }
        }

        foreach ($recipients as $recipient) {
            // 1. In-App Portal Notification (Instant < 5ms)
            PortalNotification::create([
                'user_id' => $recipient->id,
                'title' => $title,
                'message' => $message,
                'type' => in_array($stage, ['approved', 'published', 'approved by Office of the Registrar', 'approved by PA to Registrar (Office of the Registrar)']) ? 'success' : (in_array($stage, ['marked for discussion', 'editor_discuss', 'discuss', 'marked for discussion by PA to Registrar (Office of the Registrar)', 'rejected', 'rejected by Office of the Registrar']) ? 'alert' : 'info'),
                'action_url' => $actionUrl,
            ]);

            // 2. Queue Email Update for Consolidation by Ref ID (skip if this user has email notifications disabled)
            if (!($recipient->email_notifications_enabled ?? true)) {
                continue;
            }

            self::$pendingEmailQueue[$recipient->email][$submission->reference_id][] = [
                'recipient' => $recipient,
                'submission' => $submission,
                'stage' => $stage,
                'title' => $title,
                'message' => $message,
                'emailSubject' => $emailSubject,
                'stageColor' => $stageColor,
                'remarks' => $remarks,
                'actionUrl' => $actionUrl,
                'timestamp' => now()->format('h:i A, d M Y')
            ];
        }

        // 3. Register non-blocking shutdown function once per lifecycle to flush consolidated emails
        if (!self::$shutdownRegistered) {
            self::$shutdownRegistered = true;
            $flushHandler = function () {
                self::flushPendingEmails();
            };

            if (function_exists('fastcgi_finish_request')) {
                register_shutdown_function(function () use ($flushHandler) {
                    $flushHandler();
                });
            } else {
                register_shutdown_function($flushHandler);
            }
        }
    }

    private static function getDefaultRecipientsForStage(ActivitySubmission $submission, string $stage): \Illuminate\Support\Collection
    {
        if (in_array($stage, ['submitted', 'resubmitted'])) {
            return self::getStakeholderParticulars($submission, ['office_registrar', 'registrar', 'vc', 'pro_vc', 'super_admin']);
        } elseif (in_array($stage, ['approved', 'approved by Office of the Registrar', 'submitted to Registrar', 'published', 'scheduled'])) {
            return self::getStakeholderParticulars($submission, ['media_editor', 'office_registrar', 'registrar', 'super_admin']);
        } else {
            return self::getStakeholderParticulars($submission, ['media_editor', 'office_registrar', 'registrar']);
        }
    }

    /**
     * Flush and send all queued notifications, combining any updates belonging to the same Reference ID
     * into a single comprehensive HTML email per recipient, while embedding CID logo for universal email client compatibility.
     */
    public static function flushPendingEmails()
    {
        if (empty(self::$pendingEmailQueue)) {
            return;
        }

        $queue = self::$pendingEmailQueue;
        self::$pendingEmailQueue = [];
        self::$shutdownRegistered = false;

        $oldTimeout = @ini_set('default_socket_timeout', 1);

        if (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
        }

        foreach ($queue as $email => $refMap) {
            foreach ($refMap as $refId => $updates) {
                if (empty($updates)) {
                    continue;
                }

                $firstUpdate = $updates[0];
                $recipient = $firstUpdate['recipient'];
                $submission = $firstUpdate['submission'];
                $updateCount = count($updates);

                if ($updateCount === 1) {
                    $subject = $firstUpdate['emailSubject'];
                } else {
                    $subject = "[VMRFDU Portal] Consolidated Activity Report ({$updateCount} Updates) for Ref ID: {$refId}";
                }

                // Build template with {{PORTAL_LOGO_SRC}} placeholder
                $rawHtmlBody = self::buildConsolidatedHtmlEmail($recipient, $submission, $updates, $updateCount, $subject);

                // Prepare Web Preview version (for EmailLog storage inside DB)
                $logoSetting = SystemSetting::getValue('portal_logo');
                $webLogoSrc = !empty($logoSetting) ? asset($logoSetting) : '';
                $dbHtmlBody = str_replace('{{PORTAL_LOGO_SRC}}', $webLogoSrc, $rawHtmlBody);

                $status = 'sent';
                $errorMessage = null;

                // Dispatch via SMTP cleanly without file attachments (using web asset URL for logo)
                try {
                    Mail::send([], [], function ($message) use ($recipient, $subject, $dbHtmlBody, $refId, $updateCount, $firstUpdate) {
                        $message->to($recipient->email, $recipient->name)
                                ->subject($subject);

                        $message->html($dbHtmlBody);

                        // Email Threading Headers
                        $refIdClean = preg_replace('/[^a-zA-Z0-9_-]/', '', $refId);
                        $threadId = "{$refIdClean}@vmrfdu.edu.in";
                        $headers = $message->getHeaders();
                        
                        if ($updateCount === 1 && $firstUpdate['stage'] === 'submitted') {
                            // This is the first email (creation), set the root Message-ID
                            $headers->addTextHeader('Message-ID', "<{$threadId}>");
                        } else {
                            // This is an update, thread it to the original Message-ID
                            $headers->addTextHeader('In-Reply-To', "<{$threadId}>");
                            $headers->addTextHeader('References', "<{$threadId}>");
                            // Set a unique Message-ID for this specific reply to avoid client deduplication
                            $uniqueId = uniqid() . "-{$threadId}";
                            $headers->addTextHeader('Message-ID', "<{$uniqueId}>");
                        }
                    });
                } catch (\Throwable $e) {
                    $status = 'failed';
                    $errorMessage = $e->getMessage();
                    Log::warning("Background SMTP dispatch failed for {$recipient->email} ({$refId}): " . $errorMessage);
                }

                // Log comprehensive record in database for Super Admin monitoring
                try {
                    EmailLog::create([
                        'sender' => SystemSetting::getValue('portal_name', "VINAYAKA MISSION'S RESEARCH FOUNDATION") . ' Notification Desk',
                        'recipient_user_id' => $recipient->id ?? null,
                        'recipient_email' => $recipient->email,
                        'reference_id' => $refId,
                        'subject' => $subject,
                        'stage' => $updateCount === 1 ? strtoupper($firstUpdate['stage']) : 'CONSOLIDATED_UPDATES',
                        'body_html' => $dbHtmlBody,
                        'status' => $status,
                        'error_message' => $errorMessage,
                    ]);
                } catch (\Throwable $e) {
                    Log::error("Failed to log consolidated email in DB (`email_logs`): " . $e->getMessage());
                }
            }
        }

        if ($oldTimeout !== false) {
            @ini_set('default_socket_timeout', $oldTimeout);
        }
    }

    /**
     * Build responsive, highly professional HTML email template incorporating dynamic organization branding, logo placeholder,
     * and either a single update or consolidated updates timeline for the given Ref ID.
     */
    private static function buildConsolidatedHtmlEmail($recipient, ActivitySubmission $sub, array $updates, int $updateCount, string $subject): string
    {
        $dateStr = $sub->activity_date ? $sub->activity_date->format('d M, Y') : 'N/A';
        $colCode = $sub->college ? $sub->college->code : 'Central';
        $colName = $sub->college ? $sub->college->name : 'Central Administration';
        $actionUrl = $updates[0]['actionUrl'] ?? route('home');

        // Retrieve dynamic branding & colors
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

        $logoHtml = '';
        if (!empty($logoSetting)) {
            $logoHtml = "<div style=\"margin-bottom: 10px;\"><img src=\"{{PORTAL_LOGO_SRC}}\" alt=\"{$portalName} Logo\" style=\"max-height: 65px; max-width: 230px; object-fit: contain; display: inline-block;\"></div>";
        }

        $bannerColor = $updateCount === 1 ? ($updates[0]['stageColor'] ?? $primaryColor) : '#4338ca';
        $bannerText = $updateCount === 1
            ? "OFFICIAL STATUS: " . strtoupper(str_replace('_', ' ', $updates[0]['stage']))
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
}
