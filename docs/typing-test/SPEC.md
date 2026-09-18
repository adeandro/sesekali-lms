# SPEC: Typing Test Feature — ExamFlow

**Tanggal:** 2026-09-18  
**Stack:** Laravel 12, Blade, Alpine.js, Tailwind CSS v4, MySQL

## Keputusan Teknis

### Database
- Tabel `typing_tests` — konfigurasi tes (judul, durasi, target WPM, bobot, jadwal, kelas)
- Tabel `typing_attempts` — hasil per-siswa (kata generated, kata diketik, statistik)
- Unique constraint `(typing_test_id, student_id)` — satu siswa satu attempt per tes
- SoftDeletes di `typing_tests` agar data attempt tetap utuh

### Word Source
- Hardcoded 250+ kata Bahasa Indonesia di `TypingTestService::$wordList`
- Tidak fetch dari API luar — sederhana dan cepat
- Kata digenerate saat siswa pertama buka tes, disimpan di DB (konsistensi saat refresh)

### Scoring Formula
- `accuracy     = (characters_correct / characters_total) * 100`
- `wpm          = words_correct / (duration_seconds / 60)`
- `speed_score  = min(wpm / target_wpm, 1.0) * 100`
- `final_score  = (accuracy * weight_accuracy/100) + (speed_score * weight_speed/100)`
- Default bobot: Akurasi 60%, Kecepatan 40%

### Authorization
- Admin routes: `role:superadmin,teacher`
- Student routes: `role:student`
- `class_restriction` validasi: `exists:classes,id` (tabel `classes`, model `ClassRoom`)

### Export
- `PhpOffice\PhpSpreadsheet` (via `maatwebsite/excel ^3.1` yang sudah terinstall)
- Format `.xlsx`, header bold, autosize kolom

### Frontend
- Alpine.js `typingArena()` component di `show.blade.php`
- Karakter per-span berwarna (hijau=benar, merah=salah, abu=pending)
- Submit via `fetch()` AJAX — tidak reload halaman saat mengetik
- Copy/paste/cut di-prevent agar fair

### Sidebar
- Menu "Tes Mengetik" di accordion "CBT & Ujian" untuk role teacher/superadmin
- Active state: `request()->routeIs('admin.typing-tests.*')`
- Siswa melihat typing test di `student.exams.index` (section tersendiri)

## File yang Dibuat/Dimodifikasi

| File | Tipe |
|------|------|
| `database/migrations/2026_09_18_000001_create_typing_tests_table.php` | Migration |
| `database/migrations/2026_09_18_000002_create_typing_attempts_table.php` | Migration |
| `app/Models/TypingTest.php` | Model |
| `app/Models/TypingAttempt.php` | Model |
| `app/Services/TypingTestService.php` | Service |
| `app/Http/Controllers/Admin/TypingTestController.php` | Controller |
| `app/Http/Controllers/Student/TypingTestController.php` | Controller |
| `routes/web.php` | Modified |
| `app/Providers/AppServiceProvider.php` | Modified |
| `resources/views/admin/typing-tests/index.blade.php` | View |
| `resources/views/admin/typing-tests/create.blade.php` | View |
| `resources/views/admin/typing-tests/edit.blade.php` | View |
| `resources/views/admin/typing-tests/results.blade.php` | View |
| `resources/views/student/typing-tests/show.blade.php` | View |
| `resources/views/student/typing-tests/result.blade.php` | View |
| `resources/views/student/exams/index.blade.php` | Modified |
| `resources/views/layouts/app.blade.php` | Modified |
