@extends('layouts.app')

@section('title', 'Kehadiran & Kepribadian - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Kehadiran & Kepribadian')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 animate-fadeIn pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                <i class="fas fa-user-edit text-2xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Data Raport Siswa</h2>
                <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Input manual kehadiran dan kepribadian siswa per semester</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.report-data.index') }}" class="h-14 px-8 bg-white text-gray-400 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:text-gray-900 transition border border-gray-100 flex items-center gap-3 group">
                <i class="fas fa-arrow-left text-sm group-hover:-translate-x-1 transition-transform"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
        <form action="{{ route('admin.report-data.student-data') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            @if(auth()->user()->role === 'teacher' && $classes->count() === 1)
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Kelas Anda</label>
                    <div class="w-full h-14 bg-indigo-50 border-none rounded-2xl px-6 flex items-center text-sm font-black text-indigo-700 uppercase tracking-tight">
                        Kelas {{ $classes->first()->grade }} - {{ $classes->first()->name }}
                    </div>
                    <input type="hidden" name="class_id" value="{{ $classes->first()->id }}">
                </div>
            @elseif($classes->isEmpty())
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 text-rose-500">Status Akses</label>
                    <div class="w-full h-14 bg-rose-50 border-none rounded-2xl px-6 flex items-center text-[10px] font-black text-rose-600 uppercase tracking-[0.2em]">
                        Tidak Ada Akses Wali Kelas
                    </div>
                </div>
            @else
                <div class="space-y-3">
                    <label for="class_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Pilih Kelas</label>
                    <select name="class_id" id="class_id" class="w-full h-14 bg-gray-50 border-none rounded-2xl px-6 text-sm font-bold border-r-[1.5rem] border-transparent focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer" onchange="this.form.submit()">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                Kelas {{ $class->grade }} - {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="space-y-3">
                <label for="semester" class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Semester</label>
                <select name="semester" id="semester" class="w-full h-14 bg-gray-50 border-none rounded-2xl px-6 text-sm font-bold border-r-[1.5rem] border-transparent focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer" onchange="this.form.submit()">
                    <option value="ganjil" {{ $semester == 'ganjil' ? 'selected' : '' }}>Semester Ganjil</option>
                    <option value="genap" {{ $semester == 'genap' ? 'selected' : '' }}>Semester Genap</option>
                </select>
            </div>
            <div class="h-14 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-2xl text-[10px] font-black uppercase tracking-widest">
                {{ $academicYear }}
            </div>
        </form>
    </div>

    @if($classId && count($students) > 0)
    <form action="{{ route('admin.report-data.save-student-data') }}" method="POST">
        @csrf
        <input type="hidden" name="academic_year" value="{{ $academicYear }}">
        <input type="hidden" name="semester" value="{{ $semester }}">
        <input type="hidden" name="class_id" value="{{ $classId }}">

        <div class="space-y-6">
            @foreach($students as $student)
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
                <div class="p-8 border-b border-gray-50 bg-gray-50/30 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white shadow-sm shrink-0">
                            <img src="{{ $student->photo_url }}" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">{{ $student->name }}</h3>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">NISN: {{ $student->username }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
                    {{-- Kolom Kiri: Kehadiran --}}
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                            <i class="fas fa-calendar-alt"></i> Kehadiran
                        </h4>
                        <div class="grid grid-cols-3 gap-6">
                            <div class="space-y-3">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Sakit</label>
                                <input type="number" name="students[{{ $student->id }}][attendance][sick_days]" value="{{ $student->attendance->first()->sick_days ?? 0 }}" class="w-full h-12 bg-gray-50 border-none rounded-xl px-4 text-sm font-bold text-center focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                            <div class="space-y-3">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Izin</label>
                                <input type="number" name="students[{{ $student->id }}][attendance][permit_days]" value="{{ $student->attendance->first()->permit_days ?? 0 }}" class="w-full h-12 bg-gray-50 border-none rounded-xl px-4 text-sm font-bold text-center focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                            <div class="space-y-3">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Alfa</label>
                                <input type="number" name="students[{{ $student->id }}][attendance][alpha_days]" value="{{ $student->attendance->first()->alpha_days ?? 0 }}" class="w-full h-12 bg-gray-50 border-none rounded-xl px-4 text-sm font-bold text-center focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Kepribadian --}}
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                            <i class="fas fa-heart"></i> Kepribadian
                        </h4>
                        <div class="space-y-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-3">
                                    <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Kedisiplinan</label>
                                    <select name="students[{{ $student->id }}][personality][discipline]" class="w-full h-12 bg-gray-50 border-none rounded-xl px-4 text-[10px] font-bold uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                        <option value="">-</option>
                                        <option value="Sangat Baik" {{ ($student->personality->first()->discipline ?? '') == 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik</option>
                                        <option value="Baik" {{ ($student->personality->first()->discipline ?? '') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                        <option value="Cukup" {{ ($student->personality->first()->discipline ?? '') == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                        <option value="Kurang" {{ ($student->personality->first()->discipline ?? '') == 'Kurang' ? 'selected' : '' }}>Kurang</option>
                                    </select>
                                </div>
                                <div class="space-y-3">
                                    <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Kelakuan</label>
                                    <select name="students[{{ $student->id }}][personality][behavior]" class="w-full h-12 bg-gray-50 border-none rounded-xl px-4 text-[10px] font-bold uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                        <option value="">-</option>
                                        <option value="Sangat Baik" {{ ($student->personality->first()->behavior ?? '') == 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik</option>
                                        <option value="Baik" {{ ($student->personality->first()->behavior ?? '') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                        <option value="Cukup" {{ ($student->personality->first()->behavior ?? '') == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                        <option value="Kurang" {{ ($student->personality->first()->behavior ?? '') == 'Kurang' ? 'selected' : '' }}>Kurang</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Kerapian</label>
                                <select name="students[{{ $student->id }}][personality][neatness]" class="w-full h-12 bg-gray-50 border-none rounded-xl px-4 text-[10px] font-bold uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                    <option value="">-</option>
                                    <option value="Sangat Baik" {{ ($student->personality->first()->neatness ?? '') == 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik</option>
                                    <option value="Baik" {{ ($student->personality->first()->neatness ?? '') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                    <option value="Cukup" {{ ($student->personality->first()->neatness ?? '') == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                    <option value="Kurang" {{ ($student->personality->first()->neatness ?? '') == 'Kurang' ? 'selected' : '' }}>Kurang</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="fixed bottom-12 right-12 z-50">
            <button type="submit" class="h-16 px-12 bg-indigo-600 text-white text-[11px] font-black uppercase tracking-[0.3em] rounded-2xl hover:bg-indigo-700 transition shadow-2xl shadow-indigo-200 flex items-center gap-4 group">
                <i class="fas fa-save text-base group-hover:scale-110 transition-transform"></i> Simpan Semua Data
            </button>
        </div>
    </form>
    @elseif($classId)
    <div class="bg-white py-24 rounded-[3rem] border-4 border-dashed border-gray-50 flex flex-col items-center justify-center text-center px-8">
        <div class="w-24 h-24 rounded-full bg-gray-50 flex items-center justify-center text-gray-200 mb-8">
            <i class="fas fa-user-friends text-5xl"></i>
        </div>
        <h4 class="text-xl font-black text-gray-400 uppercase tracking-widest mb-2">Siswa Tidak Ditemukan</h4>
        <p class="text-[11px] font-bold text-gray-300 uppercase tracking-[0.3em] max-w-sm leading-relaxed">Belum ada siswa terdaftar di kelas ini untuk tahun ajaran aktif.</p>
    </div>
    @else
    <div class="bg-indigo-600 py-24 rounded-[3rem] shadow-2xl shadow-indigo-200 flex flex-col items-center justify-center text-center px-8 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
            <div class="grid grid-cols-6 gap-8 p-12 translate-y-[-20%] rotate-[-12deg]">
                @for($i = 0; $i < 24; $i++)
                    <i class="fas fa-user-edit text-6xl text-white"></i>
                @endfor
            </div>
        </div>
        <div class="relative z-10 animate-bounce-slow">
            <div class="w-24 h-24 rounded-[2rem] bg-indigo-500 flex items-center justify-center text-white mb-8 mx-auto shadow-2xl border-4 border-indigo-400">
                <i class="fas fa-mouse-pointer text-4xl"></i>
            </div>
        </div>
        <h4 class="relative z-10 text-2xl font-black text-white uppercase tracking-[0.2em] mb-4">Mulai Input Data Raport</h4>
        <p class="relative z-10 text-[11px] font-black text-indigo-100 uppercase tracking-[0.3em] max-w-md leading-relaxed">Silakan pilih kelas dan semester terlebih dahulu untuk memuat daftar siswa dan form input raport.</p>
    </div>
    @endif
</div>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    .animate-bounce-slow { animation: bounce 3s infinite; }
    @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
</style>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: '<span class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-600">BERHASIL!</span>',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2500,
            customClass: { popup: 'rounded-[2rem] border-none shadow-xl' }
        });
    });
</script>
@endif
@endsection
