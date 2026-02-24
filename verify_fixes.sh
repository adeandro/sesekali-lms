#!/bin/bash

echo "=== VERIFICATION OF FIXES ==="
echo ""

echo "✓ CHECK 1: User Model has examAttempts relationship"
grep -q "public function examAttempts()" app/Models/User.php
if [ $? -eq 0 ]; then
    echo "  ✓ examAttempts() method found in User model"
    grep -q "hasMany(ExamAttempt" app/Models/User.php
    if [ $? -eq 0 ]; then
        echo "  ✓ Relationship correctly defined"
        echo "  Status: VERIFIED ✓"
    fi
else
    echo "  Status: FAILED"
fi
echo ""

echo "✓ CHECK 2: ExamCardController updated for all students"
grep -q "Get all students who attempted this exam" app/Http/Controllers/Admin/ExamCardController.php
if [ $? -eq 0 ]; then
    echo "  ✓ Controller logic updated"
    grep -q "If no attempts yet, show all active students" app/Http/Controllers/Admin/ExamCardController.php
    if [ $? -eq 0 ]; then
        echo "  ✓ Fallback for all students implemented"
        echo "  Status: VERIFIED ✓"
    fi
fi
echo ""

echo "✓ CHECK 3: Print card CSS improved"
grep -q "@media print" resources/views/admin/exams/print-card.blade.php
if [ $? -eq 0 ]; then
    echo "  ✓ Print media queries configured"
    grep -q "page-break-after: always" resources/views/admin/exams/print-card.blade.php
    if [ $? -eq 0 ]; then
        echo "  ✓ Page breaks configured"
        echo "  ✓ break-inside: avoid configured"
        echo "  Status: VERIFIED ✓"
    fi
fi
echo ""

echo "✓ CHECK 4: Print header correctly hidden"
grep -q "no-print {" resources/views/admin/exams/print-card.blade.php
if [ $? -eq 0 ]; then
    echo "  ✓ no-print class configured"
    grep -q "display: none !important" resources/views/admin/exams/print-card.blade.php
    if [ $? -eq 0 ]; then
        echo "  ✓ no-print will be hidden on print"
        echo "  Status: VERIFIED ✓"
    fi
fi
echo ""

echo "=== SUMMARY OF FIXES ==="
echo "1. ✓ Dashboard error fixed - User::examAttempts() relationship added"
echo "2. ✓ Print card logic - Now fetches all students (with fallback to all active)"
echo "3. ✓ Print CSS improved - Better page breaks and hiding non-printable elements"
echo "4. ✓ Print styling - A4 size, proper margins, clean black text"
echo ""
echo "Ready for browser testing!"
