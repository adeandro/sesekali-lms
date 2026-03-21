@extends('layouts.app')

@section('title', 'Ekskul yang Saya Bina')

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-indigo-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                <i class="fas fa-chalkboard-teacher text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight uppercase">Ekskul yang Saya Bina</h1>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Kelola nilai pengembangan diri siswa per semester
                </p>
            </div>
        </div>

        <form action="{{ route('admin.extracurriculars.my-assignments') }}" method="GET" class="relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-hover:text-emerald-500 transition-colors">
                <i class="fas fa-calendar-alt text-[10px]"></i>
            </div>
            <input type="text" name="academic_year" 
                   value="{{ $academicYear }}" 
                   onchange="this.form.submit()"
                   placeholder="2024/2025"
                   class="pl-10 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-emerald-500 w-36 transition-all">
        </form>
    </div>

    {{-- Assignment Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @forelse($assignments as $assignment)
            @php $ekskul = $assignment->extracurricular; @endphp
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-emerald-500/5 transition-all duration-500 group overflow-hidden">
                <div class="p-8">
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex-1">
                            <h3 class="text-sm font-black text-gray-900 tracking-tight flex items-center gap-2">
                                {{ $ekskul->name }}
                                @if($ekskul->is_active)
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-red-400"></span>
                                @endif
                            </h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">
                                {{ $ekskul->is_active ? 'Aktif' : 'Non-aktif' }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 group-hover:text-emerald-500 transition-colors shadow-sm">
                            <i class="fas fa-running text-lg"></i>
                        </div>
                    </div>

                    <p class="text-[11px] text-gray-500 leading-relaxed line-clamp-2 h-8 mb-8 font-medium italic">
                        {{ $ekskul->description ?? 'Tidak ada deskripsi kegiatan.' }}
                    </p>

                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('admin.extracurriculars.grades', $ekskul) }}?semester=1&academic_year={{ $academicYear }}"
                           class="flex flex-col items-center justify-center p-6 rounded-2xl bg-gray-50 hover:bg-emerald-50 hover:text-emerald-600 transition-all border border-transparent hover:border-emerald-100 group/btn">
                            <div class="text-xs font-black uppercase tracking-widest mb-1">Semester 1</div>
                            <div class="text-[9px] font-bold text-gray-400 group-hover/btn:text-emerald-500 uppercase tracking-widest">Input Nilai Ganjil</div>
                        </a>
                        <a href="{{ route('admin.extracurriculars.grades', $ekskul) }}?semester=2&academic_year={{ $academicYear }}"
                           class="flex flex-col items-center justify-center p-6 rounded-2xl bg-gray-50 hover:bg-emerald-50 hover:text-emerald-600 transition-all border border-transparent hover:border-emerald-100 group/btn">
                            <div class="text-xs font-black uppercase tracking-widest mb-1">Semester 2</div>
                            <div class="text-[9px] font-bold text-gray-400 group-hover/btn:text-emerald-500 uppercase tracking-widest">Input Nilai Genap</div>
                        </a>
                    </div>
                </div>
                
                {{-- Footer Info --}}
                <div class="px-8 py-4 bg-gray-50/50 border-t border-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-users text-[10px] text-gray-300"></i>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">
                            {{ $ekskul->members()->where('academic_year', $academicYear)->count() }} Siswa Terdaftar
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.extracurriculars.sessions.index', [
                            'extracurricular' => $ekskul->id,
                            'academic_year'   => $academicYear,
                        ]) }}" 
                        class="flex items-center gap-1.5 text-[8px] font-black text-emerald-600 
                                uppercase tracking-widest hover:underline">
                            <i class="fas fa-clipboard-list text-[8px]"></i> Jurnal & Presensi
                        </a>
                        @if(auth()->user()->role === 'superadmin')
                            <span class="text-gray-200">|</span>
                            <a href="{{ route('admin.extracurriculars.show', $ekskul) }}?academic_year={{ $academicYear }}" 
                            class="text-[8px] font-black text-emerald-600 uppercase tracking-widest hover:underline">
                                Kelola Anggota <i class="fas fa-arrow-right ml-1 text-[7px]"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white p-20 rounded-[2.5rem] border border-dashed border-gray-200 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto text-gray-300 mb-6">
                    <i class="fas fa-chalkboard-teacher text-3xl"></i>
                </div>
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">Belum Ada Penugasan Pembina</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-2 max-w-xs mx-auto leading-relaxed">
                    Anda belum terdaftar sebagai pembina ekskul manapun untuk tahun ajaran {{ $academicYear }}.
                </p>
                @if(auth()->user()->role === 'superadmin')
                    <a href="{{ route('admin.extracurriculars.index') }}" class="mt-8 inline-flex items-center gap-3 px-8 py-3.5 rounded-2xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 hover:scale-105 transition-all duration-300">
                        <i class="fas fa-cog"></i> Kelola Deskripsi Ekskul
                    </a>
                @endif
            </div>
        @endforelse
    </div>
</div>
@endsection
