@extends('layouts.app')

@section('title', 'Catat Pertemuan Baru: ' . $extracurricular->name)

@section('content')
<div class="space-y-8" x-data="{ 
    allHadir() {
        document.querySelectorAll('.attendance-radio-hadir').forEach(radio => radio.checked = true);
    }
}">
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
                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Baru</span>
                </nav>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight uppercase flex items-center gap-3">
                    Catat Pertemuan
                </h1>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.extracurriculars.sessions.store', $extracurricular) }}" method="POST" class="space-y-8">
        @csrf
        <input type="hidden" name="academic_year" value="{{ $academicYear }}">
        <input type="hidden" name="semester" value="{{ $semester }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- Bagian Kiri: Info Pertemuan --}}
            <div class="lg:col-span-1 space-y-8">
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-1">Informasi</h3>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Detail pelaksanaan kegiatan</p>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Tanggal</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                                   class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Materi / Topik</label>
                            <input type="text" name="topic" placeholder="Misal: Latihan Dasar Tendangan" required
                                   class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Catatan Jurnal (Opsional)</label>
                            <textarea name="notes" rows="4" placeholder="Tuliskan catatan tambahan mengenai pertemuan ini..."
                                      class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-500 transition-all resize-none"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Presensi Pembina --}}
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-1">Presensi Pembina</h3>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest italic text-amber-600">Catat kehadiran pelatih/pembina</p>
                    </div>

                    <div class="space-y-3">
                        @foreach($coaches as $coach)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-transparent hover:border-indigo-100 transition-all">
                                <div class="min-w-0 pr-4">
                                    <div class="text-[11px] font-bold text-gray-900 truncate">{{ $coach->teacher->name }}</div>
                                    <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Pembina</div>
                                </div>
                                <div class="flex items-center gap-1 bg-white p-1 rounded-xl shadow-sm">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="coach_attendances[{{ $coach->teacher_id }}]" value="hadir" checked class="hidden peer">
                                        <div class="px-3 py-1.5 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all peer-checked:bg-emerald-500 peer-checked:text-white text-gray-400">Hadir</div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="coach_attendances[{{ $coach->teacher_id }}]" value="tidak_hadir" class="hidden peer">
                                        <div class="px-3 py-1.5 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all peer-checked:bg-rose-500 peer-checked:text-white text-gray-400">Absen</div>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Bagian Kanan: Presensi Siswa --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden h-full">
                    <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-1">Presensi Anggota</h3>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Daftar hadir seluruh anggota aktif</p>
                        </div>
                        <button type="button" @click="allHadir()" 
                                class="px-4 py-2 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-100 transition-all">
                            Set Semua Hadir
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 border-b border-gray-50">
                                <tr>
                                    <th class="px-8 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest">Siswa</th>
                                    <th class="px-8 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Status Kehadiran</th>
                                    <th class="px-8 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest">Catatan (Jika Ada)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($members as $member)
                                    <tr class="group hover:bg-gray-50/50 transition-colors">
                                        <td class="px-8 py-6">
                                            <div class="text-xs font-black text-gray-900 tracking-tight">{{ $member->student->name }}</div>
                                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">
                                                {{ $member->student->nis ?? '-' }} • {{ $member->student->classroom->name ?? 'Tanpa Kelas' }}
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="flex items-center justify-center gap-1.5 bg-gray-50 p-1.5 rounded-[1.25rem] w-fit mx-auto shadow-inner">
                                                {{-- Hadir --}}
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="attendances[{{ $member->student_id }}]" value="hadir" checked class="hidden peer attendance-radio-hadir">
                                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl text-[10px] font-black uppercase tracking-widest transition-all peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-emerald-200 text-gray-300 bg-white" title="Hadir">H</div>
                                                </label>
                                                {{-- Izin --}}
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="attendances[{{ $member->student_id }}]" value="izin" class="hidden peer">
                                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl text-[10px] font-black uppercase tracking-widest transition-all peer-checked:bg-blue-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-blue-200 text-gray-300 bg-white" title="Izin">I</div>
                                                </label>
                                                {{-- Sakit --}}
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="attendances[{{ $member->student_id }}]" value="sakit" class="hidden peer">
                                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl text-[10px] font-black uppercase tracking-widest transition-all peer-checked:bg-amber-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-amber-200 text-gray-300 bg-white" title="Sakit">S</div>
                                                </label>
                                                {{-- Alfa --}}
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="attendances[{{ $member->student_id }}]" value="alfa" class="hidden peer">
                                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl text-[10px] font-black uppercase tracking-widest transition-all peer-checked:bg-rose-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-rose-200 text-gray-300 bg-white" title="Alfa">A</div>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <input type="text" name="attendance_notes[{{ $member->student_id }}]" 
                                                   placeholder="Misal: Pulang kampung"
                                                   class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-[10px] font-bold focus:ring-2 focus:ring-emerald-500 transition-all">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-8 py-20 text-center">
                                            <p class="text-[9px] font-black text-gray-300 uppercase tracking-widest">Belum ada anggota siswa terdaftar</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-8 bg-gray-50/50 border-t border-gray-50 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.extracurriculars.sessions.index', $extracurricular) }}?academic_year={{ $academicYear }}&semester={{ $semester }}" 
                           class="px-8 py-4 bg-white text-gray-500 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-gray-100 transition-all border border-gray-200">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-10 py-4 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-600/20">
                            Simpan Pertemuan
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
