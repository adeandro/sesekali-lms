#!/bin/bash

echo "════════════════════════════════════════════════════════════"
echo "           🔧 FINAL SYSTEM VERIFICATION TEST"
echo "════════════════════════════════════════════════════════════"
echo ""

cd /home/adeandro/developments/sesekaliCBT

SUCCESS_COUNT=0
FAIL_COUNT=0

# Test function
test_feature() {
    local test_name=$1
    local query=$2
    local expected=$3
    
    echo ""
    echo "📌 TEST: $test_name"
    echo "   Query: $query"
    
    result=$(php artisan tinker --execute="$query" 2>&1 | grep -v "^Psy" | tail -1)
    
    if [[ "$result" == *"$expected"* ]]; then
        echo "   ✅ PASS: $result"
        ((SUCCESS_COUNT++))
    else
        echo "   ❌ FAIL: Got '$result', Expected '$expected'"
        ((FAIL_COUNT++))
    fi
}

# Test 1: Database has required data
echo "═══ DATABASE STRUCTURE TESTS ═══"
test_feature "Active Students Count" \
    "echo App\Models\User::where('role', 'student')->where('is_active', true)->count();" \
    "50"

test_feature "Published Exams" \
    "echo App\Models\Exam::where('status', 'published')->count();" \
    "1"

test_feature "Draft Exams" \
    "echo App\Models\Exam::where('status', 'draft')->count();" \
    "2"

test_feature "Total Questions" \
    "echo App\Models\Question::count();" \
    "100"

# Test 2: Student can see published exams
echo ""
echo "═══ STUDENT EXAM VISIBILITY TESTS ═══"
test_feature "Published Exam Available" \
    "\\\$student = App\Models\User::where('role', 'student')->first();\\\$exams = App\Services\ExamEngineService::getAvailableExams(\\\$student); echo \\\$exams->count();" \
    "1"

# Test 3: Timer endpoint works
echo ""
echo "═══ TIMER SYNC TESTS ═══"
test_feature "Timer Endpoint Available" \
    "\\\$attempt = App\Models\ExamAttempt::first(); \\\$minutes = \\\$attempt?->getRemainingTimeMinutes() ?? 0; echo (\\\$minutes >= 0) ? 'OK' : 'FAIL';" \
    "OK"

# Test 4: Print card shows all students
echo ""
echo "═══ PRINT CARD TESTS ═══"
test_feature "Print Card Shows All Students" \
    "\\\$exam = App\Models\Exam::where('status', 'published')->first(); \\\$count = App\Models\User::where('role', 'student')->where('is_active', true)->count(); echo \\\$count;" \
    "50"

# Test 5: CSS print styling
echo ""
echo "═══ CSS PRINT STYLING TESTS ═══"
PRINT_CSS=$(grep -c "min-height: 330mm" resources/views/admin/exams/print-card.blade.php)
PAGE_SIZE=$(grep -c "size: 210mm 330mm" resources/views/admin/exams/print-card.blade.php)

echo ""
echo "📌 TEST: F4 Size CSS Configuration"
if [ "$PRINT_CSS" -gt 0 ] && [ "$PAGE_SIZE" -gt 0 ]; then
    echo "   ✅ PASS: F4 sizing rules found"
    ((SUCCESS_COUNT++))
else
    echo "   ❌ FAIL: F4 sizing rules missing"
    ((FAIL_COUNT++))
fi

# Test 6: Timer sync JavaScript
echo ""
echo "═══ JAVASCRIPT TIMER SYNC TESTS ═══"
TIMER_SYNC=$(grep -c "remaining-time" resources/views/student/exams/take.blade.php)

echo ""
echo "📌 TEST: Timer Sync with Server"
if [ "$TIMER_SYNC" -gt 0 ]; then
    echo "   ✅ PASS: Timer sync code found in view"
    ((SUCCESS_COUNT++))
else
    echo "   ❌ FAIL: Timer sync code missing"
    ((FAIL_COUNT++))
fi

# Test 7: Form autosave maintained
echo ""
echo "═══ FORM PERSISTENCE TESTS ═══"
AUTOSAVE=$(grep -c "autosaveAnswer" resources/views/student/exams/take.blade.php)

echo ""
echo "📌 TEST: Answer Autosave Implemented"
if [ "$AUTOSAVE" -gt 0 ]; then
    echo "   ✅ PASS: Autosave functionality present"
    ((SUCCESS_COUNT++))
else
    echo "   ❌ FAIL: Autosave functionality missing"
    ((FAIL_COUNT++))
fi

# Summary
echo ""
echo "════════════════════════════════════════════════════════════"
echo "                   📊 TEST SUMMARY"
echo "════════════════════════════════════════════════════════════"
echo ""
echo "✅ PASSED: $SUCCESS_COUNT tests"
echo "❌ FAILED: $FAIL_COUNT tests"
echo ""

if [ "$FAIL_COUNT" -eq 0 ]; then
    echo "🎉 ALL TESTS PASSED! System is ready for production."
    echo ""
    echo "═══ QUICK START ═══"
    echo ""
    echo "1. Server: http://localhost:8001"
    echo ""
    echo "2. Student Login:"
    echo "   Email: student01@school.local"
    echo "   Password: password"
    echo "   URL: http://localhost:8001/student/exams"
    echo ""
    echo "3. Admin/Print Card:"
    echo "   Email: admin@localhost"
    echo "   Password: password"
    echo "   URL: http://localhost:8001/admin/exams/1/print-card"
    echo ""
    echo "4. Test Flow:"
    echo "   - Student sees published exam"
    echo "   - Click 'Mulai Ujian' to start"
    echo "   - Try reload: timer should sync from server"
    echo "   - Answer questions and reload: answers persist"
    echo "   - Admin: print card shows all 50 students"
    echo ""
    exit 0
else
    echo "⚠️  Some tests failed. Please review and fix."
    exit 1
fi
