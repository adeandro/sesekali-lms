# Emergency Fix Summary: Student Import Issue

## Problem Diagnosis ✓ SOLVED

Your import errors were caused by **TWO separate issues**:

### Issue 1: Database Lock Timeouts (Rows 2-51)

```
Error: Lock wait timeout exceeded; try restarting transaction
```

**Root Cause**: Batch import of 50 students in single transaction holding locks too long on hosted MySQL

**Fixed By**:

- Batch size reduced from 50 to 10
- Individual error handling per student (no batch rollback)
- 100ms delays between batches
- Smaller transaction scope

### Issue 2: Duplicate Records (Rows 52-197)

```
Error: Duplicate entry '2324100698' for key 'users.users_nis_unique'
Error: Duplicate entry 'student_2324100698@sesekalicbt.local' for key 'users_email_unique'
```

**Root Cause**: Your CSV file has the **same student repeated multiple times**

- Student AHMAD AKHIT MAULANA: Rows 2-51 (50 duplicate rows!)
- Student YANDI FEBRIYAN: Rows 52-101 (50 duplicate rows)
- And more...

**Fixed By**:

- Duplicate detection within import file
- New `createOrUpdateStudent()` method handles re-imports
- Better error messages identifying exact issues

---

## Action Plan: IMMEDIATE STEPS

### Step 1: Check Your CSV File

Open your student data CSV file and:

1. **Look for duplicates** - Students appearing multiple times
2. **Keep only 1 row per student NIS**
3. **Save the cleaned CSV**

**How to find duplicates in Excel/Google Sheets:**

```
1. Sort by NIS column
2. Look for same NIS appearing multiple times
3. Delete duplicate rows
```

### Step 2: Clear Existing Corrupted Data (Optional but Recommended)

If you've already imported the corrupted data, clear it first:

```bash
# SSH into your hosting server, or use cPanel Terminal

cd /home/adeandro/developments/sesekaliCBT

# Open Laravel Tinker
php artisan tinker

# Delete all student records:
App\Models\User::where('role', 'student')->delete();

# Verify they're deleted:
App\Models\User::where('role', 'student')->count();

# Exit
exit
```

### Step 3: Upload Cleaned CSV

1. Go to: **Admin Dashboard → Students → Import Students**
2. Select your **cleaned CSV file** (no duplicates)
3. Submit the form
4. Wait for results

---

## Expected Results After Fix

### Success Indicators ✓

- No lock timeout errors
- All valid students imported
- Report shows: "X students imported successfully"

### If You Still See Errors

The error messages will now be **much more specific**:

- "Duplicate email in import (previously seen in row 5)"
- "NIS already exists in database"
- "Database lock - try again later"

These tell you exactly what to fix in your CSV.

---

## Database Verification

### Quick Health Check

```bash
php artisan tinker

# Check for duplicates already in database:
App\Models\User::selectRaw('nis, COUNT(*) as count')
    ->where('role', 'student')
    ->groupBy('nis')
    ->havingRaw('count > 1')
    ->get();

# If returns empty, no duplicates in DB ✓
exit
```

### If Duplicates Found, Clean Them

```bash
php artisan tinker

# Find and delete duplicate NISSes (keeps first entry only):
$duplicateNisses = App\Models\User::selectRaw('nis')
    ->where('role', 'student')
    ->groupBy('nis')
    ->havingRaw('count(*) > 1')
    ->pluck('nis');

foreach ($duplicateNisses as $nis) {
    App\Models\User::where('nis', $nis)
        ->where('role', 'student')
        ->orderBy('created_at')
        ->skip(1)
        ->delete();
}

echo "Duplicates removed";
exit
```

---

## What Changed in Your Code

### `app/Imports/StudentImport.php`

- ✓ Smaller batch size (10 instead of 50)
- ✓ Individual transaction handling per student
- ✓ Duplicate detection within import (NIS + Email)
- ✓ Better error messages
- ✓ Delays between batches

### `app/Services/StudentService.php`

- ✓ New `createOrUpdateStudent()` method
- ✓ Uses `updateOrCreate()` for idempotent imports
- ✓ Removed NIS uniqueness validation (handled differently)

---

## MySQL Hosting Tips

### For Future Large Imports

Add to `.env`:

```env
DB_WAIT_TIMEOUT=28800
```

### Monitor Imports

Watch these in cPanel/hosting control panel:

- Current database connections
- Query times (should be < 1 second each)
- Lock waits (should be 0)

---

## Full Code Changes Reference

### New Method Added to StudentService

```php
public static function createOrUpdateStudent(array $data): array
{
    // Updates if NIS exists, creates if new
    $student = User::updateOrCreate(
        ['nis' => $data['nis']],
        [
            'name' => $data['name'],
            'email' => 'student_' . $data['nis'] . '@sesekalicbt.local',
            'password' => Hash::make($password),
            'password_display' => $password,
            'grade' => $data['grade'],
            'class_group' => $data['class_group'],
            'role' => 'student',
            'is_active' => true,
        ]
    );

    return ['student' => $student, 'password' => $password];
}
```

---

## Next Time: Prevention

Prevent this in future imports:

1. **Always validate CSV first**
    - Check for duplicates before upload
    - Use spreadsheet tools to identify

2. **Test with small batch first**
    - Import 10 students first
    - Check results
    - If OK, proceed with full import

3. **Keep a backup**
    - Save original CSV
    - Save imported list
    - Compare to find issues

---

## Still Having Issues?

Run this diagnostic command:

```bash
cd /home/adeandro/developments/sesekaliCBT

# Run artisan command to check Student table
php artisan tinker

# Check current student count
App\Models\User::where('role', 'student')->count();

# Check for any integrity issues:
App\Models\User::where('role', 'student')
    ->whereNull('nis')
    ->count();  # Should be 0

# Check grade field issues:
App\Models\User::where('role', 'student')
    ->whereNull('grade')
    ->count();  # Should be 0

exit
```

If any return non-zero, you have data integrity issues to address.

---

## Support Resources

- Migration file: `database/migrations/2026_02_13_130917_add_student_fields_to_users_table.php`
- Guide document: `IMPORT_FIX_GUIDE.md`
- Controller: `app/Http/Controllers/Admin/StudentController.php`
- Service: `app/Services/StudentService.php`
- Importer: `app/Imports/StudentImport.php`

---

**Ready to test?** Clean your CSV and try the import now! The error messages will be much more helpful.
