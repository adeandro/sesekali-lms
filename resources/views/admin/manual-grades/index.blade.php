@extends('layouts.app')

@section('title', 'Ringkasan Nilai Manual - ' . ($configs['school_name'] ?? 'SesekaliCBT'))
@section('page-title', 'Nilai Manual')

@section('content')
<div class="space-y-8 animate-fadeIn">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                <i class="fas fa-clipboard-list text-xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Nilai Manual</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ringkasan nilai yang sudah diinput per mapel per periode</p>
            </div>
        </div>
        <a href="{{ route('admin.manual-grades.input') }}"
           class="inline-flex items-center gap-2 h-12 px-6 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
            <i class="fas fa-edit"></i> Input Nilai Baru
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <form method="GET" action="{{ route('admin.manual-grades.index') }}" class="p-6 flex flex-wrap items-end gap-4">
            <div class="space-y-2 flex-1 min-w-[140px]">
                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Mata Pelajaran</label>
                <div class="relative">
                    <select name="subject_id" class="w-full h-10 bg-gray-50 border-transparent rounded-xl px-3 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 appearance-none">
                        <option value="">Semua Mapel</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-chevron-down text-xs"></i></div>
                </div>
            </div>
            <div class="space-y-2 min-w-[120px]">
                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Semester</label>
                <div class="relative">
                    <select name="semester" class="w-full h-10 bg-gray-50 border-transparent rounded-xl px-3 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 appearance-none">
                        <option value="">Semua</option>
                        <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>Semester 1</option>
                        <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>Semester 2</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-chevron-down text-xs"></i></div>
                </div>
            </div>
            <div class="space-y-2 min-w-[130px]">
                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Tahun Ajaran</label>
                <input type="text" name="academic_year" value="{{ request('academic_year') }}" placeholder="2024/2025"
                    class="w-full h-10 bg-gray-50 border-transparent rounded-xl px-3 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 placeholder:text-gray-300">
            </div>
            <button type="submit" class="h-10 px-5 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('admin.manual-grades.index') }}" class="h-10 px-5 bg-gray-100 text-gray-500 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-gray-200 transition flex items-center">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                <i class="fas fa-list text-sm"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Daftar Nilai Manual</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $summaries->total() }} konfigurasi nilai</p>
            </div>
        </div>

        @if($summaries->isEmpty())
            <div class="p-16 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center text-gray-300 mx-auto mb-4">
                    <i class="fas fa-clipboard-list text-2xl"></i>
                </div>
                <p class="text-sm font-black text-gray-400 uppercase tracking-widest">Belum ada nilai manual yang diinput</p>
                <a href="{{ route('admin.manual-grades.input') }}" class="mt-4 inline-flex items-center gap-2 text-indigo-600 text-xs font-bold hover:underline">
                    <i class="fas fa-edit"></i> Input nilai sekarang
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-50">
                            <th class="px-8 py-4 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Mata Pelajaran</th>
                            <th class="px-6 py-4 text-center text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Kelas</th>
                            <th class="px-6 py-4 text-center text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Semester</th>
                            <th class="px-6 py-4 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Tahun Ajaran</th>
                            <th class="px-6 py-4 text-center text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Jml Siswa</th>
                            @if(auth()->user()->role === 'superadmin')
                                <th class="px-6 py-4 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Guru</th>
                            @endif
                            <th class="px-8 py-4 text-right text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($summaries as $summary)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-8 py-5">
                                <span class="text-sm font-black text-gray-900">{{ $summary->subject->name ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="inline-flex px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded-full uppercase tracking-widest">
                                    Kelas {{ $summary->jenjang ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="text-sm font-bold text-gray-600">{{ $summary->semester }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-sm font-bold text-gray-600">{{ $summary->academic_year }}</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="text-sm font-black text-gray-800">{{ $summary->student_count }}</span>
                            </td>
                            @if(auth()->user()->role === 'superadmin')
                                <td class="px-6 py-5">
                                    <span class="text-xs font-bold text-gray-500">{{ $summary->teacher->name ?? '—' }}</span>
                                </td>
                            @endif
                            <td class="px-8 py-5 text-right">
                                @php
                                    // Find a class in this jenjang to pass class_id
                                    $cls = \App\Models\ClassRoom::where('grade', $summary->jenjang)
                                        ->where('academic_year', $summary->academic_year)
                                        ->first();
                                @endphp
                                <a href="{{ route('admin.manual-grades.input', [
                                    'subject_id'    => $summary->subject_id,
                                    'class_id'      => $cls?->id ?? '',
                                    'semester'      => $summary->semester,
                                    'academic_year' => $summary->academic_year,
                                ]) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-100 transition-colors">
                                    <i class="fas fa-edit text-[9px]"></i> Edit Nilai
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($summaries->hasPages())
                <div class="p-6 border-t border-gray-50">{{ $summaries->links() }}</div>
            @endif
        @endif
    </div>
</div>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
