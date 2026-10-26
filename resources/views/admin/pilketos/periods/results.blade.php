@extends('layouts.app')

@section('title', 'Hasil Pilketos — ' . $period->title)

@section('content')
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
                <h1 class="text-xl font-bold text-slate-800 dark:text-white">📊 Hasil Resmi Pilketos</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ $period->title }} &middot; Status: <span class="font-semibold text-red-600">Ditutup</span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pilketos.periods.results.export', $period) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                📥 Unduh Excel Rekap
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

    {{-- Pemenang / Seri Banner --}}
    @if($tally['is_tie'])
        <div class="p-5 bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-300 dark:border-amber-700 rounded-xl text-center">
            <div class="text-3xl mb-1">⚖️</div>
            <h2 class="text-lg font-bold text-amber-800 dark:text-amber-200">Hasil Pemilihan Seri</h2>
            <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">
                Terdapat lebih dari satu paslon yang memperoleh suara tertinggi yang sama.
            </p>
        </div>
    @elseif($tally['winner'])
        <div class="p-6 bg-gradient-to-r from-amber-500 to-indigo-600 rounded-xl text-white shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 text-center sm:text-left">
                <div class="text-5xl">🏆</div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-200">Pemenang Pemilihan</span>
                    <h2 class="text-2xl font-bold mt-0.5">
                        Paslon {{ $tally['winner']['candidate']->number }} &mdash; {{ $tally['winner']['candidate']->chairman_name }} &amp; {{ $tally['winner']['candidate']->vice_name }}
                    </h2>
                    <p class="text-xs text-indigo-100 mt-1">
                        Perolehan: <strong>{{ $tally['winner']['votes'] }} suara</strong>
                        @if($tally['total_votes'] > 0)
                            ({{ round(($tally['winner']['votes'] / $tally['total_votes']) * 100, 1) }}%)
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Perolehan Suara Paslon & Suara Kosong --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-slate-800 dark:text-white">Perolehan Suara</h2>
                <p class="text-xs text-slate-500">Total Suara Masuk: <strong>{{ $tally['total_votes'] }}</strong></p>
            </div>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @php
                $maxVotes = $tally['max_votes'] ?? ($tally['results']->max('votes') ?? 0);
            @endphp
            @foreach($tally['results'] as $index => $row)
                @php
                    $pct = $tally['total_votes'] > 0 ? round(($row['votes'] / $tally['total_votes']) * 100, 1) : 0;
                    $isTop = $row['votes'] > 0 && $row['votes'] === $maxVotes;
                @endphp
                <div class="p-5 hover:bg-slate-50 dark:hover:bg-slate-700/20 transition">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full {{ $isTop ? 'bg-amber-500 text-slate-950 font-black' : 'bg-indigo-600 text-white font-bold' }} flex items-center justify-center flex-shrink-0 shadow transition-colors">
                            {{ $row['candidate']->number }}
                        </div>
                        @if($row['candidate']->hasCustomPhoto())
                            <img src="{{ $row['candidate']->photo_url }}" alt=""
                                 class="w-12 h-12 rounded-xl object-cover border {{ $isTop ? 'border-amber-400' : 'border-slate-200 dark:border-slate-600' }} flex-shrink-0">
                        @else
                            <div class="flex items-center -space-x-2 flex-shrink-0">
                                <img src="{{ $row['candidate']->photo_url }}" alt="" title="Ketua: {{ $row['candidate']->chairman_name }}"
                                     class="w-11 h-11 rounded-xl object-cover border-2 {{ $isTop ? 'border-amber-400' : 'border-indigo-500' }} shadow bg-white dark:bg-slate-800">
                                <img src="{{ $row['candidate']->vice_photo_url }}" alt="" title="Wakil: {{ $row['candidate']->vice_name }}"
                                     class="w-11 h-11 rounded-xl object-cover border-2 {{ $isTop ? 'border-amber-400' : 'border-emerald-500' }} shadow bg-white dark:bg-slate-800">
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-800 dark:text-white text-sm">
                                    {{ $row['candidate']->chairman_name }} &amp; {{ $row['candidate']->vice_name }}
                                </span>
                                @if($isTop && !$tally['is_tie'])
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
                                        Terpilih
                                    </span>
                                @elseif($isTop && $tally['is_tie'])
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
                                        Suara Tertinggi (Seri)
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                Ketua: {{ $row['candidate']->chairman_class ?? '-' }} &bull; Wakil: {{ $row['candidate']->vice_class ?? '-' }}
                            </div>

                            <div class="mt-2 w-full h-2.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-2.5 {{ $isTop ? 'bg-amber-500' : 'bg-indigo-600' }} rounded-full transition-all duration-700"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 pl-2">
                            <div class="text-xl font-bold {{ $isTop ? 'text-amber-500 dark:text-amber-400' : 'text-slate-800 dark:text-white' }}">{{ $row['votes'] }}</div>
                            <div class="text-xs text-slate-400">{{ $pct }}%</div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Suara Kosong (hanya jika ada data suara kosong tercatat) --}}
            @if($tally['blank_count'] > 0)
                @php
                    $blankPct = $tally['total_votes'] > 0 ? round(($tally['blank_count'] / $tally['total_votes']) * 100, 1) : 0;
                @endphp
                <div class="p-5 bg-slate-50/50 dark:bg-slate-900/30">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-slate-400 text-white font-bold flex items-center justify-center flex-shrink-0 text-sm">
                            &Oslash;
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-700 dark:text-slate-300 text-sm">
                                Suara Kosong (Kotak Kosong)
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5">Pemilih memilih opsi suara kosong</div>
                            <div class="mt-2 w-full h-2.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-2.5 bg-slate-400 rounded-full transition-all duration-700"
                                     style="width: {{ $blankPct }}%"></div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 pl-2">
                            <div class="text-xl font-bold text-slate-600 dark:text-slate-400">{{ $tally['blank_count'] }}</div>
                            <div class="text-xs text-slate-400">{{ $blankPct }}%</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Blok Rekonsiliasi & Konsistensi Data --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
        <h2 class="font-semibold text-slate-800 dark:text-white text-base mb-1">🔍 Rekonsiliasi Suara &amp; Konsistensi</h2>
        <p class="text-xs text-slate-500 mb-4">
            Pemeriksaan integritas antara data suara di kotak digital, status kehadiran pemilih, dan fisik kertas kredensial yang dikumpulkan.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40">
                <span class="text-xs text-slate-500 block">A. Suara Tercatat di Sistem</span>
                <span class="text-2xl font-bold text-slate-800 dark:text-white">{{ $consistency['votes_count'] }}</span>
            </div>
            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40">
                <span class="text-xs text-slate-500 block">B. Pemilih Berstatus "Sudah Memilih"</span>
                <span class="text-2xl font-bold text-slate-800 dark:text-white">{{ $consistency['voters_voted_count'] }}</span>
                <span class="text-xs {{ $consistency['votes_match_voters'] ? 'text-green-600' : 'text-red-500' }} block mt-1">
                    {{ $consistency['votes_match_voters'] ? '✓ Cocok dengan Suara' : '✗ Tidak Cocok' }}
                </span>
            </div>
            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40">
                <span class="text-xs text-slate-500 block">C. Kertas Fisik Terkumpul</span>
                <span class="text-2xl font-bold text-slate-800 dark:text-white">
                    {{ $consistency['collected_cards_count'] ?? '—' }}
                </span>
                @if($consistency['collected_cards_count'] !== null)
                    <span class="text-xs {{ $consistency['votes_match_cards'] ? 'text-green-600' : 'text-amber-500' }} block mt-1">
                        {{ $consistency['votes_match_cards'] ? '✓ Cocok dengan Suara' : 'Selisih: ' . ($consistency['collected_cards_count'] - $consistency['votes_count']) }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Form input collected_cards_count --}}
        <form method="POST" action="{{ route('admin.pilketos.periods.collected-cards', $period) }}" class="flex items-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700 no-loading">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                    Input Jumlah Kertas Fisik Terkumpul
                </label>
                <input type="number" name="collected_cards_count" min="0" required
                       value="{{ $period->collected_cards_count }}"
                       placeholder="Jumlah lembar"
                       class="px-3 py-1.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-white w-48">
            </div>
            <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition">
                Simpan &amp; Verifikasi
            </button>
        </form>
    </div>

    {{-- Partisipasi per Kelas --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700">
            <h2 class="font-semibold text-slate-800 dark:text-white">Partisipasi per Kelas</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 border-b border-slate-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">Kelas</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Total DPT</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Menggunakan Hak Suara</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Tingkat Partisipasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($participation['per_class'] as $row)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-4 py-2.5 font-medium text-slate-800 dark:text-white">
                                {{ $row['class_name'] }}
                            </td>
                            <td class="px-4 py-2.5 text-center text-slate-600 dark:text-slate-400">
                                {{ $row['total'] }}
                            </td>
                            <td class="px-4 py-2.5 text-center font-semibold text-green-600 dark:text-green-400">
                                {{ $row['voted'] }}
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $row['percentage'] }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-slate-400">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
