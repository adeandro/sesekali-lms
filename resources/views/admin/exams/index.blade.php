@extends('layouts.app')

@section('title', 'Manajemen Ujian - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Manajemen Ujian')

@section('content')
<div class="space-y-8 animate-fadeIn pb-12">
    <!-- Header & Quick Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                <i class="fas fa-file-signature text-xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Manajemen Ujian</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Kelola jadwal, butir soal, dan konfigurasi ujian terpusat</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.exams.create') }}" class="group h-14 px-8 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center gap-3">
                <i class="fas fa-plus-circle text-xs group-hover:rotate-90 transition-transform duration-500"></i> Buat Ujian Baru
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Ujian -->
        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-scroll"></i>
                </div>
                <span class="text-[10px] font-black text-indigo-200 uppercase tracking-widest">Total</span>
            </div>
            <h3 class="text-3xl font-black text-gray-900 tracking-tighter">{{ $exams->total() }}</h3>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1 italic">Ujian Terdaftar</p>
        </div>

        <!-- Ujian Aktif -->
        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm group hover:shadow-xl hover:shadow-emerald-500/5 transition-all duration-500">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-play"></i>
                </div>
                <span class="text-[10px] font-black text-emerald-200 uppercase tracking-widest">Live</span>
            </div>
            <h3 class="text-3xl font-black text-gray-900 tracking-tighter">{{ $exams->where('status', 'published')->count() }}</h3>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1 italic">Dipublikasikan</p>
        </div>

        <!-- Selesai -->
        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm group hover:shadow-xl hover:shadow-gray-500/5 transition-all duration-500">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-600 group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-check-double"></i>
                </div>
                <span class="text-[10px] font-black text-gray-200 uppercase tracking-widest">Done</span>
            </div>
            <h3 class="text-3xl font-black text-gray-900 tracking-tighter">{{ $exams->where('status', 'finished')->count() }}</h3>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1 italic">Telah Selesai</p>
        </div>

        <!-- Draft -->
        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm group hover:shadow-xl hover:shadow-amber-500/5 transition-all duration-500">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-pencil-alt"></i>
                </div>
                <span class="text-[10px] font-black text-amber-200 uppercase tracking-widest">Draft</span>
            </div>
            <h3 class="text-3xl font-black text-gray-900 tracking-tighter">{{ $exams->where('status', 'draft')->count() }}</h3>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1 italic">Butuh Peninjauan</p>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8">
        <form action="{{ route('admin.exams.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="md:col-span-1 relative group">
                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none group-focus-within:text-indigo-500 transition-colors">
                    <i class="fas fa-search text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul ujian..." 
                    class="block w-full h-14 pl-12 pr-6 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 text-xs font-bold transition-all placeholder:text-gray-300">
            </div>
            
            <div class="relative">
                <select name="subject" class="block w-full h-14 pl-6 pr-12 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 text-[10px] font-black uppercase tracking-widest appearance-none transition-all cursor-pointer">
                    <option value="">Semua Mapel</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </div>
            </div>

            <div class="relative">
                <select name="status" class="block w-full h-14 pl-6 pr-12 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 text-[10px] font-black uppercase tracking-widest appearance-none transition-all cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>DRAFT</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>DIPUBLIKASIKAN</option>
                    <option value="finished" {{ request('status') == 'finished' ? 'selected' : '' }}>SELESAI</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </div>
            </div>

            <button type="submit" class="h-14 bg-gray-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-800 transition active:scale-95 flex items-center justify-center gap-3">
                <i class="fas fa-filter text-[10px]"></i> Saring Data
            </button>
        </form>
    </div>

    <!-- Exams Grid/Table -->
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Detail Sesi Ujian</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Kelas & Durasi</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Butir Soal</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Kelola</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($exams as $exam)
                        <tr class="group hover:bg-indigo-50/30 transition-all duration-500">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-5">
                                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:bg-white group-hover:shadow-lg group-hover:shadow-indigo-500/10 transition-all duration-500 border border-transparent group-hover:border-indigo-100">
                                        <i class="fas fa-file-invoice text-xl"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors duration-500">{{ $exam->title }}</p>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">{{ $exam->subject->name }}</span>
                                            <span class="text-[9px] font-black text-gray-300">•</span>
                                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ $exam->start_time->format('d M Y, H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <div class="inline-flex flex-col items-center gap-1.5 p-3 rounded-2xl bg-gray-50 group-hover:bg-white transition-colors duration-500">
                                    <span class="px-3 py-1 bg-white text-gray-900 text-[10px] font-black rounded-lg uppercase tracking-tight shadow-sm">Kelas {{ $exam->jenjang }}</span>
                                    <span class="text-[10px] font-bold text-gray-400 italic">{{ $exam->duration_minutes }} Menit</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <div class="inline-flex flex-col items-center min-w-[80px] p-3 border border-gray-100 bg-white rounded-2xl shadow-sm group-hover:border-indigo-100 transition-all duration-500">
                                    <span class="text-sm font-black text-gray-900 tracking-tighter">{{ $exam->questions_count }}<span class="text-gray-300 mx-0.5">/</span>{{ $exam->total_questions }}</span>
                                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5 italic">Butir</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                @php
                                    $statusClasses = [
                                        'published' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'finished' => 'bg-gray-100 text-gray-500 border-gray-200',
                                        'draft' => 'bg-amber-50 text-amber-600 border-amber-100'
                                    ];
                                    $statusLabels = [
                                        'published' => 'AKTIF',
                                        'finished' => 'SELESAI',
                                        'draft' => 'DRAFT'
                                    ];
                                @endphp
                                <span class="px-4 py-2 border {{ $statusClasses[$exam->status] ?? 'bg-gray-100 text-gray-600' }} text-[9px] font-black rounded-xl uppercase tracking-[0.2em] shadow-sm">
                                    {{ $statusLabels[$exam->status] ?? 'UNKNOWN' }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center justify-end gap-2 opacity-50 group-hover:opacity-100 transition-all duration-500 translate-x-4 group-hover:translate-x-0">
                                    <a href="{{ route('admin.exams.manage-questions', $exam) }}" class="w-10 h-10 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm" title="Kelola Soal">
                                        <i class="fas fa-list-ol text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.exams.edit', $exam) }}" class="w-10 h-10 flex items-center justify-center bg-amber-50 text-amber-600 rounded-xl hover:bg-amber-500 hover:text-white transition-all shadow-sm" title="Edit Ujian">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.exams.print-credentials', $exam) }}" target="_blank" class="w-10 h-10 flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-500 hover:text-white transition-all shadow-sm" title="Cetak Kartu">
                                        <i class="fas fa-print text-xs"></i>
                                    </a>
                                    @if($exam->status === 'published')
                                        <a href="{{ route('admin.monitor.exams.index', $exam) }}" class="w-10 h-10 flex items-center justify-center bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-500 hover:text-white transition-all shadow-sm animate-pulse" title="Monitor Live">
                                            <i class="fas fa-video text-xs"></i>
                                        </a>
                                    @endif
                                    
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open" class="w-10 h-10 flex items-center justify-center bg-gray-50 text-gray-400 rounded-xl hover:bg-gray-900 hover:text-white transition-all">
                                            <i class="fas fa-ellipsis-v text-xs"></i>
                                        </button>
                                        <div x-show="open" 
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             @click.away="open = false"
                                             class="absolute right-0 bottom-full mb-3 w-48 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50"
                                             style="display: none;">
                                            @if($exam->status !== 'published' && $exam->canPublish())
                                                <form action="{{ route('admin.exams.publish', $exam) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="w-full px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-emerald-600 hover:bg-emerald-50 transition-colors flex items-center gap-3">
                                                        <i class="fas fa-check-circle text-xs"></i> Publikasikan
                                                    </button>
                                                </form>
                                            @endif
                                            @if($exam->status === 'published')
                                                <form action="{{ route('admin.exams.set-to-draft', $exam) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="w-full px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-amber-600 hover:bg-amber-50 transition-colors flex items-center gap-3">
                                                        <i class="fas fa-undo text-xs"></i> Kembalikan ke Draft
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.exams.destroy', $exam) }}" method="POST" id="deleteExamForm{{ $exam->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="deleteExam('{{ $exam->title }}', {{ $exam->id }})" class="w-full px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-rose-600 hover:bg-rose-50 transition-colors flex items-center gap-3">
                                                    <i class="fas fa-trash-alt text-xs"></i> Hapus Ujian
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400 space-y-4">
                                    <div class="w-20 h-20 rounded-[2rem] bg-gray-50 flex items-center justify-center text-gray-200">
                                        <i class="fas fa-scroll text-4xl"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm font-black text-gray-900 uppercase tracking-widest">Tidak Ada Data Ujian</p>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest italic leading-relaxed">Silakan buat ujian baru atau sesuaikan filter pencarian Anda</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="px-2">
        {{ $exams->appends(request()->query())->links() }}
    </div>
</div>

<script>
    function deleteExam(title, id) {
        Swal.fire({
            title: 'Hapus Sesi Ujian?',
            html: `
                <div class="space-y-3">
                    <p class="text-sm text-gray-500">Anda akan menghapus sesi <span class="font-black text-gray-900">${title}</span></p>
                    <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100 text-left">
                        <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest flex items-center gap-2 mb-1">
                            <i class="fas fa-exclamation-triangle"></i> Peringatan
                        </p>
                        <p class="text-[10px] font-bold text-rose-400 leading-relaxed uppercase tracking-tighter">
                            Seluruh riwayat pengerjaan, nilai, dan jawaban siswa akan dihapus permanen!
                        </p>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'YA, HAPUS PERMANEN',
            cancelButtonText: 'BATALKAN',
            customClass: {
                popup: 'rounded-[2.5rem] border-none shadow-2xl',
                confirmButton: 'h-12 px-8 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-transform active:scale-95',
                cancelButton: 'h-12 px-8 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-transform active:scale-95'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteExamForm' + id).submit();
            }
        });
    }

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '<span class="text-[14px] font-black uppercase tracking-widest text-emerald-600">Berhasil!</span>',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2500,
            customClass: { popup: 'rounded-[2.5rem] p-8' }
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: '<span class="text-[14px] font-black uppercase tracking-widest text-rose-600">Terjadi Kesalahan</span>',
            text: "{{ session('error') }}",
            confirmButtonColor: '#4f46e5',
            customClass: { popup: 'rounded-[2.5rem] p-8' }
        });
    @endif
</script>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    .animate-pop { animation: pop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pop { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
</style>
@endsection
