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
@endphp

<style>
  @media print {
      .report-footer {
          position: absolute !important;
          bottom: 1cm !important;
          left: 0 !important;
          right: 0 !important;
          text-align: center !important;
          font-size: 7pt !important;
          color: #9ca3af !important;
          font-style: italic !important;
      }
  }

  @media screen {
      .report-footer {
          text-align: center;
          font-size: 7pt;
          color: #9ca3af;
          font-style: italic;
          padding-top: 8px;
      }
  }
</style>
@php
  // Kelompokkan mapel per kategori
  $grouped = ['umum' => [], 'kejuruan' => [], 'muatan_sekolah' => [], 'pilihan' => []];
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

@if(($reportType ?? 'semester') === 'mid')
@php
  // Nama lengkap wali kelas
  $homeroom = $class->homeroomTeacher ?? null;
  $homeroomName = null;
  if ($homeroom) {
      $parts = [];
      if ($homeroom->title_ahead)
          $parts[] = trim($homeroom->title_ahead);
      $parts[] = trim($homeroom->name);
      $homeroomName = implode(' ', $parts);
      if ($homeroom->title_behind)
          $homeroomName .= ', ' . trim($homeroom->title_behind);
  }

  $semesterLabel = $semester % 2 === 1 ? 'Ganjil' : 'Genap';

  // Nilai UTS per mapel
  $groupedMid = [
      'umum'           => [],
      'kejuruan'       => [],
      'muatan_sekolah' => [],
      'pilihan'        => [],
  ];
  foreach ($data as $row) {
      $cat = $row['subject']->category ?? 'umum';
      if (!isset($groupedMid[$cat])) $groupedMid[$cat] = [];
      $groupedMid[$cat][] = $row;
  }

  // Rekapitulasi: hanya nilai UTS
  $allUts = array_map(
      fn($row) => $row['grades']['uts'],
      $data
  );
  $allUts = array_filter($allUts, fn($v) => $v !== null);
  $jumlahUts  = count($allUts) > 0 ? array_sum($allUts) : null;
  $rataUts    = count($allUts) > 0
      ? round($jumlahUts / count($allUts), 2) : null;

  // Kop surat
  $borderStyle = $configs['letterhead_border_style'] ?? 'double';
  $borderCss = match($borderStyle) {
      'single' => 'border-bottom: 2px solid #000;',
      'thick'  => 'border-bottom: 4px solid #000;',
      default  => 'border-bottom: 3px double #000;',
  };

  // Kehadiran
  $attendance = $attendance ?? \App\Models\StudentAttendance
      ::where('student_id', $student->id)
      ->where('semester', $semester)
      ->where('academic_year', $academicYear)
      ->first();
@endphp

<div class="report-page"
     style="display:flex; flex-direction:column;
            position: relative;">

  {{-- ══ WATERMARK ══ --}}
  @if(($configs['watermark_enabled'] ?? 'off') === 'on' && isset($configs['logo']))
    <div class="report-watermark">
        <img src="{{ asset('storage/' . $configs['logo']) }}" class="report-watermark-img">
    </div>
  @endif

  {{-- flex:1 wrapper mendorong footnote ke bawah --}}
  <div style="flex:1;">

    {{-- ══ KOP SURAT ══ --}}
    <div style="display:table; width:100%;
                padding-bottom:8px; margin-bottom:12px;
                {{ $borderCss }}">

      {{-- Logo --}}
      <div style="display:table-cell; width:75px; vertical-align:middle;">
        @if(isset($configs['logo']) && $configs['logo'])
          @php
            $logoPath = storage_path('app/public/' . $configs['logo']);
            $logoSrc = asset('storage/' . $configs['logo']);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
                $logoSrc = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . $logoBase64;
            }
          @endphp
          <img src="{{ $logoSrc }}"
               style="width:65px; height:65px; object-fit:contain;">
        @endif
      </div>

      {{-- Teks kop --}}
      <div style="display:table-cell; vertical-align:middle;
                  text-align:center; padding:0 10px;">

        @if(!empty($configs['letterhead_foundation']))
          <p style="margin:0; font-size:9pt;
                    text-transform:uppercase;
                    line-height:1.3;">
            {{ $configs['letterhead_foundation'] }}
          </p>
        @endif

        <h1 style="margin:2px 0; font-size:15pt;
                   font-weight:bold;
                   text-transform:uppercase;
                   line-height:1.2;">
          {{ $configs['school_name'] ?? 'NAMA SEKOLAH' }}
        </h1>

        @if(!empty($configs['letterhead_program']))
          <p style="margin:1px 0; font-size:9pt;
                    font-weight:bold;
                    text-transform:uppercase;
                    line-height:1.3;">
            PROGRAM KEAHLIAN :
            {{ $configs['letterhead_program'] }}
          </p>
        @endif

        @if(!empty($configs['letterhead_email'])
            || !empty($configs['letterhead_website']))
          <p style="margin:1px 0; font-size:8pt;
                    line-height:1.3; color:#333;">
            @if(!empty($configs['letterhead_email']))
              Pos-El : {{ $configs['letterhead_email'] }}
            @endif
            @if(!empty($configs['letterhead_email'])
                && !empty($configs['letterhead_website']))
              &nbsp;|&nbsp;
            @endif
            @if(!empty($configs['letterhead_website']))
              Laman : {{ $configs['letterhead_website'] }}
            @endif
          </p>
        @endif

        <p style="margin:1px 0; font-size:8.5pt;
                  line-height:1.3;">
          {{ $configs['school_address'] ?? '' }}
        </p>
      </div>
    </div>

    {{-- ══ JUDUL ══ --}}
    <div style="text-align:center; margin-bottom:12px;">
      <h2 style="margin:0; font-size:12pt; font-weight:bold; text-transform:uppercase; line-height:1.2;">
        HASIL PENILAIAN SUMATIF TENGAH SEMESTER
      </h2>
      <p style="margin:2px 0; font-size:11pt; font-weight:bold;">
        {{ strtoupper($semesterLabel) }} - {{ strtoupper($configs['school_name'] ?? '') }}
      </p>
      <p style="margin:0; font-size:10pt; font-style:italic;">
        Tahun Pelajaran {{ $configs['academic_year'] ?? $academicYear }}
      </p>
    </div>

    {{-- ══ IDENTITAS SISWA ══ --}}
    <div style="margin-bottom:10px;">
      <table style="width:100%; border-collapse:collapse; font-size:10pt; font-weight:bold;">
        <tr>
          <td style="width:60px; padding:1px 0;">NAMA</td>
          <td style="width:10px; text-align:center;">:</td>
          <td>{{ strtoupper($student->name) }}</td>
        </tr>
        <tr>
          <td style="padding:1px 0;">NIS</td>
          <td style="text-align:center;">:</td>
          <td>{{ $student->nis ?? '-' }}</td>
        </tr>
        <tr>
          <td style="padding:1px 0;">KELAS</td>
          <td style="text-align:center;">:</td>
          <td>{{ $class->name }}</td>
        </tr>
      </table>
    </div>
  <p style="margin:2px 0; font-size:10pt;">
        telah mengikuti Penilaian Sumatif Tengah Semester
        (PSTS) {{ $semesterLabel }} dengan hasil sebagai berikut
      </p>

    {{-- ══ TABEL NILAI ══ --}}
    <table style="width:100%; border-collapse:collapse;
                  font-size:9pt; margin-bottom:6px;">
      <colgroup>
        <col style="width:5%">
        <col style="width:87%">
        <col style="width:8%">
      </colgroup>
      <thead>
        <tr style="background:#f0f0f0; font-weight:bold;
                   text-align:center;">
          <th style="border:1px solid #000; padding:3px;">NO</th>
          <th style="border:1px solid #000; padding:3px;
                     text-align:left;">MATA PELAJARAN</th>
          <th style="border:1px solid #000; padding:3px;">NILAI</th>
        </tr>
      </thead>
      <tbody>
      @php
        $catLabelsMid = [
          'umum'           => 'MATA PELAJARAN UMUM',
          'kejuruan'       => 'MATA PELAJARAN KEJURUAN',
          'muatan_sekolah' => 'MATA PELAJARAN MUATAN SEKOLAH',
          'pilihan'        => 'MATA PELAJARAN PILIHAN',
        ];
        $catKeysMid = ['umum'=>'A','kejuruan'=>'B',
                    'muatan_sekolah'=>'C','pilihan'=>'D'];
        $noMid = array_fill_keys(
            array_keys($groupedMid), 1
        );
      @endphp
      @foreach($catKeysMid as $cat => $labelCat)
        @if(!empty($groupedMid[$cat]))
        <tr style="background:#f0f0f0; font-weight:bold;">
          <td style="border:1px solid #000;
                     text-align:center; padding:2px;">
            {{ $labelCat }}
          </td>
          <td colspan="2"
              style="border:1px solid #000; padding:2px 6px;">
            {{ $catLabelsMid[$cat] }}
          </td>
        </tr>
        @foreach($groupedMid[$cat] as $row)
          @php
            $nilaiUts = $row['grades']['uts'];
            $nilaiTampil = $nilaiUts !== null
                ? (int) round($nilaiUts) : '';
          @endphp
          <tr>
            <td style="border:1px solid #000;
                       text-align:center; padding:2px;">
              {{ $noMid[$cat]++ }}
            </td>
            <td style="border:1px solid #000;
                       padding:2px 6px;">
              {{ $row['subject']->name }}
            </td>
            <td style="border:1px solid #000;
                       text-align:center; padding:2px;
                       font-weight:bold;">
              {{ $nilaiTampil }}
            </td>
          </tr>
        @endforeach
        @endif
      @endforeach

      {{-- Rekapitulasi --}}
      <tr style="background:#f0f0f0; font-weight:bold;">
        <td style="border:1px solid #000;
                   text-align:center; padding:2px;">E</td>
        <td colspan="2"
            style="border:1px solid #000; padding:2px 6px;">
          REKAPITULASI PENILAIAN
        </td>
      </tr>
      <tr>
        <td style="border:1px solid #000;
                   text-align:center; padding:2px;">1</td>
        <td style="border:1px solid #000; padding:2px 6px;">
          Jumlah Nilai
        </td>
        <td style="border:1px solid #000;
                   text-align:center; padding:2px;
                   font-weight:bold;">
          {{ $jumlahUts !== null
              ? number_format($jumlahUts, 0) : '-' }}
        </td>
      </tr>
      <tr>
        <td style="border:1px solid #000; padding:2px;"></td>
        <td style="border:1px solid #000; padding:2px 6px;">
          Rata-Rata
        </td>
        <td style="border:1px solid #000;
                   text-align:center; padding:2px;
                   font-weight:bold;">
          {{ $rataUts !== null
              ? number_format($rataUts, 2, ',', '.') : '-' }}
        </td>
      </tr>

      {{-- Kehadiran --}}
      <tr style="background:#f0f0f0; font-weight:bold;">
        <td style="border:1px solid #000;
                   text-align:center; padding:2px;">F</td>
        <td colspan="2"
            style="border:1px solid #000; padding:2px 6px;">
          KEHADIRAN
        </td>
      </tr>
      <tr>
        <td style="border:1px solid #000;
                   text-align:center; padding:2px;">1</td>
        <td style="border:1px solid #000; padding:2px 6px;">
          Sakit
        </td>
        <td style="border:1px solid #000;
                   text-align:center; padding:2px;">
          {{ $attendance?->sick_days ?? '-' }}
        </td>
      </tr>
      <tr>
        <td style="border:1px solid #000;
                   text-align:center; padding:2px;">2</td>
        <td style="border:1px solid #000; padding:2px 6px;">
          Izin
        </td>
        <td style="border:1px solid #000;
                   text-align:center; padding:2px;">
          {{ $attendance?->permit_days ?? '-' }}
        </td>
      </tr>
      <tr>
        <td style="border:1px solid #000;
                   text-align:center; padding:2px;">3</td>
        <td style="border:1px solid #000; padding:2px 6px;">
          Tanpa Keterangan
        </td>
        <td style="border:1px solid #000;
                   text-align:center; padding:2px;">
          {{ $attendance?->alpha_days ?? '-' }}
        </td>
      </tr>
      </tbody>
    </table>

  </div>{{-- end flex:1 --}}

  {{-- ══ TANDA TANGAN ══ --}}
  <table style="width:100%; border:none;
                border-collapse:collapse;
                font-size:10pt; margin-top:8px;">
    <tr>
      <td style="border:none; width:48%;
                 text-align:center; vertical-align:top;">
        {{-- Spacer to align with date line on the right --}}
        <p style="margin:0; visibility:hidden;">&nbsp;</p>
        <p style="margin:0; font-weight:bold;">
          Wali Murid,
        </p>
        <div style="height:60px;"></div>
        <p style="margin:0;">
          ( ...................................... )
        </p>
      </td>
      <td style="border:none; width:4%;"></td>
      <td style="border:none; width:48%;
                 text-align:center; vertical-align:top;">
        <p style="margin:0;">
          @if($configs['school_city'] ?? null)
            {{ $configs['school_city'] }},
          @endif
          {{ \Carbon\Carbon::now()->locale('id')
              ->translatedFormat('d F Y') }}
        </p>
        <p style="margin:2px 0;">
          Wali Kelas {{ $class->name }},
        </p>
        <div style="height:60px; display:flex;
                    align-items:center;
                    justify-content:center;">
          @if($homeroom && $homeroom->signature && $homeroom->is_signature_active)
            @php
              $sigPath = storage_path('app/public/signatures/' . $homeroom->signature);
              $sigSrc = asset('storage/signatures/' . $homeroom->signature);
              if (file_exists($sigPath)) {
                  $sigBase64 = base64_encode(file_get_contents($sigPath));
                  $sigSrc = 'data:image/' . pathinfo($sigPath, PATHINFO_EXTENSION) . ';base64,' . $sigBase64;
              }
            @endphp
            <img src="{{ $sigSrc }}"
                 style="max-height:60px; max-width:140px;
                        object-fit:contain;">
          @endif
        </div>
        <p style="margin:0; font-weight:bold;">
          ( {{ $homeroomName ?? '________________' }} )
        </p>
      </td>
    </tr>
  </table>

  </div>
</div>
@else
  {{-- ══════════════════════════════════════════ --}}
  {{-- HALAMAN 1: PENILAIAN HASIL BELAJAR        --}}
  {{-- ══════════════════════════════════════════ --}}
  <div class="report-page bg-white relative font-serif text-black leading-tight" 
     style="display: flex;
            flex-direction: column;">

  {{-- ══ WATERMARK ══ --}}
  @if(($configs['watermark_enabled'] ?? 'off') === 'on' && isset($configs['logo']))
    <div class="report-watermark">
        <img src="{{ asset('storage/' . $configs['logo']) }}" class="report-watermark-img">
    </div>
  @endif

  {{-- ══ CONTENT WRAPPER ══ --}}
  <div style="flex: 1;">
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
      @php $no = ['umum'=>1, 'kejuruan'=>1, 'muatan_sekolah'=>1, 'pilihan'=>1]; @endphp
      @foreach(['umum'=>'A', 'kejuruan'=>'B', 'muatan_sekolah'=>'C', 'pilihan'=>'D'] as $cat => $label)
        @if(!empty($grouped[$cat]))
          <tr class="bg-gray-100 font-bold">
            <td class="border border-black text-center p-1">{{ $label }}</td>
            <td colspan="8" class="border border-black px-2 py-1 uppercase text-center">
              @php
                $catLabels = [
                  'umum'           => 'MATA PELAJARAN UMUM',
                  'kejuruan'       => 'MATA PELAJARAN KEJURUAN',
                  'muatan_sekolah' => 'MATA PELAJARAN MUATAN SEKOLAH',
                  'pilihan'        => 'MATA PELAJARAN PILIHAN',
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
  </div>

  {{-- ══ FOOTNOTE ══ --}}
  <div class="report-footer">
    {{ $footnote }}
  </div>
</div>
<div class="report-page bg-white relative font-serif text-black leading-tight" 
     style="display: flex;
            flex-direction: column;">

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
  {{-- ══ CONTENT WRAPPER ══ --}}
  <div style="flex: 1;">
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


  <table style="width:100%; border-collapse:collapse;
                margin-top:8px; font-size:10pt;">

    {{-- BARIS 1: Orang Tua (kiri) + Wali Kelas (kanan) --}}
    <tr style="vertical-align:top; text-align:center;">

      {{-- Kolom 1: Orang Tua / Wali Siswa --}}
      <td style="width:50%; padding:0 8px;
                 text-align:center; vertical-align:top;">
        <p style="margin:0;">
          {{-- spacer setinggi baris kota+tanggal --}}
          &nbsp;
        </p>
        <p style="margin:2px 0; font-weight:bold;
                  text-transform:uppercase;">
          Orang Tua / Wali Siswa,
        </p>
        <div style="height:75px;"></div>
        <p style="margin:0;">
          ....................................
        </p>
      </td>

      {{-- Kolom 2: Wali Kelas --}}
      <td style="width:50%; padding:0 8px;
                 text-align:center; vertical-align:top;">
        <p style="margin:0;">
          {{ $configs['school_city']
              ?? $configs['school_village'] ?? '' }},
          {{ \Carbon\Carbon::now()->locale('id')
              ->translatedFormat('d F Y') }}
        </p>
        <p style="margin:2px 0; font-weight:bold;
                  text-transform:uppercase;">
          Wali Kelas,
        </p>
        <div style="height:75px; display:flex;
                    align-items:center;
                    justify-content:center;">
          @if($homeroom && $homeroom->signature
              && $homeroom->is_signature_active)
            @php
              $sigPath = storage_path('app/public/signatures/'
                  . $homeroom->signature);
              $sigSrc = asset('storage/signatures/'
                  . $homeroom->signature);
              if (file_exists($sigPath)) {
                  $sigBase64 = base64_encode(
                      file_get_contents($sigPath));
                  $sigSrc = 'data:image/'
                      . pathinfo($sigPath, PATHINFO_EXTENSION)
                      . ';base64,' . $sigBase64;
              }
            @endphp
            <img src="{{ $sigSrc }}"
                 style="max-height:75px; max-width:140px;
                        object-fit:contain;">
          @endif
        </div>
        <p style="margin:0; font-weight:bold;
                  text-decoration:underline;">
          {{ $homeroomName ?? '' }}
        </p>
        @if($homeroom)
          <p style="margin:0;">
            {{ $homeroom->nip
                ? 'NIP. ' . $homeroom->nip
                : ($homeroom->niy
                    ? 'NIY. ' . $homeroom->niy
                    : '') }}
          </p>
        @endif
      </td>

    </tr>

    {{-- BARIS 2: Kepala Sekolah (tengah) — hanya bukan mid --}}
    @if(!$isMid)
    <tr>
      <td colspan="2" style="text-align:center;
                              padding-top:16px;">
        <table style="margin:0 auto; border-collapse:collapse;">
          <tr>
            <td style="text-align:center; padding:0 8px;
                       min-width:180px; vertical-align:top;">
              <p style="margin:0; font-weight:bold;
                        text-transform:uppercase;">
                Kepala Sekolah,
              </p>
              <div style="height:75px; display:flex;
                          align-items:center;
                          justify-content:center;">
                @if($principal && $principal->signature
                    && $principal->is_signature_active)
                  @php
                    $pSigPath = storage_path('app/public/signatures/'
                        . $principal->signature);
                    $pSigSrc = asset('storage/signatures/'
                        . $principal->signature);
                    if (file_exists($pSigPath)) {
                        $pSigBase64 = base64_encode(
                            file_get_contents($pSigPath));
                        $pSigSrc = 'data:image/'
                            . pathinfo($pSigPath, PATHINFO_EXTENSION)
                            . ';base64,' . $pSigBase64;
                    }
                  @endphp
                  <img src="{{ $pSigSrc }}"
                       style="max-height:75px; max-width:140px;
                              object-fit:contain;">
                @endif
              </div>
              <p style="margin:0; font-weight:bold;
                        text-decoration:underline;">
                {{ $principalName ?? '' }}
              </p>
              @if($principal)
                <p style="margin:0;">
                  {{ $principal->nip
                      ? 'NIP. ' . $principal->nip
                      : ($principal->niy
                          ? 'NIY. ' . $principal->niy
                          : '') }}
                </p>
              @endif
            </td>
          </tr>
        </table>
      </td>
    </tr>
    @endif

  </table>
  </div>

  {{-- ══ FOOTNOTE ══ --}}
  <div class="report-footer">
    {{ $footnote }}
  </div>
</div>
@endif
