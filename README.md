# SesekaliCBT - Modern Computer Based Test System

SesekaliCBT adalah platform ujian daring (CBT) yang modern, aman, dan mudah digunakan, dirancang khusus untuk kebutuhan sekolah dan institusi pendidikan. Sistem ini mengintegrasikan manajemen ujian, pengawasan real-time, dan pelaporan hasil yang mendalam.

## 🚀 Teknologi Utama
- **Framework**: Laravel 12.x (PHP 8.5)
- **Frontend**: Tailwind CSS & Blade Templating
- **Database**: MySQL / MariaDB
- **Keamanan**: Multi-layer focus detection (Anti-tab switching), Token-based gating, Real-time session monitoring.

## ✨ Fitur Unggulan
- **Manajemen Siswa & Guru**: Pengelolaan data pengguna dengan sistem RBAC (Role-Based Access Control) yang ketat.
- **Bank Soal & Import**: Mendukung pembuatan soal pilihan ganda dan esai, serta fitur import massal menggunakan CSV/Excel.
- **Real-time Monitoring**: Dashboard pengawasan untuk admin untuk memantau status siswa (online/offline/pelanggaran) secara langsung.
- **Sistem Token Dinamis**: Gating ujian menggunakan token yang dapat diperbarui secara otomatis atau manual.
- **Anti-Curant Flow**: Deteksi jendela melayang (floating window) dan split-screen dengan sistem sanksi otomatis.
- **Sistem Penilaian (KKM & Bobot)**: Penilaian dinamis dengan bobot PG/Esai per ujian dan ambang kelulusan (KKM) per mata pelajaran.
- **Reporting & Export**: Laporan hasil ujian yang mendalam (Tampilkan Status Tuntas/Remidial), dapat difilter (Rombel/Kelas) dan diekspor ke format Excel.

## 🛠️ Instalasi Cepat

```bash
# 1. Clone repository & install dependencies
composer install
npm install

# 2. Persiapan Environment
cp .env.example .env
php artisan key:generate

# 3. Database & Seeding
# Pastikan konfigurasi DB di .env sudah benar
php artisan migrate --seed

# 4. Jalankan Development Server
php artisan serve
npm run dev
```

**Kredensial Default:**
- **Superadmin**: `superadmin@localhost` | `password`
- **Guru/Admin**: `admin@localhost` | `password`
- **Siswa**: `student1@localhost` | `password`

## 📂 Struktur Folder Penting
- `app/Http/Controllers/Admin`: Logika manajemen backend (Ujian, Soal, Hasil, Token).
- `app/Http/Controllers/Student`: Logika antarmuka siswa dan pengerjaan ujian.
- `app/Models`: Definisi data dan relasi (User, Exam, ExamAttempt, Subject).
- `resources/views`: Template antarmuka (Layouts, Admin, Student, Dashboard).
- `routes/web.php`: Definisi rute aplikasi dengan proteksi middleware per peran.

## 🔄 Alur Kerja Utama

### Alur Admin
1. **Persiapan**: Buat Mata Pelajaran → Buat Ujian (Draft) → Kelola Soal (Manual/Import).
2. **Aktivasi**: Publikasikan Ujian → Generate Token di menu 'Kelola Token'.
3. **Pengawasan**: Buka 'Pantau Ujian' saat sesi dimulai. Gunakan 'Force Submit' jika diperlukan.
4. **Evaluasi**: Lihat hasil di menu 'Hasil' setelah ujian selesai, lalu ekspor ke Excel.

### Alur Siswa
1. **Login**: Masuk menggunakan NIS dan Kata Sandi.
2. **Persiapan**: Masuk ke menu 'Ujian Saya' atau klik 'Mulai' di Dashboard.
3. **Validasi**: Masukkan Token yang diberikan pengawas untuk mulai mengerjakan.
4. **Pengerjaan**: Jawab soal (Auto-save aktif). Selesaikan sebelum waktu habis.

---
Dikembangkan dengan ❤️ untuk pendidikan Indonesia yang lebih maju.
