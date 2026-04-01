<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soal {{ $exam->title }}</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

        html, body {
            height: auto !important;
            overflow: visible !important;
            position: static !important;
            background: #fff !important;
            color: #1a1a1a !important;
            margin: 0 !important;
            padding: 0 !important;
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        * { box-sizing: border-box; }

        @media screen {
            body {
                background: #e5e7eb !important;
                padding: 20px !important;
            }
            .page-wrapper {
                background: white;
                box-shadow: 0 4px 24px rgba(0,0,0,0.12);
                border-radius: 4px;
                min-height: 100vh;
            }
        }

        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .page-wrapper {
                max-width: 100%;
                padding: 0 !important;
                box-shadow: none !important;
            }
        }

        @page {
            size: A4;
            margin: 30mm 30mm 20mm 30mm;
        }

        .page-wrapper {
            width: 100%;
            max-width: 720px;
            margin: 0 auto;
            padding: 24px 60px;
            box-sizing: border-box;
        }

        /* Quill content rendering */
        .question-text p,
        .option-row p { margin: 0; display: inline; }
        .question-text img { max-width: 280px; max-height: 180px;
                             display: block; margin: 6px 0; }
        .option-row img    { max-width: 180px; max-height: 100px;
                             display: block; margin: 2px 0; }
        .question-text strong, .option-row strong { font-weight: 700; }
        .question-text em,     .option-row em     { font-style: italic; }

        /* ── KOP SEKOLAH ── */
        .kop {
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 3px double #1a1a1a;
            padding-bottom: 10px;
            margin-bottom: 6px;
        }
        .kop img {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        .kop-text {
            flex: 1;
            text-align: center;
        }
        .kop-text .foundation {
            font-size: 10pt;
            font-weight: normal;
            text-transform: uppercase;
        }
        .kop-text .school-name {
            font-size: 18pt;
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: 1px;
        }
        .kop-text .program {
            font-size: 10pt;
        }
        .kop-text .address {
            font-size: 9pt;
            color: #444;
        }

        /* ── SECTION SOAL ── */
        .section-title {
            font-weight: 700;
            font-size: 12pt;
            margin: 20px 0 10px 0;
            text-transform: uppercase;
        }
        .section-instruction {
            font-size: 11pt;
            font-style: italic;
            margin-bottom: 12px;
        }

        /* ── SOAL PG ── */
        .question-item {
            break-inside: avoid;
            page-break-inside: avoid;
            margin-bottom: 14px;
        }

        .pg-columns {
            column-count: 2;
            column-gap: 24px;
            column-rule: 1px solid #e5e7eb;
        }

        /* Gambar responsive dalam kolom */
        .question-text img,
        .option-row img,
        .question-image,
        .option-image {
            max-width: 100% !important;
            height: auto !important;
            display: block;
            margin: 4px 0;
        }
        .question-number-row {
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }
        .question-number {
            font-weight: 700;
            min-width: 24px;
        }
        .question-body {
            flex: 1;
        }
        .question-text {
            margin-bottom: 6px;
            line-height: 1.6;
        }
        .question-image {
            max-width: 300px;
            max-height: 200px;
            margin: 6px 0;
            display: block;
        }
        .options {
            margin-left: 8px;
        }
        .option-row {
            display: flex;
            gap: 8px;
            margin-bottom: 3px;
            align-items: flex-start;
        }
        .option-label {
            font-weight: 600;
            min-width: 20px;
        }
        .option-image {
            max-width: 200px;
            max-height: 120px;
            margin: 2px 0;
            display: block;
        }

        /* ── SOAL ESSAY ── */
        .essay-item {
            margin-bottom: 18px;
            page-break-inside: avoid;
        }
        .answer-lines {
            margin-top: 8px;
            margin-left: 28px;
        }
        .answer-line {
            border-bottom: 1px dotted #aaa;
            height: 22px;
            margin-bottom: 2px;
        }

        /* ── FOOTER ── */
        .exam-footer {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }
        .signature-block {
            text-align: center;
            min-width: 180px;
        }
        .signature-block .sign-label {
            font-size: 11pt;
            margin-bottom: 50px;
        }
        .signature-block .sign-name {
            font-weight: 700;
            border-top: 1px solid #1a1a1a;
            padding-top: 4px;
            font-size: 11pt;
        }

        /* ── PRINT BUTTON (tidak ikut print) ── */
        .print-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(79,70,229,0.3);
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
            z-index: 100;
        }
        .print-btn:hover { background: #4338ca; }

        @media print {
            .print-btn { display: none !important; }
            .page-wrapper { padding: 0; }
        }
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">
    <i class="fas fa-print"></i> Cetak Soal
</button>

<div class="page-wrapper">

    {{-- ── KOP SEKOLAH ── --}}
    <div class="kop">
        @if(!empty($configs['logo']))
            <img src="{{ asset('storage/' . $configs['logo']) }}"
                 alt="Logo">
        @endif
        <div class="kop-text">
            <div class="foundation">
                {{ $configs['letterhead_foundation'] ?? '' }}
            </div>
            <div class="school-name">
                {{ strtoupper($configs['school_name'] ?? 'SMK') }}
            </div>
            <div class="program">
                @php
                    $programDisplay = $configs['letterhead_program']
                        ?? $configs['kompetensi_keahlian']
                        ?? $configs['program_studi']
                        ?? '';
                @endphp
                @if($programDisplay)
                    Kompetensi Keahlian: {{ $programDisplay }}
                @endif
            </div>
            <div class="address">
                {{ $configs['school_address'] ?? '' }}
                @if(!empty($configs['letterhead_email']))
                    &nbsp;|&nbsp; {{ $configs['letterhead_email'] }}
                @endif
                @if(!empty($configs['letterhead_website']))
                    &nbsp;|&nbsp; {{ $configs['letterhead_website'] }}
                @endif
            </div>
        </div>
    </div>

    {{-- ── HEADER UJIAN ── --}}
    <div style="margin: 14px 0 0 0; text-align: center;">

        {{-- Tahun Pelajaran di bawah judul --}}
        <div style="font-size: 13pt; font-weight: 700;
                    text-transform: uppercase; letter-spacing: 1px;
                    margin-bottom: 4px;">
            Soal
            @switch($exam->exam_type)
                @case('harian')  Ulangan Harian @break
                @case('uts')     Ujian Tengah Semester @break
                @case('pts')     Penilaian Tengah Semester @break
                @case('uas')     Ujian Akhir Semester @break
                @case('pas')     Penilaian Akhir Semester @break
                @case('latihan') Latihan @break
                @default {{ ucfirst($exam->exam_type) }}
            @endswitch
        </div>
        <div style="font-size: 10pt; margin-bottom: 14px; color: #444;">
            Tahun Pelajaran: {{ $exam->academic_year ?? ($configs['academic_year'] ?? '-') }}
        </div>

        {{-- 2 kolom: kiri = Mapel+Kelas, kanan = Hari+Waktu --}}
        <table style="margin: 0 auto; border-collapse: collapse;
                      font-size: 11pt; width: auto;">
            <tr>
                <td style="padding: 2px 8px; font-weight: 600;
                           text-align: right;">Mata Pelajaran</td>
                <td style="padding: 2px 4px;">:</td>
                <td style="padding: 2px 16px 2px 4px;
                           text-align: left; border-right: 1px solid #ccc;">
                    {{ $subject->name ?? '-' }}
                </td>
                <td style="padding: 2px 8px; font-weight: 600;
                           text-align: right;">Hari / Tanggal</td>
                <td style="padding: 2px 4px;">:</td>
                <td style="padding: 2px 4px; text-align: left;">
                    @if($exam->start_time)
                        {{ \Carbon\Carbon::parse($exam->start_time)
                            ->locale('id')
                            ->translatedFormat('l, d F Y') }}
                    @else
                        ___________________
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 2px 8px; font-weight: 600;
                           text-align: right;">Kelas</td>
                <td style="padding: 2px 4px;">:</td>
                <td style="padding: 2px 16px 2px 4px;
                           text-align: left; border-right: 1px solid #ccc;">
                    {{ $exam->jenjang ?? '-' }}
                </td>
                <td style="padding: 2px 8px; font-weight: 600;
                           text-align: right;">Waktu</td>
                <td style="padding: 2px 4px;">:</td>
                <td style="padding: 2px 4px; text-align: left;">
                    {{ $exam->duration_minutes }} Menit
                </td>
            </tr>
        </table>
    </div>

    {{-- ── BAGIAN A: PILIHAN GANDA ── --}}
    @if($pgQuestions->count() > 0)
    <div class="section-title">A. Pilihan Ganda</div>
    <div class="section-instruction">
        Pilihlah satu jawaban yang paling tepat dengan memberi tanda
        silang (X) pada huruf a, b, c, d, atau e!
    </div>

    <div class="pg-columns">
    @foreach($pgQuestions as $index => $question)
    <div class="question-item">
        <div class="question-number-row">
            <span class="question-number">{{ $index + 1 }}.</span>
            <div class="question-body">
                <div class="question-text">
                    @php
                        $qt = $question->question_text;
                        $isHtml = preg_match('/^\s*<(p|div|h[1-6]|ul|ol|table|br|span|strong|em)/i', $qt);
                    @endphp
                    @if($isHtml)
                        {!! $qt !!}
                    @else
                        {!! nl2br(htmlspecialchars($qt ?? '', ENT_QUOTES, 'UTF-8')) !!}
                    @endif
                </div>
                @if($question->question_image)
                    <img src="{{ asset('storage/' . $question->question_image) }}"
                         class="question-image" alt="Gambar soal">
                @endif

                <div class="options">
                    @foreach(['a','b','c','d','e'] as $opt)
                        @php
                            $optText  = $question->{'option_'.$opt};
                            $optImage = $question->{'option_'.$opt.'_image'};
                        @endphp
                        @if($optText || $optImage)
                        <div class="option-row">
                            <span class="option-label">{{ $opt }}.</span>
                            <div>
                                @if($optText)
                                    @php
                                        $isOptHtml = preg_match('/^\s*<(p|div|span|strong|em)/i', $optText ?? '');
                                    @endphp
                                    @if($isOptHtml)
                                        {!! $optText !!}
                                    @else
                                        {!! htmlspecialchars($optText ?? '', ENT_QUOTES, 'UTF-8') !!}
                                    @endif
                                @endif
                                @if($optImage)
                                    <img src="{{ asset('storage/' . $optImage) }}"
                                         class="option-image" alt="Opsi {{ $opt }}">
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endforeach
    </div>{{-- end pg-columns --}}
    @endif

    {{-- ── BAGIAN B: ESSAY ── --}}
    @if($essayQuestions->count() > 0)
    <div class="section-title"
         style="margin-top: 28px; page-break-before: always;">
      B. Essay
    </div>
    <div class="section-instruction">
        Jawablah pertanyaan berikut dengan benar dan lengkap!
    </div>

    @foreach($essayQuestions as $index => $question)
    <div class="essay-item">
        <div class="question-number-row">
            <span class="question-number">{{ $index + 1 }}.</span>
            <div class="question-body">
                <div class="question-text">
                    @php
                        $qt = $question->question_text;
                        $isHtml = preg_match('/^\s*<(p|div|h[1-6]|ul|ol|table|br|span|strong|em)/i', $qt);
                    @endphp
                    @if($isHtml)
                        {!! $qt !!}
                    @else
                        {!! nl2br(htmlspecialchars($qt ?? '', ENT_QUOTES, 'UTF-8')) !!}
                    @endif
                </div>
                @if($question->question_image)
                    <img src="{{ asset('storage/' . $question->question_image) }}"
                         class="question-image" alt="Gambar soal">
                @endif
                {{-- Garis jawaban (5 baris) --}}
                <div class="answer-lines">
                    @for($i = 0; $i < 5; $i++)
                        <div class="answer-line"></div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
    @endforeach
    </div>{{-- end pg-columns --}}
    @endif

    {{-- ── FOOTER / TTD ── --}}

</div>

</body>
</html>
