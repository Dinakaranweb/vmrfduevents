const PptxGenJS = require("pptxgenjs");
const fs = require("fs");
const path = require("path");

const SS_DIR = path.join(__dirname, "screenshots");
const OUT = path.join(__dirname, "VMRFDU_Activity_Portal_User_Manual.pptx");

// ── Color Palette (matches generate_presentation.cjs for visual consistency) ──
const colors = {
    navy: "0A192F",
    blue: "1E3A8A",
    gold: "D97706",
    lightGray: "F8FAFC",
    darkText: "1E293B",
    mutedText: "64748B",
    white: "FFFFFF",
    green: "047857",
    red: "B91C1C",
    cardBg: "FFFFFF",
    border: "E2E8F0",
};

// ── Role metadata: order, label, accent color, badge ─────────────────────────
const ROLE_INFO = {
    super_admin: { label: "Super Admin", color: "B45309", order: 1, account: "vmu.web@vmu.edu.in" },
    vc: { label: "Vice Chancellor (VC)", color: "1E3A8A", order: 2, account: "vc@vmrfdu.edu.in" },
    provc: { label: "Pro Vice Chancellor (Pro VC)", color: "3730A3", order: 3, account: "provc@vmrfdu.edu.in" },
    registrar: { label: "Registrar", color: "0F766E", order: 4, account: "registrar@vmrfdu.edu.in" },
    office_registrar: { label: "Office of Registrar", color: "6B21A8", order: 5, account: "officeregistrar@vmrfdu.edu.in" },
    media_editor: { label: "Media Editor", color: "D97706", order: 6, account: "editor@vmrfdu.edu.in" },
    college_admin: { label: "College Admin / DEO", color: "166534", order: 7, account: "deo.sahs-pdy@vmrfdu.edu.in" },
};

const LOGIN_TABLE = [
    ["Role", "Login Email", "Password"],
    ["Super Admin", "vmu.web@vmu.edu.in", "password"],
    ["Vice Chancellor (VC)", "vc@vmrfdu.edu.in", "password"],
    ["Pro Vice Chancellor (Pro VC)", "provc@vmrfdu.edu.in", "password"],
    ["Registrar", "registrar@vmrfdu.edu.in", "password"],
    ["Office of Registrar", "officeregistrar@vmrfdu.edu.in", "password"],
    ["Media Editor", "editor@vmrfdu.edu.in", "password"],
    ["College Admin (SAHS-PDY DEO)", "deo.sahs-pdy@vmrfdu.edu.in", "password"],
];

// ── Screenshot file list (mirrors docs/capture_screenshots.cjs SHOTS) ────────
// { file, role, title, caption }
function humanize(key) {
    return key
        .split("_")
        .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
        .join(" ");
}

// Build title + caption from filename heuristics.
function describe(role, key) {
    const roleLabel = ROLE_INFO[role] ? ROLE_INFO[role].label : humanize(role);
    const rest = key.replace(new RegExp("^" + role + "_"), "").replace(/^provc_/, "");
    const niceRest = humanize(rest.replace(/provc_/, ""));

    const captionMap = {
        dashboard: "The role's homepage: key stats, pending counts, and quick links to core actions.",
        verification_queue: "Stage 1 review queue. Approve, Discuss, or Reject each submitted activity with mandatory remarks on Discuss/Reject.",
        approval_queue: "Pending approvals awaiting this role's decision. Approve, Discuss, or Reject with remarks.",
        approved_list: "Historical list of activities already approved by this role, with timestamps and downstream status.",
        rejected_list: "Archive of rejected activities with the mandatory rejection reason and timestamp for each record.",
        analytics: "Analytics dashboard with charts on submission volume, categories, and trends. Supports CSV/print export.",
        submissions_view: "Read-only global view of all activity submissions across colleges and statuses, for oversight.",
        submissions: "List of submissions scoped to this account, filterable by status, with reference IDs and actions.",
        notifications: "In-app notification inbox. Alerts on stage transitions, feedback, approvals, and publication.",
        profile: "Personal profile page: edit name/phone/designation, upload an avatar, and change password.",
        colleges: "Manage constituent colleges: create, edit, and toggle active status with unique college codes.",
        users: "Create and manage user accounts: assign roles, colleges, lock/unlock, and force password resets.",
        roles: "Build dynamic roles with JSON permission matrices and assign global or college-scoped access.",
        workflows: "Configure multi-stage approval workflow templates: stage order, roles, and allowed actions.",
        access_control: "Per-user permission overrides beyond role defaults, and cross-role permission matrix view.",
        activities: "Unrestricted activity management: force status changes, reassign, restore, or hard-delete records.",
        audit_logs: "Immutable system-wide audit trail of every critical action, with actor, IP, and timestamp.",
        settings: "Portal-wide branding and configuration: logo, favicon, NTP sync, and session settings.",
        email_logs: "Complete email transmission log with delivery status and full HTML body preview.",
        media_queue: "Queue of media packages awaiting this role's review before broadcast.",
        review_queue: "Stage 2 executive review queue for locked media packages. Approve to publish or discuss to return.",
        approvals: "Full audit view of approvals and discussions handled by this role, with decision timeline.",
        queue: "Media Studio editing queue: photo optimization and caption refinement before submission.",
        locked: "Locked submissions pending Stage 2 review — read-only until the Office of Registrar resolves them.",
        ready: "Activities marked ready to publish after passing every approval stage.",
        studio_queue: "Media Studio combined view: editing queue plus locked records pending Stage 2 resolution.",
        create_activity: "Activity submission form: title, description, category, photos, optional PDF, and AI caption generator. Save as draft or submit.",
    };

    let caption = captionMap[rest];
    if (!caption) {
        // try suffix match e.g. college_admin_create_activity -> create_activity
        const parts = rest.split("_");
        for (let i = 0; i < parts.length; i++) {
            const suffix = parts.slice(i).join("_");
            if (captionMap[suffix]) { caption = captionMap[suffix]; break; }
        }
    }
    if (!caption) caption = `The ${niceRest} page for the ${roleLabel} role.`;

    return { title: `${roleLabel}: ${niceRest}`, caption };
}

// Parse the SHOTS from capture_screenshots.cjs so this stays in sync automatically.
function loadShots() {
    const src = fs.readFileSync(path.join(__dirname, "capture_screenshots.cjs"), "utf8");
    const shotsBlockMatch = src.match(/const SHOTS = \[([\s\S]*?)\n\];/);
    if (!shotsBlockMatch) throw new Error("Could not locate SHOTS array in capture_screenshots.cjs");
    const block = shotsBlockMatch[1];
    const entryRe = /\{\s*file:\s*'([^']+)',\s*url:\s*'([^']+)',\s*role:\s*(null|'([^']+)')\s*\}/g;
    const shots = [];
    let m;
    while ((m = entryRe.exec(block))) {
        const file = m[1];
        const url = m[2];
        const role = m[4] || null;
        shots.push({ file, url, role });
    }
    return shots;
}

async function generate() {
    console.log("Loading screenshot manifest...");
    const shots = loadShots();
    console.log(`Found ${shots.length} screenshot entries.`);

    const pptx = new PptxGenJS();
    pptx.layout = "LAYOUT_16x9";

    pptx.defineSlideMaster({
        title: "MASTER_SLIDE",
        background: { color: colors.lightGray },
        objects: [
            { rect: { x: 0, y: 0, w: "100%", h: 0.75, fill: { color: colors.navy } } },
            { rect: { x: 0, y: 0.75, w: "100%", h: 0.05, fill: { color: colors.gold } } },
            { rect: { x: 0, y: 7.35, w: "100%", h: 0.15, fill: { color: colors.navy } } },
        ],
    });

    function addHeader(slide, roleText, titleText) {
        slide.addText(roleText.toUpperCase(), { x: 0.4, y: 0.06, fontSize: 10, color: colors.gold, bold: true, fontFace: "Segoe UI" });
        slide.addText(titleText, { x: 0.4, y: 0.3, w: 12.0, fontSize: 18, color: colors.white, bold: true, fontFace: "Segoe UI" });
    }

    // ── Slide 1: Title ────────────────────────────────────────────────────
    const todayStr = new Date().toLocaleDateString("en-GB", { day: "2-digit", month: "long", year: "numeric" });
    let s1 = pptx.addSlide();
    s1.background = { color: colors.navy };
    s1.addShape(pptx.ShapeType.rect, { x: 0, y: 4.8, w: "100%", h: 0.08, fill: { color: colors.gold } });
    s1.addText("VINAYAKA MISSION'S RESEARCH FOUNDATION (DU)", { x: 1.0, y: 1.6, fontSize: 15, color: colors.gold, bold: true, fontFace: "Segoe UI" });
    s1.addText("VMRFDU Activity Portal — User Manual", { x: 1.0, y: 2.2, fontSize: 34, color: colors.white, bold: true, fontFace: "Segoe UI" });
    s1.addText("Step-by-Step Guide: Login to All Roles", { x: 1.0, y: 3.15, fontSize: 18, color: "94A3B8", fontFace: "Segoe UI" });
    s1.addText(todayStr, { x: 1.0, y: 5.1, fontSize: 13, color: colors.white, fontFace: "Segoe UI" });
    s1.addText("Covers: Super Admin • Vice Chancellor • Pro Vice Chancellor • Registrar • Office of Registrar • Media Editor • College Admin", {
        x: 1.0, y: 5.5, w: 11.0, fontSize: 12, color: "CBD5E1", fontFace: "Segoe UI",
    });

    // ── Slide 2: How to Login ────────────────────────────────────────────
    let s2 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(s2, "Getting Started", "How to Login");

    const loginShot = shots.find((s) => s.file === "01_login_page.png");
    const loginImgPath = path.join(SS_DIR, "01_login_page.png");
    if (loginShot && fs.existsSync(loginImgPath)) {
        s2.addImage({ path: loginImgPath, x: 0.4, y: 1.0, w: 5.6, h: 3.9, sizing: { type: "contain", w: 5.6, h: 3.9 } });
    }
    s2.addText(
        "Navigate to the portal's /login page, enter your registered email and password, then click Sign In. The system validates your account status (active/locked), enforces any pending force-password-reset, and redirects you to your role-specific dashboard.",
        { x: 6.2, y: 1.0, w: 6.4, fontSize: 12, color: colors.darkText, lineSpacing: 16, fontFace: "Segoe UI" }
    );

    const tableRows = LOGIN_TABLE.map((row, i) => {
        const isHeader = i === 0;
        return row.map((cell) => ({
            text: cell,
            options: {
                bold: isHeader,
                color: isHeader ? colors.white : colors.darkText,
                fill: { color: isHeader ? colors.blue : (i % 2 === 0 ? colors.white : "F1F5F9") },
                fontSize: 10,
                fontFace: "Segoe UI",
                valign: "middle",
            },
        }));
    });
    s2.addTable(tableRows, {
        x: 6.2, y: 2.5, w: 6.4, h: 3.8,
        colW: [2.3, 2.7, 1.4],
        border: { type: "solid", color: colors.border, pt: 0.5 },
        autoPage: false,
    });

    // ── Group shots by role, in desired order ────────────────────────────
    const byRole = {};
    for (const s of shots) {
        if (!s.role) continue; // skip login/public shots, handled above/separately
        (byRole[s.role] = byRole[s.role] || []).push(s);
    }

    const roleOrder = Object.keys(ROLE_INFO).sort((a, b) => ROLE_INFO[a].order - ROLE_INFO[b].order);

    // Public gallery gets its own slide right after login.
    let s3 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(s3, "Public Access", "Public Gallery (No Login Required)");
    const galleryPath = path.join(SS_DIR, "02_public_gallery.png");
    if (fs.existsSync(galleryPath)) {
        s3.addImage({ path: galleryPath, x: 0.8, y: 1.0, w: 11.7, h: 6.1, sizing: { type: "contain", w: 11.7, h: 6.1 } });
    }
    s3.addText(
        "Publicly accessible showcase of all published institutional activities — searchable by keyword, category, college, and date range. No login required.",
        { x: 0.4, y: 7.0, w: 12.5, fontSize: 10, color: colors.mutedText, fontFace: "Segoe UI" }
    );

    let totalContentSlides = 0;

    for (const roleKey of roleOrder) {
        const roleShots = byRole[roleKey];
        if (!roleShots || roleShots.length === 0) continue;
        const info = ROLE_INFO[roleKey];

        // Section divider slide for this role
        let divider = pptx.addSlide();
        divider.background = { color: colors.navy };
        divider.addShape(pptx.ShapeType.rect, { x: 0, y: 3.7, w: "100%", h: 0.06, fill: { color: colors.gold } });
        divider.addText(info.label, { x: 1.0, y: 3.0, fontSize: 30, color: colors.white, bold: true, fontFace: "Segoe UI" });
        divider.addText(`Account: ${info.account}`, { x: 1.0, y: 3.85, fontSize: 14, color: "94A3B8", fontFace: "Segoe UI" });
        divider.addText(`${roleShots.length} page${roleShots.length > 1 ? "s" : ""} covered in this section`, { x: 1.0, y: 4.25, fontSize: 12, color: colors.gold, fontFace: "Segoe UI" });

        // Pair 2-per-slide to keep slide count reasonable
        for (let i = 0; i < roleShots.length; i += 2) {
            const pair = roleShots.slice(i, i + 2);
            let slide = pptx.addSlide({ masterName: "MASTER_SLIDE" });
            const key0 = pair[0].file.replace(/\.png$/, "").replace(/^\d+_/, "");
            const d0 = describe(roleKey, key0);
            addHeader(slide, info.label, pair.length === 2 ? `${d0.title.split(": ")[1]} & More` : d0.title);

            const slotW = pair.length === 2 ? 6.0 : 8.0;
            const startXs = pair.length === 2 ? [0.3, 6.7] : [2.65];

            pair.forEach((shot, idx) => {
                const key = shot.file.replace(/\.png$/, "").replace(/^\d+_/, "");
                const d = describe(roleKey, key);
                const imgPath = path.join(SS_DIR, shot.file);
                const x = startXs[idx];
                if (fs.existsSync(imgPath)) {
                    slide.addImage({ path: imgPath, x, y: 1.05, w: slotW - 0.3, h: 5.3, sizing: { type: "contain", w: slotW - 0.3, h: 5.3 } });
                } else {
                    slide.addText(`Missing screenshot: ${shot.file}`, { x, y: 3.0, w: slotW - 0.3, fontSize: 12, color: colors.red });
                }
                slide.addText(d.title, { x, y: 6.4, w: slotW - 0.3, fontSize: 12, bold: true, color: colors.navy, fontFace: "Segoe UI" });
                slide.addText(d.caption, { x, y: 6.75, w: slotW - 0.3, h: 0.55, fontSize: 9, color: colors.mutedText, fontFace: "Segoe UI", lineSpacing: 11 });
            });

            totalContentSlides++;
        }
    }

    console.log(`Built ${totalContentSlides} content slides (2-up pairing) + section dividers + title/login/gallery.`);
    console.log(`Total slides: ${pptx.slides ? pptx.slides.length : "unknown"}`);

    await pptx.writeFile({ fileName: OUT });
    console.log("Saved:", OUT);
}

generate().catch((e) => {
    console.error(e);
    process.exit(1);
});
