@extends('layouts.app')

@section('title', $material->title . ' - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Kelola Bab Materi')

@section('content')
<div class="space-y-8 animate-fadeIn pb-12">
    <!-- Header/Navigation -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex flex-col gap-2">
            <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
                <a href="{{ route('admin.learning.materials.index') }}" class="hover:text-indigo-600 transition-colors">Daftar Materi</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-indigo-600">Detail Materi</span>
            </nav>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                    <i class="fas fa-layer-group text-xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">{{ $material->title }}</h2>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">{{ $material->subject->name }} • {{ $material->sections->count() }} Bab Tersedia</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('learning.show', $material) }}" target="_blank" class="h-14 px-8 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-100 flex items-center gap-3">
                <i class="fas fa-chalkboard-teacher text-xs"></i> Mode Mengajar
            </a>
            <a href="{{ route('admin.learning.materials.sections.create', $material) }}" class="group h-14 px-8 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center gap-3">
                <i class="fas fa-plus-circle text-xs group-hover:rotate-90 transition-transform duration-500"></i> Tambah Bab Baru
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Material Info Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="h-48 bg-gray-100 relative">
                    @if($material->cover_image)
                        <img src="{{ asset('storage/' . $material->cover_image) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-indigo-100">
                            <i class="fas fa-image text-5xl"></i>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex flex-col justify-end p-6">
                         <span class="px-3 py-1 bg-white/20 backdrop-blur-md text-white text-[9px] font-black uppercase rounded-lg border border-white/20 self-start mb-2">
                            Mata Pelajaran
                        </span>
                        <h4 class="text-white font-black uppercase tracking-tight">{{ $material->subject->name }}</h4>
                    </div>
                </div>
                <div class="p-8 space-y-6">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Status Publikasi</span>
                        <span class="px-3 py-1 {{ $material->is_published ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }} text-[9px] font-black rounded-lg uppercase">
                            {{ $material->is_published ? 'Aktif' : 'Draft' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Dibuat Oleh</span>
                        <span class="text-[10px] font-black text-gray-900 uppercase tracking-tighter">{{ $material->creator->full_name }}</span>
                    </div>
                    <div class="pt-6 border-t border-gray-50">
                        <a href="{{ route('admin.learning.materials.edit', $material) }}" class="w-full h-14 bg-gray-50 text-gray-500 text-[10px] font-black uppercase tracking-widest rounded-2xl flex items-center justify-center gap-3 hover:bg-gray-100 transition-all border border-transparent hover:border-gray-200">
                            <i class="fas fa-cog"></i> Pengaturan Materi
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-xl shadow-indigo-100">
                <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center mb-6">
                    <i class="fas fa-info-circle text-xl"></i>
                </div>
                <h3 class="text-xl font-black uppercase tracking-tight mb-2">Tips Pengelolaan</h3>
                <p class="text-xs font-medium text-indigo-100 leading-relaxed opacity-80 italic">
                    Tarik dan lepaskan kartu bab di sebelah kanan untuk mengubah urutan materi yang akan dilihat oleh siswa. Simpan perubahan secara otomatis.
                </p>
            </div>
        </div>

        <!-- Sections List -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between px-2">
                <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2">
                    <i class="fas fa-stream"></i> Struktur Bab Pembelajaran
                </h3>
                <span class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest italic" id="status-reorder">Klik & Tahan Untuk Mengurutkan</span>
            </div>

            <div id="sections-list" class="space-y-4">
                @forelse($material->sections as $section)
                    <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-sm flex items-center gap-6 group hover:border-indigo-200 hover:shadow-lg transition-all cursor-move" data-id="{{ $section->id }}">
                        <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-300 group-hover:bg-indigo-50 group-hover:text-indigo-400 transition-colors">
                            <i class="fas fa-bars text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-1">
                                @php
                                    $typeIcons = [
                                        'text' => 'fas fa-align-left text-blue-400',
                                        'video' => 'fab fa-youtube text-rose-400',
                                        'file' => 'fas fa-file-pdf text-amber-400'
                                    ];
                                @endphp
                                <i class="{{ $typeIcons[$section->type] ?? 'fas fa-file' }} text-[10px]"></i>
                                <span class="text-[9px] font-black uppercase tracking-widest text-gray-400">{{ $section->type }}</span>
                            </div>
                            <h4 class="text-sm font-black text-gray-900 uppercase tracking-tight truncate group-hover:text-indigo-600 transition-colors">{{ $section->title }}</h4>
                        </div>
                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('admin.learning.sections.edit', $section) }}" class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all shadow-sm">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            <form action="{{ route('admin.learning.sections.destroy', $section) }}" method="POST" id="deleteForm{{ $section->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete({{ $section->id }})" class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all shadow-sm">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-[2.5rem] p-12 text-center border-2 border-dashed border-gray-100">
                        <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-6 text-gray-200">
                            <i class="fas fa-folder-open text-2xl"></i>
                        </div>
                        <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest">Belum Ada Bab</h4>
                        <p class="text-[10px] font-bold text-gray-300 uppercase mt-2">Mulai tambahkan bab atau sub-materi pertama Anda.</p>
                        <a href="{{ route('admin.learning.materials.sections.create', $material) }}" class="mt-8 inline-flex px-8 h-12 bg-indigo-600 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all items-center gap-3">
                            <i class="fas fa-plus"></i> Tambah Bab Sekarang
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('sections-list');
        if (el) {
            Sortable.create(el, {
                animation: 150,
                ghostClass: 'bg-indigo-50',
                onEnd: function() {
                    let sectionIds = [];
                    document.querySelectorAll('#sections-list > div').forEach(div => {
                        sectionIds.push(div.getAttribute('data-id'));
                    });

                    document.getElementById('status-reorder').innerText = 'Menyimpan Urutan...';

                    fetch("{{ route('admin.learning.sections.reorder', $material) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ sections: sectionIds })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('status-reorder').innerText = 'Urutan Disimpan!';
                            setTimeout(() => {
                                document.getElementById('status-reorder').innerText = 'Klik & Tahan Untuk Mengurutkan';
                            }, 2000);
                        }
                    });
                }
            });
        }
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Bab?',
            text: "Bab ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'YA, HAPUS',
            cancelButtonText: 'BATAL',
            customClass: {
                popup: 'rounded-[2.5rem] p-8'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteForm' + id).submit();
            }
        })
    }
</script>
@endpush
