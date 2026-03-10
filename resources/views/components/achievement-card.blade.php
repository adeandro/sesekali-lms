@props([
    'achievement',
    'isUnlocked' => false,
    'isAdminMode' => false,
    'progress' => 0
])

@php
    $iconColor = $achievement->color ?: '#6366f1';
    // If it's admin mode, it shows unlocked by default to preview
    $isGrayscale = !$isUnlocked && !$isAdminMode;
    $shouldShowActions = $isAdminMode;
@endphp

<div class="group relative achievement-card rounded-[2rem] p-5 {{ $isGrayscale ? 'opacity-80 grayscale' : '' }}"
     @if(!$isAdminMode) title="{{ $achievement->description }}" @endif>
    
    <!-- Status ribbon (Admin Mode - Top Right) -->
    @if($isAdminMode)
        @if(!$achievement->is_active)
        <div class="absolute top-3 right-3 z-10">
            <span class="px-2 py-0.5 bg-rose-100 text-rose-600 text-[8px] font-black uppercase tracking-widest rounded-md border border-rose-200 shadow-sm">Non-Aktif</span>
        </div>
        @else
        <div class="absolute top-3 right-3 z-10">
            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-600 text-[8px] font-black uppercase tracking-widest rounded-md border border-emerald-200 shadow-sm">Aktif</span>
        </div>
        @endif
    @endif

    <!-- Lock Icon (Student Locked Mode - Top Right) -->
    @if($isGrayscale)
        <div class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center z-20 bg-slate-100/80 rounded-full border border-slate-200 shadow-sm backdrop-blur-sm">
            <i class="fas fa-lock text-slate-400 text-xs"></i>
        </div>
    @endif

    <!-- Minimalist Progress Ring (Top Right - Student Unlocked/Locked Mode) -->
    @if(!$isAdminMode && !$isGrayscale)
    <div class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center z-20">
        @if($progress >= 100)
            <div class="w-7 h-7 rounded-full bg-emerald-100 border border-emerald-200 flex items-center justify-center shadow-sm animate-pulse-slow">
                <i class="fas fa-check text-emerald-500 text-[8px]"></i>
            </div>
        @else
            <svg class="w-full h-full transform -rotate-90">
                <circle cx="20" cy="20" r="14" stroke="currentColor" stroke-width="2" fill="transparent" class="text-slate-200/50" />
                <circle cx="20" cy="20" r="14" stroke="var(--brand-primary)" stroke-width="2" fill="transparent" 
                        stroke-dasharray="88" stroke-dashoffset="{{ 88 - (88 * $progress / 100) }}" 
                        class="transition-all duration-1000" />
            </svg>
            <span class="absolute text-[8px] font-bold text-slate-500">{{ $progress }}%</span>
        @endif
    </div>
    @endif

    <!-- Card Content (Center Aligned) -->
    <div class="flex flex-col items-center text-center space-y-4 pt-2 relative z-10">
        <!-- Icon Area -->
        <div class="relative">
            @if($isGrayscale)
                <!-- Gray background blur for locked state -->
                <div class="absolute inset-0 bg-slate-300 opacity-20 blur-xl rounded-full"></div>
            @endif
            
            <div class="achievement-icon w-16 h-16
                 {{ !$isGrayscale ? 'group-hover:animate-float-slow group-hover:scale-110 drop-shadow-[0_0_15px_'. $iconColor . '40]' : 'opacity-40 shadow-none' }}" 
                 style="background: {{ !$isGrayscale ? 'linear-gradient(135deg, ' . $iconColor . ', ' . $iconColor . 'cc)' : 'rgba(0,0,0,0.05)' }};">
                
                @if($achievement->icon_url)
                    <img src="{{ $achievement->icon_url }}" alt="Icon" class="w-full h-full object-cover">
                @else
                    <i class="{{ $achievement->icon ?: 'fas fa-trophy' }} {{ $isGrayscale ? 'text-slate-500' : '' }}"></i>
                @endif
            </div>
        </div>

        <!-- Text Details -->
        <div class="space-y-1 w-full">
            <!-- Badge for Criteria -->
            <div class="flex justify-center mb-2">
                <span class="text-[8px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full {{ !$isGrayscale ? 'text-white' : 'text-slate-500 bg-slate-200' }}"
                      style="{{ !$isGrayscale ? 'background-color: ' . $iconColor . ';' : '' }}">
                    {{ $achievement->criteria_type === 'exam_count' ? 'Exams: ' : ($achievement->criteria_type === 'avg_score' ? 'Avg: ' : 'Syarat: ') }}{{ $achievement->criteria_value ?? 'Unlock' }}
                </span>
            </div>
            
            <h4 class="text-sm font-black tracking-tight uppercase leading-tight text-slate-900 line-clamp-1">
                {{ $achievement->display_title }}
            </h4>
            <p class="text-[11px] font-medium text-slate-500 leading-tight line-clamp-2 min-h-[1.5rem]">
                {{ $achievement->description }}
            </p>
            @if($achievement->lore_text)
            <p class="text-[9px] text-slate-400 italic mt-2 line-clamp-2 font-serif">
                "{{ $achievement->lore_text }}"
            </p>
            @endif
        </div>

        <!-- Student Progress Bar -->
        @if(!$isUnlocked && $progress > 0 && !$isAdminMode)
        <div class="w-full mt-2">
            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden shadow-inner">
                <div class="h-full rounded-full transition-all duration-1000"
                     style="width: {{ $progress }}%; background-color: var(--brand-primary)"></div>
            </div>
        </div>
        @endif
        
        <!-- Admin Mode Progress Bar Mockup -->
        @if($isAdminMode && $achievement->criteria_type && $achievement->criteria_value)
        <div class="w-full mt-2">
            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden relative shadow-inner" title="Progress Bar Preview">
                <div class="h-full rounded-full transition-all duration-1000 bg-emerald-400"
                     style="width: 50%;"></div>
            </div>
        </div>
        @endif
    </div>

    <!-- Decorative background glow -->
    <div class="absolute -bottom-10 -right-10 w-32 h-32 opacity-0 group-hover:opacity-10 blur-[80px] rounded-full transition-opacity duration-300 pointer-events-none" style="background-color: {{ $iconColor }}"></div>

    {{-- Actions for Admin Mode --}}
    @if($isAdminMode)
    <div class="pt-5 mt-4 border-t border-slate-100/50 flex gap-2 relative z-20">
         <a href="{{ route('admin.gamification.achievements.edit', $achievement) }}"
           class="flex-[3] flex items-center justify-center gap-2 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all duration-300 hover:scale-[1.03] hover:shadow-lg will-change-transform"
           style="background: linear-gradient(135deg, {{ $iconColor }}22, {{ $iconColor }}11); color: {{ $iconColor }}; border: 1px solid {{ $iconColor }}33; hover:background-color: {{ $iconColor }};">
            <i class="fas fa-pen text-[10px]"></i> Edit
        </a>
        
        <div x-data="{ showDeleteModal: false }" class="flex-1">
            <button type="button" @click="showDeleteModal = true"
                    class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all duration-300 bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-500 hover:text-white hover:scale-[1.05] hover:shadow-lg hover:shadow-rose-500/20 will-change-transform">
                <i class="fas fa-trash"></i>
            </button>

            {{-- Delete Confirmation Modal --}}
            <template x-teleport="body">
                <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        {{-- Backdrop --}}
                        <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false" aria-hidden="true"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        {{-- Modal Panel --}}
                        <div x-show="showDeleteModal" @click.away="showDeleteModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
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
                                        Ya, Hapus
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
    @endif
</div>
