<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Hasil Ujian - {{ $exam->title }}</title>
    
    <!-- Load Tailwind & FontAwesome for Standalone Layout -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Caveat:wght@700&display=swap');

        /* Standalone Print CSS Reset */
        html, body { 
            height: auto !important; 
            overflow: visible !important; 
            position: static !important;
            background: #fff !important;
            color: #1a1a1a !important;
            margin: 0 !important;
            padding: 0 !important;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        @page {
            size: auto;
            margin: 15mm;
        }

        .report-page {
            background: white;
            padding: 1rem;
            width: 100%;
            box-sizing: border-box;
            min-height: auto;
            display: flex;
            flex-direction: column;
            page-break-after: always;
            break-after: page;
            position: relative;
        }

        /* Handwritten Grade Overlay */
        .handwritten-grade {
            position: absolute;
            top: 4.5rem;
            right: 2.5rem;
            font-family: 'Caveat', cursive;
            font-size: 8rem;
            color: #b91c1c; /* Deep Red Marker Color */
            transform: rotate(-12deg);
            opacity: 0.8;
            line-height: 0.8;
            z-index: 10;
            pointer-events: none;
            filter: drop-shadow(2px 2px 0px rgba(185, 28, 28, 0.1));
            width: 9rem;
            height: 9rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .handwritten-grade::before {
            content: '';
            position: absolute;
            inset: -0.5rem;
            border: 8px solid #b91c1c;
            border-radius: 48% 52% 50% 50% / 55% 45% 55% 45%;
            transform: rotate(5deg);
            box-sizing: border-box;
        }

        /* Kop Surat / Header */
        .kop-header {
            display: flex;
            align-items: center;
            padding-bottom: 0.5rem;
            margin-bottom: 0.75rem;
            border-bottom: 3px double #000;
            position: relative;
        }

        .school-logo {
            width: 4.5rem;
            height: 4.5rem;
            object-fit: contain;
            margin-right: 1.5rem;
        }

        .school-info {
            flex-grow: 1;
            text-align: center;
        }

        .school-info h1 {
            font-size: clamp(1.2rem, 4vw, 1.85rem);
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0;
            line-height: 1.1;
        }

        .school-info p {
            margin-top: 0.25rem;
            font-size: 0.85rem;
            color: #374151;
            font-weight: 500;
            line-height: 1.4;
        }

        .report-title {
            text-align: center;
            margin-bottom: 1rem;
            position: relative;
        }

        .report-title h2 {
            font-size: 1.35rem;
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: 6px;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }

        /* Identity & Photo Section */
        .identity-layout {
            display: flex;
            gap: 2rem;
            margin-bottom: 1rem;
            padding: 0 1rem;
        }

        .identity-container {
            flex-grow: 1;
        }

        .photo-container {
            width: 3cm;
            height: 4cm;
            border: 2px solid #000;
            background: #f9fafb;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .identity-table {
            width: 100%;
            font-size: 1rem;
            border-collapse: collapse;
        }

        .identity-table td {
            padding: 0.25rem 0;
            vertical-align: top;
        }

        .identity-table .label {
            width: 140px;
            font-weight: 600;
            color: #374151;
        }

        .identity-table .colon {
            width: 1.5rem;
            text-align: center;
        }

        .identity-table .value {
            font-weight: 800;
            color: #000;
        }

        /* Results Section */
        .results-container {
            margin-bottom: 0.75rem;
            border: 1.5px solid #000;
            border-radius: 0.65rem;
            overflow: hidden;
            background: #fff;
        }

        .results-header {
            background: #000;
            color: #fff;
            padding: 0.5rem;
            text-align: center;
            font-weight: 900;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .score-display {
            padding: 1rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .final-score-value {
            font-size: clamp(2.5rem, 10vw, 4.5rem);
            font-weight: 900;
            line-height: 1;
            color: #000;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.05em;
        }

        .final-score-label {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .status-pill {
            margin-top: 1rem;
            padding: 0.50rem 2.0rem;
            border-radius: 9999px;
            font-weight: 900;
            font-size: 1.125rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border: 2px solid #000;
        }

        .status-pill.passed {
            background: #dcfce7 !important;
            color: #166534 !important;
            border-color: #166534 !important;
        }

        .status-pill.failed {
            background: #fee2e2 !important;
            color: #991b1b !important;
            border-color: #991b1b !important;
        }

        /* Footer Notes */
        .report-notes {
            margin-top: auto;
            font-size: 0.8125rem;
            line-height: 1.5;
            color: #4b5563;
            background: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px dashed #d1d5db;
        }

        /* Signatures */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 1.5rem;
            padding-bottom: 0.5rem;
        }

        .sig-block {
            text-align: center;
            width: 250px;
        }

        .sig-date {
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }

        .sig-role {
            font-weight: 600;
            margin-bottom: 35px;
        }

        .sig-name {
            font-weight: 800;
            border-bottom: 2px solid #000;
            padding-bottom: 3px;
            display: inline-block;
            min-width: 200px;
        }

        /* Controls */
        @media screen {
            body { 
                background: #f3f4f6 !important; 
                padding: 2rem 0 !important;
            }
            .report-page {
                max-width: 21cm;
                margin: 0 auto 2rem;
                padding: 2cm;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
                border-radius: 0.5rem;
                overflow: hidden;
            }
            .no-print-toolbar {
                max-width: 21cm;
                margin: 0 auto 1.5rem;
                background: white;
                padding: 1rem 2rem;
                border-radius: 1rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                position: sticky;
                top: 1rem;
                z-index: 50;
            }
        }

        @media print {
            .no-print-toolbar { display: none !important; }
            .report-page { margin: 0; border: none; }
        }
    </style>
</head>
<body>

    <!-- Navigation Bar (Screen Only) -->
    <div class="no-print-toolbar">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white shadow-lg transition-transform hover:scale-105">
                <i class="fas fa-file-invoice text-lg"></i>
            </div>
            <div>
                <h1 class="text-sm font-black text-gray-900 tracking-tight leading-none">PREVIEW LAPORAN</h1>
                <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase">{{ $exam->title }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() }}" class="px-5 py-2 bg-white border border-gray-200 text-gray-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-gray-50 transition active:scale-95">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <button onclick="window.print()" class="px-5 py-2 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-black transition shadow-lg active:scale-95">
                <i class="fas fa-print mr-2"></i> Cetak Laporan
            </button>
        </div>
    </div>

    @foreach($students as $data)
        @php
            $score = $data['score'];
            $isPassed = $score >= ($exam->subject->kkm ?? 75);
            
            // Map score to Grade (A-E)
            $gradeLabel = 'E';
            if ($score >= 90) $gradeLabel = 'A';
            elseif ($score >= 80) $gradeLabel = 'B';
            elseif ($score >= 70) $gradeLabel = 'C';
            elseif ($score >= 60) $gradeLabel = 'D';

            // Get school configs
            $configs = \App\Models\Setting::all()->pluck('value', 'key')->toArray();
        @endphp
        <div class="report-page">
            <!-- Handwritten Overlay -->
            <div class="handwritten-grade">{{ $gradeLabel }}</div>

            <!-- Kop Surat -->
            @if(($configs['show_report_header'] ?? '1') == '1')
            <div class="kop-header">
                @if(isset($configs['logo']) && $configs['logo'])
                    <img src="{{ asset('storage/' . $configs['logo']) }}" class="school-logo" alt="Logo">
                @else
                    <div class="school-logo bg-gray-100 flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300">
                        <i class="fas fa-school text-3xl text-gray-300"></i>
                    </div>
                @endif
                <div class="school-info">
                    <h1>{{ $configs['school_name'] ?? 'SESEKALI COMPUTER BASED TEST' }}</h1>
                    <p>{{ $configs['school_address'] ?? 'Alamat Instansi / Sekolah Belum Diatur' }} • WhatsApp: {{ $configs['school_phone'] ?? '-' }}</p>
                    <p class="mt-1 font-bold italic">{{ $configs['report_header_subtitle'] ?? 'Official Exam Results Certificate' }}</p>
                </div>
            </div>
            @endif

            <div class="report-title">
                <h2>LAPORAN HASIL UJIAN HARIAN</h2>
                <p class="text-sm font-bold text-gray-500 tracking-[0.2em] uppercase">Tahun Ajaran {{ date('Y') }}/{{ date('Y') + 1 }}</p>
            </div>

            <!-- Identity Section -->
            <div class="identity-layout">
                <div class="identity-container">
                    <table class="identity-table">
                        <tr>
                            <td class="label">Nama Lengkap</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $data['student']->name }}</td>
                        </tr>
                        <tr>
                            <td class="label">NIS / ID Siswa</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $data['student']->nis }}</td>
                        </tr>
                        <tr>
                            <td class="label">Kelas / Rombel</td>
                            <td class="colon">:</td>
                            <td class="value">Kls {{ $data['student']->grade }} - {{ $data['student']->class_group }}</td>
                        </tr>
                        <tr>
                            <td class="label">Mata Pelajaran</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $exam->subject->name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Jenis Ujian</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $exam->title }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Large Photo -->
                <div class="photo-container">
                    @if($data['student']->photo)
                        <img src="{{ $data['student']->photo_url }}" alt="Foto">
                    @else
                        <div class="flex flex-col items-center text-gray-300">
                            <i class="fas fa-user text-5xl mb-2"></i>
                            <span class="text-[8px] font-bold uppercase tracking-widest">Pas Foto 3X4</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Score Section -->
            <div class="results-container">
                <div class="results-header">REKAPITULASI NILAI AKHIR</div>
                <div class="score-display">
                    <div class="final-score-value">{{ number_format($data['score'], 0) }}</div>
                    <div class="final-score-label">Points (Scale 0-100)</div>
                    
                    <div class="status-pill {{ $isPassed ? 'passed' : 'failed' }}">
                        {{ $isPassed ? 'TUNTAS' : 'REMIDIAL' }}
                    </div>
                </div>
            </div>

            <div class="report-notes">
                <p><strong>Keterangan:</strong></p>
                <ul class="list-disc ml-5 mt-2 space-y-1">
                    <li>Nilai KKM untuk mata pelajaran ini adalah <strong>{{ $exam->subject->kkm ?? 75 }}</strong>.</li>
                    <li>Siswa dinyatakan <strong>TUNTAS</strong> jika nilai akhir lebih besar atau sama dengan KKM.</li>
                    <li>Laporan ini dicetak secara otomatis oleh sistem ujian CBT dan bersifat sah sebagai arsip nilai harian.</li>
                </ul>
            </div>

            <!-- Signature -->
            <div class="signature-section">
                <div class="sig-block">
                    <div class="sig-date">&nbsp;</div>
                    <div class="sig-role">Peserta Ujian,</div>
                    <div class="sig-name">{{ $data['student']->name }}</div>
                </div>
                <div class="sig-block">
                    <div class="sig-date">{{ now()->translatedFormat('d F Y') }}</div>
                    <div class="sig-role">Guru Mata Pelajaran,</div>
                    <div class="sig-name">{{ $teacherName }}</div>
                </div>
            </div>

            <div class="mt-auto pt-8 border-t border-gray-100 text-[10px] text-gray-400 flex justify-between items-end">
                <div>
                    Dokumen ini di-generate pada {{ now()->format('d/m/Y H:i:s') }}
                </div>
                <div class="font-bold tracking-tighter text-slate-300">
                    VERIFIED BY ExamFlow SYSTEM
                </div>
            </div>
        </div>
    @endforeach

</body>
</html>
