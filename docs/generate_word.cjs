const {
  Document, Packer, Paragraph, TextRun, ImageRun, Table, TableRow, TableCell,
  HeadingLevel, AlignmentType, BorderStyle, WidthType, ShadingType,
  PageBreak, Header, Footer, PageNumber, convertInchesToTwip,
} = require('docx');
const fs   = require('fs');
const path = require('path');

const SS_DIR = path.join(__dirname, 'screenshots');
const OUT    = path.join(__dirname, 'VMRFDU_Activity_Portal_Documentation.docx');

// ── Unit helpers ────────────────────────────────────────────────────────────
const PT  = n => n * 20;
const EMU = n => n * 914400;

function getPngDims(buf) {
  if (buf[1] === 0x50 && buf[2] === 0x4e && buf[3] === 0x47)
    return { w: buf.readUInt32BE(16), h: buf.readUInt32BE(20) };
  return { w: 1440, h: 900 };
}

function imgRun(file, widthIn = 6.1) {
  const fp = path.join(SS_DIR, file);
  if (!fs.existsSync(fp)) return null;
  const buf = fs.readFileSync(fp);
  const d   = getPngDims(buf);
  const w   = EMU(widthIn);
  const h   = Math.round(w * d.h / d.w);
  return new ImageRun({ data: buf, transformation: { width: w, height: h }, type: 'png' });
}

// ── Text helpers ─────────────────────────────────────────────────────────────
const R = (text, opts = {}) => new TextRun({ text, font: 'Calibri', ...opts });

const bold   = (text, sz = 22, color = '1e293b') => R(text, { bold: true,  size: sz, color });
const normal = (text, sz = 20, color = '374151') => R(text, { bold: false, size: sz, color });
const italic = (text, sz = 19, color = '64748b') => R(text, { italics: true, size: sz, color });

const P = (children, opts = {}) =>
  new Paragraph({ children: Array.isArray(children) ? children : [children],
                  spacing: { after: PT(5) }, ...opts });

const divider = (color = 'e2e8f0') =>
  new Paragraph({ children: [], spacing: { before: PT(4), after: PT(4) },
                  border: { bottom: { color, size: 6, style: BorderStyle.SINGLE } } });

const pageBreak = () => new Paragraph({ children: [new PageBreak()] });

// ── Table helpers ─────────────────────────────────────────────────────────────
const BORDER_NONE = { style: BorderStyle.NONE };
const BORDER = (color, sz = 4) => ({ style: BorderStyle.SINGLE, size: sz, color });

function cell(children, opts = {}) {
  const { fill = 'ffffff', w, bold: isBold, margins, borders } = opts;
  return new TableCell({
    children: Array.isArray(children) ? children : [P(Array.isArray(children) ? children : [children])],
    ...(w ? { width: { size: w, type: WidthType.PERCENTAGE } } : {}),
    shading: { type: ShadingType.CLEAR, fill },
    margins: margins || { top: 80, bottom: 80, left: 120, right: 80 },
    ...(borders ? { borders } : {}),
  });
}

// ── Cover page ────────────────────────────────────────────────────────────────
function buildCover() {
  return [
    P([R('', { size: 4 })], { spacing: { after: PT(40) } }),

    // University name
    P([bold('VMRF(DU)', 56, '0f2744')],
      { alignment: AlignmentType.CENTER, spacing: { after: PT(2) } }),
    P([bold('Vinayaka Mission\'s Research Foundation', 26, '2563eb'),
       bold(' (Deemed to be University)', 26, '2563eb')],
      { alignment: AlignmentType.CENTER, spacing: { after: PT(16) } }),

    divider('fbbf24'),
    P([R('', { size: 4 })], { spacing: { after: PT(10) } }),

    P([bold('INSTITUTIONAL ACTIVITY PORTAL', 40, '0f2744')],
      { alignment: AlignmentType.CENTER, spacing: { after: PT(6) } }),
    P([normal('Complete Screenshots Documentation', 28, '2563eb')],
      { alignment: AlignmentType.CENTER, spacing: { after: PT(4) } }),
    P([italic('All Modules  ·  All Pages  ·  All User Interfaces', 22, '64748b')],
      { alignment: AlignmentType.CENTER, spacing: { after: PT(24) } }),

    divider('2563eb'),
    P([R('', { size: 4 })], { spacing: { after: PT(14) } }),

    // Meta table
    new Table({
      width: { size: 100, type: WidthType.PERCENTAGE },
      borders: { top: BORDER('bfdbfe'), bottom: BORDER('bfdbfe'), left: BORDER('bfdbfe'),
                 right: BORDER('bfdbfe'), insideH: BORDER('dbeafe',2), insideV: BORDER_NONE },
      rows: [
        ['Document Type',    'Screenshots & UI Visual Reference — All User Roles'],
        ['Screenshots',      '42 Full-Page Screenshots captured from live portal'],
        ['User Accounts',    '7 Accounts  |  7 User Roles  |  9 Functional Modules'],
        ['Technology Stack', 'Laravel 12  ·  PHP 8.2+  ·  MySQL 8.0  ·  Bootstrap 5.3'],
        ['Prepared by',      'VMRFDU IT Team (Dhinakaran & Sanjai)'],
        ['Guided by',        'Dr. A. Murugan'],
        ['Version / Date',   '1.0  |  July 2026'],
      ].map(([label, value]) => new TableRow({ children: [
        cell([P([bold(label, 19, '1e3a8a')])],  { fill: 'eff6ff', w: 32 }),
        cell([P([normal(value, 19)])],            { fill: 'ffffff', w: 68 }),
      ]})),
    }),

    P([R('', { size: 4 })], { spacing: { after: PT(28) } }),
    divider('fbbf24'),
    P([R('', { size: 4 })], { spacing: { after: PT(10) } }),

    // Credits
    P([bold('Under the Guidance of : ', 24, '0f2744'),
       bold('Dr. A. Murugan', 24, '1e3a8a')],
      { alignment: AlignmentType.CENTER, spacing: { after: PT(6) } }),

    P([bold('Designed & Developed by : ', 24, '0f2744'),
       bold('VMRFDU IT Team  (Dhinakaran & Sanjai)', 24, '16a34a')],
      { alignment: AlignmentType.CENTER, spacing: { after: PT(8) } }),

    P([italic('© 2026  VMRF(DU) – All Rights Reserved.', 19, '94a3b8')],
      { alignment: AlignmentType.CENTER }),

    pageBreak(),
  ];
}

// ── Chapter header ─────────────────────────────────────────────────────────────
function chapterBanner(num, title, subtitle, fill = '1e3a8a') {
  return [
    pageBreak(),
    new Table({
      width: { size: 100, type: WidthType.PERCENTAGE },
      borders: { top: BORDER_NONE, bottom: BORDER_NONE, left: BORDER_NONE,
                 right: BORDER_NONE, insideH: BORDER_NONE, insideV: BORDER('ffffff', 2) },
      rows: [new TableRow({ children: [
        cell([P([bold(String(num), 34, 'ffffff')], { alignment: AlignmentType.CENTER })],
          { fill, w: 9, margins: { top: 220, bottom: 220, left: 60, right: 60 } }),
        cell([
          P([bold(title, 28, 'ffffff')], { spacing: { after: PT(3) } }),
          P([normal(subtitle, 17, 'dbeafe')], { spacing: { after: 0 } }),
        ], { fill, w: 91, margins: { top: 180, bottom: 180, left: 200, right: 120 } }),
      ]})],
    }),
    P([R('', { size: 4 })], { spacing: { after: PT(10) } }),
  ];
}

// ── Screenshot card ───────────────────────────────────────────────────────────
function ssCard(num, total, badge, badgeFill, title, description, annotations, imgFile) {
  const blocks = [];

  // Header row: badge | title | number
  blocks.push(new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    borders: { top: BORDER('e2e8f0'), left: BORDER('e2e8f0'), right: BORDER('e2e8f0'),
               bottom: BORDER_NONE, insideH: BORDER_NONE, insideV: BORDER('e2e8f0', 2) },
    rows: [new TableRow({ children: [
      cell([P([bold(badge, 17, 'ffffff')], { spacing: { after: 0 } })],
        { fill: badgeFill, w: 18, margins: { top: 90, bottom: 90, left: 120, right: 80 } }),
      cell([P([bold(title, 22, '1e293b')], { spacing: { after: 0 } })],
        { fill: 'f8fafc', w: 67, margins: { top: 90, bottom: 90, left: 160, right: 80 } }),
      cell([P([bold(`${num} / ${total}`, 16, '94a3b8')],
          { alignment: AlignmentType.RIGHT, spacing: { after: 0 } })],
        { fill: 'f8fafc', w: 15, margins: { top: 90, bottom: 90, left: 60, right: 140 } }),
    ]})],
  }));

  // Description bar
  blocks.push(new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    borders: { top: BORDER_NONE, bottom: BORDER_NONE, left: BORDER_NONE,
               right: BORDER_NONE, insideH: BORDER_NONE, insideV: BORDER_NONE },
    rows: [new TableRow({ children: [
      cell([P([italic(description, 19, '475569')], { spacing: { after: 0 } })], {
        fill: 'f0f9ff',
        margins: { top: 90, bottom: 90, left: 160, right: 160 },
        borders: {
          top: BORDER_NONE, bottom: BORDER_NONE, right: BORDER_NONE,
          left: { style: BorderStyle.SINGLE, size: 18, color: '2563eb' },
        },
      }),
    ]})],
  }));

  // Screenshot image
  const img = imgRun(imgFile, 6.1);
  if (img) {
    blocks.push(new Paragraph({
      children: [img],
      alignment: AlignmentType.CENTER,
      spacing: { before: PT(4), after: PT(4) },
      border: { top: BORDER('e2e8f0'), bottom: BORDER('e2e8f0'),
                left: BORDER('e2e8f0'), right: BORDER('e2e8f0') },
    }));
  } else {
    blocks.push(P([normal(`[Screenshot file not found: ${imgFile}]`, 17, 'dc2626')]));
  }

  // Annotation strip
  blocks.push(new Paragraph({
    children: annotations.flatMap((a, i) => [
      ...(i > 0 ? [R('    ', { size: 17 })] : []),
      R(a, { size: 17, bold: true, color: '1e40af' }),
    ]),
    spacing: { before: PT(3), after: PT(10) },
    border: { top: BORDER('bfdbfe', 2), bottom: BORDER('bfdbfe', 2),
              left: BORDER('bfdbfe', 2), right: BORDER('bfdbfe', 2) },
    shading: { type: ShadingType.CLEAR, fill: 'eff6ff' },
    indent: { left: convertInchesToTwip(0.1), right: convertInchesToTwip(0.1) },
  }));

  blocks.push(divider());
  return blocks;
}

// ── Summary index table ───────────────────────────────────────────────────────
function buildIndex(rows) {
  const hdr = ['#', 'User / Role', 'Page / Screen', 'Key Features'].map(t =>
    cell([P([bold(t, 19, 'ffffff')], { spacing: { after: 0 } })], { fill: '1e3a8a' })
  );
  return new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    borders: { top: BORDER('bfdbfe'), bottom: BORDER('bfdbfe'), left: BORDER('bfdbfe'),
               right: BORDER('bfdbfe'), insideH: BORDER('e2e8f0', 2), insideV: BORDER('e2e8f0', 2) },
    rows: [
      new TableRow({ children: hdr, tableHeader: true }),
      ...rows.map(([num, role, page, feat], i) =>
        new TableRow({ children: [
          cell([P([bold(num, 17, '94a3b8')], { spacing: { after: 0 } })],   { fill: i % 2 ? 'f8fafc' : 'ffffff', w: 6  }),
          cell([P([bold(role, 18, '1e3a8a')], { spacing: { after: 0 } })],  { fill: i % 2 ? 'f8fafc' : 'ffffff', w: 24 }),
          cell([P([normal(page, 18)], { spacing: { after: 0 } })],           { fill: i % 2 ? 'f8fafc' : 'ffffff', w: 32 }),
          cell([P([italic(feat, 17)], { spacing: { after: 0 } })],           { fill: i % 2 ? 'f8fafc' : 'ffffff', w: 38 }),
        ]})),
    ],
  });
}

// ── Main builder ──────────────────────────────────────────────────────────────
async function build() {
  const children = [];

  // ── Cover ──────────────────────────────────────────────────────────────────
  children.push(...buildCover());

  // ══ CHAPTER 1 — PUBLIC ══════════════════════════════════════════════════════
  children.push(...chapterBanner(1,
    '🌐  Public Access & Authentication',
    'Login Page  ·  Public Gallery  —  No login required', '334155'));

  children.push(...ssCard('01', '50', 'PUBLIC', '475569',
    'Login Page',
    'Secure authentication gateway for all portal users. Validates email + password, checks account status (active / locked / suspended), enforces force-password-reset, and redirects each user to their role-specific dashboard. Login IP address and timestamp are recorded on every successful session.',
    ['🔐 Email + Password Auth', '🛡️ CSRF Protected', '🚫 Locked/Suspended Detection', '📍 IP & Timestamp Tracking'],
    '01_login_page.png'));

  children.push(...ssCard('02', '50', 'PUBLIC', '166534',
    'Public Gallery',
    'Publicly accessible showcase of all published institutional activities — no login required. Visitors can browse using full-text search, category filter, college filter, and date-range filter. Responsive image grid layout with links to individual activity detail pages.',
    ['🔍 Full-Text Search', '🏷️ Category Filter', '📅 Date Range Filter', '🏫 College Filter', '🖼️ Responsive Image Grid'],
    '02_public_gallery.png'));

  // ══ CHAPTER 2 — SUPER ADMIN ══════════════════════════════════════════════════
  children.push(...chapterBanner(2,
    '👑  Super Admin — Enterprise Control Panel',
    'superadmin@vmrfdu.edu.in  ·  Role: super_admin  ·  Unrestricted system-wide access', 'b45309'));

  const saPages = [
    ['04', 'super_admin_dashboard',       'Dashboard',
     'System-wide enterprise overview: total activities, users, colleges, workflow status, pending actions across all roles, recent activity feed, and quick-access navigation to every admin module.',
     ['📊 System-Wide KPIs', '👥 Total Users & Colleges', '🔄 Workflow Status', '⚡ Quick Actions']],
    ['05', 'super_admin_colleges',         'College Management',
     'Full CRUD management of all constituent colleges. Create with unique codes (e.g., VMKVEC, AVIT), set location, dean name, contact email and website. Toggle colleges active or inactive. All user and activity records reference these college entries.',
     ['➕ Create College', '✏️ Edit Details', '🔘 Active/Inactive Toggle', '🏫 Unique College Code']],
    ['06', 'super_admin_users',            'User Management',
     'Create and manage all portal user accounts across the entire university. Assign roles, link to colleges, set phone and designation, force password resets, lock or unlock accounts, change account status, and transfer users between colleges.',
     ['➕ Create User', '🎭 Role Assignment', '🔒 Lock/Unlock Account', '🔑 Force Password Reset', '🏫 College Transfer']],
    ['07', 'super_admin_roles',            'Role & Permission Builder',
     'Create dynamic roles with granular JSON permission matrices. Assign data scopes (global vs. college-scoped). Define permissions for each module area: colleges, users, activities, workflows, approvals, media, analytics, settings, roles, and audit logs.',
     ['🎭 Role Definitions', '🔑 JSON Permission Matrix', '🌐 Data Scope (Global/College)', '⚙️ Permission Overrides']],
    ['08', 'super_admin_workflows',        'Workflow Engine Configuration',
     'Create, edit, and duplicate complete approval workflow templates. Configure each stage: name, order, required role or specific user IDs, allowed actions (approve/discuss/reject), status transitions for each action, and approval conditions. Set routing rules by category, college, or activity type.',
     ['🔄 Multi-Stage Workflow', '⚙️ Stage Configuration', '📋 Action-to-Status Mapping', '🌿 Routing Rules by Category']],
    ['09', 'super_admin_access_control',   'Enterprise Access Control Suite',
     'Fine-grained per-user permission overrides beyond role defaults. Copy a role\'s full permission set to multiple users simultaneously. View and manage the complete cross-user, cross-role permission matrix from one interface.',
     ['🔑 Per-User Permission Override', '📋 Copy Role Permissions', '🎭 Full Cross-Role Matrix']],
    ['10', 'super_admin_activities',       'Unrestricted Activity Management',
     'Super Admin override view of all activity submissions across every college and every workflow stage. Force status changes, reassign activity ownership, restore soft-deleted records, and perform hard deletes — all without workflow restrictions.',
     ['⚡ Force Status Change', '♻️ Restore Soft-Deleted', '👤 Reassign Ownership', '🗑️ Hard Delete Authority']],
    ['11', 'super_admin_audit_logs',       'System Audit Logs',
     'Immutable system-wide audit trail. Every critical action logged with: acting user, action type, affected record ID, before/after values, IP address, and precise timestamp. These records cannot be edited or deleted — providing complete governance and compliance coverage.',
     ['📜 Immutable Log Records', '👤 User + IP Captured', '🔍 Before/After Values', '📅 Precise Timestamps']],
    ['12', 'super_admin_settings',         'Master Settings & Branding',
     'Portal-wide configuration interface: upload the university logo and favicon, set the default user avatar image, configure NTP time-synchronisation settings, and manage session lifetime parameters — controlling the portal\'s visual identity and core behaviour.',
     ['🖼️ Logo & Favicon Upload', '🕐 NTP Time Sync Config', '⏱️ Session Lifetime', '🎨 Portal Branding']],
    ['13', 'super_admin_analytics',        'Analytics Dashboard (Super Admin)',
     'University-wide KPI dashboard with full cross-college data visibility. Charts: activities by category, by college, by workflow stage, and by month. Export options: filtered CSV download and a printable executive report with university branding.',
     ['📊 University-Wide KPIs', '📈 Category & College Charts', '📥 CSV Export with Filters', '🖨️ Printable Executive Report']],
    ['14', 'super_admin_notifications',    'In-App Notification Center',
     'System-wide notification inbox with read/unread status tracking. Mark individual notifications or all as read. Each notification includes an action link to the relevant activity, approval, or system event.',
     ['🔔 In-App Notification Inbox', '✅ Mark Individual / All Read', '🔗 Action Deep Links']],
    ['15', 'super_admin_email_logs',       'Email Transmission Audit Log',
     'Complete record of every email sent by the portal at each workflow stage. Displays recipient address, subject line, workflow stage trigger, delivery status (sent / queued / failed), timestamps, and a full HTML body preview for troubleshooting and compliance verification.',
     ['📧 Complete Email Audit Trail', '✅ Sent / Queued / Failed Status', '👁️ HTML Body Preview', '🔍 Searchable History']],
    ['16', 'super_admin_profile',          'User Profile — Super Admin',
     'Personal profile management for the Super Admin: update name, email, phone, designation, upload a profile avatar, and change the account password with current-password verification.',
     ['✏️ Edit Personal Information', '🖼️ Profile Avatar Upload', '🔑 Password Change']],
  ];

  for (const [num, key, title, desc, anns] of saPages) {
    children.push(...ssCard(num, '50', 'SUPER ADMIN', 'b45309', title, desc, anns, `${num}_${key}.png`));
  }

  // ══ CHAPTER 3 — VICE CHANCELLOR ══════════════════════════════════════════════
  children.push(...chapterBanner(3,
    '🏛️  Vice Chancellor (VC) — Verification & Oversight',
    'vc@vmrfdu.edu.in  ·  Role: vc  ·  Stage 1 Verification Authority  ·  Global data access', '1e3a8a'));

  const vcPages = [
    ['17', 'vc_dashboard',          'VC Dashboard',
     'Vice Chancellor\'s role-specific homepage: pending verification count, university-wide activity statistics, recent submission feed, approval rate indicators, and quick navigation to the verification queue and analytics dashboard.',
     ['📋 Pending Queue Count', '📊 University-Wide Stats', '📈 Approval Rate Indicator', '⚡ Quick Actions']],
    ['18', 'vc_verification_queue', 'Verification Queue — Pending Submissions',
     'Stage 1 verification dashboard listing all activities submitted by College Admins awaiting institutional review. Each record shows the activity title, submitting college, event category, date, faculty coordinator, and uploaded photos. Three actions available — Approve (status → media_review), Discuss (returns to College Admin with remarks), Reject (terminates with mandatory reason).',
     ['✅ Approve → media_review', '💬 Discuss → Returned to College Admin', '❌ Reject → Terminated (Mandatory Reason)', '📝 Mandatory Remarks on Discuss / Reject']],
    ['19', 'vc_approved_list',      'Approved Submissions List',
     'Historical record of all activities approved by the VC and forwarded to the Media Editor Studio for processing. Shows approval timestamp, remarks provided at time of approval, and the current downstream status of each record.',
     ['✅ Full Approved Records', '📅 Approval Timestamps', '🔄 Downstream Status Tracking']],
    ['20', 'vc_rejected_list',      'Rejected Submissions Archive',
     'Record of all activities rejected at Stage 1 with mandatory rejection reasons and precise timestamps. Provides full governance traceability and allows colleges to understand why specific activities were not approved.',
     ['❌ Rejected Activity Records', '📝 Mandatory Rejection Reasons', '📅 Rejection Timestamps']],
    ['21', 'vc_analytics',          'Analytics Dashboard (VC View)',
     'University-wide analytics with full cross-college data visibility: overall approval rates, submission volumes broken down by category and college, stage-wise distribution, and monthly submission trends. Supports CSV download and printable executive report export.',
     ['📊 University-Wide Analytics', '📈 Category & College Breakdown', '📥 CSV Download', '🖨️ Printable Report']],
    ['22', 'vc_submissions_view',   'All Submissions — Global Overview',
     'Global read-only view of all activity submissions across every college and every workflow status — for monitoring and executive oversight without triggering any workflow transitions. Filterable by college, status, category, and date.',
     ['📋 All Colleges Visible', '🌐 All Workflow Statuses', '👁️ Read-Only Executive Oversight', '🔍 Multi-Filter Support']],
    ['23', 'vc_notifications',      'Notifications (Vice Chancellor)',
     'In-app notification inbox for the VC: new activities submitted and awaiting verification, resubmissions by College Admins after discussion feedback, and system-level alerts.',
     ['🔔 New Submission Alerts', '🔄 Resubmission Notifications', '✅ Mark Read']],
    ['24', 'vc_profile',            'User Profile — Vice Chancellor',
     'Personal profile management for the Vice Chancellor: update name, phone, designation, upload profile avatar, and change account password with current-password confirmation.',
     ['✏️ Edit Personal Information', '🖼️ Profile Avatar Upload', '🔑 Password Change']],
  ];

  for (const [num, key, title, desc, anns] of vcPages) {
    children.push(...ssCard(num, '50', 'VICE CHANCELLOR', '1e40af', title, desc, anns, `${num}_${key}.png`));
  }

  // ══ CHAPTER 3B — PRO VICE CHANCELLOR ═════════════════════════════════════════
  children.push(...chapterBanner(3.5,
    '🏛️  Pro Vice Chancellor (Pro VC) — Verification & Oversight',
    'provc@vmrfdu.edu.in  ·  Role: pro_vc  ·  Stage 1 Verification Authority  ·  Global data access', '3730a3'));

  const provcPages = [
    ['43', 'provc_dashboard',          'Pro VC Dashboard',
     'Pro Vice Chancellor\'s role-specific homepage: pending verification count, university-wide activity statistics, recent submission feed, approval rate indicators, and quick navigation to the verification queue and analytics dashboard.',
     ['📋 Pending Queue Count', '📊 University-Wide Stats', '📈 Approval Rate Indicator', '⚡ Quick Actions']],
    ['43', 'provc_verification_queue', 'Verification Queue — Pending Submissions',
     'Stage 1 verification dashboard listing all activities submitted by College Admins awaiting institutional review. Each record shows the activity title, submitting college, event category, date, faculty coordinator, and uploaded photos. Three actions available — Approve (status → media_review), Discuss (returns to College Admin with remarks), Reject (terminates with mandatory reason).',
     ['✅ Approve → media_review', '💬 Discuss → Returned to College Admin', '❌ Reject → Terminated (Mandatory Reason)', '📝 Mandatory Remarks on Discuss / Reject']],
    ['44', 'provc_approved_list',      'Approved Submissions List',
     'Historical record of all activities approved by the Pro VC and forwarded to the Media Editor Studio for processing. Shows approval timestamp, remarks provided at time of approval, and the current downstream status of each record.',
     ['✅ Full Approved Records', '📅 Approval Timestamps', '🔄 Downstream Status Tracking']],
    ['44', 'provc_rejected_list',      'Rejected Submissions Archive',
     'Record of all activities rejected at Stage 1 with mandatory rejection reasons and precise timestamps. Provides full governance traceability and allows colleges to understand why specific activities were not approved.',
     ['❌ Rejected Activity Records', '📝 Mandatory Rejection Reasons', '📅 Rejection Timestamps']],
    ['45', 'provc_analytics',          'Analytics Dashboard (Pro VC View)',
     'University-wide analytics with full cross-college data visibility: overall approval rates, submission volumes broken down by category and college, stage-wise distribution, and monthly submission trends. Supports CSV download and printable executive report export.',
     ['📊 University-Wide Analytics', '📈 Category & College Breakdown', '📥 CSV Download', '🖨️ Printable Report']],
    ['45', 'provc_submissions_view',   'All Submissions — Global Overview',
     'Global read-only view of all activity submissions across every college and every workflow status — for monitoring and executive oversight without triggering any workflow transitions. Filterable by college, status, category, and date.',
     ['📋 All Colleges Visible', '🌐 All Workflow Statuses', '👁️ Read-Only Executive Oversight', '🔍 Multi-Filter Support']],
    ['46', 'provc_notifications',      'Notifications (Pro Vice Chancellor)',
     'In-app notification inbox for the Pro VC: new activities submitted and awaiting verification, resubmissions by College Admins after discussion feedback, and system-level alerts.',
     ['🔔 New Submission Alerts', '🔄 Resubmission Notifications', '✅ Mark Read']],
    ['46', 'provc_profile',            'User Profile — Pro Vice Chancellor',
     'Personal profile management for the Pro Vice Chancellor: update name, phone, designation, upload profile avatar, and change account password with current-password confirmation.',
     ['✏️ Edit Personal Information', '🖼️ Profile Avatar Upload', '🔑 Password Change']],
  ];

  for (const [num, key, title, desc, anns] of provcPages) {
    children.push(...ssCard(num, '50', 'PRO VICE CHANCELLOR', '3730a3', title, desc, anns, `${num}_${key}.png`));
  }

  // ══ CHAPTER 4 — OFFICE REGISTRAR ═════════════════════════════════════════════
  children.push(...chapterBanner(4,
    '📋  Office of Registrar — Stage 2 Final Authorization',
    'officeregistrar@vmrfdu.edu.in  ·  Role: office_registrar  ·  Executive review before broadcast', '6b21a8'));

  const orPages = [
    ['25', 'office_registrar_dashboard',    'Office of Registrar Dashboard',
     'Role-specific homepage showing locked submissions currently pending Stage 2 executive review (registrar_approval status), the executive approval action queue, and a summary of previously reviewed media packages.',
     ['🔒 Locked Submissions Pending Review', '✅ Executive Approval Queue', '📊 Review Statistics']],
    ['26', 'office_registrar_review_queue', 'Stage 2 Media Review Queue — Locked Packages',
     'Executive review interface for media packages submitted by the Media Editor and now locked (registrar_approval status). Each package displays the optimized photos, AI-generated social media captions, and the full activity details. Two actions: Approve (status → ready_to_publish) or Discuss (🔓 unlocks the record and returns it to the Media Editor\'s queue with feedback remarks).',
     ['🔒 Locked / Read-Only Records', '✅ Approve → ready_to_publish', '💬 Discuss → 🔓 Unlocks & Returns to Editor', '📸 Optimized Media Package Preview']],
    ['27', 'office_registrar_approvals',    'Approvals View (Office of Registrar)',
     'Historical view of all activities that the Office of Registrar has approved or sent for discussion, with the full audit trail of decisions and timestamps for each record.',
     ['✅ Approved History', '💬 Discuss / Return History', '📅 Full Decision Timeline']],
    ['28', 'office_registrar_analytics',    'Analytics Dashboard (Office of Registrar)',
     'Stage 2 processing KPIs from the Office of Registrar perspective: media review throughput, approval and discussion rates, and average turnaround times for executive reviews.',
     ['📊 Stage 2 Review KPIs', '📈 Throughput Charts', '⏱️ Turnaround Time Analysis']],
    ['29', 'office_registrar_notifications','Notifications (Office of Registrar)',
     'Alerts received when the Media Editor submits a new package for Stage 2 review, and confirmations when approval or discussion actions are completed.',
     ['🔒 New Package for Review Alert', '✅ Approval Confirmation', '💬 Discussion Action Alert']],
    ['30', 'office_registrar_profile',      'User Profile — Office of Registrar',
     'Personal profile management: update information, upload avatar, and change account password with current-password verification.',
     ['✏️ Edit Personal Information', '🖼️ Profile Avatar Upload', '🔑 Password Change']],
  ];

  for (const [num, key, title, desc, anns] of orPages) {
    children.push(...ssCard(num, '50', 'OFFICE REGISTRAR', '6b21a8', title, desc, anns, `${num}_${key}.png`));
  }

  // ══ CHAPTER 5 — MEDIA EDITOR ══════════════════════════════════════════════════
  children.push(...chapterBanner(5,
    '🎨  Media Editor Desk — Studio & Broadcast',
    'editor@vmrfdu.edu.in  ·  Role: media_editor  ·  Karthik N. (Lead Media Editor)  ·  Photo · Caption · ZIP · Publish', 'd97706'));

  const mePages = [
    ['31', 'media_editor_dashboard',     'Media Editor Dashboard',
     'The Media Editor\'s command centre — on login, the system lands directly on the Media Studio. Displays four queue counts at a glance: Editing Queue (media_review activities awaiting processing), Locked Submissions (registrar_approval 🔒 — submitted to Office of Registrar), Revision Queue (editor_discuss 🔓 — returned for corrections), and Ready to Publish count.',
     ['📸 Editing Queue Count', '🔒 Locked Submissions Count', '✅ Ready to Publish Count', '🔓 Discuss / Revision Queue Count']],
    ['32', 'media_editor_studio_queue',  'Media Studio — Editing Queue & Locked Submissions',
     'Main media processing workspace. Activities in media_review appear in the editing queue for photo optimization and AI caption refinement. After submitting to the Office of Registrar, records move to the Locked section (🔒 padlock visible — fully read-only). Activities returned via editor_discuss appear for revision. One-click ZIP media pack download available for any activity.',
     ['📸 Photo Optimization', '✏️ AI Caption Refinement', '🔒 Locked Read-Only Records (Padlock 🔒)', '📤 Submit to Office of Registrar', '📦 ZIP Media Pack Download', '🔓 editor_discuss Revision Queue']],
    ['33', 'media_editor_submissions',   'Submission History (Media Editor)',
     'Historical view of all activity submissions processed by this Media Editor, including their current downstream status after Stage 2 executive review and final publication on platforms.',
     ['📋 Processed Activity History', '🎉 Published Activity Records', '🔄 Downstream Status Tracking']],
    ['34', 'media_editor_analytics',     'Analytics Dashboard (Media Editor)',
     'Media processing performance KPIs: editing throughput, average processing time per activity, publishing rates, and distribution of published content across platforms (Instagram, LinkedIn, Twitter/X, Portal Archive).',
     ['📊 Processing Performance KPIs', '📈 Throughput Trend Charts', '📡 Platform Publishing Distribution']],
    ['35', 'media_editor_notifications', 'Notifications (Media Editor)',
     'Alert inbox for the Media Editor: new activities entering media_review queue (from Registrar/VC approval), notifications when the Office of Registrar sends packages back for revision (editor_discuss), and confirmations when ready_to_publish status is granted.',
     ['📸 New Media Review Queue Alert', '🔓 editor_discuss Return Notification', '✅ Ready to Publish Granted Alert']],
    ['36', 'media_editor_profile',       'User Profile — Media Editor (Karthik N.)',
     'Personal profile management for Karthik N. (Lead Media Editor): update name, phone, designation, upload profile avatar, and change account password.',
     ['✏️ Edit Personal Information', '🖼️ Profile Avatar Upload', '🔑 Password Change']],
  ];

  for (const [num, key, title, desc, anns] of mePages) {
    children.push(...ssCard(num, '50', 'MEDIA EDITOR', 'd97706', title, desc, anns, `${num}_${key}.png`));
  }

  // ══ CHAPTER 6 — COLLEGE ADMIN ══════════════════════════════════════════════════
  children.push(...chapterBanner(6,
    '🏫  College Admin / DEO — Activity Submission & Tracking',
    'deo.sahs-pdy@vmrfdu.edu.in  ·  Role: college_admin  ·  DEO, School of Allied Health Sciences (SAHS-PDY)  ·  College-scoped', '166534'));

  const caPages = [
    ['37', 'college_admin_dashboard',      'College Admin Dashboard',
     'Role-specific homepage for the DEO (SAHS-PDY). Displays submission counts by status (draft / pending verification / approved / rejected / published) scoped exclusively to SAHS-PDY college. Recent activity feed filtered to this college. Quick-submit action button for immediate activity creation.',
     ['📊 SAHS-PDY College-Scoped Statistics', '🏫 Own College Data Only', '⚡ Quick Submit New Activity', '🔄 Live Status Summary']],
    ['38', 'college_admin_submissions',    'Submissions List (College Admin)',
     'Complete list of all activities submitted by SAHS-PDY, filterable by status (All / Draft / Pending / Approved / Media Review / Published / Rejected). Each row shows the unique Reference ID (VMRF-2026-XXXX format), activity title, event date, and current status badge. The Edit button is enabled only when status is "discuss" — the activity has been returned for correction.',
     ['📋 Status-Filtered Submission List', '🔑 VMRF-2026-XXXX Reference IDs', '💬 View Registrar Feedback', '✏️ Edit Enabled Only in Discuss State']],
    ['39', 'college_admin_create_activity','Create New Activity Form',
     'The primary submission form for institutional activities. Mandatory fields: activity title, description, event date, event category (Symposium / Workshop / Health Camp / Guest Lecture / Cultural / MoU / Sports / NSS / NCC / Hackathon / etc.), event location, target audience, and faculty coordinator name, email, and phone. Mandatory photo upload (JPG / PNG / WEBP, max 10 MB each). Optional PDF document upload (max 15 MB). Integrated AI Caption Generator modal with 12+ professional institutional templates for instant social media caption creation. Save as Draft at any time, or Submit directly to the Stage 1 verification pipeline.',
     ['📝 Structured Submission Form', '📸 Mandatory Photo Upload (10 MB max)', '🤖 AI Caption Generator — 12+ Templates', '💾 Save as Draft at Any Time', '📄 Optional PDF Document Upload', '🏷️ 12 Event Category Options']],
    ['40', 'college_admin_analytics',      'Analytics Dashboard (College Admin)',
     'College-scoped analytics for SAHS-PDY showing activity data for this college only: submission volume trends, category breakdown charts, approval/rejection statistics, and status distribution — helping college management understand their institutional activity profile.',
     ['🏫 SAHS-PDY-Scoped Data Only', '📊 Category Breakdown Charts', '📈 Monthly Submission Trends', '📥 CSV Export']],
    ['41', 'college_admin_notifications',  'Notifications (College Admin)',
     'In-app notification inbox for the DEO: alerts when submissions progress through workflow stages, when the VC/Registrar sends discussion feedback, when activities are approved and move to media processing, and when activities are published or rejected.',
     ['✅ Stage Approval Notifications', '💬 Discuss / Feedback Alerts', '❌ Rejection Notifications', '🎉 Published Activity Alerts']],
    ['42', 'college_admin_profile',        'User Profile — College Admin (DEO)',
     'Personal profile management for the DEO (SAHS-PDY): update name, phone, designation, upload profile avatar, and change account password with current-password confirmation.',
     ['✏️ Edit Personal Information', '🖼️ Profile Avatar Upload', '🔑 Change Password', '📞 Phone & Designation Update']],
  ];

  for (const [num, key, title, desc, anns] of caPages) {
    children.push(...ssCard(num, '50', 'COLLEGE ADMIN', '166534', title, desc, anns, `${num}_${key}.png`));
  }

  // ══ SUMMARY INDEX ══════════════════════════════════════════════════════════════
  children.push(pageBreak());
  children.push(P([bold('COMPLETE SCREENSHOTS INDEX', 32, '0f2744')],
    { alignment: AlignmentType.CENTER,
      border: { bottom: { color: '2563eb', size: 6, style: BorderStyle.SINGLE } },
      spacing: { after: PT(4) } }));
  children.push(P([normal('50 Pages  ·  7 User Accounts  ·  7 Roles  ·  All Portal Modules', 20, '2563eb')],
    { alignment: AlignmentType.CENTER, spacing: { after: PT(12) } }));

  children.push(buildIndex([
    ['01','Public','Login Page',                    'Email/password auth, CSRF, IP tracking, force-reset'],
    ['02','Public','Public Gallery',                'Search, category, college, date filters, image grid'],
    ['04','Super Admin','Dashboard',                'System-wide KPIs, all users, workflow status'],
    ['05','Super Admin','College Management',       'CRUD colleges, codes, toggle active'],
    ['06','Super Admin','User Management',          'Create/edit users, role assign, lock, transfer'],
    ['07','Super Admin','Role & Permission Builder','JSON permissions, data scope, role hierarchy'],
    ['08','Super Admin','Workflow Engine',          'Stage config, action mapping, routing rules'],
    ['09','Super Admin','Access Control Suite',     'Per-user overrides, copy role permissions'],
    ['10','Super Admin','Activity Override',        'Force status, restore, reassign, hard delete'],
    ['11','Super Admin','System Audit Logs',        'Immutable trail, user/IP/before-after logging'],
    ['12','Super Admin','Master Settings',          'Logo, favicon, NTP, session config, branding'],
    ['13','Super Admin','Analytics Dashboard',      'University-wide KPIs, trend charts, CSV/print export'],
    ['14','Super Admin','Notification Center',      'In-app alerts, read tracking, action links'],
    ['15','Super Admin','Email Audit Log',          'Email trail, delivery status, HTML body preview'],
    ['16','Super Admin','User Profile',             'Edit info, avatar upload, password change'],
    ['17','Vice Chancellor','Dashboard',            'Pending queue, global stats, approval rate'],
    ['18','Vice Chancellor','Verification Queue',   'Approve / Discuss / Reject, mandatory remarks'],
    ['19','Vice Chancellor','Approved List',        'Historical approvals, downstream status tracking'],
    ['20','Vice Chancellor','Rejected Archive',     'Rejection records, mandatory reasons'],
    ['21','Vice Chancellor','Analytics',            'University-wide data, trends, CSV/print export'],
    ['22','Vice Chancellor','All Submissions View', 'Global read-only oversight, multi-filter'],
    ['23','Vice Chancellor','Notifications',        'New submission + resubmission alerts'],
    ['24','Vice Chancellor','User Profile',         'Edit info, avatar upload, password change'],
    ['25','Office Registrar','Dashboard',           'Locked submissions count, Stage 2 queue'],
    ['26','Office Registrar','Stage 2 Review Queue 🔒','Locked packages, approve / discuss actions'],
    ['27','Office Registrar','Approvals View',      'Approved / discuss history, decision trail'],
    ['28','Office Registrar','Analytics',           'Review KPIs, throughput, turnaround analysis'],
    ['29','Office Registrar','Notifications',       'New package alerts, confirmation alerts'],
    ['30','Office Registrar','User Profile',        'Edit info, avatar upload, password change'],
    ['31','Media Editor','Dashboard',               'Queue counts: editing / locked 🔒 / ready / discuss 🔓'],
    ['32','Media Editor','Media Studio Queue',      'Photo edit, caption refine, 🔒 locked records, ZIP pack'],
    ['33','Media Editor','Submission History',      'Processed activities, downstream status'],
    ['34','Media Editor','Analytics',               'Processing KPIs, platform publishing distribution'],
    ['35','Media Editor','Notifications',           'New media review, editor_discuss return alerts'],
    ['36','Media Editor — Karthik N.','User Profile','Edit info, avatar upload, password change'],
    ['37','College Admin','Dashboard',              'VMKVEC-scoped stats, live status summary'],
    ['38','College Admin','Submissions List',       'Status filters, VMRF-IDs, edit on discuss only'],
    ['39','College Admin','Create Activity Form',   'Full form, AI captions (12+), photo upload, draft/submit'],
    ['40','College Admin','Analytics',              'College-scoped KPIs, category charts, CSV export'],
    ['41','College Admin','Notifications',          'Approval / discuss / reject / published alerts'],
    ['42','College Admin — Muthukumar S.','User Profile','Edit info, avatar upload, password change'],
  ]));

  // Final credits
  children.push(P([R('')], { spacing: { after: PT(20) } }));
  children.push(divider('2563eb'));
  children.push(P([
    bold('Under the Guidance of : ', 20, '0f2744'),
    bold('Dr. A. Murugan', 20, '2563eb'),
    normal('     |     ', 20, '94a3b8'),
    bold('Designed & Developed by : ', 20, '0f2744'),
    bold('VMRFDU IT Team  (Dhinakaran & Sanjai)', 20, '16a34a'),
  ], { alignment: AlignmentType.CENTER, spacing: { before: PT(10), after: PT(6) } }));
  children.push(P([italic('© 2026  VMRF(DU) – Vinayaka Mission\'s Research Foundation (Deemed to be University). All Rights Reserved.', 18, '94a3b8')],
    { alignment: AlignmentType.CENTER }));

  // ── Assemble Document ─────────────────────────────────────────────────────
  const doc = new Document({
    title:       'VMRF(DU) Activity Portal – Screenshots Documentation',
    subject:     'All Modules, Pages & User Interfaces',
    creator:     'VMRFDU IT Team (Dhinakaran & Sanjai)',
    description: 'Under the Guidance of Dr. A. Murugan | © 2026 VMRF(DU)',
    keywords:    'VMRFDU, Activity Portal, Screenshots, Laravel, Documentation',
    styles: {
      default: {
        document: { run: { font: 'Calibri', size: 20, color: '1e293b' } },
      },
    },
    sections: [{
      properties: {
        page: {
          margin: {
            top:    convertInchesToTwip(0.85),
            bottom: convertInchesToTwip(0.85),
            left:   convertInchesToTwip(0.9),
            right:  convertInchesToTwip(0.9),
          },
        },
      },
      headers: {
        default: new Header({ children: [
          new Paragraph({
            children: [
              bold('VMRF(DU)  Institutional Activity Portal', 17, '1e3a8a'),
              normal('   —   Screenshots Documentation   —   ', 17, '94a3b8'),
              italic('Under the Guidance of Dr. A. Murugan', 16, '64748b'),
            ],
            border: { bottom: { color: 'e2e8f0', size: 4, style: BorderStyle.SINGLE } },
            spacing: { after: PT(3) },
          }),
        ]}),
      },
      footers: {
        default: new Footer({ children: [
          new Paragraph({
            children: [
              italic('Designed & Developed by: VMRFDU IT Team (Dhinakaran & Sanjai)   |   © 2026 VMRF(DU). All Rights Reserved.   |   Page ', 15, '94a3b8'),
              new TextRun({ children: [PageNumber.CURRENT], size: 16, color: '2563eb', bold: true }),
              italic(' / ', 15, '94a3b8'),
              new TextRun({ children: [PageNumber.TOTAL_PAGES], size: 16, color: '2563eb' }),
            ],
            alignment: AlignmentType.CENTER,
            border: { top: { color: 'e2e8f0', size: 4, style: BorderStyle.SINGLE } },
            spacing: { before: PT(3) },
          }),
        ]}),
      },
      children,
    }],
  });

  const buf = await Packer.toBuffer(doc);
  fs.writeFileSync(OUT, buf);
  const mb = (buf.length / 1024 / 1024).toFixed(2);
  console.log(`\n✅  Word document generated successfully`);
  console.log(`    File : ${OUT}`);
  console.log(`    Size : ${mb} MB`);
}

build().catch(e => { console.error('\n❌ Error:', e.message); process.exit(1); });
