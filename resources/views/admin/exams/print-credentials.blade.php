<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Peserta - {{ $exam->title }}</title>
    
    <!-- Load Tailwind & FontAwesome for Standalone Layout -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Standalone Print CSS Reset */
        html, body { 
            height: auto !important; 
            overflow: visible !important; 
            position: static !important;
            background: white !important;
            color: black !important;
            margin: 0 !important;
            padding: 0 !important;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
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
        }

        .exam-card {
            background: white;
            border: 1px dashed #000;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            break-inside: avoid;
            page-break-inside: avoid;
            min-height: 9cm; /* Adjusted for A4 height efficiency */
            position: relative;
        }

        /* Component Styling */
        .card-header {
            text-align: center;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .school-name {
            font-size: 0.75rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .exam-label {
            font-size: 1.125rem;
            font-weight: 900;
            color: #1e293b;
        }

        .card-body {
            display: flex;
            gap: 1rem;
        }

        .photo-box {
            width: 1.5cm;
            height: 2cm;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 4px;
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
            gap: 0.4rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .label {
            font-size: 0.6rem;
            font-weight: 900;
            color: #94a3b8;
            text-transform: uppercase;
        }

        .value {
            font-size: 0.8rem;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
        }

        .credential-box {
            background: #f1f5f9;
            padding: 0.75rem;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            border: 1px solid #e2e8f0;
            margin-top: auto;
        }

        .cred-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .cred-item:first-child {
            border-right: 1px solid #cbd5e1;
        }

        .cred-label {
            font-size: 0.6rem;
            font-weight: 900;
            color: #64748b;
        }

        .cred-value {
            font-size: 0.9rem;
            font-weight: 900;
            color: #0f172a;
            font-family: monospace;
        }

        .highlight {
            color: #4f46e5;
        }

        .card-footer {
            font-size: 0.6rem;
            font-weight: 700;
            color: #94a3b8;
            text-align: center;
            font-style: italic;
            margin-top: 0.5rem;
        }

        /* Screen Only Controls */
        @media screen {
            body {
                background-color: #f1f5f9 !important;
                padding: 2rem !important;
            }
            .print-grid {
                max-width: 1000px;
                margin: 0 auto;
                gap: 1.5rem;
            }
            .exam-card {
                border-style: solid;
                border-color: #e2e8f0;
                border-radius: 1rem;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            }
            .no-print-toolbar {
                max-width: 1000px;
                margin: 0 auto 2rem;
                background: white;
                padding: 1.5rem;
                border-radius: 1.5rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            }
        }

        @media print {
            .no-print-toolbar { display: none !important; }
            .exam-card { 
                box-shadow: none !important;
                border-color: #000 !important;
            }
            .credential-box {
                background: white !important;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Navigation Bar (Screen Only) -->
    <div class="no-print-toolbar">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                <i class="fas fa-id-card text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-black text-gray-900 leading-none">KARTU PESERTA</h1>
                <p class="text-xs font-bold text-gray-400 mt-1 uppercase">{{ $exam->title }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() }}" class="px-6 py-2.5 bg-white border border-gray-100 text-gray-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <button onclick="window.print()" class="px-6 py-2.5 bg-indigo-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                <i class="fas fa-print mr-2"></i> Cetak Sekarang
            </button>
        </div>
    </div>

    <!-- Printable Area -->
    <div class="print-grid">
        @foreach($students as $data)
            <div class="exam-card">
                <div class="card-header">
                    <div class="school-name">{{ $configs['school_name'] ?? 'SESEKALI CBT' }}</div>
                    <div class="exam-label">KARTU PESERTA UJIAN</div>
                </div>

                <div class="card-body">
                    <!-- Photo -->
                    <div class="photo-box">
                        @if(isset($data['student']) && $data['student']->photo)
                            <img src="{{ $data['student']->photo_url }}" alt="Foto">
                        @else
                            <div class="flex flex-col items-center text-[#cbd5e1] font-black uppercase text-[8px]">
                                <i class="fas fa-user-circle text-2xl mb-1"></i>
                                <span>3 x 4</span>
                            </div>
                        @endif
                    </div>

                    <!-- Details -->
                    <div class="details-box">
                        <div class="detail-item">
                            <span class="label">NAMA LENGKAP</span>
                            <span class="value">{{ $data['name'] }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">NOMOR INDUK (NIS)</span>
                            <span class="value">{{ $data['nis'] }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">KELAS / ROMBEL</span>
                            <span class="value">{{ $data['class'] }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">MATA PELAJARAN</span>
                            <span class="value">{{ $exam->subject->name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Credentials -->
                <div class="credential-box">
                    <div class="cred-item">
                        <span class="cred-label uppercase">ID Login</span>
                        <span class="cred-value">{{ $data['nis'] }}</span>
                    </div>
                    <div class="cred-item">
                        <span class="cred-label uppercase">Kata Sandi</span>
                        <span class="cred-value highlight">{{ $data['password'] }}</span>
                    </div>
                </div>

                <div class="card-footer">
                    * Rahasiakan Kata Sandi Anda demi keamanan data.
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
