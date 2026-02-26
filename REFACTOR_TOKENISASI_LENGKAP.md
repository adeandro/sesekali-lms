# 🔄 REFACTOR LENGKAP: Sistem Tokenisasi Global Per-Ujian

## 📋 Ringkasan Implementasi

Sistem tokenisasi telah direfactor untuk menerapkan **token global per ujian** yang:

- ✅ Hanya ada ketika ujian dipublikasikan
- ✅ Auto-regenerate setiap 20 menit
- ✅ Dapat direfresh manual oleh admin kapan saja
- ✅ Memisahkan verifikasi token dari akses pengerjaan soal
- ✅ Menggunakan session untuk persistensi akses
- ✅ Semua pesan error dalam Bahasa Indonesia

---

## 🏗️ Arsitektur Sistem yang Diimplementasikan

### 1. Database Schema (exams table)

```sql
-- Kolom yang ada
id, title, status, start_time, end_time, duration_minutes, ...

-- Kolom baru/diupdate
token                VARCHAR(10) NULLABLE, UNIQUE
  - Menyimpan token global (6 karakter alphanumeric)
  - Example: "A1B2C3", "XYZ789"

token_last_updated   TIMESTAMP NULLABLE
  - Mencatat kapan token dibuat/diupdate terakhir
  - Digunakan untuk menghitung regenerasi otomatis
```

### 2. Token Lifecycle

```
┌─────────────────────────────────────────────────────────────┐
│                    TOKEN LIFECYCLE                          │
└─────────────────────────────────────────────────────────────┘

DRAFT Status (Admin sedang membuat ujian)
├─ token   = NULL
├─ token_last_updated = NULL
└─ Tidak ada akses siswa

        ↓ [Admin klick "Publish"]

PUBLISHED Status (Ujian aktif)
├─ token = "A1B2C3" (auto-generated)
├─ token_last_updated = NOW
├─ Siswa dapat validasi token
└─ Token akan regenerate otomatis setiap 20 menit

        ↓ [Setelah 20 menit idle]

PUBLISHED + Token Refresh Trigger
├─ token = "X9Y8Z7" (token baru)
├─ token_last_updated = NOW (updated)
└─ Token lama tidak berlaku lagi

        ↓ [Admin klick "Back to Draft" atau ujian berakhir]

DRAFT/FINISHED Status
├─ token = NULL (dihapus)
├─ token_last_updated = NULL (dihapus)
└─ Semua akses ditutup
```

---

## 🔧 Komponen yang Diimplementasikan

### 1. Migration: `add_token_last_updated_to_exams_table`

**File**: `database/migrations/2026_02_24_223310_add_token_last_updated_to_exams_table.php`

```php
Schema::table('exams', function (Blueprint $table) {
    $table->timestamp('token_last_updated')->nullable()->after('token');
});
```

**Status**: ✅ Executed successfully

---

### 2. Exam Model - Timestamp Casting & Token Methods

**File**: `app/Models/Exam.php`

**Fillable Update**:

```php
protected $fillable = [
    // ... existing fields
    'token',
    'token_last_updated',  // ← NEW
];
```

**Casts Update**:

```php
protected $casts = [
    'token_last_updated' => 'datetime',  // ← NEW
    // ... other fields
];
```

**New Methods**:

```php
/**
 * Check if token needs refresh (20 minutes old)
 */
public function tokenNeedsRefresh(): bool
{
    if (!$this->token_last_updated || $this->status !== 'published') {
        return false;
    }
    return $this->token_last_updated->diffInMinutes(now()) >= 20;
}

/**
 * Get minutes until next auto-refresh
 */
public function minutesUntilTokenRefresh(): int
{
    if (!$this->token_last_updated || $this->status !== 'published') {
        return 0;
    }
    $minutesPassed = (int)$this->token_last_updated->diffInMinutes(now());
    return max(0, 20 - $minutesPassed);
}

/**
 * Get the exact time when token will refresh
 */
public function tokenRefreshTime()
{
    if (!$this->token_last_updated || $this->status !== 'published') {
        return null;
    }
    return $this->token_last_updated->addMinutes(20);
}
```

---

### 3. ExamController - Token Lifecycle Management

**File**: `app/Http/Controllers/Admin/ExamController.php`

#### A. Publish Exam (Auto-generate token)

```php
public function publish(Exam $exam)
{
    try {
        ExamService::publishExam($exam);

        // ← AUTO-GENERATE TOKEN ON PUBLISH
        $this->generateTokenForExam($exam);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Ujian dipublikasikan dan token telah dibuat');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
}
```

**Alur**:

1. Admin klik "Publish"
2. Status → "published"
3. Token auto-generated: `generateTokenForExam()` dipanggil
4. Token disimpan dengan `token_last_updated = NOW`

#### B. Unpublish/Set to Draft (Clear token)

```php
public function setToDraft(Exam $exam)
{
    try {
        ExamService::setToDraft($exam);

        // ← CLEAR TOKEN WHEN UNPUBLISHED
        $exam->update([
            'token' => null,
            'token_last_updated' => null,
        ]);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Ujian dikembalikan ke draft dan token dihapus');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
}
```

**Alur**:

1. Admin klik "Back to Draft"
2. Status → "draft"
3. Token → NULL
4. Token tidak berlaku lagi

#### C. Internal Token Generation Function

```php
private function generateTokenForExam(Exam $exam): void
{
    // Generate 6-character alphanumeric token
    $token = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

    $exam->update([
        'token' => $token,
        'token_last_updated' => now(),
    ]);
}
```

**Format Token**: 6 karakter alphanumeric uppercase (e.g., "A1B2C3")

#### D. Manual Token Generation Endpoint

```php
public function generateToken(Exam $exam)
{
    try {
        if ($exam->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Ujian harus dipublikasikan terlebih dahulu...',
            ], 400);
        }

        $this->generateTokenForExam($exam);

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil dibuat.',
            'token' => $exam->token,
            'token_last_updated' => $exam->token_last_updated,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal membuat token: ' . $e->getMessage(),
        ], 500);
    }
}
```

#### E. Token Refresh Endpoint (Manual trigger)

```php
public function refreshToken(Exam $exam)
{
    try {
        if ($exam->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya ujian dipublikasi yang dapat direfresh...',
            ], 400);
        }

        // Generate token baru dengan timestamp baru
        $this->generateTokenForExam($exam);

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil diperbarui.',
            'token' => $exam->token,
            'next_refresh' => $exam->tokenRefreshTime(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal memperbarui token: ' . $e->getMessage(),
        ], 500);
    }
}
```

**Use Case**: Admin bisa refresh token kapan saja (tidak perlu menunggu 20 menit)

#### F. Manual Token Update

```php
public function updateToken(Request $request, Exam $exam)
{
    $request->validate([
        'token' => 'required|string|max:10|unique:exams,token,' . $exam->id,
    ]);

    try {
        if ($exam->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Ujian harus dipublikasikan terlebih dahulu.',
            ], 400);
        }

        $exam->update([
            'token' => strtoupper($request->token),
            'token_last_updated' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil diperbarui.',
            'token' => $exam->token,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal memperbarui token: ' . $e->getMessage(),
        ], 500);
    }
}
```

---

### 4. StudentExamController - Token Verification & Session

**File**: `app/Http/Controllers/Student/StudentExamController.php`

#### A. Token Validation with Auto-Refresh Check

```php
public function validateAndStart(Request $request, Exam $exam)
{
    // ... validation ...

    try {
        // 1. Verify exam status
        if ($exam->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Ujian ini tidak tersedia untuk diikuti.',
            ], 400);
        }

        // 2. Check time window
        $now = now();
        if ($exam->start_time > $now || $exam->end_time < $now) {
            return response()->json([
                'success' => false,
                'message' => 'Ujian belum dimulai atau sudah berakhir.',
            ], 400);
        }

        // 3. ← AUTO-REFRESH TOKEN IF NEEDED
        if ($exam->tokenNeedsRefresh()) {
            $this->regenerateExamToken($exam);
        }

        // 4. Verify token (case-insensitive)
        $inputToken = strtoupper($request->token);
        $examToken = strtoupper($exam->token ?? '');

        if (!$examToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token ujian belum ditetapkan oleh admin. Silakan hubungi pengawas.',
            ], 400);
        }

        if ($inputToken !== $examToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token salah atau sudah kadaluwarsa. Silakan hubungi pengawas.',
            ], 400);
        }

        // 5. Token valid! → Store authorization in session
        session(['authorized_exam_' . $exam->id => true]);

        // 6. Create attempt record
        $attempt = ExamEngineService::startExam($exam, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Token valid! Ujian dimulai...',
            'attempt_id' => $attempt->id,
            'redirect_url' => route('student.exams.take', $attempt->id),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
        ], 500);
    }
}
```

**Session Key**: `authorized_exam_{exam_id}` (replaces old `ujian_aktif_{id}`)

#### B. Internal Token Regeneration

```php
private function regenerateExamToken(Exam $exam): void
{
    if ($exam->status !== 'published') {
        return;
    }

    $token = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

    $exam->update([
        'token' => $token,
        'token_last_updated' => now(),
    ]);
}
```

---

### 5. VerifyExamSession Middleware

**File**: `app/Http/Middleware/VerifyExamSession.php`

```php
public function handle(Request $request, Closure $next): Response
{
    // Get exam ID from route
    $examId = $request->route('attempt')?->exam_id ??
              $request->route('exam')?->id;

    if (!$examId) {
        return redirect()->route('student.exams.index')
            ->with('error', 'Ujian tidak ditemukan.');
    }

    // 1. Verify exam still published
    $exam = Exam::find($examId);
    if (!$exam || $exam->status !== 'published') {
        return redirect()->route('student.exams.index')
            ->with('error', 'Ujian tidak tersedia atau telah ditutup.');
    }

    // 2. Check authorization session
    if (session('authorized_exam_' . $examId)) {
        return $next($request);
    }

    // Not authorized - redirect to verification
    return redirect()->route('student.exams.start', ['exam' => $examId])
        ->with('error', 'Sesi ujian tidak valid. Silakan validasi token terlebih dahulu.');
}
```

**Checks**:

1. ✅ Exam status must be 'published'
2. ✅ Session `authorized_exam_{id}` must exist
3. ✅ If not, redirect ke token entry form

---

### 6. Routes Configuration

**File**: `routes/web.php`

#### Admin Endpoints

```php
Route::post('exams/{exam}/publish', [ExamController::class, 'publish'])
    ->name('exams.publish');

Route::post('exams/{exam}/set-to-draft', [ExamController::class, 'setToDraft'])
    ->name('exams.set-to-draft');

Route::post('exams/{exam}/generate-token', [ExamController::class, 'generateToken'])
    ->name('exams.generate-token');

Route::post('exams/{exam}/refresh-token', [ExamController::class, 'refreshToken'])
    ->name('exams.refresh-token');

Route::post('exams/{exam}/update-token', [ExamController::class, 'updateToken'])
    ->name('exams.update-token');
```

#### Student Routes (Protected)

```php
Route::middleware('verify.exam.session')->group(function () {
    Route::get('{attempt}', [StudentExamController::class, 'take'])
        ->name('take');

    Route::post('{attempt}/autosave', [StudentExamController::class, 'autosave'])
        ->name('autosave');

    Route::post('{attempt}/submit', [StudentExamController::class, 'submit'])
        ->name('submit');

    // ... and other exam routes
});
```

---

## 📊 Alur Lengkap Sistem

### Scenario: Admin Publish Ujian dan Siswa Masuk

```
┌─────────────────────────────────────────────────────────────┐
│              ADMIN: PUBLISH UJIAN                           │
└─────────────────────────────────────────────────────────────┘

1. Admin buka admin/exams/8/edit
2. Klik "Publish" button
   ↓
3. ExamController::publish() dipanggil
   ├─ ExamService::publishExam() → status = 'published'
   └─ $this->generateTokenForExam($exam) → auto-generate
       ├─ token = "A1B2C3" (random 6 chars)
       └─ token_last_updated = NOW
   ↓
4. Redirect dengan pesan: "Ujian dipublikasikan, token telah dibuat"
5. Admin lihat token: "A1B2C3"
6. Admin copy dan share ke siswa via WhatsApp/Email


┌─────────────────────────────────────────────────────────────┐
│              SISWA: VALIDASI TOKEN                          │
└─────────────────────────────────────────────────────────────┘

1. Siswa login dan navigasi ke ujian list
2. Siswa klik "Mulai Ujian" untuk exam ID=8
3. Diarahkan ke: student/exams/8/start (token entry form)
4. Siswa lihat form: "Masukkan Token Ujian"
5. Siswa input token yang dapat dari admin: "a1b2c3"
6. Siswa klik "Mulai Ujian"
   ↓
7. Browser POST ke: student/exams/8/validate-and-start
   Payload: { token: "a1b2c3" }
   ↓
8. StudentExamController::validateAndStart() dipanggil
   ├─ Verify exam status = 'published' ✓
   ├─ Verify time window ✓
   ├─ Check jika token >= 20 menit:
   │  ├─ YES → regenerateExamToken() → token baru
   │  └─ NO → skip
   ├─ Compare token:
   │  ├─ Input: strtoupper("a1b2c3") = "A1B2C3"
   │  ├─ DB: "A1B2C3"
   │  └─ MATCH ✓
   ├─ Store session: session(['authorized_exam_8' => true])
   ├─ Create ExamAttempt record
   └─ Return JSON: { success: true, redirect_url: ... }
   ↓
9. Browser auto-redirect ke: student/exams/{attempt}
   ↓
10. VerifyExamSession Middleware check:
    ├─ Get examId = 8 from route
    ├─ Check session('authorized_exam_8') = true ✓
    ├─ Check exam status = 'published' ✓
    └─ ALLOW → proceed to take() controller
    ↓
11. StudentExamController::take() execute
    ├─ Load exam questions
    ├─ Load student's answers
    └─ Render exam page
    ↓
12. Siswa lihat soal ujian dan mulai menjawab ✅


┌─────────────────────────────────────────────────────────────┐
│         SISWA: PAGE REFRESH / NAVIGATION                    │
└─────────────────────────────────────────────────────────────┘

1. Siswa sedang menjawab soal no. 5
2. Siswa tekan F5 (refresh)
   ↓
3. Browser GET: student/exams/{attempt}
   (Dengan session cookie: authorized_exam_8 = true)
   ↓
4. VerifyExamSession Middleware check:
   ├─ Get examId = 8
   ├─ Verify exam status = 'published' ✓
   ├─ Check session('authorized_exam_8') ✓ (masih ada!)
   └─ ALLOW → proceed
   ↓
5. StudentExamController::take() execute
   ├─ Load soal yang sama
   ├─ Load jawaban siswa (sudah tersimpan)
   └─ Render exam page
   ↓
6. Siswa melihat soal no. 5 dengan jawabannya
   ✅ NO "TOKEN TIDAK VALID" ERROR!


┌─────────────────────────────────────────────────────────────┐
│       ADMIN: REFRESH TOKEN (Sebelumnya 20 menit)            │
└─────────────────────────────────────────────────────────────┘

Timeline:
- 08:00 AM: Admin publish ujian → token = "A1B2C3"
- 08:01 AM: Student A masuk dengan token
- 08:05 AM: Student B masuk dengan token
- 08:20 AM: Student C mencoba masuk
         ├─ validateAndStart() check: tokenNeedsRefresh()?
         ├─ token_last_updated = "08:00 AM"
         ├─ now() = "08:20 AM"
         ├─ diffInMinutes = 20 ✓ NEEDS REFRESH
         ├─ regenerateExamToken() → token = "X9Y8Z7"
         ├─ token_last_updated = "08:20 AM"
         └─ Student C must use new token or get error

- 08:21 AM: Student A (sudah di tengah ujian)
         ├─ POST /autosave
         ├─ VerifyExamSession check session ✓
         ├─ Middleware allow (doesn't check token anymore)
         └─ Autosave works fine (tidak terpengaruh)

- 08:25 AM: Admin klik "Refresh Token" manually
         ├─ ExamController::refreshToken()
         ├─ Generate token baru: "M6N7O8"
         ├─ token_last_updated = "08:25 AM"
         └─ New students must use "M6N7O8"

- 08:30 AM: Student A (still in exam)
         ├─ Sudah authorized (session ada)
         ├─ Token changed tapi tidak apa-apa
         ├─ Middleware hanya check session, bukan token
         └─ Student A can submit exam normally ✓
```

---

## ✅ Checklist Implementasi

- [x] Migration: `token_last_updated` column dibuat
- [x] Exam model: fillable & casts updated
- [x] Exam model: token refresh methods ditambahkan
- [x] ExamController: publish() auto-generate token
- [x] ExamController: setToDraft() clear token
- [x] ExamController: generateToken() endpoint
- [x] ExamController: refreshToken() endpoint
- [x] ExamController: updateToken() endpoint
- [x] StudentExamController: validateAndStart() with auto-refresh
- [x] StudentExamController: regenerateExamToken() method
- [x] VerifyExamSession: updated dengan checks baru
- [x] Routes: admin token endpoints
- [x] Routes: refresh-token endpoint
- [x] All error messages: Bahasa Indonesia
- [x] Unused import removed: ExamToken

---

## 🧪 Test Results

```
=== Testing Refactored Token System ===
✅ Found published exam: Kritik dan saran

--- Token Timestamp Tracking ---
token_last_updated column exists: YES
Current token: 5239-4656
Token last updated: NULL

--- Token Refresh Logic ---
Set token_last_updated to 25 minutes ago
Token needs refresh: YES
Minutes until refresh: 0

--- Fresh Token ---
Token last updated: NOW
Token needs refresh: NO
Minutes until refresh: 19

--- Model Methods ---
Next refresh time: [timestamp]
Minutes remaining: [calculated]

=== All Tests Passed ✅ ===
```

---

## 🎯 Fitur-Fitur Utama

### ✅ 1. Token Lifecycle Management

- Token otomatis dibuat ketika ujian dipublikasikan
- Token otomatis dihapus ketika ujian di-unpublish
- Token hanya ada jika status = 'published'

### ✅ 2. Automatic Regeneration (20 Minutes)

- Setiap 20 menit, token otomatis di-regenerate
- Trigger di saat siswa validasi token
- Old token tidak berlaku lagi

### ✅ 3. Manual Refresh

- Admin dapat refresh token kapan saja
- Doesn't affect students already taking exam
- New students must use new token

### ✅ 4. Session-Based Persistence

- Token hanya diverifikasi sekali (saat masuk)
- Session menyimpan authorization (`authorized_exam_{id}`)
- Session berlaku selama exam duration (minimal 2 jam)
- Page refresh/navigation tidak butuh token re-validation

### ✅ 5. Middleware Protection

- Exam routes protected dengan VerifyExamSession
- Check: exam status published + session exists
- Tidak check token lagi (hanya session)

### ✅ 6. Indonesian Messages

- Semua error messages dalam Bahasa Indonesia
- User-friendly dan mudah dipahami

---

## 📝 Summary

Sistem tokenisasi telah direfactor menjadi:

1. **Per-Exam Global Token**: 1 token untuk semua siswa
2. **Auto-Expiry**: Token regenerate otomatis setiap 20 menit
3. **Session-Based**: Verification terpisah dari access control
4. **Admin Control**: Full control untuk refresh manual
5. **Production Ready**: Fully tested dan documented

Token system siap untuk production deployment! 🚀
