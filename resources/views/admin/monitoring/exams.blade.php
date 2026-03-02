@extends('layouts.app')

@section('title', 'Monitor Ujian - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Monitor Ujian')

@section('content')
    <div class="max-w-6xl mx-auto space-y-8 animate-fadeIn pb-12">
        <!-- Header & Stats -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="space-y-2">
                <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
                    <a href="{{ route('dashboard.superadmin') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span class="text-indigo-600">Monitor Ujian</span>
                </nav>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight text-indigo-950">Monitor Real-Time</h2>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Pantau aktivitas siswa dan kontrol jalannya ujian secara langsung</p>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex items-center gap-6 group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-500 shadow-sm border border-emerald-100/50">
                    <i class="fas fa-satellite-dish text-2xl animate-pulse"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Aktif Sekarang</p>
                    <p class="text-3xl font-black text-gray-900 leading-tight group-hover:text-emerald-600 transition-colors">{{ $activeExamsCount }}</p>
                    <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest mt-1">Ujian Berlangsung</p>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex items-center gap-6 group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-500 shadow-sm border border-amber-100/50">
                    <i class="fas fa-calendar-clock text-2xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Akan Datang</p>
                    <p class="text-3xl font-black text-gray-900 leading-tight group-hover:text-amber-600 transition-colors">{{ $upcomingExamsCount }}</p>
                    <p class="text-[9px] font-bold text-amber-600 uppercase tracking-widest mt-1">Terjadwal Hari Ini</p>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex items-center gap-6 group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-500 shadow-sm border border-indigo-100/50">
                    <i class="fas fa-check-double text-2xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Selesai</p>
                    <p class="text-3xl font-black text-gray-900 leading-tight group-hover:text-indigo-600 transition-colors">{{ $finishedExamsCount }}</p>
                    <p class="text-[9px] font-bold text-indigo-600 uppercase tracking-widest mt-1">Riwayat Sesi</p>
                </div>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
            <form action="{{ route('admin.monitor-exams.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <div class="md:col-span-5 relative group">
                    <i class="fas fa-search absolute left-6 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors"></i>
                    <input type="text" name="search" placeholder="Cari berdasarkan nama ujian..." 
                        value="{{ request('search') }}"
                        class="w-full h-14 bg-gray-50 border-transparent rounded-2xl pl-14 pr-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                </div>
                <div class="md:col-span-4 relative group">
                    <i class="fas fa-book-open absolute left-6 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors"></i>
                    <select name="subject" class="w-full h-14 bg-gray-50 border-transparent rounded-2xl pl-14 pr-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer">
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3 flex gap-2">
                    <button type="submit" class="flex-1 h-14 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center justify-center gap-2">
                        <i class="fas fa-filter"></i> Saring
                    </button>
                    @if(request()->anyFilled(['search', 'subject']))
                        <a href="{{ route('admin.monitor-exams.index') }}" class="w-14 h-14 bg-gray-50 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 transition">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Exams List -->
        <div class="grid grid-cols-1 gap-6">
            @forelse($exams as $exam)
                @php
                    $now = now();
                    $isActive = $exam->start_time <= $now && $exam->end_time >= $now;
                    $isUpcoming = $exam->start_time > $now;
                    $isFinished = $exam->end_time < $now;
                    
                    $activeStudents = $exam->sessions()->where('status', 'active')->count();
                    $totalStudents = $exam->attempts()->count();
                    $progressPercent = $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100) : 0;
                @endphp
                
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500 hover:-translate-y-1">
                    <div class="p-8">
                        <div class="flex flex-col lg:flex-row lg:items-center gap-8">
                            <!-- Info -->
                            <div class="flex-1 space-y-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    @if($isActive)
                                        <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[9px] font-black uppercase tracking-widest flex items-center gap-2 border border-emerald-100/50">
                                            <span class="w-1.5 h-1.5 bg-emerald-600 rounded-full animate-ping"></span> Sedang Berlangsung
                                        </span>
                                    @elseif($isUpcoming)
                                        <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-lg text-[9px] font-black uppercase tracking-widest flex items-center gap-2 border border-amber-100/50">
                                            <i class="fas fa-clock text-[8px]"></i> Belum Dimulai
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-gray-50 text-gray-400 rounded-lg text-[9px] font-black uppercase tracking-widest flex items-center gap-2 border border-gray-100/50">
                                            <i class="fas fa-check-circle text-[8px]"></i> Selesai
                                        </span>
                                    @endif
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[9px] font-black uppercase tracking-widest border border-indigo-100/50">{{ $exam->subject->name }}</span>
                                    <span class="text-[10px] font-bold text-gray-300">|</span>
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kls {{ $exam->jenjang }}</span>
                                </div>

                                <div class="space-y-1">
                                    <h3 class="text-xl font-black text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $exam->title }}</h3>
                                    <div class="flex items-center gap-4 text-xs font-bold text-gray-400">
                                        <div class="flex items-center gap-2"><i class="fas fa-calendar"></i> {{ $exam->start_time->format('d M Y') }}</div>
                                        <div class="flex items-center gap-2"><i class="fas fa-clock"></i> {{ $exam->start_time->format('H:i') }} - {{ $exam->end_time->format('H:i') }}</div>
                                        <div class="flex items-center gap-2"><i class="fas fa-hourglass-half"></i> {{ $exam->duration_minutes }}m</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Real-time Stats -->
                            <div class="flex items-center gap-12 lg:px-12 lg:border-x lg:border-gray-50">
                                <div class="text-center">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Mengerjakan</p>
                                    <p class="text-2xl font-black text-indigo-600">{{ $activeStudents }}</p>
                                    <p class="text-[9px] font-bold text-gray-300 uppercase tracking-widest mt-1">Siswa Aktif</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Peserta</p>
                                    <p class="text-2xl font-black text-gray-900">{{ $totalStudents }}</p>
                                    <p class="text-[9px] font-bold text-gray-300 uppercase tracking-widest mt-1">Terdaftar</p>
                                </div>
                            </div>

                            <!-- Action -->
                            <div class="lg:w-64 space-y-3">
                                @if($isActive || $isFinished)
                                    <a href="{{ route('admin.monitor.exams.index', $exam) }}" class="flex h-12 w-full bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl items-center justify-center gap-3 hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 group/btn">
                                        <i class="fas fa-satellite-dish group-hover/btn:animate-ping"></i> Monitor Panel
                                    </a>
                                @else
                                    <button disabled class="flex h-12 w-full bg-gray-50 text-gray-300 text-[10px] font-black uppercase tracking-widest rounded-2xl items-center justify-center gap-3 cursor-not-allowed">
                                        <i class="fas fa-lock"></i> Belum Tersedia
                                    </button>
                                @endif
                                
                                @if(auth()->user()->role === 'superadmin')
                                    <a href="{{ route('admin.exams.edit', $exam) }}" class="flex h-10 w-full bg-amber-50 text-amber-600 text-[9px] font-black uppercase tracking-widest rounded-xl items-center justify-center gap-2 hover:bg-amber-100 transition">
                                        <i class="fas fa-pen"></i> Konfigurasi
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Mini Progress -->
                        @if($isActive && $totalStudents > 0)
                            <div class="mt-8 pt-8 border-t border-gray-50 space-y-2">
                                <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest">
                                    <span class="text-gray-400">Aktivitas Real-time</span>
                                    <span class="text-indigo-600">{{ $progressPercent }}% Siswa Masuk</span>
                                </div>
                                <div class="h-2 bg-gray-50 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-full transition-all duration-1000" style="width: {{ $progressPercent }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-20 text-center animate-fadeIn">
                    <div class="w-24 h-24 bg-indigo-50 rounded-[2.5rem] flex items-center justify-center text-indigo-200 mx-auto mb-6 transform -rotate-12 group hover:rotate-0 transition-transform duration-500">
                        <i class="fas fa-video-slash text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 mb-2 uppercase tracking-tight">Tidak Ada Ujian</h3>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest max-w-sm mx-auto leading-relaxed">Belum ada jadwal ujian untuk dipantau. Pastikan ujian sudah dibuat dan dipublikasikan.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($exams->hasPages())
            <div class="flex justify-center pt-8">
                {{ $exams->links('vendor.pagination.tailwind-indigo') }}
            </div>
        @endif

        <!-- Monitoring Guide -->
        <div class="bg-indigo-900 rounded-[3rem] p-12 text-white relative overflow-hidden group">
            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-1000"></div>
            <div class="absolute -left-12 -top-12 w-48 h-48 bg-white/5 rounded-full blur-2xl group-hover:scale-125 transition-transform duration-1000"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-center gap-12">
                <div class="w-20 h-20 bg-white/10 rounded-3xl flex items-center justify-center text-3xl backdrop-blur-md border border-white/10">
                    <i class="fas fa-info-circle text-indigo-100 animate-bounce"></i>
                </div>
                <div class="flex-1 space-y-4 text-center md:text-left">
                    <h3 class="text-xl font-black uppercase tracking-tight">Panduan Monitoring</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-2 h-2 rounded-full bg-emerald-400 mt-2"></div>
                            <p class="text-sm font-bold text-indigo-100 opacity-80 leading-relaxed">Gunakan <span class="text-white">Monitor Panel</span> untuk melihat aktivitas layar, keyboard, dan pelanggaran siswa secara instan.</p>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-2 h-2 rounded-full bg-amber-400 mt-2"></div>
                            <p class="text-sm font-bold text-indigo-100 opacity-80 leading-relaxed">Anda dapat <span class="text-white">Membuka Kembali</span> akses ujian bagi siswa yang tidak sengaja terputus atau mengalami kendala teknis.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
@endsection
