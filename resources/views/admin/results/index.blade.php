@extends('layouts.app')

@section('title', 'Hasil Ujian - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Hasil Ujian')

@section('content')
    <div class="space-y-8 animate-fadeIn pb-12">
        <!-- Breadcrumbs & Header -->
        <div class="flex flex-col gap-2">
            <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
                <a href="{{ route('dashboard.superadmin') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-indigo-600">Hasil Ujian</span>
            </nav>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Laporan Hasil Ujian</h2>
            <p class="text-sm text-gray-500 mt-1">Pantau statistik dan detail nilai siswa untuk setiap pelaksanaan ujian.</p>
        </div>

        <!-- Statistics Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 flex flex-col justify-between">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 mb-6">
                    <i class="fas fa-clipboard-check text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Pelaksanaan</p>
                    <p class="text-3xl font-black text-gray-900">{{ $exams->count() }}</p>
                </div>
            </div>
            
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 flex flex-col justify-between">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 mb-6">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Partisipan</p>
                    <p class="text-3xl font-black text-gray-900">
                        {{ $exams->sum(fn($e) => $e->stats['total_participants']) }}
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 flex flex-col justify-between">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 mb-6">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Rata-rata Nilai</p>
                    <p class="text-3xl font-black text-gray-900">
                        @php
                            $totalParticipants = $exams->sum(fn($e) => $e->stats['total_participants']);
                            $avgScore = $totalParticipants > 0 
                                ? $exams->sum(fn($e) => $e->stats['average_score'] * $e->stats['total_participants']) / $totalParticipants
                                : 0;
                        @endphp
                        {{ round($avgScore, 2) }}
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 flex flex-col justify-between">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 mb-6">
                    <i class="fas fa-graduation-cap text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Tingkat Kelulusan</p>
                    <p class="text-3xl font-black text-gray-900">
                        @php
                            $passRate = $totalParticipants > 0 
                                ? $exams->sum(fn($e) => ($e->stats['pass_rate'] ?? 0) * $e->stats['total_participants']) / $totalParticipants
                                : 0;
                        @endphp
                        {{ round($passRate, 1) }}%
                    </p>
                </div>
            </div>
        </div>

        <!-- Exams Table -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                        <i class="fas fa-file-invoice text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Daftar Hasil Ujian</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Detail Per Pelaksanaan</p>
                    </div>
                </div>
            </div>

            @if($exams->isEmpty())
                <div class="p-20 text-center space-y-4">
                    <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center text-gray-200 mx-auto">
                        <i class="fas fa-inbox text-4xl"></i>
                    </div>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest">Belum ada data hasil ujian.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Ujian</th>
                                <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Mata Pelajaran</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Partisipan</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Rata-rata</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Nilai Tertinggi</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($exams as $exam)
                                <tr class="group hover:bg-indigo-50/30 transition-colors">
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $exam->title }}</span>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">{{ $exam->created_at->translatedFormat('d F Y') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <span class="px-4 py-1.5 bg-gray-100 text-gray-600 rounded-xl text-[10px] font-black uppercase tracking-widest">
                                            {{ $exam->subject->name }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-black">
                                            <i class="fas fa-user-check text-[10px]"></i>
                                            {{ $exam->stats['total_participants'] }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="text-sm font-black text-gray-900">{{ round($exam->stats['average_score'], 1) }}</span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="text-sm font-black text-emerald-600">{{ round($exam->stats['highest_score'], 1) }}</span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <a href="{{ route('admin.results.show', $exam->id) }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white text-indigo-600 border-2 border-indigo-100 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all shadow-sm">
                                            <i class="fas fa-chart-pie"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <style>
        .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
@endsection
