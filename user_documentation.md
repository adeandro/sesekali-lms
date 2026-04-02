# 📦 Dokumentasi Produk: ExamFlow
### *Modern, Secure, and Gamified Learning Management System*

**ExamFlow** adalah solusi komprehensif sistem manajemen pembelajaran (LMS) dan ujian berbasis komputer (CBT) yang dirancang untuk institusi pendidikan modern. Berfokus pada keamanan ujian yang ketat, keterlibatan siswa melalui gamifikasi, dan efisiensi administrasi sekolah.

---

## 🚀 1. Ringkasan Sistem & Teknologi
Aplikasi ini dibangun menggunakan arsitektur monolitik yang kokoh untuk memastikan performa yang cepat dan pemeliharaan yang mudah.

- **Framework**: Laravel 12.x (Enterprise Stability)
- **Frontend Layer**: Blade Templating Engine + Tailwind CSS + Alpine.js
- **Database**: MySQL/MariaDB dengan sistem caching dinamis (Laravel Cache)
- **UI/UX**: Responsive Design dengan dukungan Tema (Light/Dark/Premium Gamification Themes)

---

## 🛡️ 2. Sistem Keamanan & Integritas Ujian
Keamanan adalah pilar utama **ExamFlow** untuk memastikan hasil ujian yang jujur dan valid.

- **Anti-Cheat Layer**: Deteksi perpindahan tab browser (`onblur` detection).
- **Strike System**: Sistem peringatan otomatis jika siswa keluar dari layar ujian. Pelanggaran berulang akan mengakibatkan *auto-submit*.
- **Token Dynamic Gating**: Ujian hanya bisa dimulai dengan token yang digenerate secara real-time oleh pengawas.
- **Session Locking**: Mencegah pengerjaan ujian dari dua perangkat berbeda secara bersamaan.
- **Heartbeat Monitor**: Pantauan status koneksi siswa (Online/Offline) secara real-time di dashboard pengawas.

---

## 📝 3. Modul CBT & Ujian
Manajemen ujian yang fleksibel dan informatif untuk berbagai skenario evaluasi.

| Fitur | Penjelasan |
|---|---|
| **Bank Soal** | Pembuatan soal Pilihan Ganda (PG) dan Esai dengan editor teks kaya (Rich Text). |
| **Import Massal** | Mendukung import ribuan soal sekaligus melalui template Excel/CSV yang mudah digunakan. |
| **Randomize Logic** | Pengacakan soal dan pengacakan opsi jawaban per siswa untuk mencegah kerja sama. |
| **Monitoring Live** | Dashboard interaktif bagi pengawas untuk melihat progres pengerjaan siswa detik-demi-detik. |
| **Penskalaan Nilai** | Bobot nilai PG dan Esai yang dapat dikonfigurasi per ujian. |
| **Kartu Ujian** | Pembuatan kartu peserta ujian otomatis dengan QR Code untuk validasi. |

---

## 🕹️ 4. Ekosistem Gamifikasi (Gamification)
Satu-satunya sistem CBT yang mengintegrasikan kompetensi dengan keceriaan game (G-LMS).

- **Battle Arena**: Kompetisi real-time antar siswa untuk menjawab soal dengan cepat dan akurat.
- **Health Points (HP)**: Siswa memiliki 'nyawa' yang berkurang jika jawaban salah atau melakukan pelanggaran tab switching.
- **League & Seasons**: Sistem peringkat berbasis musim dengan hadiah EXP dan Tema eksklusif.
- **Achievement Gallery**: Lencana (badges) digital bagi siswa yang mencapai prestasi tertentu.
- **Customizable Themes**: Siswa dapat mengaktifkan tema UI berbeda berdasarkan level atau hadiah yang dimenangkan.

---

## 📄 5. Administrasi Surat
Efisiensi administrasi melalui automasi pembuatan dokumen legal sekolah.

- **Smart Letter Templates**: Template surat dengan placeholder dinamis (Nama, NIS, Tanggal, dll).
- **Self-Service Portal**: Siswa atau Guru dapat mengajukan surat (SPPD, SKS-A) secara mandiri.
- **Global Letterhead**: Pengaturan Kop Surat satu pintu yang berlaku untuk seluruh dokumen dalam sistem.
- **Kupon Hadiah**: Automasi pembuatan kupon pengambilan hadiah fisik bagi pemenang Battle Arena.

---

## 🎓 6. Akademik & Pelaporan (Raport)
Pengelolaan data inti pendidikan dan hasil belajar siswa.

- **Manajemen Akademik**: Pengelolaan data Mata Pelajaran, Kelas, dan Guru yang terintegrasi (RBAC).
- **Hasil Belajar (Raport)**: Rekapitulasi nilai ujian yang dapat difilter berdasarkan kelas atau periode tertentu.
- **Grafik Perkembangan**: Visualisasi performa nilai siswa dari waktu ke waktu untuk evaluasi jangka panjang.
- **Status Ketuntasan**: Perhitungan otomatis status "Tuntas" atau "Remedial" berdasarkan KKM mapel.

---

## 🏀 7. Ekstrakurikuler
Modul khusus untuk mencatat aktivitas di luar jam pelajaran inti.

- **Daftar Ekskul**: Manajemen jenis kegiatan ekstrakurikuler (Pramuka, OSIS, Olahraga, dll).
- **Sesi & Presensi**: Pencatatan kehadiran siswa dalam setiap sesi kegiatan ekstrakurikuler.
- **Pelatih Ekskul**: Penugasan guru atau pelatih khusus untuk mengelola kegiatan dan absensi.

---

## 📢 8. Komunikasi
Menghubungkan seluruh civitas akademika dalam satu platform.

- **Pengumuman (Announcements)**: Informasi publik atau internal yang dapat muncul di Dashboard maupun Halaman Login.
- **Pesan Internal**: Sistem pengiriman pesan antar pengguna (Admin ke Guru/Siswa) untuk koordinasi cepat.

---

## ⚙️ 9. Pengaturan & Branding
Fleksibilitas penuh untuk menyesuaikan aplikasi dengan identitas sekolah.

- **Identitas Sekolah**: Pengaturan Nama Sekolah, Alamat, Logo, dan Kontak yang muncul di seluruh sistem.
- **Tahun Akademik**: Manajemen periode aktif pembelajaran dan migrasi data tahunan.
- **App Branding**: Penentuan logo aplikasi dan favicon untuk pengalaman white-label yang elegan.

---

## 👥 10. Matriks Hak Akses (RBAC)
Sistem ini menggunakan *Role-Based Access Control* yang sangat spesifik:

| Role | Deskripsi Singkat |
|---|---|
| **Superadmin** | Kontrol penuh sistem, manajemen user, tahun akademik, dan backup data. |
| **Guru** | Mengelola bank soal, membuat ujian, memantau pengerjaan, dan bimbingan ekskul. |
| **Tata Usaha** | Fokus pada administrasi surat-menyurat dan validasi data siswa. |
| **Kepala Sekolah** | Hak akses "Read-Only" untuk memantau performa akademik dan statistik sekolah. |
| **Siswa** | Mengakses ujian, pengerjaan ekskul, dan berpartisipasi dalam gamifikasi. |

---

> [!TIP]
> **ExamFlow** terus berkembang. Kami merekomendasikan pembaruan sistem secara berkala untuk mendapatkan fitur-fitur baru dan patch keamanan terbaru.
 dan patch keamanan terbaru.
