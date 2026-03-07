@extends('layouts.app')

@section('title', 'Detail Hasil ' . $exam->title . ' - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Detail Hasil')

@section('content')
    <div class="max-w-7xl mx-auto space-y-8 animate-fadeIn pb-12">
        <!-- Breadcrumbs & Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="flex flex-col gap-2">
                <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
                    <a href="{{ route('admin.results.index') }}" class="hover:text-indigo-600 transition-colors">Hasil Ujian</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span class="text-indigo-600">Detail Hasil</span>
                </nav>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">{{ $exam->title }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $exam->subject->name }}</span>
                    <span class="text-gray-300">•</span>
                    <span class="text-sm font-bold text-gray-500">{{ $exam->attempts->count() }} Percobaan Masuk</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    @php
                        $isAnyAdjusted = $attempts->contains('is_adjusted', true);
                    @endphp
                    @if(!$isAnyAdjusted)
                        <form action="{{ route('admin.results.apply-adjustment', $exam->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center gap-2">
                                <i class="fas fa-magic"></i> Terapkan Penyesuaian
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.results.reset-adjustment', $exam->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-6 py-3 bg-rose-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-rose-700 transition shadow-lg shadow-rose-100 flex items-center gap-2">
                                <i class="fas fa-undo"></i> Kembalikan Nilai Asli
                            </button>
                        </form>
                    @endif
                </div>
                <a href="{{ route('admin.results.export', array_merge(['examId' => $exam->id], request()->all())) }}" class="px-6 py-3 bg-white text-indigo-600 border-2 border-indigo-100 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition shadow-sm flex items-center gap-2">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
                <a href="{{ route('admin.exams.print-card', $exam->id) }}" target="_blank" class="px-6 py-3 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-100 flex items-center gap-2">
                    <i class="fas fa-id-card"></i> Cetak Kartu
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 flex flex-col justify-between overflow-hidden relative group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 mb-6 relative z-10">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Peserta</p>
                    <p class="text-3xl font-black text-gray-900">{{ $stats['total_participants'] }}</p>
                </div>
            </div>
            
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 flex flex-col justify-between overflow-hidden relative group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 mb-6 relative z-10">
                    <i class="fas fa-chart-bar text-xl"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Rata-rata Nilai</p>
                    <p class="text-3xl font-black text-gray-900">{{ round($stats['average_score'], 1) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 flex flex-col justify-between overflow-hidden relative group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 mb-6 relative z-10">
                    <i class="fas fa-trophy text-xl"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nilai Tertinggi</p>
                    <p class="text-3xl font-black text-gray-900">{{ round($stats['highest_score'], 1) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 flex flex-col justify-between overflow-hidden relative group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 mb-6 relative z-10">
                    <i class="fas fa-check-double text-xl"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Tuntas KKM ({{ $exam->subject->kkm ?? 75 }}+)</p>
                    <p class="text-3xl font-black text-gray-900">{{ round($stats['pass_rate'], 1) }}%</p>
                </div>
            </div>
        </div>

        <!-- Info Card: Fair Score Adjustment -->
        <div class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-[2.5rem] shadow-xl shadow-indigo-100 p-8 md:p-10 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-20 -mt-20 blur-3xl group-hover:scale-110 transition-transform duration-700"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-indigo-400/20 rounded-full -ml-10 -mb-10 blur-2xl"></div>
            
            <div class="relative z-10 flex flex-col lg:flex-row gap-10 items-center">
                <div class="flex-1 space-y-6">
                    <div class="inline-flex items-center gap-3 px-4 py-2 bg-white/10 backdrop-blur-md rounded-xl border border-white/20">
                        <i class="fas fa-info-circle text-white"></i>
                        <span class="text-[10px] font-bold text-white uppercase tracking-[0.2em]">Metode Penyesuaian Nilai</span>
                    </div>
                    <h3 class="text-3xl font-black text-white leading-tight">Implementasi Fair Score Adjustment (Metode Akar)</h3>
                    <p class="text-indigo-100 text-lg font-medium leading-relaxed max-w-2xl">
                        Sistem menggunakan rumus <code class="bg-white/20 px-2 py-1 rounded-lg font-black tracking-wider text-white">√Nilai_Asli * 10</code> untuk pemerataan nilai yang adil (dongkrak nilai). Metode ini memberikan peningkatkan lebih besar pada nilai rendah namun tetap menjaga proporsi nilai tinggi secara logaritmis.
                    </p>
                </div>
                
                <div class="w-full lg:w-80 bg-white/10 backdrop-blur-md border border-white/20 rounded-[2rem] p-6 space-y-4">
                    <p class="text-[10px] font-black text-indigo-200 uppercase tracking-widest mb-4">Simulasi Perubahan</p>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl border border-white/10">
                            <span class="text-xs font-bold text-indigo-100">Asli 25</span>
                            <i class="fas fa-arrow-right text-indigo-300 text-[10px]"></i>
                            <span class="text-sm font-black text-white">Jadi 50</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl border border-white/10">
                            <span class="text-xs font-bold text-indigo-100">Asli 49</span>
                            <i class="fas fa-arrow-right text-indigo-300 text-[10px]"></i>
                            <span class="text-sm font-black text-white">Jadi 70</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl border border-white/10">
                            <span class="text-xs font-bold text-indigo-100">Asli 81</span>
                            <i class="fas fa-arrow-right text-indigo-300 text-[10px]"></i>
                            <span class="text-sm font-black text-white">Jadi 90</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-6 md:p-8">
            <form method="GET" class="flex flex-col md:flex-row items-end gap-6">
                <!-- Grade Filter -->
                <div class="w-full md:w-48 space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2 px-2">
                        <i class="fas fa-layer-group"></i> Filter Kelas
                    </label>
                    <select name="class" class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-4 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer">
                        <option value="">Semua Rombel</option>
                        @foreach($classes as $class)
                            <option value="{{ $class['id'] }}" {{ request('class') === $class['id'] ? 'selected' : '' }}>
                                {{ $class['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div class="flex-1 space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2 px-2">
                        <i class="fas fa-search"></i> Cari Siswa
                    </label>
                    <div class="relative group">
                        <input type="text" name="search" placeholder="Masukkan Nama atau NIS..." value="{{ request('search') }}" 
                            class="w-full h-14 bg-gray-50 border-transparent rounded-2xl pl-12 pr-4 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-gray-300">
                        <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-indigo-600 transition-colors"></i>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex items-center gap-2 h-14">
                    <button type="submit" class="h-full px-8 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center gap-2">
                        Filter
                    </button>
                    @if(request('class') || request('search'))
                        <a href="{{ route('admin.results.show', $exam->id) }}" class="h-full px-8 bg-gray-50 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-gray-100 hover:text-gray-600 transition flex items-center gap-2">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Results Table -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                        <i class="fas fa-medal text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Peringkat & Hasil Peserta</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Ditemukan {{ $attempts->count() }} data</p>
                    </div>
                </div>
            </div>

            @if($attempts->isEmpty())
                <div class="p-20 text-center space-y-4">
                    <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center text-gray-200 mx-auto">
                        <i class="fas fa-ghost text-4xl"></i>
                    </div>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest">Tidak ada data yang sesuai filter.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-8 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-24">Rank</th>
                                <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Siswa</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Kelas</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Skor PG</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Skor Esai</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Nilai Murni</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Nilai Penyesuaian</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Status</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Waktu Selesai</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($attempts as $attempt)
                                <tr class="group hover:bg-gray-50 transition-colors {{ $attempt->ranking === 1 ? 'bg-amber-50/20' : '' }}">
                                    <td class="px-8 py-6 text-center">
                                        @if($attempt->ranking === 1)
                                            <div class="w-10 h-10 rounded-xl bg-amber-400 text-white flex items-center justify-center mx-auto shadow-lg shadow-amber-100 animate-pop">
                                                <i class="fas fa-crown text-sm"></i>
                                            </div>
                                        @elseif($attempt->ranking === 2)
                                            <div class="w-10 h-10 rounded-xl bg-gray-300 text-white flex items-center justify-center mx-auto shadow-lg shadow-gray-100">
                                                <i class="fas fa-award text-sm"></i>
                                            </div>
                                        @elseif($attempt->ranking === 3)
                                            <div class="w-10 h-10 rounded-xl bg-orange-300 text-white flex items-center justify-center mx-auto shadow-lg shadow-orange-100">
                                                <i class="fas fa-medal text-sm"></i>
                                            </div>
                                        @else
                                            <span class="text-sm font-black text-gray-300">#{{ $attempt->ranking }}</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-gray-900">{{ $attempt->student->name }}</span>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">NIS: {{ $attempt->student->nis ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="px-3 py-1 bg-gray-50 text-gray-600 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                            {{ $attempt->student->grade }}-{{ $attempt->student->class_group }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="text-sm font-black text-gray-900">{{ round($attempt->score_mc, 1) }}</span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="text-sm font-black {{ $attempt->score_essay > 0 ? 'text-emerald-600' : 'text-gray-300' }}">
                                            {{ round($attempt->score_essay ?? 0, 1) }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="text-sm font-black text-gray-400">{{ round($attempt->final_score, 1) }}</span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        @if($attempt->is_adjusted)
                                            <span class="px-4 py-1.5 rounded-xl text-xs font-black bg-indigo-50 text-indigo-600 border-2 border-indigo-100 flex flex-col items-center">
                                                <span>{{ round($attempt->adjusted_score, 1) }}</span>
                                                <span class="text-[8px] font-black uppercase tracking-tighter">Adjusted</span>
                                            </span>
                                        @else
                                            <span class="text-sm font-black text-gray-300">-</span>
                                        @endif
                                    </td>
                                    @php
                                        $displayScore = $attempt->is_adjusted ? $attempt->adjusted_score : $attempt->final_score;
                                        $kkm = $exam->subject->kkm ?? 75;
                                        $isPass = $displayScore >= $kkm;
                                    @endphp
                                    <td class="px-8 py-6 text-center">
                                        @if($isPass)
                                            <span class="text-[10px] font-black text-emerald-600 bg-emerald-100/50 px-3 py-1 rounded-lg uppercase tracking-widest border border-emerald-200">Tuntas</span>
                                        @else
                                            <span class="text-[10px] font-black text-rose-600 bg-rose-100/50 px-3 py-1 rounded-lg uppercase tracking-widest border border-rose-200">Remidial</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">
                                            {{ $attempt->submitted_at->translatedFormat('d M Y, H:i') }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <a href="{{ route('admin.results.review', [$exam->id, $attempt->id]) }}" 
                                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-white text-indigo-600 border-2 border-indigo-100 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all shadow-sm">
                                            <i class="fas fa-check-circle"></i> Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <style>
        .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
        .animate-pop { animation: pop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pop { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    </style>
@endsection
