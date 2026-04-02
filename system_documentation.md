# 🏛️ SesekaliCBT (ExamFlow) - Comprehensive System Documentation

This document provides a technical mapping of the **SesekaliCBT** (formerly ExamFlow) system. It is designed to be ingested by an AI or developer to quickly understand the architecture, data flow, and core logic of the application.

---

## 🚀 1. System Identity & Tech Stack

**SesekaliCBT** is a modern LMS and CBT system optimized for high-security exams, real-time monitoring, and student engagement through gamification.

-   **Framework**: Laravel 12.x (PHP 8.5)
-   **Frontend**: Tailwind CSS 3.x, Alpine.js (for reactive components), Blade Templating.
-   **Database**: MySQL / MariaDB (Optimized with Laravel Cache Layer).
-   **Key Architecture**: Monolithic (SSR) with heavy client-side scripting for focus detection and real-time polling.

---

## 🛡️ 2. Core Architecture & Security

### 2.1 Role-Based Access Control (RBAC)
The system uses a custom `role` middleware and specific scoping for data access:
-   **Superadmin**: Full system access, users management, and destructive actions.
-   **Teacher**: Access to Bank Soal, Exams, and Results **scoped to their assigned subjects**.
-   **Tata Usaha (TU)**: Access to letter templates, administrative forms, and student management.
-   **Principal**: Read-only access to academic performance and school-wide statistics.
-   **Student**: Access to exams, results, and gamification dashboard.

### 2.2 Security & Anti-Cheat (Focus Detection)
A multi-layered security system ensures exam integrity:
-   **Tab Switching Detection**: JavaScript listeners (`onblur`, `visibilitychange`) track if a student leaves the exam window.
-   **Strike System**: Violations are recorded in `exam_violations`. Reaching a configured limit (e.g., 3 strikes) triggers an **Auto-Submit**.
-   **Heartbeat Monitor**: Students send a "heartbeat" periodically. If a student is "Offline" for too long, the supervisor is alerted on the live monitoring dashboard.
-   **Token Dynamic Gating**: Ujian access is restricted by a 6-character alphanumeric token, refreshed periodically by the server.

---

## 📊 3. Database Encyclopedia (Key Relations)

### 3.1 CBT Engine
-   `exams`: Core exam definition (title, duration, type, KKM, scoring weights).
-   `questions`: Bank soal (Rich text questions, 5 options, 1 correct answer, essays support).
-   `exam_question`: Pivot table linking exams to randomized/selected questions.
-   `exam_attempts`: Tracks a student's session (status: `starting`, `inprogress`, `submitted`, `graded`).
-   `exam_answers`: Stores student responses (selected choice for PG, text content for Essay).
-   `exam_violations`: Records time and type of focus violations.

### 3.2 Gamification Engine
-   `battle_rooms`: Temporary rooms for real-time competitions.
-   `battle_participants`: Links students to rooms, tracks HP, XP earned, and rank.
-   `achievements`: Definitions for badges (criteria, XP reward).
-   `themes`: UI skins unlocked by levels or special achievements (Stored in `ui_theme` column of `users`).
-   `seasons`: Time-bound competitive periods for leaderboard resets.

### 3.3 Administration & Raport
-   `letter_templates`: HTML/Markdown templates with dynamic placeholders like `[[nama_lengkap]]`, `[[nis]]`, etc.
-   `manual_grades`: For subjects not handled via CBT (e.g., Praktek).
-   `extracurriculars`: Activity management with attendance and coach assignment.

---

## 🕹️ 4. Module Deep-Dive

### 4.1 The CBT Lifecycle
1.  **Drafting**: Teacher creates questions and assigns them to an Exam (Draft status).
2.  **Publishing**: Admin/Teacher publishes the exam, making it visible to students.
3.  **Token Generation**: Supervisor generates a session token.
4.  **Authorization**: Student enters NIS, Password, and the active Token.
5.  **Taking**: Student answers questions. Alpine.js manages "Auto-Save" via AJAX calls to `autosave` endpoint.
6.  **Submission**: Manual submit or Auto-submit (Time out / Strike limit).
7.  **Grading**: Auto-grading for PG; manual grading for Essays by the teacher.

### 4.2 Battle Arena (Real-time Logic)
The Battle Arena uses an **Optimized Polling** strategy for shared hosting:
-   **Lobby Polling**: Participants poll every 1-2 seconds.
-   **Caching**: Room status and participant counts are cached to avoid heavy DB hits during high-concurrency "waiting" periods.
-   **Event Loop**: Mocking real-time events through frequent AJAX calls combined with timestamp-based server validation.

### 4.3 Smart Letter Templates
The system uses a custom parser to replace placeholders in letter templates.
-   **Format**: Double brackets (e.g., `[[PlaceHolder]]`).
-   **Bulk Action**: Supports batch generating hundreds of PDF letters (e.g., SPPD or SK Aktif) in one process with progress tracking.

---

## ⚙️ 5. Performance & Optimization

-   **Image Handling**: Uses `Intervention Image` for compression and resizing (avatar & question images).
-   **Caching**: `cache_table` stores session states and frequently accessed configs.
-   **Asset Pipeline**: `Vite` for compiling CSS (Tailwind) and JS (Alpine.js), ensuring minimal bundle size.
-   **Throttling**: Rate-limiting on `autosave` and `heartbeat` endpoints to prevent server overload during large exams.

---

## 🛠️ 6. Troubleshooting & Integration

-   **Force Submit**: Admin can force a student's `exam_attempt` state to `submitted` from the monitor dashboard.
-   **Reset Session**: If a student's device fails, Admin can "Reopen" the session, allowing entry from a new device without data loss (due to heartbeat/autosave persistence).
-   **Exporting Data**: All results and student lists can be exported as `.xlsx` (Excel) using `Laravel Excel`.

---
*Generated: 2026-04-02 | System: SesekaliCBT v2.5 (Enterprise)*
