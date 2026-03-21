@extends('layouts.app')

@section('title', 'Manajemen Ekstrakurikuler')

@section('content')
<div class="space-y-8" x-data="extracurricularManager()">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                <i class="fas fa-running text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight uppercase">Manajemen Ekstrakurikuler</h1>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Kelola daftar ekskul, pembina, dan anggota siswa
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            {{-- Academic Year Filter --}}
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-hover:text-emerald-500 transition-colors">
                    <i class="fas fa-calendar-alt text-[10px]"></i>
                </div>
                <input type="text" name="academic_year" 
                       value="{{ $academicYear }}" 
                       @keydown.enter="location.href = '{{ route('admin.extracurriculars.index') }}?academic_year=' + $el.value"
                       placeholder="2024/2025"
                       class="pl-10 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-emerald-500 w-36 transition-all">
            </div>

            <button @click="openAddModal = true"
                    class="flex items-center gap-3 px-6 py-3 rounded-2xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 hover:scale-105 transition-all duration-300">
                <i class="fas fa-plus"></i>
                Tambah Ekskul
            </button>
        </div>
    </div>

    {{-- Extracurricular Grid --}}
    <div id="extracurricular-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($extracurriculars as $extracurricular)
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-emerald-500/5 transition-all duration-500 group relative"
                 data-id="{{ $extracurricular->id }}">
                
                {{-- Sort Handle --}}
                <div class="sort-handle absolute top-6 right-6 w-8 h-8 rounded-xl bg-gray-50 text-gray-300 flex items-center justify-center cursor-move opacity-0 group-hover:opacity-100 transition-opacity">
                    <i class="fas fa-grip-vertical text-xs"></i>
                </div>

                <div class="p-8">
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex-1">
                            <h3 class="text-sm font-black text-gray-900 tracking-tight flex items-center gap-2">
                                {{ $extracurricular->name }}
                                @if($extracurricular->is_active)
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-red-400"></span>
                                @endif
                            </h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">
                                {{ $extracurricular->is_active ? 'Aktif' : 'Non-aktif' }}
                            </p>
                        </div>
                    </div>

                    <p class="text-[11px] text-gray-500 leading-relaxed line-clamp-2 h-8 mb-6 font-medium">
                        {{ $extracurricular->description ?? 'Tidak ada deskripsi.' }}
                    </p>

                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="bg-gray-50 rounded-2xl p-4 text-center">
                            <div class="text-lg font-black text-gray-900 tracking-tighter">{{ $extracurricular->coaches_count }}</div>
                            <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Guru Pembina</div>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-4 text-center">
                            <div class="text-lg font-black text-gray-900 tracking-tighter">{{ $extracurricular->members_count }}</div>
                            <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Anggota Siswa</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.extracurriculars.show', $extracurricular) }}?academic_year={{ $academicYear }}"
                           class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl bg-gray-900 text-white text-[9px] font-black uppercase tracking-widest hover:bg-black transition-all">
                            <i class="fas fa-user-cog"></i>
                            Kelola
                        </a>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.extracurriculars.sessions.index', [$extracurricular->id, 'academic_year' => $academicYear]) }}"
                               class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition-all border border-indigo-100"
                               title="Jurnal & Presensi">
                                <i class="fas fa-clipboard-list text-[10px]"></i>
                            </a>
                            <a href="{{ route('admin.extracurriculars.edit', $extracurricular) }}"
                               class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-all border border-emerald-100">
                                <i class="fas fa-edit text-[10px]"></i>
                            </a>
                            <button @click="toggleActive({{ $extracurricular->id }}, {{ $extracurricular->is_active ? 'false' : 'true' }})"
                                    class="w-10 h-10 rounded-xl {{ $extracurricular->is_active ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-green-50 text-green-600 border-green-100' }} flex items-center justify-center hover:opacity-80 transition-all border">
                                <i class="fas {{ $extracurricular->is_active ? 'fa-pause' : 'fa-play' }} text-[10px]"></i>
                            </button>
                            <button @click="deleteEkskul({{ $extracurricular->id }}, '{{ $extracurricular->name }}')"
                                    class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-all border border-red-100">
                                <i class="fas fa-trash-alt text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white p-20 rounded-[2.5rem] border border-dashed border-gray-200 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto text-gray-300 mb-6">
                    <i class="fas fa-running text-3xl"></i>
                </div>
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">Belum Ada Ekstrakurikuler</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-2 max-w-xs mx-auto leading-relaxed">
                    Mulai dengan menambahkan daftar kegiatan ekskul yang tersedia di sekolah Anda.
                </p>
                <button @click="openAddModal = true"
                        class="mt-8 inline-flex items-center gap-3 px-8 py-3.5 rounded-2xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 hover:scale-105 transition-all duration-300">
                    <i class="fas fa-plus"></i>
                    Tambah Ekskul Pertama
                </button>
            </div>
        @endforelse
    </div>

    {{-- Modal: Tambah Ekskul --}}
    <div x-show="openAddModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         style="display: none;">
        
        <div class="bg-white rounded-[2.5rem] w-full max-w-md overflow-hidden shadow-2xl border border-gray-100"
             @click.away="openAddModal = false">
            <div class="p-8 border-b border-gray-50 bg-gray-50/50">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Tambah Ekstrakurikuler</h3>
            </div>
            
            <form action="{{ route('admin.extracurriculars.store') }}" method="POST" class="p-8 space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nama Ekskul</label>
                    <input type="text" name="name" required placeholder="Contoh: Pramuka, Basket, Musik"
                           class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-500 transition-all">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Deskripsi</label>
                    <textarea name="description" rows="3" placeholder="Jelaskan sedikit tentang ekskul ini..."
                              class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-500 transition-all"></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="openAddModal = false"
                            class="flex-1 py-4 rounded-2xl bg-gray-100 text-gray-500 text-[10px] font-black uppercase tracking-widest hover:bg-gray-200 transition-all">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-4 rounded-2xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 transition-all">
                        Simpan Ekskul
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SortableJS --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    function extracurricularManager() {
        return {
            openAddModal: false,

            init() {
                const el = document.getElementById('extracurricular-grid');
                if (!el) return;

                Sortable.create(el, {
                    handle: '.sort-handle',
                    animation: 150,
                    onEnd: () => {
                        const ids = Array.from(el.children)
                            .map(child => child.dataset.id)
                            .filter(id => id);
                        
                        this.reorder(ids);
                    }
                });
            },

            async reorder(ids) {
                try {
                    const response = await fetch('{{ route('admin.extracurriculars.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids })
                    });
                    
                    if (response.ok) {
                        // Toast success
                    }
                } catch (error) {
                    console.error('Reorder failed:', error);
                }
            },

            async toggleActive(id, status) {
                try {
                    const response = await fetch(`{{ url('admin/extracurriculars') }}/${id}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ is_active: status })
                    });
                    
                    if (response.ok) {
                        location.reload();
                    }
                } catch (error) {
                    console.error('Status update failed:', error);
                }
            },

            deleteEkskul(id, name) {
                Swal.fire({
                    title: 'Hapus Ekskul?',
                    text: `Anda akan menghapus "${name}". Pastikan tidak ada data guru atau siswa di dalamnya.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'rounded-[2rem]',
                        confirmButton: 'rounded-xl px-6 py-3 text-[10px] font-black uppercase tracking-widest',
                        cancelButton: 'rounded-xl px-6 py-3 text-[10px] font-black uppercase tracking-widest'
                    }
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const response = await fetch(`{{ url('admin/extracurriculars') }}/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            });
                            
                            const data = await response.json();
                            
                            if (response.ok) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: 'Ekskul berhasil dihapus.',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false,
                                    customClass: { popup: 'rounded-[2rem]' }
                                }).then(() => location.reload());
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: data.message || 'Terjadi kesalahan sistem.',
                                    icon: 'error',
                                    customClass: { popup: 'rounded-[2rem]' }
                                });
                            }
                        } catch (error) {
                            console.error('Delete failed:', error);
                        }
                    }
                });
            }
        }
    }
</script>
@endsection
