# SPESIFIKASI SISTEM PILKETOS (PEMILIHAN KETUA OSIS)
## Tahap 1 — Inti Pemilihan (Revisi Desain)
### Untuk ExamFlow / SesekaliCBT — Laravel 12

---

## BAGIAN 1 — KONTEKS & LARANGAN MUTLAK

### Larangan Mutlak — JANGAN UBAH sama sekali:
1. Model/Controller/Service/View modul CBT, Battle Arena, Typing Test, Project Gallery, Raport, Surat, Gamifikasi, GradeService, Exam, ExamAttempt, Migration lama.
2. Route yang sudah ada (hanya TAMBAH route baru).
3. `app/Models/User.php` (JANGAN menambah method/kolom di sini).
4. `bootstrap/app.php` (middleware dipasang dengan nama class langsung di route tanpa alias).
5. Konfigurasi di bilik publik: JANGAN gunakan `app('configs')` karena tidak tersedia di rute publik. Selalu gunakan `Setting::pluck('value', 'key')->toArray()`.
6. Hak akses admin: `EnsureElectionManager` hanya mengizinkan `role === 'superadmin'` (JANGAN pakai `isAdmin()`).

---

## BAGIAN 2 — ALUR DESAIN BILIK SUARA

Alur bilik suara publik (`/pilketos` tanpa parameter ID pada URL):
1. **Inisialisasi Bilik**: Panitia memasukkan **PIN BILIK** di komputer bilik (dilakukan sekali di awal sesi komputer, bukan per siswa).
2. **Pengecekan Fisik**: Panitia mengecek kertas kredensial siswa secara fisik (kertas memuat **NIS + TOKEN**).
3. **Login Pemilih**: Siswa mengetikkan **NIS + TOKEN** dalam satu form login di bilik.
4. **Lembar Suara**: Siswa melihat daftar pasangan calon (nomor, foto, nama ketua & wakil beserta kelas, visi, misi). Seluruh pemilih wajib menentukan pilihan paslon.
5. **Konfirmasi & Kirim**: Siswa memilih, mengonfirmasi pilihan pada modal, lalu suara tersimpan. Layar menampilkan ucapan terima kasih dan otomatis kembali ke halaman awal dalam 6 detik.
6. **Koleksi Kertas**: Kertas token diserahkan kembali kepada panitia untuk dihitung dalam rekonsiliasi akhir.

### Prinsip Keamanan & Desain Inti:
- **TIDAK ADA PIN PER PEMILIH**. Satu-satunya PIN adalah PIN bilik milik panitia. Kartu fisik pemilih HANYA memuat NIS dan TOKEN (format `ABCD-EFGH`).
- **Hanya 1 Periode Aktif**: Hanya SATU periode yang boleh berstatus `ready` atau `open` pada satu waktu di sistem.
- **Buka/Tutup Manual**: Buka dan tutup pemilihan dilakukan sepenuhnya secara manual oleh admin/panitia melalui tombol transisi. Tidak ada penutupan otomatis berbasis jam.
- **Kerahasiaan Total**: Tabel suara tidak memuat kolom identitas pemilih, tidak memiliki relasi balik ke siswa, dan tidak ada log per suara yang dicatat.

---

## BAGIAN 3 — KEAMANAN & PRIVASI

| Item | Kebijakan & Aturan Teknis |
|---|---|
| **Tabel `election_votes`** | PK berupa UUID v4 acak (`Str::uuid()`). Kolom `candidate_id` nullable (NULL = suara kosong). **TIDAK ADA** kolom siswa/pemilih. **TANPA** timestamps (`created_at`/`updated_at`). |
| **Tabel `election_voters`** | **TANPA** timestamps. Kolom `nis`, `student_name`, `class_name` adalah **snapshot** teks agar migrasi/hapus siswa di CBT tidak terblokir. `voted_at` dibulatkan ke jam. |
| **Token Kredensial** | 8 karakter dari `ABCDEFGHJKMNPQRSTUVWXYZ23456789` (menghindari 0, O, 1, I). Plaintext HANYA ada di memori saat render PDF kartu. Disimpan HANYA sebagai hash HMAC-SHA256 (`hash_hmac('sha256', $token, config('app.key'))`). |
| **Siklus Token** | Token baru di-generate saat cetak kartu kelas/voter. Hash baru di-commit ke DB **SETELAH** PDF berhasil dibuat. Cetak ulang otomatis menaikkan `token_version` dan menghanguskan token lama. |
| **PIN Bilik Suara** | Disimpan dengan `Hash::make()`, diverifikasi via `Hash::check()`. Pergantian PIN atau tombol "Cabut Sesi" menaikkan `booth_session_version` yang otomatis mengeluarkan seluruh komputer bilik. |
| **Sesi Pemilih di Bilik** | Dibatasi maksimal **3 menit**. Setelah 3 menit tanpa submit, sesi kadaluwarsa dan otomatis kembali ke halaman login pemilih. |
| **Anti Brute-Force** | PIN bilik: 10x gagal / 5 menit per IP. NIS/Token: 5x gagal / 5 menit per NIS + 60x gagal / 10 menit per IP. Pesan kesalahan selalu generik: *"NIS atau token tidak sesuai."* |
| **Privasi Audit Log** | **TIDAK PERNAH** mencatat log saat suara dikirim (tidak ada `vote.cast` atau `vote.submitted`). Audit log hanya mencatat aksi administratif (buat periode, ganti status, cetak kartu, ganti PIN). |
| **Cache & SEO** | Seluruh rute bilik suara mengirim header: `Cache-Control: no-store, no-cache, must-revalidate`, `Pragma: no-cache`, `Expires: 0`, dan `X-Robots-Tag: noindex`. |

---

## BAGIAN 4 — SKEMA DATABASE

### 1. `election_periods`
```sql
id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
title                   VARCHAR(191) NOT NULL
description             TEXT NULL
event_date              DATE NULL                      -- informasi saja
status                  ENUM('draft','ready','open','closed') DEFAULT 'draft'
eligible_class_ids      JSON NULL                      -- null = semua kelas
allow_blank_vote        BOOLEAN DEFAULT TRUE
booth_pin_hash          VARCHAR(255) NULL
booth_session_version   INT UNSIGNED DEFAULT 1
opened_at               TIMESTAMP NULL
closed_at               TIMESTAMP NULL
collected_cards_count   INT UNSIGNED NULL
created_by              BIGINT UNSIGNED NULL FK users.id NULL ON DELETE
created_at              TIMESTAMP NULL
updated_at              TIMESTAMP NULL
deleted_at              TIMESTAMP NULL (softDeletes)
```

### 2. `election_candidates`
```sql
id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
period_id               BIGINT UNSIGNED NOT NULL FK election_periods.id CASCADE ON DELETE
number                  TINYINT UNSIGNED NOT NULL
chairman_id             BIGINT UNSIGNED NULL FK users.id NULL ON DELETE
chairman_name           VARCHAR(191) NOT NULL
chairman_class          VARCHAR(100) NULL
vice_id                 BIGINT UNSIGNED NULL FK users.id NULL ON DELETE
vice_name               VARCHAR(191) NOT NULL
vice_class              VARCHAR(100) NULL
vision                  TEXT NULL
mission                 TEXT NULL
photo_path              VARCHAR(255) NULL
created_at              TIMESTAMP NULL
updated_at              TIMESTAMP NULL

UNIQUE (period_id, number)
UNIQUE (period_id, chairman_id)
UNIQUE (period_id, vice_id)
```

### 3. `election_voters` (TANPA timestamps)
```sql
id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
period_id               BIGINT UNSIGNED NOT NULL FK election_periods.id CASCADE ON DELETE
student_id              BIGINT UNSIGNED NULL FK users.id NULL ON DELETE
nis                     VARCHAR(30) NOT NULL
student_name            VARCHAR(191) NOT NULL
class_id                BIGINT UNSIGNED NULL           -- tanpa FK agar aman saat migrasi kelas
class_name              VARCHAR(100) NULL
token_hash              CHAR(64) NULL                  -- sha256 hex
token_version           INT UNSIGNED DEFAULT 0
token_printed_at        TIMESTAMP NULL
has_voted               BOOLEAN DEFAULT FALSE
voted_at                TIMESTAMP NULL                 -- dibulatkan ke awal jam (startOfHour)

UNIQUE (period_id, nis)
INDEX (period_id, class_id)
INDEX (period_id, has_voted)
```

### 4. `election_votes` (TANPA timestamps)
```sql
id                      CHAR(36) PRIMARY KEY           -- UUID v4 acak (Str::uuid())
period_id               BIGINT UNSIGNED NOT NULL FK election_periods.id CASCADE ON DELETE
candidate_id            BIGINT UNSIGNED NULL FK election_candidates.id CASCADE ON DELETE -- NULL = suara kosong

INDEX (period_id, candidate_id)
```

### 5. `election_audit_logs` (Hanya created_at)
```sql
id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
period_id               BIGINT UNSIGNED NULL FK election_periods.id NULL ON DELETE
action                  VARCHAR(50) NOT NULL
actor_id                BIGINT UNSIGNED NULL
meta                    JSON NULL
created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

---

## BAGIAN 5 — SERVICES & LOGIKA BISNIS (`ElectionService`)

1. **`generateVoters(ElectionPeriod $period)`**:
   - Menyalin snapshot data siswa aktif dari tabel `users` (scope `aktif()`, role `student`) yang berada di kelas eligible.
   - Diproses dalam batch/chunk 500 siswa.
   - Melewati siswa yang belum memiliki NIS (dicatat jumlahnya).
   - Nilai awal `token_hash` = NULL (tidak membebani CPU dengan hash sebelum dicetak).
2. **`issueTokens(Collection $voters)` & `commitTokens(array $issued)`**:
   - Plaintext token disimpan HANYA dalam memori.
   - Voter yang sudah `has_voted = true` tidak dapat diberi token baru.
   - Hash token di-commit ke database dalam satu DB transaction **SETELAH** output PDF berhasil digenerate. Jika pembuatan PDF gagal, token di DB tidak berubah.
3. **`printVoterCards(ElectionPeriod $period, Collection $voters)`**:
   - Merender PDF kartu pemilih (maksimal 1 kelas per file, 10 kartu per halaman, tabel 2x5, font DejaVu Sans).
   - Setiap kartu berisi: Nama Sekolah (`Setting::pluck`), Judul Periode, Nama Siswa, NIS, Kelas, Token (`ABCD-EFGH`), dan instruksi kerahasiaan.
4. **`verifyCredentials(ElectionPeriod $period, string $nis, string $rawToken, string $ip)`**:
   - Normalisasi token (uppercase, strip karakter non-alfanumerik).
   - Validasi rate limit (5x/5m per NIS, 60x/10m per IP).
   - Menggunakan `hash_equals` untuk perbandingan konstan.
   - Jika kredensial sesuai tetapi pemilih sudah berstatus memilih (`has_voted = true`), melempar `ElectionException('Anda sudah menggunakan hak pilih.')`.
5. **`castVote(ElectionPeriod $period, int $voterId, ?int $candidateId)`**:
   - Menggunakan `DB::transaction()` dengan `lockForUpdate()` pada record pemilih dan periode.
   - Memastikan periode masih berstatus `open` dan voter belum pernah memilih.
   - Insert ke `election_votes` (UUID v4 acak) tanpa mencatat siapa pemilihnya.
   - Update voter: `has_voted = true`, `voted_at = now()->startOfHour()`.
6. **`tally(ElectionPeriod $period)`**:
   - Dihitung langsung dari `COUNT(*)` tabel `election_votes` dikelompokkan per `candidate_id`.
   - **HANYA** dapat dijalankan jika periode berstatus `closed`. Jika belum closed, melempar `ElectionException`.
   - Menghitung suara kosong, total suara, pemenang, dan penanda seri (*tie*).
7. **`consistency(ElectionPeriod $period)`**:
   - Rekonsiliasi: Total Suara di kotak suara digital vs Total Pemilih berstatus `has_voted = true` vs Jumlah Kertas Fisik Terkumpul (`collected_cards_count`).
8. **`transition(ElectionPeriod $period, string $newStatus, bool $force = false)`**:
   - State machine: `draft` &harr; `ready` &rarr; `open` &harr; `closed`.
   - `draft` &rarr; `ready`: Syarat min. 1 paslon, PIN bilik sudah diatur, min. 1 voter, tidak ada periode lain `ready`/`open`.
   - `ready` &rarr; `open`: Jika ada voter yang belum dicetak kartunya (`token_hash IS NULL`), meminta konfirmasi force.

---

## BAGIAN 6 — RUTE APLIKASI

### Admin (`prefix: admin/pilketos`, middleware: `EnsureElectionManager` [superadmin])
- `GET    /periods` &rarr; `ElectionPeriodController@index`
- `POST   /periods` &rarr; `ElectionPeriodController@store`
- `GET    /periods/create` &rarr; `ElectionPeriodController@create`
- `GET    /periods/{period}` &rarr; `ElectionPeriodController@show`
- `PUT    /periods/{period}` &rarr; `ElectionPeriodController@update`
- `DELETE /periods/{period}` &rarr; `ElectionPeriodController@destroy`
- `GET    /periods/{period}/edit` &rarr; `ElectionPeriodController@edit`
- `POST   /periods/{period}/transition` &rarr; `ElectionPeriodController@transition`
- `POST   /periods/{period}/set-pin` &rarr; `ElectionPeriodController@setPin`
- `POST   /periods/{period}/revoke-sessions` &rarr; `ElectionPeriodController@revokeBoothSessions`
- `POST   /periods/{period}/generate-voters` &rarr; `ElectionPeriodController@generateVoters`
- `GET    /periods/{period}/results` &rarr; `ElectionPeriodController@results` (hanya `closed`)
- `POST   /periods/{period}/collected-cards` &rarr; `ElectionPeriodController@updateCollectedCards`
- `GET    /candidates/search-students` &rarr; `ElectionCandidateController@searchStudents`
- `POST   /periods/{period}/candidates` &rarr; `ElectionCandidateController@store`
- `GET    /periods/{period}/candidates/create` &rarr; `ElectionCandidateController@create`
- `PUT    /periods/{period}/candidates/{candidate}` &rarr; `ElectionCandidateController@update`
- `DELETE /periods/{period}/candidates/{candidate}` &rarr; `ElectionCandidateController@destroy`
- `GET    /periods/{period}/candidates/{candidate}/edit` &rarr; `ElectionCandidateController@edit`
- `GET    /periods/{period}/voters` &rarr; `ElectionVoterController@index`
- `POST   /periods/{period}/cards/class/{classId}` &rarr; `ElectionVoterController@printClass`
- `POST   /periods/{period}/cards/voter/{voter}` &rarr; `ElectionVoterController@printOne`
- `GET    /periods/{period}/voters/export` &rarr; `ElectionVoterController@exportExcel`
- `GET    /periods/{period}/results/export` &rarr; `ElectionResultController@exportExcel` (hanya `closed`)

### Bilik Suara Publik (`prefix: pilketos`, middleware: `ElectionNoCache`, `ElectionBoothAccess`)
- `GET    /` &rarr; `BoothController@index` (Form PIN bilik atau Form NIS+Token)
- `POST   /pin` &rarr; `BoothController@pinPost` (Buka kunci komputer bilik)
- `POST   /verify` &rarr; `BoothController@verify` (Verifikasi NIS + Token pemilih)
- `GET    /lembar` &rarr; `BoothController@lembar` (Tampilan kartu suara digital)
- `POST   /pilih` &rarr; `BoothController@pilih` (Kirim pilihan & rekam suara)
- `GET    /selesai` &rarr; `BoothController@selesai` (Terima kasih & auto-redirect 6s)
- `GET    /closed` &rarr; `BoothController@closed` (Halaman jika bilik belum/tidak aktif)

---

## BAGIAN 7 — CHECKLIST IMPLEMENTASI & HASIL PENGUJIAN

- [x] Skema database 5 tabel direvisi dan dimigrasikan ulang secara bersih.
- [x] Model `ElectionPeriod`, `ElectionCandidate`, `ElectionVoter`, `ElectionVote` disesuaikan.
- [x] `ElectionService` mengimplementasikan token in-memory, cetak kartu kelas/voter, verifikasi kredensial, rekonsiliasi, dan tally hanya saat closed.
- [x] Middleware `EnsureElectionManager` dibatasi eksklusif untuk superadmin (`role === 'superadmin'`).
- [x] Sidebar navigation hanya muncul untuk superadmin.
- [x] Bilik suara menggunakan URL publik tunggal `/pilketos` dengan sesi bilik berbasis PIN dan sesi voter 3 menit.
- [x] Paslon mendukung pasangan calon Ketua + Wakil Ketua dengan pencarian live siswa aktif.
- [x] Kartu PDF memuat NIS dan Token tanpa PIN pemilih (10 kartu per halaman A4, tabel 2x5).
- [x] Halaman hasil memblokir akses saat pemilihan belum ditutup, memuat rekonsiliasi suara, dan opsi ekspor Excel.
- [x] Seluruh file PHP lolos `php -l` dengan 0 syntax error.

---

## BAGIAN 8 — DEVIASI & ALASAN

| # | Hal | Ketentuan Revisi | Implementasi & Alasan |
|---|---|---|---|
| 1 | Rute Bilik Suara | Publik `/pilketos` | Menggunakan URL tunggal `/pilketos`, PIN bilik disimpan dalam sesi server dengan validasi `booth_session_version` untuk pencabutan serentak. |
| 2 | Format Token | `ABCD-EFGH` | Diinput dengan otomatis menambahkan tanda strip pemisah via Alpine.js di halaman bilik; normalisasi di server membuang karakter strip sebelum hashing HMAC-SHA256. |
| 3 | Akses Hasil | Hanya saat `closed` | Dicek di level controller `ElectionPeriodController@results` (`abort(403)` jika bukan closed), level service `ElectionService::tally()`, dan rute ekspor Excel `ElectionResultController@exportExcel`. |
| 4 | Kerahasiaan Suara | Tanpa log per suara | Dihapus seluruh event/log yang berpotensi melacak waktu persis pengiriman suara dengan identitas pemilih. `voted_at` pada pemilih dibulatkan ke awal jam (`startOfHour()`). |

---

## BAGIAN 9 — PUBLIKASI KANDIDAT & HASIL

### 1. Skema Database
Migrasi `2026_10_26_000006_add_publication_columns_to_election_periods_table.php` menambahkan kolom pada tabel `election_periods`:
- `candidates_published` (`boolean`, default `false`)
- `results_published` (`boolean`, default `false`)
- `results_published_at` (`timestamp`, `nullable`)

### 2. Aturan Layanan (`ElectionService`)
- **Publikasi Kandidat (`publishCandidates`)**:
  - Hanya diizinkan pada periode dengan status `ready`, `open`, atau `closed`, dan memiliki minimal 1 pasangan calon.
  - Ditolak jika periode berstatus `draft`.
  - Mencatat audit log: `candidates.publish` atau `candidates.unpublish`.
- **Publikasi Hasil (`publishResults`)**:
  - Mengaktifkan (ON) wajib memenuhi syarat: status periode `closed` DAN `collected_cards_count !== null`.
  - Jika rekonsiliasi selisih kartu tidak klop (`consistency()['match'] === false` atau selisih $\ne 0$), publikasi ditolak kecuali `$force = true`.
  - Menyimpan waktu publikasi pada `results_published_at`.
  - Menonaktifkan (OFF) dapat dilakukan kapan saja oleh superadmin ("Tarik Hasil").
  - Mencatat audit log: `results.publish` atau `results.unpublish`.
  - Mengosongkan cache hasil publik (`pilketos_public_results_{id}`).
- **Otomasi Transisi Status (`transition`)**:
  - Transisi ke status `draft` otomatis mencabut publikasi profil kandidat (`candidates_published = false`).
  - Transisi dari `closed` kembali ke `open` ("Buka Kembali") otomatis mencabut publikasi hasil (`results_published = false`, `results_published_at = null`) dan membersihkan cache.

### 3. Tampilan Admin Superadmin
Dikelola di `resources/views/admin/pilketos/periods/show.blade.php` pada panel "📢 Publikasi":
- **Publikasi Paslon**: Switch toggle ON/OFF, tombol "Salin URL" (`/pilketos/kandidat`), dan tombol "Buka Tampilan Proyektor" (`/pilketos/kandidat?tampilan=slide`).
- **Publikasi Hasil Suara**: Switch toggle ON/OFF, peringatan rekonsiliasi data jika ada selisih kartu/suara, checkbox konfirmasi force publish, tombol "Tarik Hasil", dan tombol "Salin URL" (`/pilketos/hasil`).

### 4. Rute & Tampilan Publik (Portal Informasi Tanpa Auth)
Rute publik di luar grup middleware bilik suara (`ElectionBoothAccess`), tanpa autentikasi LMS, tetap dilindungi header `ElectionNoCache`:
- `GET /pilketos/kandidat` (`election.candidates`):
  - Memilih periode terbaru yang berstatus `ready|open|closed` dengan `candidates_published = true`.
  - Jika belum ada publikasi: menampilkan `election.public.empty` dengan pesan netral *"Belum ada informasi yang dipublikasikan."*
  - **Tampilan Grid**: Daftar paslon berukuran seragam, nomor urut kontras tinggi, foto ketua & wakil berdampingan, nama & kelas, visi & misi berformat rapi. Tidak memuat tautan ke bilik suara dan tidak menampilkan angka suara.
  - **Tampilan Proyektor (`?tampilan=slide`)**: Mode tayang aula/layar proyektor 1 paslon per layar, transisi otomatis 20 detik, navigasi keyboard panah kiri/kanan, tombol spasi untuk jeda/lanjut, dot indikator paslon, tipografi besar terbaca dari jarak jauh.
- `GET /pilketos/hasil` (`election.results`):
  - Memilih periode terbaru dengan status wajib `closed` dan `results_published = true`. Jika status masih `open`, query secara otomatis tidak akan menampilkan data hasil pemilihan.
  - Hasil di-cache selama 10 menit menggunakan `Cache::remember('pilketos_public_results_{id}', 600, ...)` untuk mengantisipasi lonjakan akses bersamaan. Cache langsung di-flush ketika hasil ditarik atau periode dibuka kembali.
  - Menampilkan banner pemenang (atau banner hasil seri jika draw), perolehan suara tiap paslon, bar visual persentase (warna kuning/amber menonjol untuk suara terbanyak), dan statistik DPT serta tingkat partisipasi. Tidak melakukan auto-refresh.

