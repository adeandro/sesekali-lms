@extends('layouts.app')

@section('title', 'CT Escape Room - Admin')

@section('page-title', 'CT Escape Room — Kelola Kelas')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 animate-fadeIn pb-12">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                <i class="fas fa-terminal text-xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight uppercase">CT Escape Room</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kode Merah — Kelola Kelas &amp; Kelompok</p>
            </div>
        </div>
        <a href="{{ route('admin.ct.classes.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl shadow hover:bg-indigo-700 transition">
            <i class="fas fa-plus"></i> Tambah Kelas
        </a>
    </div>

    {{-- Flash messages --}}
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

    {{-- Class cards --}}
    @forelse($classes as $class)
        @php
            $isOpen   = $class->isOpen();
            $pct      = $class->teams_count > 0 ? round(($class->opened_count / $class->teams_count) * 100) : 0;
        @endphp
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-gray-50">
                {{-- Class info --}}
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl
                        {{ $class->is_active ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400' }}
                        font-black text-sm">
                        {{ $class->name }}
                    </span>
                    <div>
                        <p class="font-bold text-gray-900">Kelas {{ $class->name }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $class->teams_count }} tim
                            &middot;
                            @if($class->opens_at && $class->closes_at)
                                {{ $class->opens_at->format('d/m H:i') }} – {{ $class->closes_at->format('d/m H:i') }}
                            @else
                                Jam tidak dibatasi
                            @endif
                            &middot;
                            <span class="{{ $isOpen ? 'text-green-600' : 'text-red-500' }} font-semibold">
                                {{ $isOpen ? 'Buka' : 'Tutup' }}
                            </span>
                        </p>
                    </div>
                </div>
                {{-- Progress --}}
                <div class="hidden md:flex items-center gap-3 min-w-[140px]">
                    <div class="flex-1 bg-gray-100 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full transition-all" style="width:{{ $pct }}%"></div>
                    </div>
                    <span class="text-xs font-bold text-gray-500">{{ $class->opened_count }}/{{ $class->teams_count }}</span>
                </div>
                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-wrap justify-end">
                    <a href="{{ route('admin.ct.teams', $class) }}"
                       class="px-3 py-1.5 text-xs font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                        <i class="fas fa-users mr-1"></i>Tim
                    </a>
                    <a href="{{ route('admin.ct.answer-key', $class) }}"
                       class="px-3 py-1.5 text-xs font-bold bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-lg transition">
                        <i class="fas fa-key mr-1"></i>Kunci
                    </a>
                    <a href="{{ route('admin.ct.results', $class) }}"
                       class="px-3 py-1.5 text-xs font-bold bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition">
                        <i class="fas fa-chart-bar mr-1"></i>Hasil
                    </a>
                    <a href="{{ route('admin.ct.worksheets.all', $class) }}"
                       class="px-3 py-1.5 text-xs font-bold bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-lg transition">
                        <i class="fas fa-file-pdf mr-1"></i>PDF Semua
                    </a>
                    <a href="{{ route('admin.ct.export-csv', $class) }}"
                       class="px-3 py-1.5 text-xs font-bold bg-green-100 hover:bg-green-200 text-green-700 rounded-lg transition">
                        <i class="fas fa-download mr-1"></i>CSV
                    </a>
                    <a href="{{ route('admin.ct.classes.edit', $class) }}"
                       class="px-3 py-1.5 text-xs font-bold bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded-lg transition">
                        <i class="fas fa-pencil-alt mr-1"></i>Edit
                    </a>
                    <form method="POST" action="{{ route('admin.ct.classes.destroy', $class) }}"
                          onsubmit="return confirm('Hapus kelas {{ $class->name }} beserta semua data tim dan percobaan?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-3 py-1.5 text-xs font-bold bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition">
                            <i class="fas fa-trash mr-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>

            {{-- Generate form --}}
            <form method="POST" action="{{ route('admin.ct.classes.generate', $class) }}"
                  class="flex flex-wrap items-center gap-3 px-6 py-3 bg-gray-50">
                @csrf
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Generate Tim:</span>
                <input type="number" name="count" value="{{ $class->teams_count ?: 9 }}" min="1" max="30"
                       class="w-16 text-sm border border-gray-200 rounded-lg px-2 py-1 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                <label class="flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer">
                    <input type="checkbox" name="force" value="1" class="rounded">
                    Timpa data lama
                </label>
                <button type="submit"
                        class="px-4 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-cog mr-1"></i>Generate
                </button>
            </form>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-dashed border-gray-200 p-12 text-center">
            <div class="text-gray-300 text-5xl mb-4"><i class="fas fa-box-open"></i></div>
            <p class="text-gray-400 font-medium">Belum ada kelas. Tambah kelas terlebih dahulu.</p>
            <a href="{{ route('admin.ct.classes.create') }}"
               class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition">
                <i class="fas fa-plus"></i> Tambah Kelas
            </a>
        </div>
    @endforelse

</div>
@endsection
