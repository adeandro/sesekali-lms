# 🚀 Panduan Lengkap Memulai Ujian

## 📋 Alur Lengkap Dari A sampai Z

### **FASE 1: ADMIN - BUAT UJIAN**

#### Langkah 1: Akses Menu Manajemen Ujian

```
Sidebar → "Manajemen Ujian" → Klik Link "Daftar Ujian"
atau
Dashboard Admin → Tombol "Daftar Ujian"
```

#### Langkah 2: Buat Ujian Baru

```
Klik "+ Buat Ujian"
Isi:
  - Judul Ujian: "Ujian Matematika Kelas 10"
  - Mata Pelajaran: Pilih
  - Kelas: Pilih
  - Tanggal Mulai: Tentukan
  - Tanggal Berakhir: Tentukan
  - Durasi: Berapa menit
  - Tipe: Multiple Choice / Essay / Campuran
  - Setting lainnya
Klik "Simpan"
```

#### Langkah 3: Tambah Soal ke Ujian

```
Di list ujian → Klik icon "📋" (Soal)
atau
Klik "Kelola Soal"
```

**Edit** soal / **Add** soal sesuai kebutuhan

#### Langkah 4: Publikasikan Ujian

```
Di list ujian → Klik icon "✓" (Publikasikan)
Status akan berubah dari "Draft" menjadi "Pub"
```

⚠️ **PENTING**: Ujian harus **Published** agar siswa bisa lihat dan mulai!

---

### **FASE 2: ADMIN - GENERATE TOKEN**

#### Langkah 1: Akses Kelola Token

```
Sidebar → "🔒 Pengawasan & Keamanan" → "Kelola Token"
atau
Dashboard Admin → Tombol "Kelola Token"
```

#### Langkah 2: Generate Token untuk Ujian

```
Klik Tombol "⚡ Generate Token Baru"
atau
Klik "Generate Token" di tombol header ujian
```

#### Langkah 3: Isi Form

```
- Pilih Ujian: Pilih ujian yang sudah dipublish
- Jumlah Token: 5, 10, 20, dll (sesuai jumlah siswa)
- Validitas: 1 hari, 3 hari, dll
Klik "Generate"
```

#### Langkah 4: Token Siap

```
✅ Token berhasil dibuat!
Contoh format: A1B2-C3D4
```

#### Langkah 5: Salin & Bagikan Token

```
Klik Tombol "📋 Salin" di setiap token
Share via WhatsApp / Email ke siswa
Format: "Gunakan token ini untuk ujian: A1B2-C3D4"
```

---

### **FASE 3: SISWA - MULAI UJIAN**

#### Cara Siswa Mulai Ujian:

**Step 1**: Login sebagai siswa

```
Masukkan username & password
```

**Step 2**: Buka Dashboard

```
Menu Sidebar → "Ujian Saya"
```

**Step 3**: Lihat Daftar Ujian

```
Lihat semua ujian yang tersedia
Status "Tersedia" = bisa diambil
```

**Step 4**: Klik "Mulai Ujian"

```
Klik tombol "Mulai" atau "Mulai Ujian"
```

**Step 5**: Form Token Validation Muncul

```
Ada form untuk input token
Contoh: "A1B2-C3D4"
```

**Step 6**: Input Token

```
Masukkan token yang diberikan admin
Format bisa dengan atau tanpa dash: A1B2C3D4 atau A1B2-C3D4
Input field akan auto-format jadi A1B2-C3D4
```

**Step 7**: Klik Tombol "Validasi"

```
Server akan cek:
  ✓ Token ada di sistem?
  ✓ Token belum dipakai?
  ✓ Token belum kadaluarsa?
```

**Step 8**: Ujian Dimulai!

```
✅ Token valid → Redirect ke halaman ujian
🎯 Timer dimulai
📊 Progress bar tampil
❓ Soal siap dijawab
```

---

### **FASE 4: ADMIN - PANTAU UJIAN BERLANGSUNG**

#### Monitoring Real-Time

**Akses Monitoring**:

```
Sidebar → "🔒 Pengawasan & Keamanan" → "Pantau Ujian"
atau
Daftar Ujian → Klik icon "📹" di ujian yang active
```

**Lihat Data Real-Time**:

```
📊 Stats:
   - Total Siswa: Berapa banyak yang mulai
   - 🟢 Aktif: Siswa yang sedang ujian
   - 🔴 Melanggar: Siswa dengan 3+ violation
   - ⚫ Terputus: Siswa yang offline

👥 Tabel Siswa dengan:
   - Nama siswa
   - 🟢/🔴/⚫ Status
   - Progress bar (%)
   - Nomor soal saat ini
   - ⚠️ Jumlah pelanggaran
   - 📡 Sinyal (detik sejak terakhir ping)
```

**Setiap 5 detik auto-refresh**

#### Kontrol Siswa (Force Submit / Logout)

**Untuk Hentikan Ujian Siswa**:

```
1. Cari siswa di tabel
2. Klik tombol "⏹️ Hentikan"
3. Confirm dialog
4. Input alasan penghentian
5. ✅ Ujian siswa langsung disubmit
6. Tercatat di log aksi
```

**Untuk Logout Paksa Siswa**:

```
1. Cari siswa di tabel
2. Klik tombol "🚪 Logout"
3. Confirm dialog (WARNING)
4. Input alasan logout
5. ✅ Sesi siswa di-lock
6. Siswa tidak bisa lanjut ujian
7. Tercatat di log aksi
```

---

## 📱 Untuk Siswa - Step by Step

### **Alur Siswa dari Login sampai Selesai Ujian**

```
1️⃣  LOGIN
    └─> Username: [isi]
    └─> Password: [isi]
    └─> Klik Login

2️⃣  BUKA "UJIAN SAYA"
    └─> Menu sidebar: "Ujian Saya"

3️⃣  LIHAT DAFTAR UJIAN
    └─> Ujian yang Tersedia:
           - Ujian Matematika [Mulai]
           - Ujian Bahasa [Mulai]
    └─> Status "Tersedia" = bisa diambil

4️⃣  KLIK "MULAI"
    └─> Form: "Validasi Token Ujian"
    └─> Input: "Masukkan Token"
    └─> Contoh: A1B2-C3D4

5️⃣  INPUT TOKEN
    └─> Copy token dari guru
    └─> Paste ke form
    └─> Auto-format ke A1B2-C3D4
    └─> Klik "Validasi"

6️⃣  UJIAN DIMULAI ✅
    └─> ⏱️ Timer mulai (contoh: 120 menit)
    └─> 📊 Progress bar
    └─> ❓ Soal siap dijawab
    └─> 📑 Navigasi soal di sidebar kiri

7️⃣  JAWAB SOAL
    └─> Baca soal
    └─> Pilih jawaban / Isi essay
    └─> Auto-save (tidak perlu klik save)
    └─> Lanjut ke soal berikutnya

8️⃣  SELESAI & SUBMIT
    └─> Setelah selesai semua soal
    └─> Klik "Selesai" / "Submit"
    └─> Confirm: "Yakin ingin submit?"
    └─> ✅ Exam submitted

9️⃣  LIHAT HASIL
    └─> Auto redirect ke halaman hasil
    └─> Lihat nilai, ranking, statistik
    └─> Bisa lihat review jawaban yang salah
```

---

## ⚙️ Menu-Menu Penting

### **Untuk ADMIN (Sidebar)**

```
🏠 Beranda                              [Home]
├─ 📚 Kelola Ujian
│  ├─ Manajemen Ujian                  [CRUD Ujian]
│  │  ├─ Daftar Ujian                  [List]
│  │  └─ + Buat Ujian Baru             [Create]
│  │
│  ├─ Kelola Soal                      [Edit soal per ujian]
│  │
│  ├─ Hasil                             [Score & Review]
│  │  └─ Export hasil
│  │
│  └─ Kelola:
│     ├─ Siswa                          [Import/Export CSV]
│     ├─ Mata Pelajaran
│     └─ Soal                           [Cross-exam management]
│
├─ 🔒 Pengawasan & Keamanan
│  ├─ Kelola Token                     [NEW! Generate & Manage]
│  │  ├─ Generate Token                [Per ujian]
│  │  ├─ View Token Status             [Used/Unused/Expired]
│  │  ├─ Revoke Token                  [Nonaktifkan]
│  │  └─ Filter by Status
│  │
│  └─ Pantau Ujian                     [NEW! Real-time Monitor]
│     ├─ Live Data                     [Every 5 sec refresh]
│     ├─ Force Submit                  [+ Reason]
│     ├─ Force Logout                  [+ Reason]
│     └─ Action Logs                   [Audit trail]
│
└─ 👤 Account
   ├─ Settings
   └─ Logout
```

---

## 🎯 Perbedaan Menu (Agar Tidak Bingung)

| Menu                | Tujuan                                 | Kapan Digunakan        |
| ------------------- | -------------------------------------- | ---------------------- |
| **Manajemen Ujian** | Buat, edit, atur waktu, settings ujian | Sebelum ujian dimulai  |
| **Kelola Soal**     | Tambah/edit soal ke ujian              | Sebelum ujian dimulai  |
| **Kelola Token**    | Generate & manage token akses          | Sehari sebelum ujian   |
| **Pantau Ujian**    | Monitor siswa yang ujian (Real-time)   | Saat ujian berlangsung |
| **Hasil**           | Lihat score, ranking, ulasan           | Setelah ujian berakhir |

---

## ✅ Checklist Persiapan Ujian

```
□ Buat Ujian di "Manajemen Ujian"
  ├─ Tentukan judul, mata pelajaran, durasi, jadwal
  └─ Simpan sebagai Draft

□ Tambah Soal ke Ujian
  ├─ Klik icon "📋" di list ujian
  ├─ Tambah soal atau import dari CSV/Excel
  └─ Minimum: Harus ada 1 soal

□ Publikasikan Ujian
  ├─ Ubah status dari Draft → Published
  └─ ⚠️ Ujian harus published agar siswa lihat!

□ Generate Token
  ├─ Buka "Kelola Token"
  ├─ Klik "+ Generate Token Baru"
  ├─ Pilih ujian yang published
  ├─ Set jumlah token = jumlah siswa
  ├─ Set validitas (rekomendasi: 1-3 hari)
  └─ Generate!

□ Bagikan Token ke Siswa
  ├─ Copy token dari halaman "Kelola Token"
  ├─ Kirim via WhatsApp/Email ke setiap siswa
  ├─ Format: "Token ujian Anda: A1B2-C3D4"
  └─ Bagikan sebelum ujian dimulai

□ Siap Monitoring
  ├─ Buka "Pantau Ujian" saat ujian dimulai
  ├─ Monitor real-time 🟢/🔴/⚫ status siswa
  ├─ Siap gunakan Force Submit/Logout jika perlu
  └─ Lihat audit log semua aksi

□ Selesai
  ├─ Ujian auto-submit saat waktu habis atau siswa klik finish
  └─ Cek hasil di "Hasil & Nilai"
```

---

## 🆘 Troubleshooting

### **Pembilang bertanya: "Kok tidak ada menu monitoring?"**

```
✅ SOLUSI:
  - Sidebar sekarang jelas: "Pantau Ujian" di section "Pengawasan"
  - Berbeda dengan "Manajemen Ujian" (CRUD)
  - Icon: 📹 (video) untuk pantau × 📝 (list) untuk manajemen
```

### **Siswa input token, tapi "Token tidak valid"**

```
Kemungkinan penyebab:
  ❌ Token belum di-generate → Generate dulu
  ❌ Token sudah dipakai → Generate token baru
  ❌ Token kadaluarsa → Generate token baru dengan validitas lebih lama
  ❌ Ujian belum published → Publikasikan ujian dulu
  ❌ Typo token → Cek spelling, copy-paste yang benar
```

### **"Kelola Token menus masih belum ready"**

```
✅ SUDAH READY!
  Lokasi: Sidebar → "Pengawasan & Keamanan" → "Kelola Token"
  atau
  Dashboard Admin → Tombol "Kelola Token"
```

### **Siswa mulai ujian tapi tidak bisa input jawaban**

```
Kemungkinan:
  ❌ Ujian belum official dimulai (cek waktu start)
  ❌ Ujian sudah berakhir (cek waktu end)
  ❌ Admin force-logout siswa (cek log)
  ✓ Jika normal: Try refresh atau contact support
```

---

## 📞 Support

Jika ada yang tidak berfungsi:

1. Cek error message di halaman
2. Lihat browser Network tab (Ctrl+Shift+I → Network)
3. Cek Laravel logs: `tail -f storage/logs/laravel.log`
4. Contact developer dengan screenshot error

---

## 🎉 Selesai!

Anda sekarang sudah memahami **complete flow** dari A sampai Z:

✅ Admin: Buat Ujian → Generate Token → Pantau Real-time  
✅ Siswa: Login → Mulai Ujian → Input Token → Jawab → Submit  
✅ Admin: Lihat Hasil → Export → Analisis

**SIAP UNTUK PRODUCTION!** 🚀
