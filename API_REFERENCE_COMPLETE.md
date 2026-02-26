# API Reference: Monitoring & Security Module

**Module Version**: 1.0  
**Implementation Date**: February 24, 2026  
**Status**: Production Ready ✅

---

## Quick Reference

### 13 New Endpoints Created

| Method | Endpoint                                         | Controller            | Purpose                     |
| ------ | ------------------------------------------------ | --------------------- | --------------------------- |
| POST   | `/admin/tokens/exams/{exam}/generate`            | TokenController       | Generate batch tokens       |
| GET    | `/admin/tokens/exams/{exam}/list`                | TokenController       | List tokens + stats         |
| DELETE | `/admin/tokens/{token}/revoke`                   | TokenController       | Revoke token                |
| POST   | `/student/exams/{exam}/validate-and-start`       | StudentExamController | Validate token + start exam |
| POST   | `/student/exams/{attempt}/heartbeat`             | HeartbeatController   | Send heartbeat              |
| GET    | `/student/exams/{attempt}/session-status`        | HeartbeatController   | Get session status          |
| POST   | `/student/exams/{attempt}/sync-offline`          | HeartbeatController   | Sync offline answers        |
| POST   | `/student/exams/{attempt}/disconnect`            | HeartbeatController   | End session                 |
| GET    | `/admin/monitor/exams/{exam}`                    | MonitoringController  | Dashboard view              |
| GET    | `/admin/monitor/exams/{exam}/live`               | MonitoringController  | Live AJAX data              |
| POST   | `/admin/monitor/attempts/{attempt}/force-submit` | MonitoringController  | Force submit                |
| POST   | `/admin/monitor/attempts/{attempt}/force-logout` | MonitoringController  | Force logout                |
| GET    | `/admin/monitor/exams/{exam}/logs`               | MonitoringController  | Action logs                 |

---

## Detailed Endpoint Documentation

### 1. Generate Tokens

**Route**: `POST /admin/tokens/exams/{exam}/generate`  
**Auth**: Admin only  
**Authorization Policy**: create-tokens

#### Request

```json
{
    "quantity": 10,
    "validity_hours": 24
}
```

| Field          | Type | Min | Max | Required | Example |
| -------------- | ---- | --- | --- | -------- | ------- |
| quantity       | int  | 1   | 100 | Yes      | 10      |
| validity_hours | int  | 1   | 72  | Yes      | 24      |

#### Response (200 OK)

```json
{
    "success": true,
    "message": "Token berhasil dibuat",
    "tokens": [
        {
            "id": 1,
            "token": "ABCD-1234",
            "expires_at": "2026-02-25T14:30:00Z",
            "is_active": true,
            "used_at": null
        }
    ],
    "count": 10
}
```

#### Errors

- **400 Bad Request**: Invalid quantity or validity_hours
- **401 Unauthorized**: Not authenticated
- **403 Forbidden**: Not an admin

#### Example Usage (JavaScript)

```javascript
const response = await fetch("/admin/tokens/exams/1/generate", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
    },
    body: JSON.stringify({
        quantity: 10,
        validity_hours: 24,
    }),
});
const data = await response.json();
console.log("Generated tokens:", data.tokens);
```

---

### 2. List Tokens

**Route**: `GET /admin/tokens/exams/{exam}/list`  
**Auth**: Admin only  
**Authorization Policy**: view-tokens

#### Request

- No body required
- Optional query params: `?page=1&per_page=15`

#### Response (200 OK)

```json
{
    "success": true,
    "tokens": [
        {
            "id": 1,
            "token": "ABCD-1234",
            "expires_at": "2026-02-25T14:30:00Z",
            "is_active": true,
            "used_at": "2026-02-24T10:15:00Z",
            "used_by": {
                "id": 5,
                "name": "Budi Santoso"
            }
        }
    ],
    "stats": {
        "total": 50,
        "active": 35,
        "used": 10,
        "expired": 5
    },
    "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 50,
        "last_page": 4
    }
}
```

#### Example Usage

```javascript
const response = await fetch("/admin/tokens/exams/1/list");
const data = await response.json();
console.log("Token Status:", data.stats);
// { total: 50, active: 35, used: 10, expired: 5 }
```

---

### 3. Revoke Token

**Route**: `DELETE /admin/tokens/{token}/revoke`  
**Auth**: Admin only  
**Authorization Policy**: delete-tokens

#### Request

- No body required

#### Response (200 OK)

```json
{
    "success": true,
    "message": "Token berhasil dicabut",
    "token_id": 1
}
```

#### Errors

- **404 Not Found**: Token doesn't exist
- **400 Bad Request**: Token already used or expired

#### Example Usage

```javascript
await fetch("/admin/tokens/1/revoke", { method: "DELETE" });
```

---

### 4. Validate & Start Exam

**Route**: `POST /student/exams/{exam}/validate-and-start`  
**Auth**: Student (must own exam)  
**Rate Limit**: 5 per minute

#### Request

```json
{
    "token": "ABCD-1234"
}
```

#### Response (200 OK)

```json
{
    "success": true,
    "attempt_id": 42,
    "redirect_url": "/student/exams/42",
    "message": "Token valid - exam started"
}
```

#### Errors

- **400 Bad Request**: Token missing or empty
- **401 Unauthorized**: Student not authenticated
- **403 Forbidden**: Token not found / used / expired
- **404 Not Found**: Exam doesn't exist

#### Error Response

```json
{
    "success": false,
    "error": "token_not_found|token_used|token_expired|token_invalid",
    "message": "Kode token tidak valid atau sudah digunakan"
}
```

#### Example Usage

```javascript
const response = await fetch("/student/exams/1/validate-and-start", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
    },
    body: JSON.stringify({ token: "ABCD-1234" }),
});

const data = await response.json();
if (data.success) {
    window.location.href = data.redirect_url;
} else {
    alert("Token tidak valid: " + data.message);
}
```

---

### 5. Send Heartbeat

**Route**: `POST /student/exams/{attempt}/heartbeat`  
**Auth**: Student (must own attempt)  
**Frequency**: Every 20 seconds (client-side)

#### Request

```json
{
    "current_question": 5,
    "violation_count": 0,
    "session_id": "sess_abc123xyz"
}
```

| Field            | Type   | Required | Range | Notes                   |
| ---------------- | ------ | -------- | ----- | ----------------------- |
| current_question | int    | Yes      | 1-N   | Current question number |
| violation_count  | int    | Yes      | 0-3+  | Number of violations    |
| session_id       | string | Yes      | -     | Session identifier      |

#### Response (200 OK)

```json
{
    "success": true,
    "session_id": "sess_abc123xyz",
    "session_status": "active",
    "progress": 45.5,
    "timestamp": "2026-02-24T14:30:45.000Z"
}
```

#### Session Status Values

- `"active"` - Normal, continue exam
- `"locked"` - Admin force-logged out, stop exam
- `"ended"` - Admin force-submitted, stop exam
- `"paused"` - Exam paused by system

#### Errors

- **401 Unauthorized**: Not authenticated
- **403 Forbidden**: Attempt doesn't belong to student
- **404 Not Found**: Attempt or session doesn't exist
- **422 Unprocessable Entity**: Invalid heartbeat data

#### Example Usage

```javascript
// Sent every 20 seconds automatically
setInterval(async () => {
    const response = await fetch("/student/exams/42/heartbeat", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
            current_question: getCurrentQuestion(),
            violation_count: getViolationCount(),
            session_id: getSessionId(),
        }),
    });

    const data = await response.json();
    if (data.session_status === "locked" || data.session_status === "ended") {
        handleSessionEnded(data.session_status);
    }
}, 20000);
```

---

### 6. Get Session Status

**Route**: `GET /student/exams/{attempt}/session-status`  
**Auth**: Student (must own attempt)

#### Request

- No body or params needed

#### Response (200 OK)

```json
{
    "success": true,
    "session": {
        "id": 1,
        "session_id": "sess_abc123xyz",
        "status": "active",
        "is_connected": true,
        "current_question": 5,
        "violation_count": 0,
        "progress": 45.5,
        "seconds_since_heartbeat": 8,
        "started_at": "2026-02-24T14:00:00Z",
        "last_heartbeat": "2026-02-24T14:30:45Z"
    }
}
```

#### Example Usage

```javascript
const response = await fetch("/student/exams/42/session-status");
const data = await response.json();
console.log(`Connected: ${data.session.is_connected}`);
console.log(`Progress: ${data.session.progress}%`);
```

---

### 7. Sync Offline Answers

**Route**: `POST /student/exams/{attempt}/sync-offline`  
**Auth**: Student (must own attempt)  
**Called**: When connection restored (automatic)

#### Request

```json
{
    "1": { "selected_answer": "A" },
    "2": { "essay_answer": "Lorem ipsum dolor sit amet..." },
    "3": { "selected_answer": "C" },
    "4": { "essay_answer": "Another essay response..." }
}
```

#### Response (200 OK)

```json
{
    "success": true,
    "synced_count": 4,
    "message": "4 jawaban berhasil disinkronkan",
    "timestamp": "2026-02-24T14:30:45.000Z"
}
```

#### Errors

- **401 Unauthorized**: Not authenticated
- **400 Bad Request**: Invalid answer format
- **422 Unprocessable Entity**: Exam attempt invalid

#### Example Usage

```javascript
// Automatically called when online event fires
window.addEventListener("online", async () => {
    const cachedAnswers = JSON.parse(
        localStorage.getItem(`exam_attempt_42_answers`) || "{}",
    );

    const response = await fetch("/student/exams/42/sync-offline", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify(cachedAnswers),
    });

    if (response.ok) {
        localStorage.removeItem(`exam_attempt_42_answers`);
    }
});
```

---

### 8. Disconnect Session

**Route**: `POST /student/exams/{attempt}/disconnect`  
**Auth**: Student (must own attempt)  
**Called**: When student closes exam tab

#### Request

- No body required

#### Response (200 OK)

```json
{
    "success": true,
    "message": "Sesi berhasil ditutup",
    "session_id": "sess_abc123xyz"
}
```

#### Example Usage

```javascript
window.addEventListener("beforeunload", async () => {
    navigator.sendBeacon("/student/exams/42/disconnect", "");
});
```

---

### 9. Monitoring Dashboard View

**Route**: `GET /admin/monitor/exams/{exam}`  
**Auth**: Admin only  
**Authorization Policy**: view-monitoring

#### Response

Returns Blade template with:

- Stats cards (total, active, violations, disconnected)
- Real-time monitoring table
- Action logs footer
- JavaScript for AJAX polling

#### Example Usage

```html
<a href="/admin/monitor/exams/{{ $exam->id }}"> Buka Dashboard Monitoring </a>
```

---

### 10. Live Monitoring Data (AJAX)

**Route**: `GET /admin/monitor/exams/{exam}/live`  
**Auth**: Admin only  
**Frequency**: Every 5 seconds (client-side polling)

#### Request

- No body or params needed

#### Response (200 OK)

```json
{
    "success": true,
    "sessions": [
        {
            "id": 1,
            "student_name": "Ahmad Wijaya",
            "student_id": 5,
            "status": "active",
            "progress": 45.5,
            "current_question": 5,
            "violation_count": 0,
            "is_connected": true,
            "last_heartbeat": "2026-02-24T14:30:45Z",
            "seconds_since_heartbeat": 8,
            "session_id": "sess_abc123xyz"
        }
    ],
    "stats": {
        "total_students": 25,
        "active": 20,
        "violations": 2,
        "disconnected": 3
    }
}
```

#### Example Usage

```javascript
// Auto-polling every 5 seconds in monitoring dashboard
setInterval(async () => {
    const response = await fetch("/admin/monitor/exams/1/live");
    const data = await response.json();

    // Update table with data.sessions
    updateMonitoringTable(data.sessions, data.stats);
}, 5000);
```

---

### 11. Force Submit Exam

**Route**: `POST /admin/monitor/attempts/{attempt}/force-submit`  
**Auth**: Admin only  
**Authorization Policy**: force-submit

#### Request

```json
{
    "reason": "Ketahuan nyontek, meninggalkan ruangan"
}
```

| Field  | Type   | Required | Max Length |
| ------ | ------ | -------- | ---------- |
| reason | string | Yes      | 500        |

#### Response (200 OK)

```json
{
    "success": true,
    "message": "Ujian siswa berhasil dihentikan",
    "attempt_id": 42,
    "student_name": "Ahmad Wijaya",
    "action_logged": true,
    "timestamp": "2026-02-24T14:30:45.000Z"
}
```

#### Errors

- **400 Bad Request**: Reason missing or too long
- **401 Unauthorized**: Not admin
- **403 Forbidden**: Not authorized
- **404 Not Found**: Attempt doesn't exist
- **409 Conflict**: Exam already submitted

#### Example Usage

```javascript
const response = await fetch("/admin/monitor/attempts/42/force-submit", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
    },
    body: JSON.stringify({
        reason: "Ketahuan memberikan sinyal kepada siswa lain",
    }),
});

const data = await response.json();
if (data.success) {
    alert("Ujian " + data.student_name + " telah dihentikan");
    refreshMonitoringData();
}
```

---

### 12. Force Logout Session

**Route**: `POST /admin/monitor/attempts/{attempt}/force-logout`  
**Auth**: Admin only  
**Authorization Policy**: force-logout

#### Request

```json
{
    "reason": "Kejar curanmor, opak dari kamera"
}
```

#### Response (200 OK)

```json
{
    "success": true,
    "message": "Sesi siswa berhasil dikunci",
    "attempt_id": 42,
    "student_name": "Ahmad Wijaya",
    "is_session_locked": true,
    "action_logged": true,
    "timestamp": "2026-02-24T14:30:45.000Z"
}
```

#### Errors

- Same as force-submit (400, 401, 403, 404, 409)

#### Example Usage

```javascript
const response = await fetch("/admin/monitor/attempts/42/force-logout", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
    },
    body: JSON.stringify({
        reason: "Keluar kelas tanpa ijin",
    }),
});
```

---

### 13. Action Logs

**Route**: `GET /admin/monitor/exams/{exam}/logs`  
**Auth**: Admin only  
**Authorization Policy**: view-logs

#### Request

- Optional: `?page=1&per_page=50`

#### Response (200 OK)

```json
{
    "success": true,
    "logs": [
        {
            "id": 1,
            "action_type": "force_submit",
            "description": "Admin Ira Suryani menghentikan ujian siswa Ahmad Wijaya",
            "admin": {
                "id": 3,
                "name": "Ira Suryani"
            },
            "student": {
                "id": 5,
                "name": "Ahmad Wijaya"
            },
            "exam": {
                "id": 1,
                "title": "Matematika Dasar"
            },
            "metadata": {
                "reason": "Ketahuan mencontek"
            },
            "created_at": "2026-02-24T14:30:45.000Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total": 15,
        "per_page": 50
    }
}
```

#### Action Types

- `force_submit` - Admin force-ended exam
- `force_logout` - Admin force-disconnected session
- `session_locked` - Session locked (multi-login)
- `violation_detected` - Auto-detected violation
- `token_generated` - Admin created tokens
- `token_revoked` - Admin disabled token

---

## Headers Required

### All Requests

```
Content-Type: application/json
X-CSRF-TOKEN: <token from meta tag>
X-Requested-With: XMLHttpRequest (for AJAX)
```

### Authentication

- Students: Must be logged in + own the exam/attempt
- Admin: Must be logged in + have admin role

---

## Error Response Format

### Standard Error (4xx/5xx)

```json
{
    "success": false,
    "error": "error_code",
    "message": "Human-readable error message in Bahasa Indonesia",
    "status": 400
}
```

### Validation Error (422)

```json
{
    "success": false,
    "message": "Validator failed",
    "errors": {
        "quantity": ["Quantity harus antara 1-100"],
        "validity_hours": ["Validity harus antara 1-72"]
    }
}
```

---

## Rate Limiting

| Endpoint        | Limit      | Window       |
| --------------- | ---------- | ------------ |
| Validate token  | 5/minute   | Per student  |
| Heartbeat       | 500/minute | Per student  |
| Autosave        | Unlimited  | Per question |
| Dashboard AJAX  | Unlimited  | Per admin    |
| Generate tokens | 10/minute  | Per admin    |

---

## curl Examples

### Generate Tokens

```bash
curl -X POST http://localhost/admin/tokens/exams/1/generate \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: <token>" \
  -d '{"quantity": 10, "validity_hours": 24}'
```

### Validate Token

```bash
curl -X POST http://localhost/student/exams/1/validate-and-start \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: <token>" \
  -d '{"token": "ABCD-1234"}'
```

### Send Heartbeat

```bash
curl -X POST http://localhost/student/exams/42/heartbeat \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: <token>" \
  -d '{
    "current_question": 5,
    "violation_count": 0,
    "session_id": "sess_abc123xyz"
  }'
```

### Force Submit

```bash
curl -X POST http://localhost/admin/monitor/attempts/42/force-submit \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: <token>" \
  -d '{"reason": "Ketahuan mencontek"}'
```

---

## Integration Notes

1. **CSRF Protection**: All POST/DELETE requests require valid CSRF token
2. **Authentication**: All endpoints require authenticated user
3. **Authorization**: Controllers use custom policies for permission checks
4. **Timestamps**: All timestamps in ISO 8601 format (UTC)
5. **Errors**: Always check `response.ok` before using response data
6. **Retry Logic**: Client should retry on 5xx errors (max 3 times)

---

**End of API Reference**
