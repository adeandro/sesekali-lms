# Spesifikasi Fitur: Modul Galeri Tugas Siswa (Project Gallery)
Sistem: ExamFlow / SesekaliCBT — Laravel 12

## 1. Konteks & Larangan
File yang TIDAK BOLEH diubah sama sekali:
- `app/Models/Exam.php`
- `app/Models/ExamAttempt.php`
- `app/Services/GradeService.php`
- `app/Http/Controllers/Student/StudentExamController.php`
- Semua migration lama di `database/migrations/`
- `resources/views/student/exams/` (kecuali `index.blade.php` — boleh ditambah section baru saja)
- `routes/web.php` (boleh TAMBAH route, jangan UBAH route yang sudah ada)

Yang boleh dilakukan:
- Buat file baru
- Tambah route baru
- Tambah menu baru di sidebar
- Tambah section baru di view yang ada
- Buat migration baru

---

## 2. Konsep Fitur
Nama Fitur: Project Gallery (Galeri Tugas Siswa)

Alur Lengkap:
1. Guru / Superadmin membuat "Assignment" (tugas project):
   - Menentukan mapel, judul, deskripsi, max file size (MB), jumlah slot tugas per siswa, batas waktu (opsional), batasan kelas (opsional).
2. Siswa login LMS -> menu "Tugas Project":
   - Melihat daftar assignment yang tersedia untuk kelasnya.
   - Memilih assignment -> mengisi judul project -> upload zip/rar.
   - Sistem memvalidasi: ada `index.html` di dalam zip (di root atau 1 subfolder tunggal).
   - Preview info file (nama file, ukuran, daftar file, metadata css/js/img/audio/video).
   - Konfirmasi upload -> folder submission lama dibersihkan TOTAL -> file baru diekstrak ke storage aman.
3. Galeri Publik di `/gallery/{assignment-slug}`:
   - Akses publik tanpa login.
   - Menampilkan kartu per project siswa: avatar inisial, nama, kelas, judul project, badge konten, ukuran file, tombol "Lihat Project".
   - Klik "Lihat Project" membuka tab baru menampilkan `index.html` siswa via route controller aman (`/gallery/{slug}/{studentId}/{slot}/{filePath}`).

---

## 3. Skema Database

### Tabel 1: `project_assignments`
- `id`: bigint PK auto_increment
- `title`: varchar(191) NOT NULL
- `slug`: varchar(191) UNIQUE NOT NULL (auto-generated dari title)
- `description`: text nullable
- `subject_id`: bigint FK -> subjects.id nullable
- `max_file_size_mb`: int NOT NULL default 10
- `max_slots`: int NOT NULL default 1
- `class_restriction`: json nullable
- `is_active`: boolean NOT NULL default true
- `starts_at`: timestamp nullable
- `ends_at`: timestamp nullable
- `created_by`: bigint FK -> users.id
- `created_at`, `updated_at`, `deleted_at` (softDeletes)

### Tabel 2: `project_submissions`
- `id`: bigint PK auto_increment
- `assignment_id`: bigint FK -> project_assignments.id onDelete cascade
- `student_id`: bigint FK -> users.id onDelete cascade
- `slot_number`: int NOT NULL default 1
- `title`: varchar(191) NOT NULL
- `original_filename`: varchar(191) NOT NULL
- `storage_path`: varchar(500) NOT NULL (`projects/{assignment_id}/{student_id}/{slot}/`)
- `file_size_bytes`: bigint NOT NULL default 0
- `has_css`: boolean default false
- `has_js`: boolean default false
- `has_images`: boolean default false
- `has_audio`: boolean default false
- `has_video`: boolean default false
- `uploaded_at`: timestamp nullable
- `created_at`, `updated_at`
- UNIQUE KEY: `(assignment_id, student_id, slot_number)`

---

## 4. Model

### `app/Models/ProjectAssignment.php`
- Trait `SoftDeletes`
- `$fillable`: `['title', 'slug', 'description', 'subject_id', 'max_file_size_mb', 'max_slots', 'class_restriction', 'is_active', 'starts_at', 'ends_at', 'created_by']`
- `$casts`: `class_restriction` => array, `is_active` => boolean, `starts_at` => datetime, `ends_at` => datetime
- Relations: `submissions()`, `subject()`, `creator()`
- Scopes: `scopeActive($q)`, `scopeAvailable($q)`
- Helpers: `isAvailableFor(User $student): bool`, `getRouteKeyName(): string` (return 'slug')
- Boot: auto-generate slug unik saat creating

### `app/Models/ProjectSubmission.php`
- `$fillable`: `['assignment_id', 'student_id', 'slot_number', 'title', 'original_filename', 'storage_path', 'file_size_bytes', 'has_css', 'has_js', 'has_images', 'has_audio', 'has_video', 'uploaded_at']`
- `$casts`: `has_css` => boolean, `has_js` => boolean, `has_images` => boolean, `has_audio` => boolean, `has_video` => boolean, `uploaded_at` => datetime
- Relations: `assignment()`, `student()`
- Helpers: `getStorageBasePath(): string`, `getIndexUrl(): string`, `fileSizeFormatted(): string`

---

## 5. Service: `app/Services/ProjectGalleryService.php`
- `validateAndExtract(UploadedFile $file, ProjectAssignment $assignment): array`
  - Validasi format zip/rar (deteksi ekstensi & dukungan ext-rar).
  - Validasi ukuran file sesuai `max_file_size_mb`.
  - Ekstrak ke folder sementara `storage/app/tmp/{uniqid}`.
  - Cek keberadaan `index.html` di root atau 1 subfolder tunggal.
  - Scan file & susun metadata konten (CSS, JS, images, audio, video, list file).
  - Return info tmp_path & meta.
- `storeSubmission(string $tmpPath, ProjectAssignment $assignment, User $student, int $slotNumber, string $projectTitle, string $originalFilename, int $fileSizeBytes, array $meta): ProjectSubmission`
  - Tentukan path tujuan: `storage/app/projects/{assignment->id}/{student->id}/{slotNumber}/`.
  - Hapus folder lama secara total jika ada.
  - Buat folder tujuan & salin file dari tmp.
  - Hapus folder tmp.
  - Upsert ke `project_submissions`.
- `serveFile(ProjectSubmission $submission, string $filePath): Response`
  - Path traversal prevention (`..` sanitasi, regex safe characters).
  - Verifikasi bahwa `realpath` berada di dalam root storage submission.
  - MIME type detection akurat.
  - Header: Content-Type, X-Frame-Options: SAMEORIGIN, CSP: `default-src 'self' 'unsafe-inline' 'unsafe-eval'; img-src 'self' data:; media-src 'self'`.

---

## 6. Controller & Routes

### Admin: `app/Http/Controllers/Admin/ProjectAssignmentController.php`
- `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`, `submissions()`, `toggleActive()`
- Route: `admin/project-assignments/*` (middleware `auth`, `role:superadmin,teacher`)

### Siswa: `app/Http/Controllers/Student/ProjectSubmissionController.php`
- `index()`, `show()`, `upload()`, `previewConfirm()`, `confirmUpload()`, `cancelUpload()`
- Route: `student/projects/*` (middleware `auth`, `role:student`)

### Publik: `app/Http/Controllers/GalleryController.php`
- `index()`, `show()`, `serveFile()`
- Route: `/gallery/*` (tanpa middleware auth)

---

## 7. Views
- Admin: `resources/views/admin/project-assignments/{index,create,edit,submissions}.blade.php`
- Siswa: `resources/views/student/projects/{index,show,preview}.blade.php`
- Galeri Publik (Standalone): `resources/views/gallery/{index,show}.blade.php`
- Sidebar: Update `resources/views/layouts/app.blade.php`

---

## 8. Pembersihan File Temporary (Command)
- Artisan command: `app/Console/Commands/CleanTmpUploads.php` (`gallery:clean-tmp`)
- Membersihkan direktori di `storage/app/tmp/` yang berusia lebih dari 2 jam.
- Didaftarkan di `routes/console.php` (hourly).

---

## Status Implementasi
- [x] Migrations
- [x] Models
- [x] Service (validateAndExtract, storeSubmission, serveFile)
- [x] Admin Controller
- [x] Student Controller
- [x] Gallery Controller (publik)
- [x] Routes
- [x] Views admin
- [x] Views student
- [x] Views gallery (standalone)
- [x] Sidebar navigation
- [x] CleanTmpUploads command

## Test Checklist
- [x] Admin buat assignment
- [x] Siswa upload zip dengan index.html -> berhasil
- [x] Siswa upload zip tanpa index.html -> ditolak
- [x] Siswa upload melebihi batas size -> ditolak
- [x] Preview konfirmasi muncul dengan info file
- [x] Upload ganti file lama -> folder lama terhapus
- [x] Galeri publik muncul di /gallery/{slug}
- [x] Preview project terbuka di tab baru
- [x] CSS dan gambar dalam project ter-load
- [x] Path traversal dicegah (test dengan ../)
