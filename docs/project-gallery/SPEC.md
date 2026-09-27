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
   - Menentukan tipe assignment (`web`, `document`, `image`, `general`), mapel, judul, deskripsi, max file size (MB), jumlah slot tugas per siswa, batas waktu (opsional), batasan kelas (opsional), serta ekstensi khusus (untuk tipe general).
2. Siswa login LMS -> menu "Tugas Project":
   - Melihat daftar assignment yang tersedia untuk kelasnya.
   - Memilih assignment -> mengisi judul project -> upload file.
   - Untuk tipe `web`: alur preview konfirmasi (wajib ada `index.html` di dalam zip).
   - Untuk tipe `document`, `image`, `general`: langsung validasi dan simpan tanpa alur preview.
3. Galeri Publik di `/gallery/{assignment-slug}`:
   - Akses publik tanpa login.
   - Menampilkan kartu per karya siswa: avatar inisial, nama, kelas, judul karya, badge tipe / konten, ukuran file, tombol aksi interaktif (Lihat Project / Lihat Dokumen / Lihat Gambar / Download).

---

## 3. Skema Database

### Tabel 1: `project_assignments`
- `id`: bigint PK auto_increment
- `title`: varchar(191) NOT NULL
- `slug`: varchar(191) UNIQUE NOT NULL (auto-generated dari title)
- `assignment_type`: enum('web', 'document', 'image', 'general') default 'web'
- `description`: text nullable
- `subject_id`: bigint FK -> subjects.id nullable
- `max_file_size_mb`: int NOT NULL default 10
- `allowed_extensions`: json nullable
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

## 4. Model & Enum

### `app/Enums/AssignmentType.php`
- Cases: `Web = 'web'`, `Document = 'document'`, `Image = 'image'`, `General = 'general'`
- Methods: `label()`, `icon()`, `defaultMaxMb()`, `allowedMimes()`, `allowedExtensions()`, `canPreview()`, `hasPublicGallery()`, `badgeColor()`

### `app/Models/ProjectAssignment.php`
- Trait `SoftDeletes`
- `$fillable`: `['title', 'slug', 'assignment_type', 'description', 'subject_id', 'max_file_size_mb', 'allowed_extensions', 'max_slots', 'class_restriction', 'is_active', 'starts_at', 'ends_at', 'created_by']`
- `$casts`: `assignment_type` => AssignmentType::class, `allowed_extensions` => array, `class_restriction` => array, `is_active` => boolean, `starts_at` => datetime, `ends_at` => datetime
- Relations: `submissions()`, `subject()`, `creator()`
- Helpers: `getEffectiveExtensions(): array`, `getAcceptAttribute(): string`, `isAvailableFor(User $student): bool`, `getRouteKeyName(): string`

### `app/Models/ProjectSubmission.php`
- `$fillable`: `['assignment_id', 'student_id', 'slot_number', 'title', 'original_filename', 'storage_path', 'file_size_bytes', 'has_css', 'has_js', 'has_images', 'has_audio', 'has_video', 'uploaded_at']`
- `$casts`: `has_css` => boolean, `has_js` => boolean, `has_images` => boolean, `has_audio` => boolean, `has_video` => boolean, `uploaded_at` => datetime
- Relations: `assignment()`, `student()`
- Helpers: `getStorageBasePath(): string`, `getIndexUrl(): string`, `fileSizeFormatted(): string`

---

## 5. Service: `app/Services/ProjectGalleryService.php`
- `validateAndStore(UploadedFile $file, ProjectAssignment $assignment, User $student, int $slotNumber, string $projectTitle): ProjectSubmission`
  - Entry point terpadu untuk validasi ukuran, format ekstensi, dan penyimpanan file per tipe.
- `validateAndExtractWeb(UploadedFile $file, ProjectAssignment $assignment): array`
  - Khusus tipe web: validasi format zip/rar, ekstraksi ke folder temporary `storage/app/tmp/{uniqid}`, validasi `index.html`, scan metadata.
- `storeSubmission(...)`
  - Penyimpanan permanen direktori proyek web dari folder temporary ke `storage/app/projects/...`.
- `serveFile(ProjectSubmission $submission, string $filePath): Response`
  - Path traversal security, MIME detection, header Content-Disposition (inline untuk PDF/Image/Web, attachment untuk docx/pptx/general).
- `getMainFilename(ProjectSubmission $submission): string`
  - Mengembalikan `index.html` untuk tipe web atau nama file fisik yang ada (`file.{ext}`).

---

## 6. Upgrade: Multi-Type Assignment

Dokumentasi spesifikasi upgrade dukungan 4 tipe assignment:
1. **Web Project (`web`)**: Wajib file archive (.ZIP/.RAR) berisi `index.html`. Alur melalui pratinjau konfirmasi sebelum dipublikasikan.
2. **Dokumen (`document`)**: Mendukung PDF, DOCX, DOC, PPTX, PPT, XLSX, XLS. Upload langsung disimpan. PDF dapat di-preview secara inline, dokumen office di-download.
3. **Gambar (`image`)**: Mendukung JPG, JPEG, PNG, GIF, SVG, WEBP, PSD. Upload langsung disimpan, thumbnail tampil di kartu galeri, klik untuk melihat gambar resolusi penuh.
4. **File Umum (`general`)**: Ekstensi ditentukan secara dinamis oleh guru / admin atau dibebaskan jika dikosongkan.

### Deviasi & Alasan
- Penanganan pra-validasi upload PHP (`post_max_size`, `upload_max_filesize`, progress bar AJAX) tetap dipertahankan pada controller agar pengalaman pengguna responsif dan pesan error bahasa Indonesia tetap informatif.
- `getMainFilename()` mendeteksi file aktual di storage (`file.*`) sehingga fleksibel terhadap ekstensi apapun yang diunggah siswa pada tipe single file.
