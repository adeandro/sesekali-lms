# ⚡ QUICK REFERENCE - Alur Ujian SesekaliCBT

## 🎯 TL;DR - 30 Detik

```
Admin:   Buat Ujian → Publish → Generate Token → Bagikan Token → Pantau
Siswa:   Login → Ujian Saya → Mulai → Input Token → Jawab → Submit
Admin:   Lihat Hasil & Review
```

---

## 📍 LOKASI MENU

### Admin Sidebar

```
🏠 BERANDA (Home)
│
├─ 📚 KELOLA UJIAN
│  ├─ Ujian Saya       ← List & CRUD Ujian (Status: Draft/Published)
│  ├─ Mata Pelajaran
│  ├─ Soal             ← Manage questions
│  ├─ Hasil            ← Score & analytics
│  └─ Siswa
│
├─ 🔒 PENGAWASAN & KEAMANAN  ← NEW!
│  ├─ Kelola Token     ← Generate token per ujian
│  └─ Pantau Ujian     ← Monitor siswa real-time (5sec refresh)
│
└─ 👤 ACCOUNT
   ├─ Settings
   └─ Logout
```

---

## 🚀 ADMIN - 5 LANGKAH MULAI UJIAN

### 1. BUAT UJIAN (Draft)

```
Menu: Ujian Saya → "+ Buat Ujian"
Isi:  Judul, Mata Pelajaran, Durasi, Jadwal, Tipe Soal
```

### 2. TAMBAH SOAL

```
Menu: Ujian Saya → Klik "📋" (icon soal)
atau: Ujian Saya → "Kelola Soal"
Cara: Tambah manual atau import CSV
```

### 3. PUBLIKASIKAN

```
Menu: Ujian Saya → Klik "✓" (icon publikasi)
Efek: Status Draft → Published
```

### 4. GENERATE TOKEN

```
Menu: 🔒 Pengawasan → "Kelola Token"
Step: Generate Token Baru → Pilih Ujian → Input Jumlah & Validitas
Hasil: Token siap pakai (Contoh: A1B2-C3D4)
```

### 5. BAGIKAN TOKEN

```
Copy: Token dari halaman "Kelola Token"
Kirim: Via WhatsApp/Email ke siswa
Format: "Token Anda: A1B2-C3D4"
```

---

## 👤 SISWA - 5 LANGKAH MULAI UJIAN

### 1. LOGIN

```
Username: [isi]
Password: [isi]
```

### 2. BUKA "UJIAN SAYA"

```
Menu Sidebar → "Ujian Saya"
```

### 3. PILIH UJIAN

```
Lihat daftar ujian tersedia
Cari: "Ujian Matematika" atau ujian lainnya
Status: "Tersedia" = bisa diambil
```

### 4. KLIK "MULAI"

```
Tombol "Mulai" atau "Start Exam"
Form muncul: Token Validation
```

### 5. INPUT TOKEN & MULAI

```
Input: Token (Contoh: A1B2-C3D4)
Klik: "Validasi"
✅ Ujian dimulai!
```

---

## 🎓 MONITORING - Saat Ujian Berlangsung

```
Akses: Menu 🔒 → "Pantau Ujian"
atau:  List Ujian → Klik "📹" icon

Lihat:
  📊 Stats: Total, 🟢 Aktif, 🔴 Melanggar, ⚫ Terputus
  👥 Tabel: Nama, Status, Progress%, Soal, Violations, Signal
  📡 Signal: Detik sejak terakhir heartbeat
  📋 Logs: Action history (force submit/logout)

Aksi:
  ⏹️ Hentikan: Stop ujian siswa + reason
  🚪 Logout: Force logout siswa + reason

Auto Refresh: Setiap 5 detik
```

---

## 📝 PERBEDAAN MENU (Quick Comparison)

| Feature             | Menu         | Purpose             | Akses                | Kapan          |
| ------------------- | ------------ | ------------------- | -------------------- | -------------- |
| **Buat/Edit Ujian** | Ujian Saya   | CRUD                | Sidebar > Ujian Saya | Persiapan      |
| **Kelola Soal**     | Ujian Saya   | Edit soal per ujian | Ujian Saya > "📋"    | Persiapan      |
| **Generate Token**  | Kelola Token | Create access codes | 🔒 > Kelola Token    | Sehari sebelum |
| **Monitor Live**    | Pantau Ujian | Real-time tracking  | 🔒 > Pantau Ujian    | Saat ujian     |
| **Force Submit**    | Pantau Ujian | Stop exam           | Monitoring dashboard | Saat curang    |
| **Lihat Hasil**     | Hasil        | Score & analytics   | Ujian Saya > Hasil   | Setelah ujian  |

---

## 🔴 TOKEN STATUS COLORS

```
🟢 AKTIF (Hijau)       → Token belum dipakai, belum expired
🔵 DIPAKAI (Biru)      → Token sudah digunakan siswa
🔴 KADALUARSA (Merah)  → Token expired atau sudah direvoke
```

---

## 📡 SISWA STATUS COLORS (Monitoring)

```
🟢 AKTIF      → Siswa sedang ujian, signal kuat (< 40 detik)
🔴 MELANGGAR  → Siswa dengan 3+ violation
⚫ TERPUTUS    → Offline (> 40 detik no signal)
```

---

## ⏱️ TIMEOUT & TIMING

```
Token Validation:     < 100ms (instant)
Exam Auto-Submit:     Saat waktu habis
Heartbeat:           Setiap 20 detik (siswa)
Monitoring Refresh:   Setiap 5 detik (admin)
Autosave:            Setiap perubahan + 500ms delay
```

---

## 🎨 BUTTON ICONS (Cheat Sheet)

```
ADMIN EXAM LIST:
  ✏️  Edit             → Ubah setting ujian
  📋 Manage Questions → Edit soal
  🎓 Print Credential → Print kartu ujian
  ✓  Publish          → Publish ujian
  ↺  Draft            → Ubah ke draft
  📹 Monitor          → Monitor siswa
  🗑️  Delete           → Hapus ujian

ADMIN TOKEN PAGE:
  ⚡ Generate Token   → Create new tokens
  📋 Copy             → Copy token ke clipboard
  🚫 Disable          → Revoke token

MONITORING:
  ⏹️  Stop             → Force submit
  🚪 Logout           → Force logout
  🔄 Refresh          → Manual refresh
```

---

## 🧠 TIPS & TRIK

### Untuk Admin

```
1. Generate token 1 hari sebelum ujian
2. Bagikan token 1 jam sebelum ujian dimulai
3. Buka monitoring dashboard 10 menit sebelum ujian
4. Monitor di tablet/laptop terpisah selama ujian
5. Jangan close tab monitoring sampai ujian selesai
6. Export hasil langsung setelah ujian selesai
```

### Untuk Siswa

```
1. Catat token di tempat aman (jangan lupa!)
2. Login 5-10 menit sebelum ujian dimulai
3. Buka "Ujian Saya" dan cari ujian
4. Klik "Mulai" dan input token dengan benar
5. Jangan close browser sampai selesai (bisa hilang jawaban)
6. Auto-save aktif, tidak perlu klik save setiap soal
7. Klik "Selesai" saat selesai ujian
```

---

## 🔗 LINK CEPAT

```
Dashboard Admin:        /dashboard
Kelola Ujian:          /admin/exams
Kelola Token:          /admin/tokens
Pantau Ujian:          /admin/monitor/exams/{id}
Lihat Hasil:           /admin/results

Dashboard Siswa:       /dashboard
Ujian Saya:            /student/exams
Mulai Ujian:           /student/exams/{id}/start
Hasil Ujian:           /student/results
```

---

## ❓ FAQ (30 detik answers)

### Q: Token tidak valid?

**A**: Cek: Ujian sudah publish? Token belum dipakai? Belum kadaluarsa?

### Q: Siswa lihat error "Please input token"?

**A**: Admin belum generate token. Generate di "Kelola Token" dulu.

### Q: Monitoring tidak update?

**A**: Check: Ujian sudah dimulai siswa? Heartbeat visible di Network tab? Refresh halaman.

### Q: Tombol "Hentikan" tidak jalan?

**A**: Input alasan terlebih dahulu di confirm dialog.

### Q: Siswa terputus (⚫), gimana?

**A**: Tunggu 40 detik, atau force logout di monitoring jika perlu.

### Q: Ujian tidak auto-submit saat waktu habis?

**A**: Manual selesai via server, atau admin force submit.

---

## ✅ READY TO GO!

Anda sudah paham:

- ✅ Cara buat ujian (5 steps)
- ✅ Cara generate token (1 click)
- ✅ Cara siswa mulai ujian (5 steps)
- ✅ Cara monitor live (1 click)
- ✅ Cara force submit/logout (1 click)

**SELAMAT! Siap untuk production!** 🚀
