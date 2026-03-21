@extends('layouts.app')

@section('title', 'Input Nilai Ekskul: ' . $extracurricular->name)

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <a href="{{ route('admin.extracurriculars.my-assignments') }}?academic_year={{ $academicYear }}" 
               class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:text-emerald-500 hover:bg-emerald-50 transition-all shadow-sm">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <nav class="flex items-center gap-2 mb-1">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Ekskul Saya</span>
                    <i class="fas fa-chevron-right text-[8px] text-gray-300"></i>
                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">{{ $extracurricular->name }}</span>
                </nav>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight uppercase flex items-center gap-3">
                    Input Nilai Siswa
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-4 bg-gray-50 p-2 rounded-2xl border border-gray-100">
            <div class="px-4 py-2 bg-white rounded-xl shadow-sm">
                <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-0.5 text-center">Semester</div>
                <div class="text-xs font-black text-emerald-600 text-center uppercase">{{ $semester == 1 ? '1 (Ganjil)' : '2 (Genap)' }}</div>
            </div>
            <div class="px-4 py-2 bg-white rounded-xl shadow-sm min-w-24">
                <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-0.5 text-center">Tahun Ajaran</div>
                <div class="text-xs font-black text-gray-900 text-center">{{ $academicYear }}</div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex flex-wrap items-center justify-between gap-6">
        <form action="{{ route('admin.extracurriculars.grades', $extracurricular) }}" method="GET" class="flex flex-wrap items-center gap-4">
            <input type="hidden" name="academic_year" value="{{ $academicYear }}">
            
            <div class="flex items-center gap-2">
                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest mr-2">Ganti Semester:</span>
                <button type="submit" name="semester" value="1" 
                        class="px-5 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all {{ $semester == 1 ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-gray-50 text-gray-400 hover:bg-gray-100 border border-transparent' }}">
                    Ganjil
                </button>
                <button type="submit" name="semester" value="2" 
                        class="px-5 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all {{ $semester == 2 ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-gray-50 text-gray-400 hover:bg-gray-100 border border-transparent' }}">
                    Genap
                </button>
            </div>
        </form>

        <div class="flex items-center gap-2 text-gray-400">
            <i class="fas fa-info-circle text-xs"></i>
            <span class="text-[9px] font-black uppercase tracking-widest">Pilih nilai dan simpan secara berkala.</span>
        </div>
    </div>

    {{-- Grade Table --}}
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden group hover:shadow-xl hover:shadow-emerald-500/5 transition-all duration-500">
        @if($members->isEmpty())
            <div class="p-20 text-center opacity-40">
                <i class="fas fa-users-slash text-4xl text-gray-300 mb-4 block"></i>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Belum ada siswa yang terdaftar sebagai anggota ekskul ini.</p>
            </div>
        @else
            <form action="{{ route('admin.extracurriculars.grades.save', $extracurricular) }}" method="POST">
                @csrf
                <input type="hidden" name="academic_year" value="{{ $academicYear }}">
                <input type="hidden" name="semester" value="{{ $semester }}">

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-50">
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest w-12 text-center">No</th>
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Data Siswa</th>
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Nilai</th>
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Catatan / Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50/50">
                            @foreach($members as $index => $member)
                                @php 
                                    $student = $member->student;
                                    $existingGrade = $existingGrades[$student->id] ?? null;
                                @endphp
                                <tr class="group/row hover:bg-gray-50/30 transition-colors">
                                    <td class="px-8 py-6 text-center text-xs font-black text-gray-300 font-mono">{{ $index + 1 }}</td>
                                    <td class="px-8 py-6">
                                        <div class="text-xs font-black text-gray-900 tracking-tight uppercase">{{ $student->name }}</div>
                                        <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">
                                            NIS: {{ $student->nis ?? '-' }} • {{ $student->classroom->name ?? 'Tanpa Kelas' }}
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="relative max-w-[180px] mx-auto">
                                            <select name="grades[{{ $student->id }}]" 
                                                    class="w-full bg-gray-50 border-none rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest focus:ring-4 focus:ring-emerald-500/10 transition-all appearance-none cursor-pointer pr-10">
                                                <option value="">-- Pilih --</option>
                                                <option value="Sangat Baik" {{ ($existingGrade->grade ?? '') == 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik</option>
                                                <option value="Baik" {{ ($existingGrade->grade ?? '') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                                <option value="Cukup" {{ ($existingGrade->grade ?? '') == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                                <option value="Kurang" {{ ($existingGrade->grade ?? '') == 'Kurang' ? 'selected' : '' }}>Kurang</option>
                                            </select>
                                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 text-[10px] pointer-events-none group-hover/row:text-emerald-500 transition-colors"></i>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <input type="text" name="notes[{{ $student->id }}]" 
                                               value="{{ $existingGrade->note ?? '' }}"
                                               placeholder="Aktif berpartisipasi dalam setiap kegiatan..."
                                               class="w-full bg-gray-50 border-none rounded-xl px-5 py-2.5 text-[10px] font-bold text-gray-700 placeholder:text-gray-300 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all shadow-sm">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-8 bg-gray-50/50 border-t border-gray-100 flex items-center justify-end gap-4">
                    <a href="{{ route('admin.extracurriculars.my-assignments') }}?academic_year={{ $academicYear }}" class="px-8 py-4 rounded-2xl bg-white text-gray-500 text-[10px] font-black uppercase tracking-widest hover:bg-gray-100 transition-all border border-gray-200">
                        Batal
                    </a>
                    <button type="submit" class="px-12 py-4 rounded-2xl bg-emerald-600 text-white text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-emerald-500/20 hover:bg-emerald-700 hover:scale-[1.02] transition-all will-change-transform">
                        Simpan Semua Nilai
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
