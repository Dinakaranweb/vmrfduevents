const puppeteer = require('puppeteer');
const path = require('path');

const BASE = 'http://localhost/vmrfdu_activity_portal/public';
const SS_DIR = path.join(__dirname, 'screenshots');

const PASSWORD = 'password';

const ROLES = {
  super_admin: 'vmu.web@vmu.edu.in',
  vc: 'vc@vmrfdu.edu.in',
  registrar: 'registrar@vmrfdu.edu.in',
  office_registrar: 'officeregistrar@vmrfdu.edu.in',
  media_editor: 'editor@vmrfdu.edu.in',
  college_admin: 'deo.sahs-pdy@vmrfdu.edu.in',
  pro_vc: 'provc@vmrfdu.edu.in',
};

// { file, url, role|null }  role=null => unauthenticated
const SHOTS = [
  { file: '01_login_page.png', url: '/login', role: null },
  { file: '02_public_gallery.png', url: '/gallery', role: null },
  // 03_qr_verify.png - no such route found in app, SKIPPED (kept old file)

  { file: '04_super_admin_dashboard.png', url: '/super-admin/dashboard', role: 'super_admin' },
  { file: '05_super_admin_colleges.png', url: '/super-admin/colleges', role: 'super_admin' },
  { file: '06_super_admin_users.png', url: '/super-admin/users', role: 'super_admin' },
  { file: '07_super_admin_roles.png', url: '/super-admin/roles', role: 'super_admin' },
  { file: '08_super_admin_workflows.png', url: '/super-admin/workflows', role: 'super_admin' },
  { file: '09_super_admin_access_control.png', url: '/super-admin/access-control', role: 'super_admin' },
  { file: '10_super_admin_activities.png', url: '/super-admin/activities', role: 'super_admin' },
  { file: '11_super_admin_audit_logs.png', url: '/super-admin/audit-logs', role: 'super_admin' },
  { file: '12_super_admin_settings.png', url: '/super-admin/settings', role: 'super_admin' },
  { file: '13_super_admin_analytics.png', url: '/analytics', role: 'super_admin' },
  { file: '14_super_admin_notifications.png', url: '/super-admin/notifications', role: 'super_admin' },
  { file: '15_super_admin_email_logs.png', url: '/emails', role: 'super_admin' },
  { file: '16_super_admin_profile.png', url: '/profile', role: 'super_admin' },

  { file: '17_vc_dashboard.png', url: '/dashboard', role: 'vc' },
  { file: '17_registrar_dashboard.png', url: '/dashboard', role: 'registrar' },
  { file: '18_registrar_approval_queue.png', url: '/approvals/pending', role: 'registrar' },
  { file: '18_vc_verification_queue.png', url: '/approvals/pending', role: 'vc' },
  { file: '19_registrar_approved_list.png', url: '/approvals/approved', role: 'registrar' },
  { file: '19_vc_approved_list.png', url: '/approvals/approved', role: 'vc' },
  { file: '20_registrar_rejected_list.png', url: '/approvals/rejected', role: 'registrar' },
  { file: '20_vc_rejected_list.png', url: '/approvals/rejected', role: 'vc' },
  { file: '21_registrar_analytics.png', url: '/analytics', role: 'registrar' },
  { file: '21_vc_analytics.png', url: '/analytics', role: 'vc' },
  { file: '22_registrar_notifications.png', url: '/notifications', role: 'registrar' },
  { file: '22_vc_submissions_view.png', url: '/submissions', role: 'vc' },
  { file: '23_registrar_profile.png', url: '/profile', role: 'registrar' },
  { file: '23_vc_notifications.png', url: '/notifications', role: 'vc' },
  { file: '24_office_registrar_dashboard.png', url: '/dashboard', role: 'office_registrar' },
  { file: '24_vc_profile.png', url: '/profile', role: 'vc' },
  { file: '25_office_registrar_dashboard.png', url: '/dashboard', role: 'office_registrar' },
  { file: '25_office_registrar_media_queue.png', url: '/approvals/pending', role: 'office_registrar' },
  { file: '26_office_registrar_notifications.png', url: '/notifications', role: 'office_registrar' },
  { file: '26_office_registrar_review_queue.png', url: '/approvals/pending', role: 'office_registrar' },
  { file: '27_media_editor_dashboard.png', url: '/media-editor', role: 'media_editor' },
  { file: '27_office_registrar_approvals.png', url: '/approvals', role: 'office_registrar' },
  { file: '28_media_editor_queue.png', url: '/media-editor/editing', role: 'media_editor' },
  { file: '28_office_registrar_analytics.png', url: '/analytics', role: 'office_registrar' },
  { file: '29_media_editor_locked.png', url: '/media-editor/editing', role: 'media_editor' },
  { file: '29_office_registrar_notifications.png', url: '/notifications', role: 'office_registrar' },
  { file: '30_media_editor_ready.png', url: '/media-editor/ready', role: 'media_editor' },
  { file: '30_office_registrar_profile.png', url: '/profile', role: 'office_registrar' },
  { file: '31_media_editor_dashboard.png', url: '/media-editor', role: 'media_editor' },
  { file: '31_media_editor_notifications.png', url: '/notifications', role: 'media_editor' },
  { file: '32_media_editor_profile.png', url: '/profile', role: 'media_editor' },
  { file: '32_media_editor_studio_queue.png', url: '/media-editor/editing', role: 'media_editor' },
  { file: '33_college_admin_dashboard.png', url: '/dashboard', role: 'college_admin' },
  { file: '33_media_editor_submissions.png', url: '/media-editor/approved', role: 'media_editor' },
  { file: '34_college_admin_submissions.png', url: '/submissions', role: 'college_admin' },
  { file: '34_media_editor_analytics.png', url: '/analytics', role: 'media_editor' },
  { file: '35_college_admin_create_activity.png', url: '/submissions/create', role: 'college_admin' },
  { file: '35_media_editor_notifications.png', url: '/notifications', role: 'media_editor' },
  { file: '36_college_admin_analytics.png', url: '/analytics', role: 'college_admin' },
  { file: '36_media_editor_profile.png', url: '/profile', role: 'media_editor' },
  { file: '37_college_admin_dashboard.png', url: '/dashboard', role: 'college_admin' },
  { file: '37_college_admin_notifications.png', url: '/notifications', role: 'college_admin' },
  { file: '38_college_admin_profile.png', url: '/profile', role: 'college_admin' },
  { file: '38_college_admin_submissions.png', url: '/submissions', role: 'college_admin' },
  { file: '39_college_admin_create_activity.png', url: '/submissions/create', role: 'college_admin' },
  { file: '40_college_admin_analytics.png', url: '/analytics', role: 'college_admin' },
  { file: '41_college_admin_notifications.png', url: '/notifications', role: 'college_admin' },
  { file: '42_college_admin_profile.png', url: '/profile', role: 'college_admin' },

  { file: '43_provc_dashboard.png', url: '/dashboard', role: 'pro_vc' },
  { file: '43_provc_verification_queue.png', url: '/approvals/pending', role: 'pro_vc' },
  { file: '44_provc_approved_list.png', url: '/approvals/approved', role: 'pro_vc' },
  { file: '44_provc_rejected_list.png', url: '/approvals/rejected', role: 'pro_vc' },
  { file: '45_provc_analytics.png', url: '/analytics', role: 'pro_vc' },
  { file: '45_provc_submissions_view.png', url: '/submissions', role: 'pro_vc' },
  { file: '46_provc_notifications.png', url: '/notifications', role: 'pro_vc' },
  { file: '46_provc_profile.png', url: '/profile', role: 'pro_vc' },
];

async function login(page, email) {
  await page.goto(BASE + '/login', { waitUntil: 'networkidle0', timeout: 60000 });
  await page.type('#loginEmail', email, { delay: 10 });
  await page.type('#loginPassword', PASSWORD, { delay: 10 });
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 60000 }).catch(() => {}),
    page.click('button[type="submit"]'),
  ]);
}

(async () => {
  const browser = await puppeteer.launch({ headless: 'new', args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  const results = { ok: [], failed: [] };

  // group shots by role so we log in once per role
  const byRole = {};
  for (const s of SHOTS) {
    const key = s.role || '__anon__';
    (byRole[key] = byRole[key] || []).push(s);
  }

  for (const roleKey of Object.keys(byRole)) {
    const context = await browser.createBrowserContext();
    const page = await context.newPage();
    await page.setViewport({ width: 1440, height: 900 });

    if (roleKey !== '__anon__') {
      try {
        await login(page, ROLES[roleKey]);
        console.log(`Logged in as ${roleKey} (${ROLES[roleKey]})`);
      } catch (e) {
        console.log(`LOGIN FAILED for ${roleKey}: ${e.message}`);
        for (const s of byRole[roleKey]) results.failed.push(`${s.file} (login failed: ${e.message})`);
        await context.close();
        continue;
      }
    }

    for (const s of byRole[roleKey]) {
      try {
        await page.goto(BASE + s.url, { waitUntil: 'networkidle0', timeout: 45000 });
        await new Promise(r => setTimeout(r, 500));
        const outPath = path.join(SS_DIR, s.file);
        await page.screenshot({ path: outPath, fullPage: true });
        console.log(`OK  ${s.file}  <-  ${s.url}`);
        results.ok.push(s.file);
      } catch (e) {
        console.log(`FAIL ${s.file}  <-  ${s.url}  :: ${e.message}`);
        results.failed.push(`${s.file} (${e.message})`);
      }
    }
    await context.close();
  }

  await browser.close();

  console.log('\n=== SUMMARY ===');
  console.log(`Captured: ${results.ok.length}`);
  console.log(`Failed: ${results.failed.length}`);
  for (const f of results.failed) console.log('  - ' + f);
})();
