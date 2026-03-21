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
                    <h4 class="text-3xl font-black text-gray-900 leading-none tracking-tight">{{ \App\Models\Subject::count() }}</h4>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex items-center gap-6 group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50/30 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform duration-700"></div>
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-500">
                    <i class="fas fa-tasks text-2xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Total Bank Soal</p>
                    <h4 class="text-3xl font-black text-gray-900 leading-none tracking-tight">{{ \App\Models\Question::count() }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Subjects List (Grouped by Category) -->
    <div class="space-y-12">
        @foreach(\App\Models\Subject::categories() as $categoryKey => $categoryName)
            @php
                $categorySubjects = \App\Models\Subject::where('category', $categoryKey)->withCount('questions')->orderBy('sort_order')->get();
            @endphp
            
            <div class="space-y-6">
                <div class="flex items-center gap-4 px-4">
                    <div class="h-px flex-1 bg-gray-100"></div>
                    <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.3em]">{{ $categoryName }}</h3>
                    <div class="h-px flex-1 bg-gray-100"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 sortable-container" data-category="{{ $categoryKey }}">
                    @forelse($categorySubjects as $subject)
                        <div class="group bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 relative overflow-hidden flex flex-col" data-id="{{ $subject->id }}">
                            <!-- Decorative Circle -->
                            <div class="absolute -top-4 -right-4 w-24 h-24 rounded-full opacity-0 group-hover:opacity-10 shadow-xl transition-all duration-500 
                                {{ $categoryKey === 'umum' ? 'bg-indigo-600' : ($categoryKey === 'kejuruan' ? 'bg-emerald-600' : 'bg-purple-600') }}"></div>

                            <div class="relative z-10 flex flex-col h-full">
                                <div class="flex items-start justify-between mb-6">
                                    <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500 shadow-inner drag-handle cursor-move">
                                        <i class="fas fa-bars text-sm"></i>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest shadow-sm border
                                            {{ $categoryKey === 'umum' ? 'bg-blue-50 text-blue-600 border-blue-100' : ($categoryKey === 'kejuruan' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-purple-50 text-purple-600 border-purple-100') }}">
                                            {{ $categoryKey === 'umum' ? 'Umum' : ($categoryKey === 'kejuruan' ? 'Kejuruan' : 'Muatan Lokal') }}
                                        </span>
                                        <span class="px-2 py-1 bg-amber-50 text-amber-600 border border-amber-100 rounded-md text-[8px] font-black tracking-widest">KKM: {{ $subject->kkm }}</span>
                                    </div>
                                </div>
                                
                                <h3 class="text-lg font-black text-gray-900 leading-tight group-hover:text-indigo-600 transition-colors duration-500 line-clamp-2 mb-2" title="{{ $subject->name }}">
                                    {{ $subject->name }}
                                </h3>
                                
                                <div class="mt-auto pt-6 flex items-center justify-between border-t border-gray-50">
                                    <div class="flex flex-col">
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-2xl font-black text-gray-900 leading-none">{{ $subject->questions_count }}</span>
                                            <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Soal</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.subjects.edit', $subject) }}" class="w-10 h-10 flex items-center justify-center bg-gray-50 text-gray-400 rounded-xl hover:bg-amber-500 hover:text-white transition-all duration-300 shadow-sm" title="Edit Mapel">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" id="deleteSubjectForm{{ $subject->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="deleteSubject('{{ $subject->name }}', {{ $subject->id }})" class="w-10 h-10 flex items-center justify-center bg-gray-50 text-gray-400 rounded-xl hover:bg-rose-500 hover:text-white transition-all duration-300 shadow-sm" title="Hapus Mapel">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 bg-gray-50/50 rounded-[2rem] border-2 border-dashed border-gray-100 flex flex-col items-center justify-center text-center px-8">
                            <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest leading-relaxed">Belum ada mapel di kategori ini</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.sortable-container');
        
        containers.forEach(el => {
            Sortable.create(el, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'opacity-50',
                onEnd: function() {
                    const ids = [...el.querySelectorAll('[data-id]')]
                        .map(item => item.dataset.id);
                    
                    fetch('{{ route("admin.subjects.reorder") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ ids })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Reorder',
                                text: 'Terjadi kesalahan saat menyimpan urutan baru.'
                            });
                        }
                    });
                }
            });
        });
    });

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