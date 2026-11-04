@extends('layouts.app')

@section('title', 'Tim CT — Kelas ' . $ctClass->name)

@section('page-title', 'Daftar Tim — Kelas ' . $ctClass->name)

@section('content')
<div class="max-w-5xl mx-auto space-y-6 animate-fadeIn pb-12" x-data="{ fixTeamId: null, fixNames: [] }">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ct.index') }}" class="text-gray-400 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">Tim — Kelas {{ $ctClass->name }}</h2>
                <p class="text-xs text-gray-400">{{ $teams->count() }} kelompok &middot;
                    {{ $teams->whereNotNull('opened_at')->count() }} terbuka
                </p>
            </div>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.ct.answer-key', $ctClass) }}"
               class="px-3 py-1.5 text-xs font-bold bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-lg transition">
                <i class="fas fa-key mr-1"></i>Kunci Jawaban
            </a>
            <a href="{{ route('admin.ct.worksheets.all', $ctClass) }}"
               class="px-3 py-1.5 text-xs font-bold bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-lg transition">
                <i class="fas fa-file-pdf mr-1"></i>PDF Semua
            </a>
            <a href="{{ route('admin.ct.answer-key.pdf', $ctClass) }}"
               class="px-3 py-1.5 text-xs font-bold bg-orange-100 hover:bg-orange-200 text-orange-700 rounded-lg transition">
                <i class="fas fa-file-pdf mr-1"></i>Kunci PDF
            </a>
            <a href="{{ route('admin.ct.results', $ctClass) }}"
               class="px-3 py-1.5 text-xs font-bold bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition">
                <i class="fas fa-chart-bar mr-1"></i>Hasil
            </a>
            <a href="{{ route('admin.ct.export-csv', $ctClass) }}"
               class="px-3 py-1.5 text-xs font-bold bg-green-100 hover:bg-green-200 text-green-700 rounded-lg transition">
                <i class="fas fa-download mr-1"></i>CSV
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Team table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs font-black text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left w-14">Tim</th>
                    <th class="px-4 py-3 text-left">Kode (Jawaban)</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Percobaan</th>
                    <th class="px-4 py-3 text-left">Waktu Buka</th>
                    <th class="px-4 py-3 text-left">Anggota</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($teams as $team)
                    @php
                        $code = $generator->deriveCode($team->params);
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-black text-indigo-700">{{ $team->number }}</td>
                        <td class="px-4 py-3 font-mono font-bold text-gray-700 tracking-widest">{{ $code }}</td>
                        <td class="px-4 py-3">
                            @if($team->isOpened())
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                    <i class="fas fa-unlock text-[10px]"></i> Terbuka
                                </span>
                            @elseif($team->isOnCooldown())
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">
                                    <i class="fas fa-clock text-[10px]"></i> Cooldown
                                </span>
                            @elseif($team->wrong_count > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full">
                                    <i class="fas fa-times text-[10px]"></i> {{ $team->wrong_count }}× Salah
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 text-gray-500 text-xs font-bold rounded-full">
                                    <i class="fas fa-lock text-[10px]"></i> Terkunci
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $team->attempts_count }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $team->opened_at?->format('d/m H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            @if($team->member_names)
                                {{ implode(', ', $team->member_names) }}
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5 justify-end flex-wrap">
                                <a href="{{ route('admin.ct.teams.worksheet', $team) }}"
                                   class="px-2.5 py-1 text-xs font-bold bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-lg transition">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                @if($team->isOpened())
                                    <button type="button"
                                            @click="fixTeamId = {{ $team->id }}; fixNames = {{ json_encode($team->member_names ?? ['','','','']) }}"
                                            class="px-2.5 py-1 text-xs font-bold bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition">
                                        <i class="fas fa-user-edit"></i>
                                    </button>
                                @endif
                                <form method="POST" action="{{ route('admin.ct.teams.reset', $team) }}"
                                      onsubmit="return confirm('Reset Tim {{ $team->number }}? Semua data akan dihapus.')">
                                    @csrf
                                    <button type="submit"
                                            class="px-2.5 py-1 text-xs font-bold bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">
                            Belum ada tim. Generate tim dari halaman utama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Fix-names modal --}}
    <div x-show="fixTeamId !== null"
         x-cloak
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
         @keydown.escape.window="fixTeamId = null">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm space-y-4" @click.stop>
            <h3 class="font-black text-gray-900 uppercase text-sm tracking-widest">Perbaiki Nama Anggota</h3>
            <template x-for="(name, idx) in fixNames" :key="idx">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">
                        Anggota <span x-text="idx + 1"></span>
                    </label>
                    <input type="text" x-model="fixNames[idx]"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                </div>
            </template>
            <div class="flex gap-3">
                <form method="POST"
                      :action="'/admin/ct/tim/' + fixTeamId + '/perbaiki-nama'"
                      @submit.prevent="$el.submit()">
                    @csrf
                    <template x-for="(name, idx) in fixNames" :key="idx">
                        <input type="hidden" :name="'names[' + idx + ']'" :value="name">
                    </template>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition">
                        Simpan
                    </button>
                </form>
                <button type="button" @click="fixTeamId = null"
                        class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-bold rounded-xl hover:bg-gray-200 transition">
                    Batal
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
