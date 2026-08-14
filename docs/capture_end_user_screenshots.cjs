/**
 * capture_end_user_screenshots.cjs
 *
 * Captures a sequence of PNG screenshots of the VMRFDU Activity Portal
 * as seen by an END USER (College Admin / DEO role) for use in the
 * end-user PowerPoint manual.
 *
 * Logs in as deo.sahs-pdy@vmrfdu.edu.in and walks through:
 *  - Login (empty + filled)
 *  - Dashboard
 *  - Activity creation form (Past Event flow) - empty, filled, upload, submit, pending status
 *  - Activity creation form (Future Event flow) - abbreviated distinguishing screens
 *  - Submissions list showing the new pending submission
 *  - Profile view, edit, and change-password tab
 *
 * Every capture is verified to be an authenticated, expected page (checks
 * for sidebar nav / heading / form fields) before being counted as success.
 */

const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

const BASE_URL = 'http://localhost/vmrfdu_activity_portal/public';
const LOGIN_EMAIL = 'deo.sahs-pdy@vmrfdu.edu.in';
const LOGIN_PASSWORD = 'password';
const OUT_DIR = path.join(__dirname, 'screenshots_end_user');

if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

const results = [];
let seq = 0;

function nextName(label) {
    seq += 1;
    return String(seq).padStart(2, '0') + '_' + label + '.png';
}

async function shoot(page, label, opts = {}) {
    const filename = nextName(label);
    const filepath = path.join(OUT_DIR, filename);
    await page.screenshot({ path: filepath, fullPage: opts.fullPage !== false });
    console.log('  [captured] ' + filename);
    return filepath;
}

async function verifyPage(page, { mustContainText = [], mustExistSelectors = [], mustNotBeLogin = true } = {}) {
    const url = page.url();
    if (mustNotBeLogin && /\/login/i.test(url)) {
        return { ok: false, reason: 'Redirected to login page (' + url + ')' };
    }
    const bodyText = await page.evaluate(() => document.body ? document.body.innerText : '');
    for (const txt of mustContainText) {
        if (!bodyText.includes(txt)) {
            return { ok: false, reason: 'Missing expected text: "' + txt + '" on ' + url };
        }
    }
    for (const sel of mustExistSelectors) {
        const exists = await page.$(sel);
        if (!exists) {
            return { ok: false, reason: 'Missing expected selector: "' + sel + '" on ' + url };
        }
    }
    return { ok: true };
}

async function record(label, page, verifyOpts, shootOpts) {
    const verdict = await verifyPage(page, verifyOpts);
    if (!verdict.ok) {
        console.log('  [FAILED] ' + label + ' -> ' + verdict.reason);
        results.push({ label, ok: false, reason: verdict.reason });
        return false;
    }
    const filepath = await shoot(page, label, shootOpts);
    results.push({ label, ok: true, file: filepath });
    return true;
}

async function main() {
    console.log('Starting end-user screenshot capture...');
    const browser = await puppeteer.launch({
        headless: 'new',
        defaultViewport: { width: 1440, height: 900 },
        args: ['--window-size=1440,900', '--no-sandbox'],
    });
    const page = await browser.newPage();

    try {
        // ---------- 1. Login page (empty) ----------
        console.log('\n[Step] Login page (empty)');
        await page.goto(BASE_URL + '/login', { waitUntil: 'networkidle2' });
        await record('login_empty', page, {
            mustExistSelectors: ['input[name="email"], input[type="email"]', 'input[name="password"], input[type="password"]'],
            mustNotBeLogin: false,
        });

        // ---------- 2. Login page (filled) ----------
        console.log('\n[Step] Login page (filled)');
        const emailSel = (await page.$('input[name="email"]')) ? 'input[name="email"]' : 'input[type="email"]';
        const passSel = (await page.$('input[name="password"]')) ? 'input[name="password"]' : 'input[type="password"]';
        await page.type(emailSel, LOGIN_EMAIL, { delay: 20 });
        await page.type(passSel, LOGIN_PASSWORD, { delay: 20 });
        await record('login_filled', page, {
            mustExistSelectors: [emailSel, passSel],
            mustNotBeLogin: false,
        });

        // ---------- 3. Submit login -> Dashboard ----------
        console.log('\n[Step] Submitting login...');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}),
            page.click('button[type="submit"], input[type="submit"]'),
        ]);
        await new Promise((r) => setTimeout(r, 500));

        await record('dashboard', page, {
            mustContainText: [],
            mustExistSelectors: ['nav, .sidebar, #sidebar, .navbar'],
        });

        // ---------- 4. Activity Create - Past Event - empty ----------
        console.log('\n[Step] Activity create form (empty, Past Event default)');
        await page.goto(BASE_URL + '/submissions/create', { waitUntil: 'networkidle2' });
        await record('create_form_empty_past', page, {
            mustExistSelectors: ['input[name="title"]', 'select[name="activity_type"]', 'input[name="activity_date"]'],
        });

        // ---------- 5. Fill form - Past Event ----------
        console.log('\n[Step] Fill activity form with sample Past Event data');
        await page.evaluate(() => {
            const sel = document.querySelector('select[name="activity_type"]');
            if (sel) { sel.value = 'Past Event'; sel.dispatchEvent(new Event('change', { bubbles: true })); }
        });
        await page.type('input[name="title"]', 'National Seminar on Advances in Public Health Research 2026', { delay: 5 });

        // Event category - pick first non-empty option
        await page.evaluate(() => {
            const sel = document.querySelector('select[name="event_category"]');
            if (sel && sel.options.length > 1) {
                sel.selectedIndex = 1;
                sel.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        // Activity date - a recent past date
        await page.evaluate(() => {
            const el = document.querySelector('input[name="activity_date"]');
            if (el) { el.value = '2026-07-15'; el.dispatchEvent(new Event('change', { bubbles: true })); }
        });

        const locSel = 'input[name="location"]';
        if (await page.$(locSel)) {
            await page.click(locSel, { clickCount: 3 });
            await page.type(locSel, 'Main Auditorium, SAHS Campus', { delay: 5 });
        }

        const taSel = 'input[name="target_audience"]';
        if (await page.$(taSel)) {
            await page.type(taSel, '60 Faculty Members, 220 Undergraduate Students attended', { delay: 5 });
        }

        const rpSel = 'input[name="resource_person_name"]';
        if (await page.$(rpSel)) {
            await page.type(rpSel, 'Dr. Anitha Kumar, Senior Public Health Consultant', { delay: 5 });
        }

        const outSel = 'input[name="outcome"], #outcome';
        if (await page.$(outSel)) {
            await page.type(outSel, 'Participants gained practical insights into community health research; 1 MoU signed with district health department.', { delay: 5 });
        }

        // Report type -> By Text
        await page.evaluate(() => {
            const sel = document.querySelector('select[name="report_type"], #reportTypeSelect');
            if (sel) {
                sel.value = 'By Text';
                sel.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        await new Promise((r) => setTimeout(r, 300));
        const descSel = '#textDescription';
        if (await page.$(descSel)) {
            await page.type(descSel, 'The seminar brought together faculty and students to discuss recent advances in public health research, featuring keynote talks and panel discussions.', { delay: 3 });
        }

        const captionSel = 'textarea[name="social_media_caption"]';
        if (await page.$(captionSel)) {
            await page.type(captionSel, 'Highlights from our National Seminar on Public Health Research 2026! #VMRFDU #PublicHealth', { delay: 3 });
        }

        await record('create_form_filled_past', page, {
            mustExistSelectors: ['input[name="title"]'],
        });

        // ---------- 6. Upload images (stand-in files) ----------
        console.log('\n[Step] Upload media (stand-in sample images)');
        const sampleImagesDir = path.join(__dirname, '..', 'public', 'uploads', 'activities', 'images');
        let sampleFiles = [];
        try {
            sampleFiles = fs.readdirSync(sampleImagesDir)
                .filter((f) => /\.(png|jpg|jpeg)$/i.test(f))
                .slice(0, 2)
                .map((f) => path.join(sampleImagesDir, f));
        } catch (e) {
            console.log('  [warn] could not read sample images dir: ' + e.message);
        }

        if (sampleFiles.length >= 1) {
            const imagesInput = await page.$('input[name="images[]"]');
            if (imagesInput) {
                await imagesInput.uploadFile(...sampleFiles.slice(0, Math.min(2, sampleFiles.length)));
                await new Promise((r) => setTimeout(r, 400));
            }
            const geoInput = await page.$('input[name="geo_tagged_photo[]"]');
            if (geoInput) {
                await geoInput.uploadFile(sampleFiles[0]);
                await new Promise((r) => setTimeout(r, 400));
            }
        } else {
            console.log('  [warn] No sample images found to use as stand-in uploads.');
        }

        await page.evaluate(() => {
            const el = document.getElementById('imagesContainer') || document.getElementById('geoTaggedContainer');
            if (el) el.scrollIntoView({ block: 'center' });
        });
        await record('create_form_upload_media', page, {
            mustExistSelectors: ['input[name="images[]"]'],
        });

        // ---------- 7. Submit the form (real submission) ----------
        console.log('\n[Step] Submitting the activity form for real...');
        const submitBtn = await page.$('#submitSubmissionBtn');
        let submitted = false;
        if (submitBtn) {
            await submitBtn.click();
            await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }).catch(() => {});
            submitted = /\/submissions\/\d+/.test(page.url());
        }
        console.log('  [info] Post-submit URL: ' + page.url() + ' (submitted=' + submitted + ')');

        // ---------- 8. Pending status / confirmation page ----------
        console.log('\n[Step] Confirmation / pending-status page');
        await record('submission_pending_status', page, {
            mustExistSelectors: [],
        });

        // ---------- 9. Submissions list showing new pending submission ----------
        console.log('\n[Step] Submissions list');
        await page.goto(BASE_URL + '/submissions', { waitUntil: 'networkidle2' });
        await record('submissions_list_pending', page, {
            mustExistSelectors: ['table, .card, .list-group'],
        });

        // ---------- 10. Future Event flow (abbreviated) ----------
        console.log('\n[Step] Activity create form - Future Event flow (abbreviated)');
        await page.goto(BASE_URL + '/submissions/create', { waitUntil: 'networkidle2' });
        await page.evaluate(() => {
            const sel = document.querySelector('select[name="activity_type"]');
            if (sel) { sel.value = 'Future Event'; sel.dispatchEvent(new Event('change', { bubbles: true })); }
        });
        await page.type('input[name="title"]', 'International Conference on AI in Healthcare 2026 (Upcoming)', { delay: 5 });
        await page.evaluate(() => {
            const sel = document.querySelector('select[name="event_category"]');
            if (sel && sel.options.length > 1) { sel.selectedIndex = 1; sel.dispatchEvent(new Event('change', { bubbles: true })); }
        });
        await page.evaluate(() => {
            const el = document.querySelector('input[name="activity_date"]');
            if (el) { el.value = '2026-12-10'; el.dispatchEvent(new Event('change', { bubbles: true })); }
        });
        if (await page.$(locSel)) {
            await page.click(locSel, { clickCount: 3 });
            await page.type(locSel, 'Convention Centre, Central Campus', { delay: 5 });
        }
        await record('create_form_future_event', page, {
            mustExistSelectors: ['select[name="activity_type"]'],
        });

        // ---------- 11. Profile view ----------
        console.log('\n[Step] Profile view');
        await page.goto(BASE_URL + '/profile', { waitUntil: 'networkidle2' });
        await record('profile_view', page, {
            mustContainText: ['Profile'],
        });

        // ---------- 12. Profile edit form (already visible as default tab) ----------
        console.log('\n[Step] Profile edit form');
        await record('profile_edit_form', page, {
            mustExistSelectors: ['input[name="name"]'],
        });

        // ---------- 13. Change password tab ----------
        console.log('\n[Step] Change password form (Security tab)');
        const secTab = await page.$('#security-tab');
        if (secTab) {
            await secTab.click();
            await new Promise((r) => setTimeout(r, 400));
        }
        await record('profile_change_password', page, {
            mustExistSelectors: ['input[name="current_password"]', 'input[name="new_password"]'],
        });

    } catch (err) {
        console.error('Fatal error during capture: ', err);
        results.push({ label: 'FATAL_ERROR', ok: false, reason: err.message });
    } finally {
        await browser.close();
    }

    console.log('\n================ CAPTURE SUMMARY ================');
    let successCount = 0;
    for (const r of results) {
        if (r.ok) {
            successCount++;
            console.log('  OK   - ' + r.label + ' -> ' + path.basename(r.file));
        } else {
            console.log('  FAIL - ' + r.label + ' -> ' + r.reason);
        }
    }
    console.log('Total: ' + successCount + '/' + results.length + ' succeeded.');
    console.log('Output directory: ' + OUT_DIR);
}

main();
