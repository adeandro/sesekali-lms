<?php
namespace App\Services;

class TypingTestService
{
    /**
     * Daftar kata bahasa Indonesia untuk typing test.
     * Minimal 250 kata unik, hardcoded (tidak fetch dari luar).
     */
    private static array $wordList = [
        'makan', 'minum', 'jalan', 'rumah', 'buku', 'tulis', 'baca', 'pergi', 'datang', 'kerja',
        'tidur', 'bangun', 'sekolah', 'guru', 'siswa', 'belajar', 'pintar', 'rajin', 'senang', 'sedih',
        'marah', 'tertawa', 'menangis', 'berlari', 'berjalan', 'melihat', 'mendengar', 'berbicara',
        'menulis', 'membaca', 'menghitung', 'bermain', 'bekerja', 'memasak', 'mandi', 'pulang',
        'naik', 'turun', 'masuk', 'keluar', 'buka', 'tutup', 'ambil', 'taruh', 'beli', 'jual',
        'pasar', 'toko', 'kantor', 'gedung', 'jalan', 'sungai', 'danau', 'gunung', 'pantai', 'hutan',
        'pohon', 'bunga', 'buah', 'sayur', 'nasi', 'ayam', 'ikan', 'telur', 'susu', 'roti',
        'kopi', 'teh', 'air', 'gula', 'garam', 'merica', 'bawang', 'cabai', 'tomat', 'wortel',
        'sepatu', 'baju', 'celana', 'tas', 'topi', 'kacamata', 'jam', 'cincin', 'gelang', 'kalung',
        'meja', 'kursi', 'lemari', 'kasur', 'bantal', 'selimut', 'pintu', 'jendela', 'lantai', 'atap',
        'mobil', 'motor', 'sepeda', 'bus', 'kereta', 'pesawat', 'kapal', 'becak', 'ojek', 'taksi',
        'telepon', 'komputer', 'laptop', 'tablet', 'kamera', 'televisi', 'radio', 'mesin', 'lampu', 'kipas',
        'dokter', 'perawat', 'polisi', 'tentara', 'petani', 'nelayan', 'pedagang', 'pelajar', 'mahasiswa', 'presiden',
        'ibu', 'bapak', 'kakak', 'adik', 'nenek', 'kakek', 'paman', 'bibi', 'teman', 'sahabat',
        'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh',
        'baik', 'buruk', 'besar', 'kecil', 'panjang', 'pendek', 'tinggi', 'rendah', 'cepat', 'lambat',
        'baru', 'lama', 'muda', 'tua', 'cantik', 'tampan', 'jelek', 'bagus', 'indah', 'kotor',
        'bersih', 'keras', 'lembut', 'panas', 'dingin', 'terang', 'gelap', 'ramai', 'sepi', 'dekat',
        'jauh', 'kiri', 'kanan', 'atas', 'bawah', 'depan', 'belakang', 'tengah', 'dalam', 'luar',
        'pagi', 'siang', 'sore', 'malam', 'hari', 'minggu', 'bulan', 'tahun', 'waktu', 'lama',
        'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'januari', 'februari', 'maret',
        'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember',
        'membeli', 'menjual', 'mencari', 'menemukan', 'membawa', 'menaruh', 'mengambil', 'memberikan',
        'menerima', 'mengirim', 'membantu', 'menolong', 'melayani', 'memimpin', 'mengajar', 'mendidik',
        'merawat', 'menjaga', 'melindungi', 'menyelamatkan', 'menghormati', 'mencintai', 'menyayangi',
        'berpikir', 'berencana', 'berusaha', 'berhasil', 'gagal', 'mencoba', 'berlatih', 'bersabar',
        'percaya', 'berharap', 'berdoa', 'bersyukur', 'maafkan', 'terima', 'kasih', 'selamat',
        'halo', 'hei', 'apa', 'bagaimana', 'kapan', 'dimana', 'siapa', 'kenapa', 'mengapa', 'apakah',
        'semua', 'beberapa', 'banyak', 'sedikit', 'cukup', 'lebih', 'kurang', 'sama', 'berbeda', 'lain',
        'setiap', 'tiap', 'selalu', 'kadang', 'sering', 'jarang', 'tidak', 'belum', 'sudah', 'akan',
        'dan', 'atau', 'tapi', 'tetapi', 'namun', 'karena', 'sebab', 'jadi', 'maka', 'sehingga',
        'dengan', 'untuk', 'kepada', 'dari', 'tentang', 'antara', 'oleh', 'tanpa', 'selain', 'bagi',
    ];

    /**
     * Generate array kata acak bahasa Indonesia.
     *
     * @param int $count Jumlah kata yang dihasilkan
     * @return array
     */
    public static function generateWords(int $count = 200): array
    {
        $words = self::$wordList;
        // Shuffle lalu ambil $count kata
        // Jika $count > jumlah kata unik, ulangi daftar kata
        $result = [];
        while (count($result) < $count) {
            $shuffled = $words;
            shuffle($shuffled);
            $result = array_merge($result, $shuffled);
        }
        return array_slice($result, 0, $count);
    }

    /**
     * Hitung hasil typing test dari data yang dikirim siswa.
     *
     * @param array $data
     * @return array
     */
    public static function calculateResult(array $data): array
    {
        $wordsGenerated  = trim($data['words_generated']);
        $wordsTyped      = trim($data['words_typed'] ?? '');
        $durationSeconds = (int) ($data['duration_seconds'] ?? 60);
        $targetWpm       = (int) ($data['target_wpm'] ?? 30);
        $weightAccuracy  = (int) ($data['weight_accuracy'] ?? 60);
        $weightSpeed     = (int) ($data['weight_speed'] ?? 40);

        // ─── 1. Split per spasi ─────────────────────────────
        $genWords    = $wordsGenerated ? explode(' ', $wordsGenerated) : [];
        $typedWords  = $wordsTyped     ? explode(' ', $wordsTyped)     : [];

        // ─── 2. Hitung words_correct ────────────────────────
        $wordsCorrect = 0;
        foreach ($typedWords as $i => $word) {
            if (isset($genWords[$i]) && $word === $genWords[$i]) {
                $wordsCorrect++;
            }
        }

        // ─── 3. Hitung character-level accuracy ─────────────
        // Gabungkan kata dengan spasi untuk perbandingan karakter
        $genString    = implode(' ', $genWords);
        $typedString  = implode(' ', $typedWords);
        $charsTotal   = mb_strlen($typedString);
        $charsCorrect = 0;
        $charsWrong   = 0;

        for ($i = 0; $i < $charsTotal; $i++) {
            $typedChar = mb_substr($typedString, $i, 1);
            $genChar   = mb_substr($genString, $i, 1);
            if ($genChar !== '' && $typedChar === $genChar) {
                $charsCorrect++;
            } else {
                $charsWrong++;
            }
        }

        // ─── 4. WPM ─────────────────────────────────────────
        $minutes = $durationSeconds > 0 ? ($durationSeconds / 60) : 1;
        $wpm     = round($wordsCorrect / $minutes, 2);

        // ─── 5. Accuracy ─────────────────────────────────────
        $accuracy = 0.0;
        if ($charsTotal > 0) {
            $accuracy = min(round(($charsCorrect / $charsTotal) * 100, 2), 100);
        }

        // ─── 6. Speed Score ──────────────────────────────────
        $speedScore = $targetWpm > 0
            ? min($wpm / $targetWpm, 1.0) * 100
            : 0;

        // ─── 7. Final Score ──────────────────────────────────
        $finalScore = round(
            ($accuracy * $weightAccuracy / 100) + ($speedScore * $weightSpeed / 100),
            2
        );

        return [
            'characters_correct' => $charsCorrect,
            'characters_wrong'   => $charsWrong,
            'characters_total'   => $charsTotal,
            'words_correct'      => $wordsCorrect,
            'wpm'                => $wpm,
            'accuracy'           => $accuracy,
            'final_score'        => $finalScore,
        ];
    }
}
