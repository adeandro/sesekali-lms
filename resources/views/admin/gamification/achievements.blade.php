@extends('layouts.app')

@section('title', 'Achievement Manager — Gamification Center')
@section('page-title', 'Achievement Manager')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 animate-fadeIn pb-12">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-purple-500/20 will-change-transform"
                 style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
                <i class="fas fa-medal text-2xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black tracking-tight" style="color: var(--brand-text);">Achievement Manager</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kelola pencapaian atau buat tantangan baru untuk siswa</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.gamification.settings') }}"
               class="flex items-center gap-2 px-5 py-3 bg-white border border-gray-100 shadow-sm rounded-xl text-[10px] font-black uppercase tracking-widest text-gray-600 hover:text-purple-600 hover:border-purple-100 transition-all">
                <i class="fas fa-sliders-h"></i> Global Settings
            </a>
            <a href="{{ route('admin.gamification.achievements.create') }}"
               class="flex items-center gap-2 px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-white shadow-lg hover:scale-105 transition-all duration-300 will-change-transform"
               style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
                <i class="fas fa-plus"></i> Tambah Pencapaian Baru
            </a>
        </div>
    </div>

    {{-- Sub-nav tabs --}}
    <div class="flex gap-2 bg-white rounded-2xl p-1.5 shadow-sm border border-gray-100">
        <a href="{{ route('admin.gamification.settings') }}"
           class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 text-gray-500 hover:bg-gray-50">
            <i class="fas fa-sliders-h text-sm"></i> Global Settings
        </a>
        <a href="{{ route('admin.gamification.achievements') }}"
           class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 bg-purple-600 text-white shadow-lg shadow-purple-200">
            <i class="fas fa-medal text-sm"></i> Achievement Manager
        </a>
        <a href="{{ route('admin.gamification.themes') }}"
           class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 text-gray-500 hover:bg-gray-50">
            <i class="fas fa-palette text-sm"></i> Theme Manager
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-sm font-bold">
        <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Achievement Grid (The Golden Ten) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($achievements as $achievement)
            <x-achievement-card :achievement="$achievement" :is-admin-mode="true" />
        @endforeach
    </div>

    {{-- Legend --}}
    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">📖 Peta Kriteria Achievement</p>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-center">
            @foreach([
                ['exam_count','#f97316','Jumlah Ujian'],
                ['submission_hour','#1e293b','Jam Submit'],
                ['completion_time_pct','#06b6d4','% Waktu Selesai'],
                ['consecutive_pass','#6366f1','Lulus Berturut'],
                ['avg_score','#dc2626','Rata-rata Nilai'],
            ] as [$type, $color, $label])
            <div class="flex flex-col items-center gap-1.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: {{ $color }}22;">
                    <span class="text-[8px] font-black" style="color: {{ $color }};">{{ strtoupper(substr($type, 0, 3)) }}</span>
                </div>
                <p class="text-[9px] font-bold text-gray-500">{{ $label }}</p>
                <p class="text-[8px] text-gray-300 font-mono">{{ $type }}</p>
            </div>
            @endforeach
        </div>
    </div>

</div>

<style>
.animate-fadeIn { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection
