@extends('layouts.app')

@section('title', 'Jurnal & Presensi: ' . $extracurricular->name)

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <a href="{{ route('admin.extracurriculars.show', $extracurricular) }}?academic_year={{ $academicYear }}" 
               class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:text-emerald-500 hover:bg-emerald-50 transition-all">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <nav class="flex items-center gap-2 mb-1">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Ekstrakurikuler</span>
                    <i class="fas fa-chevron-right text-[8px] text-gray-300"></i>
                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">{{ $extracurricular->name }}</span>
                </nav>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight uppercase flex items-center gap-3">
                    Jurnal & Presensi
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-600 text-[9px] font-black rounded-lg tracking-widest">
                        Sem {{ $semester }} • {{ $academicYear }}
                    </span>
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.extracurriculars.sessions.recap', [$extracurricular, 'academic_year' => $academicYear, 'semester' => $semester]) }}" 
               class="flex items-center gap-2 px-5 py-3 bg-amber-50 text-amber-600 text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-amber-100 transition-all">
                <i class="fas fa-chart-line"></i> Rekap Kehadiran
            </a>
            <a href="{{ route('admin.extracurriculars.sessions.create', [$extracurricular, 'academic_year' => $academicYear, 'semester' => $semester]) }}" 
               class="flex items-center gap-2 px-5 py-3 bg-emerald-600 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20">
                <i class="fas fa-plus"></i> Catat Pertemuan
            </a>
        </div>
    </div>

    {{-- Stats Ringkas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-lg">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Total Pertemuan</div>
                <div class="text-xl font-black text-gray-900">{{ $sessions->count() }} Kali</div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-lg">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Total Anggota</div>
                <div class="text-xl font-black text-gray-900">{{ $extracurricular->members()->where('academic_year', $academicYear)->count() }} Siswa</div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-lg">
                <i class="fas fa-user-tie"></i>
            </div>
            <div>
                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Guru Pembina</div>
                <div class="text-xl font-black text-gray-900">{{ $coaches->count() }} Orang</div>
            </div>
        </div>
    </div>

    {{-- Filter & List --}}
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Daftar Pertemuan</h3>
            
            <form action="{{ route('admin.extracurriculars.sessions.index', $extracurricular) }}" method="GET" class="flex items-center gap-3">
                <select name="semester" onchange="this.form.submit()" 
                        class="px-4 py-2 bg-gray-50 border-none rounded-xl text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-emerald-500">
                    <option value="1" {{ $semester == 1 ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                    <option value="2" {{ $semester == 2 ? 'selected' : '' }}>Semester 2 (Genap)</option>
                </select>
                <input type="text" name="academic_year" value="{{ $academicYear }}" 
                       placeholder="T.A (Misal: 2024/2025)"
                       @keydown.enter="this.form.submit()"
                       class="px-4 py-2 bg-gray-50 border-none rounded-xl text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-emerald-500 w-36 text-center">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-50">
                    <tr>
                        <th class="px-8 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                        <th class="px-8 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest">Materi / Topik</th>
                        <th class="px-8 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Hadir</th>
                        <th class="px-8 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest">Catatan</th>
                        <th class="px-8 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($sessions as $session)
                        <tr class="group hover:bg-gray-50/50 transition-colors">
                            <td class="px-8 py-6">
                                <div class="text-xs font-black text-gray-900">{{ $session->date->translatedFormat('d F Y') }}</div>
                                <div class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter mt-1">Oleh: {{ $session->creator->name }}</div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="text-xs font-bold text-gray-800 tracking-tight">{{ $session->topic }}</div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black rounded-lg">
                                    {{ $session->student_attendances_count }} Siswa
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-[10px] text-gray-500 leading-relaxed max-w-xs truncate">
                                    {{ $session->notes ?? '-' }}
                                </p>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.extracurriculars.sessions.show', [$extracurricular, $session]) }}" 
                                       class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-emerald-50 hover:text-emerald-600 transition-all"
                                       title="Lihat Detail">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.extracurriculars.sessions.destroy', [$extracurricular, $session]) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Hapus data pertemuan ini? Seluruh data presensi akan ikut terhapus.')"
                                          class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" 
                                                class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition-all"
                                                title="Hapus">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="w-20 h-20 bg-gray-50 text-gray-200 rounded-[2rem] flex items-center justify-center text-3xl mx-auto mb-6">
                                    <i class="fas fa-calendar-times"></i>
                                </div>
                                <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-1">Belum Ada Pertemuan</h4>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Mulai catat pertemuan perdana untuk semester ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
