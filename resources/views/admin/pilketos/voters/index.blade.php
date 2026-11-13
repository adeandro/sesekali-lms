@extends('layouts.app')

@section('title', 'Daftar Pemilih — ' . $period->title)

@section('content')
@php
    $hasActiveFilter = request()->filled('class') || request()->filled('voted') || request()->filled('printed') || request()->filled('search') || (request()->filled('sort') && request('sort') !== 'class_asc');
@endphp
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pilketos.periods.show', $period) }}"
               class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white">📋 Daftar Pemilih</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ $period->title }} &bull; Total DPT: <span class="font-bold text-slate-700 dark:text-slate-300">{{ number_format($periodTotal) }}</span> pemilih
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.pilketos.periods.voters.export', [$period] + request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition shadow-sm"
               title="{{ $hasActiveFilter ? 'Unduh Excel sesuai filter yang aktif' : 'Unduh seluruh DPT' }}">
                📥 Unduh Excel {{ $hasActiveFilter ? '(Sesuai Filter)' : 'DPT' }}
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg text-green-700 dark:text-green-300 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg text-red-700 dark:text-red-300 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Ringkasan per Kelas & Cetak Kartu --}}
    @if($summary->isNotEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="font-semibold text-slate-800 dark:text-white text-xs uppercase tracking-wider">
                        Ringkasan per Kelas &amp; Cetak Kartu
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Klik kelas untuk memfilter daftar pemilih di bawah.</p>
                </div>
                @if(request('class'))
                    <a href="{{ route('admin.pilketos.periods.voters.index', [$period] + request()->except(['class', 'page'])) }}"
                       class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold inline-flex items-center gap-1">
                        ✕ Hapus Filter Kelas (Tampilkan Semua)
                    </a>
                @endif
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($summary as $row)
                    @php
                        $isCurrentClass = request('class') === ($row->class_name ?? 'Tanpa Kelas');
                        $unvotedCount = $row->total - $row->voted;
                        $pctVoted = $row->total > 0 ? round(($row->voted / $row->total) * 100) : 0;
                    @endphp
                    <div class="p-3 border rounded-xl transition-all relative flex flex-col justify-between
                        {{ $isCurrentClass
                            ? 'border-indigo-500 bg-indigo-50/70 dark:bg-indigo-950/40 ring-2 ring-indigo-500/50 shadow-sm'
                            : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 hover:border-slate-300 dark:hover:border-slate-600' }}">

                        <div>
                            {{-- Baris Atas: Nama Kelas + Tombol Cetak --}}
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <a href="{{ route('admin.pilketos.periods.voters.index', [$period] + array_merge(request()->except(['class', 'page']), ['class' => $row->class_name ?? 'Tanpa Kelas'])) }}"
                                   class="font-bold text-sm text-slate-800 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition flex items-center gap-1.5"
                                   title="Filter hanya kelas ini">
                                    @if($row->class_name === 'GURU & STAF')
                                        👨‍🏫 {{ $row->class_name }}
                                    @else
                                        {{ $row->class_name ?? 'Tanpa Kelas' }}
                                    @endif
                                    @if($isCurrentClass)
                                        <span class="inline-block w-2 h-2 rounded-full bg-indigo-600"></span>
                                    @endif
                                </a>

                                @if($period->status !== 'closed' && ($row->class_id !== null || $row->class_name === 'GURU & STAF'))
                                    <form method="POST" action="{{ route('admin.pilketos.periods.cards.class', [$period, $row->class_id ?? 0]) }}" class="no-loading flex-shrink-0">
                                        @csrf
                                        <button type="submit"
                                                class="px-2 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-[11px] font-semibold rounded transition"
                                                title="Cetak seluruh kartu kelas ini">
                                            🖨️ Cetak
                                        </button>
                                    </form>
                                @endif
                            </div>

                            {{-- Info Total & Status Cetak --}}
                            <div class="text-xs text-slate-500 mb-2">
                                <span>{{ $row->total }} {{ $row->class_name === 'GURU & STAF' ? 'orang' : 'siswa' }}</span>
                                &bull;
                                @if($row->printed > 0)
                                    <span class="text-green-600 dark:text-green-400 font-medium">{{ $row->printed }} dicetak</span>
                                @else
                                    <span class="text-amber-500 font-medium">Belum dicetak</span>
                                @endif
                            </div>

                            {{-- Progress Bar Hak Suara --}}
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden mb-2">
                                <div class="bg-emerald-500 h-full rounded-full transition-all duration-300"
                                     style="width: {{ $pctVoted }}%"></div>
                            </div>
                        </div>

                        {{-- Quick Link ke Belum Memilih di kelas ini --}}
                        <div class="flex items-center justify-between text-[11px] pt-1 border-t border-slate-200/60 dark:border-slate-700/60 mt-1">
                            <span class="text-slate-600 dark:text-slate-400 font-medium">
                                {{ $row->voted }}/{{ $row->total }} memilih ({{ $pctVoted }}%)
                            </span>
                            @if($unvotedCount > 0)
                                <a href="{{ route('admin.pilketos.periods.voters.index', [$period] + array_merge(request()->except(['class', 'voted', 'page']), ['class' => $row->class_name ?? 'Tanpa Kelas', 'voted' => 'belum'])) }}"
                                   class="text-rose-600 dark:text-rose-400 hover:underline font-bold"
                                   title="Lihat {{ $unvotedCount }} orang yang belum memilih di kelas ini">
                                    {{ $unvotedCount }} belum &rarr;
                                </a>
                            @else
                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Lengkap ✓</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Filter, Search & Sort Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 space-y-4">

        {{-- Quick Tabs Hak Suara --}}
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700">
            <div class="flex flex-wrap items-center gap-1.5">
                {{-- Tab: Semua --}}
                <a href="{{ route('admin.pilketos.periods.voters.index', [$period] + request()->except(['voted', 'page'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-2
                          {{ !request()->filled('voted') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    <span>Semua Pemilih</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ !request()->filled('voted') ? 'bg-indigo-700 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-200' }}">
                        {{ number_format($periodTotal) }}
                    </span>
                </a>

                {{-- Tab: Belum Memilih (Paling dicari panitia!) --}}
                <a href="{{ route('admin.pilketos.periods.voters.index', [$period] + array_merge(request()->except(['voted', 'page']), ['voted' => 'belum'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-2
                          {{ request('voted') === 'belum' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 hover:bg-rose-100' }}">
                    <span>⚠️ Belum Memilih</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('voted') === 'belum' ? 'bg-rose-700 text-white' : 'bg-rose-200 dark:bg-rose-900 text-rose-800 dark:text-rose-200' }}">
                        {{ number_format($periodUnvoted) }}
                    </span>
                </a>

                {{-- Tab: Sudah Memilih --}}
                <a href="{{ route('admin.pilketos.periods.voters.index', [$period] + array_merge(request()->except(['voted', 'page']), ['voted' => 'sudah'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-2
                          {{ request('voted') === 'sudah' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100' }}">
                    <span>✓ Sudah Memilih</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('voted') === 'sudah' ? 'bg-emerald-700 text-white' : 'bg-emerald-200 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200' }}">
                        {{ number_format($periodVoted) }}
                    </span>
                </a>
            </div>

            {{-- Stat ringkas filter saat ini --}}
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Menampilkan <strong class="text-slate-800 dark:text-slate-200">{{ number_format($filteredTotal) }}</strong> data
                @if($hasActiveFilter)
                    (<strong>{{ number_format($filteredUnvoted) }}</strong> belum memilih, <strong>{{ number_format($filteredVoted) }}</strong> sudah)
                @endif
            </div>
        </div>

        {{-- Form Filter Lengkap: Cari, Kelas, Status Kartu, Urutan (Sort) --}}
        <form method="GET" action="{{ route('admin.pilketos.periods.voters.index', $period) }}" class="no-loading">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

                {{-- 1. Cari Nama / NIS / ID --}}
                <div class="lg:col-span-1">
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                        Cari Pemilih
                    </label>
                    <div class="relative">
                        <input type="text"
                               name="search"
                               value="{{ $search }}"
                               placeholder="Nama atau NIS / ID..."
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @if($search)
                            <a href="{{ route('admin.pilketos.periods.voters.index', [$period] + request()->except(['search', 'page'])) }}"
                               class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">✕</a>
                        @endif
                    </div>
                </div>

                {{-- 2. Filter Kelas --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                        Kelas / Kelompok
                    </label>
                    <select name="class"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Kelas</option>
                        @foreach($classList as $c)
                            <option value="{{ $c }}" {{ $selectedClass === $c ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Filter Status Hak Suara --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                        Hak Suara
                    </label>
                    <select name="voted"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Status Hak Suara</option>
                        <option value="belum" {{ $selectedVoted === 'belum' ? 'selected' : '' }}>⚠️ Belum Memilih</option>
                        <option value="sudah" {{ $selectedVoted === 'sudah' ? 'selected' : '' }}>✓ Sudah Memilih</option>
                    </select>
                </div>

                {{-- 4. Filter Status Kartu --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                        Status Kartu
                    </label>
                    <select name="printed"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Status Kartu</option>
                        <option value="sudah" {{ $selectedPrinted === 'sudah' ? 'selected' : '' }}>Sudah Dicetak</option>
                        <option value="belum" {{ $selectedPrinted === 'belum' ? 'selected' : '' }}>Belum Dicetak</option>
                    </select>
                </div>

                {{-- 5. Urutan (Sort / Short) --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                        Urutkan (Sort)
                    </label>
                    <select name="sort"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="class_asc" {{ $sort === 'class_asc' ? 'selected' : '' }}>Kelas &amp; Nama (A-Z)</option>
                        <option value="unvoted_first" {{ $sort === 'unvoted_first' ? 'selected' : '' }}>⚠️ Belum Memilih Teratas</option>
                        <option value="voted_first" {{ $sort === 'voted_first' ? 'selected' : '' }}>✓ Sudah Memilih Teratas</option>
                        <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Nama Pemilih (A-Z)</option>
                        <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Nama Pemilih (Z-A)</option>
                        <option value="nis_asc" {{ $sort === 'nis_asc' ? 'selected' : '' }}>NIS / NIP / ID</option>
                        <option value="voted_at_desc" {{ $sort === 'voted_at_desc' ? 'selected' : '' }}>Waktu Memilih Terbaru</option>
                    </select>
                </div>

            </div>

            {{-- Tombol Aksi Filter --}}
            <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-100 dark:border-slate-700">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition shadow-sm flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Terapkan Filter &amp; Urutan
                    </button>

                    @if($hasActiveFilter)
                        <a href="{{ route('admin.pilketos.periods.voters.index', $period) }}"
                           class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg transition">
                            ↺ Reset Semua Filter
                        </a>
                    @endif
                </div>

                {{-- Indikator Filter Aktif --}}
                @if($hasActiveFilter)
                    <div class="hidden sm:flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="font-medium">Filter aktif:</span>
                        @if($selectedClass)
                            <span class="px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-medium">
                                Kelas: {{ $selectedClass }}
                            </span>
                        @endif
                        @if($selectedVoted)
                            <span class="px-2 py-0.5 rounded font-medium {{ $selectedVoted === 'belum' ? 'bg-rose-50 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                                Hak Suara: {{ $selectedVoted === 'belum' ? 'Belum' : 'Sudah' }}
                            </span>
                        @endif
                        @if($sort && $sort !== 'class_asc')
                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium">
                                Sort: {{ $sort }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </form>

    </div>

    {{-- Tabel Pemilih --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 border-b border-slate-100 dark:border-slate-700">
                <tr>
                    <th class="px-5 py-3 font-semibold w-12">#</th>
                    <th class="px-5 py-3 font-semibold">Nama Pemilih</th>
                    <th class="px-5 py-3 font-semibold">NIS / NIP / ID</th>
                    <th class="px-5 py-3 font-semibold">Kelas / Kelompok</th>
                    <th class="px-5 py-3 font-semibold text-center">Status Kartu</th>
                    <th class="px-5 py-3 font-semibold text-center">Hak Suara</th>
                    <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($voters as $voter)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors {{ !$voter->has_voted && request('voted') === 'belum' ? 'bg-rose-50/20' : '' }}">
                        <td class="px-5 py-3 text-slate-400">{{ $voters->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-3">
                            <span class="font-bold text-slate-800 dark:text-white block">
                                {{ $voter->student_name }}
                            </span>
                        </td>
                        <td class="px-5 py-3 font-mono text-slate-600 dark:text-slate-400">
                            {{ $voter->nis }}
                        </td>
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.pilketos.periods.voters.index', [$period] + array_merge(request()->except(['class', 'page']), ['class' => $voter->class_name])) }}"
                               class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-950/40 transition"
                               title="Filter hanya kelas ini">
                                {{ $voter->class_name ?? '-' }}
                            </a>
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($voter->token_hash)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                    Dicetak (v{{ $voter->token_version }})
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">
                                    Belum
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($voter->has_voted)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Sudah Memilih
                                </span>
                                @if($voter->voted_at)
                                    <span class="block text-[10px] text-slate-400 mt-0.5">
                                        {{ $voter->voted_at->format('H:i') }} WIB
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">
                                    ⚠️ Belum Memilih
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            @if($period->status !== 'closed' && !$voter->has_voted)
                                <form method="POST" action="{{ route('admin.pilketos.periods.cards.voter', [$period, $voter]) }}" class="inline no-loading">
                                    @csrf
                                    <button type="submit"
                                            class="px-2.5 py-1 text-xs border border-amber-300 dark:border-amber-600 text-amber-700 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded transition font-medium"
                                            title="Cetak ulang akan menghanguskan token lama">
                                        🖨️ Cetak Ulang
                                    </button>
                                </form>
                            @elseif($voter->has_voted)
                                <span class="text-xs text-slate-400 font-medium">Selesai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center">
                            <div class="max-w-sm mx-auto text-center space-y-3">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 text-xl">
                                    🔍
                                </div>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Tidak ada data pemilih yang cocok
                                </p>
                                <p class="text-xs text-slate-500">
                                    @if($hasActiveFilter)
                                        Tidak ditemukan pemilih dengan kriteria filter yang Anda tentukan. Coba ubah atau reset filter.
                                    @else
                                        Belum ada pemilih yang terdaftar pada periode ini.
                                    @endif
                                </p>
                                @if($hasActiveFilter)
                                    <a href="{{ route('admin.pilketos.periods.voters.index', $period) }}"
                                       class="inline-block px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition">
                                        ↺ Reset Filter
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($voters->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">
                {{ $voters->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

