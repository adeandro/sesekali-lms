@extends('layouts.app')

@section('title', 'Cetak Kartu Ujian - ' . $exam->title)

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6 no-print">
            <h1 class="text-3xl font-bold">Kartu Ujian - {{ $exam->title }}</h1>
            <button onclick="window.print()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-print mr-2"></i>Cetak
            </button>
        </div>

        <!-- Exam Card for each student -->
        @foreach($students as $index => $data)
            <div class="p-8 bg-white border-2 border-gray-800 rounded-lg shadow-lg exam-card">
                <!-- Header -->
                <div class="text-center mb-6 border-b-3 border-gray-800 pb-4">
                    <h2 class="text-2xl font-bold text-gray-900">KARTU UJIAN</h2>
                    <p class="text-gray-600 mt-1">ID Sekolah: SesekaliCBT-{{ str_pad($data['student']->id, 5, '0', STR_PAD_LEFT) }}</p>
                </div>

                <!-- Student Information -->
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-gray-700 text-sm font-bold">NAMA SISWA</p>
                        <p class="text-lg font-bold text-gray-900 mt-1">{{ $data['student']->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-700 text-sm font-bold">NIS</p>
                        <p class="text-lg font-bold text-gray-900 mt-1">{{ $data['student']->nis }}</p>
                    </div>
                    <div>
                        <p class="text-gray-700 text-sm font-bold">KELAS</p>
                        <p class="text-lg font-bold text-gray-900 mt-1">{{ $data['student']->class ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-700 text-sm font-bold">TANGGAL LAHIR</p>
                        <p class="text-lg font-bold text-gray-900 mt-1">________________</p>
                    </div>
                </div>

                <!-- Exam Details -->
                <div class="bg-gray-100 p-3 rounded mb-4 border-l-4 border-blue-700">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-gray-700 font-bold">JUDUL UJIAN</p>
                            <p class="text-gray-900 font-semibold mt-1">{{ $exam->title }}</p>
                        </div>
                        <div>
                            <p class="text-gray-700 font-bold">MATA PELAJARAN</p>
                            <p class="text-gray-900 font-semibold mt-1">{{ $exam->subject->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-700 font-bold">DURASI</p>
                            <p class="text-gray-900 font-semibold mt-1">{{ $exam->duration_minutes }} Menit</p>
                        </div>
                        <div>
                            <p class="text-gray-700 font-bold">JUMLAH SOAL</p>
                            <p class="text-gray-900 font-semibold mt-1">{{ $exam->total_questions }} Soal</p>
                        </div>
                        <div>
                            <p class="text-gray-700 font-bold">TANGGAL UJIAN</p>
                            <p class="text-gray-900 font-semibold mt-1">{{ $exam->start_time->translatedFormat('d F Y') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-700 font-bold">JAM UJIAN</p>
                            <p class="text-gray-900 font-semibold mt-1">{{ $exam->start_time->format('H:i') }} - {{ $exam->end_time->format('H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Score Section -->
                <div class="bg-green-100 p-3 rounded mb-4 border-l-4 border-green-700">
                    <div class="grid grid-cols-3 gap-3">
                        <div class="text-center">
                            <p class="text-gray-700 text-sm font-bold">NILAI AKHIR</p>
                            <p class="text-3xl font-bold text-green-700 mt-1">{{ intval($data['score']) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-700 text-sm font-bold">STATUS</p>
                            <p class="text-lg font-bold text-green-700 mt-1">{{ $data['score'] >= 70 ? 'LULUS' : 'TIDAK LULUS' }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-700 text-sm font-bold">KETERANGAN</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">
                                {{ $data['score'] >= 90 ? 'Sangat Baik' : ($data['score'] >= 80 ? 'Baik' : ($data['score'] >= 70 ? 'Cukup' : 'Kurang')) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Instructions Section -->
                <div class="bg-yellow-100 p-2 rounded mb-4 border-l-4 border-yellow-700 text-xs">
                    <p class="text-gray-700 font-bold mb-1">PERATURAN:</p>
                    <ul class="text-gray-800 space-y-0">
                        <li>✓ Simpan kartu ini - merupakan bukti selesai ujian</li>
                        <li>✓ Laporkan jika ada kesalahan nilai ke adminstrasi ujian</li>
                        <li>✓ Kartu berlaku hingga akhir tahun ajaran</li>
                    </ul>
                </div>

                <!-- Signature Section -->
                <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t-2 border-gray-800 text-center text-xs">
                    <div>
                        <p class="text-gray-700 font-bold mb-3">Tanda Tangan Siswa</p>
                        <div style="border-top: 1px solid #000; height: 30px;"></div>
                        <p class="text-gray-600 text-xs mt-1">(__________________)</p>
                    </div>
                    <div>
                        <p class="text-gray-700 font-bold mb-3">Pengawas</p>
                        <div style="border-top: 1px solid #000; height: 30px;"></div>
                        <p class="text-gray-600 text-xs mt-1">(__________________)</p>
                    </div>
                    <div>
                        <p class="text-gray-700 font-bold mb-3">Kepala Sekolah</p>
                        <div style="border-top: 1px solid #000; height: 30px;"></div>
                        <p class="text-gray-600 text-xs mt-1">(__________________)</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-3 pt-2 border-t border-gray-300">
                    <p class="text-xs text-gray-500">SesekaliCBT - Sistem Ujian Berbasis Komputer</p>
                    <p class="text-xs text-gray-500">{{ now()->locale('id')->translatedFormat('d F Y H:i') }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <style>
        /* Screen styles */
        body {
            background-color: #f3f4f6;
        }

        .no-print {
            display: block;
        }

        .exam-card {
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Print styles */
        @media print {
            * {
                margin: 0;
                padding: 0;
            }

            html, body {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                background: white;
                font-size: 11pt;
            }

            .max-w-4xl {
                max-width: 100% !important;
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .exam-card {
                display: block;
                page-break-after: always;
                page-break-inside: avoid;
                break-after: page;
                break-inside: avoid;
                margin: 0;
                padding: 15mm;
                width: 100%;
                min-height: 330mm;
                box-sizing: border-box;
                border: 2px solid #000;
                background: white;
                box-shadow: none;
                font-size: 11pt;
                line-height: 1.4;
                page-break-before: auto;
            }

            .exam-card:last-child {
                page-break-after: avoid;
            }

            /* Remove unnecessary styling during print */
            .shadow-lg {
                box-shadow: none !important;
            }

            /* Ensure text is black */
            h2, p, span, div {
                color: #000 !important;
                background: transparent !important;
            }

            /* Remove rounded corners for cleaner print */
            .rounded-lg {
                border-radius: 0 !important;
            }

            /* Adjust grid for print */
            .grid {
                display: grid !important;
            }

            /* Make text smaller for print preview */
            .text-3xl {
                font-size: 16pt !important;
            }

            .text-2xl {
                font-size: 14pt !important;
            }

            .text-lg {
                font-size: 12pt !important;
            }

            .text-sm {
                font-size: 10pt !important;
            }

            .text-xs {
                font-size: 9pt !important;
            }
        }

        @page {
            size: 210mm 330mm;
            margin: 0;
            padding: 0;
        }
    </style>
@endsection
