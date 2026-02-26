# 🔄 System Architecture Diagram - Static Token with Session Persistence

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     EXAM TOKEN SYSTEM v2                        │
│                  (Static Token + Session)                       │
└─────────────────────────────────────────────────────────────────┘

            TIER 1: DATABASE LAYER
┌──────────────────────────────────────────────────────────────────┐
│                                                                  │
│  ┌────────────────┐    ┌──────────────┐    ┌──────────────┐   │
│  │  exams TABLE   │    │ sessions     │    │ exam_        │   │
│  ├────────────────┤    │ TABLE        │    │ attempts     │   │
│  │ id             │    ├──────────────┤    │ TABLE        │   │
│  │ title          │    │ id           │    ├──────────────┤   │
│  │ start_time     │    │ user_id      │    │ id           │   │
│  │ end_time       │    │ ip_address   │    │ student_id   │   │
│  │ status         │    │ user_agent   │    │ exam_id      │   │
│  │ 🔑 TOKEN ✨    │←───┤ payload      │    │ started_at   │   │
│  │ duration_min   │    │ created_at   │    │ submitted_at │   │
│  │ questions...   │    └──────────────┘    └──────────────┘   │
│  └────────────────┘                                             │
│        ↑                ↑                                       │
│        │ Store          │ Store session key:                   │
│        │ token here     │ 'ujian_aktif_8' => true              │
│                                                                │
└──────────────────────────────────────────────────────────────────┘

            TIER 2: APPLICATION LAYER
┌──────────────────────────────────────────────────────────────────┐
│                                                                  │
│           ┌─────────────────────────────────────┐              │
│           │  VerifyExamSession Middleware       │              │
│           ├─────────────────────────────────────┤              │
│           │ Check: session('ujian_aktif_N')     │              │
│           │ YES  → Next request                 │              │
│           │ NO   → Redirect to token form       │              │
│           └──────────┬──────────────────────────┘              │
│                      │                                         │
│           ┌──────────▼──────────────────────┐                │
│           │ StudentExamController           │                │
│           ├─────────────────────────────────┤                │
│           │ validateAndStart()              │                │
│           │                                  │                │
│           │ 1. Validate exam status         │                │
│           │ 2. Check time window            │                │
│           │ 3. Compare token                │                │
│           │ 4. Set session                  │                │
│           │ 5. Create attempt               │                │
│           │ 6. Return JSON redirect         │                │
│           └─────────────────────────────────┘                │
│                      │                                        │
│           ┌──────────▼──────────────────────┐                │
│           │  Admin ExamController           │                │
│           ├─────────────────────────────────┤                │
│           │ generateToken()                 │                │
│           │ - Random format: XXXX-XXXX      │                │
│           │ - Save to exam.token            │                │
│           │ - Return JSON token             │                │
│           │                                  │                │
│           │ updateToken()                   │                │
│           │ - Validate input                │                │
│           │ - Update exam.token             │                │
│           │ - Return success                │                │
│           └─────────────────────────────────┘                │
│                                                                │
└──────────────────────────────────────────────────────────────────┘

            TIER 3: ROUTING & VIEWS
┌──────────────────────────────────────────────────────────────────┐
│                                                                  │
│  ┌──────────────────┐           ┌──────────────────────────┐   │
│  │  Student Routes  │           │   Admin Routes           │   │
│  ├──────────────────┤           ├──────────────────────────┤   │
│  │ /student/exams   │           │ /admin/exams/{id}        │   │
│  │  ├─ index        │           │  ├─ show                 │   │
│  │  ├─ start        │           │  ├─ edit                 │   │
│  │  ├─ validate (✓) │           │  ├─ generate-token       │   │
│  │  ├─ take  (🔒)   │───────┐   │  └─ update-token         │   │
│  │  ├─ autosave(🔒)│       │   └──────────────────────────┘   │
│  │  ├─ submit (🔒) │       │                                  │
│  │  ├─ result (🔒) │       └──> 🔒 = Protected by                    │
│  │  └─ ...   (🔒)  │           VerifyExamSession            │
│  └──────────────────┘                                         │
│                                                                │
└──────────────────────────────────────────────────────────────────┘
```

---

## Complete Student Flow Diagram

```
┌──────────────────────────────────────────────────────────────┐
│  STUDENT EXAM ENTRY FLOW                                     │
└──────────────────────────────────────────────────────────────┘

   START
     │
     ▼
┌─────────────────────────┐
│ Student clicks "Mulai"  │
│ (Start Exam)            │
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────────────────────────────┐
│ Browser: GET /student/exams/{exam}/start        │
│ ✓ Not protected (no middleware)                 │
│ ✓ Show token entry form                         │
└────────────┬────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────┐
│ Student enters token:       │
│ Input: "abcd-1234"          │
│ Click: "Mulai Ujian"        │
└────────────┬────────────────┘
             │
             ▼
┌────────────────────────────────────────────────┐
│ Browser: POST /student/exams/{exam}/validate   │
│ Payload: { token: "abcd-1234" }                │
└────────────┬───────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────┐
│ Server: StudentExamController                   │
│   validateAndStart()                            │
│                                                 │
│   ✓ Check exam status = 'published'             │
│   ✓ Check time: now between start_time/end_time│
│   ✓ Compare token:                              │
│     strtoupper("abcd-1234") === strtoupper(    │
│              exam.token = "ABCD-1234"           │
│     ) ✓ MATCH!                                 │
│   ✓ Set session:                                │
│     session(['ujian_aktif_8' => true])          │
│   ✓ Create ExamAttempt                          │
│   ✓ Return JSON:                                │
│     { success: true, redirect_url: '...' }     │
│                                                 │
└────────────┬────────────────────────────────────┘
             │
             ▼
┌──────────────────────────────────────────────────┐
│ Browser: Auto-redirect to                        │
│ GET /student/exams/{attempt}                    │
│                                                  │
│ Now session exists: ujian_aktif_8 = true        │
└────────────┬─────────────────────────────────────┘
             │
             ▼
┌──────────────────────────────────────────────────┐
│ Middleware: VerifyExamSession                    │
│                                                  │
│ Check: session('ujian_aktif_8')?                │
│ YES ✓ (exists from validateAndStart)            │
│ → Allow request to proceed                      │
│                                                  │
└────────────┬─────────────────────────────────────┘
             │
             ▼
┌──────────────────────────────────────────────────┐
│ Server: StudentExamController::take()            │
│ ✓ Display exam questions                        │
│ ✓ Load student answers                          │
│ ✓ Start timer                                   │
└────────────┬─────────────────────────────────────┘
             │
             ▼
┌──────────────────────────────────────────────────┐
│ Student sees exam questions                     │
│ Starts answering                                │
└──────────────────────────────────────────────────┘


┌──────────────────────────────────────────────────────────────┐
│  STUDENT DURING EXAM (Persistence Test)                      │
└──────────────────────────────────────────────────────────────┘

Student at question 5, refreshes page...

   F5 (Refresh)
     │
     ▼
┌──────────────────────────────────────────────────┐
│ Browser: GET /student/exams/{attempt}            │
│ (Same route, page refresh)                       │
└────────────┬─────────────────────────────────────┘
             │
             ▼
┌──────────────────────────────────────────────────┐
│ Middleware: VerifyExamSession                    │
│                                                  │
│ Check: session('ujian_aktif_8')?                │
│ YES ✓ (still exists in database)                │
│ Timeout: Not reached (120 min lifetime)          │
│ → Allow request to proceed                      │
│                                                  │
└────────────┬─────────────────────────────────────┘
             │
             ▼
┌──────────────────────────────────────────────────┐
│ Server: Returns existing exam page              │
│ ✓ Same questions shown                          │
│ ✓ Previous answers loaded                       │
│ ✓ Timer continues                               │
└────────────┬─────────────────────────────────────┘
             │
             ▼
┌──────────────────────────────────────────────────┐
│ Student continues exam                          │
│ NO "TOKEN TIDAK VALID" ERROR! ✓✓✓              │
└──────────────────────────────────────────────────┘


Student autosaves answer...

   autosave.js triggers
     │
     ▼
┌──────────────────────────────────────────────────┐
│ Browser: POST /student/exams/{attempt}/autosave  │
│ Payload: { question_id: 5, answer: "C" }         │
│ Has session cookie with ujian_aktif_8=true      │
└────────────┬─────────────────────────────────────┘
             │
             ▼
┌──────────────────────────────────────────────────┐
│ Middleware: VerifyExamSession ✓ Pass             │
│ Controller: Saves answer                         │
│ Returns: { success: true }                      │
└────────────────────────────────────────────────────┘


Student views results...

   /student/exams/{attempt}/result
     │
     ▼
┌──────────────────────────────────────────────────┐
│ Middleware: VerifyExamSession ✓ Pass             │
│ Controller: Display results                      │
│ Returns: Result page                            │
└────────────────────────────────────────────────────┘

2 hours later (session expires)...

   student comes back to site
   session('ujian_aktif_8') is NOW EXPIRED
     │
     ▼
┌──────────────────────────────────────────────────┐
│ Browser: GET /student/exams/{attempt}            │
└────────────┬─────────────────────────────────────┘
             │
             ▼
┌──────────────────────────────────────────────────┐
│ Middleware: VerifyExamSession                    │
│                                                  │
│ Check: session('ujian_aktif_8')?                │
│ NO ✗ (expired - over 120 minutes)               │
│ → Redirect to token entry                       │
│ With message: "Sesi ujian tidak valid..."        │
│                                                  │
└────────────┬─────────────────────────────────────┘
             │
             ▼
┌──────────────────────────────────────────────────┐
│ Browser: GET /student/exams/{exam}/start         │
│ Show: Token entry form                           │
│ Message: "Sesi ujian tidak valid..."             │
│                                                  │
│ Student options:                                │
│ A) If exam still available, re-enter token      │
│ B) If exam ended, see "Exam closed" message     │
└──────────────────────────────────────────────────┘
```

---

## Admin Token Management Flow

```
┌──────────────────────────────────────────────────────────────┐
│  ADMIN TOKEN MANAGEMENT                                      │
└──────────────────────────────────────────────────────────────┘

Admin wants to allow exam entry

   Step 1: Go to exam edit page
     │
     ▼
   ┌─────────────────────────┐
   │ /admin/exams/8/edit     │
   │ (Edit exam page)        │
   └────────────┬────────────┘
                │
                ▼
   ┌─────────────────────────────────────────────┐
   │  Section: "Set Token"                       │
   │  ┌───────────────────────────────┐          │
   │  │ Current token: [empty/ABCD]   │          │
   │  │                               │          │
   │  │ [⚡ Generate Token Baru] <──┐ │          │
   │  │ [Set Token]                  │ │          │
   │  │ [Input field]                │ │          │
   │  └───────────────────────────────┘│          │
   │                                   │          │
   │   Option A: Auto-Generate ────────┘          │
   │     OR                                       │
   │   Option B: Manual Input                    │
   └──────────────┬──────────────────────────────┘
                  │
        ┌─────────┴─────────┐
        │                   │
        ▼                   ▼
   ┌─────────────┐  ┌──────────────────┐
   │ OPTION A    │  │ OPTION B         │
   │ Generate    │  │ Manual Setter    │
   ├─────────────┤  ├──────────────────┤
   │ Click       │  │ Type token:      │
   │ Generate    │  │ "MYTOKEN-1234"   │
   │             │  │ Click "Set Token"│
   └──────┬──────┘  └────────┬─────────┘
          │                  │
          ▼                  ▼
   ┌─────────────────────────────────────┐
   │ Server: ExamController              │
   │                                     │
   │ Option A:                           │
   │ - generateToken()                   │
   │ - Create random: "K9M2-X5L7"        │
   │ - exam.update(['token' => token])   │
   │                                     │
   │ Option B:                           │
   │ - updateToken(request)              │
   │ - Validate input: "MYTOKEN-1234"    │
   │ - exam.update(['token' => token])   │
   │                                     │
   │ Return: { success: true, token:...} │
   └────────────┬────────────────────────┘
                │
                ▼
   ┌──────────────────────────┐
   │ Browser shows token:     │
   │ "X5K2-M9L7" ✓           │
   │ [Copy] [Change]          │
   └────────────┬─────────────┘
                │
                ▼
   ┌──────────────────────────────────┐
   │ Admin copies token                │
   │ Sends to students via:            │
   │ - WhatsApp: "Token: X5K2-M9L7"    │
   │ - Email: "Use token: X5K2-M9L7"  │
   │ - Chat: "X5K2-M9L7"              │
   └────────────────────────────────────┘


During exam, admin wants to change token

   SCENARIO: Change token to block new entries
   but keep active students in exam

   ┌────────────────────────────┐
   │ Admin edits exam again     │
   │ See current token: X5K2-   │
   │ Clear field, type new:     │
   │ "NEWTOKENVALUE"            │
   │ Click "Set Token"          │
   └──────────────┬─────────────┘
                  │
                  ▼
          ┌───────────────────────┐
          │ exam.token updated    │
          │ OLD: X5K2-M9L7        │
          │ NEW: NEWTOKENVALUE    │
          └───────────────┬───────┘
                          │
          ┌───────────────┴───────────────┐
          │                               │
          ▼                               ▼
   ┌──────────────────┐        ┌──────────────────┐
   │ Student A        │        │ Student B (New)  │
   │ (Already in exam)│        │ (Never entered)  │
   │                  │        │                  │
   │ session exists:  │        │ Tries old token: │
   │ 'ujian_aktif_8'  │        │ "X5K2-M9L7"      │
   │ ✓ NOT affected   │        │                  │
   │ ✓ Can continue   │        │ validateAndStart │
   │ ✓ Can submit     │        │ compares:        │
   │ ✓ Can view score │        │ X5K2 !== NEW... │
   │                  │        │ ✗ Token mismatch │
   │                  │        │ → Error message  │
   │                  │        │   "Token error"  │
   │                  │        │                  │
   │                  │        │ Try new token:   │
   │                  │        │ "NEWTOKENVALUE"  │
   │                  │        │ validateAndStart │
   │                  │        │ matches! ✓       │
   │                  │        │ → Can enter exam │
   │                  │        │                  │
   │ 30 min later:    │        │                  │
   │ Submits exam ✓   │        │                  │
   │ Session invalid  │        │                  │
   │ Can view results │        │                  │
   └──────────────────┘        └──────────────────┘

   ✓ Old students unaffected
   ✓ New students use new token
   ✓ Token change is SAFE
```

---

## Database Flow

```
┌────────────────────────────┐
│  exams TABLE (Exam Data)   │
├────────────────────────────┤
│ id       | 8               │
│ title    | "Math Final"    │
│ token    | "X5K2-M9L7" ←──┐│ ONE token per exam
│ status   | "published"     ││
│ duration │ 60              ││
│ ...      | ...             ││
└────────────────────────────┘
           ▲
           │
      [Admin sets]
           │
┌──────────────────────────┐
│  sessions TABLE          │ Database persistence
├──────────────────────────┤ (Laravel default table)
│ id       | abc123...     │
│ user_id  | 15            │ Student ID
│ payload  | {...}         │ Serialized session data
│          | ujian_aktif_8 │ KEY: Set by validateAndStart
│          | => true       │ VALUE: true
│ ...      | ...           │
└──────────────────────────┘


┌──────────────────────────┐
│  exam_attempts TABLE     │ Exam progress tracking
├──────────────────────────┤ (used by ExamEngineService)
│ id       | 52            │
│ exam_id  | 8             │
│ student_ │ 15            │
│ id       |               │
│ started_ │ 2026-02-24... │
│ at       |               │
│ answers_count | 8        │ Questions answered
│ ...      | ...           │
└──────────────────────────┘


┌──────────────────────────┐
│  exam_answers TABLE      │ Individual answers
├──────────────────────────┤
│ id       | 201           │
│ attempt_ │ 52            │
│ id       |               │
│ question│ 1              │
│ _id      |               │
│ answer   | "C"           │ Student's chosen answer
│ ...      | ...           │
└──────────────────────────┘
```

---

## Middleware Flow (Request Processing)

```
Student Browser
     │
     ├─ GET /student/exams/52 (exam taking)
     │  (Session cookie: ujian_aktif_8=true)
     │
     ▼
┌────────────────────────────────┐
│ Laravel Routing                │
│ Match: student.exams.take      │
│ Middleware: 'verify.exam.session'
└──────────┬─────────────────────┘
           │
           ▼
┌────────────────────────────────────────┐
│ VerifyExamSession Middleware           │
│ __construct() { ... }                  │
│                                        │
│ handle(Request, Closure):              │
│ 1. Get examId from route param         │
│    $examId = $request->route('attempt') │
│            ->exam_id = 8               │
│                                        │
│ 2. Check session key:                  │
│    session('ujian_aktif_8')            │
│                                        │
│ 3. Decision:                           │
│    true  → return $next($request)      │
│    false → redirect to token form      │
└────────────┬────────────────────────────┘
             │
       ┌─────┴─────┐
       │           │
   YES (true)   NO (false)
       │           │
       ▼           ▼
 ┌──────────┐  ┌──────────────────────┐
 │ CONTINUE │  │ REDIRECT             │
 │ to next  │  │ to token validation  │
 │ middleware│  │ form                 │
 │ /handler │  └──────────────────────┘
 └────┬─────┘
      │
      ▼
 ┌───────────────────┐
 │ Controller Action │
 │ take()            │
 │ autosave()        │
 │ submit()          │
 │ result()          │
 └────┬──────────────┘
      │
      ▼
 ┌───────────────┐
 │ Response      │
 │ (HTML/JSON)   │
 └───────────────┘
```

---

This architecture ensures:

- ✅ Token-based entry control
- ✅ Session-based persistence
- ✅ Middleware protection on sensitive routes
- ✅ 120-minute sessions in database
- ✅ No token re-validation after entry
- ✅ Clean separation of concerns
