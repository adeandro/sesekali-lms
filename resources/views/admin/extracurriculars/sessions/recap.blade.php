@extends('layouts.app')

@section('title', 'Rekap Kehadiran: ' . $extracurricular->name)

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <a href="{{ route('admin.extracurriculars.sessions.index', $extracurricular) }}?academic_year={{ $academicYear }}&semester={{ $semester }}" 
               class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:text-emerald-500 hover:bg-emerald-50 transition-all">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <nav class="flex items-center gap-2 mb-1">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Jurnal & Presensi</span>
                    <i class="fas fa-chevron-right text-[8px] text-gray-300"></i>
                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Rekap Semester</span>
                </nav>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight uppercase flex items-center gap-3">
                    Rekap Kehadiran
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.extracurriculars.sessions.export.excel', [$extracurricular, 'academic_year' => $academicYear, 'semester' => $semester]) }}" 
               class="flex items-center gap-2 px-6 py-3 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-100 transition-all">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ route('admin.extracurriculars.sessions.export.pdf', [$extracurricular, 'academic_year' => $academicYear, 'semester' => $semester]) }}" 
               class="flex items-center gap-2 px-6 py-3 bg-rose-50 text-rose-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-rose-100 transition-all">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Filter & Stats --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-6">Filter Data</h3>
                <form action="{{ route('admin.extracurriculars.sessions.recap', $extracurricular) }}" method="GET" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Semester</label>
                        <select name="semester" onchange="this.form.submit()" 
                                class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-emerald-500">
                            <option value="1" {{ $semester == 1 ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                            <option value="2" {{ $semester == 2 ? 'selected' : '' }}>Semester 2 (Genap)</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Tahun Ajaran</label>
                        <input type="text" name="academic_year" value="{{ $academicYear }}" 
                               placeholder="Contoh: 2024/2025"
                               @keydown.enter="this.form.submit()"
                               class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-emerald-500 text-center">
                    </div>
                </form>
            </div>

            <div class="bg-indigo-600 p-8 rounded-[2.5rem] text-white shadow-lg shadow-indigo-600/20">
                <div class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-1">Status Rekap</div>
                <div class="text-2xl font-black mb-6">{{ $sessions->count() }} Pertemuan</div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2 border-b border-white/10">
                        <span class="text-[10px] font-black uppercase tracking-widest opacity-60">Aktif</span>
                        <span class="text-xs font-black">Semester {{ $semester }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3 space-y-8">
            {{-- Rekap Siswa --}}
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Rekap Kehadiran Siswa</h3>
                    <span class="px-3 py-1 bg-gray-50 text-[9px] font-black text-gray-400 uppercase tracking-widest rounded-lg">
                        {{ count($recap) }} Siswa
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-50">
                            <tr>
                                <th class="px-6 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest">Siswa</th>
                                <th class="px-6 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Hadir</th>
                                <th class="px-6 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Izin</th>
                                <th class="px-6 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Sakit</th>
                                <th class="px-6 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Alfa</th>
                                <th class="px-6 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest w-48">% Hadir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($recap as $row)
                                <tr class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-6">
                                        <div class="text-[11px] font-black text-gray-900 tracking-tight">{{ $row['student']->name }}</div>
                                        <div class="text-[8px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">
                                            {{ $row['student']->nis ?? '-' }} • {{ $row['student']->classroom->name ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 text-center text-xs font-black text-emerald-600 bg-emerald-50/30">
                                        {{ $row['hadir'] }}
                                    </td>
                                    <td class="px-6 py-6 text-center text-xs font-black text-blue-600">
                                        {{ $row['izin'] }}
                                    </td>
                                    <td class="px-6 py-6 text-center text-xs font-black text-amber-600">
                                        {{ $row['sakit'] }}
                                    </td>
                                    <td class="px-6 py-6 text-center text-xs font-black text-rose-600">
                                        {{ $row['alfa'] }}
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="flex items-center gap-3">
                                            @php
                                                $pct = $row['pct_hadir'];
                                                $color = match(true) {
                                                    $pct >= 80 => 'bg-emerald-500 shadow-emerald-200',
                                                    $pct >= 60 => 'bg-amber-500 shadow-amber-200',
                                                    default    => 'bg-rose-500 shadow-rose-200',
                                                };
                                                $textColor = match(true) {
                                                    $pct >= 80 => 'text-emerald-600',
                                                    $pct >= 60 => 'text-amber-600',
                                                    default    => 'text-rose-600',
                                                };
                                            @endphp
                                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                                <div class="h-full {{ $color }} rounded-full" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="text-[10px] font-black {{ $textColor }} w-8">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-8 py-20 text-center">
                                        <p class="text-[9px] font-black text-gray-300 uppercase tracking-widest">Belum ada data kehadiran untuk periode ini</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Rekap Pembina --}}
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Rekap Kehadiran Pembina</h3>
                    <span class="px-3 py-1 bg-gray-50 text-[9px] font-black text-gray-400 uppercase tracking-widest rounded-lg">
                        {{ count($coachRecap) }} Pembina
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-50">
                            <tr>
                                <th class="px-6 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest">Nama Pembina</th>
                                <th class="px-6 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Total</th>
                                <th class="px-6 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Hadir</th>
                                <th class="px-6 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Tidak Hadir</th>
                                <th class="px-6 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest w-48">% Presensi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($coachRecap as $row)
                                <tr class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-6">
                                        <div class="text-[11px] font-black text-gray-900 tracking-tight">{{ $row['name'] }}</div>
                                        <div class="text-[8px] font-bold text-emerald-500 uppercase tracking-widest mt-0.5">
                                            Pembina Ekstrakurikuler
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 text-center text-xs font-black text-gray-400">
                                        {{ $row['total'] }}
                                    </td>
                                    <td class="px-6 py-6 text-center text-xs font-black text-emerald-600 bg-emerald-50/30">
                                        {{ $row['hadir'] }}
                                    </td>
                                    <td class="px-6 py-6 text-center text-xs font-black text-rose-600">
                                        {{ $row['tidak_hadir'] }}
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="flex items-center gap-3">
                                            @php
                                                $pct = $row['pct_hadir'];
                                                $color = match(true) {
                                                    $pct >= 80 => 'bg-emerald-500 shadow-emerald-200',
                                                    $pct >= 60 => 'bg-amber-500 shadow-amber-200',
                                                    default    => 'bg-rose-500 shadow-rose-200',
                                                };
                                                $textColor = match(true) {
                                                    $pct >= 80 => 'text-emerald-600',
                                                    $pct >= 60 => 'text-amber-600',
                                                    default    => 'text-rose-600',
                                                };
                                            @endphp
                                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                                <div class="h-full {{ $color }} rounded-full" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="text-[10px] font-black {{ $textColor }} w-8">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-20 text-center">
                                        <p class="text-[9px] font-black text-gray-300 uppercase tracking-widest">Belum ada data pembina terdaftar</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
