const PptxGenJS = require("pptxgenjs");
const fs = require("path");

async function generatePresentation() {
    console.log("Initializing VMRFDU Activity Portal Presentation Generator...");
    const pptx = new PptxGenJS();

    // Set 16:9 widescreen layout
    pptx.layout = 'LAYOUT_16x9';

    // Color Palette
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
        border: "E2E8F0"
    };

    // Define Master Slide Template
    pptx.defineSlideMaster({
        title: "MASTER_SLIDE",
        background: { color: colors.lightGray },
        objects: [
            // Top Header Banner
            { rect: { x: 0, y: 0, w: "100%", h: 0.9, fill: { color: colors.navy } } },
            // Accent Gold Stripe
            { rect: { x: 0, y: 0.9, w: "100%", h: 0.06, fill: { color: colors.gold } } },
            // Bottom Footer Banner
            { rect: { x: 0, y: 7.0, w: "100%", h: 0.5, fill: { color: colors.navy } } },
            // Footer Text
            { text: { text: "VMRFDU Institutional Activity & Media Portal | Enterprise Technical Presentation", options: { x: 0.5, y: 7.1, w: 8.0, h: 0.3, color: colors.white, fontSize: 11, fontFace: "Arial" } } },
        ]
    });

    // Helper to add standard header title
    function addHeader(slide, titleText, categoryText = "VMRFDU ENTERPRISE PORTAL") {
        slide.addText(categoryText, { x: 0.5, y: 0.1, fontSize: 10, color: colors.gold, bold: true, fontFace: "Arial" });
        slide.addText(titleText, { x: 0.5, y: 0.35, fontSize: 22, color: colors.white, bold: true, fontFace: "Arial" });
    }

    // ==========================================
    // SLIDE 1: Title Slide (Dark Theme)
    // ==========================================
    let slide1 = pptx.addSlide();
    slide1.background = { color: colors.navy };
    slide1.addShape(pptx.ShapeType.rect, { x: 0, y: 4.8, w: "100%", h: 0.1, fill: { color: colors.gold } });
    
    slide1.addText("VINAYAKA MISSION'S RESEARCH FOUNDATION", { x: 1.0, y: 1.5, fontSize: 16, color: colors.gold, bold: true, tracking: 2, fontFace: "Arial" });
    slide1.addText("Institutional Activity & Media Portal", { x: 1.0, y: 2.1, fontSize: 36, color: colors.white, bold: true, fontFace: "Arial" });
    slide1.addText("Enterprise Activity Management, Multi-Stage Governance & AI Media Syndication System", { x: 1.0, y: 3.2, fontSize: 18, color: "94A3B8", fontFace: "Arial", w: 11.0 });

    slide1.addText("Prepared for: VMRFDU Executive Leadership & 28 Constituent Colleges\nArchitecture: Laravel 11 • MySQL 8 • Google Gemini AI • Role-Based Access Control", { x: 1.0, y: 5.3, fontSize: 14, color: colors.white, lineSpacing: 24, fontFace: "Arial" });

    // ==========================================
    // SLIDE 2: Problem Statement
    // ==========================================
    let slide2 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(slide2, "Executive Summary & Problem Statement");
    
    slide2.addText("The Challenges of Decentralized Campus Activity Management", { x: 0.5, y: 1.2, fontSize: 18, color: colors.blue, bold: true });

    // Box 1
    slide2.addShape(pptx.ShapeType.rect, { x: 0.5, y: 1.8, w: 5.8, h: 4.8, fill: { color: colors.white }, line: { color: colors.border, width: 1 } });
    slide2.addText("Legacy Operational Bottlenecks", { x: 0.8, y: 2.0, fontSize: 16, color: colors.red, bold: true });
    slide2.addText([
        { text: "• Decentralized Silos: ", options: { bold: true } },
        { text: "28 constituent colleges operated independent reporting structures, causing massive data fragmentation.\n\n" },
        { text: "• Manual Approval Delays: ", options: { bold: true } },
        { text: "Paper-based and email-heavy verification workflows created multi-week bottlenecks before activities could be published.\n\n" },
        { text: "• Lack of Central Visibility: ", options: { bold: true } },
        { text: "University leadership lacked real-time dashboard oversight of campus academic, research, and sports achievements." }
    ], { x: 0.8, y: 2.5, w: 5.2, fontSize: 13, color: colors.darkText, lineSpacing: 20 });

    // Box 2
    slide2.addShape(pptx.ShapeType.rect, { x: 6.7, y: 1.8, w: 6.0, h: 4.8, fill: { color: colors.white }, line: { color: colors.border, width: 1 } });
    slide2.addText("Media & Accreditation Risks", { x: 7.0, y: 2.0, fontSize: 16, color: colors.red, bold: true });
    slide2.addText([
        { text: "• Inconsistent Branding: ", options: { bold: true } },
        { text: "Photos submitted lacked standard aspect ratios, resolution quality, and official institutional logos/watermarks.\n\n" },
        { text: "• Manual Social Syndication: ", options: { bold: true } },
        { text: "Public relations teams manually formatted and posted events to Facebook, LinkedIn, X, and Instagram without automation.\n\n" },
        { text: "• tedious NAAC / NIRF Filing: ", options: { bold: true } },
        { text: "Compiling annual accreditation data required weeks of manual data collation across departments." }
    ], { x: 7.0, y: 2.5, w: 5.4, fontSize: 13, color: colors.darkText, lineSpacing: 20 });

    // ==========================================
    // SLIDE 3: The Solution
    // ==========================================
    let slide3 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(slide3, "The Solution: A Unified Institutional Portal");
    
    slide3.addText("Centralized Governance with Strict Institutional Data Privacy", { x: 0.5, y: 1.2, fontSize: 18, color: colors.blue, bold: true });

    const solBoxes = [
        { title: "1. Unified Clearinghouse", desc: "A single digital portal for all 28 constituent colleges to submit academic, research, sports, and cultural activities.", x: 0.5 },
        { title: "2. 3-Tier Governance", desc: "An immutable workflow routing submissions through Academic Verification, Media Desk Review, and Registrar Authorization.", x: 4.7 },
        { title: "3. AI & Media Studio", desc: "Built-in photo cropping, automated logo watermarking, and Google Gemini AI copywriting for instant social captions.", x: 8.9 }
    ];

    solBoxes.forEach(box => {
        slide3.addShape(pptx.ShapeType.rect, { x: box.x, y: 2.0, w: 3.8, h: 4.5, fill: { color: colors.white }, line: { color: colors.blue, width: 2 } });
        slide3.addShape(pptx.ShapeType.rect, { x: box.x, y: 2.0, w: 3.8, h: 0.8, fill: { color: colors.blue } });
        slide3.addText(box.title, { x: box.x + 0.2, y: 2.2, w: 3.4, fontSize: 16, color: colors.white, bold: true, align: "center" });
        slide3.addText(box.desc, { x: box.x + 0.3, y: 3.1, w: 3.2, fontSize: 14, color: colors.darkText, lineSpacing: 22, align: "center" });
    });

    // ==========================================
    // SLIDE 4: Key System Features
    // ==========================================
    let slide4 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(slide4, "Key System Features & Capabilities");

    const features = [
        { title: "Dynamic RBAC & Access Matrix", desc: "Database-driven permission management allowing Super Admin to configure roles, menus, and CRUD access without code changes.", x: 0.5, y: 1.6 },
        { title: "Mandatory Image Evidence", desc: "Strict backend validation requiring high-resolution photo evidence before submissions can be forwarded for executive approval.", x: 6.7, y: 1.6 },
        { title: "Interactive Resubmission Loop", desc: "When activities are returned with 'Discuss' status, creators can dynamically edit, replace, or remove media attachments.", x: 0.5, y: 4.2 },
        { title: "Real-Time Tracking & Analytics", desc: "Role-scoped dashboard KPI cards, campus comparison charts, and one-click NAAC/NIRF accreditation report exports.", x: 6.7, y: 4.2 }
    ];

    features.forEach(f => {
        slide4.addShape(pptx.ShapeType.rect, { x: f.x, y: f.y, w: 6.0, h: 2.3, fill: { color: colors.white }, line: { color: colors.border, width: 1 } });
        slide4.addShape(pptx.ShapeType.rect, { x: f.x, y: f.y, w: 0.2, h: 2.3, fill: { color: colors.gold } });
        slide4.addText(f.title, { x: f.x + 0.5, y: f.y + 0.2, fontSize: 16, color: colors.navy, bold: true });
        slide4.addText(f.desc, { x: f.x + 0.5, y: f.y + 0.8, w: 5.2, fontSize: 13, color: colors.darkText, lineSpacing: 18 });
    });

    // ==========================================
    // SLIDE 5: Multi-Stage Governance Workflow
    // ==========================================
    let slide5 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(slide5, "Multi-Stage Governance Workflow", "APPROVAL HIERARCHY");
    
    slide5.addText("The 3-Tier Immutable Approval Engine", { x: 0.5, y: 1.2, fontSize: 18, color: colors.blue, bold: true });

    const stages = [
        { step: "SUBMISSION", role: "Creator / DEO", desc: "Logs activity details, participant counts, and uploads raw photo evidence.", color: colors.navy, x: 0.5 },
        { step: "STAGE 1: VERIFY", role: "Registrar / VC / Admin", desc: "Verifies academic accuracy and institutional policy compliance.", color: colors.blue, x: 3.6 },
        { step: "STAGE 2: MEDIA", role: "Media Desk Editor", desc: "Crops photos, stamps watermark, and generates AI captions & tags.", color: colors.gold, x: 6.7 },
        { step: "STAGE 3: APPROVE", role: "Office of Registrar (PA)", desc: "Final executive review. Authorizes public release & syndication.", color: colors.green, x: 9.8 }
    ];

    stages.forEach((s, idx) => {
        slide5.addShape(pptx.ShapeType.rect, { x: s.x, y: 2.2, w: 2.9, h: 4.0, fill: { color: colors.white }, line: { color: s.color, width: 2 } });
        slide5.addShape(pptx.ShapeType.rect, { x: s.x, y: 2.2, w: 2.9, h: 0.7, fill: { color: s.color } });
        slide5.addText(s.step, { x: s.x, y: 2.4, w: 2.9, fontSize: 14, color: colors.white, bold: true, align: "center" });
        slide5.addText(`Role: ${s.role}`, { x: s.x + 0.1, y: 3.1, w: 2.7, fontSize: 12, color: colors.navy, bold: true, align: "center" });
        slide5.addText(s.desc, { x: s.x + 0.2, y: 3.8, w: 2.5, fontSize: 12, color: colors.darkText, lineSpacing: 18, align: "center" });

        if (idx < 3) {
            slide5.addText("➔", { x: s.x + 2.9, y: 3.8, w: 0.7, fontSize: 24, color: colors.mutedText, align: "center" });
        }
    });

    // ==========================================
    // SLIDE 6: Institutional Data Privacy
    // ==========================================
    let slide6 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(slide6, "Institutional Data Privacy & Access Scoping", "MULTI-TENANCY ARCHITECTURE");

    slide6.addShape(pptx.ShapeType.rect, { x: 0.5, y: 1.5, w: 5.8, h: 5.0, fill: { color: colors.white }, line: { color: colors.border, width: 1 } });
    slide6.addText("Constituent College Isolation", { x: 0.8, y: 1.8, fontSize: 18, color: colors.navy, bold: true });
    slide6.addText([
        { text: "• 28 Isolated Campus Tenancies:\n", options: { bold: true, color: colors.blue } },
        { text: "Each college (e.g., VMKVMC, AVMC, VDC) operates in a strictly isolated data boundary.\n\n" },
        { text: "• Automatic Query Scoping:\n", options: { bold: true, color: colors.blue } },
        { text: "Backend Eloquent queries automatically inject ", options: {} },
        { text: "where('college_id', Auth::user()->college_id)", options: { bold: true, fontFace: "Courier New" } },
        { text: " for all campus-level users.\n\n" },
        { text: "• Zero Cross-Campus Data Leakage:\n", options: { bold: true, color: colors.blue } },
        { text: "College Administrators, DEOs, and Viewers cannot view, edit, or track submissions from other colleges." }
    ], { x: 0.8, y: 2.4, w: 5.2, fontSize: 13, color: colors.darkText, lineSpacing: 20 });

    slide6.addShape(pptx.ShapeType.rect, { x: 6.7, y: 1.5, w: 6.0, h: 5.0, fill: { color: colors.navy } });
    slide6.addText("Super Admin & Global Authority", { x: 7.0, y: 1.8, fontSize: 18, color: colors.gold, bold: true });
    slide6.addText([
        { text: "• Super Admin Global Oversight:\n", options: { bold: true, color: colors.white } },
        { text: "Only the Super Admin role has unrestricted visibility across all 28 constituent campuses and dashboards.\n\n", options: { color: "E2E8F0" } },
        { text: "• Workflow-Scoped Global Queues:\n", options: { bold: true, color: colors.white } },
        { text: "University Registrar, VC, Pro-VC, Media Desk, and Office of Registrar receive global access strictly scoped to their respective approval queues.\n\n", options: { color: "E2E8F0" } },
        { text: "• Dynamic Access Audit:\n", options: { bold: true, color: colors.white } },
        { text: "Every query and state change is immutably logged with actor IP and institutional ID.", options: { color: "E2E8F0" } }
    ], { x: 7.0, y: 2.4, w: 5.4, fontSize: 13, color: colors.white, lineSpacing: 20 });

    // ==========================================
    // SLIDE 7: Dynamic RBAC Module
    // ==========================================
    let slide7 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(slide7, "Dynamic Access Control & RBAC Module");

    slide7.addText("Database-Driven Governance Without Hardcoded Rules", { x: 0.5, y: 1.2, fontSize: 18, color: colors.blue, bold: true });

    // Table showing RBAC matrix
    const tableData = [
        [
            { text: "Role Identifier", options: { fill: colors.navy, color: colors.white, bold: true } },
            { text: "Role Name", options: { fill: colors.navy, color: colors.white, bold: true } },
            { text: "Primary Responsibilities", options: { fill: colors.navy, color: colors.white, bold: true } },
            { text: "Data Scope", options: { fill: colors.navy, color: colors.white, bold: true } }
        ],
        ["super_admin", "Super Admin", "Total system governance, RBAC matrix configuration, college setup.", "Global (All 28 Colleges)"],
        ["registrar", "University Registrar", "Stage 1 verification of institutional activities, academic policy oversight.", "Global Workflow"],
        ["office_registrar", "Office of Registrar (PA)", "Stage 3 final executive sign-off, scheduling public releases.", "Global Final Queue"],
        ["media_editor", "Media Desk Editor", "Stage 2 photo cropping, AI caption generation, social formatting.", "Global Media Queue"],
        ["college_admin", "College Admin", "Campus-level administration, overseeing all submissions from assigned college.", "Assigned College Only"],
        ["creator / deo", "Creator / DEO", "Drafting activity reports, uploading photo evidence, replying to queries.", "Assigned College Only"]
    ];

    slide7.addTable(tableData, { x: 0.5, y: 1.8, w: 12.2, colW: [2.0, 2.5, 5.2, 2.5], rowH: 0.6, fontSize: 12, color: colors.darkText, border: { pt: 1, color: colors.border } });

    // ==========================================
    // SLIDE 8: Media Studio Desk & AI Assistant
    // ==========================================
    let slide8 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(slide8, "Media Studio Desk & AI Content Assistant", "BRAND STANDARDISATION");

    slide8.addShape(pptx.ShapeType.rect, { x: 0.5, y: 1.5, w: 5.8, h: 5.0, fill: { color: colors.white }, line: { color: colors.border, width: 1 } });
    slide8.addText("Interactive Media Studio Desk", { x: 0.8, y: 1.8, fontSize: 18, color: colors.navy, bold: true });
    slide8.addText([
        { text: "• Visual Aspect-Ratio Cropping:\n", options: { bold: true, color: colors.gold } },
        { text: "Built-in cropping tools standardizing photos for social media: 1:1 (Square), 16:9 (Landscape), 4:3 (Standard), and 9:16 (Story/Reel).\n\n" },
        { text: "• Automated Institutional Branding:\n", options: { bold: true, color: colors.gold } },
        { text: "Automatically stamps official VMRFDU logos and NAAC/NIRF accreditation badges onto approved media.\n\n" },
        { text: "• Mandatory Image Validation:\n", options: { bold: true, color: colors.gold } },
        { text: "System enforces mandatory image uploads before submissions can be forwarded to the Registrar." }
    ], { x: 0.8, y: 2.4, w: 5.2, fontSize: 13, color: colors.darkText, lineSpacing: 20 });

    slide8.addShape(pptx.ShapeType.rect, { x: 6.7, y: 1.5, w: 6.0, h: 5.0, fill: { color: colors.white }, line: { color: colors.border, width: 1 } });
    slide8.addText("Google Gemini AI Copywriter", { x: 7.0, y: 1.8, fontSize: 18, color: colors.blue, bold: true });
    slide8.addText([
        { text: "• Automated Press Releases:\n", options: { bold: true, color: colors.blue } },
        { text: "Transforms raw event summaries into polished, professional institutional press releases in seconds.\n\n" },
        { text: "• Social Media Captions:\n", options: { bold: true, color: colors.blue } },
        { text: "Generates tailored captions optimized for Facebook, LinkedIn, X (Twitter), and Instagram audiences.\n\n" },
        { text: "• Smart Hashtag Extraction:\n", options: { bold: true, color: colors.blue } },
        { text: "Automatically extracts trending academic, research, and campus hashtags to maximize reach." }
    ], { x: 7.0, y: 2.4, w: 5.4, fontSize: 13, color: colors.darkText, lineSpacing: 20 });

    // ==========================================
    // SLIDE 9: System Architecture
    // ==========================================
    let slide9 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(slide9, "System Architecture & Data Flow", "TECHNICAL ENGINEERING");

    slide9.addText("Modern MVC Architecture with Layered Security & Service Decoupling", { x: 0.5, y: 1.2, fontSize: 18, color: colors.blue, bold: true });

    const archLayers = [
        { title: "Client Layer", items: "Web Browser / Mobile UI\nBootstrap 5 • Vanilla CSS\nES6+ AJAX • localStorage", x: 0.5, color: colors.navy },
        { title: "Routing & Auth", items: "Laravel 11 HTTP Routing\nCSRF Protection Middleware\nStrict RBAC & Role Check", x: 3.6, color: colors.blue },
        { title: "Service Layer", items: "ActivityService • AIService\nMedia Processing (GD/Image)\nQuery Scoping Engine", x: 6.7, color: colors.gold },
        { title: "Data & External", items: "MySQL 8.0 Enterprise DB\nLocal/Cloud Media Storage\nGoogle Gemini AI & Graph API", x: 9.8, color: colors.green }
    ];

    archLayers.forEach((l, idx) => {
        slide9.addShape(pptx.ShapeType.rect, { x: l.x, y: 2.2, w: 2.9, h: 4.0, fill: { color: colors.white }, line: { color: l.color, width: 2 } });
        slide9.addShape(pptx.ShapeType.rect, { x: l.x, y: 2.2, w: 2.9, h: 0.8, fill: { color: l.color } });
        slide9.addText(l.title, { x: l.x, y: 2.4, w: 2.9, fontSize: 16, color: colors.white, bold: true, align: "center" });
        slide9.addText(l.items, { x: l.x + 0.2, y: 3.4, w: 2.5, fontSize: 13, color: colors.darkText, lineSpacing: 24, align: "center" });

        if (idx < 3) {
            slide9.addText("⇄", { x: l.x + 2.9, y: 3.8, w: 0.7, fontSize: 24, color: colors.mutedText, align: "center" });
        }
    });

    // ==========================================
    // SLIDE 10: Database Schema
    // ==========================================
    let slide10 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(slide10, "Database Schema & E-R Overview", "RELATIONAL DESIGN");

    const dbTables = [
        { name: "users", cols: "id (PK)\nname, email, password\nrole (VARCHAR)\ncollege_id (FK -> colleges)\ncustom_permissions (JSON)\nis_active, timestamps", x: 0.5, y: 1.6 },
        { name: "colleges", cols: "id (PK)\nname, code (UNIQUE)\ncampus_location\nlogo_path\nis_active\ntimestamps", x: 4.7, y: 1.6 },
        { name: "activity_submissions", cols: "id (PK)\ntitle, event_category\nactivity_date\ncollege_id (FK), user_id (FK)\ndescription, status (INDEX)\nai_caption, ai_hashtags", x: 8.9, y: 1.6 },
        { name: "media_attachments", cols: "id (PK)\nactivity_submission_id (FK)\nfile_path, file_type\nfile_size, dimensions\nis_featured (BOOLEAN)\ntimestamps", x: 0.5, y: 4.2 },
        { name: "roles & permissions", cols: "roles: id, name, slug\npermissions: id, name, module\npermission_role (Pivot Table)\ndynamic RBAC linkage\ntimestamps", x: 4.7, y: 4.2 },
        { name: "activity_logs", cols: "id (PK)\nuser_id (FK), action\ntarget_type, target_id\nip_address, user_agent\ndetails (JSON), timestamps", x: 8.9, y: 4.2 }
    ];

    dbTables.forEach(t => {
        slide10.addShape(pptx.ShapeType.rect, { x: t.x, y: t.y, w: 3.8, h: 2.3, fill: { color: colors.white }, line: { color: colors.border, width: 1 } });
        slide10.addShape(pptx.ShapeType.rect, { x: t.x, y: t.y, w: 3.8, h: 0.5, fill: { color: colors.navy } });
        slide10.addText(t.name, { x: t.x + 0.1, y: t.y + 0.1, w: 3.6, fontSize: 13, color: colors.gold, bold: true, fontFace: "Courier New" });
        slide10.addText(t.cols, { x: t.x + 0.2, y: t.y + 0.6, w: 3.4, fontSize: 11, color: colors.darkText, lineSpacing: 14, fontFace: "Courier New" });
    });

    // ==========================================
    // SLIDE 11: Dashboards & Analytics
    // ==========================================
    let slide11 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(slide11, "Executive Dashboards & Analytics", "BUSINESS INTELLIGENCE");

    slide11.addText("Real-Time Institutional Intelligence & Accreditation Reporting", { x: 0.5, y: 1.2, fontSize: 18, color: colors.blue, bold: true });

    // KPI Cards simulation
    const kpis = [
        { label: "TOTAL SUBMISSIONS", val: "1,248+", color: colors.navy, x: 0.5 },
        { label: "PENDING APPROVALS", val: "18", color: colors.gold, x: 3.6 },
        { label: "MEDIA DESK READY", val: "42", color: colors.blue, x: 6.7 },
        { label: "PUBLISHED POSTS", val: "1,180", color: colors.green, x: 9.8 }
    ];

    kpis.forEach(k => {
        slide11.addShape(pptx.ShapeType.rect, { x: k.x, y: 1.8, w: 2.9, h: 1.5, fill: { color: colors.white }, line: { color: k.color, width: 2 } });
        slide11.addText(k.label, { x: k.x, y: 2.0, w: 2.9, fontSize: 11, color: colors.mutedText, bold: true, align: "center" });
        slide11.addText(k.val, { x: k.x, y: 2.4, w: 2.9, fontSize: 26, color: k.color, bold: true, align: "center" });
    });

    slide11.addShape(pptx.ShapeType.rect, { x: 0.5, y: 3.6, w: 12.2, h: 3.0, fill: { color: colors.white }, line: { color: colors.border, width: 1 } });
    slide11.addText("Comprehensive Analytics & Export Capabilities", { x: 0.8, y: 3.9, fontSize: 16, color: colors.navy, bold: true });
    slide11.addText([
        { text: "• Campus Comparison Charts: ", options: { bold: true } },
        { text: "Visual bar charts and pie graphs comparing activity frequency across all 28 constituent colleges.\n\n" },
        { text: "• Status Breakdown Tracking: ", options: { bold: true } },
        { text: "Real-time monitoring of items in Stage 1 Verification, Stage 2 Media Review, and Stage 3 Registrar Authorization.\n\n" },
        { text: "• One-Click NAAC / NIRF Export: ", options: { bold: true } },
        { text: "Export structured activity logs, participant statistics, and verified photo links in Excel and PDF formats formatted specifically for annual university accreditation filings." }
    ], { x: 0.8, y: 4.4, w: 11.6, fontSize: 13, color: colors.darkText, lineSpacing: 20 });

    // ==========================================
    // SLIDE 12: UI/UX Design Excellence
    // ==========================================
    let slide12 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(slide12, "UI/UX & Visual Design Excellence", "ENTERPRISE AESTHETICS");

    const uxBoxes = [
        { title: "Clean Enterprise Aesthetics", desc: "Adopts a clean, minimal, and professional design system with curated harmonious color palettes (Deep Navy, Royal Blue, Accent Gold) suitable for a premier university.", x: 0.5, y: 1.6 },
        { title: "Standardized Typography & Spacing", desc: "Utilizes modern typography (Inter/Roboto) with proper weights and line spacing. Dashboard cards feature balanced padding and subtle shadows without bulkiness.", x: 6.7, y: 1.6 },
        { title: "Collapsible Sidebar Navigation", desc: "Features a responsive sidebar with toggle collapse/expand functionality. The user's preferred layout state is saved in localStorage across browser sessions.", x: 0.5, y: 4.2 },
        { title: "Responsive Glassmorphism", desc: "Incorporates subtle glassmorphism effects, smooth transitions, and interactive micro-animations that make the portal feel responsive and alive.", x: 6.7, y: 4.2 }
    ];

    uxBoxes.forEach(u => {
        slide12.addShape(pptx.ShapeType.rect, { x: u.x, y: u.y, w: 6.0, h: 2.3, fill: { color: colors.white }, line: { color: colors.border, width: 1 } });
        slide12.addShape(pptx.ShapeType.rect, { x: u.x, y: u.y, w: 6.0, h: 0.4, fill: { color: colors.navy } });
        slide12.addText(u.title, { x: u.x + 0.2, y: u.y + 0.08, fontSize: 14, color: colors.gold, bold: true });
        slide12.addText(u.desc, { x: u.x + 0.3, y: u.y + 0.6, w: 5.4, fontSize: 13, color: colors.darkText, lineSpacing: 18 });
    });

    // ==========================================
    // SLIDE 13: Technology Stack
    // ==========================================
    let slide13 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(slide13, "Technology Stack & Engineering Standards");

    const techGrid = [
        { layer: "Backend Framework", tech: "Laravel 11 (PHP 8.2+)", desc: "Enterprise MVC architecture, Eloquent ORM, robust routing, and middleware pipelines.", x: 0.5, y: 1.6 },
        { layer: "Database Engine", tech: "MySQL 8.0 Enterprise", desc: "Relational database with JSON column support, compound indexing, and ACID compliance.", x: 6.7, y: 1.6 },
        { layer: "Frontend Styling", tech: "Vanilla CSS & Bootstrap 5", desc: "Responsive layout grid, clean utility classes, and zero-bloat styling.", x: 0.5, y: 4.2 },
        { layer: "AI & Media Services", tech: "Google Gemini AI & GD Lib", desc: "AI copywriting REST API and server-side image cropping/watermarking.", x: 6.7, y: 4.2 }
    ];

    techGrid.forEach(tg => {
        slide13.addShape(pptx.ShapeType.rect, { x: tg.x, y: tg.y, w: 6.0, h: 2.3, fill: { color: colors.white }, line: { color: colors.border, width: 1 } });
        slide13.addText(tg.layer, { x: tg.x + 0.4, y: tg.y + 0.2, fontSize: 12, color: colors.gold, bold: true, textTransform: "uppercase" });
        slide13.addText(tg.tech, { x: tg.x + 0.4, y: tg.y + 0.6, fontSize: 18, color: colors.navy, bold: true });
        slide13.addText(tg.desc, { x: tg.x + 0.4, y: tg.y + 1.2, w: 5.2, fontSize: 13, color: colors.darkText, lineSpacing: 18 });
    });

    // ==========================================
    // SLIDE 14: Security & Compliance
    // ==========================================
    let slide14 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(slide14, "Security & Compliance Framework", "SYSTEM INTEGRITY");

    const secItems = [
        { title: "CSRF & XSS Protection", desc: "All state-changing requests are protected by Laravel CSRF tokens. Blade templates automatically HTML-encode outputs to prevent Cross-Site Scripting.", x: 0.5, y: 1.6 },
        { title: "SQL Injection Prevention", desc: "All database queries utilize PDO parameter binding and Eloquent ORM, eliminating SQL injection vulnerabilities across all modules.", x: 6.7, y: 1.6 },
        { title: "Strict RBAC Middleware", desc: "Route groups are safeguarded by custom role and permission middleware that intercepts unauthorized requests and returns HTTP 403 Forbidden.", x: 0.5, y: 4.2 },
        { title: "Immutable Audit Logging", desc: "Every login attempt, submission creation, status change, and permission modification is immutably recorded with actor ID, IP address, and timestamp.", x: 6.7, y: 4.2 }
    ];

    secItems.forEach(si => {
        slide14.addShape(pptx.ShapeType.rect, { x: si.x, y: si.y, w: 6.0, h: 2.3, fill: { color: colors.white }, line: { color: colors.border, width: 1 } });
        slide14.addShape(pptx.ShapeType.rect, { x: si.x, y: si.y, w: 0.15, h: 2.3, fill: { color: colors.green } });
        slide14.addText(si.title, { x: si.x + 0.4, y: si.y + 0.2, fontSize: 16, color: colors.navy, bold: true });
        slide14.addText(si.desc, { x: si.x + 0.4, y: si.y + 0.8, w: 5.3, fontSize: 13, color: colors.darkText, lineSpacing: 18 });
    });

    // ==========================================
    // SLIDE 15: Business Benefits & ROI
    // ==========================================
    let slide15 = pptx.addSlide({ masterName: "MASTER_SLIDE" });
    addHeader(slide15, "Business Benefits & ROI", "VALUE PROPOSITION");

    const benefits = [
        { stat: "80%", label: "FASTER TURNAROUND", desc: "Reduces activity approval time from weeks to hours through automated routing.", color: colors.blue, x: 0.5 },
        { stat: "100%", label: "BRAND CONSISTENCY", desc: "Ensures every published image has proper aspect ratios and official VMRFDU watermarks.", color: colors.gold, x: 4.7 },
        { stat: "ZERO", label: "DATA LEAKAGE", desc: "Strict college scoping guarantees complete data privacy across all 28 campuses.", color: colors.green, x: 8.9 }
    ];

    benefits.forEach(b => {
        slide15.addShape(pptx.ShapeType.rect, { x: b.x, y: 1.8, w: 3.8, h: 4.5, fill: { color: colors.white }, line: { color: b.color, width: 2 } });
        slide15.addText(b.stat, { x: b.x, y: 2.2, w: 3.8, fontSize: 44, color: b.color, bold: true, align: "center" });
        slide15.addText(b.label, { x: b.x, y: 3.3, w: 3.8, fontSize: 14, color: colors.navy, bold: true, align: "center" });
        slide15.addText(b.desc, { x: b.x + 0.3, y: 4.0, w: 3.2, fontSize: 13, color: colors.darkText, lineSpacing: 20, align: "center" });
    });

    // ==========================================
    // SLIDE 16: Roadmap & Conclusion
    // ==========================================
    let slide16 = pptx.addSlide();
    slide16.background = { color: colors.navy };
    slide16.addShape(pptx.ShapeType.rect, { x: 0, y: 0, w: "100%", h: 0.1, fill: { color: colors.gold } });
    
    slide16.addText("FUTURE ROADMAP & CONCLUSION", { x: 1.0, y: 1.2, fontSize: 14, color: colors.gold, bold: true, tracking: 2 });
    slide16.addText("Transforming University Operations for the Digital Era", { x: 1.0, y: 1.6, fontSize: 32, color: colors.white, bold: true });

    slide16.addText([
        { text: "• Upcoming Mobile Application (iOS & Android):\n", options: { bold: true, color: colors.gold } },
        { text: "Enabling campus faculty and DEOs to capture and upload event photos directly from their smartphones.\n\n", options: { color: colors.white } },
        { text: "• Automated AI Video Reel Rendering:\n", options: { bold: true, color: colors.gold } },
        { text: "Expanding the Media Studio to automatically compile event photos into short video reels for Instagram and YouTube.\n\n", options: { color: colors.white } },
        { text: "• Direct NAAC / NIRF API Synchronization:\n", options: { bold: true, color: colors.gold } },
        { text: "Connecting portal archives directly with national accreditation government portals for automated annual submission.", options: { color: colors.white } }
    ], { x: 1.0, y: 2.5, w: 11.0, fontSize: 15, lineSpacing: 22 });

    slide16.addText("Thank You! Questions & Live Demonstration", { x: 1.0, y: 5.6, fontSize: 24, color: colors.gold, bold: true });

    // Save Presentation
    const outputPath = "d:/xampp/htdocs/vmrfdu_activity_portal/public/VMRFDU_Activity_Portal_Presentation.pptx";
    const artifactPath = "C:/Users/ICT/.gemini/antigravity/brain/31c67c11-cd51-4657-a0d4-cf5eddd64bb9/VMRFDU_Activity_Portal_Presentation.pptx";
    
    await pptx.writeFile({ fileName: outputPath });
    console.log(`Presentation successfully saved to: ${outputPath}`);
    
    try {
        await pptx.writeFile({ fileName: artifactPath });
        console.log(`Presentation successfully copied to artifact directory: ${artifactPath}`);
    } catch (e) {
        console.log("Note: Could not copy to artifact directory directly, but public file is ready.");
    }
}

generatePresentation().catch(err => {
    console.error("Error generating presentation:", err);
});
