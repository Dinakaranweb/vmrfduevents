# VMRF(DU) Institutional Activity Portal
## Technical Architecture, Workflow Flowcharts & System Documentation

> **Vinayaka Mission's Research Foundation (Deemed to be University)**  
> *Centralized Institutional Activity Management, Verification, Media Studio Enhancement, and Public Broadcast System.*

---

## 1. Executive Summary

The **VMRF(DU) Institutional Activity Portal** is an enterprise-grade web application designed to digitize, standardize, verify, and broadcast academic, research, medical outreach, and cultural events across all constituent colleges and departments of Vinayaka Mission's Research Foundation (Deemed to be University).

The platform introduces a **strict, multi-tiered institutional review lifecycle** that ensures every event record undergoes administrative verification by the Registrar, visual and PR enhancement by the Media Editor Desk, and final executive authorization by the Office of the Registrar before being published to the university portal archive or broadcast to official social media channels (Instagram, LinkedIn, Twitter/X).

---

## 2. Interface Showcase & UI Screenshots

### 2.1 Media Studio Desk & Status Locking Queue
The Media Editor Studio Desk (`/media-editor`) organizes verified institutional packages into structured processing queues. Once a package is submitted to the Office of the Registrar (`officeregistrar@vmrfdu.edu.in`), it automatically transitions to a **Locked Queue** with explicit padlock indicators preventing unauthorized edits.

![VMRF(DU) Media Studio Desk with Stage 1 Processing Queue and Locked Submissions Section](C:\Users\ICT\.gemini\antigravity\brain\31c67c11-cd51-4657-a0d4-cf5eddd64bb9\media_studio_desk_mockup_1782974195755.jpg)

---

### 2.2 Universal AI Phrasing & Institutional Template Library (>10 Templates)
Available to all users across activity creation, modification, and media editing forms, the interactive modal provides instant access to **12+ pre-drafted institutional broadcast templates** alongside real-time AI social media caption synthesis.

![Universal AI Phrasing & Institutional Template Library Modal with Category Filter Tabs](C:\Users\ICT\.gemini\antigravity\brain\31c67c11-cd51-4657-a0d4-cf5eddd64bb9\ai_template_library_mockup_1782974214698.jpg)

---

### 2.3 Activity Submission Form & Mandatory Gallery Enforcement
The submission form guarantees data integrity by strictly enforcing the upload of at least one high-resolution gallery image (`images[]`) along with optional PDF event brochures and video links.

![Activity Submission Form illustrating Mandatory Image Upload Enforcement and AI Phrasing Integration](C:\Users\ICT\.gemini\antigravity\brain\31c67c11-cd51-4657-a0d4-cf5eddd64bb9\activity_submission_form_mockup_1782974254424.jpg)

---

## 3. Role-Based Access Control (RBAC) & Scope Matrix

The portal strictly enforces strict data isolation and role-based permissions across five specialized institutional roles:

| Role Name | Identifier / System Role | Primary Responsibilities & Scope | Access Permissions & Limitations |
| :--- | :--- | :--- | :--- |
| **Registrar Authority** | `registrar` | Initial institutional verification of submitted records, global system analytics, college & department setup, user administration. | Full administrative access. Performs Stage 1 institutional verification (`Approve to Media Studio`, `Discuss`, `Reject`). |
| **Office of the Registrar** | `office_registrar` (`officeregistrar@vmrfdu.edu.in`) | Final executive review and authorization of media packages prepared by the Media Editor Desk. | Evaluates optimized photos and refined PR captions. Approves for broadcast (`ready_to_publish`) or returns for correction (`editor_discuss`). Cannot perform initial Stage 1 verification. |
| **Media Editor Desk** | `media_editor` | Visual optimization, photo enhancement, AI broadcast caption refinement, downloading ZIP media packs. | Accesses Stage 1 editing queue (`media_review`, `editor_discuss`) and Stage 2 broadcast queue (`ready_to_publish`). Cannot edit records locked under Registrar review. |
| **College Administrator** | `college_admin` | Submits, manages, and tracks activities for their assigned constituent college. | **Strictly college-scoped**. Can only view, create, and edit records belonging to their assigned college. College field is locked upon creation. |
| **Department User** | `department_user` | Submits and tracks activities for a specific academic department within a constituent college. | **Strictly college & department-scoped**. Can only interact with records mapped to their specific department. |

---

## 4. End-to-End System Workflow Flowchart

The diagram below illustrates the complete life cycle of an institutional activity submission—from creation by college administrators to final publication on public channels.

```mermaid
graph TD
    %% Roles / Actors
    CA(["College Admin / Dept User"])
    REG(["Registrar Authority (registrar)"])
    MED(["Media Editor Desk (media_editor)"])
    OREG(["Office of the Registrar (office_registrar)"])
    PUB(["Public Broadcast Archive & Social Media"])

    %% Stage 0: Creation
    subgraph STAGE_0 [Stage 0: Activity Submission]
        CA -->|1. Create Record & Attach Mandatory Photos| S_PEND[Status: verification_pending]
        CA -.->|Use AI Phrasing / Template Library| S_PEND
    end

    %% Stage 1: Institutional Verification
    subgraph STAGE_1 [Stage 1: Institutional Authority Verification]
        S_PEND -->|Reviewed by Registrar ID| REG
        REG -->|Action: Approve| S_MED[Status: media_review]
        REG -->|Action: Discuss / Return| S_DISC[Status: discuss / sent_back]
        REG -->|Action: Reject| S_REJ[Status: rejected]
        S_DISC -->|College Admin Corrects & Resubmits| S_PEND
    end

    %% Stage 2: Media Studio Enhancement
    subgraph STAGE_2 [Stage 2: Media Studio & PR Enhancement]
        S_MED --> MED
        MED -->|2. Optimize Photos & Refine Caption via AI| MED_SUB[Submit to Office of Registrar]
        MED_SUB -->|Status changes to registrar_approval| S_LOCK[Status: registrar_approval <br/> 🔒 RECORD LOCKED FOR EDITOR]
    end

    %% Stage 3: Executive Approval
    subgraph STAGE_3 [Stage 3: Office of the Registrar Review]
        S_LOCK --> OREG
        OREG -->|Action: Approve Final Package| S_READY[Status: ready_to_publish]
        OREG -->|Action: Discuss / Request Correction| S_EDISC[Status: editor_discuss <br/> 🔓 RECORD UNLOCKED]
        OREG -->|Action: Reject| S_REJ
        S_EDISC -->|Editor Makes Corrections| MED
    end

    %% Stage 4: Publication
    subgraph STAGE_4 [Stage 4: Broadcast & Archiving]
        S_READY -->|3. Editor / Admin Triggers Publish| PUB_ACT[Status: published / scheduled]
        PUB_ACT --> PUB
    end

    %% Styling
    style S_LOCK fill:#fef08a,stroke:#854d0e,stroke-width:2px,color:#713f12
    style S_READY fill:#bbf7d0,stroke:#166534,stroke-width:2px,color:#14532d
    style S_REJ fill:#fecaca,stroke:#991b1b,stroke-width:2px,color:#7f1d1d
```

---

## 5. Submission Locking & Unlocking State Machine

To preserve review integrity, the system implements an automatic locking mechanism when activities are transferred between the Media Editor and the Office of the Registrar.

```mermaid
stateDiagram-v2
    [*] --> media_review: Approved by Registrar ID
    
    state "Stage 1: Media Review (Editable by Editor)" as media_review
    state "Stage 1: Editor Discuss / Returned (Editable by Editor)" as editor_discuss
    state "🔒 LOCKED: Office of Registrar Review (Editing Disabled)" as registrar_approval
    state "✅ Ready to Publish (Stage 2 Broadcast Queue)" as ready_to_publish

    media_review --> registrar_approval: Editor clicks 'Submit to Registrar'
    editor_discuss --> registrar_approval: Editor resubmits corrected package

    registrar_approval --> editor_discuss: Office of Registrar marks as 'Discuss' (UNLOCKED)
    registrar_approval --> ready_to_publish: Office of Registrar marks as 'Approve'
    registrar_approval --> Rejected: Office of Registrar Rejects

    note right of registrar_approval
        When locked:
        1. Controller aborts edit/update requests.
        2. UI hides 'Edit & Submit' button.
        3. Displayed in 'Locked Submissions' queue.
    end note
```

---

## 6. Core System Modules & Features

### 6.1 Mandatory Image Verification & Attachment Engine
- **Strict Enforcement**: Every activity submission must include at least one high-resolution image (`images[]`). The backend `ActivitySubmissionController` enforces validation (`required|array|min:1`).
- **Media Gallery Storage**: Uploaded files are hashed, stored in `/uploads/activities/images/`, and indexed in the `activity_media` table with automatic primary image tagging (`is_primary = true`).
- **Supplemental Brochures**: Supports optional PDF event brochures (`document`) up to 15MB.

### 6.2 Universal AI Phrasing & Institutional Template Library (>10 Templates)
Available to **all portal users** across activity creation, modification, and media editing modal windows:
- **12+ Professional Institutional Templates**: Pre-configured, high-standard broadcast phrasing organized into specialized categories:
  1. *Academic Symposium & National Conference*
  2. *International Hands-on Technical Workshop*
  3. *Community Health & Medical Screening Outreach Camp*
  4. *Research Colloquium & Expert Guest Lecture*
  5. *Strategic Industry Partnership & MoU Signing*
  6. *Value Added & Professional Skill Development Program*
  7. *Annual Inter-College Sports Championship*
  8. *Institutional Cultural & Arts Extravaganza*
  9. *Faculty Development Program (FDP)*
  10. *Alumni Meet & Career Mentorship Conclave*
  11. *24-Hour Innovation & Hackathon Challenge*
  12. *NSS / NCC Environmental & Community Outreach Drive*
- **Dynamic Placeholder Replacement**: Clicking **"Use This Template"** automatically detects the active form's Event Title and College Name, injecting customized institutional phrasing directly into the description or caption field.
- **Live AI Phrasing Generator**: Connects to `/api/ai/generate-captions` to synthesize platform-specific suggestions (Instagram, LinkedIn, Twitter/X) with emojis and official university hashtags (`#VMRFDU`).

### 6.3 Media Editor Studio Desk (`/media-editor`)
- **Stage 1 (Photo & Caption Editing)**: Displays verified queues awaiting editorial enhancement alongside a visual count badge.
- **Locked Submissions Monitor**: Dedicated section showing all records currently awaiting evaluation by `officeregistrar@vmrfdu.edu.in`.
- **Stage 2 (Ready for Broadcast)**: Manages final authorized packages ready for immediate publication or scheduled dispatch.
- **One-Click ZIP Export**: Packages all high-resolution photos, primary edited images, and PDF documents into a downloadable ZIP archive (`/media-editor/{id}/download-pack`).

---

## 7. Database Schema Overview

```mermaid
erDiagram
    COLLEGES ||--o{ USERS : "employs"
    COLLEGES ||--o{ DEPARTMENTS : "contains"
    COLLEGES ||--o{ ACTIVITY_SUBMISSIONS : "hosts"
    DEPARTMENTS ||--o{ ACTIVITY_SUBMISSIONS : "maps_to"
    USERS ||--o{ ACTIVITY_SUBMISSIONS : "creates"
    ACTIVITY_SUBMISSIONS ||--o{ ACTIVITY_MEDIA : "has_attachments"
    ACTIVITY_SUBMISSIONS ||--o{ APPROVAL_LOGS : "generates_history"
    ACTIVITY_SUBMISSIONS ||--o{ ACTIVITY_DISCUSSIONS : "has_comments"

    ACTIVITY_SUBMISSIONS {
        bigint id PK
        string reference_id UK "VMRF-2026-XXXX"
        bigint college_id FK
        bigint department_id FK
        bigint user_id FK
        string title
        text description
        date activity_date
        string event_category
        string status "verification_pending | media_review | registrar_approval | ready_to_publish | published"
        string current_stage
        text social_media_caption
    }

    ACTIVITY_MEDIA {
        bigint id PK
        bigint activity_submission_id FK
        string file_type "image | video | document"
        string file_path
        string file_name
        boolean is_primary
    }

    APPROVAL_LOGS {
        bigint id PK
        bigint activity_submission_id FK
        bigint user_id FK
        string action "submitted | approved | discuss | rejected"
        string from_status
        string to_status
        text remarks
    }
```

---

## 8. Key HTTP Routes & Controller Mapping

| HTTP Method | Route URI | Controller & Action | Middleware / Security Scope |
| :--- | :--- | :--- | :--- |
| `GET` | `/dashboard` | `DashboardController@index` | `auth` & Role-scoped metric computation |
| `GET` | `/submissions` | `ActivitySubmissionController@index` | Scoped by `college_id` for college admins |
| `POST` | `/submissions` | `ActivitySubmissionController@store` | Enforces mandatory `images[]` upload |
| `POST` | `/submissions/{id}/approval` | `ApprovalController@processAction` | Authority verification (`registrar` / `office_registrar`) |
| `GET` | `/media-editor` | `MediaEditorController@dashboard` | Restricted to `media_editor` & authorities |
| `POST` | `/media-editor/{id}/submit-registrar` | `MediaEditorController@submitToRegistrar` | Locks submission (`status = registrar_approval`) |
| `POST` | `/media-editor/{id}/publish` | `MediaEditorController@publish` | Transitions to `published` & creates broadcast feed |
| `GET` | `/media-editor/{id}/download-pack` | `MediaEditorController@downloadMediaPack` | Generates on-the-fly ZIP bundle |
| `POST` | `/api/ai/generate-captions` | `ActivitySubmissionController@generateAiCaptions`| Returns instant AI phrasing & social captions |

---

## 9. Footer & Branding Standard
All public and portal pages adhere strictly to the university branding standard:
```html
2026 VMRF(DU) – Vinayaka Mission's Research Foundation (Deemed to be University). All Rights Reserved.
Designed & Developed by VMRF(DU) Web Team
```
