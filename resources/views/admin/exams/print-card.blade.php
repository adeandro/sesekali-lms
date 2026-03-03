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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

        /* Standalone Print CSS Reset */
        html, body { 
            height: auto !important; 
            overflow: visible !important; 
            position: static !important;
            background: #f8fafc !important;
            color: #1e293b !important;
            margin: 0 !important;
            padding: 0 !important;
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        @page {
            size: A4;
            margin: 1cm 0.5cm;
        }

        /* Responsive Grid for A4 (2 columns) */
        .print-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 10px;
            padding: 10px;
        }

        .exam-card {
            background: white;
            border: 1px dashed #cbd5e1;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            break-inside: avoid;
            page-break-inside: avoid;
            min-height: 14cm;
            position: relative;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
        }

        /* Component Styling */
        .card-header {
            text-align: center;
            border-bottom: 2px solid #ef4444; /* Red accent for results */
            padding-bottom: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .school-name {
            font-size: 0.75rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .exam-label {
            font-size: 1.25rem;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.025em;
        }

        .student-info {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .photo-box {
            width: 2cm;
            height: 2.5cm;
            border: 2px solid #f1f5f9;
            background: #f8fafc;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 0.75rem;
        }

        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .details-box {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .label {
            font-size: 0.6rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .value {
            font-size: 0.875rem;
            font-weight: 700;
            color: #334155;
            line-height: 1.2;
        }

        /* Result Section */
        .result-section {
            background: #f8fafc;
            border-radius: 1rem;
            padding: 1rem;
            border: 1px solid #e2e8f0;
        }

        .score-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .score-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.5rem;
            background: white;
            border-radius: 0.75rem;
            border: 1px solid #f1f5f9;
        }

        .score-label {
            font-size: 0.55rem;
            font-weight: 900;
            color: #94a3b8;
            text-transform: uppercase;
        }

        .score-value {
            font-size: 1.125rem;
            font-weight: 800;
            color: #475569;
        }

        .final-score-box {
            grid-column: span 2;
            padding: 1rem;
            background: #fff;
            border: 2px solid #ef4444; /* Default accent */
            border-radius: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .final-score-box.passed {
            border-color: #22c55e;
        }

        .final-score-value {
            font-size: 2.5rem;
            font-weight: 900;
            line-height: 1;
        }

        .status-badge {
            margin-top: 0.25rem;
            font-size: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .passed-text { color: #16a34a; }
        .failed-text { color: #dc2626; }

        .rules-box {
            font-size: 0.65rem;
            color: #64748b;
            line-height: 1.5;
            padding: 0.75rem;
            border-top: 1px solid #f1f5f9;
            margin-top: auto;
        }

        .rules-title {
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
            color: #475569;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 0.5rem;
            padding-top: 1rem;
            border-top: 1px dotted #cbd5e1;
        }

        .sig-item {
            text-align: center;
        }

        .sig-label {
            font-size: 0.65rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 2.5rem;
        }

        .sig-line {
            font-size: 0.75rem;
            font-weight: 800;
            color: #1e293b;
            border-bottom: 1px solid #1e293b;
            display: inline-block;
            min-width: 80%;
            padding-bottom: 2px;
        }

        /* Screen Only Controls */
        @media screen {
            .no-print-toolbar {
                max-width: 1000px;
                margin: 2rem auto;
                background: white;
                padding: 1.5rem;
                border-radius: 1.5rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            }
            .print-grid {
                max-width: 1000px;
                margin: 0 auto 3rem;
                gap: 1.5rem;
            }
        }

        @media print {
            .no-print-toolbar { display: none !important; }
            body { background: white !important; }
            .exam-card { 
                box-shadow: none !important;
                border-color: #000 !important;
                border-radius: 0 !important;
            }
            .result-section { border-color: #000 !important; background: white !important; }
            .score-item { border-color: #000 !important; }
            .final-score-box { border-color: #000 !important; }
        }
    </style>
</head>
<body>

    <!-- Navigation Bar (Screen Only) -->
    <div class="no-print-toolbar">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-600 flex items-center justify-center text-white shadow-lg shadow-red-100">
                <i class="fas fa-file-invoice text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-black text-gray-900 leading-none">LAPORAN HASIL UJIAN</h1>
                <p class="text-xs font-bold text-gray-400 mt-1 uppercase">{{ $exam->title }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() }}" class="px-6 py-2.5 bg-white border border-gray-100 text-gray-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <button onclick="window.print()" class="px-6 py-2.5 bg-red-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-red-700 transition shadow-lg shadow-red-100">
                <i class="fas fa-print mr-2"></i> Cetak Sekarang
            </button>
        </div>
    </div>

    <!-- Printable Area -->
    <div class="print-grid">
        @foreach($students as $data)
            @php
                $isPassed = $data['score'] >= ($exam->subject->kkm ?? 75);
                $accentColor = $isPassed ? 'text-green-600' : 'text-red-600';
            @endphp
            <div class="exam-card">
                <div class="card-header">
                    <div class="school-name">{{ $configs['school_name'] ?? 'SESEKALI CBT' }}</div>
                    <div class="exam-label">LAPORAN HASIL UJIAN</div>
                </div>

                <div class="student-info">
                    <div class="photo-box">
                        @if($data['student']->photo)
                            <img src="{{ $data['student']->photo_url }}" alt="Foto">
                        @else
                            <div class="flex flex-col items-center text-[#cbd5e1] font-black uppercase text-[8px]">
                                <i class="fas fa-user-circle text-4xl mb-1"></i>
                                <span>Pas Foto</span>
                            </div>
                        @endif
                    </div>
                    <div class="details-box">
                        <div class="detail-item">
                            <span class="label">NAMA LENGKAP</span>
                            <span class="value">{{ $data['student']->name }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">NIS / ID SISWA</span>
                            <span class="value">{{ $data['student']->nis }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">KELAS / ROMBEL</span>
                            <span class="value">{{ $data['student']->grade }} - {{ $data['student']->class_group }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">MATA PELAJARAN</span>
                            <span class="value">{{ $exam->subject->name }} (KKM: {{ $exam->subject->kkm ?? 75 }})</span>
                        </div>
                    </div>
                </div>

                <!-- Scores -->
                <div class="result-section">
                    <div class="score-grid">
                        <div class="score-item">
                            <span class="score-label">Bobot PG ({{ $exam->weight_pg }}%)</span>
                            <span class="score-value">{{ $data['is_submitted'] ? 'Aktif' : 'N/A' }}</span>
                        </div>
                        <div class="score-item">
                            <span class="score-label">Bobot Esai ({{ $exam->weight_essay }}%)</span>
                            <span class="score-value">{{ $data['is_submitted'] ? 'Aktif' : 'N/A' }}</span>
                        </div>
                        <div class="final-score-box {{ $isPassed ? 'passed' : '' }}">
                            <span class="score-label">NILAI AKHIR</span>
                            <span class="final-score-value {{ $accentColor }}">{{ number_format($data['score'], 0) }}</span>
                            <div class="status-badge {{ $accentColor }}">
                                {{ $isPassed ? '● TUNTAS' : '● REMIDIAL' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rules-box">
                    <div class="rules-title">Keterangan:</div>
                    <p>Laporan ini merupakan hasil rekapitulasi sistem secara otomatis. Nilai akhir dihitung berdasarkan pembobotan Pilihan Ganda dan Esai yang telah ditentukan.</p>
                </div>

                <!-- Signature -->
                <div class="signature-grid">
                    <div class="sig-item">
                        <div class="sig-label">Siswa Peserta,</div>
                        <div class="sig-line">{{ $data['student']->name }}</div>
                    </div>
                    <div class="sig-item">
                        <div class="sig-label">Administrator,</div>
                        <div class="sig-line">Panitia Ujian</div>
                    </div>
                </div>

                <div class="mt-4 text-[8px] text-gray-400 text-center uppercase font-bold tracking-tighter">
                    Dicetak Otomatis oleh {{ $configs['school_name'] ?? 'SESEKALI CBT' }} - {{ now()->format('d/m/Y H:i') }}
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
