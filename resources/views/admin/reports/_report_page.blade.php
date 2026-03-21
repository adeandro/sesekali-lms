@once
@php
if (!function_exists('numberToWords')) {
    function numberToWords(int $n): string {
        if ($n===0) return 'Nol';
        $ones=['','Satu','Dua','Tiga','Empat','Lima','Enam',
       'Tujuh','Delapan','Sembilan','Sepuluh','Sebelas',
       'Dua Belas','Tiga Belas','Empat Belas','Lima Belas',
       'Enam Belas','Tujuh Belas','Delapan Belas','Sembilan Belas'];
        $tens=['','','Dua Puluh','Tiga Puluh','Empat Puluh','Lima Puluh',
       'Enam Puluh','Tujuh Puluh','Delapan Puluh','Sembilan Puluh'];
        if ($n<20)  return $ones[$n];
        if ($n<100) return $tens[(int)($n/10)]
            .($n%10!=0?' '.$ones[$n%10]:'');
        if ($n<200) return 'Seratus'
            .($n%100!=0?' '.numberToWords($n%100):'');
        if ($n<1000) return $ones[(int)($n/100)].'ratus'
            .($n%1000!=0?' '.numberToWords($n%100):''); // Fixed recursion call
        if ($n<2000) return 'Seribu'
            .($n%1000!=0?' '.numberToWords($n%1000):'');
        if ($n<1000000) return numberToWords((int)($n/1000))
            .' ribu'.($n%1000!=0?' '.numberToWords($n%1000):'');
        return (string)$n;
    }

    function numberToWordsDecimal($n): string {
        $n = round($n, 2);
        $parts = explode('.', (string)$n);
        $intPart = (int)$parts[0];
        $decimalPart = isset($parts[1]) ? $parts[1] : '';

        $res = numberToWords($intPart);

        if ($decimalPart !== '') {
            $res .= ' Koma';
            $ones = ['Nol', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan'];
            for ($i = 0; $i < strlen($decimalPart); $i++) {
                $digit = (int)$decimalPart[$i];
                $res .= ' ' . $ones[$digit];
            }
        }

        return $res;
    }

    function formatNilai($v, $useDecimal = false) {
        if ($v === null) return '-';
        if (!$useDecimal) return round($v);
        
        $formatted = number_format($v, 2, ',', '.');
        if (str_contains($formatted, ',')) {
            $formatted = rtrim(rtrim($formatted, '0'), ',');
        }
        return $formatted;
    }

    function formatTerbilang($v, $useDecimal = false) {
        if ($v === null) return '';
        if (!$useDecimal) return numberToWords((int)round($v));
        return numberToWordsDecimal($v);
    }
}
@endphp
@endonce

@php
  // Helper nama lengkap wali kelas
  $homeroom = $class->homeroomTeacher ?? null;
  $homeroomName = null;
  if ($homeroom) {
      $parts = [];
      if ($homeroom->title_ahead) $parts[] = trim($homeroom->title_ahead);
      $parts[] = trim($homeroom->name);
      $homeroomName = implode(' ', $parts);
      if ($homeroom->title_behind) $homeroomName .= ', ' . trim($homeroom->title_behind);
  }

  // Deteksi jenjang dari model
  $gradeLevel = $class->getGradeLevel();

  // Semester absolut (1-6) → relatif per kelas (1 = ganjil, 2 = genap)
  // Hitung index absolut: ((tingkat - 10) * 2) + semester_sekarang
  $absSemester = (($gradeLevel - 10) * 2) + $semester;
  
  $semesterMap = [
      1 => ['grade' => 'X',   'label' => 'Ganjil', 'sem' => 1],
      2 => ['grade' => 'X',   'label' => 'Genap',  'sem' => 2],
      3 => ['grade' => 'XI',  'label' => 'Ganjil', 'sem' => 1],
      4 => ['grade' => 'XI',  'label' => 'Genap',  'sem' => 2],
      5 => ['grade' => 'XII', 'label' => 'Ganjil', 'sem' => 1],
      6 => ['grade' => 'XII', 'label' => 'Genap',  'sem' => 2],
  ];
  $sMap = $semesterMap[$absSemester] ?? ['grade' => '?', 'label' => '?', 'sem' => '?'];
  
  $semesterLabel = "{$sMap['grade']} / {$sMap['label']}";
  $semesterRel   = $sMap['sem'];
  $semesterWord  = ($semesterRel == 1 ? 'Satu' : 'Dua');

  $isGenap = ($semester % 2 === 0);
  $isMid   = (($reportType ?? 'semester') === 'mid');

  // Kelompokkan mapel per kategori
  $grouped = ['umum'=>[], 'kejuruan'=>[], 'muatan_sekolah'=>[]];
  foreach ($data as $row) {
      $cat = $row['subject']->category ?? 'umum';
      if (!isset($grouped[$cat])) $grouped[$cat] = [];
      $grouped[$cat][] = $row;
  }

  // Rekapitulasi (hanya hitung yang ada nilai)
  $finals = array_filter(array_column($data,'final'),
      fn($v) => $v !== null);
  $totalNilai = count($finals) > 0 ? array_sum($finals) : null;
  $rataRata   = count($finals) > 0
      ? round($totalNilai/count($finals), 2) : null;

  // Query data tambahan (Gunakan pre-loaded jika tersedia, fallback ke query jika tidak)
  $attendance = $attendance ?? \App\Models\StudentAttendance
      ::where('student_id', '=', $student->id, 'and')
      ->where('semester', '=', $semester, 'and')
      ->where('academic_year', '=', $academicYear, 'and')->first();

  $personality = $personality ?? \App\Models\StudentPersonality
      ::where('student_id', '=', $student->id, 'and')
      ->where('semester', '=', $semester, 'and')
      ->where('academic_year', '=', $academicYear, 'and')->first();

  $extracurriculars = $extracurriculars ?? \App\Models\StudentExtracurricular
      ::where('student_id', '=', $student->id, 'and')
      ->where('semester', '=', $semester, 'and')
      ->where('academic_year', '=', $academicYear, 'and')->get();

  $footnote = "Raport : " . $student->name . " (" . ($student->nis ?? '-') . ")";
  $useDecimal = ($configs['report_decimal'] ?? '0') == '1';
@endphp

<style>
  /* ── Watermark base ── */
  .report-watermark {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    z-index: 0;
    overflow: hidden;
  }

  .report-watermark-img {
    width: 700px;
    height: 700px;
    object-fit: contain;
    opacity: 0.08;
    user-select: none;
    filter: grayscale(100%);
  }

  /* ── Watermark saat print ── */
  @media print {
    body {
        background-color: white !important;
    }

    .report-watermark {
        display: none !important;
    }

    .report-wrapper:last-child .report-page:last-child {
        page-break-after: avoid !important;
    }

    @page {
        size: A4 portrait;
        margin: 1cm;
    }

    .report-page {
        position: relative;
        min-height: 25cm; /* A4 (29.7cm) - margin atas bawah (2cm) */
        overflow: hidden;   /* potong konten yang keluar */
        background-color: white !important;
    }

    .report-page::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 14cm;
        height: 14cm;
        background-image: var(--watermark-url);
        background-repeat: no-repeat;
        background-position: center center;
        background-size: contain;
        opacity: 0.1;
        pointer-events: none;
        z-index: 0;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
@if(($configs['watermark_enabled'] ?? 'off') === 'on' && isset($configs['logo']))
<style>
    .report-page {
        --watermark-url: url('{{ asset('storage/' . $configs['logo']) }}');
    }
</style>
@endif

{{-- ══════════════════════════════════════════ --}}
{{-- HALAMAN 1: PENILAIAN HASIL BELAJAR        --}}
{{-- ══════════════════════════════════════════ --}}
<div class="report-page bg-white relative font-serif text-black leading-tight" style="page-break-after: always; padding-bottom: 2cm;">

  {{-- ══ WATERMARK ══ --}}
  @if(($configs['watermark_enabled'] ?? 'off') === 'on' && isset($configs['logo']))
    <div class="report-watermark">
        <img src="{{ asset('storage/' . $configs['logo']) }}" class="report-watermark-img">
    </div>
  @endif

  {{-- ══ HEADER ══ --}}
  <div class="mb-6">
  <h1 class="text-[14pt] font-extrabold uppercase mb-4 text-center">LAPORAN PENILAIAN HASIL BELAJAR</h1>
    
    <table class="w-full text-[10pt] border-none leading-normal text-left">
      <tr>
        <td class="py-0.5" style="width: 21%;">Bidang Studi Keahlian</td>
        <td class="py-0.5" style="width: 2%;">:</td>
        <td class="py-0.5" style="width: 40%;">{{ $configs['bidang_studi'] ?? '' }}</td>
        <td class="py-0.5" style="width: 15%;">Tahun Ajaran</td>
        <td class="py-0.5" style="width: 2%;">:</td>
        <td class="py-0.5">{{ $configs['academic_year'] ?? $academicYear }}</td>
      </tr>
      <tr>
        <td class="py-0.5">Program Studi Keahlian</td>
        <td class="py-0.5">:</td>
        <td class="py-0.5">{{ $configs['program_studi'] ?? '' }}</td>
        <td class="py-0.5" style="width: 15%;">Semester</td>
        <td class="py-0.5" style="width: 2%;">:</td>
        <td class="py-0.5">{{ $absSemester }} ({{ numberToWords($absSemester) }}) / {{ $semester % 2 === 1 ? 'ganjil' : 'genap' }}</td>
      </tr>
      <tr>
        <td class="py-0.5">Kompetensi Keahlian</td>
        <td class="py-0.5">:</td>
        <td class="py-0.5">{{ $configs['kompetensi_keahlian'] ?? '' }}</td>
        <td class="py-0.5">Kelas</td>
        <td class="py-0.5">:</td>
        <td class="py-0.5">{{ $class->name ?? '' }}</td>
      </tr>
      <tr>
        <td class="py-0.5">Nama Siswa</td>
        <td class="py-0.5">:</td>
        <td class="py-0.5 font-bold">{{ $student->name }}</td>
        <td class="py-0.5">No Induk</td>
        <td class="py-0.5">:</td>
        <td class="py-0.5">{{ $student->nis ?? '-' }}</td>
      </tr>
    </table>
  </div>

  {{-- ══ TABEL NILAI ══ --}}
  <table class="w-full text-[10pt] border-collapse border border-black mb-4">
    <thead>
      <tr class="font-bold text-center bg-gray-50 uppercase tracking-wider text-[8pt]">
        <th rowspan="2" class="border border-black p-2" style="width: 4%;">No</th>
        <th rowspan="2" class="border border-black p-2" style="width: 23%;">Mata Pelajaran</th>
        <th rowspan="2" class="border border-black p-2" style="width: 6%;">KKM</th>
        <th rowspan="2" class="border border-black p-2" style="width: 6%;">RATA-RATA<br>KELAS</th>
        <th colspan="5" class="border border-black p-2">Penilaian Hasil Belajar</th>
      </tr>
      <tr class="font-bold text-center bg-gray-50 uppercase tracking-wider text-[7pt]">
        <th class="border border-black p-1" style="width: 10%;">Angka</th>
        <th class="border border-black p-1" style="width: 23%;">Huruf</th>
        <th colspan="2" class="border border-black p-1" style="width: 7%;">Predikat</th>
        <th class="border border-black p-1" style="width: 21%;">Deskripsi kemajuan belajar</th>
      </tr>
    </thead>
    <tbody>
      @php $no = ['umum'=>1,'kejuruan'=>1,'muatan_sekolah'=>1]; @endphp
      @foreach(['umum'=>'A','kejuruan'=>'B','muatan_sekolah'=>'C'] as $cat => $label)
        @if(!empty($grouped[$cat]))
          <tr class="bg-gray-100 font-bold">
            <td class="border border-black text-center p-1">{{ $label }}</td>
            <td colspan="8" class="border border-black px-2 py-1 uppercase text-center">
              @php
                $catLabels = [
                  'umum'           => 'MATA PELAJARAN UMUM',
                  'kejuruan'       => 'MATA PELAJARAN KEJURUAN',
                  'muatan_sekolah' => 'MATA PELAJARAN MUATAN SEKOLAH',
                ];
              @endphp
              {{ $catLabels[$cat] }}
            </td>
          </tr>
          @foreach($grouped[$cat] as $row)
            @php
              $final    = $row['final'];
              $kkm      = $row['kkm'];
              $classAvg = $row['class_average'];

              $angka    = formatNilai($final, $useDecimal);
              $huruf    = formatTerbilang($final, $useDecimal);
              $predikat = '';
              $deskripsi = '';
              if ($final !== null) {
                if ($final>=90)     $predikat='A';
                elseif($final>=80)  $predikat='B';
                elseif($final>=70)  $predikat='C';
                elseif($final>=60)  $predikat='D';
                else                $predikat='F';
                $deskripsi = $final>=$kkm ? 'Terlampaui' : 'Harus Diperbaiki';
              }
            @endphp
            <tr>
              <td class="border border-black text-center p-1 font-sans">{{ $no[$cat]++ }}</td>
              <td class="border border-black px-2 py-1 font-bold">{{ $row['subject']->name ?? 'Mapel Terhapus' }}</td>
              <td class="border border-black text-center p-1">{{ $kkm }}</td>
              <td class="border border-black text-center p-1 italic text-gray-500">
                {{ $classAvg !== null ? number_format($classAvg,0) : '' }}
              </td>
              <td class="border border-black text-center p-1 font-bold">{{ $angka }}</td>
              <td class="border border-black px-2 py-1 text-[8pt] italic uppercase">{{ $huruf }}</td>
              <td colspan="2" class="border border-black text-center p-1 font-bold">{{ $predikat }}</td>
              <td class="border border-black px-2 py-1 text-[7pt] leading-tight">{{ $deskripsi }}</td>
            </tr>
          @endforeach
        @endif
      @endforeach

      {{-- Baris kosong pemisah --}}
      <tr><td colspan="9" class="border border-black h-2 bg-gray-50"></td></tr>

      {{-- Rekapitulasi --}}
      <tr class="bg-gray-100 font-bold">
        <td class="border border-black text-center p-1">E</td>
        <td colspan="8" class="border border-black px-2 py-1 uppercase italic bg-gray-200 text-center">REKAPITULASI PENILAIAN</td>
      </tr>
      <tr>
        <td class="border border-black text-center p-1 font-sans">1</td>
        <td colspan="2" class="border border-black px-2 py-1 font-bold">Jumlah Nilai</td>
        <td colspan="2" class="border border-black text-center p-1 font-black italic bg-indigo-50/30">
          {{ formatNilai($totalNilai, $useDecimal) }}
        </td>
        <td colspan="4" class="border border-black px-2 py-1 text-[8pt] italic uppercase text-gray-600">
          {{ formatTerbilang($totalNilai, $useDecimal) }}
        </td>
      </tr>
      <tr>
        <td class="border border-black text-center p-1 font-sans">2</td>
        <td colspan="2" class="border border-black px-2 py-1 font-bold">Nilai Rata-rata</td>
        <td colspan="2" class="border border-black text-center p-1 font-black italic bg-emerald-50/30">
         {{ $rataRata !== null ? rtrim(rtrim(number_format($rataRata, 2, ',', ''), '0'), ',') : '-' }}
        </td>
        <td colspan="4" class="border border-black px-2 py-1 text-[8pt] italic uppercase text-gray-600">
          {{ $rataRata !== null ? formatTerbilang($rataRata, true) : '-' }}
        </td>
      </tr>
    </tbody>
  </table>

  {{-- ══ FOOTNOTE ══ --}}
  <div class="absolute bottom-4 left-0 right-0 text-center text-[7pt] text-gray-400 italic">
    {{ $footnote }}
  </div>
</div><div class="report-page bg-white relative font-serif text-black leading-tight" style="padding-bottom: 2cm;">

  {{-- ══ WATERMARK ══ --}}
  @if(($configs['watermark_enabled'] ?? 'off') === 'on' && isset($configs['logo']))
    <div class="report-watermark">
        <img src="{{ asset('storage/' . $configs['logo']) }}" class="report-watermark-img">
    </div>
  @endif
  @php
    $dudis = $dudis ?? \App\Models\StudentDudi
        ::where('student_id', '=', $student->id, 'and')
        ->where('semester', '=', $semester, 'and')
        ->where('academic_year', '=', $academicYear, 'and')
        ->orderBy('sort_order', 'asc')
        ->get();
    $dudiRows = max($dudis->count(), 3);

    // Kepala Sekolah
    $principal = \App\Models\User::where('role', '=', 'principal', 'and')->first();
    $principalName = null;
    if ($principal) {
        $parts = [];
        if ($principal->title_ahead) $parts[] = trim($principal->title_ahead);
        $parts[] = trim($principal->name);
        $principalName = implode(' ', $parts);
        if ($principal->title_behind)
            $principalName .= ', ' . trim($principal->title_behind);
    }

    // Keputusan kenaikan kelas (Gunakan pre-loaded jika tersedia, fallback ke query jika tidak)
    $promotion = $promotion ?? \App\Models\StudentPromotion
        ::where('student_id', '=', $student->id, 'and')
        ->where('academic_year', '=', $academicYear, 'and')
        ->first();

    // Tingkat kelas berikutnya (otomatis)
    $nextGradeMap = [
        10 => 'XI (Sebelas)',
        11 => 'XII (Dua Belas)',
        12 => 'XII (Dua Belas)',
    ];
    $nextGrade = $nextGradeMap[$gradeLevel] ?? '';
  @endphp
  <div class="text-center mb-6 mt-2">
    <h3 class="text-[14pt] font-extrabold uppercase underline tracking-widest text-slate-800">CATATAN AKHIR SEMESTER</h3>
  </div>
  
  {{-- A. Kegiatan DU/DI --}}
  <div class="mb-4">
    <h4 class="text-[10pt] font-bold mb-1.5 uppercase text-slate-700">A.&nbsp;&nbsp;KEGIATAN DI DU/DI YANG RELEVAN</h4>
    <table class="w-full text-[8pt] border-collapse border border-black">
      <thead>
        <tr class="bg-gray-50 text-center font-bold font-sans">
          <th class="border border-black p-1 w-8">NO</th>
          <th class="border border-black p-1">KEGIATAN</th>
          <th class="border border-black p-1">NAMA / ALAMAT INSTANSI</th>
          <th class="border border-black p-1">WAKTU PELAKSANAAN</th>
          <th class="border border-black p-1 w-12">NILAI</th>
        </tr>
      </thead>
      <tbody>
        @for($i = 0; $i < $dudiRows; $i++)
        <tr>
          <td class="border border-black text-center p-1 h-5 font-sans">{{ $i + 1 }}</td>
          <td class="border border-black p-1">{{ isset($dudis[$i]) ? ($dudis[$i]->activity_name ?? '') : '' }}</td>
          <td class="border border-black p-1">
            {{ isset($dudis[$i]) ? trim(($dudis[$i]->institution_name ?? '') . ' ' . ($dudis[$i]->institution_address ?? '')) : '' }}
          </td>
          <td class="border border-black p-1">{{ isset($dudis[$i]) ? ($dudis[$i]->period ?? '') : '' }}</td>
          <td class="border border-black p-1 text-center font-bold">{{ isset($dudis[$i]) ? ($dudis[$i]->grade ?? '') : '' }}</td>
        </tr>
        @endfor
      </tbody>
    </table>
  </div>

  {{-- B. Kegiatan Pengembangan Diri --}}
  <div class="mb-3">
    <h4 class="text-[10pt] font-bold mb-1.5 uppercase text-slate-700">B.&nbsp;&nbsp;KEGIATAN PENGEMBANGAN DIRI</h4>
    <table class="w-full text-[8pt] border-collapse border border-black leading-relaxed">
      <thead>
        <tr class="bg-gray-50 text-center font-bold font-sans uppercase tracking-tight">
          <th class="border border-black p-1.5 w-1/3">KOMPONEN</th>
          <th class="border border-black p-1.5">KETERANGAN</th>
          <th class="border border-black p-1.5 w-24">PREDIKAT</th>
        </tr>
      </thead>
      <tbody>
        <tr class="font-bold underline italic">
          <td colspan="3" class="border border-black px-2 py-0.5 uppercase tracking-tighter text-center">KEGIATAN EKSTRA KURIKULER</td>
        </tr>
        @php $ekskulCount = max($extracurriculars->count(), 3); @endphp
        @for($i=0; $i<$ekskulCount; $i++)
          <tr>
            <td class="border border-black px-2 p-1 font-sans">@if($i===0) - @endif</td>
            <td class="border border-black px-2 p-1 font-bold italic uppercase tracking-tighter">
              {{ isset($extracurriculars[$i]) ? ($extracurriculars[$i]->name ?? '') : '' }}
            </td>
            <td class="border border-black text-center p-1 font-black underline">
              {{ isset($extracurriculars[$i]) ? ($extracurriculars[$i]->grade ?? '') : '' }}
            </td>
          </tr>
        @endfor

        <tr class="font-bold underline italic">
          <td colspan="3" class="border border-black px-2 py-0.5 uppercase tracking-tighter text-center">KEPRIBADIAN</td>
        </tr>
        <tr>
          <td class="border border-black px-2 p-1">Kedisiplinan</td>
          <td class="border border-black"></td>
          <td class="border border-black text-center p-1 font-bold underline">{{ $personality?->discipline ?? '' }}</td>
        </tr>
        <tr>
          <td class="border border-black px-2 p-1">Kelakuan</td>
          <td class="border border-black"></td>
          <td class="border border-black text-center p-1 font-bold underline">{{ $personality?->behavior ?? '' }}</td>
        </tr>
        <tr>
          <td class="border border-black px-2 p-1">Kerapian</td>
          <td class="border border-black"></td>
          <td class="border border-black text-center p-1 font-bold underline">{{ $personality?->neatness ?? '' }}</td>
        </tr>

        <tr class="font-bold underline italic">
          <td colspan="3" class="border border-black px-2 py-0.5 uppercase tracking-tighter text-center">KEHADIRAN</td>
        </tr>
        <tr>
          <td class="border border-black px-2 p-1">Sakit</td>
          <td class="border border-black"></td>
          <td class="border border-black text-center p-1 font-sans italic">{{ $attendance?->sick_days ?? 0 }} hari</td>
        </tr>
        <tr>
          <td class="border border-black px-2 p-1">Ijin</td>
          <td class="border border-black"></td>
          <td class="border border-black text-center p-1 font-sans italic">{{ $attendance?->permit_days ?? 0 }} hari</td>
        </tr>
        <tr>
          <td class="border border-black px-2 p-1">Tanpa Keterangan</td>
          <td class="border border-black"></td>
          <td class="border border-black text-center p-1 font-sans italic">{{ $attendance?->alpha_days ?? 0 }} hari</td>
        </tr>
      </tbody>
    </table>
  </div>

  {{-- C. Catatan Wali Kelas --}}
  <div class="mb-4">
    <h4 class="text-[10pt] font-bold mb-1 uppercase">C.&nbsp;&nbsp;CATATAN SEKOLAH / WALI KELAS</h4>
    <div class="border border-black p-2 text-[9pt] italic min-h-[50px] relative font-sans">
      {{ $note?->note ?? '' }}
      @if($ranking['rank'])
        <div class="absolute bottom-1 right-2 text-[8pt] font-bold uppercase tracking-widest text-indigo-900 border-b-2 border-indigo-100">
          Peringkat {{ $ranking['rank'] }} Dari {{ $ranking['total'] }} Siswa
        </div>
      @endif
    </div>
  </div>

  {{-- ══ TANDA TANGAN ══ --}}
<div class="w-full grid {{ ($isGenap && !$isMid) ? 'grid-cols-2' : 'grid-cols-1' }} gap-4 mb-6">
  {{-- Kiri: selalu tampil --}}
  <div class="pl-8 text-[10pt]">
    <div>Diberikan di &nbsp;: {{ $configs['school_village'] ?? $configs['school_city'] ?? '' }}</div>
    <div>Pada Tanggal &nbsp;: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</div>
  </div>

  {{-- Kanan: hanya semester genap & bukan mid --}}
  @if($isGenap && !$isMid)
  <div class="border border-black p-2 text-[9pt] leading-relaxed">
    <div class="font-bold mb-1">Keputusan :</div>
    <div>Dengan memperhatikan hasil yang dicapai pada</div>
    <div>Semester Ganjil dan Genap, Tahun Ajaran {{ $academicYear }}</div>
    <div class="mt-2">
      Maka ditetapkan&nbsp;
      <span class="font-black uppercase underline tracking-widest">
        {{ $promotion?->decision ?? 'NAIK' }}
      </span>
    </div>
    <div>pada tingkat : <span class="font-bold">{{ $nextGrade }}</span></div>
  </div>
  @endif
</div>


  {{-- ══ FOOTNOTE ══ --}}
  <div class="absolute bottom-4 left-0 right-0 text-center text-[7pt] text-gray-400 italic">
    {{ $footnote }}
  </div>

  {{-- ══ SIGNATURES ══ --}}
  <table class="w-full text-[10pt] mt-6 border-none" style="border-collapse: collapse; width: 100%;">
    <tr style="text-align: center;">
      {{-- Kolom 1: Orang Tua / Wali Siswa --}}
      <td style="width: {{ $isMid ? '50%' : '33%' }}; vertical-align: top; padding: 0 10px; text-align: center;">
        <p class="font-bold uppercase tracking-widest leading-relaxed">Orang Tua / Wali Siswa,</p>
        <div class="h-20 my-2"></div>
        <p class="font-bold">....................................</p>
      </td>

      {{-- Kolom 2: Wali Kelas --}}
      <td style="width: {{ $isMid ? '50%' : '33%' }}; vertical-align: top; padding: 0 10px; text-align: center;">
        <p class="font-bold uppercase tracking-widest leading-relaxed">Wali Kelas,</p>
        <div class="h-20 w-full flex items-center justify-center my-2">
          @if($homeroom && $homeroom->signature_url && $homeroom->is_signature_active)
            <img src="{{ $homeroom->signature_url }}"
                 class="h-20 w-auto object-contain mix-blend-multiply opacity-95 mx-auto">
          @endif
        </div>
        <p class="font-bold uppercase underline">{{ $homeroomName ?? '' }}</p>
        @if($homeroom)
          <p class="text-[9pt] font-sans text-center">
            {{ $homeroom->nip ? 'NIP. ' . $homeroom->nip : ($homeroom->niy ? 'NIY. ' . $homeroom->niy : '') }}
          </p>
        @endif
      </td>

      {{-- Kolom 3: Kepala Sekolah (hanya jika bukan mid) --}}
      @if(!$isMid)
      <td style="width: 34%; vertical-align: top; padding: 0 10px; text-align: center;">
        <p class="font-bold uppercase tracking-widest leading-relaxed">Kepala Sekolah,</p>
        <div class="h-20 w-full flex items-center justify-center my-2">
          @if($principal && $principal->signature_url && $principal->is_signature_active)
            <img src="{{ $principal->signature_url }}"
                 class="h-20 w-auto object-contain mix-blend-multiply opacity-95 mx-auto">
          @endif
        </div>
        <p class="font-bold uppercase underline">{{ $principalName ?? '' }}</p>
        @if($principal)
          <p class="text-[9pt] font-sans text-center">
            {{ $principal->nip ? 'NIP. ' . $principal->nip : ($principal->niy ? 'NIY. ' . $principal->niy : '') }}
          </p>
        @endif
      </td>
      @endif
    </tr>
  </table>
</div>
