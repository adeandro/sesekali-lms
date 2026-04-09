@extends('layouts.app')

@section('title', 'E-Learning - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Pusat Pembelajaran')

@section('content')
<div class="space-y-8 animate-fadeIn pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-xl shadow-indigo-100">
                <i class="fas fa-graduation-cap text-2xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">E-Learning</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Akses materi pembelajaran mandiri dari guru Anda</p>
            </div>
        </div>
    </div>

    @forelse($subjects as $subject)
        <div class="space-y-6">
            <div class="flex items-center gap-4 px-2">
                <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>
                <h3 class="text-[11px] font-black text-indigo-400 uppercase tracking-[0.3em] bg-white px-6 py-2 rounded-full border border-gray-100 shadow-sm">{{ $subject->name }}</h3>
                <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($subject->learningMaterials as $material)
                    <a href="{{ route('learning.show', $material) }}" class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 flex flex-col hover:-translate-y-2">
                        <div class="relative h-48 overflow-hidden bg-gray-50">
                            @if($material->cover_image)
                                <img src="{{ asset('storage/' . $material->cover_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-indigo-100 bg-indigo-50">
                                    <i class="fas fa-book-open text-5xl opacity-30"></i>
                                </div>
                            @endif
                            <div class="absolute bottom-4 left-4 right-4 translate-y-full group-hover:translate-y-0 transition-transform duration-500">
                                <div class="bg-white/90 backdrop-blur-md p-3 rounded-2xl border border-white/20 shadow-lg text-center">
                                    <span class="text-[9px] font-black text-indigo-600 uppercase tracking-widest">Mulai Belajar Sekarang</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-8 flex-1 flex flex-col">
                            <h4 class="text-lg font-black text-gray-900 group-hover:text-indigo-600 transition-colors uppercase leading-tight mb-4 lines-clamp-2">{{ $material->title }}</h4>
                            <div class="mt-auto flex items-center justify-between pt-6 border-t border-gray-50">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                                        <i class="fas fa-list-ul text-[10px]"></i>
                                    </span>
                                    <span class="text-[10px] font-black text-gray-900 uppercase">{{ $material->sections->count() }} Bab</span>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all transform group-hover:rotate-12">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <x-empty-state 
            icon="fas fa-school"
            title="Belum Ada Materi"
            description="Guru Anda belum mempublikasikan materi pembelajaran untuk saat ini. Silakan hubungi guru mata pelajaran Anda."
            actionText="Refresh Halaman"
            actionUrl="{{ route('learning.index') }}"
        />
    @endforelse
</div>
@endsection
