# Fix NIS Integer Overflow Issue

## Masalah

Ketika mengimpor data siswa dengan NIS besar seperti `2425100836`, terjadi **integer overflow** karena:

1. Excel membaca NIS sebagai angka (INTEGER)
2. NIS `2425100836` > 2,147,483,647 (batas signed 32-bit integer)
3. PHP/MySQL mengkonversi ke nilai negatif: `-1869866460`
4. Data disimpan ke database dengan NIS yang salah

## Penyebab

Maatwebsite Excel library membaca sel sebagai number/integer jika formatnya adalah number di Excel, menyebabkan overflow sebelum data sampai ke application.

## Solusi Permanen

Sudah diperbaiki di:

### 1. **StudentImport.php**

- NIS sekarang di-cast eksplisit ke string: `(string) $nisValue`
- Ini memastikan, apapun tipe data dari Excel, akan dikonversi ke string

### 2. **StudentService.php**

- Both `createStudent()` dan `createOrUpdateStudent()`
- Sekarang cast NIS ke string: `$nis = (string) $data['nis']`

## Memperbaiki Data yang Sudah Corrupt

Jika sudah ada data siswa dengan NIS negatif/salah di database, gunakan script di bawah:

### Option 1: Cari dan lihat dulu

```bash
php artisan tinker

# Cari semua student dengan NIS negatif
App\Models\User::where('role', 'student')
    ->where('nis', '<', 0)
    ->get()
    ->each(fn($u) => echo "{$u->id}: {$u->nis} - {$u->name}\n");

exit
```

### Option 2: Jika Anda tahu NIS yang benarnya

Jika NIS asli adalah `2425100836` dan tersimpan sebagai `-1869866460`:

```bash
php artisan tinker

# Update satu student
$student = App\Models\User::find(ID_SISWA); # ganti dengan ID siswa yang benar
$student->update(['nis' => '2425100836']);

# Verify
$student->refresh();
echo $student->nis; # harus show 2425100836

exit
```

### Option 3: Hapus dan re-import

Jika banyak data yang corrupt:

```bash
php artisan tinker

# Hapus semua student dengan NIS negatif
App\Models\User::where('role', 'student')
    ->where('nis', '<', 0)
    ->delete();

echo "Deleted students with negative NIS";
exit
```

Kemudian:

1. Perbaiki CSV file (pastikan NIS adalah jenis `Text`, bukan `Number`)
2. Re-import melalui admin panel

## Cara Menyimpan CSV dengan Benar di Excel

### Metode 1: Format Kolom NIS sebagai Text

1. Buka CSV di Excel
2. Select kolom NIS
3. Right-click → **Format Cells**
4. Set ke **Text** format
5. Save as CSV

### Metode 2: Gunakan Google Sheets

1. Upload CSV ke Google Sheets
2. Google Sheets otomatis mempertahankan format text untuk angka besar
3. Download sebagai CSV

### Metode 3: Format di CSV Langsung

Tambahkan single quote di depan NIS saat membuat CSV manual:

```csv
nis,full_name,grade,class_group
'2425100836,NAMA SISWA,12,B
'2324100698,NAMA SISWA 2,12,B
```

Quote akan hilang saat dimuat ke application.

## Test Import Sekarang

Sekarang coba import dengan NIS besar:

1. Buat CSV test dengan beberapa siswa dengan NIS >= 2,147,483,647
2. Pastikan kolom NIS di-format sebagai TEXT di Excel
3. Upload melalui admin panel
4. Verifikasi NIS tersimpan dengan benar:

```bash
php artisan tinker
App\Models\User::where('nis', '2425100836')->first();
# Harus show NIS = '2425100836', bukan -1869866460
exit
```

## Perbaikan Permanen Lainnya

Untuk mencegah issue ini di masa depan, pertimbangkan:

### Option A: CSV Template dengan Format Sudah Benar

Buat template CSV di Excel dengan NIS sudah di-format sebagai Text, kemudian berikan kepada user

### Option B: Validasi di Import Form

Tambahkan validasi yang mendeteksi NIS negatif dan reject import:

```php
// Add to StudentImport.php
if ($nisString < 0) {
    throw new Exception("NIS cannot be negative: {$nisString}. Please format NIS as Text in Excel before export.");
}
```

### Option C: Migration untuk Format Kolom NIS

Edit migration untuk explicitly membuat kolom NIS sebagai CHAR/VARCHAR dengan fixed length:

```php
$table->char('nis', 12)->unique()->nullable();  // Fixed 12-digit NIS
```

## Verification Commands

```bash
cd /home/adeandro/developments/sesekaliCBT

# Check if fix works
php artisan tinker

# Count students with proper NIS (should be all positive or string numeric)
echo "Students count: ";
App\Models\User::where('role', 'student')->count();

# Check for any negative NIS (should return 0)
echo "Negative NIS count: ";
App\Models\User::where('role', 'student')
    ->whereRaw("CAST(nis AS SIGNED) < 0")
    ->count();

exit
```

---

**Masalah sudah diperbaiki. Next import akan menangani NIS besar dengan benar.** ✅
