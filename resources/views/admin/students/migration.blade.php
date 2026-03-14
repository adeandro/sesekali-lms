@extends('layouts.app')

@section('title', 'Migrasi Tahunan - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Migrasi Tahunan')

@section('content')
    <div class="space-y-6 animate-fadeIn">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Migrasi Tahunan Siswa</h2>
                <p class="text-sm text-gray-500">Proses kenaikan kelas masal, kelulusan, dan penanganan siswa tinggal kelas.</p>
            </div>
            <a href="{{ route('admin.students.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all active:scale-95 shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
            </a>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Siswa Aktif</p>
                    <p class="text-xl font-black text-gray-900">{{ number_format($stats['total_active'] ?? 0) }}</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fas fa-graduation-cap text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Kelas 12 (Lulus)</p>
                    <p class="text-xl font-black text-gray-900">{{ number_format($stats['grade_12'] ?? 0) }}</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                    <i class="fas fa-level-up-alt text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Kelas 11 (Naik)</p>
                    <p class="text-xl font-black text-gray-900">{{ number_format($stats['grade_11'] ?? 0) }}</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                    <i class="fas fa-chevron-circle-up text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Kelas 10 (Naik)</p>
                    <p class="text-xl font-black text-gray-900">{{ number_format($stats['grade_10'] ?? 0) }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Student List for Retention -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 border-b border-gray-50 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-user-clock text-amber-500"></i>
                            Seleksi Siswa Tinggal Kelas (Grade 10 & 11)
                        </h3>
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 bg-gray-50 px-2 py-1 rounded-md">
                            {{ $students->count() }} Siswa
                        </span>
                    </div>
                    
                    <form id="migrationForm" action="{{ route('admin.students.migration.execute') }}" method="POST">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Identitas</th>
                                        <th class="px-6 py-4 text-center text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Kelas Saat Ini</th>
                                        <th class="px-6 py-4 text-center text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Status Migrasi</th>
                                        <th class="px-6 py-4 text-center text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Tinggal Kelas?</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 bg-white">
                                    @forelse($students as $student)
                                        <tr class="hover:bg-amber-50/30 transition-colors group">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <img src="{{ $student->photo_url }}" class="w-8 h-8 rounded-lg object-cover">
                                                    <div>
                                                        <div class="text-sm font-bold text-gray-900">{{ $student->formatted_name }}</div>
                                                        <div class="text-[10px] text-gray-400 font-mono tracking-tight">NIS: {{ $student->nis }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600">
                                                    Kelas {{ $student->grade }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <div class="flex flex-col items-center justify-center">
                                                    <span id="label-target-{{ $student->id }}" class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                                        Naik ke Kelas {{ (int)$student->grade + 1 }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="retention_ids[]" value="{{ $student->id }}" 
                                                           onchange="toggleRetentionRow(this, {{ $student->id }}, {{ $student->grade }})"
                                                           class="sr-only peer">
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none ring-offset-2 peer-focus:ring-2 peer-focus:ring-amber-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                                                </label>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <i class="fas fa-user-slash text-4xl text-gray-200"></i>
                                                    <p class="text-gray-500 font-medium italic">Tidak ada siswa Grade 10-11 untuk dimigrasi.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right: Action Sidebar -->
            <div class="space-y-6">
                <!-- Action Panel -->
                <div class="bg-gray-900 rounded-2xl shadow-xl border border-gray-800 p-6 text-white overflow-hidden relative">
                    <div class="absolute -right-10 -bottom-10 opacity-10 rotate-12">
                        <i class="fas fa-graduation-cap text-9xl"></i>
                    </div>
                    
                    <h3 class="text-xl font-black mb-2">Eksekusi Migrasi</h3>
                    <p class="text-gray-400 text-sm mb-6 leading-relaxed">
                        Tindakan ini akan memigrasikan status siswa ke tahun ajaran baru. Grade 12 akan menjadi <b>Alumni</b>, sedangkan Grade 10-11 akan <b>Naik Kelas</b> (kecuali yang dicentang tinggal).
                    </p>

                    <div class="space-y-4 relative z-10">
                        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Konfirmasi Aksi</p>
                            <div class="flex items-center gap-3 mb-4">
                                <i class="fas fa-exclamation-triangle text-rose-500 text-xl"></i>
                                <span class="text-xs text-rose-200 font-bold">OPERASI INI TIDAK DAPAT DIBATALKAN!</span>
                            </div>
                            <button type="button" onclick="confirmMigration()" 
                                    class="w-full py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-black rounded-xl transition-all shadow-lg hover:shadow-indigo-500/50 active:scale-[0.98] uppercase tracking-widest text-xs">
                                EKSEKUSI MIGRASI TAHUNAN
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Recent Logs -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-50 bg-gray-50/50">
                        <h3 class="font-bold text-gray-900 text-sm">Histori Migrasi Terakhir</h3>
                    </div>
                    <div class="p-0">
                        @forelse($recentLogs as $log)
                            <div class="p-4 border-b border-gray-50 last:border-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded uppercase tracking-wider {{ $log->action_type === 'promote' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ $log->actionLabel() }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-medium">{{ $log->executed_at->format('d M Y, H:i') }}</span>
                                </div>
                                <div class="text-xs text-gray-600 leading-relaxed mt-2">
                                    <p>Tahun: <b class="text-gray-900">{{ $log->academic_year }}</b></p>
                                    <p>Siswa: <b class="text-gray-900">{{ $log->affected_count }} orang</b></p>
                                    <div class="mt-1 flex items-center gap-1 text-[10px] text-gray-400 italic">
                                        <i class="fas fa-user-cog"></i>
                                        <span>Oleh: {{ $log->executor->name ?? 'System' }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center">
                                <i class="fas fa-history text-gray-200 text-2xl mb-2"></i>
                                <p class="text-xs text-gray-400">Belum ada riwayat migrasi.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleRetentionRow(checkbox, studentId, currentGrade) {
            const label = document.getElementById('label-target-' + studentId);
            if (checkbox.checked) {
                label.innerHTML = `<i class="fas fa-pause-circle text-[10px]"></i> Tinggal di Kelas ${currentGrade}`;
                label.classList.remove('text-emerald-600');
                label.classList.add('text-amber-600');
            } else {
                label.innerHTML = `<i class="fas fa-arrow-right text-[10px]"></i> Naik ke Kelas ${currentGrade + 1}`;
                label.classList.remove('text-amber-600');
                label.classList.add('text-emerald-600');
            }
        }

        function confirmMigration() {
            Swal.fire({
                title: 'EKSEKUSI MIGRASI?',
                html: `
                    <div class="text-left text-sm space-y-3 p-4 bg-rose-50 border border-rose-100 rounded-xl">
                        <p class="text-rose-800 font-bold uppercase tracking-tight flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle"></i> Peringatan Keras:
                        </p>
                        <ul class="list-disc ml-5 text-rose-700 space-y-1">
                            <li>Kelas 12 akan dipindahkan ke status <b>Alumni</b>.</li>
                            <li>Kelas 10 & 11 akan <b>naik grade</b> secara permanen.</li>
                            <li>Seluruh pemetaan Rombel akan <b>dihapus (NULL)</b>.</li>
                            <li>Data ujian & nilai tetap aman, namun cache akan dibersihkan.</li>
                        </ul>
                        <p class="mt-4 text-rose-900 font-black italic">TINDAKAN INI TIDAK DAPAT DIBATALKAN!</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'YA, EKSEKUSI SEKARANG',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Konfirmasi Terakhir',
                        text: 'Apakah Anda benar-benar yakin? Semua akses siswa akan terupdate sesuai tingkatan baru.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#be123c',
                        confirmButtonText: 'Sangat Yakin, Proses!',
                        cancelButtonText: 'Batal'
                    }).then((finalResult) => {
                        if (finalResult.isConfirmed) {
                            document.getElementById('loading-overlay').style.display = 'block';
                            document.getElementById('migrationForm').submit();
                        }
                    });
                }
            });
        }
    </script>

    <style>
        .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
@endsection
