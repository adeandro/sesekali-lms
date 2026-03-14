@extends('layouts.app')

@section('title', 'Data Alumni - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Data Alumni')

@section('content')
<div class="space-y-6 animate-fadeIn">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
                <span class="inline-flex w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 items-center justify-center">
                    <i class="fas fa-graduation-cap"></i>
                </span>
                Daftar Alumni
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Siswa yang telah menyelesaikan masa studi (Lulus Grade 12).
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-700 ml-2">
                    {{ number_format($totalAlumni) }} alumni
                </span>
            </p>
        </div>
        <a href="{{ route('admin.students.index') }}"
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Siswa Aktif
        </a>
    </div>

    {{-- Search & Year Filter --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <form action="{{ route('admin.alumni.index') }}" method="GET" class="no-loading">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-6 lg:col-span-7">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1 ml-1">Cari Alumni</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" placeholder="Cari berdasarkan NIS atau nama..."
                               value="{{ request('search') }}"
                               class="w-full pl-11 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all text-sm">
                    </div>
                </div>
                <div class="md:col-span-4 lg:col-span-3">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1 ml-1">Tahun Lulus</label>
                    <select name="year" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all text-sm appearance-none">
                        <option value="">Semua Angkatan</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                Angkatan {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button type="submit" class="w-full px-6 py-2.5 bg-gray-900 text-white font-bold rounded-xl hover:bg-emerald-600 transition-all flex items-center justify-center gap-2 active:scale-95 shadow-sm">
                        Terapkan
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Alumni Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-emerald-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Foto</th>
                        <th class="px-6 py-4 text-left text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Identitas</th>
                        <th class="px-6 py-4 text-left text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Angkatan</th>
                        <th class="px-6 py-4 text-left text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Kelas Terakhir</th>
                        <th class="px-6 py-4 text-center text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 bg-white">
                    @forelse($alumni as $student)
                        <tr class="hover:bg-emerald-50/20 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img src="{{ $student->photo_url }}" alt="Foto"
                                     class="h-10 w-10 rounded-xl object-cover ring-2 ring-white shadow-sm">
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">{{ $student->formatted_name ?? $student->name }}</div>
                                <div class="text-xs text-gray-500 font-mono tracking-tight mt-0.5">NIS: {{ $student->nis }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($student->alumni_year)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-100 text-emerald-700">
                                        <i class="fas fa-graduation-cap mr-1.5"></i> {{ $student->alumni_year }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs italic">Tidak tercatat</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-700 font-medium">
                                    Kelas {{ $student->grade ?? '12' }}
                                    @if($student->class_group) · {{ $student->class_group }} @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-2.5 py-1 text-[10px] font-extrabold rounded-md uppercase tracking-wider bg-emerald-100 text-emerald-700">
                                    <i class="fas fa-graduation-cap mr-1"></i> Alumni
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                        <i class="fas fa-graduation-cap text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium italic">Belum ada data alumni.</p>
                                    <p class="text-gray-400 text-xs">Data alumni muncul otomatis setelah Migrasi Tahunan dijalankan untuk kelas 12.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($alumni->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $alumni->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>
@endsection
