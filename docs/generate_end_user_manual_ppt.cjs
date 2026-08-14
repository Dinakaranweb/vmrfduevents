/**
 * generate_end_user_manual_ppt.cjs
 *
 * Generates a NEW, standalone End User Manual PowerPoint deck for the
 * College Admin / DEO role of the VMRFDU Activity Portal.
 *
 * Output: docs/VMRFDU_Activity_Portal_End_User_Manual.pptx
 *
 * This script is independent from generate_presentation.cjs and
 * generate_user_manual_ppt.cjs (those are left untouched). The color
 * palette below is copied from generate_presentation.cjs for visual
 * consistency across the deck family.
 */

const PptxGenJS = require('pptxgenjs');
const path = require('path');
const fs = require('fs');

const SCREENSHOT_DIR = path.join(__dirname, 'screenshots_end_user');
const OUTPUT_FILE = path.join(__dirname, 'VMRFDU_Activity_Portal_End_User_Manual.pptx');

// ---- Palette copied from docs/../generate_presentation.cjs (read-only reference) ----
const colors = {
    navy: '0A192F',
    blue: '1E3A8A',
    gold: 'D97706',
    lightGray: 'F8FAFC',
    darkText: '1E293B',
    mutedText: '64748B',
    white: 'FFFFFF',
    green: '047857',
    red: 'B91C1C',
    cardBg: 'FFFFFF',
    border: 'E2E8F0',
};

const SLIDE_W = 13.333;
const SLIDE_H = 7.5;

function imgPath(filename) {
    return path.join(SCREENSHOT_DIR, filename);
}

function pngSize(filename) {
    const buf = fs.readFileSync(imgPath(filename));
    return { w: buf.readUInt32BE(16), h: buf.readUInt32BE(20) };
}

/**
 * Places an image scaled to fit within a bounding box (x,y,w,h) in inches,
 * preserving aspect ratio, anchored to the top of the box.
 * Returns the actual {x,y,w,h} in inches used, so callout overlays can be
 * computed proportionally against the source PNG pixel dimensions.
 */
function placeImage(slide, filename, box) {
    const { w: pxW, h: pxH } = pngSize(filename);
    const boxAspect = box.w / box.h;
    const imgAspect = pxW / pxH;
    let w, h;
    if (imgAspect > boxAspect) {
        w = box.w;
        h = w / imgAspect;
    } else {
        h = box.h;
        w = h * imgAspect;
    }
    const x = box.x + (box.w - w) / 2;
    const y = box.y;
    slide.addImage({ path: imgPath(filename), x, y, w, h, shadow: { type: 'outer', color: '000000', opacity: 0.25, blur: 6, offset: 2, angle: 90 } });
    return { x, y, w, h, pxW, pxH };
}

/**
 * Converts a pixel coordinate (from the ORIGINAL screenshot PNG) into
 * slide-relative inches, given the placed image geometry returned by placeImage().
 */
function pxToSlide(placed, px, py) {
    return {
        x: placed.x + (px / placed.pxW) * placed.w,
        y: placed.y + (py / placed.pxH) * placed.h,
    };
}

// Adds a semi-transparent gold rounded-rect outline callout around a pixel region.
function calloutBox(slide, placed, px1, py1, px2, py2) {
    const p1 = pxToSlide(placed, px1, py1);
    const p2 = pxToSlide(placed, px2, py2);
    slide.addShape('roundRect', {
        x: p1.x - 0.05,
        y: p1.y - 0.05,
        w: (p2.x - p1.x) + 0.1,
        h: (p2.y - p1.y) + 0.1,
        rectRadius: 0.08,
        fill: { color: colors.gold, transparency: 88 },
        line: { color: colors.gold, width: 2.5 },
        shadow: { type: 'outer', color: colors.gold, opacity: 0.4, blur: 8, offset: 0, angle: 0 },
    });
}

// Adds a numbered circle marker pointing at a pixel location.
function calloutNumber(slide, placed, px, py, number) {
    const p = pxToSlide(placed, px, py);
    const d = 0.34;
    slide.addShape('ellipse', {
        x: p.x - d / 2, y: p.y - d / 2, w: d, h: d,
        fill: { color: colors.gold },
        line: { color: colors.white, width: 1.5 },
        shadow: { type: 'outer', color: '000000', opacity: 0.35, blur: 4, offset: 1, angle: 90 },
    });
    slide.addText(String(number), {
        x: p.x - d / 2, y: p.y - d / 2, w: d, h: d,
        align: 'center', valign: 'middle',
        fontSize: 13, bold: true, color: colors.white, fontFace: 'Segoe UI',
    });
}

async function main() {
    console.log('Initializing VMRFDU End User Manual PPT Generator...');
    const pptx = new PptxGenJS();
    pptx.layout = 'LAYOUT_16x9';
    pptx.author = 'VMRFDU Activity Portal';
    pptx.title = 'VMRFDU Activity Portal - End User Guide';

    pptx.defineSlideMaster({
        title: 'EU_MASTER',
        background: { color: colors.lightGray },
        objects: [
            { rect: { x: 0, y: 0, w: '100%', h: 0.7, fill: { color: colors.navy } } },
            { rect: { x: 0, y: 0.7, w: '100%', h: 0.05, fill: { color: colors.gold } } },
            { rect: { x: 0, y: 7.15, w: '100%', h: 0.35, fill: { color: colors.navy } } },
            { text: { text: 'VMRFDU Activity Portal | End User Guide - College Admin / DEO', options: { x: 0.4, y: 7.17, w: 9.0, h: 0.3, color: colors.white, fontSize: 9, fontFace: 'Segoe UI' } } },
        ],
    });

    // ---------------------------------------------------------------
    // Helper: section divider slide
    // ---------------------------------------------------------------
    function addDivider(sectionNo, title, subtitle, icon) {
        const slide = pptx.addSlide();
        slide.background = { color: colors.navy };
        slide.addShape('rect', { x: 0, y: 0, w: SLIDE_W, h: SLIDE_H, fill: { color: colors.navy } });
        slide.addShape('rect', { x: 0, y: 3.55, w: SLIDE_W, h: 0.06, fill: { color: colors.gold } });
        slide.addText(icon, { x: 0.9, y: 1.7, w: 2.0, h: 1.6, fontSize: 72, color: colors.gold, align: 'center', valign: 'middle' });
        slide.addText('SECTION ' + sectionNo, { x: 3.1, y: 2.0, w: 8.5, h: 0.5, fontSize: 16, color: colors.gold, bold: true, fontFace: 'Segoe UI', charSpacing: 3 });
        slide.addText(title, { x: 3.1, y: 2.5, w: 9.4, h: 1.1, fontSize: 36, color: colors.white, bold: true, fontFace: 'Segoe UI' });
        slide.addText(subtitle, { x: 3.1, y: 3.75, w: 9.4, h: 0.8, fontSize: 16, color: 'CBD5E1', fontFace: 'Segoe UI' });
        return slide;
    }

    // ---------------------------------------------------------------
    // Helper: content slide with image + numbered steps
    // ---------------------------------------------------------------
    function addContentSlide({ heading, kicker, filename, steps, calloutFn, imageOnRight = true }) {
        const slide = pptx.addSlide({ masterName: 'EU_MASTER' });
        slide.addText(kicker || 'STEP-BY-STEP', { x: 0.5, y: 0.85, w: 8, h: 0.3, fontSize: 11, bold: true, color: colors.gold, fontFace: 'Segoe UI', charSpacing: 2 });
        slide.addText(heading, { x: 0.5, y: 1.12, w: 12.3, h: 0.55, fontSize: 22, bold: true, color: colors.navy, fontFace: 'Segoe UI' });

        const imgBoxW = 6.7, imgBoxH = 5.55;
        const imgBox = imageOnRight
            ? { x: 6.35, y: 1.75, w: imgBoxW, h: imgBoxH }
            : { x: 0.4, y: 1.75, w: imgBoxW, h: imgBoxH };
        const textBox = imageOnRight
            ? { x: 0.45, y: 1.8, w: 5.7, h: 5.3 }
            : { x: 7.2, y: 1.8, w: 5.7, h: 5.3 };

        let placed = null;
        if (filename) {
            // Border/frame behind the screenshot
            slide.addShape('rect', {
                x: imgBox.x - 0.06, y: imgBox.y - 0.06, w: imgBoxW + 0.12, h: imgBoxH + 0.12,
                fill: { color: colors.white }, line: { color: colors.border, width: 1 },
            });
            placed = placeImage(slide, filename, { x: imgBox.x, y: imgBox.y, w: imgBoxW, h: imgBoxH });
        }

        // Step text block
        let curY = textBox.y;
        slide.addShape('rect', { x: textBox.x, y: curY, w: textBox.w, h: 0.03, fill: { color: colors.border } });
        curY += 0.12;
        steps.forEach((step, idx) => {
            const stepH = 0.9;
            slide.addShape('ellipse', {
                x: textBox.x, y: curY, w: 0.36, h: 0.36,
                fill: { color: colors.blue }, line: { type: 'none' },
            });
            slide.addText(String(idx + 1), {
                x: textBox.x, y: curY, w: 0.36, h: 0.36,
                align: 'center', valign: 'middle', fontSize: 14, bold: true, color: colors.white, fontFace: 'Segoe UI',
            });
            slide.addText(step, {
                x: textBox.x + 0.5, y: curY - 0.03, w: textBox.w - 0.5, h: stepH,
                fontSize: 12.5, color: colors.darkText, fontFace: 'Segoe UI', valign: 'top', lineSpacingMultiple: 1.08,
            });
            curY += stepH;
        });

        if (calloutFn && placed) {
            calloutFn(slide, placed);
        }

        return slide;
    }

    function addCalloutLegend(slide, x, y, text) {
        slide.addShape('roundRect', { x, y, w: 4.6, h: 0.4, rectRadius: 0.06, fill: { color: colors.gold, transparency: 90 }, line: { color: colors.gold, width: 1 } });
        slide.addText('⚠ ' + text, { x: x + 0.1, y, w: 4.4, h: 0.4, fontSize: 10, italic: true, color: colors.darkText, valign: 'middle', fontFace: 'Segoe UI' });
    }

    // =================================================================
    // TITLE SLIDE
    // =================================================================
    {
        const slide = pptx.addSlide();
        slide.background = { color: colors.navy };
        slide.addShape('rect', { x: 0, y: 0, w: SLIDE_W, h: SLIDE_H, fill: { color: colors.navy } });
        slide.addShape('rect', { x: 0, y: 4.55, w: SLIDE_W, h: 0.08, fill: { color: colors.gold } });
        slide.addText('VMRFDU ACTIVITY PORTAL', { x: 0.9, y: 1.55, w: 11.5, h: 0.5, fontSize: 18, color: colors.gold, bold: true, charSpacing: 3, fontFace: 'Segoe UI' });
        slide.addText('End User Guide', { x: 0.9, y: 2.05, w: 11.5, h: 1.3, fontSize: 48, color: colors.white, bold: true, fontFace: 'Segoe UI' });
        slide.addText('Step-by-Step Instructions for Creating and Managing Activities', { x: 0.9, y: 3.15, w: 11.0, h: 0.6, fontSize: 19, color: 'CBD5E1', fontFace: 'Segoe UI' });
        slide.addText('For: College Admin / DEO Role', { x: 0.9, y: 4.85, w: 8, h: 0.4, fontSize: 14, color: colors.gold, bold: true, fontFace: 'Segoe UI' });
        slide.addText('06 August 2026', { x: 0.9, y: 5.25, w: 8, h: 0.4, fontSize: 13, color: 'CBD5E1', fontFace: 'Segoe UI' });
        slide.addText('✓ 📤 🔒', { x: 9.7, y: 5.6, w: 2.8, h: 0.7, fontSize: 30, color: colors.gold, align: 'right' });
    }

    // =================================================================
    // AGENDA SLIDE
    // =================================================================
    {
        const slide = pptx.addSlide({ masterName: 'EU_MASTER' });
        slide.addText('In This Guide', { x: 0.5, y: 0.95, w: 10, h: 0.6, fontSize: 26, bold: true, color: colors.navy, fontFace: 'Segoe UI' });
        const items = [
            ['1', 'User Login', 'Sign in to the portal with your DEO / College Admin credentials.'],
            ['2', 'Creating a New Activity', 'Fill in the Past Event or Future Event form with your activity details.'],
            ['3', 'Submitting an Activity', 'Review, submit, and track your activity through Pending Verification.'],
            ['4', 'Profile Management', 'View your profile, edit your details, and change your password.'],
        ];
        let y = 1.9;
        items.forEach(([num, title, desc]) => {
            slide.addShape('roundRect', { x: 0.6, y, w: 12.1, h: 1.15, rectRadius: 0.1, fill: { color: colors.white }, line: { color: colors.border, width: 1 } });
            slide.addShape('ellipse', { x: 0.9, y: y + 0.28, w: 0.6, h: 0.6, fill: { color: colors.navy } });
            slide.addText(num, { x: 0.9, y: y + 0.28, w: 0.6, h: 0.6, align: 'center', valign: 'middle', fontSize: 20, bold: true, color: colors.gold, fontFace: 'Segoe UI' });
            slide.addText(title, { x: 1.85, y: y + 0.14, w: 10.5, h: 0.45, fontSize: 17, bold: true, color: colors.darkText, fontFace: 'Segoe UI' });
            slide.addText(desc, { x: 1.85, y: y + 0.58, w: 10.5, h: 0.45, fontSize: 12, color: colors.mutedText, fontFace: 'Segoe UI' });
            y += 1.35;
        });
    }

    // =================================================================
    // SECTION 1: USER LOGIN
    // =================================================================
    addDivider(1, 'User Login', 'Signing in to the VMRFDU Activity Portal', '🔒');

    addContentSlide({
        heading: 'Step 1: Open the Login Page',
        kicker: 'SECTION 1 - USER LOGIN',
        filename: '01_login_empty.png',
        steps: [
            'Open your web browser and go to the VMRFDU Activity Portal address given by your administrator.',
            'The portal shows the Login page automatically if you are not signed in.',
            'You will see two boxes: one for your Email Address and one for your Password.',
            'Make sure Caps Lock is off before you start typing your password.',
        ],
        calloutFn: (slide, placed) => {
            // Roughly the email/password field cluster on the right panel (1440x900 screenshot)
            calloutBox(slide, placed, 760, 330, 1300, 560);
            calloutNumber(slide, placed, 780, 350, 1);
        },
    });

    addContentSlide({
        heading: 'Step 2: Enter Your Credentials and Sign In',
        kicker: 'SECTION 1 - USER LOGIN',
        filename: '02_login_filled.png',
        steps: [
            'Step 1: Click the Email Address field and type your official college email address (example: deo.sahs-pdy@vmrfdu.edu.in).',
            'Step 2: Click the Password field and type your password carefully.',
            'Step 3: Click the Sign In button to continue.',
            'If your details are correct, the portal will take you straight to your Dashboard.',
        ],
        calloutFn: (slide, placed) => {
            calloutBox(slide, placed, 760, 330, 1300, 560);
        },
        imageOnRight: false,
    });

    addContentSlide({
        heading: 'Step 3: Your Dashboard',
        kicker: 'SECTION 1 - USER LOGIN',
        filename: '03_dashboard.png',
        steps: [
            'After signing in, you land on My Dashboard - your home screen.',
            'The left-hand Sidebar Menu lets you open Submit New Activity, Submission History, and My Profile.',
            'The top summary cards show Total Activities, Verification Queue, Media Studio Desk, and Published Live counts for your college.',
            'Use the blue Submit New Activity button at any time to start recording a new activity.',
        ],
        calloutFn: (slide, placed) => {
            // KPI cards row
            calloutBox(slide, placed, 285, 375, 835, 578);
            // Submit New Activity button in the welcome banner
            calloutBox(slide, placed, 965, 250, 1165, 290);
            calloutNumber(slide, placed, 1000, 270, 2);
        },
    });

    // =================================================================
    // SECTION 2: CREATING A NEW ACTIVITY
    // =================================================================
    addDivider(2, 'Creating a New Activity', 'Filling in the Past Event and Future Event forms', '📤');

    addContentSlide({
        heading: 'Step 1: Open the Activity Submission Form',
        kicker: 'SECTION 2 - PAST EVENT FLOW',
        filename: '04_create_form_empty_past.png',
        steps: [
            'Step 1: From the sidebar, click Activity Capture Desk, then Submit New Activity.',
            'Step 2: Enter the Activity / Event Title (for example, the name of your seminar or workshop).',
            'Step 3: Your Constituent College is filled in automatically and cannot be changed.',
            'Step 4: Choose the Activity Type: select "Past Event" for an event that has already happened.',
            'Step 5: Choose the Event Category, Activity Date, and Venue / Campus Location.',
        ],
        calloutFn: (slide, placed) => {
            calloutBox(slide, placed, 290, 355, 890, 410);
            calloutNumber(slide, placed, 300, 370, 1);
        },
    });

    addContentSlide({
        heading: 'Step 2: Complete Event Details (Past Event)',
        kicker: 'SECTION 2 - PAST EVENT FLOW',
        filename: '05_create_form_filled_past.png',
        steps: [
            'Step 1: Fill in Participants Attended, for example "60 Faculty Members, 220 Students attended".',
            'Step 2: Optionally add the Resource Person Name & Designation.',
            'Step 3: Describe the Outcome of the Program / Event in a few sentences.',
            'Step 4: Choose the Event Report Type - "By Text" to type a description, or "By Attach File" to upload a report document.',
            'Step 5: A social media caption is suggested for you automatically. You may edit it or click AI Generate Caption for a new suggestion.',
        ],
        calloutFn: (slide, placed) => {
            calloutBox(slide, placed, 290, 700, 1195, 950);
        },
        imageOnRight: false,
    });

    addContentSlide({
        heading: 'Step 3: Upload Photos and Supporting Files',
        kicker: 'SECTION 2 - PAST EVENT FLOW',
        filename: '06_create_form_upload_media.png',
        steps: [
            'Step 1: Under Media Attachments, click Choose Files and select your event photos. At least one Stage Photo and one Participants Photo are required.',
            'Step 2: Under Geo-Tagged Photo, click Choose Files and upload the official geo-tagged photo of the venue.',
            'Step 3: Optionally add a Photo Link, Video Clip Link, or an Event Brochure document.',
            'Step 4: Selected photos appear as small previews. Click the red X on a photo to remove it before submitting.',
        ],
        calloutFn: (slide, placed) => {
            calloutBox(slide, placed, 292, 1320, 735, 1460);
            calloutNumber(slide, placed, 300, 1335, 1);
            calloutBox(slide, placed, 758, 1320, 1197, 1460);
            calloutNumber(slide, placed, 770, 1335, 2);
        },
    });

    addContentSlide({
        heading: 'Future Event: What Is Different',
        kicker: 'SECTION 2 - FUTURE EVENT FLOW',
        filename: '09_create_form_future_event.png',
        steps: [
            'Step 1: On the Activity Type dropdown, select "Future Event" instead of "Past Event".',
            'Step 2: The Activity Date now accepts any upcoming date - it is no longer limited to today or earlier.',
            'Step 3: The Participants, Resource Person, Outcome, Report Type, and Photo Upload sections are hidden and not required, because the event has not happened yet.',
            'Step 4: Simply fill in the Title, Event Category, Date, and Location, then submit - you can add photos and outcomes later once the event takes place.',
        ],
        calloutFn: (slide, placed) => {
            calloutBox(slide, placed, 290, 355, 890, 410);
            calloutNumber(slide, placed, 300, 370, 1);
        },
        imageOnRight: false,
    });

    // =================================================================
    // SECTION 3: SUBMITTING AN ACTIVITY
    // =================================================================
    addDivider(3, 'Submitting an Activity', 'Sending your activity for institutional verification', '✓');

    addContentSlide({
        heading: 'Step 1: Review and Submit the Record',
        kicker: 'SECTION 3 - SUBMIT',
        filename: '06_create_form_upload_media.png',
        steps: [
            'Step 1: Scroll to the bottom of the form and review all the details you entered.',
            'Step 2: Click Cancel if you want to go back without saving.',
            'Step 3: Click the blue Submit Institutional Record button to send your activity for verification.',
            'Step 4: The portal automatically optimizes your uploaded images and processes your submission - please do not close the window while this happens.',
        ],
        calloutFn: (slide, placed) => {
            calloutBox(slide, placed, 942, 1855, 1197, 1905);
            calloutNumber(slide, placed, 1000, 1870, 3);
        },
    });

    addContentSlide({
        heading: 'Step 2: Confirmation and Pending Verification Status',
        kicker: 'SECTION 3 - SUBMIT',
        filename: '07_submission_pending_status.png',
        steps: [
            'Step 1: After submitting, the portal shows a green success message with your unique Reference ID (example: VMRF-2026-0020).',
            'Step 2: Your activity status is displayed as "Pending Verification" - this means your college authorities will review it next.',
            'Step 3: The Workflow Audit Trail on the right shows the submission history for this activity.',
            'Step 4: You can add follow-up notes in the Institutional Discussion Desk if needed.',
        ],
        calloutFn: (slide, placed) => {
            calloutBox(slide, placed, 415, 190, 715, 210);
            calloutNumber(slide, placed, 420, 195, 1);
            calloutBox(slide, placed, 272, 186, 415, 210);
        },
    });

    addContentSlide({
        heading: 'Step 3: Track Your Submission in the List',
        kicker: 'SECTION 3 - SUBMIT',
        filename: '08_submissions_list_pending.png',
        steps: [
            'Step 1: From the sidebar, click Activity Capture Desk, then Submission History to see all your submitted activities.',
            'Step 2: Your new activity appears at the top of the Institutional Activity Directory Table with a "Pending Verification" status badge.',
            'Step 3: Use Approval Status, From Date, To Date, or the search box to find a specific activity.',
            'Step 4: Click View next to any record to see its full details and audit trail.',
        ],
        calloutFn: (slide, placed) => {
            calloutBox(slide, placed, 1133, 780, 1263, 805);
            calloutNumber(slide, placed, 1140, 785, 1);
        },
        imageOnRight: false,
    });

    // =================================================================
    // SECTION 4: PROFILE MANAGEMENT
    // =================================================================
    addDivider(4, 'Profile Management', 'Viewing your profile, editing details, and changing your password', '🔒');

    addContentSlide({
        heading: 'Step 1: View Your Profile',
        kicker: 'SECTION 4 - PROFILE',
        filename: '10_profile_view.png',
        steps: [
            'Step 1: Click My Profile in the left sidebar under Account Settings.',
            'Step 2: Your name, email, role, assigned college, and account status appear on the left card.',
            'Step 3: Fields marked with a lock icon (Role, College, Account State) are managed only by the Super Administrator and cannot be changed here.',
            'Step 4: Use the two tabs on the right - Edit Profile Info and Change Password - to manage your account.',
        ],
        calloutFn: (slide, placed) => {
            calloutBox(slide, placed, 671, 205, 1400, 245);
            calloutNumber(slide, placed, 690, 210, 1);
        },
    });

    addContentSlide({
        heading: 'Step 2: Edit Your Profile Information',
        kicker: 'SECTION 4 - PROFILE',
        filename: '11_profile_edit_form.png',
        steps: [
            'Step 1: On the Edit Profile Info tab, update your Full Name if needed.',
            'Step 2: Your Official Email Address is system-protected and cannot be edited here.',
            'Step 3: Add or update your Mobile / Phone Number and Official Designation.',
            'Step 4: Optionally upload a new Profile Photo (JPG or PNG, max 2MB).',
            'Step 5: Click Save Profile Changes to update your details.',
        ],
        calloutFn: (slide, placed) => {
            calloutBox(slide, placed, 671, 205, 1035, 245);
            calloutNumber(slide, placed, 690, 210, 1);
        },
        imageOnRight: false,
    });

    addContentSlide({
        heading: 'Step 3: Change Your Password',
        kicker: 'SECTION 4 - PROFILE',
        filename: '12_profile_change_password.png',
        steps: [
            'Step 1: On your profile page, click the Change Password tab.',
            'Step 2: Enter your Current Password to confirm your identity.',
            'Step 3: Type a New Password (minimum 6 characters), then re-enter it in Confirm New Password.',
            'Step 4: Click Update Security Password to save your new password.',
            'Tip: Choose a strong password and keep it confidential. Change it periodically for account security.',
        ],
        calloutFn: (slide, placed) => {
            calloutBox(slide, placed, 1136, 208, 1400, 250);
            calloutNumber(slide, placed, 1150, 215, 1);
            calloutBox(slide, placed, 1136, 555, 1375, 588);
            calloutNumber(slide, placed, 1150, 565, 2);
        },
    });

    // =================================================================
    // CLOSING SLIDE
    // =================================================================
    {
        const slide = pptx.addSlide();
        slide.background = { color: colors.navy };
        slide.addShape('rect', { x: 0, y: 0, w: SLIDE_W, h: SLIDE_H, fill: { color: colors.navy } });
        slide.addShape('rect', { x: 0, y: 3.9, w: SLIDE_W, h: 0.06, fill: { color: colors.gold } });
        slide.addText('✓', { x: 0, y: 1.4, w: SLIDE_W, h: 1.4, fontSize: 60, color: colors.gold, align: 'center' });
        slide.addText('You Are Ready to Use the VMRFDU Activity Portal', { x: 0.9, y: 2.9, w: 11.5, h: 0.8, fontSize: 26, bold: true, color: colors.white, align: 'center', fontFace: 'Segoe UI' });
        slide.addText('For further assistance, contact your Institutional Support Desk: 1880-052-58015', { x: 0.9, y: 4.1, w: 11.5, h: 0.5, fontSize: 14, color: 'CBD5E1', align: 'center', fontFace: 'Segoe UI' });
    }

    await pptx.writeFile({ fileName: OUTPUT_FILE });
    console.log('PPTX written to: ' + OUTPUT_FILE);

    // Report slide count and file size
    const AdmZip = (() => { try { return require('adm-zip'); } catch (e) { return null; } })();
    const stat = fs.statSync(OUTPUT_FILE);
    console.log('File size: ' + (stat.size / 1024).toFixed(1) + ' KB');

    // Count slides via slide XML files inside the pptx (zip) if adm-zip available; else count addSlide calls manually.
    console.log('Slides generated by script (addSlide calls tracked): see log above.');
}

main().catch((err) => {
    console.error('Error generating presentation:', err);
    process.exit(1);
});
