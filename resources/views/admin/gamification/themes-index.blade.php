@extends('layouts.app')

@section('title', 'Theme Manager — Gamification Center')
@section('page-title', 'Theme Manager')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 animate-fadeIn pb-12">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/20 will-change-transform"
                 style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                <i class="fas fa-palette text-2xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black tracking-tight" style="color: var(--brand-text);">Theme Manager</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Buat dan kelola tema visual untuk dashboard siswa</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.gamification.settings') }}"
               class="flex items-center gap-2 px-5 py-3 bg-white border border-gray-100 shadow-sm rounded-xl text-[10px] font-black uppercase tracking-widest text-gray-600 hover:text-indigo-600 hover:border-indigo-100 transition-all">
                <i class="fas fa-sliders-h"></i> Global Settings
            </a>
            <a href="{{ route('admin.gamification.themes.create') }}"
               class="flex items-center gap-2 px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-white shadow-lg hover:scale-105 transition-all duration-300 will-change-transform"
               style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                <i class="fas fa-plus"></i> Tambah Tema Baru
            </a>
        </div>
    </div>

    {{-- Sub-nav tabs --}}
    <div class="flex gap-2 bg-white rounded-2xl p-1.5 shadow-sm border border-gray-100">
        <a href="{{ route('admin.gamification.settings') }}"
           class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 text-gray-500 hover:bg-gray-50">
            <i class="fas fa-sliders-h text-sm"></i> Global Settings
        </a>
        <a href="{{ route('admin.gamification.achievements') }}"
           class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 text-gray-500 hover:bg-gray-50">
            <i class="fas fa-medal text-sm"></i> Achievement Manager
        </a>
        <a href="{{ route('admin.gamification.themes') }}"
           class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 bg-indigo-600 text-white shadow-lg shadow-indigo-200">
            <i class="fas fa-palette text-sm"></i> Theme Manager
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-sm font-bold animate-slideDown">
        <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Theme Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($themes as $theme)
        <div class="group relative bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden transition-all duration-500 hover:shadow-2xl hover:-translate-y-1 will-change-transform">
            
            {{-- Preview Area --}}
            <div class="h-32 p-6 flex flex-col justify-end relative overflow-hidden" style="background-color: {{ $theme->bg_color }};">
                {{-- Decorative Glow --}}
                <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full blur-3xl opacity-50" style="background-color: {{ $theme->primary_color }};"></div>
                
                <div class="relative z-10 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl shadow-lg flex items-center justify-center text-white" 
                         style="background: linear-gradient(135deg, {{ $theme->primary_color }}, {{ $theme->secondary_color }});">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-lg tracking-tight" style="color: {{ $theme->text_color }};">{{ $theme->name }}</h3>
                        <p class="text-[8px] font-bold uppercase tracking-widest opacity-60" style="color: {{ $theme->text_color }};">Slug: {{ $theme->slug }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-4">
                {{-- Color Swatches --}}
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full border border-gray-100 shadow-inner" style="background-color: {{ $theme->primary_color }};" title="Primary"></div>
                    <div class="w-6 h-6 rounded-full border border-gray-100 shadow-inner" style="background-color: {{ $theme->secondary_color }};" title="Secondary"></div>
                    <div class="w-6 h-6 rounded-full border border-gray-100 shadow-inner" style="background-color: {{ $theme->bg_color }};" title="Background"></div>
                    <div class="w-6 h-6 rounded-full border border-gray-100 shadow-inner" style="background-color: {{ $theme->text_color }};" title="Text"></div>
                </div>

                {{-- Badges --}}
                <div class="flex flex-wrap gap-2">
                    @if($theme->is_unlocked_by_default)
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-widest rounded-lg border border-emerald-100">
                            <i class="fas fa-unlock mr-1"></i> Gratis (Default)
                        </span>
                    @else
                        <span class="px-2.5 py-1 bg-amber-50 text-amber-600 text-[9px] font-black uppercase tracking-widest rounded-lg border border-amber-100">
                            <i class="fas fa-lock mr-1"></i> Perlu Unlock
                        </span>
                    @endif

                    @if($theme->is_active)
                        <span class="px-2.5 py-1 bg-blue-50 text-blue-600 text-[9px] font-black uppercase tracking-widest rounded-lg border border-blue-100">
                            <i class="fas fa-check-circle mr-1"></i> Aktif
                        </span>
                    @else
                        <span class="px-2.5 py-1 bg-gray-100 text-gray-500 text-[9px] font-black uppercase tracking-widest rounded-lg border border-gray-200">
                            <i class="fas fa-times-circle mr-1"></i> Nonaktif
                        </span>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 pt-2">
                    <a href="{{ route('admin.gamification.themes.edit', $theme) }}"
                       class="flex-1 flex items-center justify-center gap-2 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest bg-gray-50 text-gray-600 hover:bg-indigo-600 hover:text-white transition-all duration-300">
                        <i class="fas fa-pen"></i> Edit Detail
                    </a>
                    
                    <div x-data="{ showDeleteModal: false }">
                        <button type="button" @click="showDeleteModal = true"
                                class="px-4 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all duration-300">
                            <i class="fas fa-trash"></i>
                        </button>

                        {{-- Delete Confirmation Modal --}}
                        <template x-teleport="body">
                            <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                                         class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false"></div>

                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                                    <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                         class="relative inline-block align-bottom bg-white rounded-[2rem] px-4 pt-5 pb-4 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full sm:p-8 border border-rose-100">
                                        <div class="sm:flex sm:items-start text-center sm:text-left">
                                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full bg-rose-100 sm:mx-0">
                                                <i class="fas fa-exclamation-triangle text-2xl text-rose-600"></i>
                                            </div>
                                            <div class="mt-4 sm:mt-0 sm:ml-6">
                                                <h3 class="text-xl font-black text-gray-900 tracking-tight">Hapus Tema?</h3>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-500">Anda yakin ingin menghapus tema <strong>{{ $theme->name }}</strong>? User yang menggunakan tema ini akan dikembalikan ke tema default Indigo.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-8 flex flex-col sm:flex-row-reverse gap-3">
                                            <form action="{{ route('admin.gamification.themes.destroy', $theme) }}" method="POST" class="inline-block w-full sm:w-auto">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full inline-flex justify-center rounded-2xl px-8 py-3.5 bg-rose-600 text-[11px] font-black text-white uppercase tracking-widest hover:bg-rose-700 transition-all">
                                                    Ya, Hapus
                                                </button>
                                            </form>
                                            <button type="button" @click="showDeleteModal = false" class="w-full inline-flex justify-center rounded-2xl border border-gray-200 px-8 py-3.5 bg-white text-[11px] font-black text-gray-600 uppercase tracking-widest hover:bg-gray-50 transition-all">
                                                Batal
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-20 text-center bg-white rounded-[3rem] border border-dashed border-gray-200">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-palette text-3xl text-gray-300"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 tracking-tight">Belum ada tema kustom</h3>
            <p class="text-sm text-gray-400 mt-2">Mulai buat tema pertamamu untuk memberikan variasi visual bagi siswa.</p>
            <a href="{{ route('admin.gamification.themes.create') }}" class="mt-8 inline-flex items-center gap-2 px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-indigo-200 hover:scale-105 transition-all">
                <i class="fas fa-plus"></i> Buat Tema Sekarang
            </a>
        </div>
        @endforelse
    </div>
    
    {{-- Tip Section --}}
    <div class="bg-gradient-to-br from-gray-900 to-indigo-950 rounded-[3rem] p-8 text-white relative overflow-hidden shadow-2xl">
        <div class="absolute -top-20 -right-20 w-80 h-80 bg-indigo-500 rounded-full blur-[120px] opacity-20"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-center gap-8">
            <div class="w-24 h-24 rounded-3xl bg-white/10 backdrop-blur-md flex items-center justify-center flex-shrink-0">
                <i class="fas fa-lightbulb text-4xl text-amber-400"></i>
            </div>
            <div>
                <h3 class="text-2xl font-black tracking-tight mb-2">Tips Pembuatan Tema</h3>
                <p class="text-indigo-100 text-sm opacity-80 leading-relaxed max-w-2xl">
                    Pastikan kombinasi warna **Text Color** dan **Background Color** memiliki kontras yang cukup agar mudah dibaca. 
                    Gunakan **Glow Color** sebagai aksen lembut (transparan) untuk elemen bayangan yang bercahaya. 
                    Tema kustom ini akan otomatis muncul sebagai pilihan bagi siswa yang sudah mencapai syarat tertentu.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
