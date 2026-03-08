@extends('layouts.app')

@section('title', 'Achievement Manager — Gamification Center')
@section('page-title', 'Achievement Manager')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 animate-fadeIn pb-12">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-purple-500/20 will-change-transform"
                 style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
                <i class="fas fa-medal text-2xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black tracking-tight" style="color: var(--brand-text);">Achievement Manager</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kelola pencapaian atau buat tantangan baru untuk siswa</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.gamification.settings') }}"
               class="flex items-center gap-2 px-5 py-3 bg-white border border-gray-100 shadow-sm rounded-xl text-[10px] font-black uppercase tracking-widest text-gray-600 hover:text-purple-600 hover:border-purple-100 transition-all">
                <i class="fas fa-sliders-h"></i> Global Settings
            </a>
            <a href="{{ route('admin.gamification.achievements.create') }}"
               class="flex items-center gap-2 px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-white shadow-lg hover:scale-105 transition-all duration-300 will-change-transform"
               style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
                <i class="fas fa-plus"></i> Tambah Pencapaian Baru
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
           class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 bg-purple-600 text-white shadow-lg shadow-purple-200">
            <i class="fas fa-medal text-sm"></i> Achievement Manager
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-sm font-bold">
        <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Achievement Grid (The Golden Ten) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($achievements as $achievement)
        @php
            $active = $achievement->is_active;
            $iconHex = $achievement->color ?? '#6366f1';
        @endphp
        <div class="group relative bg-white rounded-[2rem] border shadow-sm overflow-hidden transition-all duration-500 hover:shadow-2xl hover:-translate-y-1 hover:shadow-purple-500/10 will-change-transform
                    {{ $active ? 'border-gray-100' : 'border-gray-200 opacity-60' }}">

            {{-- Status ribbon --}}
            @if(!$active)
            <div class="absolute top-3 right-3 z-10">
                <span class="px-2 py-0.5 bg-rose-100 text-rose-600 text-[8px] font-black uppercase tracking-widest rounded-md">Non-Aktif</span>
            </div>
            @else
            <div class="absolute top-3 right-3 z-10">
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-600 text-[8px] font-black uppercase tracking-widest rounded-md">Aktif</span>
            </div>
            @endif

            {{-- Icon header --}}
            <div class="relative h-28 flex items-center justify-center overflow-hidden"
                 style="background: linear-gradient(135deg, {{ $iconHex }}22, {{ $iconHex }}11);">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500"
                     style="background: radial-gradient(circle at 50% 50%, {{ $iconHex }}33 0%, transparent 70%);"></div>
                @if($achievement->icon_url)
                    <img src="{{ $achievement->icon_url }}" alt="{{ $achievement->display_title }}"
                         class="w-16 h-16 object-contain drop-shadow will-change-transform group-hover:scale-110 transition-transform duration-500">
                @else
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-white text-3xl shadow-lg will-change-transform group-hover:scale-110 transition-transform duration-500 group-hover:rotate-6"
                         style="background: linear-gradient(135deg, {{ $iconHex }}, {{ $iconHex }}cc); box-shadow: 0 8px 24px {{ $iconHex }}40;">
                        <i class="{{ $achievement->icon ?? 'fas fa-trophy' }}"></i>
                    </div>
                @endif
            </div>

            {{-- Content --}}
            <div class="p-5 space-y-2">
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] mb-0.5">{{ $achievement->criteria_type ?? '—' }}</p>
                    <h3 class="text-sm font-black text-gray-900 tracking-tight leading-tight">{{ $achievement->display_title }}</h3>
                </div>
                <p class="text-[10px] text-gray-500 leading-relaxed line-clamp-2">{{ $achievement->description }}</p>

                {{-- Criteria badge --}}
                @if($achievement->criteria_value)
                <div class="flex items-center gap-1.5 pt-1">
                    <span class="text-[8px] font-black uppercase tracking-widest text-white px-2 py-0.5 rounded-md"
                          style="background-color: {{ $iconHex }};">
                        Nilai: {{ $achievement->criteria_value }}
                    </span>
                </div>
                @endif

                {{-- Lore text --}}
                @if($achievement->lore_text)
                <p class="text-[9px] italic text-gray-400 leading-relaxed border-t border-gray-50 pt-2 line-clamp-2">
                    "{{ $achievement->lore_text }}"
                </p>
                @endif
            </div>

            {{-- Actions --}}
            <div class="px-5 pb-5 flex gap-2">
                <a href="{{ route('admin.gamification.achievements.edit', $achievement) }}"
                   class="flex-[3] flex items-center justify-center gap-2 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all duration-300 hover:scale-[1.03] hover:shadow-lg will-change-transform"
                   style="background: linear-gradient(135deg, {{ $iconHex }}22, {{ $iconHex }}11); color: {{ $iconHex }}; border: 1px solid {{ $iconHex }}33; hover:background-color: {{ $iconHex }};">
                    <i class="fas fa-pen text-[10px]"></i> Edit
                </a>
                
                <div x-data="{ showDeleteModal: false }" class="flex-1">
                    <button type="button" @click="showDeleteModal = true"
                            class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all duration-300 bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-500 hover:text-white hover:scale-[1.05] hover:shadow-lg hover:shadow-rose-500/20 will-change-transform">
                        <i class="fas fa-trash"></i>
                    </button>

                    {{-- Delete Confirmation Modal --}}
                    <template x-teleport="body">
                        <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                {{-- Backdrop --}}
                                <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false" aria-hidden="true"></div>

                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                {{-- Modal Panel --}}
                                <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                     class="relative inline-block align-bottom bg-white rounded-[2rem] px-4 pt-5 pb-4 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full sm:p-8 border border-rose-100">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full bg-rose-100 sm:mx-0 sm:h-16 sm:w-16 shadow-inner">
                                            <i class="fas fa-exclamation-triangle text-2xl text-rose-600"></i>
                                        </div>
                                        <div class="mt-4 text-center sm:mt-0 sm:ml-6 sm:text-left">
                                            <h3 class="text-xl leading-6 font-black text-gray-900 tracking-tight" id="modal-title">Hapus Pencapaian?</h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500 font-medium">Anda yakin ingin menghapus <strong>{{ $achievement->display_title }}</strong> secara permanen? Data pemain yang sudah memiliki pencapaian ini akan ditarik. Tindakan ini tidak dapat dibatalkan.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-8 sm:flex sm:flex-row-reverse gap-3">
                                        <form action="{{ route('admin.gamification.achievements.destroy', $achievement) }}" method="POST" class="inline-block w-full sm:w-auto">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-lg shadow-rose-500/20 px-8 py-3.5 bg-gradient-to-r from-rose-500 to-rose-600 text-[11px] font-black text-white uppercase tracking-widest hover:brightness-110 focus:outline-none focus:ring-4 focus:ring-rose-500/30 transition-all will-change-transform hover:scale-105 sm:w-auto sm:text-sm">
                                                Ya, Hapus Permanen
                                            </button>
                                        </form>
                                        <button type="button" @click="showDeleteModal = false" class="mt-3 w-full inline-flex justify-center rounded-2xl border border-gray-200 px-8 py-3.5 bg-white text-[11px] font-black text-gray-600 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-100 transition-all hover:border-gray-300 sm:mt-0 sm:w-auto sm:text-sm">
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
        @endforeach
    </div>

    {{-- Legend --}}
    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">📖 Peta Kriteria Achievement</p>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-center">
            @foreach([
                ['exam_count','#f97316','Jumlah Ujian'],
                ['submission_hour','#1e293b','Jam Submit'],
                ['completion_time_pct','#06b6d4','% Waktu Selesai'],
                ['consecutive_pass','#6366f1','Lulus Berturut'],
                ['avg_score','#dc2626','Rata-rata Nilai'],
            ] as [$type, $color, $label])
            <div class="flex flex-col items-center gap-1.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: {{ $color }}22;">
                    <span class="text-[8px] font-black" style="color: {{ $color }};">{{ strtoupper(substr($type, 0, 3)) }}</span>
                </div>
                <p class="text-[9px] font-bold text-gray-500">{{ $label }}</p>
                <p class="text-[8px] text-gray-300 font-mono">{{ $type }}</p>
            </div>
            @endforeach
        </div>
    </div>

</div>

<style>
.animate-fadeIn { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection
