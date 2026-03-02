@extends('layouts.app')

@section('title', 'Hasil Ujian Saya - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('content')
<div class="space-y-10 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-2">
        <div class="space-y-1">
            <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.3em] italic">Riwayat Kompetensi</p>
            <h1 class="text-3xl font-black text-gray-900 uppercase tracking-wider flex items-center gap-3">
                <span class="w-2 h-10 bg-indigo-600 rounded-full"></span>
                Hasil Ujian Saya
            </h1>
        </div>
        
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard.student') }}" class="group px-6 py-3 bg-white border border-gray-100 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-indigo-600 hover:border-indigo-100 transition-all flex items-center gap-2 shadow-sm">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-[2rem] p-8 border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-24 h-24 bg-indigo-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            <div class="relative space-y-3">
                <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-sm">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Total Ujian</p>
                    <p class="text-3xl font-black text-gray-900">{{ $results->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] p-8 border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-24 h-24 bg-emerald-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            @php
                $avgScore = $results->where('can_view_score', true)->avg('final_score');
            @endphp
            <div class="relative space-y-3">
                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-sm">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Skor Rata-rata</p>
                    <p class="text-3xl font-black text-gray-900">{{ $avgScore ? round($avgScore, 1) : '-' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] p-8 border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-24 h-24 bg-amber-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            @php
                $maxScore = $results->where('can_view_score', true)->max('final_score');
            @endphp
            <div class="relative space-y-3">
                <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-sm">
                    <i class="fas fa-trophy"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Skor Tertinggi</p>
                    <p class="text-3xl font-black text-gray-900">{{ $maxScore ? round($maxScore, 1) : '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- History List -->
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden min-h-[400px]">
        <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-[0.2em] flex items-center gap-3">
                <i class="fas fa-history text-indigo-600"></i> Riwayat Pencapaian
            </h2>
        </div>

        @if($results->isEmpty())
            <div class="py-24 flex flex-col items-center justify-center text-center space-y-4">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-gray-200 text-3xl">
                    <i class="fas fa-folder-open"></i>
                </div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Belum ada riwayat ujian</p>
                <a href="{{ route('student.exams.index') }}" class="px-6 py-3 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">Cari Ujian Tersedia</a>
            </div>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($results as $result)
                    <div class="p-8 hover:bg-indigo-50/30 transition-all duration-300 group">
                        <div class="flex flex-col lg:flex-row lg:items-center gap-8">
                            <!-- Exam Identity -->
                            <div class="flex-1 space-y-1">
                                <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] italic">{{ $result->exam->subject->name }}</p>
                                <h4 class="text-lg font-black text-gray-900 leading-tight group-hover:text-indigo-600 transition-colors">{{ $result->exam->title }}</h4>
                                <div class="flex items-center gap-2 text-gray-400 text-[11px] font-bold uppercase tracking-wider">
                                    <i class="far fa-calendar-check mt-0.5"></i>
                                    {{ $result->submitted_at->format('d M Y') }} <span class="text-gray-200 mx-1">|</span> {{ $result->submitted_at->format('H:i') }}
                                </div>
                            </div>

                            <!-- Scores & Breakdown -->
                            <div class="flex flex-wrap items-center gap-8">
                                @if($result->can_view_score)
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 rounded-[1.25rem] flex items-center justify-center text-2xl font-black shadow-sm
                                            @if($result->final_score >= 75) bg-emerald-50 text-emerald-600 @else bg-rose-50 text-rose-600 @endif group-hover:scale-105 transition-transform">
                                            {{ intval($result->final_score) }}
                                        </div>
                                        <div>
                                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Skor Akhir</p>
                                            <p class="text-[11px] font-black @if($result->final_score >= 75) text-emerald-600 @else text-rose-600 @endif uppercase tracking-wider">
                                                @if($result->final_score >= 75) Kompeten @else Perlu Belajar @endif
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="hidden md:block w-px h-8 bg-gray-100 mx-2"></div>

                                    <div class="flex items-center gap-6">
                                        <div class="space-y-1">
                                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">PG</p>
                                            <p class="text-xs font-black text-gray-700 text-center">{{ round($result->score_mc, 1) }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Esai</p>
                                            <p class="text-xs font-black text-gray-700 text-center">{{ round($result->score_essay, 1) }}</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-4 px-6 py-4 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                        <i class="fas fa-mask text-gray-300 text-xl"></i>
                                        <div class="space-y-0.5">
                                            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Skor Dirahasiakan</p>
                                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Menunggu verifikasi admin</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Action -->
                            <div class="pt-4 lg:pt-0">
                                @if($result->can_view_score)
                                    <a href="{{ route('student.exams.result', $result->id) }}" 
                                        class="w-full lg:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-white border border-gray-200 group-hover:border-indigo-600 group-hover:bg-indigo-600 text-[10px] font-black uppercase tracking-widest text-gray-700 group-hover:text-white rounded-2xl transition-all shadow-sm">
                                        Detail Review <i class="fas fa-chevron-right ml-2 text-[8px] group-hover:translate-x-1 transition-transform"></i>
                                    </a>
                                @else
                                    <div class="w-full lg:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-gray-50 text-[10px] font-black uppercase tracking-widest text-gray-400 rounded-2xl border border-gray-100 cursor-not-allowed">
                                        <i class="fas fa-lock mr-2 text-[8px]"></i> Terkunci
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
