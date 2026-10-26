@extends('election.public.layout')

@section('title', 'Kandidat Paslon — ' . $period->title)
@section('header_subtitle', $period->title . ' — Profil Paslon')

@if($isSlide)
    @section('hide_header', '1')
@else
    @section('header_action')
        <a href="{{ route('election.candidates', ['tampilan' => 'slide']) }}"
           class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold rounded-lg transition shadow-md shadow-amber-500/20">
            🖥️ <span>Mode Proyektor</span>
        </a>
    @endsection
@endif

@section('content')
@if($isSlide)
    {{-- ================================================================= --}}
    {{-- TAMPILAN PROYEKTOR / SLIDE DECK (1 Paslon per Layar)              --}}
    {{-- ================================================================= --}}
    <div x-data="slideDeck({ total: {{ $candidates->count() }} })"
         x-init="init()"
         class="h-screen w-screen flex flex-col justify-between p-6 sm:p-10 bg-slate-950 text-white select-none overflow-hidden fixed inset-0 z-50">

        {{-- Top Bar Proyektor --}}
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">🗳️</span>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-wider text-amber-400 uppercase">
                        {{ $settings['school_name'] ?? 'PILKETOS DIGITAL' }}
                    </h1>
                    <p class="text-xs sm:text-sm font-semibold text-slate-400">
                        {{ $period->title }} &bull; Profil Pasangan Calon
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                {{-- Pause/Play Indicator --}}
                <button type="button" @click="togglePause()"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold transition"
                        :class="paused ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-slate-800 text-slate-300 border border-slate-700'">
                    <span x-text="paused ? '⏸ Dijeda (Tekan SPASI untuk lanjut)' : '▶ Auto-slide aktif (20s)'"></span>
                </button>

                {{-- Exit Slide Mode --}}
                <a href="{{ route('election.candidates') }}"
                   class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg text-xs font-semibold text-slate-300 transition">
                    ✕ Keluar Mode Proyektor
                </a>
            </div>
        </div>

        {{-- Slide Content (1 Paslon per view) --}}
        <div class="flex-1 flex items-center justify-center my-4">
            @foreach($candidates as $index => $candidate)
                <div x-show="current === {{ $index }}"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="w-full max-w-6xl flex flex-col lg:flex-row items-center gap-8 lg:gap-12 p-6 sm:p-8 bg-slate-900/90 border border-slate-800 rounded-3xl shadow-2xl">

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
            @endforeach
        </div>

        {{-- Bottom Bar Proyektor: Navigasi Keyboard, Dots, Indikator --}}
        <div class="flex items-center justify-between border-t border-slate-800 pt-4">
            <div class="flex items-center gap-2">
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
                <span class="text-xs text-slate-400 ml-2 hidden sm:inline">Gunakan tombol &larr; / &rarr; pada keyboard &bull; Spasi = Jeda</span>
            </div>

            {{-- Dots --}}
            <div class="flex items-center gap-2">
                @foreach($candidates as $index => $candidate)
                    <button type="button" @click="goTo({{ $index }})"
                            class="h-3 rounded-full transition-all duration-300"
                            :class="current === {{ $index }} ? 'w-8 bg-amber-400 shadow-md shadow-amber-400/50' : 'w-3 bg-slate-700 hover:bg-slate-600'"
                            title="Lihat Paslon {{ $candidate->number }}">
                    </button>
                @endforeach
            </div>

            {{-- Index Counter --}}
            <div class="text-sm font-semibold text-slate-300">
                Paslon <span class="text-amber-400 font-bold" x-text="current + 1"></span> dari {{ $candidates->count() }}
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
                init() {
                    this.startTimer();
                    window.addEventListener('keydown', (e) => {
                        if (e.code === 'Space') {
                            e.preventDefault();
                            this.togglePause();
                        } else if (e.code === 'ArrowRight') {
                            this.next();
                        } else if (e.code === 'ArrowLeft') {
                            this.prev();
                        }
                    });
                },
                startTimer() {
                    if (this.timer) clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        if (!this.paused && this.total > 1) {
                            this.next();
                        }
                    }, 20000);
                },
                togglePause() {
                    this.paused = !this.paused;
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
