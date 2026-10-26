@extends('election.public.layout')

@section('title', 'Hasil Resmi — ' . $period->title)
@section('header_subtitle', $period->title . ' — Rekapitulasi Suara')

@section('header_action')
    <div class="inline-flex items-center gap-2 px-3 py-1 bg-red-600/90 border border-red-500 text-white text-xs font-bold rounded-lg shadow-sm">
        <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
        <span>HASIL RESMI DITUTUP</span>
    </div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-8">

    {{-- Header Banner Info --}}
    <div class="text-center space-y-2">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-400/30 text-indigo-300 text-xs font-bold tracking-wide uppercase">
            <span>🗳️ Rekapitulasi Akhir Pilketos</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
            {{ $period->title }}
        </h1>
        <p class="text-sm sm:text-base text-slate-300">
            {{ $settings['school_name'] ?? 'Bilik Suara Digital' }}
            @if($period->results_published_at)
                &bull; Diumumkan pada {{ $period->results_published_at->format('d/m/Y H:i') }} WIB
            @endif
        </p>
    </div>

    {{-- Pemenang / Seri Banner --}}
    @if($tally['is_tie'])
        <div class="p-6 sm:p-8 bg-amber-500/15 border-2 border-amber-400 rounded-3xl text-center shadow-xl">
            <div class="text-5xl mb-2">⚖️</div>
            <h2 class="text-2xl font-black text-amber-300">Hasil Pemilihan Seri</h2>
            <p class="text-sm text-amber-200 mt-2 max-w-xl mx-auto">
                Terdapat lebih dari satu pasangan calon yang memperoleh suara tertinggi yang sama (masing-masing <strong>{{ $tally['max_votes'] }} suara</strong>). Hasil akhir menunggu keputusan pleno panitia pemilihan.
            </p>
        </div>
    @elseif($tally['winner'])
        <div class="p-6 sm:p-8 bg-gradient-to-r from-amber-600 via-amber-500 to-indigo-700 rounded-3xl text-white shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
                <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur border border-white/30 flex items-center justify-center text-5xl flex-shrink-0 shadow-lg">
                    🏆
                </div>
                <div>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-slate-950/40 text-amber-200 border border-amber-300/40 mb-1">
                        Pemenang Terpilih
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black mt-1">
                        Paslon {{ $tally['winner']['candidate']->number }} &mdash; {{ $tally['winner']['candidate']->chairman_name }} &amp; {{ $tally['winner']['candidate']->vice_name }}
                    </h2>
                    <p class="text-sm text-indigo-100 mt-1 font-medium">
                        Ketua: {{ $tally['winner']['candidate']->chairman_class ?? '-' }} &bull; Wakil: {{ $tally['winner']['candidate']->vice_class ?? '-' }}
                    </p>
                </div>
            </div>
            <div class="text-center sm:text-right flex-shrink-0 bg-slate-950/40 px-6 py-4 rounded-2xl border border-white/20">
                <div class="text-3xl sm:text-4xl font-black text-amber-300">{{ $tally['winner']['votes'] }}</div>
                <div class="text-xs font-bold text-slate-200 uppercase tracking-wider">
                    Suara Terkumpul
                    @if($tally['total_votes'] > 0)
                        ({{ round(($tally['winner']['votes'] / $tally['total_votes']) * 100, 1) }}%)
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Perolehan Suara Paslon --}}
    <div class="bg-slate-800/90 border border-slate-700 rounded-3xl overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-slate-700/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-white">Rincian Perolehan Suara</h2>
                <p class="text-xs text-slate-400">Total Suara Sah &amp; Masuk: <strong class="text-slate-200">{{ $tally['total_votes'] }}</strong> suara</p>
            </div>
        </div>

        <div class="divide-y divide-slate-700/60">
            @php
                $maxVotes = $tally['max_votes'] ?? ($tally['results']->max('votes') ?? 0);
            @endphp
            @foreach($tally['results'] as $row)
                @php
                    $pct = $tally['total_votes'] > 0 ? round(($row['votes'] / $tally['total_votes']) * 100, 1) : 0;
                    $isTop = $row['votes'] > 0 && $row['votes'] === $maxVotes;
                @endphp
                <div class="p-6 hover:bg-slate-750/30 transition">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-5">

                        {{-- Nomor & Foto Paslon --}}
                        <div class="flex items-center gap-4 flex-shrink-0">
                            <div class="w-12 h-12 rounded-2xl {{ $isTop ? 'bg-amber-500 text-slate-950 font-black' : 'bg-indigo-600 text-white font-bold' }} flex items-center justify-center text-xl flex-shrink-0 shadow-lg">
                                {{ $row['candidate']->number }}
                            </div>

                            @if($row['candidate']->hasCustomPhoto())
                                <img src="{{ $row['candidate']->photo_url }}" alt=""
                                     class="w-16 h-16 rounded-2xl object-cover border-2 {{ $isTop ? 'border-amber-400' : 'border-slate-600' }} shadow bg-slate-900 flex-shrink-0">
                            @else
                                <div class="flex items-center -space-x-3 flex-shrink-0">
                                    <img src="{{ $row['candidate']->photo_url }}" alt="" title="Ketua: {{ $row['candidate']->chairman_name }}"
                                         class="w-14 h-14 rounded-2xl object-cover border-2 {{ $isTop ? 'border-amber-400' : 'border-indigo-500' }} shadow bg-slate-900">
                                    <img src="{{ $row['candidate']->vice_photo_url }}" alt="" title="Wakil: {{ $row['candidate']->vice_name }}"
                                         class="w-14 h-14 rounded-2xl object-cover border-2 {{ $isTop ? 'border-amber-400' : 'border-emerald-500' }} shadow bg-slate-900">
                                </div>
                            @endif
                        </div>

                        {{-- Data Nama & Progress Bar --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-white text-base sm:text-lg">
                                    {{ $row['candidate']->chairman_name }} &amp; {{ $row['candidate']->vice_name }}
                                </span>
                                @if($isTop && !$tally['is_tie'])
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-400 text-slate-950 shadow-sm">
                                        Terpilih
                                    </span>
                                @elseif($isTop && $tally['is_tie'])
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-400 text-slate-950 shadow-sm">
                                        Suara Tertinggi (Seri)
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400 mt-1">
                                Kelas: Ketua ({{ $row['candidate']->chairman_class ?? '-' }}) &bull; Wakil ({{ $row['candidate']->vice_class ?? '-' }})
                            </div>

                            {{-- Progress Bar --}}
                            <div class="mt-3 w-full h-3.5 bg-slate-900 rounded-full overflow-hidden p-0.5 border border-slate-700/60">
                                <div class="h-full {{ $isTop ? 'bg-amber-400 shadow-md shadow-amber-400/50' : 'bg-indigo-600' }} rounded-full transition-all duration-700"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        {{-- Total Suara & Persen --}}
                        <div class="text-right sm:text-right flex sm:flex-col justify-between items-center sm:items-end flex-shrink-0 pt-2 sm:pt-0">
                            <div class="text-2xl sm:text-3xl font-black {{ $isTop ? 'text-amber-400' : 'text-white' }}">
                                {{ $row['votes'] }}
                                <span class="text-xs font-semibold text-slate-400 uppercase">Suara</span>
                            </div>
                            <div class="text-sm font-bold text-slate-300">
                                {{ $pct }}%
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach

            {{-- Suara Kosong (hanya jika ada data suara kosong tercatat) --}}
            @if($tally['blank_count'] > 0)
                @php
                    $blankPct = $tally['total_votes'] > 0 ? round(($tally['blank_count'] / $tally['total_votes']) * 100, 1) : 0;
                @endphp
                <div class="p-6 bg-slate-900/40">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                        <div class="flex items-center gap-4 flex-shrink-0">
                            <div class="w-12 h-12 rounded-2xl bg-slate-700 text-slate-300 font-bold flex items-center justify-center text-lg shadow">
                                &Oslash;
                            </div>
                            <div class="sm:hidden font-bold text-slate-300">Suara Kosong</div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-200 text-base">
                                Suara Kosong / Kotak Kosong
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5">Pemilih memilih opsi tanpa memilih pasangan calon manapun</div>
                            <div class="mt-3 w-full h-3.5 bg-slate-900 rounded-full overflow-hidden p-0.5 border border-slate-700/60">
                                <div class="h-full bg-slate-500 rounded-full transition-all duration-700"
                                     style="width: {{ $blankPct }}%"></div>
                            </div>
                        </div>

                        <div class="text-right sm:text-right flex sm:flex-col justify-between items-center sm:items-end flex-shrink-0 pt-2 sm:pt-0">
                            <div class="text-2xl sm:text-3xl font-black text-slate-300">
                                {{ $tally['blank_count'] }}
                                <span class="text-xs font-semibold text-slate-400 uppercase">Suara</span>
                            </div>
                            <div class="text-sm font-bold text-slate-400">{{ $blankPct }}%</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Ringkasan Partisipasi Pemilih --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-5 text-center shadow-lg">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total DPT (Hak Suara)</span>
            <div class="text-3xl font-black text-white mt-1">{{ $participation['total'] }}</div>
            <p class="text-xs text-slate-400 mt-1">Pemilih Terdaftar</p>
        </div>

        <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-5 text-center shadow-lg">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Suara Masuk</span>
            <div class="text-3xl font-black text-indigo-400 mt-1">{{ $participation['voted'] }}</div>
            <p class="text-xs text-slate-400 mt-1">Pemilih Telah Memilih</p>
        </div>

        <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-5 text-center shadow-lg">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tingkat Partisipasi</span>
            <div class="text-3xl font-black text-amber-400 mt-1">{{ $participation['percentage'] }}%</div>
            <p class="text-xs text-slate-400 mt-1">Dari Keseluruhan DPT</p>
        </div>
    </div>

    {{-- Footer Info Notice --}}
    <div class="text-center text-xs text-slate-500 py-4">
        <p>Data ini merupakan hasil rekapitulasi resmi dari sistem Bilik Suara Digital Pilketos.</p>
        <p class="mt-1 text-slate-600">Halaman ini bersifat statis dan tidak melakukan auto-refresh untuk stabilitas server.</p>
    </div>

</div>
@endsection
