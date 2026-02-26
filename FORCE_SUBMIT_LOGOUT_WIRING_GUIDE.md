# Integration Guide: Force Submit/Logout Button Wiring

**Status**: Frontend buttons created ✅ | Backend endpoints ready ✅ | Integration code needed ⚠️

---

## What's Already Done

### Frontend (HTML/JavaScript)

✅ Buttons created in monitoring dashboard
✅ SweetAlert2 modal dialogs implemented
✅ Button click handlers defined (`forceSubmit()` and `forceLogout()`)
✅ Confirmation modals with reason input

### Backend (PHP/Laravel)

✅ `MonitoringController::forceSubmit()` method created
✅ `MonitoringController::forceLogout()` method created
✅ Routes configured in `web.php`
✅ ActionLog recording implemented
✅ Database fields ready (is_session_locked, force_submitted)

---

## What Needs to Be Wired

### Current State (Button JS in monitoring/index.blade.php)

```javascript
async function forceSubmit(sessionId, studentName) {
    const result = await Swal.fire({
        title: "⚠️ Hentikan Ujian?",
        // ... confirmation dialog ...
    });

    if (result.isConfirmed) {
        const reasonResult = await Swal.fire({
            title: "Alasan Penghentian",
            input: "text",
            // ... reason input ...
        });

        if (reasonResult.isConfirmed && reasonResult.value) {
            // ❌ IMPLEMENTATION NEEDED HERE
            try {
                Swal.fire({
                    title: "Memproses...",
                    icon: "info",
                    allowOutsideClick: false,
                    willOpen: async () => {
                        Swal.showLoading();
                        // ⚠️ ACTUAL API CALL DOESN'T EXIST YET
                    },
                });
            } catch (error) {
                Swal.fire("Error!", error.message, "error");
            }
        }
    }
}
```

---

## Solution: Wire Up the API Calls

### Step 1: Update `forceSubmit()` Function

**Location**: `resources/views/admin/monitoring/index.blade.php` (around line 135)

**Replace This**:

```javascript
if (reasonResult.isConfirmed && reasonResult.value) {
    // Implementation of force submit API call
    try {
        Swal.fire({
            title: "Memproses...",
            icon: "info",
            allowOutsideClick: false,
            willOpen: async () => {
                Swal.showLoading();
                // Implementation of force submit API call
            },
        });
    } catch (error) {
        Swal.fire("Error!", error.message, "error");
    }
}
```

**With This**:

```javascript
if (reasonResult.isConfirmed && reasonResult.value) {
    // Show loading dialog while processing
    Swal.fire({
        title: "Memproses...",
        icon: "info",
        allowOutsideClick: false,
        willOpen: async () => {
            Swal.showLoading();

            try {
                // Get exam attempt ID from session ID
                const attemptId = sessionId; // NOTE: May need mapping if sessionId !== attemptId

                // Call the backend endpoint
                const response = await fetch(
                    `/admin/monitor/attempts/${attemptId}/force-submit`,
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]',
                            ).content,
                        },
                        body: JSON.stringify({
                            reason: reasonResult.value,
                        }),
                    },
                );

                if (!response.ok) {
                    throw new Error(`Server error: ${response.statusText}`);
                }

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        title: "✅ Berhasil!",
                        text: `Ujian ${studentName} telah dihentikan.`,
                        icon: "success",
                    });

                    // Refresh monitoring data
                    setTimeout(refreshData, 1000);
                } else {
                    throw new Error(
                        result.message || "Gagal menghentikan ujian",
                    );
                }
            } catch (error) {
                Swal.fire(
                    "❌ Error!",
                    error.message ||
                        "Terjadi kesalahan saat menghentikan ujian",
                    "error",
                );
            }
        },
    });
}
```

---

### Step 2: Update `forceLogout()` Function

**Location**: `resources/views/admin/monitoring/index.blade.php` (around line 160)

**Replace This**:

```javascript
async function forceLogout(sessionId, studentName) {
    const result = await Swal.fire({
        title: "🔴 Logout Paksa?",
        html: `<p>Apakah Anda yakin ingin logout paksa <strong>${studentName}</strong>?</p><p class="text-sm text-red-600 mt-2">Aksi ini tidak dapat dibatalkan.</p>`,
        icon: "error",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Ya, Logout!",
        cancelButtonText: "Batal",
    });

    if (result.isConfirmed) {
        // Execute force logout
        Swal.fire("✅ Berhasil!", "Siswa telah dilogout", "success");
        refreshData();
    }
}
```

**With This**:

```javascript
async function forceLogout(sessionId, studentName) {
    const result = await Swal.fire({
        title: "🔴 Logout Paksa?",
        html: `<p>Apakah Anda yakin ingin logout paksa <strong>${studentName}</strong>?</p><p class="text-sm text-red-600 mt-2">Aksi ini tidak dapat dibatalkan.</p>`,
        icon: "error",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Ya, Logout!",
        cancelButtonText: "Batal",
    });

    if (result.isConfirmed) {
        // Show processing dialog
        Swal.fire({
            title: "Memproses...",
            icon: "info",
            allowOutsideClick: false,
            willOpen: async () => {
                Swal.showLoading();

                try {
                    // Get exam attempt ID from session ID
                    const attemptId = sessionId; // NOTE: May need mapping if sessionId !== attemptId

                    // Call the backend endpoint
                    const response = await fetch(
                        `/admin/monitor/attempts/${attemptId}/force-logout`,
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector(
                                    'meta[name="csrf-token"]',
                                ).content,
                            },
                            body: JSON.stringify({
                                reason: "Logout dipaksa oleh pengawas",
                            }),
                        },
                    );

                    if (!response.ok) {
                        throw new Error(`Server error: ${response.statusText}`);
                    }

                    const result = await response.json();

                    if (result.success) {
                        Swal.fire({
                            title: "✅ Berhasil!",
                            text: `${studentName} telah dilogout dari sistem.`,
                            icon: "success",
                        });

                        // Refresh monitoring data
                        setTimeout(refreshData, 1000);
                    } else {
                        throw new Error(
                            result.message || "Gagal melakukan logout paksa",
                        );
                    }
                } catch (error) {
                    Swal.fire(
                        "❌ Error!",
                        error.message || "Terjadi kesalahan saat logout paksa",
                        "error",
                    );
                }
            },
        });
    }
}
```

---

## Important: Session ID vs Attempt ID

⚠️ **CRITICAL**: The button passes `sessionId` (which is the ExamSession.id), but the endpoint expects `attemptId` (ExamAttempt.id).

### Option A: Pass Attempt ID Instead (Recommended)

Modify the table row to pass `id` instead of session ID:

```blade
<button onclick="forceSubmit({{ $session['attempt_id'] }}, '{{ $session['student_name'] }}')"
    class="px-3 py-1 text-xs bg-orange-500 text-white rounded hover:bg-orange-600 transition">
    <i class="fas fa-stop mr-1"></i>Hentikan
</button>
```

Then in the data gathering section of `index()` method in MonitoringController:

```php
$sessions = ExamSession::where('exam_id', $exam->id)
    ->with('student', 'examAttempt')
    ->orderBy('last_heartbeat', 'desc')
    ->get()
    ->map(function ($session) {
        return [
            'id' => $session->examAttempt->id,  // ← Actual attempt ID
            'student_name' => $session->student->name,
            // ... rest of mapping
        ];
    });
```

### Option B: Map Session ID to Attempt ID in JavaScript

```javascript
// In the fetch call, use the mapping
const attemptId = getAttemptIdFromSessionId(sessionId);

// Where getAttemptIdFromSessionId is a function that:
// - Either stores the mapping in a data attribute
// - Or calls a new endpoint to look it up
```

---

## Testing the Integration

### Test Force Submit

1. Open monitoring dashboard
2. Locate an active student
3. Click "Hentikan" button
4. Confirm in modal
5. Enter reason (e.g., "Ketahuan nyontek")
6. Click "Ya, Hentikan!"
7. Verify:
    - ✅ Success notification appears
    - ✅ Monitoring table refreshes
    - ✅ Student's exam disables (controls become inactive)
    - ✅ Action logged in footer

### Test Force Logout

1. Open monitoring dashboard
2. Locate an active student
3. Click "Logout" button
4. Confirm dangerous action warning
5. System shows processing
6. Verify:
    - ✅ Success notification appears
    - ✅ Session marked as locked
    - ✅ Student's page shows "Session ended" message
    - ✅ Student cannot continue exam
    - ✅ Action logged in footer

---

## Database Verification After Logout

After force submit/logout, verify in database:

```sql
-- Check exam_attempts table
SELECT
    id,
    student_id,
    force_submitted,
    force_submit_reason,
    force_submitted_at,
    is_session_locked
FROM exam_attempts
WHERE id = <attempt_id>;

-- Check action_logs table
SELECT
    id,
    action_type,
    description,
    admin_id,
    student_id,
    created_at
FROM action_logs
WHERE exam_id = <exam_id>
ORDER BY created_at DESC
LIMIT 10;

-- Check exam_sessions table
SELECT
    id,
    session_id,
    status,
    ended_at,
    violation_count
FROM exam_sessions
WHERE exam_id = <exam_id>
ORDER BY last_heartbeat DESC;
```

---

## What the Backend Does (Already Implemented)

### `forceSubmit()` - Line 82-111

```php
public function forceSubmit(Request $request, ExamAttempt $attempt)
{
    // 1. Authorize user is admin
    // 2. Validate input (reason required)
    // 3. Update exam attempt:
    //    - force_submitted = true
    //    - force_submit_reason = $request->reason
    //    - force_submitted_at = now()
    // 4. End the session
    // 5. Log action to ActionLog
    // 6. Return JSON success response
}
```

### `forceLogout()` - Line 113-142

```php
public function forceLogout(Request $request, ExamAttempt $attempt)
{
    // 1. Authorize user is admin
    // 2. Validate input (reason required)
    // 3. Update exam attempt:
    //    - is_session_locked = true
    // 4. End the session
    // 5. Log action to ActionLog
    // 6. Return JSON success response
}
```

---

## Expected Response Format

### Success Response (200 OK)

```json
{
    "success": true,
    "message": "Ujian berhasil dihentikan",
    "action_logged": true,
    "timestamp": "2026-02-24T14:30:45.000Z"
}
```

### Error Response (400/500)

```json
{
    "success": false,
    "message": "Ujian sudah selesai",
    "error_code": "UNAVAILABLE_ACTION"
}
```

---

## Common Issues & Fixes

### Issue 1: "Unauthorized" Error (401)

**Cause**: Admin not authenticated or lacking permission  
**Fix**: Check `@authorize('view-monitoring', $exam)` in MonitoringController.index()

### Issue 2: "Not Found" Error (404)

**Cause**: Attempt ID doesn't exist  
**Fix**: Verify session ID is mapped to correct attempt ID (see mapping section above)

### Issue 3: Button Click Does Nothing

**Cause**: CSRF token missing or invalid  
**Fix**: Ensure `<meta name="csrf-token" content="{{ csrf_token() }}">` in header

### Issue 4: Success Response but Nothing Changes

**Cause**: Frontend not refreshing data after action  
**Fix**: Ensure `refreshData()` is called after success (already in code)

---

## Final Checklist

- [ ] Update `forceSubmit()` function with API call (Step 1)
- [ ] Update `forceLogout()` function with API call (Step 2)
- [ ] Verify session ID → attempt ID mapping
- [ ] Test with actual student exam in progress
- [ ] Verify database records created in action_logs
- [ ] Verify student sees "session locked" message
- [ ] Check monitoring dashboard refreshes correctly
- [ ] Test error handling (invalid attempt, etc.)

---

## Need Help?

Reference files:

- **Dashboard**: `resources/views/admin/monitoring/index.blade.php`
- **Controller**: `app/Http/Controllers/Admin/MonitoringController.php`
- **Routes**: `routes/web.php` (search for `/admin/monitor`)

The backend is 100% ready - just need to connect the frontend buttons! 🎯
