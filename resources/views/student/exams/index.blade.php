@extends('layouts.app')

@section('title', 'Daftar Ujian - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('content')
<div class="space-y-8 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 px-2">
        <div class="space-y-1">
            <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.3em] italic">Eksplorasi Ujian</p>
            <h1 class="text-3xl font-black text-gray-900 uppercase tracking-wider flex items-center gap-3">
                <span class="w-2 h-10 bg-indigo-600 rounded-full"></span>
                Ujian Tersedia
            </h1>
        </div>
        
        <!-- Stats Summary -->
        <div class="flex items-center gap-3 bg-white px-6 py-3 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Sesi:</span>
            <span class="text-lg font-black text-indigo-600">{{ $exams->count() }}</span>
        </div>
    </div>

    <!-- Exam Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($exams as $exam)
            @php
                $now = now();
                $attempt = isset($attempts[$exam->id]) ? $attempts[$exam->id] : null;
                $attemptStatus = $attempt ? $attempt->status : null;
                
                // Determine status based on attempt and time
                if($attemptStatus === 'submitted') {
                    $status = 'submitted';
                } elseif($attemptStatus === 'active' || $attemptStatus === 'in_progress') {
                    $status = 'in_progress';
                } elseif($exam->start_time > $now) {
                    $status = 'upcoming';
                } elseif($exam->end_time < $now) {
                    $status = 'ended';
                } else {
                    $status = 'available';
                }
            @endphp
            
            <div class="group relative bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm hover:shadow-2xl hover:shadow-indigo-100 transition-all duration-500 hover:-translate-y-2 overflow-hidden flex flex-col h-full">
                <!-- Background Decoration -->
                <div class="absolute -top-12 -right-12 w-40 h-40 bg-indigo-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"></div>

                <!-- Card Header -->
                <div class="relative flex items-start justify-between mb-8">
                    <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-[1.5rem] flex items-center justify-center text-3xl group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-500 shadow-sm shadow-indigo-50">
                        <i class="fas fa-book-open"></i>
                    </div>
                    
                    <div>
                        @if($status === 'submitted')
                            <span class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-full text-[9px] font-black uppercase tracking-widest flex items-center gap-2 border border-emerald-200">
                                <i class="fas fa-check-circle text-[10px]"></i>
                                Selesai
                            </span>
                        @elseif($status === 'in_progress')
                            <span class="px-4 py-2 bg-amber-100 text-amber-700 rounded-full text-[9px] font-black uppercase tracking-widest flex items-center gap-2 border border-amber-200">
                                <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                                Lanjutkan
                            </span>
                        @elseif($status === 'available')
                            <span class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-full text-[9px] font-black uppercase tracking-widest flex items-center gap-2 border border-emerald-200">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span>
                                Aktif
                            </span>
                        @elseif($status === 'upcoming')
                            <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-[9px] font-black uppercase tracking-widest flex items-center gap-2 border border-blue-200">
                                <i class="far fa-clock text-[10px]"></i>
                                Mendatang
                            </span>
                        @else
                            <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full text-[9px] font-black uppercase tracking-widest flex items-center gap-2 border border-gray-200">
                                <i class="fas fa-times-circle text-[10px]"></i>
                                Berakhir
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Card Content -->
                <div class="relative flex-grow space-y-4">
                    <div>
                        <p class="text-[11px] font-black text-indigo-500 uppercase tracking-[0.2em] mb-1 italic">{{ $exam->subject->name }}</p>
                        <h3 class="text-xl font-black text-gray-900 leading-tight group-hover:text-indigo-600 transition-colors">{{ $exam->title }}</h3>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-6 gap-y-3 pt-2">
                        <div class="flex items-center gap-2.5 text-gray-400 group-hover:text-gray-500 transition-colors">
                            <div class="w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center text-xs">
                                <i class="far fa-clock"></i>
                            </div>
                            <span class="text-[11px] font-black uppercase tracking-wider">{{ $exam->duration_minutes }} Menit</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-gray-400 group-hover:text-gray-500 transition-colors">
                            <div class="w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center text-xs">
                                <i class="far fa-file-alt"></i>
                            </div>
                            <span class="text-[11px] font-black uppercase tracking-wider">{{ $exam->total_questions }} Soal</span>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-2xl p-4 flex items-center justify-between group-hover:bg-indigo-50 transition-colors duration-500">
                        <div class="space-y-1">
                            <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Jadwal Ujian</p>
                            <p class="text-[11px] font-black text-gray-700 uppercase tracking-wider">
                                {{ $exam->start_time->format('d M') }} <span class="text-gray-300 mx-1">|</span> {{ $exam->start_time->format('H:i') }} - {{ $exam->end_time->format('H:i') }}
                            </p>
                        </div>
                        <i class="fas fa-calendar-alt text-gray-200 group-hover:text-indigo-200 transition-colors"></i>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="relative mt-8 pt-6 border-t border-gray-50">
                    @if($status === 'submitted')
                        <div class="w-full py-4 bg-gray-50 text-gray-400 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] flex items-center justify-center gap-3 border border-gray-100">
                            <i class="fas fa-check-double text-emerald-400"></i> Sudah Dikerjakan
                        </div>
                    @elseif($status === 'in_progress')
                        <a href="{{ route('student.exams.start', $exam->id) }}" class="w-full py-4 bg-amber-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-amber-100 hover:bg-amber-600 transition-all flex items-center justify-center gap-3 group/btn hover:scale-[1.02] active:scale-95">
                            Lanjutkan Sesi <i class="fas fa-arrow-right group-hover/btn:translate-x-1 transition-transform"></i>
                        </a>
                    @elseif($status === 'upcoming')
                        <div class="w-full py-4 bg-gray-100 text-gray-400 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] flex items-center justify-center gap-3 border border-gray-100">
                            <i class="fas fa-lock text-[10px]"></i> Belum Dibuka
                        </div>
                    @elseif($status === 'ended')
                        <div class="w-full py-4 bg-gray-100 text-gray-400 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] flex items-center justify-center gap-3 border border-gray-100">
                            <i class="fas fa-hourglass-end text-[10px]"></i> Sesi Berakhir
                        </div>
                    @else
                        <a href="{{ route('student.exams.start', $exam->id) }}" class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center justify-center gap-3 group/btn relative overflow-hidden animate-pulse-custom">
                            <i class="fas fa-play text-[8px] group-hover/btn:translate-x-1 transition-transform"></i> Mulai Ujian
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 bg-white rounded-[3rem] border-2 border-dashed border-gray-100 flex flex-col items-center justify-center text-center space-y-6">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center text-gray-200 text-5xl">
                    <i class="fas fa-inbox"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-gray-900 uppercase tracking-wider">Kotak Masuk Kosong</h3>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest max-w-xs">Saat ini tidak ada ujian aktif yang dijadwalkan untuk kelas Anda.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
    @keyframes pulse-custom {
        0%, 100% { transform: scale(1); box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.1), 0 10px 10px -5px rgba(79, 70, 229, 0.04); }
        50% { transform: scale(1.01); box-shadow: 0 25px 30px -5px rgba(79, 70, 229, 0.2), 0 15px 15px -5px rgba(79, 70, 229, 0.1); }
    }
    .animate-pulse-custom {
        animation: pulse-custom 3s infinite ease-in-out;
    }
</style>
@endsection
