# 📋 PANDUAN PELAKSANAAN PILKETOS
## Buku Panduan Panitia Pemilihan Ketua & Wakil Ketua OSIS
**Sistem: SesekaliCBT / ExamFlow — Laravel 12**

---

## 1. TAHAP PERSIAPAN (H-1 Sore s/d Pagi Hari H)

### Langkah 1: Buat Periode Pemilihan
1. Login ke aplikasi sebagai **Superadmin**.
2. Di sidebar navigasi, buka menu **Pilketos &rarr; Buat Periode**.
3. Isi informasi periode:
   - **Judul Periode**: Contoh: `Pemilihan Ketua & Wakil Ketua OSIS 2026/2027`
   - **Tanggal Pemilihan**: Masukkan tanggal pelaksanaan (informasi acara).
   - **Deskripsi**: Keterangan tambahan (opsional).
   - **PIN Bilik Suara**: Masukkan 4–8 digit angka (atau klik tombol **🎲 Acak 6 Digit**). **Catat PIN ini**, karena akan digunakan panitia untuk membuka komputer bilik suara!
   - **Kelas Pemilih**: Pilih kelas-kelas yang berhak memilih (kosongkan semua jika seluruh siswa aktif berhak memilih).
4. Klik **Buat Periode** &rarr; Status periode akan menjadi **Draft**.

---

### Langkah 2: Daftarkan Pasangan Calon (Paslon)
1. Pada halaman detail periode, di bagian **Pasangan Calon**, klik **+ Tambah Paslon**.
2. Masukkan:
   - **Nomor Urut**: Angka 1, 2, dst.
   - **Calon Ketua OSIS**: Pilih angkatan (misal: Tingkat 11), pilih kelas, lalu pilih nama siswa dari dropdown yang tersedia (foto siswa dan kelas otomatis terisi).
   - **Calon Wakil Ketua OSIS**: Pilih angkatan, pilih kelas, lalu pilih nama siswa dari dropdown (foto siswa dan kelas otomatis terisi).
   - **Foto Paslon**: Foto otomatis menyesuaikan dari profil siswa Ketua dan Wakil. Jika ada foto resmi berdua, unggah foto pada bagian unggah foto khusus (format JPG/PNG, maks. 2MB).
   - **Visi & Misi**: Masukkan visi dan misi paslon.
3. Klik **Tambah Paslon**. Ulangi untuk paslon berikutnya.

---

### Langkah 3: Generate Data Pemilih (DPT)
1. Pada halaman detail periode, klik tombol **👥 Generate / Sinkron Pemilih**.
2. Sistem akan menyalin snapshot seluruh siswa aktif dari kelas yang dipilih.
3. Periksa jumlah siswa pada tabel ringkasan pemilih per kelas.

---

### Langkah 4: Cetak Kartu Kredensial (NIS + TOKEN) per Kelas

> ⚠️ **PERINGATAN KERAS:**
> **Cetak Kartu Kelas HANYA SEKALI per kelas. Mengklik ulang akan menghanguskan token SEMUA kartu kelas itu yang belum dipakai, termasuk yang sudah dibagikan. Untuk satu siswa gunakan Cetak Ulang.**

1. **Waktu Pencetakan**: Lakukan pencetakan kartu pada **H-1 sore atau pagi hari H** (bukan H-3).
2. Pada tabel ringkasan kelas, klik tombol **🖨️ Cetak Kartu Kelas** pada tiap kelas.
3. File PDF akan terunduh otomatis:
   - Berisi 10 kartu per lembar A4 (tabel 2 kolom x 5 baris).
   - Setiap kartu berisi: **Nama Sekolah**, **Judul Periode**, **Nama Siswa**, **Kelas**, **NIS**, dan **Kode Token** (format `ABCD-EFGH`).
   - **Catatan Penting**: Kertas ini **TIDAK** memuat PIN siswa. Kredensial hanya berupa NIS dan Token.
4. **Verifikasi & Kerahasiaan Dokumen**:
   - Buka file PDF hasil unduhan dan pastikan semua kartu terbaca jelas sebelum dibagikan.
   - File PDF berisi token pemilih adalah **dokumen rahasia**; segera hapus file PDF dari folder Downloads dan bersihkan antrean printer (print queue) setelah kartu selesai dicetak.
5. Gunting kartu per siswa dan bagikan kepada siswa pada hari pemilihan atau sesaat sebelum masuk bilik.
6. *Jika ada siswa yang kartunya rusak/hilang sebelum memilih*: Buka menu **Semua Pemilih**, cari nama siswa, lalu klik **🖨️ Cetak Ulang**. Cetak ulang akan otomatis menghanguskan kode token yang lama tanpa mengganggu siswa lain di kelas tersebut.

---

### Langkah 5: Tandai Status "Siap"
1. Jika paslon, PIN bilik, dan pemilih sudah siap, klik tombol **✅ Tandai Siap**.
2. Status periode berubah menjadi **Siap**.

---

## 2. HARI-H: PELAKSANAAN PEMILIHAN

### Langkah 1: Persiapan Komputer Bilik Suara
1. Di komputer/laptop/tablet bilik suara, buka browser (Chrome / Edge / Firefox) dan akses URL:
   ```
   https://[domain-sekolah]/pilketos
   ```
2. Pada layar akan muncul pesan **"Bilik Terkunci"**.
3. **Panitia memasukkan PIN Bilik Suara** yang telah dicatat sebelumnya, lalu klik **Buka Kunci Komputer Bilik**.
4. Komputer bilik kini siap digunakan untuk pemilih. Panitia **tidak perlu** memasukkan PIN bilik lagi untuk setiap siswa berikutnya.

---

### Langkah 2: Buka Pemilihan Secara Resmi
1. Di komputer panitia (panel admin), klik tombol **🟢 Buka Pemilihan Sekarang**.
2. Status periode berubah menjadi **Berlangsung**.
3. Komputer bilik suara sekarang aktif dan siap melayani pemilih.

---

### Langkah 3: Alur Siswa di Bilik Suara
1. **Pemeriksaan Fisik**: Siswa menunjukkan kartu kertas token kepada panitia di pintu bilik.
2. **Login di Bilik**:
   - Siswa duduk di bilik suara.
   - Siswa mengetik **NIS** dan **Kode Token** pada layar.
   - Klik **Buka Lembar Suara**.
3. **Pemberian Suara (Batas waktu: 3 Menit)**:
   - Siswa melihat kartu suara digital berisi foto, nomor, dan nama pasangan calon.
   - Siswa menekan kartu paslon pilihannya hingga muncul tanda centang/highlight.
   - Klik **Lanjut Konfirmasi Pilihan**.
4. **Konfirmasi & Selesai**:
   - Muncul modal konfirmasi pilihan.
   - Siswa menekan **✓ Ya, Kirim Suara**.
   - Layar menampilkan ucapan terima kasih bahwa suara telah dicatat secara anonim.
   - Dalam **6 detik**, layar bilik akan otomatis kembali bersih ke halaman login pemilih berikutnya.
5. **Pengumpulan Kertas Token**:
   - Siswa keluar bilik dan **menyerahkan kertas tokennya ke kotak pengumpulan panitia** sebagai tanda fisik bahwa siswa telah menggunakan hak pilihnya.

---

### Tindakan Khusus Panitia saat Hari-H:
- **Jika komputer bilik ingin diganti PIN atau dikeluarkan serentak**: Klik tombol **🚫 Cabut Sesi** di panel admin, atau perbarui PIN bilik melalui tombol **🔑 Ganti PIN**. Seluruh komputer bilik akan langsung terkunci kembali dan meminta PIN baru.
- **Monitoring**: Panitia dapat melihat angka partisipasi total pemilih, jumlah sudah memilih, dan persentase kehadiran di panel admin.

---

## 3. PASCA PEMILIHAN (PENUTUPAN & PENGUMUMAN)

### Langkah 1: Tutup Pemilihan

> ⚠️ **PERHATIAN SAAT PENUTUPAN:**
> **Tutup HANYA setelah antrean bilik suara sudah benar-benar kosong.** Suara yang dikirim oleh siswa setelah tombol tutup ditekan akan otomatis ditolak oleh sistem.

1. Ketika waktu pemilihan telah berakhir dan seluruh siswa di antrean bilik selesai memilih, panitia mengklik tombol **🔴 Tutup Pemilihan**.
2. Status periode menjadi **Ditutup**.
3. Bilik suara publik otomatis ditutup dan tidak dapat menerima suara lagi.

---

### Langkah 2: Rekonsiliasi & Pemeriksaan Integritas Suara
1. Panitia menghitung secara manual jumlah lembar fisik kartu token yang terkumpul dari para pemilih.
2. Buka menu **📊 Lihat Hasil & Rekonsiliasi**.
3. Pada blok **Rekonsiliasi Suara & Konsistensi**:
   - Periksa apakah **Suara Tercatat di Sistem** sama dengan **Pemilih Berstatus "Sudah Memilih"**.
   - Masukkan jumlah fisik kertas terkumpul pada form **Input Jumlah Kertas Fisik Terkumpul**, lalu klik **Simpan & Verifikasi**.
   - Sistem akan memvalidasi apakah ada selisih antara kertas fisik dengan suara yang masuk.

---

### Langkah 3: Pengumuman & Ekspor Hasil

> 🛑 **ATURAN MUTLAK:**
> **Jangan pernah menekan tombol "Buka Kembali" setelah hasil pemilihan dilihat.** Membuka kembali pemilihan setelah hasil diketahui mencederai integritas, kerahasiaan, dan keabsahan pemilihan umum.

1. Halaman hasil akan menampilkan:
   - **Pemenang Pemilihan** (atau tanda **Hasil Seri** jika terjadi perolehan sama).
   - Diagram dan persentase perolehan suara per paslon.
   - Tabel partisipasi kehadiran per kelas.
2. Klik tombol **📥 Unduh Excel Rekap** untuk mengunduh berita acara resmi dalam format Excel untuk laporan pertanggungjawaban kepada Kepala Sekolah.

---

## 4. PERTANYAAN UMUM & SOLUSI KENDALA (FAQ)

| Kendala | Penyebab & Solusi |
|---|---|
| Siswa salah memasukkan NIS / Token | Pesan muncul *"NIS atau token tidak sesuai."* Pastikan siswa mengetik NIS yang benar dan kode 8 karakter token (besar/kecil huruf tidak berpengaruh). Jika 5x salah, NIS akan terkunci selama 5 menit untuk alasan keamanan. |
| Muncul pesan *"NIS atau token tidak sesuai"* padahal fisik kartu ada | Pesan tersebut sengaja dibuat generik demi keamanan. Segera periksa status siswa tersebut di menu **Semua Pemilih**. Jika statusnya sudah **"Sudah memilih"**, artinya token pada kartu tersebut telah terpakai sebelumnya (tangani kejadian ini sebagai dugaan kartu ganda atau kartu yang tercecer). |
| Layar bilik suara error `419 / Page Expired` | Sesi halaman CSRF browser kadaluwarsa karena komputer dibiarkan menyala terlalu lama tanpa aktivitas. **Tekan tombol F5 (Refresh)** pada keyboard, lalu panitia memasukkan kembali PIN bilik jika diminta. |
| Token siswa hilang atau rusak sebelum memilih | Buka panel admin &rarr; **Semua Pemilih** &rarr; cari siswa &rarr; klik **🖨️ Cetak Ulang**. Token lama otomatis hangus dan digantikan token baru tanpa mempengaruhi siswa lain. |
| Siswa yang sudah memilih mencoba login lagi | Sistem otomatis menolak dengan pesan *"Anda sudah menggunakan hak pilih."* Siswa yang sudah berstatus memilih tidak dapat login kembali. |
| Layar bilik suara terkunci meminta PIN | Masukkan PIN bilik suara milik panitia. Ini terjadi saat komputer baru dinyalakan atau sesi bilik dicabut dari admin. |
| Hasil perolehan suara tidak bisa dibuka saat pemilihan | Sesuai standar integritas pemilu, hasil pemilihan dirahasiakan dan **HANYA dapat dibuka setelah pemilihan resmi berstatus Ditutup**. |
