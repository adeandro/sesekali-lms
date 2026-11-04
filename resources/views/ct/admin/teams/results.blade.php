@extends('layouts.app')

@section('title', 'Hasil CT — Kelas ' . $ctClass->name)

@section('page-title', 'Hasil — Kelas ' . $ctClass->name)

@section('content')
<div class="max-w-5xl mx-auto space-y-6 animate-fadeIn pb-12">

    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ct.teams', $ctClass) }}" class="text-gray-400 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">Hasil — {{ $ctClass->name }}</h2>
        </div>
        <a href="{{ route('admin.ct.export-csv', $ctClass) }}"
           class="px-4 py-2 bg-green-600 text-white text-xs font-bold rounded-xl hover:bg-green-700 transition">
            <i class="fas fa-download mr-1"></i>Export CSV
        </a>
        <a href="{{ route('ct.leaderboard', $ctClass) }}" target="_blank"
           class="px-4 py-2 bg-slate-600 text-white text-xs font-bold rounded-xl hover:bg-slate-700 transition">
            <i class="fas fa-trophy mr-1"></i>Papan Skor Publik
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    {{-- Summary cards --}}
    @php
        $totalTeams   = $teams->count();
        $openedTeams  = $teams->whereNotNull('opened_at')->count();
        $totalAttempts = $teams->sum(fn($t) => $t->attempts->count());
    @endphp
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
            <p class="text-3xl font-black text-gray-900">{{ $totalTeams }}</p>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Total Tim</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
            <p class="text-3xl font-black text-green-600">{{ $openedTeams }}</p>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Brankas Terbuka</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
            <p class="text-3xl font-black text-indigo-600">{{ $totalAttempts }}</p>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Total Percobaan</p>
        </div>
    </div>

    {{-- Team results --}}
    @foreach($teams as $team)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between gap-4 px-5 py-3 border-b border-gray-50">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl
                        {{ $team->isOpened() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}
                        font-black text-sm">
                        {{ $team->number }}
                    </span>
                    <div>
                        <p class="font-bold text-gray-900 text-sm">Tim {{ $team->number }}
                            @if($team->certificate_no)
                                <span class="ml-2 font-mono text-xs text-indigo-500">{{ $team->certificate_no }}</span>
                            @endif
                        </p>
                        @if($team->member_names)
                            <p class="text-xs text-gray-400">{{ implode(' · ', $team->member_names) }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($team->isOpened())
                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                            <i class="fas fa-unlock mr-1"></i>Terbuka {{ $team->opened_at?->format('d/m H:i') }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-xs font-bold rounded-full">
                            Belum terbuka
                        </span>
                    @endif
                    <span class="text-xs text-gray-400">{{ $team->attempts->count() }} percobaan</span>
                </div>
            </div>

            @if($team->attempts->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50 text-gray-400 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-2 text-left">#</th>
                                <th class="px-4 py-2 text-left">Waktu</th>
                                <th class="px-4 py-2 text-left">Kode Dikirim</th>
                                <th class="px-4 py-2 text-left">Hasil</th>
                                <th class="px-4 py-2 text-left">IP</th>
                                <th class="px-4 py-2 text-left">Anggota (saat percobaan)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($team->attempts as $i => $attempt)
                                <tr class="{{ $attempt->is_correct ? 'bg-green-50' : '' }}">
                                    <td class="px-4 py-2 text-gray-400">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2 font-mono text-gray-500">{{ $attempt->created_at->format('d/m H:i:s') }}</td>
                                    <td class="px-4 py-2 font-mono font-bold tracking-widest text-gray-700">{{ $attempt->submitted_code }}</td>
                                    <td class="px-4 py-2">
                                        @if($attempt->is_correct)
                                            <span class="text-green-600 font-bold"><i class="fas fa-check"></i> Benar</span>
                                        @else
                                            <span class="text-red-500 font-bold"><i class="fas fa-times"></i> Salah</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-gray-400">{{ $attempt->ip ?? '—' }}</td>
                                    <td class="px-4 py-2 text-gray-500">
                                        {{ $attempt->member_names ? implode(', ', $attempt->member_names) : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="px-5 py-4 text-xs text-gray-300 italic">Belum ada percobaan.</p>
            @endif
        </div>
    @endforeach

</div>
@endsection
