@extends('layouts.app')

@section('title', 'Beranda Siswa - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('content')
<div class="space-y-8 pb-12">
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-[2.5rem] p-8 md:p-12 shadow-2xl shadow-indigo-200">
        <!-- Abstract Decoration -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 bg-indigo-400/20 rounded-full blur-3xl"></div>

        <div class="relative flex flex-col md:flex-row items-center gap-8">
            <!-- Profile Photo -->
            <div class="relative group">
                <div class="absolute -inset-1 bg-white/20 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                <img src="{{ Auth::user()->photo_url }}" alt="Profile" 
                    class="relative w-32 h-32 md:w-40 md:h-40 rounded-full object-cover border-4 border-white/30 shadow-2xl transition-transform duration-500 group-hover:scale-105">
                <div class="absolute bottom-2 right-2 w-8 h-8 bg-emerald-500 border-4 border-white rounded-full shadow-lg"></div>
            </div>

            <!-- Welcome Text -->
            <div class="flex-1 text-center md:text-left text-white">
                <p class="text-indigo-100 font-bold uppercase tracking-[0.3em] text-[10px] mb-2">Selamat Datang Kembali</p>
                <h1 class="text-3xl md:text-5xl font-black mb-4">{{ Auth::user()->name }}</h1>
                <div class="flex flex-wrap justify-center md:justify-start gap-3">
                    <span class="px-4 py-1.5 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-xs font-black uppercase tracking-widest">
                        NIS: {{ Auth::user()->nis }}
                    </span>
                    <span class="px-4 py-1.5 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-xs font-black uppercase tracking-widest">
                        Kelas: {{ Auth::user()->grade }}-{{ Auth::user()->class_group }}
                    </span>
                </div>
            </div>

            <!-- Header Stats -->
            <div class="grid grid-cols-2 gap-4 w-full md:w-auto">
                <div class="bg-white/10 backdrop-blur-xl border border-white/10 p-5 rounded-3xl text-center">
                    <p class="text-[10px] font-black uppercase tracking-widest text-indigo-100 mb-1">Ujian Aktif</p>
                    <p class="text-2xl font-black text-white">{{ $stats['available_exams'] }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur-xl border border-white/10 p-5 rounded-3xl text-center">
                    <p class="text-[10px] font-black uppercase tracking-widest text-indigo-100 mb-1">Skor Rata²</p>
                    <p class="text-2xl font-black text-white">{{ $stats['avg_score'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Exams Section (Left + Middle) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between px-2">
                <h3 class="text-xl font-black text-gray-900 flex items-center gap-3 uppercase tracking-wider">
                    <span class="w-2 h-8 bg-indigo-600 rounded-full"></span>
                    Daftar Ujian Tersedia
                </h3>
                <a href="{{ route('student.exams.index') }}" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-700 transition flex items-center gap-2">
                    Lihat Semua <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            @if($availableExams->isEmpty())
                <div class="bg-white rounded-[2rem] p-12 border-2 border-dashed border-gray-100 text-center space-y-4 shadow-sm">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                        <i class="fas fa-calendar-check text-4xl"></i>
                    </div>
                    <h4 class="text-lg font-black text-gray-900">Tidak Ada Ujian Aktif</h4>
                    <p class="text-sm text-gray-400 font-medium">Semua ujian telah diselesaikan atau belum ada ujian yang dijadwalkan.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($availableExams as $exam)
                        @php
                            $attempt = Auth::user()->examAttempts()->where('exam_id', $exam->id)->first();
                            $isInProgress = $attempt && $attempt->status === 'in_progress';
                        @endphp
                        <div class="group relative bg-white rounded-[2rem] p-7 border border-gray-100 shadow-sm hover:shadow-2xl hover:shadow-indigo-100 transition-all duration-500 hover:-translate-y-2 overflow-hidden">
                            <!-- Background Decoration -->
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>

                            <div class="relative flex flex-col h-full">
                                <div class="flex items-start justify-between mb-6">
                                    <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-500 shadow-sm">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                    @if($isInProgress)
                                        <span class="px-4 py-1.5 bg-amber-100 text-amber-700 rounded-full text-[9px] font-black uppercase tracking-widest flex items-center gap-2 border border-amber-200">
                                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                                            Lanjutkan
                                        </span>
                                    @else
                                        <span class="px-4 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-[9px] font-black uppercase tracking-widest flex items-center gap-2 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                            Tersedia
                                        </span>
                                    @endif
                                </div>

                                <div class="flex-grow">
                                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] mb-1 italic">{{ $exam->subject->name }}</p>
                                    <h4 class="text-lg font-black text-gray-900 leading-tight mb-4 group-hover:text-indigo-600 transition-colors">{{ $exam->title }}</h4>
                                    
                                    <div class="flex items-center gap-5 text-gray-400">
                                        <div class="flex items-center gap-2">
                                            <i class="far fa-clock text-xs"></i>
                                            <span class="text-[11px] font-bold uppercase tracking-wider">{{ $exam->duration_minutes }} Menit</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="far fa-file-alt text-xs"></i>
                                            <span class="text-[11px] font-bold uppercase tracking-wider">{{ $exam->total_questions }} Soal</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-8">
                                    @if($isInProgress)
                                        <a href="{{ route('student.exams.take', $attempt) }}" class="w-full py-4 bg-amber-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-amber-100 hover:bg-amber-600 transition-all flex items-center justify-center gap-3 group/btn">
                                            Lanjutkan Ujian <i class="fas fa-arrow-right group-hover/btn:translate-x-1 transition-transform"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('student.exams.start', $exam) }}" class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center justify-center gap-3 group/btn animate-pulse-slow">
                                            Mulai Sekarang <i class="fas fa-play text-[8px] group-hover/btn:translate-x-1 transition-transform"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Sidebar Section (Right) -->
        <div class="space-y-8">
            <!-- Results Card -->
            <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black text-gray-900 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-history text-indigo-600"></i>
                        Hasil Terbaru
                    </h3>
                    <a href="{{ route('student.results') }}" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-indigo-50 hover:text-indigo-600 transition">
                        <i class="fas fa-chevron-right text-[10px]"></i>
                    </a>
                </div>

                @if($recentResults->isEmpty())
                    <div class="text-center py-10">
                        <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                            <i class="fas fa-chart-line text-2xl"></i>
                        </div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Belum ada hasil</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($recentResults as $result)
                            @if($result->exam)
                            <div class="group p-4 rounded-3xl hover:bg-indigo-50/50 border border-transparent hover:border-indigo-100 transition-all duration-300">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg font-black 
                                        @if($result->final_score >= 75) bg-emerald-50 text-emerald-600 @else bg-rose-50 text-rose-600 @endif group-hover:scale-110 transition-transform">
                                        {{ intval($result->final_score) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-black text-gray-800 uppercase tracking-wide truncate group-hover:text-indigo-600 transition-colors">{{ $result->exam->title }}</p>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ $result->submitted_at->diffForHumans() }}</p>
                                    </div>
                                    @if($result->final_score >= 75)
                                        <i class="fas fa-check-circle text-emerald-400 text-sm"></i>
                                    @endif
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Quick Info -->
            <div class="bg-indigo-900 rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-2xl shadow-indigo-200">
                <div class="absolute top-0 right-0 -mr-10 -mt-10 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
                
                <h3 class="text-sm font-black uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
                    <span class="w-1 h-1 bg-white rounded-full"></span>
                    Tips Belajar
                </h3>
                
                <div class="space-y-6">
                    <div class="flex gap-4">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-lightbulb text-amber-300 text-xs"></i>
                        </div>
                        <p class="text-[11px] leading-relaxed font-bold text-indigo-100">Baca instruksi ujian dengan teliti sebelum menekan tombol mulai.</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-shield-alt text-blue-300 text-xs"></i>
                        </div>
                        <p class="text-[11px] leading-relaxed font-bold text-indigo-100">Pastikan koneksi internet stabil selama mengerjakan ujian.</p>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-white/10">
                    <p class="text-[10px] font-black uppercase tracking-widest text-indigo-300">Status Sistem:</p>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="w-2 h-2 bg-emerald-400 rounded-full animate-ping"></span>
                        <span class="text-[11px] font-bold uppercase tracking-wider">Online & Sinkron</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes pulse-slow {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.95; transform: scale(0.98); }
    }
    .animate-pulse-slow {
        animation: pulse-slow 3s infinite ease-in-out;
    }
</style>
@endsection
