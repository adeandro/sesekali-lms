@extends('layouts.app')

@section('title', 'Beranda Siswa - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('content')
<div class="space-y-8 pb-12">
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-[var(--brand-primary)] to-[var(--brand-dark)] rounded-[2.5rem] p-8 md:p-12 shadow-2xl shadow-[var(--brand-glow)]">
        <!-- Abstract Decoration -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 bg-[var(--brand-glow)] rounded-full blur-3xl opacity-30"></div>

        <div class="relative flex flex-col md:flex-row items-center gap-8">
            <!-- Profile Photo -->
            <div class="relative group/avatar" x-data>
                <a href="{{ route('student.profile') }}" class="block relative">
                    <div class="absolute -inset-1.5 bg-gradient-to-tr from-[var(--brand-primary)] to-[var(--brand-dark)] rounded-full opacity-20 group-hover/avatar:opacity-40 transition-opacity blur-md"></div>
                    @if(($configs['enable_gamification'] ?? '1') == '1')
                        <div class="relative w-24 h-24 md:w-32 md:h-32 rounded-full border-4 bg-white flex items-center justify-center overflow-hidden transition-all duration-500 group-hover/avatar:scale-105 {{ Auth::user()->avatar_frame_class }}">
                            @if(Auth::user()->is_avatar_seed)
                                <div x-data="{ loaded: false }" x-intersect.once="loaded = true" class="w-full h-full relative">
                                    <template x-if="loaded">
                                        <div x-html="multiavatar('{{ Auth::user()->avatar_seed }}')" class="w-full h-full animate-fadeIn"></div>
                                    </template>
                                </div>
                            @elseif(Auth::user()->has_avatar)
                                <img src="{{ Auth::user()->avatar_url }}" alt="Profile" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-[var(--brand-primary)] to-[var(--brand-dark)] flex items-center justify-center text-white text-3xl font-black italic tracking-tighter shadow-inner">
                                    {{ Auth::user()->initials }}
                                </div>
                            @endif
                        </div>
                        <!-- Large Level Badge -->
                        <div class="absolute -bottom-2 -right-2 bg-white text-[var(--brand-primary)] font-black px-4 py-1 rounded-2xl shadow-xl border-2 border-[var(--brand-glow)] flex items-center gap-2 group-hover/avatar:scale-110 transition-transform">
                            <i class="fas fa-bolt text-amber-500 text-xs"></i>
                            <span class="text-sm">LVL {{ Auth::user()->current_level }}</span>
                        </div>
                    @else
                        @if(Auth::user()->has_avatar)
                            <img src="{{ Auth::user()->photo_url }}" alt="Profile" 
                                class="relative w-24 h-24 md:w-32 md:h-32 rounded-full object-cover border-4 border-white/30 shadow-2xl transition-transform duration-500 group-hover/avatar:scale-105">
                        @else
                            <div class="relative w-24 h-24 md:w-32 md:h-32 rounded-full border-4 border-white/30 bg-gradient-to-br from-[var(--brand-primary)] to-[var(--brand-dark)] flex items-center justify-center text-white text-3xl font-black italic tracking-tighter shadow-2xl transition-transform duration-500 group-hover/avatar:scale-105">
                                {{ Auth::user()->initials }}
                            </div>
                        @endif
                    @endif
                    <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-[var(--brand-primary)] text-white rounded-full border-2 border-white flex items-center justify-center shadow-lg opacity-0 group-hover/avatar:opacity-100 transition-opacity">
                        <i class="fas fa-cog text-[10px]"></i>
                    </div>
                </a>
                <div class="absolute -top-1 -right-1 w-6 h-6 bg-emerald-500 border-2 border-white rounded-full shadow-lg"></div>
            </div>

            <!-- Welcome Text -->
            <div class="flex-1 text-center md:text-left text-white">
                <p class="text-white/80 font-bold uppercase tracking-[0.3em] text-[10px] mb-2 italic">{{ $stats['greeting'] }}</p>
                <h1 class="text-3xl md:text-5xl font-black mb-4 tracking-tight">{{ explode(' ', Auth::user()->formatted_name)[0] }}! 👋</h1>
                <p class="text-white/70 text-sm font-medium mb-6">{{ $stats['motivational_text'] }}</p>
                <div class="flex flex-wrap justify-center md:justify-start gap-3">
                    <span class="px-4 py-1.5 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-[10px] font-black uppercase tracking-widest">
                        NIS: {{ Auth::user()->nis }}
                    </span>
                         @if(($configs['enable_gamification'] ?? '1') == '1')
                            <span class="px-4 py-1.5 bg-white/20 backdrop-blur-md border border-white/30 rounded-full text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                                 <i class="fas fa-crown text-amber-400"></i> {{ Auth::user()->level_title }}
                            </span>
                        @endif
                    <span class="px-4 py-1.5 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-[10px] font-black uppercase tracking-widest">
                        G-Rank #{{ $stats['current_rank'] }}
                    </span>
                    <span class="px-4 py-1.5 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-[10px] font-black uppercase tracking-widest">
                        L-Rank #{{ $stats['current_local_rank'] }}
                    </span>
                </div>

                 @if(($configs['enable_gamification'] ?? '1') == '1')
                    <!-- XP Progress Bar (Large) -->
                    <div class="mt-8 max-w-md">
                        <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-[0.2em] mb-2 text-white/90">
                            <span>Experience Progress</span>
                            <span>{{ Auth::user()->total_exp % 100 }}/100 XP</span>
                        </div>
                        <div class="h-4 w-full bg-white/5 backdrop-blur-md rounded-full border border-white/10 p-1">
                            <div class="h-full bg-[var(--brand-primary)] rounded-full transition-all duration-1000 theme-active-glow shadow-[0_0_10px_var(--brand-glow)]"
                                 style="width: {{ Auth::user()->xp_progress_percentage }}%"></div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Header Stats -->
            <div class="grid grid-cols-3 gap-3 w-full md:w-auto">
                <div class="bg-white/10 backdrop-blur-xl border border-white/10 p-4 rounded-3xl text-center min-w-[100px]">
                    <p class="text-[8px] font-black uppercase tracking-widest text-white/70 mb-1">Top Lokal</p>
                    <p class="text-xl font-black text-white">#{{ $stats['current_local_rank'] }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur-xl border border-white/10 p-4 rounded-3xl text-center min-w-[100px]">
                    <p class="text-[8px] font-black uppercase tracking-widest text-white/70 mb-1">Rata-rata</p>
                    <p class="text-xl font-black text-white">{{ intval($stats['avg_score']) }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur-xl border border-white/10 p-4 rounded-3xl text-center min-w-[100px]">
                    <p class="text-[8px] font-black uppercase tracking-widest text-white/70 mb-1">TopGlobal</p>
                    <p class="text-xl font-black text-white">#{{ $stats['current_rank'] }}</p>
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
                    <span class="w-2 h-8 bg-[var(--brand-primary)] rounded-full"></span>
                    Daftar Ujian Tersedia
                </h3>
                <a href="{{ route('student.exams.index') }}" class="text-[10px] font-black text-[var(--brand-primary)] uppercase tracking-widest hover:text-[var(--brand-dark)] transition flex items-center gap-2">
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
                        <div class="group relative bg-white rounded-[2rem] p-7 border theme-soft-shadow hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 overflow-hidden" style="border-color: var(--brand-glow);">
                            <!-- Background Decoration -->
                            <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700" style="background-color: var(--brand-glow);"></div>

                            <div class="relative flex flex-col h-full">
                                <div class="flex items-start justify-between mb-6">
                                    <div class="w-14 h-14 bg-[var(--brand-glow)] text-[var(--brand-primary)] rounded-2xl flex items-center justify-center text-2xl group-hover:bg-[var(--brand-primary)] group-hover:text-white transition-colors duration-500 shadow-sm">
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
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] mb-1 italic" style="color: var(--brand-primary);">{{ $exam->subject->name }}</p>
                                    <h4 class="text-lg font-black text-gray-900 leading-tight mb-4 group-hover:text-[var(--brand-primary)] transition-colors">{{ $exam->title }}</h4>
                                    
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
                                        <a href="{{ route('student.exams.start', $exam) }}" class="w-full py-4 theme-primary-btn text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all flex items-center justify-center gap-3 group/btn animate-pulse-slow">
                                            Mulai Sekarang <i class="fas fa-play text-[8px] group-hover/btn:translate-x-1 transition-transform"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Achievement Badges -->
            @if($configs['enable_gamification'] ?? true)
            <div class="bg-white rounded-[2.5rem] p-8 border-l-4 border-[var(--brand-primary)] shadow-md shadow-[var(--brand-glow)] relative overflow-hidden group">
                <!-- Achievement Background Glow -->
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-[var(--brand-glow)] rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
                
                <div class="flex items-center justify-between mb-8 relative">
                    <h3 class="text-lg font-black text-gray-900 uppercase tracking-widest flex items-center gap-3">
                        <i class="fas fa-award text-[var(--brand-primary)]"></i>
                        Pencapaian Kamu
                    </h3>
                    <span class="text-[10px] font-black text-[var(--brand-primary)] bg-[var(--brand-glow)] px-3 py-1 rounded-full uppercase">{{ count($earnedAchievements) }}/{{ count($allAchievements) }} Terkumpul</span>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 relative z-10" style="content-visibility: auto;">
                    @foreach($allAchievements as $achievement)
                        @php 
                            $isEarned = isset($earnedAchievements[$achievement->slug]); 
                            $progress = $isEarned ? 100 : 0;
                            $loreTexts = [
                                "Penjaga Gerbang" => "Kunci rahasia terletak pada ketekunan.",
                                "Pejuang Malam" => "Cahaya ilmu menembus kegelapan.",
                                "Sang Perintis" => "Setiap langkah besar dimulai dari sini.",
                                "Pemburu Nilai" => "Kesempurnaan adalah hasil dari dedikasi."
                            ];
                            $lore = $loreTexts[$achievement->name] ?? "Langkah nyata menuju puncak intelektual.";
                        @endphp
                        <div class="group relative bg-[#0f172a]/95 border border-white/5 rounded-2xl p-6 transition-all duration-300 premium-motion shadow-lg hover:shadow-[0_20px_50px_rgba(0,0,0,0.3)] hover:-translate-y-1 overflow-hidden gpu-accelerated premium-shine"
                             title="{{ $achievement->description }}">
                            
                            <!-- Minimalist Progress Ring (Top Right) -->
                            <div class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center z-20">
                                @if($progress >= 100)
                                    <div class="w-7 h-7 rounded-full bg-emerald-500/20 border border-emerald-500/50 flex items-center justify-center shadow-[0_0_15px_rgba(16,185,129,0.4)] animate-pulse">
                                        <i class="fas fa-check text-emerald-400 text-[8px]"></i>
                                    </div>
                                @else
                                    <svg class="w-full h-full transform -rotate-90">
                                        <circle cx="20" cy="20" r="14" stroke="currentColor" stroke-width="2" fill="transparent" class="text-white/5" />
                                        <circle cx="20" cy="20" r="14" stroke="[var(--brand-primary)]" stroke-width="2" fill="transparent" 
                                                stroke-dasharray="88" stroke-dashoffset="{{ 88 - (88 * $progress / 100) }}" 
                                                class="transition-all duration-1000" />
                                    </svg>
                                    <span class="absolute text-[8px] font-bold text-white/60">{{ $progress }}%</span>
                                @endif
                            </div>

                            <!-- Card Content (Center Aligned) -->
                            <div class="flex flex-col items-center text-center space-y-4 pt-2 relative z-10">
                                
                                <!-- Icon Area (Standardized Size) -->
                                <div class="relative">
                                    @if(!$isEarned)
                                         <div class="absolute inset-0 bg-[var(--brand-primary)] opacity-5 blur-2xl rounded-full animate-pulse"></div>
                                    @endif
                                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-white text-3xl shadow-none transition-all duration-300 premium-motion relative z-10
                                         {{ $isEarned ? 'group-hover:animate-float-slow group-hover:drop-shadow-[0_0_15px_var(--brand-primary)]' : 'opacity-20 grayscale shadow-none' }}" 
                                         style="background-color: {{ $isEarned ? $achievement->color : 'rgba(255,255,255,0.05)' }}">
                                        <i class="{{ $achievement->icon }}"></i>
                                    </div>
                                </div>

                                <!-- Text Details -->
                                <div class="space-y-1 w-full">
                                    <h4 class="text-sm font-extrabold tracking-tight uppercase leading-tight {{ $isEarned ? 'text-white' : 'text-slate-500' }} line-clamp-1">
                                        {{ $achievement->name }}
                                    </h4>
                                    <p class="text-[11px] font-medium text-slate-400 leading-tight line-clamp-2 min-h-[1.5rem]">
                                        {{ $achievement->description }}
                                    </p>
                                    <p class="text-[9px] text-slate-500/80 italic mt-2 line-clamp-1">
                                        "{{ $lore }}"
                                    </p>
                                </div>
                            </div>

                            <!-- Decorative background glow (Simplified) -->
                            <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-[var(--brand-primary)] opacity-0 group-hover:opacity-10 blur-[80px] rounded-full transition-opacity duration-300 pointer-events-none"></div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Section (Right) -->
        <div class="space-y-8">
            <!-- Results Card -->
            <div class="bg-white rounded-[2.5rem] p-8 border-l-4 border-[var(--brand-primary)] shadow-md shadow-[var(--brand-glow)]">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black text-gray-900 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-history text-[var(--brand-primary)]"></i>
                        Hasil Terbaru
                    </h3>
                    <a href="{{ route('student.results') }}" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-[var(--brand-glow)] hover:text-[var(--brand-primary)] transition">
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
                            <div class="group p-4 rounded-3xl hover:bg-[var(--brand-glow)] border border-transparent hover:border-[var(--brand-glow)] transition-all duration-300">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg font-black 
                                        @if($result->final_score >= 75) bg-emerald-50 text-emerald-600 @else bg-rose-50 text-rose-600 @endif group-hover:scale-110 transition-transform">
                                        {{ intval($result->final_score) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-black text-gray-800 uppercase tracking-wide truncate group-hover:text-[var(--brand-primary)] transition-colors">{{ $result->exam->title }}</p>
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

            <!-- Leaderboards (Hall of Fame) -->
            @if(($configs['enable_leaderboard'] ?? '1') == '1')
            <div class="relative group bg-white border border-transparent rounded-[2.5rem] p-8 shadow-2xl shadow-[var(--brand-glow)] overflow-hidden" x-data="{ tab: 'global' }">
                <!-- Animated Background Blob -->
                <div class="absolute -top-20 -left-20 w-64 h-64 rounded-full blur-3xl animate-pulse opacity-30" style="background-color: var(--brand-glow);"></div>
                <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-purple-200/30 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s"></div>

                <div class="relative">
                    <div class="flex flex-col gap-6 mb-8">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-black text-gray-900 uppercase tracking-widest flex items-center gap-3">
                                <i class="fas fa-trophy text-amber-500"></i>
                                Hall of Fame
                            </h3>
                        </div>
                        
                        <!-- Tabs -->
                        <div class="flex bg-gray-100/50 p-1.5 rounded-2xl">
                            <button @click="tab = 'global'" :class="tab === 'global' ? 'bg-white shadow-sm text-[var(--brand-primary)]' : 'text-gray-500'" class="flex-1 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all">Top Global</button>
                            <button @click="tab = 'local'" :class="tab === 'local' ? 'bg-white shadow-sm text-[var(--brand-primary)]' : 'text-gray-500'" class="flex-1 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all">Top Lokal</button>
                        </div>
                    </div>

                    <!-- Global Leaderboard -->
                    <div class="space-y-3" x-show="tab === 'global'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                        @foreach($leaderboard as $index => $student)
                            <div class="flex items-center gap-4 p-3 rounded-2xl transition-all duration-300 hover:bg-white/60 {{ Auth::id() === $student->id ? 'bg-white/80 scale-[1.02] shadow-sm' : '' }}" style="{{ Auth::id() === $student->id ? 'border: 1px solid var(--brand-glow); ring: 2px solid var(--brand-glow);' : '' }}">
                                <div class="w-10 h-10 flex items-center justify-center text-sm font-black relative">
                                    @if($index === 0)
                                        <i class="fas fa-crown text-amber-500 text-xl drop-shadow-[0_0_8px_rgba(245,158,11,0.5)]"></i>
                                    @elseif($index === 1)
                                        <i class="fas fa-crown text-slate-400 text-lg drop-shadow-[0_0_8px_rgba(148,163,184,0.5)]"></i>
                                    @elseif($index === 2)
                                        <i class="fas fa-crown text-orange-400 text-lg drop-shadow-[0_0_8px_rgba(251,146,60,0.5)]"></i>
                                    @else
                                        <span class="text-gray-400">#{{ $index + 1 }}</span>
                                    @endif
                                </div>
                                <div class="relative group/rank-avatar {{ $index === 0 ? 'sovereign-ring' : '' }}">
                                    <div x-data="{ loaded: false, seed: '{{ $student->avatar_seed }}', hasAvatar: {{ $student->has_avatar ? 'true' : 'false' }}, isSeed: {{ $student->is_avatar_seed ? 'true' : 'false' }} }" x-intersect.once="loaded = true" 
                                         class="w-10 h-10 rounded-full border-2 shadow-sm bg-white overflow-hidden transition-all duration-500 premium-motion relative z-10 gpu-accelerated
                                         @if($index === 0) border-amber-300
                                         @elseif($index === 1) border-slate-300 ring-4 ring-slate-100 shadow-[0_0_15px_rgba(203,213,225,0.3)]
                                         @elseif($index === 2) border-orange-300 ring-4 ring-orange-100 shadow-[0_0_15px_rgba(253,186,116,0.3)]
                                         @else border-white @endif">
                                         <template x-if="loaded && isSeed">
                                             <div x-html="multiavatar(seed)" class="w-full h-full animate-fadeIn"></div>
                                         </template>
                                         <template x-if="loaded && !isSeed && hasAvatar">
                                             <img src="{{ $student->avatar_url }}" class="w-full h-full object-cover">
                                         </template>
                                         <template x-if="loaded && !isSeed && !hasAvatar">
                                             <div class="w-full h-full bg-gradient-to-br from-[var(--brand-primary)] to-[var(--brand-dark)] flex items-center justify-center text-white text-[10px] font-black italic leading-none">
                                                 {{ $student->initials }}
                                             </div>
                                         </template>
                                    </div>
                                     @if($configs['enable_gamification'] ?? '1' == '1')
                                         @if($index === 0)
                                            <!-- Sovereign Crown -->
                                            <div class="absolute -top-2 -right-2 text-amber-500 drop-shadow-sm z-20 text-xs crown-bounce">
                                                <i class="fas fa-crown"></i>
                                            </div>
                                         @endif
                                         <div class="absolute -bottom-1 -right-1 bg-[var(--brand-primary)] text-white text-[6px] font-black w-4 h-4 rounded-full flex items-center justify-center border border-white shadow-sm z-10">
                                             {{ $index + 1 }}
                                         </div>
                                     @endif
                                    @if($index < 3)
                                        <div class="absolute -inset-1 rounded-full opacity-30 blur-sm animate-pulse
                                            @if($index === 0) bg-amber-400 @elseif($index === 1) bg-slate-400 @else bg-orange-400 @endif">
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs {{ $index === 0 ? 'font-extrabold tracking-wide bg-clip-text text-transparent bg-gradient-to-r from-yellow-400 to-amber-600 drop-shadow-sm' : 'font-black text-gray-900' }} truncate">
                                        {{ $student->formatted_name }}
                                        @if(Auth::id() === $student->id)
                                            <span class="ml-1 text-[8px] bg-[var(--brand-primary)] text-white px-1.5 py-0.5 rounded-full uppercase">Kamu</span>
                                        @endif
                                    </p>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Skor: {{ number_format($student->total_score, 0) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Local Leaderboard -->
                    <div class="space-y-3" x-show="tab === 'local'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
                        @forelse($localLeaderboard as $index => $student)
                            <div class="flex items-center gap-4 p-3 rounded-2xl transition-all duration-300 hover:bg-white/60 {{ Auth::id() === $student->id ? 'bg-white/80 border-[var(--brand-glow)] ring-2 ring-[var(--brand-glow)]/50 scale-[1.02]' : '' }}">
                                <div class="w-10 h-10 flex items-center justify-center text-sm font-black relative">
                                    @if($index === 0)
                                        <i class="fas fa-medal text-amber-500 text-xl drop-shadow-[0_0_8px_rgba(245,158,11,0.5)]"></i>
                                    @elseif($index === 1)
                                        <i class="fas fa-medal text-slate-400 text-lg drop-shadow-[0_0_8px_rgba(148,163,184,0.5)]"></i>
                                    @elseif($index === 2)
                                        <i class="fas fa-medal text-orange-400 text-lg drop-shadow-[0_0_8px_rgba(251,146,60,0.5)]"></i>
                                    @else
                                        <span class="text-gray-400">#{{ $index + 1 }}</span>
                                    @endif
                                </div>
                                <div class="relative group/rank-avatar {{ $index === 0 ? 'sovereign-ring' : '' }}">
                                    <div x-data="{ loaded: false, seed: '{{ $student->avatar_seed }}', hasAvatar: {{ $student->has_avatar ? 'true' : 'false' }}, isSeed: {{ $student->is_avatar_seed ? 'true' : 'false' }} }" x-intersect.once="loaded = true" 
                                         class="w-10 h-10 rounded-full border-2 shadow-sm bg-white overflow-hidden transition-all duration-500 premium-motion relative z-10 gpu-accelerated
                                         @if($index === 0) border-amber-300
                                         @elseif($index === 1) border-slate-300 ring-4 ring-slate-100 shadow-[0_0_15px_rgba(203,213,225,0.3)]
                                         @elseif($index === 2) border-orange-300 ring-4 ring-orange-100 shadow-[0_0_15px_rgba(253,186,116,0.3)]
                                         @else border-white @endif">
                                         <template x-if="loaded && isSeed">
                                             <div x-html="multiavatar(seed)" class="w-full h-full animate-fadeIn"></div>
                                         </template>
                                         <template x-if="loaded && !isSeed && hasAvatar">
                                             <img src="{{ $student->avatar_url }}" class="w-full h-full object-cover">
                                         </template>
                                         <template x-if="loaded && !isSeed && !hasAvatar">
                                             <div class="w-full h-full bg-gradient-to-br from-[var(--brand-primary)] to-[var(--brand-dark)] flex items-center justify-center text-white text-[10px] font-black italic leading-none">
                                                 {{ $student->initials }}
                                             </div>
                                         </template>
                                    </div>
                                     @if($configs['enable_gamification'] ?? '1' == '1')
                                         @if($index === 0)
                                            <!-- Sovereign Crown -->
                                            <div class="absolute -top-2 -right-2 text-amber-500 drop-shadow-sm z-20 text-xs crown-bounce">
                                                <i class="fas fa-crown"></i>
                                            </div>
                                         @endif
                                         <div class="absolute -bottom-1 -right-1 bg-[var(--brand-primary)] text-white text-[6px] font-black w-4 h-4 rounded-full flex items-center justify-center border border-white shadow-sm z-10">
                                             {{ $student->current_level ?? 1 }}
                                         </div>
                                     @endif
                                    @if($index < 3)
                                        <div class="absolute -inset-1 rounded-full opacity-30 blur-sm animate-pulse
                                            @if($index === 0) bg-amber-400 @elseif($index === 1) bg-slate-400 @else bg-orange-400 @endif">
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs {{ $index === 0 ? 'font-extrabold tracking-wide bg-clip-text text-transparent bg-gradient-to-r from-yellow-400 to-amber-600 drop-shadow-sm' : 'font-black text-gray-900' }} truncate">
                                        {{ $student->formatted_name }}
                                        @if(Auth::id() === $student->id)
                                            <span class="ml-1 text-[8px] bg-[var(--brand-primary)] text-white px-1.5 py-0.5 rounded-full uppercase">Kamu</span>
                                        @endif
                                    </p>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Skor: {{ number_format($student->total_score, 0) }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Belum ada peringkat</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Info -->
            <div class="rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-2xl theme-soft-shadow" style="background-color: var(--brand-dark); box-shadow: 0 20px 60px -12px var(--brand-glow);">
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
                        <p class="text-[11px] leading-relaxed font-bold text-white/80">Baca instruksi ujian dengan teliti sebelum menekan tombol mulai.</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-shield-alt text-blue-300 text-xs"></i>
                        </div>
                        <p class="text-[11px] leading-relaxed font-bold text-white/80">Pastikan koneksi internet stabil selama mengerjakan ujian.</p>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-white/10">
                    <p class="text-[10px] font-black uppercase tracking-widest text-white/50">Status Sistem:</p>
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
    .animate-pulse-slow {
        animation: pulse-slow 3s infinite ease-in-out;
    }

    .pulse-glow {
        animation: pulse-glow 2s infinite ease-in-out;
    }

    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 5px rgba(99, 102, 241, 0.2); }
        50% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.6); }
    }
</style>

@if(session('new_achievements'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @foreach(session('new_achievements') as $achievement)
        Swal.fire({
            title: '🎉 Achievement Unlocked!',
            html: `
                <div class="flex flex-col items-center gap-4 py-4">
                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-white text-3xl shadow-xl" style="background-color: {{ $achievement['color'] }}">
                        <i class="{{ $achievement['icon'] }}"></i>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight mb-1">{{ $achievement['name'] }}</h3>
                        <p class="text-sm text-gray-500 font-medium">{{ $achievement['description'] }}</p>
                    </div>
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'KEREN!',
            confirmButtonColor: '#4f46e5',
            customClass: {
                popup: 'rounded-[2rem]',
                confirmButton: 'rounded-xl px-8 py-3 text-[10px] font-black uppercase tracking-widest'
            },
            backdrop: `rgba(79, 70, 229, 0.1) backdrop-filter: blur(4px)`
        });
        @endforeach
    });
</script>
@php session()->forget('new_achievements') @endphp
@endif
@endsection
