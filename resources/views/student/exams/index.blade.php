@extends('layouts.app')

@section('title', 'Daftar Ujian - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('content')
<div class="space-y-8 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 px-2">
        <div class="space-y-1">
            <p class="text-[10px] font-black text-[var(--brand-primary)] uppercase tracking-[0.3em] italic">Eksplorasi Ujian</p>
            <h1 class="text-3xl font-black text-gray-900 uppercase tracking-wider flex items-center gap-3">
                <span class="w-2 h-10 bg-[var(--brand-primary)] rounded-full"></span>
                Ujian Tersedia
            </h1>
        </div>
        
        <!-- Stats Summary -->
        <div class="flex items-center gap-3 bg-white px-6 py-3 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Sesi:</span>
            <span class="text-lg font-black text-[var(--brand-primary)]">{{ $exams->count() }}</span>
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
            
            <div class="group relative bg-white border-l-4 border-[var(--brand-primary)] rounded-[2.5rem] p-8 shadow-md shadow-[var(--brand-glow)] hover:shadow-2xl hover:shadow-[var(--brand-glow)] transition-all duration-500 hover:-translate-y-2 overflow-hidden flex flex-col h-full">
                <!-- Background Decoration -->
                <div class="absolute -top-12 -right-12 w-40 h-40 bg-[var(--brand-glow)] rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"></div>

                <!-- Card Header -->
                <div class="relative flex items-start justify-between mb-8">
                    <div class="w-16 h-16 bg-[var(--brand-glow)] text-[var(--brand-primary)] rounded-[1.5rem] flex items-center justify-center text-3xl group-hover:bg-[var(--brand-primary)] group-hover:text-white transition-colors duration-500 shadow-sm shadow-[var(--brand-glow)]">
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
                        <p class="text-[11px] font-black text-[var(--brand-primary)] uppercase tracking-[0.2em] mb-1 italic">{{ $exam->subject->name }}</p>
                        <h3 class="text-xl font-black text-gray-900 leading-tight group-hover:text-[var(--brand-primary)] transition-colors">{{ $exam->title }}</h3>
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

                    <div class="bg-gray-50 rounded-2xl p-4 flex items-center justify-between group-hover:bg-[var(--brand-glow)] transition-colors duration-500">
                        <div class="space-y-1">
                            <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Jadwal Ujian</p>
                            <p class="text-[11px] font-black text-gray-700 uppercase tracking-wider">
                                {{ $exam->start_time->format('d M') }} <span class="text-gray-300 mx-1">|</span> {{ $exam->start_time->format('H:i') }} - {{ $exam->end_time->format('H:i') }}
                            </p>
                        </div>
                        <i class="fas fa-calendar-alt text-gray-200 group-hover:text-[var(--brand-primary)]/20 transition-colors"></i>
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
                        <a href="{{ route('student.exams.start', $exam->id) }}" class="w-full py-4 bg-[var(--brand-primary)] text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] shadow-xl shadow-[var(--brand-glow)] hover:bg-[var(--brand-dark)] transition-all flex items-center justify-center gap-3 group/btn relative overflow-hidden animate-pulse-custom">
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
        0%, 100% { transform: scale(1); box-shadow: 0 20px 25px -5px var(--brand-glow), 0 10px 10px -5px var(--brand-glow); }
        50% { transform: scale(1.01); box-shadow: 0 25px 30px -5px var(--brand-glow), 0 15px 15px -5px var(--brand-glow); }
    }
    .animate-pulse-custom {
        animation: pulse-custom 3s infinite ease-in-out;
    }
</style>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- SECTION: TES MENGETIK                           --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if(isset($typingTests) && $typingTests->isNotEmpty())
    <div class="mt-10">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-1 h-7 rounded-full" style="background: var(--brand-primary)"></div>
            <h2 class="text-xl font-bold text-gray-800">Tes Mengetik</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($typingTests as $typingTest)
            @php
                $studentAttempts   = $typingTest->attempts->where('student_id', auth()->id());
                $completedAttempts = $studentAttempts->where('status', 'completed');
                $bestAttempt       = $completedAttempts->sortByDesc(fn($att) => ((float)$att->final_score * 1000) + (float)($att->wpm ?? 0))->first();
                $maxAttempts       = $typingTest->max_attempts ?? 2;
                $canRetry          = $completedAttempts->count() < $maxAttempts;
            @endphp
            <div class="group relative flex flex-col rounded-[2.5rem] border-l-4 bg-white shadow-lg overflow-hidden transition-all duration-300 hover:-translate-y-1"
                 style="border-color: var(--brand-primary); box-shadow: 0 8px 30px -4px var(--brand-glow, rgba(99,102,241,0.2))">

                {{-- Status Badge --}}
                <div class="absolute top-4 right-4">
                    @if($completedAttempts->isNotEmpty())
                        @if($canRetry)
                            <span class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                                Percobaan {{ $completedAttempts->count() }}/{{ $maxAttempts }}
                            </span>
                        @else
                            <span class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                Selesai
                            </span>
                        @endif
                    @else
                        <span class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                            <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-ping"></span>
                            Tersedia
                        </span>
                    @endif
                </div>

                <div class="p-7 flex flex-col gap-3 flex-1">
                    <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider" style="color: var(--brand-primary)">
                        <i class="fas fa-keyboard"></i> Tes Mengetik
                    </div>

                    <h3 class="text-lg font-bold text-gray-800 leading-snug">{{ $typingTest->title }}</h3>

                    @if($typingTest->description)
                        <p class="text-sm text-gray-500 line-clamp-2">{{ $typingTest->description }}</p>
                    @endif

                    <div class="grid grid-cols-2 gap-3 mt-1">
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <i class="fas fa-clock w-4 text-center" style="color: var(--brand-primary)"></i>
                            {{ $typingTest->duration_seconds }} detik
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <i class="fas fa-tachometer-alt w-4 text-center" style="color: var(--brand-primary)"></i>
                            Target {{ $typingTest->target_wpm }} WPM
                        </div>
                    </div>

                    @if($bestAttempt && ($typingTest->show_wpm_accuracy || $typingTest->show_score))
                    <div class="bg-gray-50 rounded-xl p-3 mt-1 grid grid-cols-{{ ($typingTest->show_wpm_accuracy && $typingTest->show_score) ? '3' : ($typingTest->show_wpm_accuracy ? '2' : '1') }} gap-2 text-center">
                        @if($typingTest->show_wpm_accuracy)
                        <div>
                            <div class="font-bold text-sm" style="color: var(--brand-primary)">{{ number_format($bestAttempt->wpm, 1) }}</div>
                            <div class="text-xs text-gray-400">WPM {{ $completedAttempts->count() > 1 ? '(Terbaik)' : '' }}</div>
                        </div>
                        <div>
                            <div class="font-bold text-sm text-blue-600">{{ number_format($bestAttempt->accuracy, 1) }}%</div>
                            <div class="text-xs text-gray-400">Akurasi</div>
                        </div>
                        @endif
                        @if($typingTest->show_score)
                        <div>
                            <div class="font-bold text-sm text-emerald-600">{{ number_format($bestAttempt->final_score, 1) }}</div>
                            <div class="text-xs text-gray-400">Nilai Akhir</div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- CTA Button --}}
                <div class="px-7 pb-6">
                    @if($completedAttempts->isNotEmpty())
                        <div class="flex gap-2">
                            <a href="{{ route('student.typing-tests.result', $typingTest) }}"
                               class="flex-1 text-center py-2.5 rounded-2xl text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                                <i class="fas fa-chart-bar mr-1"></i>Lihat Hasil
                            </a>
                            @if($canRetry)
                            <a href="{{ route('student.typing-tests.show', $typingTest) }}"
                               class="flex-1 text-center py-2.5 rounded-2xl text-xs font-semibold text-white transition hover:opacity-90 shadow-sm"
                               style="background: var(--brand-primary)">
                                <i class="fas fa-redo-alt mr-1"></i>Coba Lagi
                            </a>
                            @endif
                        </div>
                    @else
                        <a href="{{ route('student.typing-tests.show', $typingTest) }}"
                           class="block w-full text-center py-3 rounded-2xl text-sm font-semibold text-white transition hover:opacity-90 shadow-md"
                           style="background: var(--brand-primary)">
                            <i class="fas fa-play mr-2"></i>Mulai Tes Mengetik
                        </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Project Assignments Banner / Quick Access --}}
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="relative z-10 space-y-2">
            <span class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 rounded-full text-[10px] font-black uppercase tracking-widest text-indigo-200 border border-white/10">
                <i class="fas fa-cubes text-amber-400"></i> Portofolio Digital
            </span>
            <h3 class="text-2xl font-black tracking-tight">Galeri Tugas & Project Siswa</h3>
            <p class="text-indigo-200 text-sm max-w-xl">
                Unggah hasil karya pemrograman web dan tugas project interaktifmu. Tampilkan kreativitasmu di galeri publik sekolah!
            </p>
        </div>
        <div class="relative z-10 flex-shrink-0 flex flex-wrap items-center gap-3">
            <a href="{{ route('student.projects.index') }}"
               class="px-6 py-3.5 bg-white text-indigo-950 font-black text-xs uppercase tracking-wider rounded-2xl shadow-lg hover:bg-indigo-50 transition duration-300 flex items-center gap-2">
                <i class="fas fa-folder-open text-indigo-600"></i> Buka Tugas Project
            </a>
            <a href="{{ route('gallery.index') }}" target="_blank"
               class="px-5 py-3.5 bg-white/10 text-white font-bold text-xs rounded-2xl hover:bg-white/20 transition border border-white/20 flex items-center gap-2">
                <i class="fas fa-external-link-alt text-xs"></i> Galeri Publik
            </a>
        </div>
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
    </div>
</div>
@endsection
