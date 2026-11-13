@extends('election.public.layout')

@section('title', 'Kandidat Paslon — ' . $period->title)
@section('header_subtitle', $period->title . ' — Profil Paslon')

@if($isSlide)
    @section('hide_header', '1')
@else
    @section('header_action')
        <div class="flex items-center gap-2">
            <a href="{{ route('election.candidates', ['tampilan' => 'slide', 'mode' => 'sandingan']) }}"
               class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 text-xs font-bold rounded-lg transition shadow-md">
                👥 <span>Mode Sandingan</span>
            </a>
            <a href="{{ route('election.candidates', ['tampilan' => 'slide']) }}"
               class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold rounded-lg transition shadow-md shadow-amber-500/20">
                🖥️ <span>Mode Proyektor</span>
            </a>
        </div>
    @endsection
@endif

@section('content')
@if($isSlide)
    {{-- ================================================================= --}}
    {{-- TAMPILAN PROYEKTOR / TAYANGAN AULA                                --}}
    {{-- Mendukung: Mode Slide (1 Paslon) & Mode Sandingan (Semua Paslon) --}}
    {{-- ================================================================= --}}
    <div x-data="slideDeck({ total: {{ $candidates->count() }}, initialMode: '{{ $initialMode }}', initialVisiMisi: {{ $initialVisiMisi ? 'true' : 'false' }} })"
         x-init="init()"
         class="h-screen w-screen flex flex-col justify-between p-4 sm:p-6 lg:p-8 bg-slate-950 text-white select-none overflow-hidden fixed inset-0 z-50">

        {{-- Top Bar Proyektor --}}
        <div class="flex flex-wrap items-center justify-between border-b border-slate-800 pb-3 gap-3">
            <div class="flex items-center gap-3">
                <span class="text-2xl sm:text-3xl">🗳️</span>
                <div>
                    <h1 class="text-lg sm:text-xl font-black tracking-wider text-amber-400 uppercase">
                        {{ $settings['school_name'] ?? 'PILKETOS DIGITAL' }}
                    </h1>
                    <p class="text-xs sm:text-sm font-semibold text-slate-400">
                        {{ $period->title }} &bull; Profil Pasangan Calon
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                {{-- Mode Switcher: Slide vs Sandingan --}}
                <div class="flex items-center bg-slate-900 border border-slate-800 rounded-xl p-1 text-xs font-bold">
                    <button type="button" @click="setMode('slide')"
                            :class="mode === 'slide' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-400 hover:text-white'"
                            class="px-2.5 sm:px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                        <span>🎞️ Slide (1 Paslon)</span>
                    </button>
                    <button type="button" @click="setMode('sandingan')"
                            :class="mode === 'sandingan' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-400 hover:text-white'"
                            class="px-2.5 sm:px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                        <span>👥 Mode Sandingan</span>
                    </button>
                </div>

                {{-- Toggle Visi & Misi --}}
                <button type="button" @click="toggleVisiMisi()"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border"
                        :class="showVisiMisi ? 'bg-indigo-600/30 text-indigo-300 border-indigo-500/50 hover:bg-indigo-600/40' : 'bg-slate-900 text-slate-400 border-slate-800 hover:text-white'">
                    <span x-text="showVisiMisi ? '👁️ Visi-Misi: Tampil' : '🚫 Tanpa Visi-Misi'"></span>
                </button>

                {{-- Pause/Play Indicator (Hanya di Mode Slide) --}}
                <button type="button" x-show="mode === 'slide'" @click="togglePause()"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition border"
                        :class="paused ? 'bg-amber-500/20 text-amber-300 border-amber-500/40' : 'bg-slate-900 text-slate-300 border-slate-800 hover:bg-slate-800'">
                    <span x-text="paused ? '⏸ Jeda (SPASI)' : '▶ Auto-slide (20s)'"></span>
                </button>

                {{-- Fullscreen Toggle --}}
                <button type="button" @click="toggleFullscreen()"
                        class="hidden sm:inline-flex px-3 py-1.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-xl text-xs font-semibold text-slate-300 transition"
                        title="Layar Penuh (F11)">
                    ⛶ Layar Penuh
                </button>

                {{-- Exit Slide Mode --}}
                <a href="{{ route('election.candidates') }}"
                   class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-xl text-xs font-semibold text-slate-300 transition">
                    ✕ Keluar
                </a>
            </div>
        </div>

        {{-- Main View Container --}}
        <div class="flex-1 flex items-center justify-center my-3 overflow-y-auto w-full">

            {{-- ───────────────────────────────────────────────────────────── --}}
            {{-- A. MODE SANDINGAN: Semua Paslon Berdampingan (Foto & Nama)   --}}
            {{-- ───────────────────────────────────────────────────────────── --}}
            <div x-show="mode === 'sandingan'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="w-full h-full flex items-center justify-center">

                @php
                    $count = $candidates->count();
                    $gridCols = match(true) {
                        $count === 1 => 'grid-cols-1 max-w-xl',
                        $count === 2 => 'grid-cols-1 md:grid-cols-2 max-w-6xl gap-6 lg:gap-10',
                        $count === 3 => 'grid-cols-1 md:grid-cols-3 max-w-7xl gap-5 lg:gap-6',
                        default      => 'grid-cols-2 lg:grid-cols-' . min($count, 4) . ' max-w-7xl gap-4 lg:gap-6',
                    };
                @endphp

                <div class="grid {{ $gridCols }} w-full mx-auto items-stretch h-full py-2">
                    @foreach($candidates as $candidate)
                        <div class="bg-slate-900/90 border-2 border-slate-800 hover:border-amber-400/60 rounded-3xl p-5 sm:p-6 flex flex-col justify-between items-center text-center shadow-2xl transition-all duration-300 group">

                            {{-- Bagian Atas: Nomor Urut --}}
                            <div class="w-full flex justify-center mb-3">
                                <div class="inline-flex items-center gap-3 px-6 py-2 rounded-2xl bg-amber-500/20 border-2 border-amber-400 text-amber-300 shadow-lg shadow-amber-500/10 group-hover:scale-105 transition-transform">
                                    <span class="text-xs uppercase font-extrabold tracking-widest">Nomor Urut</span>
                                    <span class="text-3xl sm:text-5xl font-black">{{ $candidate->number }}</span>
                                </div>
                            </div>

                            {{-- Foto Paslon --}}
                            <div class="flex-1 flex items-center justify-center my-2 w-full">
                                @if($candidate->hasCustomPhoto())
                                    <div class="w-48 h-48 sm:w-60 sm:h-60 lg:w-72 lg:h-72 rounded-2xl overflow-hidden border-4 border-amber-400 shadow-2xl bg-slate-850 group-hover:shadow-amber-500/20 transition-shadow">
                                        <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->label }}"
                                             class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="flex items-center justify-center gap-2 sm:gap-3">
                                        <div class="flex flex-col items-center">
                                            <div class="w-24 h-32 sm:w-32 sm:h-44 rounded-2xl overflow-hidden border-2 border-indigo-400 shadow-lg bg-slate-850">
                                                <img src="{{ $candidate->photo_url }}" alt="Ketua: {{ $candidate->chairman_name }}"
                                                     class="w-full h-full object-cover">
                                            </div>
                                            <span class="mt-1 text-[10px] font-bold uppercase tracking-wider text-indigo-300">Calon Ketua</span>
                                        </div>
                                        <div class="flex flex-col items-center">
                                            <div class="w-24 h-32 sm:w-32 sm:h-44 rounded-2xl overflow-hidden border-2 border-emerald-400 shadow-lg bg-slate-850">
                                                <img src="{{ $candidate->vice_photo_url }}" alt="Wakil: {{ $candidate->vice_name }}"
                                                     class="w-full h-full object-cover">
                                            </div>
                                            <span class="mt-1 text-[10px] font-bold uppercase tracking-wider text-emerald-300">Calon Wakil</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Nama Pasangan Calon & Kelas --}}
                            <div class="w-full mt-3">
                                <h2 class="text-lg sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-snug">
                                    {{ $candidate->chairman_name }}
                                    <span class="text-amber-400 font-bold block sm:inline text-base sm:text-xl">&amp;</span>
                                    {{ $candidate->vice_name }}
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-300 font-semibold mt-2 px-3 py-1 bg-slate-950/70 rounded-full inline-block border border-slate-800">
                                    Kelas: {{ $candidate->chairman_class ?? '-' }} &bull; {{ $candidate->vice_class ?? '-' }}
                                </p>

                                {{-- Visi-Misi Opsional di Mode Sandingan --}}
                                <div x-show="showVisiMisi" x-transition class="mt-3 text-left space-y-2 text-xs">
                                    <div class="bg-slate-950/80 p-3 rounded-xl border border-slate-800">
                                        <span class="text-[10px] font-extrabold uppercase text-amber-400 block mb-0.5">Visi</span>
                                        <p class="text-slate-200 line-clamp-3 leading-relaxed">{{ $candidate->vision ?: '-' }}</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────── --}}
            {{-- B. MODE SLIDE DECK: 1 Paslon per Layar                       --}}
            {{-- ───────────────────────────────────────────────────────────── --}}
            <div x-show="mode === 'slide'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="w-full flex items-center justify-center">

                @foreach($candidates as $index => $candidate)
                    <div x-show="current === {{ $index }}"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="w-full max-w-6xl">

                        {{-- Tampilan Slide DENGAN Visi & Misi --}}
                        <div x-show="showVisiMisi"
                             class="flex flex-col lg:flex-row items-center gap-8 lg:gap-12 p-6 sm:p-8 bg-slate-900/90 border border-slate-800 rounded-3xl shadow-2xl">

                            {{-- Left Column: Nomor Urut & Foto --}}
                            <div class="flex flex-col items-center flex-shrink-0 text-center">
                                <div class="mb-4 inline-flex items-center gap-3 px-6 py-2 rounded-2xl bg-amber-500/20 border-2 border-amber-400 text-amber-300">
                                    <span class="text-xs sm:text-sm uppercase font-bold tracking-widest">Nomor Urut</span>
                                    <span class="text-4xl sm:text-6xl font-black">{{ $candidate->number }}</span>
                                </div>

                                @if($candidate->hasCustomPhoto())
                                    <div class="w-64 h-64 sm:w-80 sm:h-80 rounded-2xl overflow-hidden border-4 border-amber-400 shadow-2xl bg-slate-800">
                                        <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->label }}"
                                             class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="flex items-center gap-4">
                                        <div class="flex flex-col items-center">
                                            <div class="w-32 h-36 sm:w-44 sm:h-52 rounded-2xl overflow-hidden border-2 border-indigo-400 shadow-lg bg-slate-800">
                                                <img src="{{ $candidate->photo_url }}" alt="Ketua: {{ $candidate->chairman_name }}"
                                                     class="w-full h-full object-cover">
                                            </div>
                                            <span class="mt-2 text-xs font-bold uppercase tracking-wider text-indigo-300">Calon Ketua</span>
                                        </div>
                                        <div class="flex flex-col items-center">
                                            <div class="w-32 h-36 sm:w-44 sm:h-52 rounded-2xl overflow-hidden border-2 border-emerald-400 shadow-lg bg-slate-800">
                                                <img src="{{ $candidate->vice_photo_url }}" alt="Wakil: {{ $candidate->vice_name }}"
                                                     class="w-full h-full object-cover">
                                            </div>
                                            <span class="mt-2 text-xs font-bold uppercase tracking-wider text-emerald-300">Calon Wakil</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Right Column: Nama, Kelas, Visi & Misi --}}
                            <div class="flex-1 w-full space-y-4">
                                <div class="border-b border-slate-800 pb-3">
                                    <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                                        {{ $candidate->chairman_name }}
                                        <span class="text-slate-400 font-normal text-xl sm:text-2xl">&amp;</span>
                                        {{ $candidate->vice_name }}
                                    </h2>
                                    <p class="text-sm sm:text-lg text-slate-300 font-medium mt-1">
                                        Kelas: {{ $candidate->chairman_class ?? '-' }} &bull; {{ $candidate->vice_class ?? '-' }}
                                    </p>
                                </div>

                                {{-- Visi --}}
                                <div class="bg-slate-950/60 p-4 sm:p-5 rounded-2xl border border-slate-800/80">
                                    <div class="text-xs uppercase font-extrabold tracking-wider text-amber-400 mb-1 flex items-center gap-1.5">
                                        <span>🎯</span> <span>VISI</span>
                                    </div>
                                    <p class="text-sm sm:text-base text-slate-100 font-medium leading-relaxed">
                                        {{ $candidate->vision ?: 'Tidak ada visi yang dicantumkan.' }}
                                    </p>
                                </div>

                                {{-- Misi --}}
                                <div class="bg-slate-950/60 p-4 sm:p-5 rounded-2xl border border-slate-800/80 max-h-56 overflow-y-auto">
                                    <div class="text-xs uppercase font-extrabold tracking-wider text-amber-400 mb-1 flex items-center gap-1.5">
                                        <span>🚀</span> <span>MISI</span>
                                    </div>
                                    <div class="text-xs sm:text-sm text-slate-200 leading-relaxed space-y-1">
                                        {!! nl2br(e($candidate->mission ?: 'Tidak ada misi yang dicantumkan.')) !!}
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- Tampilan Slide TANPA Visi & Misi (Fokus Foto Besar & Nama) --}}
                        <div x-show="!showVisiMisi"
                             class="max-w-4xl mx-auto flex flex-col items-center text-center p-8 sm:p-10 bg-slate-900/90 border border-slate-800 rounded-3xl shadow-2xl">

                            {{-- Nomor Urut Besar --}}
                            <div class="mb-6 inline-flex items-center gap-4 px-8 py-2.5 rounded-2xl bg-amber-500/20 border-2 border-amber-400 text-amber-300 shadow-xl shadow-amber-500/10">
                                <span class="text-sm uppercase font-extrabold tracking-widest">Nomor Urut</span>
                                <span class="text-5xl sm:text-7xl font-black">{{ $candidate->number }}</span>
                            </div>

                            {{-- Foto Sangat Besar --}}
                            @if($candidate->hasCustomPhoto())
                                <div class="w-64 h-64 sm:w-80 sm:h-80 lg:w-96 lg:h-96 rounded-3xl overflow-hidden border-4 border-amber-400 shadow-2xl bg-slate-850 mb-6">
                                    <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->label }}"
                                         class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="flex items-center justify-center gap-6 sm:gap-8 mb-6">
                                    <div class="flex flex-col items-center">
                                        <div class="w-36 h-48 sm:w-52 sm:h-64 rounded-3xl overflow-hidden border-3 border-indigo-400 shadow-2xl bg-slate-850">
                                            <img src="{{ $candidate->photo_url }}" alt="Ketua: {{ $candidate->chairman_name }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                        <span class="mt-2 text-xs sm:text-sm font-bold uppercase tracking-wider text-indigo-300">Calon Ketua</span>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <div class="w-36 h-48 sm:w-52 sm:h-64 rounded-3xl overflow-hidden border-3 border-emerald-400 shadow-2xl bg-slate-850">
                                            <img src="{{ $candidate->vice_photo_url }}" alt="Wakil: {{ $candidate->vice_name }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                        <span class="mt-2 text-xs sm:text-sm font-bold uppercase tracking-wider text-emerald-300">Calon Wakil</span>
                                    </div>
                                </div>
                            @endif

                            {{-- Nama & Kelas Besar --}}
                            <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-snug">
                                {{ $candidate->chairman_name }}
                                <span class="text-amber-400 font-bold">&amp;</span>
                                {{ $candidate->vice_name }}
                            </h2>
                            <p class="text-base sm:text-xl text-slate-300 font-semibold mt-3 px-5 py-1.5 bg-slate-950/70 rounded-full border border-slate-800">
                                Kelas: {{ $candidate->chairman_class ?? '-' }} &bull; {{ $candidate->vice_class ?? '-' }}
                            </p>
                        </div>

                    </div>
                @endforeach
            </div>

        </div>

        {{-- Bottom Bar Proyektor --}}
        <div class="flex items-center justify-between border-t border-slate-800 pt-3 text-xs text-slate-400">
            {{-- Kontrol Navigasi (Mode Slide) --}}
            <div x-show="mode === 'slide'" class="flex items-center gap-2">
                <button type="button" @click="prev()"
                        class="p-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl transition font-bold"
                        title="Sebelumnya (Panah Kiri)">
                    &larr; Prev
                </button>
                <button type="button" @click="next()"
                        class="p-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl transition font-bold"
                        title="Selanjutnya (Panah Kanan)">
                    Next &rarr;
                </button>
                <span class="ml-2 hidden sm:inline">Navigasi: &larr; / &rarr; &bull; Spasi = Jeda &bull; V = Visi-Misi &bull; M = Mode Sandingan</span>
            </div>

            {{-- Keterangan Mode Sandingan --}}
            <div x-show="mode === 'sandingan'" class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 font-bold text-amber-400">
                    <span>👥 Mode Sandingan Paslon</span>
                </span>
                <span class="hidden sm:inline">&bull; Menampilkan seluruh paslon berdampingan &bull; Tekan V untuk Visi-Misi &bull; M untuk Slide 1 Paslon</span>
            </div>

            {{-- Dots Navigasi (Mode Slide) --}}
            <div x-show="mode === 'slide'" class="flex items-center gap-2">
                @foreach($candidates as $index => $candidate)
                    <button type="button" @click="goTo({{ $index }})"
                            class="h-3 rounded-full transition-all duration-300"
                            :class="current === {{ $index }} ? 'w-8 bg-amber-400 shadow-md shadow-amber-400/50' : 'w-3 bg-slate-700 hover:bg-slate-600'"
                            title="Lihat Paslon {{ $candidate->number }}">
                    </button>
                @endforeach
            </div>

            {{-- Index Counter / Total --}}
            <div class="text-sm font-semibold text-slate-300">
                <template x-if="mode === 'slide'">
                    <span>Paslon <span class="text-amber-400 font-bold" x-text="current + 1"></span> dari {{ $candidates->count() }}</span>
                </template>
                <template x-if="mode === 'sandingan'">
                    <span>Total <span class="text-amber-400 font-bold">{{ $candidates->count() }}</span> Pasangan Calon</span>
                </template>
            </div>
        </div>

    </div>

    <script>
        function slideDeck(config) {
            return {
                current: 0,
                total: config.total,
                paused: false,
                timer: null,
                mode: config.initialMode || 'slide', // 'slide' | 'sandingan'
                showVisiMisi: config.initialVisiMisi !== undefined ? config.initialVisiMisi : true,
                init() {
                    this.startTimer();
                    window.addEventListener('keydown', (e) => {
                        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

                        if (e.code === 'Space') {
                            e.preventDefault();
                            this.togglePause();
                        } else if (e.code === 'ArrowRight') {
                            if (this.mode === 'slide') this.next();
                        } else if (e.code === 'ArrowLeft') {
                            if (this.mode === 'slide') this.prev();
                        } else if (e.key === 'v' || e.key === 'V') {
                            this.toggleVisiMisi();
                        } else if (e.key === 'm' || e.key === 'M') {
                            this.toggleMode();
                        }
                    });
                },
                startTimer() {
                    if (this.timer) clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        if (!this.paused && this.total > 1 && this.mode === 'slide') {
                            this.next();
                        }
                    }, 20000);
                },
                togglePause() {
                    this.paused = !this.paused;
                },
                setMode(newMode) {
                    this.mode = newMode;
                    if (newMode === 'sandingan') {
                        this.showVisiMisi = false; // default tanpa visi misi di mode sandingan
                    }
                },
                toggleMode() {
                    this.setMode(this.mode === 'slide' ? 'sandingan' : 'slide');
                },
                toggleVisiMisi() {
                    this.showVisiMisi = !this.showVisiMisi;
                },
                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(() => {});
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen().catch(() => {});
                        }
                    }
                },
                next() {
                    this.current = (this.current + 1) % this.total;
                },
                prev() {
                    this.current = (this.current - 1 + this.total) % this.total;
                },
                goTo(idx) {
                    this.current = idx;
                }
            }
        }
    </script>

@else
    {{-- ================================================================= --}}
    {{-- TAMPILAN DEFAULT: GRID KARTU PASLON                               --}}
    {{-- ================================================================= --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

        {{-- Page Heading --}}
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Kandidat Pasangan Calon
                </h1>
                <p class="text-sm text-slate-300 mt-1">
                    Kenali visi dan misi pasangan calon ketua dan wakil ketua OSIS untuk periode {{ $period->title }}.
                </p>
            </div>
            <div>
                <a href="{{ route('election.candidates', ['tampilan' => 'slide']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 text-sm font-bold rounded-xl transition shadow-lg shadow-amber-500/20">
                    🖥️ <span>Buka Tampilan Proyektor</span>
                </a>
            </div>
        </div>

        {{-- Grid Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch">
            @forelse($candidates as $candidate)
                <div class="flex flex-col h-full bg-slate-800/90 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl hover:border-slate-600 transition">

                    {{-- Card Header: Paslon Number Badge & Photos --}}
                    <div class="p-6 bg-slate-850/60 border-b border-slate-700/50 flex flex-col items-center">
                        <div class="w-full flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-700/60 text-slate-300 border border-slate-600">
                                Paslon Resmi
                            </span>
                            <div class="w-12 h-12 rounded-xl bg-amber-500 text-slate-950 font-black text-2xl flex items-center justify-center shadow-md">
                                {{ $candidate->number }}
                            </div>
                        </div>

                        {{-- Photo Container --}}
                        @if($candidate->hasCustomPhoto())
                            <div class="w-full h-56 rounded-xl overflow-hidden border border-slate-700 bg-slate-900 shadow">
                                <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->label }}"
                                     class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="w-full grid grid-cols-2 gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-full h-44 rounded-xl overflow-hidden border-2 border-indigo-500/70 bg-slate-900 shadow">
                                        <img src="{{ $candidate->photo_url }}" alt="Ketua: {{ $candidate->chairman_name }}"
                                             class="w-full h-full object-cover">
                                    </div>
                                    <span class="mt-1.5 text-[11px] font-bold uppercase tracking-wider text-indigo-300">Calon Ketua</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <div class="w-full h-44 rounded-xl overflow-hidden border-2 border-emerald-500/70 bg-slate-900 shadow">
                                        <img src="{{ $candidate->vice_photo_url }}" alt="Wakil: {{ $candidate->vice_name }}"
                                             class="w-full h-full object-cover">
                                    </div>
                                    <span class="mt-1.5 text-[11px] font-bold uppercase tracking-wider text-emerald-300">Calon Wakil</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Card Body --}}
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-5">
                        {{-- Candidate Names & Classes --}}
                        <div>
                            <div class="border-b border-slate-700/50 pb-3">
                                <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold mb-1">Pasangan Calon</div>
                                <h3 class="text-lg font-bold text-white leading-snug">
                                    {{ $candidate->chairman_name }}
                                </h3>
                                <p class="text-xs text-indigo-300 font-medium">
                                    Ketua &bull; Kelas {{ $candidate->chairman_class ?? '-' }}
                                </p>
                                <h3 class="text-lg font-bold text-white leading-snug mt-2">
                                    {{ $candidate->vice_name }}
                                </h3>
                                <p class="text-xs text-emerald-300 font-medium">
                                    Wakil &bull; Kelas {{ $candidate->vice_class ?? '-' }}
                                </p>
                            </div>

                            {{-- Visi --}}
                            <div class="mt-4">
                                <span class="text-xs uppercase font-extrabold tracking-wider text-amber-400">Visi</span>
                                <p class="text-sm text-slate-300 mt-1 leading-relaxed">
                                    {{ $candidate->vision ?: '-' }}
                                </p>
                            </div>

                            {{-- Misi --}}
                            <div class="mt-4">
                                <span class="text-xs uppercase font-extrabold tracking-wider text-amber-400">Misi</span>
                                <div class="text-sm text-slate-300 mt-1 leading-relaxed">
                                    {!! nl2br(e($candidate->mission ?: '-')) !!}
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            @empty
                <div class="col-span-full text-center py-12 text-slate-400">
                    Belum ada calon paslon yang terdaftar.
                </div>
            @endforelse
        </div>

    </div>
@endif
@endsection
