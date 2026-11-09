# Spesifikasi: Unggah Foto Siswa (ZIP) & Pengaman Hapus Semua Siswa

Sistem: ExamFlow / SesekaliCBT (Laravel 12)  
Status: Aktif di Production  

---

## 1. Aturan & Batasan
- **HANYA UBAH**:
  - `app/Http/Controllers/Admin/StudentController.php` (`uploadPhotos`, `deleteAllStudents`, helper terkait, dan method konfirmasi baru)
  - `resources/views/admin/students/upload-photos.blade.php`
  - Tombol hapus massal siswa (e.g. di `resources/views/admin/students/index.blade.php`)
  - View konfirmasi baru: `resources/views/admin/students/delete-all-confirm.blade.php`
  - Penambahan rute GET baru untuk konfirmasi di `routes/web.php`
- **JANGAN UBAH**:
  - `app/Models/User.php` (termasuk model hook penghapus foto)
  - `app/Imports/StudentImport.php`
  - Migration yang sudah ada (tidak ada migration baru)
  - Artisan command (tidak ada command baru)
  - Modul CBT, Raport, Pilketos, Galeri, Typing Test
  - `bootstrap/app.php`
- Semua antarmuka berbahasa Indonesia.

---

## 2. Bagian A: Unggah Foto Siswa (ZIP)

### A1. Helper `normalizeName(string $s): string` (private di `StudentController`)
1. Ambil nama file tanpa ekstensi (`pathinfo(..., PATHINFO_FILENAME)`).
2. Samakan apostrof: `’`, `‘`, ````, `´` menjadi `'`.
3. Samakan spasi: NBSP (`\xc2\xa0`) dan unicode whitespace menjadi spasi biasa.
4. Trim & gabungkan spasi berulang: `preg_replace('/\s+/u', ' ', $s)`.
5. Ubah ke huruf kecil: `mb_strtolower($s, 'UTF-8')`.
6. Jangan hapus tanda baca lain, jangan transliterasi ke ASCII.

### A2. Peta Pencarian (In-Memory Map)
Dibangun sekali di awal sebelum iterasi file ZIP:
- Ambil siswa aktif (`role = 'student'`, `status = 'Aktif'`): `id`, `name`, `nis`, `photo`.
- `byName = $students->groupBy(fn($u) => $this->normalizeName($u->name));`
- `byNis = $students->filter(fn($u) => !empty($u->nis))->keyBy(fn($u) => trim((string)$u->nis));`

### A3. Filter & Pencocokan per Entri ZIP
- **Lewati tanpa error**:
  - Direktori/folder (`str_ends_with($filename, '/')`)
  - Entri Mac metadata: prefix `__MACOSX/` atau basename diawali `._`
  - Ekstensi selain `jpg`, `jpeg`, `png`, `webp` (case-insensitive)
  - Batas maksimal: 500 file gambar per ZIP (sisanya dilaporkan `dilewati (melebihi batas)`).
- **Ukuran file**:
  - Entri > 8 MB (via `$zip->statIndex($i)['size']`) dilewati dengan status gagal/dilewati.
- **Pencocokan**:
  - `key = normalizeName($filename)`
  - Jika `ctype_digit($key)` dan ada di `byNis`: cocok via NIS.
  - Selain itu, cari di `byName`:
    - Tepat 1 siswa: **cocok**.
    - 0 siswa: catat **"tidak ditemukan"** (jangan simpan file).
    - > 1 siswa: catat **"ambigu"** (jangan menebak, laporkan daftar kandidat).
- **Memory Guard (Hosting 128MB)**:
  - Sebelum `imagecreatefromstring()`, periksa dimensi dengan `getimagesizefromstring($content)`.
  - Jika lebar * tinggi > 20.000.000 piksel atau bukan gambar valid: catat **"gagal (gambar terlalu besar / tidak valid)"** dan lanjut ke file berikutnya.

### A4. Penyimpanan & Sanitasi
- Pertahankan GD resize (maks 400px panjang/lebar, preserve aspect ratio, PNG transparency, `imagedestroy`).
- Format nama simpanan:
  - Jika siswa memiliki NIS: `{nis}.{ext}` (ext: `jpg` untuk jpg/jpeg, `png`, `webp`).
  - Jika tanpa NIS: `u{id}.{ext}`.
- Sanitasi foto lama:
  - Jika `$siswa->photo` berisi nama file lain dan filenya ada di disk `public` (`profiles/{photo}`):
    - JANGAN hapus jika cocok pola terlindungi:
      - `/^\d{9,}_\d+\./` (foto unggahan mandiri siswa)
      - `/^(teacher|principal|tu|staff)_/i` (foto non-siswa)
    - JANGAN hapus jika nama file masih dipakai oleh user lain di database.
    - Hapus file lama setelah file baru berhasil ditulis.
- Update database:
  - `DB::table('users')->where('id', $siswa->id)->update(['photo' => $newFilename]);`
  - Tidak memicu Eloquent event. Jika simpan file gagal, kolom `photo` tidak diubah.

### A5. Pelaporan & UI
- Flash message `success` umum dan `photo_report` (array) untuk view:
  - `berhasil`: jumlah foto tersimpan & terhubung
  - `tidak_ditemukan`: array nama file yang tidak cocok dengan siswa manapun
  - `ambigu`: array nama file beserta info kandidat (nama, NIS, kelas)
  - `gagal`: array info kegagalan (nama file + alasan)
  - `dilewati`: array info file yang dilewati
- Teks petunjuk di view `upload-photos`:
  - "Nama file = nama lengkap siswa (huruf besar/kecil bebas) atau NIS, mis. AHMAD AKHIT MAULANA.jpg atau 2324100698.jpg"

---

## 3. Bagian B: Pengaman deleteAllStudents()

### B1. Alur & Rute Baru
- GET `admin/students/delete-all/confirm` (`admin.students.deleteAll.confirm`)
  - Middleware: sama dengan route delete all siswa (`role:superadmin`).
  - Ditempatkan sebelum resource route `admin/students`.
- Tombol lama di halaman index diubah menjadi link menuju halaman konfirmasi ini.

### B2. Isi Halaman Konfirmasi
- Jumlah siswa yang akan dihapus.
- Hitung data terkait yang akan ikut terhapus akibat cascade:
  - Query FK ke tabel users dengan `DELETE_RULE = 'CASCADE'` dari `information_schema` (dibungkus `try/catch`).
  - Hitung baris terkait id siswa yang akan terhapus.
  - Tampilkan tabel "Nama Data / Tabel" dan "Jumlah Baris" (hanya yang > 0).
- Jumlah file foto yang akan terhapus (siswa dengan kolom `photo` terisi).
- Banner peringatan merah:
  - "Tindakan ini TIDAK DAPAT DIBATALKAN. Untuk memperbarui data siswa gunakan Import (dicocokkan NIS) atau Nonaktifkan siswa."
- Teks pengingat: "Sudah backup database dan folder foto hari ini?"
- Checkbox konfirmasi wajib: "Saya mengerti semua data di atas ikut terhapus permanen."
- Input teks konfirmasi: ketik persis `HAPUS SEMUA SISWA`.
- Tombol submit nonaktif (disabled) via JavaScript sampai checkbox dicentang dan input teks persis sesuai.

### B3. Server-side Validation di `deleteAllStudents()`
- Validasi input:
  - `confirm_text === 'HAPUS SEMUA SISWA'`
  - `understand === true / '1'`
  - Jika tidak cocok, redirect back dengan pesan error jelas, TANPA menghapus data.
- Cek Pemilihan Pilketos:
  - Jika ada `ElectionPeriod::where('status', 'open')->exists()`, tolak dengan pesan "Pemilihan sedang berlangsung".
- Cek Ujian Sedang Berlangsung:
  - Cek tabel `exam_sessions` atau `exams` yang aktif/sedang berlangsung.
- Eksekusi penghapusan per siswa (Eloquent delete untuk trigger hook User).
- Log peringatan aplikasi:
  - `Log::warning(...)` mencatat: ID superadmin pelaksana, jumlah siswa terhapus, waktu.
  - TIDAK mencatat nama atau NIS siswa.

---

## 4. Deviasi & Alasan
*(Catat di sini jika ada penyesuaian dari spesifikasi awal)*
1. **Pengecekan Ujian Berlangsung**:
   - Sistem memeriksa `ExamSession::where('status', 'active')` atau model sesi ujian yang aktif di database CBT. Jika tidak ada sesi ujian aktif, operasi diizinkan.
