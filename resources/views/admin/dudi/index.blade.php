@extends('layouts.app')

@section('title', 'Kegiatan DU/DI Siswa - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Kegiatan DU/DI Siswa')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 animate-fadeIn pb-12" x-data="{ showImportModal: false }">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-100">
                <i class="fas fa-industry text-2xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Kegiatan DU/DI</h2>
                <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Kelola data prakerin dan industri mitra siswa</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showImportModal = true" class="h-14 px-8 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center gap-3 group">
                <i class="fas fa-file-import text-sm group-hover:scale-110 transition-transform"></i> Import Excel
            </button>
            <a href="{{ route('admin.dudi.template', ['class_id' => $classId]) }}" class="h-14 px-8 bg-white text-gray-700 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-50 transition border border-gray-200 flex items-center gap-3 group">
                <i class="fas fa-download text-sm group-hover:scale-110 transition-transform text-indigo-600"></i> Template
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
        <form action="{{ route('admin.dudi.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            @if(auth()->user()->role === 'teacher' && $classes->count() === 1)
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Kelas Anda</label>
                    <div class="w-full px-6 py-4 bg-gray-100 border-none rounded-2xl text-sm font-black text-indigo-600 uppercase tracking-tight">
                        {{ $classes->first()->name }}
                    </div>
                    <input type="hidden" name="class_id" value="{{ $classes->first()->id }}">
                </div>
            @elseif($classes->isEmpty())
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Kelas</label>
                    <div class="w-full px-6 py-4 bg-rose-50 border-none rounded-2xl text-[10px] font-black text-rose-600 uppercase tracking-widest leading-none">
                        Bukan Wali Kelas
                    </div>
                </div>
            @else
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Pilih Kelas</label>
                    <select name="class_id" onchange="this.form.submit()" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold text-gray-900 appearance-none cursor-pointer">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Semester</label>
                <select name="semester" onchange="this.form.submit()" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold text-gray-900 appearance-none">
                    <option value="1" {{ $semester == 1 ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                    <option value="2" {{ $semester == 2 ? 'selected' : '' }}>Semester 2 (Genap)</option>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tahun Ajaran</label>
                <input type="text" name="academic_year" value="{{ $academicYear }}" placeholder="Contoh: 2024/2025" 
                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold text-gray-900">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 h-14 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-black transition shadow-lg shadow-gray-200">
                    Filter Data
                </button>
                <a href="{{ route('admin.dudi.index') }}" class="w-14 h-14 bg-gray-100 text-gray-500 flex items-center justify-center rounded-2xl hover:bg-gray-200 transition">
                    <i class="fas fa-sync-alt"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    @if($classId)
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">No</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Siswa</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">NIS</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">Jumlah Kegiatan</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($students as $index => $student)
                    <tr class="group hover:bg-gray-50/50 transition-colors">
                        <td class="px-8 py-6 text-sm font-bold text-gray-400">{{ $index + 1 }}</td>
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-xs uppercase shadow-sm">
                                    {{ substr($student->name, 0, 1) }}
                                </div>
                                <span class="text-sm font-black text-gray-900 uppercase tracking-tight">{{ $student->name }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-sm font-bold text-gray-500">{{ $student->nis ?? '-' }}</td>
                        <td class="px-8 py-6 text-center">
                            @if($student->dudi_count > 0)
                                <span class="px-4 py-1.5 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black border border-emerald-100">
                                    {{ $student->dudi_count }} KEGIATAN
                                </span>
                            @else
                                <span class="px-4 py-1.5 bg-gray-50 text-gray-400 rounded-full text-[10px] font-black border border-gray-100">
                                    KOSONG
                                </span>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-right">
                            <a href="{{ route('admin.dudi.edit', [$student->id, 'semester' => $semester, 'academic_year' => $academicYear]) }}" 
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-xl border border-indigo-100 hover:bg-indigo-600 hover:text-white hover:shadow-lg hover:shadow-indigo-100 transition-all group/btn">
                                <i class="fas fa-edit text-xs transition-transform group-hover/btn:scale-110"></i> Kelola
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-20 h-20 rounded-full bg-gray-50 flex items-center justify-center text-gray-300">
                                    <i class="fas fa-user-graduate text-3xl"></i>
                                </div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Tidak ada siswa ditemukan di kelas ini</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-gray-50/50 rounded-[2.5rem] border-2 border-dashed border-gray-200 py-32 flex flex-col items-center justify-center text-center px-8">
        <div class="w-24 h-24 rounded-full bg-white flex items-center justify-center text-gray-300 shadow-sm mb-6">
            <i class="fas fa-layer-group text-4xl"></i>
        </div>
        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight mb-2">Pilih Kelas Terlebih Dahulu</h3>
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] max-w-sm leading-relaxed">Gunakan filter di atas untuk memilih kelas dan periode akademik yang ingin dikelola.</p>
    </div>
    @endif

    <!-- Import Modal -->
    <div x-show="showImportModal" 
        class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none;">
        
        <div @click.away="showImportModal = false" 
            class="bg-white w-full max-w-xl rounded-[3rem] shadow-2xl p-10 relative overflow-hidden"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100">
            
            <div class="absolute top-0 left-0 w-full h-2 bg-indigo-600"></div>
            
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-file-upload text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Import Data DU/DI</h3>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest text-left">Unggah file Excel untuk memproses data massal</p>
                    </div>
                </div>
                <button @click="showImportModal = false" class="text-gray-400 hover:text-rose-500 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="{{ route('admin.dudi.import') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                <input type="hidden" name="class_id" value="{{ $classId }}">
                <input type="hidden" name="semester" value="{{ $semester }}">
                <input type="hidden" name="academic_year" value="{{ $academicYear }}">

                <div class="bg-indigo-50/50 p-8 rounded-[2.5rem] border-2 border-dashed border-indigo-100 text-center group hover:border-indigo-400 transition-all cursor-pointer relative">
                    <input type="file" name="file" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-4">
                        <div class="w-16 h-16 rounded-2xl bg-white flex items-center justify-center text-indigo-600 mx-auto shadow-sm group-hover:scale-110 transition-transform">
                            <i class="fas fa-cloud-upload-alt text-2xl"></i>
                        </div>
                        <p class="text-xs font-black text-indigo-900 uppercase tracking-widest">Klik atau Tarik File Excel ke Sini</p>
                        <p class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest leading-relaxed">Format: .xlsx, .xls (Maks 10MB)</p>
                    </div>
                </div>

                <div class="bg-amber-50 rounded-2xl p-6 flex gap-4 items-start border border-amber-100">
                    <i class="fas fa-info-circle text-amber-500 mt-1"></i>
                    <p class="text-[10px] font-bold text-amber-700 leading-relaxed uppercase tracking-wide">
                        Pastikan menggunakan <a href="{{ route('admin.dudi.template', ['class_id' => $classId]) }}" class="underline font-black hover:text-amber-900">template resmi</a> agar data terproses dengan benar.
                    </p>
                </div>

                <div class="flex gap-4 pt-2">
                    <button type="submit" class="flex-1 h-16 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                        MULAI IMPORT DATA
                    </button>
                    <button type="button" @click="showImportModal = false" class="px-10 h-16 bg-gray-50 text-gray-500 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-gray-100 transition">
                        BATAL
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: '<span class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-600">BERHASIL!</span>',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 3000,
            customClass: { popup: 'rounded-[2rem] border-none shadow-xl p-8' }
        });
    });
</script>
@endif
@endsection
