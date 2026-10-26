@extends('layouts.app')

@section('title', 'Daftar Pemilih — ' . $period->title)

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
                <h1 class="text-xl font-bold text-slate-800 dark:text-white">📋 Daftar Pemilih</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $period->title }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pilketos.periods.voters.export', $period) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                📥 Unduh Excel DPT
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

    {{-- Ringkasan per Kelas --}}
    @if($summary->isNotEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                <h2 class="font-semibold text-slate-800 dark:text-white text-xs uppercase tracking-wider">
                    Cetak Kartu per Kelas
                </h2>
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($summary as $row)
                    <div class="p-3 border border-slate-200 dark:border-slate-700 rounded-lg flex items-center justify-between bg-slate-50 dark:bg-slate-900/40">
                        <div>
                            <div class="font-bold text-sm text-slate-800 dark:text-white">
                                @if($row->class_name === 'GURU & STAF')
                                    👨‍🏫 {{ $row->class_name }}
                                @else
                                    {{ $row->class_name ?? 'Tanpa Kelas' }}
                                @endif
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ $row->total }} {{ $row->class_name === 'GURU & STAF' ? 'orang' : 'siswa' }} &bull;
                                @if($row->printed > 0)
                                    <span class="text-green-600 font-medium">{{ $row->printed }} dicetak</span>
                                @else
                                    <span class="text-amber-500 font-medium">Belum dicetak</span>
                                @endif
                            </div>
                        </div>
                        @if($period->status !== 'closed' && ($row->class_id !== null || $row->class_name === 'GURU & STAF'))
                            <form method="POST" action="{{ route('admin.pilketos.periods.cards.class', [$period, $row->class_id ?? 0]) }}" class="no-loading">
                                @csrf
                                <button type="submit"
                                        class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-xs font-semibold rounded transition">
                                    🖨️ Cetak
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tabel Pemilih --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 border-b border-slate-100 dark:border-slate-700">
                <tr>
                    <th class="px-5 py-3 font-semibold">#</th>
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
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                        <td class="px-5 py-3 text-slate-400">{{ $voters->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-3 font-medium text-slate-800 dark:text-white">
                            {{ $voter->student_name }}
                        </td>
                        <td class="px-5 py-3 font-mono text-slate-600 dark:text-slate-400">
                            {{ $voter->nis }}
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">
                            {{ $voter->class_name ?? '-' }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($voter->token_hash)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                    Dicetak (v{{ $voter->token_version }})
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">
                                    Belum
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($voter->has_voted)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                    ✓ Sudah Memilih
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300">
                                    Belum
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
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                            Belum ada pemilih yang terdaftar pada periode ini.
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
