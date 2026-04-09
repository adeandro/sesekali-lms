@extends('layouts.app')

@section('title', 'Manajemen Materi - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Manajemen Materi')

@section('content')
<div class="space-y-8 animate-fadeIn pb-12">
    <!-- Header & Quick Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                <i class="fas fa-book-open text-xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">E-Learning Materi</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Kelola bahan ajar, modul, dan video pembelajaran per mata pelajaran</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.learning.materials.create') }}" class="group h-14 px-8 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center gap-3">
                <i class="fas fa-plus-circle text-xs group-hover:rotate-90 transition-transform duration-500"></i> Tambah Materi Baru
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-gray-900">{{ $materials->total() }}</h3>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Materi</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-gray-900">{{ $materials->where('is_published', true)->count() }}</h3>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Dipublikasikan</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-gray-900">{{ $materials->where('is_published', false)->count() }}</h3>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Draft / Review</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8">
        <form action="{{ route('admin.learning.materials.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none group-focus-within:text-indigo-500 transition-colors">
                    <i class="fas fa-search text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul materi..." 
                    class="block w-full h-14 pl-12 pr-6 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 text-xs font-bold transition-all">
            </div>
            
            <div class="relative">
                <select name="subject_id" class="block w-full h-14 pl-6 pr-12 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 text-[10px] font-black uppercase tracking-widest appearance-none cursor-pointer">
                    <option value="">Semua Mata Pelajaran</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </div>
            </div>

            <button type="submit" class="h-14 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center justify-center gap-3">
                <i class="fas fa-filter text-[10px]"></i> Terapkan Filter
            </button>
        </form>
    </div>

    <!-- Materials List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($materials as $material)
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 flex flex-col">
                <div class="relative h-48 overflow-hidden bg-gray-100">
                    @if($material->cover_image)
                        <img src="{{ asset('storage/' . $material->cover_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-indigo-50 text-indigo-200">
                            <i class="fas fa-image text-5xl"></i>
                        </div>
                    @endif
                    <div class="absolute top-4 left-4">
                        <span class="px-4 py-2 bg-white/90 backdrop-blur-md text-gray-900 text-[10px] font-black uppercase rounded-xl shadow-sm border border-white/20">
                            {{ $material->subject->name }}
                        </span>
                    </div>
                    <div class="absolute top-4 right-4">
                        <span class="px-4 py-2 {{ $material->is_published ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white' }} text-[9px] font-black uppercase rounded-xl shadow-lg border border-white/20">
                            {{ $material->is_published ? 'AKTIF' : 'DRAFT' }}
                        </span>
                    </div>
                </div>
                <div class="p-8 flex-1 flex flex-col">
                    <h3 class="text-lg font-black text-gray-900 group-hover:text-indigo-600 transition-colors mb-2 line-clamp-2 uppercase leading-tight">{{ $material->title }}</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-6 italic">Oleh: {{ $material->creator->full_name }}</p>
                    
                    <div class="mt-auto pt-6 border-t border-gray-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                                <i class="fas fa-list-ul text-[10px]"></i>
                            </div>
                            <span class="text-[10px] font-black text-gray-900 uppercase">{{ $material->sections_count ?? $material->sections()->count() }} Bab</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.learning.materials.edit', $material) }}" class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all shadow-sm">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            <a href="{{ route('admin.learning.materials.show', $material) }}" class="px-6 h-10 rounded-xl bg-indigo-600 text-white text-[9px] font-black uppercase tracking-widest flex items-center justify-center hover:bg-indigo-700 transition-all shadow-md shadow-indigo-100">
                                Kelola Bab
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-empty-state 
                    icon="fas fa-book-reader"
                    title="Materi Belum Tersedia"
                    description="Anda belum memiliki materi pembelajaran yang aktif. Mulai buat materi pertama Anda untuk membantu siswa belajar mandiri."
                    actionText="Buat Materi Baru"
                    actionUrl="{{ route('admin.learning.materials.create') }}"
                />
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $materials->links() }}
    </div>
</div>
@endsection
