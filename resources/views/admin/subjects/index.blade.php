@extends('layouts.app')

@section('title', 'Manajemen Mata Pelajaran - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Manajemen Mata Pelajaran')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 animate-fadeIn pb-12">
    <!-- Header & Statistics -->
    <div class="flex flex-col gap-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                    <i class="fas fa-book-open text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Mata Pelajaran</h2>
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Kelola kategori ujian dan bank soal sistem</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.subjects.create') }}" class="h-14 px-8 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center gap-3 group">
                    <i class="fas fa-plus-circle text-sm group-hover:scale-110 transition-transform"></i> Tambah Mapel
                </a>
                <form id="deleteAllSubjectsForm" action="{{ route('admin.subjects.deleteAll') }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmDeleteAllSubjects()" class="h-14 px-8 bg-rose-50 text-rose-600 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-rose-600 hover:text-white transition flex items-center gap-3 border border-rose-100 group">
                        <i class="fas fa-trash-alt text-sm group-hover:scale-110 transition-transform"></i> Kosongkan
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex items-center gap-6 group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50/30 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform duration-700"></div>
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500">
                    <i class="fas fa-list-ul text-2xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Total Mata Pelajaran</p>
                    <h4 class="text-3xl font-black text-gray-900 leading-none tracking-tight">{{ $subjects->total() }}</h4>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex items-center gap-6 group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50/30 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform duration-700"></div>
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-500">
                    <i class="fas fa-tasks text-2xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Total Bank Soal</p>
                    <h4 class="text-3xl font-black text-gray-900 leading-none tracking-tight">{{ $subjects->sum('questions_count') }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Subjects Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($subjects as $subject)
            <div class="group bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 hover:shadow-2xl hover:shadow-indigo-500/10 hover:-translate-y-2 transition-all duration-500 relative overflow-hidden">
                <!-- Decorative Layer -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50/50 rounded-bl-[5rem] group-hover:bg-indigo-600 group-hover:scale-110 transition-all duration-700 -mr-8 -mt-8 opacity-50 group-hover:opacity-100"></div>
                
                <div class="relative z-10 flex flex-col h-full">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 mb-6 group-hover:bg-white/20 group-hover:text-white transition-all duration-500 shadow-sm">
                        <i class="fas fa-book-reader text-2xl"></i>
                    </div>
                    
                    <h3 class="text-xl font-black text-gray-900 leading-tight truncate group-hover:text-white transition-colors duration-500 pr-12" title="{{ $subject->name }}">
                        {{ $subject->name }}
                    </h3>
                    
                    <div class="mt-auto pt-8 flex items-end justify-between border-t border-gray-50 group-hover:border-white/20 transition-colors duration-500">
                        <div class="flex flex-col">
                            <span class="text-3xl font-black text-gray-900 group-hover:text-white transition-colors duration-500 leading-none mb-1">{{ $subject->questions_count }}</span>
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] group-hover:text-white/60 transition-colors duration-500">Butir Soal</span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.subjects.edit', $subject) }}" class="w-12 h-12 flex items-center justify-center bg-gray-50 text-gray-400 rounded-2xl hover:bg-amber-500 hover:text-white hover:rotate-12 transition-all duration-300 shadow-sm" title="Edit Mapel">
                                <i class="fas fa-pen-nib text-sm"></i>
                            </a>
                            <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" id="deleteSubjectForm{{ $subject->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="deleteSubject('{{ $subject->name }}', {{ $subject->id }})" class="w-12 h-12 flex items-center justify-center bg-gray-50 text-gray-400 rounded-2xl hover:bg-rose-500 hover:text-white hover:-rotate-12 transition-all duration-300 shadow-sm" title="Hapus Mapel">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-24 bg-white rounded-[3rem] border-4 border-dashed border-gray-50 flex flex-col items-center justify-center text-center px-8 group hover:border-indigo-100 transition-colors duration-500">
                <div class="w-24 h-24 rounded-full bg-gray-50 flex items-center justify-center text-gray-200 mb-8 group-hover:bg-indigo-50 group-hover:text-indigo-200 transition-all duration-500">
                    <i class="fas fa-layer-group text-5xl"></i>
                </div>
                <h4 class="text-xl font-black text-gray-400 group-hover:text-indigo-600 transition-colors duration-500 uppercase tracking-widest mb-2">Data Mapel Kosong</h4>
                <p class="text-[11px] font-bold text-gray-300 group-hover:text-indigo-400 transition-colors duration-500 uppercase tracking-[0.3em] max-w-sm leading-relaxed">Sistem belum memiliki kategori mata pelajaran. Silakan tambahkan data baru untuk mulai membuat bank soal.</p>
                <a href="{{ route('admin.subjects.create') }}" class="mt-8 px-8 py-4 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center gap-3 group">
                    <i class="fas fa-plus-circle text-xs"></i> Tambah Sekarang
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="flex justify-center mt-12">
        {{ $subjects->links() }}
    </div>
</div>

<script>
    function deleteSubject(name, id) {
        Swal.fire({
            title: '<span class="text-xl font-black uppercase tracking-widest">Hapus Mata Pelajaran?</span>',
            html: `
                <div class="text-center space-y-4 py-4">
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-tight leading-relaxed">Anda akan menghapus kategori <span class="text-indigo-600">"${name}"</span> secara permanen.</p>
                    <div class="bg-rose-50 p-4 rounded-2xl border border-rose-100">
                        <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest leading-relaxed">⚠ PERINGATAN: Seluruh bank soal dan data ujian terkait akan ikut terhapus secara permanen!</p>
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
                popup: 'rounded-[2.5rem] border-none shadow-2xl p-8',
                confirmButton: 'rounded-2xl font-black px-8 py-4 text-[10px] uppercase tracking-widest mr-4',
                cancelButton: 'rounded-2xl font-black px-8 py-4 text-[10px] uppercase tracking-widest'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteSubjectForm' + id).submit();
            }
        });
    }

    function confirmDeleteAllSubjects() {
        Swal.fire({
            title: '<span class="text-xl font-black text-rose-600 uppercase tracking-widest">⚠ TINDAKAN KRITIKAL!</span>',
            html: `
                <div class="text-center space-y-6 py-4">
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-tight leading-relaxed">Anda akan menghapus <span class="text-rose-600 underline">SELURUH</span> data mata pelajaran di sistem.</p>
                    <div class="bg-rose-50 border-2 border-rose-100 p-6 rounded-[2rem] shadow-sm">
                        <p class="font-black text-rose-900 mb-4 text-[10px] uppercase tracking-widest">KONSEKUENSI TINDAKAN:</p>
                        <ul class="text-[10px] text-rose-800 space-y-3 font-black uppercase tracking-wider text-left pl-4 list-disc italic">
                            <li>SELURUH KATEGORI MAPEL AKAN HILANG</li>
                            <li>SELURUH BANK SOAL AKAN DIKOSONGKAN</li>
                            <li>RIWAYAT UJIAN & NILAI TERHAPUS PERMANEN</li>
                        </ul>
                    </div>
                    <div class="space-y-3">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ketik <span class="text-gray-900 bg-gray-100 px-2 py-1 rounded">HAPUS SEMUA</span> untuk konfirmasi:</p>
                        <input type="text" id="confirmInput" placeholder="HAPUS SEMUA" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-rose-500/10 text-sm font-black text-center uppercase tracking-widest">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#be123c',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'EKSEKUSI PENGHAPUSAN',
            cancelButtonText: 'BATALKAN',
            customClass: {
                popup: 'rounded-[3rem] border-none shadow-2xl p-10',
                confirmButton: 'rounded-2xl font-black px-8 py-4 text-[10px] uppercase tracking-widest mr-4',
                cancelButton: 'rounded-2xl font-black px-8 py-4 text-[10px] uppercase tracking-widest'
            },
            preConfirm: () => {
                const val = document.getElementById('confirmInput').value;
                if (val !== 'HAPUS SEMUA') {
                    Swal.showValidationMessage('Teks konfirmasi tidak sesuai!');
                    return false;
                }
                return true;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteAllSubjectsForm').submit();
            }
        });
    }

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '<span class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-600">BERHASIL!</span>',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2500,
            customClass: { popup: 'rounded-[2rem] border-none shadow-xl' }
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: '<span class="text-[10px] font-black uppercase tracking-[0.3em] text-rose-600">GAGAL!</span>',
            text: "{{ session('error') }}",
            confirmButtonColor: '#4f46e5',
            customClass: { 
                popup: 'rounded-[2rem] border-none shadow-xl',
                confirmButton: 'rounded-2xl font-black px-8 py-4 text-[10px] uppercase tracking-widest'
            }
        });
    @endif
</script>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection

    