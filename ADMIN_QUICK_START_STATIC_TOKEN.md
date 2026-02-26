# 📚 Admin Quick Start - Static Token System

## 🎯 What Changed?

**Old Flow**: Admin generates 30 tokens for 30 students → Complex token management
**New Flow**: Admin generates 1 token → All 30 students use same token → Done!

## ⚡ 3-Step Admin Quick Start

### Step 1: Create/Publish Exam

```
1. Go: Sidebar → Manajemen Ujian → Daftar Ujian
2. Create new exam or edit existing
3. Set questions, time, duration
4. Publish exam
   ✅ Status = Published
```

### Step 2: Generate Token

```
1. On exam page, scroll to "Set Token" section
2. Click "⚡ Generate Token Baru"
   OR
   Enter custom token manually + Click "Set Token"
   ✅ See token like: A1B2-C3D4 or TEST-1234
```

### Step 3: Share Token with Students

```
1. Copy token from admin page
2. Send to students via:
   - WhatsApp: "Token ujian: A1B2-C3D4"
   - Email: "Gunakan token ini untuk ujian..."
   - Chat: Just paste the token
3. Done! All 30 students use the SAME token
```

## 📋 How Students Use Token

### Student's Perspective (No Change)

```
1. Login → Click "Mulai Ujian"
2. Enter token: "A1B2-C3D4"
3. Click "Start Exam"
4. Answer questions
5. Submit exam
```

### Student's Experience (Improved)

- ✅ No more "Token não válido" on page refresh
- ✅ Navigation between pages works seamlessly
- ✅ 120-minute session (2 hours of exam time)
- ✅ Even if page crashes, session persists

## 🔐 Key Differences

| What            | Before                             | Now                                              |
| --------------- | ---------------------------------- | ------------------------------------------------ |
| Tokens per exam | 30-50 (one per student)            | **1** (all students)                             |
| Token format    | ABCD-1234                          | ABCD-1234 (same)                                 |
| Change token    | Need care, affects active students | Anytime! (old token blocked, session unaffected) |
| Student sees    | Works/Error randomly               | Always works (session persistent)                |
| Admin workload  | Generate 30, distribute 30         | Generate 1, distribute 1                         |

## ⚙️ Common Admin Tasks

### Generate New Token

```
Exam page → "⚡ Generate Token Baru"
Result: Random token (e.g., X5K2-M9L7)
```

### Change Existing Token

```
Exam page → Clear field → Enter new token → "Set Token"
Result: New token active immediately
Action: Blocks NEW student entries, doesn't affect active sessions
```

### View Current Token

```
Exam page → "Set Token" section
Shows: Current token (if set) or "No token set"
```

## 🚀 Before Exam Starts

```
☑ Exam created & questions added
☑ Time/date set correctly
☑ Exam published
☑ Token generated & copied
☑ Token shared with students via WhatsApp/Email
☑ 10 minutes before start: Send reminder with token
```

## 🔍 During Exam

### Monitor Students (Optional)

```
Sidebar → Pengawasan → Pantau Ujian
Shows:
- Who's taking exam now
- How many answers done
- Time remaining
- Can force submit if needed
```

### Change Token (If Needed)

```
NO PROBLEM! You can change token anytime:
1. Click "Set Token"
2. Enter new token
3. Students ALREADY TAKING exam: Won't be affected (session-based)
4. NEW STUDENTS: Must use new token
```

## ✅ Verification Checklist

Before going live, verify:

```bash
# In bash/terminal:
cd /path/to/project

# Check 1: Token column exists
php artisan tinker
$exam = App\Models\Exam::first();
echo $exam->token; // Should be empty or existing token
exit;

# Check 2: Middleware is registered
grep -r "verify.exam.session" bootstrap/app.php routes/web.php
# Should see: 'verify.exam.session' and middleware applied

# Check 3: Routes exist
php artisan route:list | grep "generate-token"
php artisan route:list | grep "update-token"
# Should see both routes
```

## 🆘 Troubleshooting

### "Token not set" error

- Some exams might have NULL token from before update
- Click "⚡ Generate Token Baru" on exam page
- Or manually set token: Enter value + "Set Token"

### Student can't enter exam

1. Ask student: "What token did I send you?"
2. Check exam page: Current token is...?
3. If different: Get current token and resend to students
4. Student tries again with correct token

### Multiple students conflict

- OLD: Couldn't happen (each had own token)
- NEW: Can't happen either! Each student has own session
    - 30 students, 1 token, 30 separate sessions ✅

### Student finishes but exam shows "In Progress"

- Check: Session page → Submission tracking
- Admin can manually force-submit if needed
- Or wait 120 minutes for session to auto-expire

## 📞 Support Questions

**Q: Can I change token while students are taking exam?**
A: Yes! Already-taking students won't be affected (session-based). Only blocks new entries with old token.

**Q: What if 50 students use same token?**
A: Perfect! That's exactly what system is designed for. No conflicts, each has separate session.

**Q: How long is session valid?**
A: 120 minutes (2 hours). After that, student needs to re-enter token if exam still available.

**Q: Can I reuse token for multiple exams?**
A: Yes, but not recommended (confusing). Better to generate unique token per exam.

**Q: What if I forget to generate token?**
A: Students see error: "Token belum ditetapkan". Just generate token and inform students.

**Q: Old ExamToken table - still needed?**
A: No, but kept for audit trail. Can ignore or archive later.

---

**Happy exam proctoring! 🎓**
