# Student CSV Validation Checklist

Before importing, verify your CSV file meets all requirements.

## Column Requirements

- [ ] **nis** - Student National ID (must be unique)
- [ ] **name** or **full_name** - Student full name
- [ ] **grade** - Class/Year (10, 11, 12, etc.)
- [ ] **class_group** - Class group (A, B, C, etc.)

### Example Valid CSV:
```csv
nis,full_name,grade,class_group
2324100698,AHMAD AKHIT MAULANA,12,B
2324100755,YANDI FEBRIYAN,12,B
2425100810,MUHAMMAD ZIDAN,11,C
2526100856,FILQIYA NARVALISNA,10,A
```

---

## Data Validation Checklist

### ✓ NIS Field
- [ ] No empty values
- [ ] No duplicates within CSV
- [ ] Appears only ONCE per student
- [ ] Must be numeric or alphanumeric
- [ ] Example: `2324100698`

### ✓ Name Field  
- [ ] No empty values
- [ ] Contains student's full name
- [ ] No special characters (mainly letters and spaces)
- [ ] Example: `AHMAD AKHIT MAULANA`

### ✓ Grade Field
- [ ] No empty values  
- [ ] Valid values: `10`, `11`, `12` (or your school's grades)
- [ ] One value per student
- [ ] Example: `12`

### ✓ Class Group Field
- [ ] No empty values
- [ ] Valid values: `A`, `B`, `C`, `D`, etc.
- [ ] Must be single letter or designation
- [ ] Example: `B`

---

## How to Find Duplicates in Excel

### Method 1: Sort and Find Manually
1. Select all data
2. **Data → Sort** by NIS column
3. Scan visually for same NIS appearing twice
4. Delete duplicate rows

### Method 2: Use Conditional Formatting
1. Select NIS column
2. **Home → Conditional Formatting → Highlight Cell Rules → Duplicate Values**
3. Duplicates appear highlighted in red
4. Delete highlighted rows

### Method 3: Use Filter
1. Select NIS column
2. **Data → Filter**
3. Click filter dropdown
4. Sort A→Z and look for duplicates

---

## Common CSV Issues

### ❌ These Will Cause Errors:

```csv
# WRONG - Empty name field
nis,full_name,grade,class_group
2324100698,,12,B
```

```csv
# WRONG - Duplicate NIS
nis,full_name,grade,class_group
2324100698,AHMAD AKHIT MAULANA,12,B
2324100698,AHMAD AKHIT MAULANA,12,B
```

```csv
# WRONG - Missing grade
nis,full_name,grade,class_group
2324100698,AHMAD AKHIT MAULANA,,B
```

```csv
# WRONG - Invalid class_group
nis,full_name,grade,class_group
2324100698,AHMAD AKHIT MAULANA,12,
```

### ✓ These Are Correct:

```csv
# RIGHT - All fields complete, unique NIS
nis,full_name,grade,class_group
2324100698,AHMAD AKHIT MAULANA,12,B
2324100755,YANDI FEBRIYAN,12,B
2425100810,MUHAMMAD ZIDAN,11,C
```

---

## Quick Validation in Spreadsheet

### Step 1: Count Total Rows
- Should match: (# of unique students) + 1 header row
- Example: 200 students = 201 rows total

### Step 2: Count Unique NIS
- **Excel**: `=SUMPRODUCT(1/COUNTIF(A2:A1000,A2:A1000))`
- **Google Sheets**: Add formula `=COUNTA(UNIQUE(A2:A999))`
- Result should equal: (Total rows - 1)

### Step 3: Check for Empty Cells
- **Excel**: **Find & Replace → Find Empty cells**
- **Google Sheets**: Use filter to show blanks
- Should find: 0 empty cells

### Step 4: Verify Column Order
- Column 1: nis
- Column 2: full_name (or name)
- Column 3: grade
- Column 4: class_group

---

## File Format Requirements

### Save As
- [ ] CSV (Comma Separated Values)
- [ ] NOT Excel format (.xlsx)
- [ ] If using Excel: **File → Save As... → CSV UTF-8**

### Encoding
- [ ] UTF-8 encoding (default for most systems)
- [ ] NOT ANSI or Windows-1252

### Line Endings
- [ ] Unix/Mac format (if you get weird characters)
- [ ] Use text editor to verify

---

## Pre-Import Checklist

Before uploading to admin panel, verify:

- [ ] Opened CSV in text editor and checked format
- [ ] Counted rows (total count matches expectation)
- [ ] Verified no duplicate NIS values
- [ ] Checked no empty required fields
- [ ] Confirmed save format is CSV (not XLSX)
- [ ] Tested with small sample first (5 students)?
- [ ] Have backup copy of original file?

---

## If You See Import Errors

### Error: "Duplicate entry for NIS"
→ Your CSV has this NIS twice
→ Solution: Delete duplicate row from CSV

### Error: "Duplicate email"  
→ Email generated from NIS is duplicate
→ Solution: Check NIS uniqueness (see above)

### Error: "Lock wait timeout"
→ Database is slow (rare now with fix)
→ Solution: Try again in a few minutes

### Error: "Grade is required" (Row X)
→ Grade field is empty for that row
→ Solution: Add grade value to that row

### Error: "Class group is required" (Row X)
→ Class_group field is empty for that row
→ Solution: Add class group to that row

---

## Example: How to Clean Corrupted CSV

If your current CSV has duplicates like:

```csv
nis,full_name,grade,class_group
2324100698,AHMAD AKHIT MAULANA,12,B
2324100698,AHMAD AKHIT MAULANA,12,B   ← DELETE THIS (duplicate)
2324100698,AHMAD AKHIT MAULANA,12,B   ← DELETE THIS (duplicate)
2324100755,YANDI FEBRIYAN,12,B
2324100755,YANDI FEBRIYAN,12,B         ← DELETE THIS (duplicate)
```

**Clean it to:**

```csv
nis,full_name,grade,class_group
2324100698,AHMAD AKHIT MAULANA,12,B
2324100755,YANDI FEBRIYAN,12,B
```

Then upload the cleaned file.

---

## Support

If CSV validation fails:
1. Check against this checklist
2. Use duplicate finder to identify issues
3. Review example CSVs for correct format
4. Try with just 5 students first (to test format)

Once you have a clean CSV, the import should work smoothly!
