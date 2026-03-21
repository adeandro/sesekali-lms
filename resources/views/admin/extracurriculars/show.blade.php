@extends('layouts.app')

@section('title', 'Kelola Ekskul: ' . $extracurricular->name)

@section('content')
<div class="space-y-8" x-data="{
    searchSiswa: '',
    selectedIds: [],
    students: {{ json_encode($availableStudents) }},
    get filteredStudents() {
        if (!this.searchSiswa) return this.students;
        const search = this.searchSiswa.toLowerCase();
        return this.students.filter(s => 
            s.name.toLowerCase().includes(search) || 
            (s.nis && s.nis.toLowerCase().includes(search))
        );
    },
    submitMembers(e) {
        if (this.selectedIds.length > 0) {
            e.target.submit();
        }
    }
}">
    {{-- Header --}}
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <a href="{{ route('admin.extracurriculars.index') }}?academic_year={{ $academicYear }}" 
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
                    {{ $extracurricular->name }}
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-600 text-[9px] font-black rounded-lg tracking-widest">
                        T.A {{ $academicYear }}
                    </span>
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <a href="{{ route('admin.extracurriculars.sessions.index', [
                'extracurricular' => $extracurricular->id,
                'academic_year'   => $academicYear,
            ]) }}" 
               class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white 
                      text-[9px] font-black uppercase tracking-widest rounded-xl 
                      hover:bg-emerald-700 transition shadow-lg shadow-emerald-100">
                <i class="fas fa-clipboard-list text-sm"></i> Jurnal & Presensi
            </a>

            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-hover:text-emerald-500 transition-colors">
                    <i class="fas fa-calendar-alt text-[10px]"></i>
                </div>
                <input type="text" name="academic_year" 
                       value="{{ $academicYear }}" 
                       @keydown.enter="location.href = '{{ route('admin.extracurriculars.show', $extracurricular->id) }}?academic_year=' + $el.value"
                       class="pl-10 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-emerald-500 w-36 transition-all">
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
        
        {{-- SECTION KIRI: Guru Pembina --}}
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden h-full">
            <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Guru Pembina</h3>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Penanggung jawab kegiatan</p>
                </div>
                <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chalkboard-teacher text-sm"></i>
                </div>
            </div>

            <div class="p-8 space-y-8">
                {{-- Form Tambah Coach --}}
                <form action="{{ route('admin.extracurriculars.coaches.add', $extracurricular) }}" method="POST" class="bg-gray-50 p-6 rounded-2xl space-y-4">
                    @csrf
                    <input type="hidden" name="academic_year" value="{{ $academicYear }}">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Pilih Guru</label>
                        <select name="teacher_id" required class="w-full px-4 py-3 bg-white border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500 shadow-sm">
                            <option value="">-- Pilih Guru --</option>
                            @foreach($availableTeachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full py-3 bg-indigo-600 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-600/20">
                        Tambah Pembina
                    </button>
                    @if($availableTeachers->isEmpty())
                        <p class="text-[8px] text-amber-600 font-bold uppercase tracking-widest text-center italic">Semua guru sudah terdaftar.</p>
                    @endif
                </form>

                {{-- Tabel Daftar Coach --}}
                <div class="overflow-hidden border border-gray-50 rounded-2xl">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Nama Guru</th>
                                <th class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($coaches as $coach)
                                <tr class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-bold text-gray-900 tracking-tight">{{ $coach->teacher->name }}</div>
                                        <div class="text-[9px] font-bold text-gray-400 tracking-widest uppercase">{{ $coach->teacher->niy ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form action="{{ route('admin.extracurriculars.coaches.remove', [$extracurricular, $coach]) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all">
                                                <i class="fas fa-trash-alt text-[10px]"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-12 text-center">
                                        <p class="text-[9px] font-black text-gray-300 uppercase tracking-widest">Belum ada pembina</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- SECTION KANAN: Anggota Siswa --}}
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden h-full">
            <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Anggota Siswa</h3>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Total: {{ $members->count() }} Anggota</p>
                </div>
                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-sm"></i>
                </div>
            </div>

            <div class="p-8 space-y-8">
                {{-- Form Tambah Member --}}
                <div class="bg-gray-50 p-6 rounded-2xl relative">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Daftar Siswa</label>
                            <span class="text-[8px] font-black text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full uppercase tracking-tighter" x-text="selectedIds.length + ' dipilih'"></span>
                        </div>
                        
                        {{-- Custom Searchable Multi-select with Alpine --}}
                        <div class="relative">
                            <input type="text" x-model="searchSiswa" placeholder="Cari nama siswa..." 
                                   class="w-full px-10 py-3 bg-white border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500 shadow-sm transition-all">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-[10px]"></i>
                        </div>

                        <div class="max-h-48 overflow-y-auto border border-gray-100 rounded-xl bg-white p-2 custom-scrollbar">
                            <template x-for="siswa in filteredStudents" :key="siswa.id">
                                <label class="flex items-center gap-3 p-2 rounded-lg cursor-pointer transition-all"
                                       :class="selectedIds.includes(siswa.id) ? 'bg-emerald-50' : 'hover:bg-gray-50'"
                                       @click="selectedIds.includes(siswa.id) 
                                           ? selectedIds = selectedIds.filter(i => i !== siswa.id) 
                                           : selectedIds.push(siswa.id)">
                                    <div class="relative w-4 h-4 rounded border-2 transition-all flex items-center justify-center shrink-0"
                                         :class="selectedIds.includes(siswa.id) 
                                             ? 'bg-emerald-500 border-emerald-500' 
                                             : 'border-gray-200 bg-white'">
                                        <i class="fas fa-check text-[8px] text-white" 
                                           x-show="selectedIds.includes(siswa.id)"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[11px] font-bold uppercase tracking-tight transition-colors"
                                             :class="selectedIds.includes(siswa.id) ? 'text-emerald-600' : 'text-gray-700'"
                                             x-text="siswa.name"></div>
                                        <div class="text-[8px] text-gray-400 font-medium" 
                                             x-text="siswa.nis + ' • ' + siswa.class"></div>
                                    </div>
                                </label>
                            </template>
                            <div x-show="filteredStudents.length === 0" class="p-4 text-center text-[9px] font-black text-gray-300 uppercase tracking-widest">
                                Siswa tidak ditemukan
                            </div>
                        </div>

                        <form action="{{ route('admin.extracurriculars.members.add', $extracurricular) }}" method="POST" @submit.prevent="submitMembers">
                            @csrf
                            <input type="hidden" name="academic_year" value="{{ $academicYear }}">
                            <template x-for="id in selectedIds" :key="id">
                                <input type="hidden" name="student_ids[]" :value="id">
                            </template>
                            <button type="submit" 
                                    :disabled="selectedIds.length === 0"
                                    class="w-full py-4 bg-emerald-500 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-500/20 disabled:opacity-50 disabled:grayscale disabled:cursor-not-allowed">
                                Tambah Siswa Terpilih
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Tabel Daftar Member --}}
                <div class="overflow-hidden border border-gray-50 rounded-2xl">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Siswa</th>
                                <th class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($members as $member)
                                <tr class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-bold text-gray-900 tracking-tight">{{ $member->student->name }}</div>
                                        <div class="text-[9px] font-bold text-gray-400 tracking-widest uppercase">
                                            {{ $member->student->nis ?? '-' }} • {{ $member->student->classroom->name ?? 'Tanpa Kelas' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form action="{{ route('admin.extracurriculars.members.remove', [$extracurricular, $member]) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all">
                                                <i class="fas fa-trash-alt text-[10px]"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-12 text-center">
                                        <p class="text-[9px] font-black text-gray-300 uppercase tracking-widest">Belum ada anggota</p>
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
