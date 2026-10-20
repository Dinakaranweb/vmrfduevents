<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;
use Carbon\Carbon;
use App\Services\NtpTimeService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Loaded here as well as through composer autoload.files so the upload
        // helpers survive a deploy where composer dump-autoload has not been run.
        require_once app_path('Support/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        date_default_timezone_set('Asia/Kolkata');

        // Globally override all Carbon implementations (now, Carbon::now, Eloquent timestamps) 
        // to use the synchronized NTP monotonic CPU time, immune to OS clock tampering.
        \App\Services\NtpTimeService::overrideGlobalCarbon();

        Carbon::macro('ntpNow', function ($tz = null) {
            return NtpTimeService::now($tz);
        });

        View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('system_settings')) {
                    $view->with('globalPortalLogo', SystemSetting::getValue('portal_logo'));
                    $view->with('globalDefaultAvatar', SystemSetting::getValue('default_avatar'));
                    $view->with('globalPortalName', SystemSetting::getValue('portal_name', 'VMRFDU Activity Portal'));
                    $view->with('globalPortalTagline', SystemSetting::getValue('portal_tagline', 'Activity Verification & Tracking'));
                    $view->with('globalHeaderTitle', SystemSetting::getValue('portal_header_title', 'Institutional Activity Submissions & Verification Desk'));
                    $view->with('globalFooterText', SystemSetting::getValue('portal_footer_text', SystemSetting::getValue('email_footer_text', '© ' . date('Y') . ' Vinayaka Mission\'s Research Foundation (DU). All rights reserved.')));
                    $view->with('globalFooterSystemText', SystemSetting::getValue('portal_footer_system_text', SystemSetting::getValue('email_footer_system_text', 'Institutional Activity Verification Engine')));
                    $view->with('globalPrimaryColor', SystemSetting::getValue('portal_primary_color', '#1e293b'));
                    $view->with('globalAccentColor', SystemSetting::getValue('portal_accent_color', SystemSetting::getValue('email_accent_color', '#2563eb')));
                    $view->with('globalCardRadius', SystemSetting::getValue('portal_card_radius', '16px'));
                    $view->with('globalCardBorder', SystemSetting::getValue('portal_card_border', '#e2e8f0'));
                    
                    // Login Page Settings
                    $view->with('globalLoginTitle', SystemSetting::getValue('login_title', 'Institutional Login - VMRFDU Centralized Portal'));
                    $view->with('globalLoginBannerHeader', SystemSetting::getValue('login_banner_header', 'VMRFDU Official Portal'));
                    $view->with('globalLoginBannerSubheader', SystemSetting::getValue('login_banner_subheader', 'Central Administration'));
                    $view->with('globalLoginBannerMainText', SystemSetting::getValue('login_banner_main_text', 'Centralized Activity & Media Publication'));
                    $view->with('globalLoginBannerParagraph', SystemSetting::getValue('login_banner_paragraph', 'A unified institutional platform designed for constituent colleges across Salem, Chennai, and Puducherry to submit, verify, approve, and broadcast university events and research milestones.'));
                    $view->with('globalLoginFormTitle', SystemSetting::getValue('login_form_title', 'Sign In'));
                    $view->with('globalLoginFormSubtitle', SystemSetting::getValue('login_form_subtitle', 'Enter your institutional credentials to access the activity portal.'));
                    
                    // Comprehensive Login Variables
                    $view->with('globalLoginShowDirectory', filter_var(SystemSetting::getValue('login_show_directory', 'true'), FILTER_VALIDATE_BOOLEAN));
                    $view->with('globalLoginAnnouncement', SystemSetting::getValue('login_announcement', ''));
                    $view->with('globalLoginAnnouncementType', SystemSetting::getValue('login_announcement_type', 'info'));
                    $view->with('globalLoginSupportEmail', SystemSetting::getValue('login_support_email', ''));
                    $view->with('globalLoginHelpdeskUrl', SystemSetting::getValue('login_helpdesk_url', ''));
                    $view->with('globalLoginFooterText', SystemSetting::getValue('login_footer_text', SystemSetting::getValue('portal_footer_text', '© ' . date('Y') . ' Vinayaka Mission\'s Research Foundation (DU). All rights reserved.')));
                    $view->with('globalLoginFooterSystemText', SystemSetting::getValue('login_footer_system_text', SystemSetting::getValue('portal_footer_system_text', 'Institutional Activity Verification Engine')));
                    $view->with('globalHelplineNumber', SystemSetting::getValue('helpline_number', '1800-VMRF-HELP'));
                } else {
                    $view->with('globalPortalLogo', null);
                    $view->with('globalDefaultAvatar', null);
                    $view->with('globalPortalName', 'VMRFDU Activity Portal');
                    $view->with('globalPortalTagline', 'Activity Verification & Tracking');
                    $view->with('globalHeaderTitle', 'Institutional Activity Submissions & Verification Desk');
                    $view->with('globalFooterText', '© ' . date('Y') . ' Vinayaka Mission\'s Research Foundation (DU). All rights reserved.');
                    $view->with('globalFooterSystemText', 'Institutional Activity Verification Engine');
                    $view->with('globalPrimaryColor', '#1e293b');
                    $view->with('globalAccentColor', '#2563eb');
                    $view->with('globalCardRadius', '16px');
                    $view->with('globalCardBorder', '#e2e8f0');
                    
                    // Login Page Settings Defaults
                    $view->with('globalLoginTitle', 'Institutional Login - VMRFDU Centralized Portal');
                    $view->with('globalLoginBannerHeader', 'VMRFDU Official Portal');
                    $view->with('globalLoginBannerSubheader', 'Central Administration');
                    $view->with('globalLoginBannerMainText', 'Centralized Activity & Media Publication');
                    $view->with('globalLoginBannerParagraph', 'A unified institutional platform designed for constituent colleges across Salem, Chennai, and Puducherry to submit, verify, approve, and broadcast university events and research milestones.');
                    $view->with('globalLoginFormTitle', 'Sign In');
                    $view->with('globalLoginFormSubtitle', 'Enter your institutional credentials to access the activity portal.');
                    $view->with('globalHelplineNumber', '1800-VMRF-HELP');
                }
            } catch (\Exception $e) {
                $view->with('globalPortalLogo', null);
                $view->with('globalDefaultAvatar', null);
                $view->with('globalPortalName', 'VMRFDU Activity Portal');
                $view->with('globalPortalTagline', 'Activity Verification & Tracking');
                $view->with('globalHeaderTitle', 'Institutional Activity Submissions & Verification Desk');
                $view->with('globalFooterText', '© ' . date('Y') . ' Vinayaka Mission\'s Research Foundation (DU). All rights reserved.');
                $view->with('globalFooterSystemText', 'Institutional Activity Verification Engine');
                $view->with('globalPrimaryColor', '#1e293b');
                $view->with('globalAccentColor', '#2563eb');
                $view->with('globalCardRadius', '16px');
                $view->with('globalCardBorder', '#e2e8f0');
                
                // Login Page Settings Error Defaults
                $view->with('globalLoginTitle', 'Institutional Login - VMRFDU Centralized Portal');
                $view->with('globalLoginBannerHeader', 'VMRFDU Official Portal');
                $view->with('globalLoginBannerSubheader', 'Central Administration');
                $view->with('globalLoginBannerMainText', 'Centralized Activity & Media Publication');
                $view->with('globalLoginBannerParagraph', 'A unified institutional platform designed for constituent colleges across Salem, Chennai, and Puducherry to submit, verify, approve, and broadcast university events and research milestones.');
                $view->with('globalLoginFormTitle', 'Sign In');
                $view->with('globalLoginFormSubtitle', 'Enter your institutional credentials to access the activity portal.');
                $view->with('globalHelplineNumber', '1800-VMRF-HELP');
            }
        });
    }
}
