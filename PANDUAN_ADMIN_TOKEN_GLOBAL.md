# 📘 PANDUAN ADMIN - Sistem Token Global Dinamis

## 🎯 Ringkas: Apa yang Berubah?

| Aspek               | Sebelum            | Sekarang                 |
| ------------------- | ------------------ | ------------------------ |
| **Token per Ujian** | Multiple (1-50)    | **1 Global**             |
| **Cara Generate**   | Manual di admin    | **Auto saat publish**    |
| **Refresh**         | Tidak pernah       | **Auto 20 menit**        |
| **Format**          | XXXX-XXXX (8 char) | **XXXXXX (6 char)**      |
| **Session Key**     | ujian*aktif*{id}   | **authorized*exam*{id}** |

---

## ⚡ 3 Langkah Cepat

### Langkah 1: Buat & Publikasikan Ujian

```
1. Admin → Manajemen Ujian → Buat Ujian Baru
2. Isi: Judul, Waktu, Durasi, Soal
3. Klik "PUBLISH"
   ✅ Token otomatis dibuat!
   ✅ Misalnya: "A1B2C3"
```

### Langkah 2: Bagikan Token ke Siswa

```
1. Copy token dari admin dashboard
   "Token Ujian: A1B2C3"
2. Kirim via WhatsApp/Email ke siswa
3. Biarkan token mengubah otomatis
   setiap 20 menit (sistem automatic)
```

### Langkah 3: Pantau Ujian

```
1. Admin → Pengawasan → Pantau Ujian
2. Lihat siswa yang sedang mengerjakan
3. Jika perlu, force submit atau logout
```

---

## 🔄 Token Lifecycle

### Status: DRAFT (Sedang Membuat Ujian)

```
├─ Tombol: "PUBLISH"
├─ Token: ❌ TIDAK ADA
└─ Siswa: Tidak bisa masuk
```

### Status: PUBLISHED (Ujian Aktif)

```
├─ Tombol: "BACK TO DRAFT", "REFRESH TOKEN"
├─ Token: ✅ ADA (misalnya "A1B2C3")
├─ Refresh: Auto setiap 20 menit
└─ Siswa: Bisa masuk dengan token terbaru
```

### Status: DRAFT/FINISHED (Ujian Ditutup)

```
├─ Token: ❌ DIHAPUS OTOMATIS
└─ Siswa: Tidak bisa masuk lagi
```

---

## 🚀 Fitur-Fitur Utama

### ✅ 1. Auto-Generate Token saat Publish

```
Kejadian: Admin klik "PUBLISH"
Sistem:  Token otomatis dibuat (6 angka/huruf)
Misal:   "X5Z9M2"
Hasil:   Admin lihat di dashboard
```

### ✅ 2. Auto-Refresh Setiap 20 Menit

```
Jam 08:00 AM → Token: "A1B2C3" (generated)
Jam 08:20 AM → Token: "X5Y6Z7" (auto-refresh!)
             → Siswa baru harus pakai "X5Y6Z7"
             → Siswa lama (sudah masuk) tidak terpengaruh
Jam 08:40 AM → Token: "K9L8M7" (auto-refresh!)
```

### ✅ 3. Manual Refresh Token (Kapan Saja)

```
Tombol: "🔄 REFRESH TOKEN" (di admin dashboard)

Use Case:
- Ingin ganti token sebelum 20 menit
- Token terlupa (ganti untuk siswa baru)
- Security concern (ingin token baru immediately)

Efek:
- Token lama LANGSUNG tidak berlaku
- Siswa baru harus pakai token baru
- Siswa yang sudah masuk tetap aman (session)
```

### ✅ 4. Countdown Timer di Dashboard

```
Bagian: Token Active Display
```

┌─────────────────────────────┐
│ Token Aktif: A1B2C3 │
│ Berlaku Hingga: 08:20 AM │
│ Waktu Tersisa: 15 menit ⏱ │
│ [🔄 Refresh Sekarang] │
└─────────────────────────────┘

```

---

## 📋 Workflow: Dari A sampai Z

### SEBELUM UJIAN

```

Senin 08:00 AM
├─ Admin: Buat ujian baru
│ └─ Judul: "Ujian Matematika Kelas 8"
│ Waktu: Senin 09:00 AM - 10:30 AM
│ Durasi: 90 menit
│ Soal: 40 butir
│
├─ Admin: Klik "PUBLISH"
│ └─ ✅ Token auto-dibuat: "K4M7P2"
│ ✅ Dashboard show: "Berlaku hingga 08:20 AM"
│
└─ Admin: Copy token & share ke siswa
├─ Via WA: "Ni token ujian mat kls 8: K4M7P2"
├─ Via Email: Terlampir di lampiran
└─ Via Chat: K4M7P2

```

### SAAT UJIAN SUDAH DIMULAI

```

Senin 08:55 AM (5 menit sebelum ujian mulai)
├─ Student A login
│ └─ Lihat ujian "Ujian Matematika Kelas 8"
│ Klik "Mulai"
│ Input token: "k4m7p2" (case-insensitive)
│ Klik "Mulai Ujian"
│ ✅ Session created: authorized*exam*[id] = true
│ ✅ Redirect ke halaman soal
│
├─ Student B login
│ └─ (Same flow dengan token yang sama)
│ ✅ Separate session untuk Student B
│
└─ Student C login
└─ Same flow
✅ Separate session untuk Student C

```

### SAAT UJIAN BERLANGSUNG

```

Senin 09:00 AM - 09:15 AM
├─ Student A mengerjakan soal 1-5
│ └─ Autosave terus berjalan
│
├─ Student B mengerjakan soal 1-3
│ └─ Browsing normal, tidak ada error
│
├─ Student C refresh page (F5)
│ └─ VerifyExamSession middleware check
│ ├─ Exam status = 'published' ✓
│ ├─ Session authorized*exam*[id] ✓
│ └─ ALLOW → Student C lanjut soal

Senin 09:20 AM (TOKEN AUTO-REFRESH TERJADI!)
├─ Token lama: "K4M7P2" → TOKEN LAMA
├─ Token baru: "X9Z5M3" → TOKEN BARU
│
├─ Student D ingin masuk
│ └─ Input token "K4M7P2" (lama)
│ ❌ ERROR: "Token salah atau sudah kadaluwarsa"
│ Admin: Kasih token baru "X9Z5M3"
│
└─ Student A, B, C (sudah masuk)
└─ Tetap aman (session berlaku)
Tidak perlu re-validasi token

```

### SAAT UJIAN SEDANG BERLANGSUNG, ADMIN INGIN GANTI TOKEN

```

Senin 09:25 AM
Admin: Klik "🔄 REFRESH TOKEN" di dashboard
System: Generate token baru "M2K8L5"

Efek:
├─ Token "X9Z5M3" → tidak berlaku
├─ Token baru: "M2K8L5" (aktif)
├─ Student A,B,C (masih di ujian)
│ └─ Tidak terdampak (session ada)
│ Bisa submit ujian normally
│
└─ Student E (belum masuk)
└─ Harus gunakan "M2K8L5"

```

### SAAT UJIAN SELESAI

```

Senin 10:30 AM (Ujian berakhir)
├─ Student A: Klik "SELESAI" → submit
├─ Student B: Klik "SELESAI" → submit
├─ Student C: Klik "SELESAI" → submit
│
└─ Admin: Klik "BACK TO DRAFT"
├─ Status → "draft"
├─ Token → ❌ DIHAPUS OTOMATIS
└─ Siswa baru: Tidak bisa masuk
Message: "Ujian tidak tersedia"

```

---

## 🎮 Admin Dashboard Features

```

┌────────────────────────────────────────────────┐
│ ADMIN DASHBOARD - MANAJEMEN UJIAN │
└────────────────────────────────────────────────┘

Daftar Ujian
├─ Judul │ Status │ Aksi
├─ Matematika Kls 8 │ PUBLISHED │ [Edit] [Publish↓]
│ │ │ [Refresh Token]
│ Token: K4M7P2 │ │ [Monitor]
│ Berlaku: 15 min ⏱ │ │
│
├─ B. Inggris Kls 7 │ DRAFT │ [Edit] [Publish]
│ Token: (tidak ada) │ │
│
└─ IPA Kls 9 │ PUBLISHED │ [Edit] [Publish↓]
Token: X9Z5M3 │ │ [Refresh Token]
Berlaku: 5 min ⏱ │ │ [Monitor]

```

### Tombol-Tombol Penting

```

1. [PUBLISH]
    - Ubah status dari draft → published
    - Auto-generate token
    - Siswa bisa masuk

2. [BACK TO DRAFT]
    - Ubah status dari published → draft
    - Token dihapus
    - Siswa tidak bisa masuk

3. [REFRESH TOKEN]
    - Generate token baru
    - Token lama tidak berlaku
    - Siswa baru pakai token baru
    - Siswa lama (sudah masuk) aman

4. [MONITOR]
    - Lihat siswa yang sedang mengerjakan
    - Real-time progress tracking
    - Option untuk force submit/logout

5. [EDIT]
    - Edit judul, waktu, soal
    - Hanya jika status = DRAFT

```

---

## ❓ Frequently Asked Questions (FAQ)

### Q: Token pembagian ke berapa siswa?
A: **Satu token untuk SEMUA siswa**. Tidak perlu buat berlipat-lipat token.

### Q: Apa token berubah otomatis?
A: **Ya, setiap 20 menit otomatis berubah**.
- Misalnya 08:00 AM → 08:20 AM → 08:40 AM
- Siswa yang sudah masuk tidak terpengaruh

### Q: Bagaimana jika siswa sudah di tengah ujian saat token berubah?
A: **Tidak apa-apa!** Siswa sudah ada session, jadi sistem tidak check token lagi.

### Q: Bolehkah refresh token manual sebelum 20 menit?
A: **Boleh!** Klik tombol "REFRESH TOKEN" kapan saja. Siswa baru harus pakai token baru.

### Q: Jika token lupa,bagaimana?
A: **Refresh token!** Ganti dengan yang baru, kasih ke siswa.

### Q: Token lama masih berlaku jika di-refresh?
A: **Tidak.** Token lama langsung tidak berlaku. Hanya token baru yang aktif.

### Q: Bagaimana jika admin publikasi ujian tapi lupa generate token?
A: **Sistem otomatis generate!** Tidak perlu manual.

### Q: Session berapa lama?
A: **120 menit (2 jam).** Cukup untuk ujian hingga 90 menit. Jika lebih, siswa mungkin perlu re-validasi.

### Q: Bisa ganti token berkali-kali dalam satu ujian?
A: **Bisa, tapi tidak disarankan.** Siswa yang belum masuk akan kebingungan. Gunakan hanya jika perlu.

---

## 🎯 Best Practices

### ✅ DO
- [ ] Publish ujian minimal 30 menit sebelum jam mulai
- [ ] Token akan auto-generate saat publish
- [ ] Bagikan token via channel resmi (WhatsApp, Email, Chat)
- [ ] Gunakan "REFRESH TOKEN" jika ada concern keamanan
- [ ] Monitor ujian melalui "MONITOR" button
- [ ] Baca countdown timer untuk next auto-refresh

### ❌ DON'T
- [ ] Jangan refresh token terlalu sering (membingungkan siswa)
- [ ] Jangan share token via public channel (ganti token, kasih yang baru)
- [ ] Jangan ubah status ujian saat siswa sedang mengerjakan (jika mungkin)
- [ ] Jangan lupa backup hasil ujian setelah selesai

---

## 📞 Troubleshooting

### "Token tidak valid"
**Penyebab**: Siswa pakai token lama (setelah 20 menit auto-refresh)
**Solusi**: Kasih token terbaru dari admin dashboard

### "Ujian tidak tersedia"
**Penyebab**: Status ujian bukan "published"
**Solusi**: Admin klik "PUBLISH" untuk mengaktifkan

### "Ujian belum dimulai"
**Penyebab**: Waktu mulai ujian belum tiba
**Solusi**: Tunggu sampai waktu mulai yang dijadwalkan

### Siswa keluar ujian tiba-tiba
**Diagnosis**: Check "MONITOR" untuk lihat status
**Solusi**:
- Jika unintentional: Siswa dapat refresh page & lanjut (session masih ada)
- Jika intentional: Admin bisa force submit

---

## 💡 Tips & Tricks

1. **Set reminder**: Alarm 10 menit sebelum auto-refresh jika penting
2. **Monitor countdown**: Dashboard menunjukkan waktu refresh berikutnya
3. **Test terlebih dahulu**: Coba publish exam dummy, validasi token dengan akun test
4. **Communication**: Jelaskan ke siswa tentang auto-refresh sebelum ujian
5. **Keep it simple**: Satu token untuk semua, tidak perlu distribute multiple

---

**Siap menggunakan sistem dynamic token! 🚀**

Jika ada pertanyaan, lihat dokumentasi lengkap di: `REFACTOR_TOKENISASI_LENGKAP.md`
```
