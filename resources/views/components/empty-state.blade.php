@props([
    'icon' => 'fas fa-search',
    'title' => 'Data Tidak Ditemukan',
    'description' => 'Maaf, kami tidak dapat menemukan data yang Anda cari. Silakan coba kata kunci lain atau tambah data baru.',
    'actionText' => null,
    'actionUrl' => null,
    'secondaryActionText' => null,
    'secondaryActionUrl' => null,
])

<div class="flex flex-col items-center justify-center py-20 px-6 animate-fadeIn">
    <div class="relative mb-8">
        {{-- Modern Abstract SVG Illustration --}}
        <div class="w-48 h-48 bg-[var(--brand-glow)] rounded-[3rem] absolute -top-4 -left-4 animate-pulse opacity-50"></div>
        <div class="w-48 h-48 bg-white border-2 border-[var(--brand-glow)] rounded-[3rem] shadow-xl flex items-center justify-center relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-transparent to-[var(--brand-glow)] opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            <i class="{{ $icon }} text-6xl text-[var(--brand-primary)] group-hover:scale-110 transition-transform duration-500"></i>
        </div>
        
        {{-- Floating particles --}}
        <div class="absolute -top-2 -right-2 w-4 h-4 bg-amber-400 rounded-full animate-bounce"></div>
        <div class="absolute bottom-4 -right-6 w-3 h-3 bg-emerald-400 rounded-full animate-pulse"></div>
    </div>

    <div class="text-center max-w-sm space-y-4">
        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-wider">{{ $title }}</h3>
        <p class="text-sm font-bold text-gray-400 leading-relaxed italic">{{ $description }}</p>
    </div>

    @if($actionText && $actionUrl)
        <div class="mt-10 flex flex-wrap justify-center gap-4">
            <a href="{{ $actionUrl }}" class="px-8 py-4 bg-[var(--brand-primary)] text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-[var(--brand-dark)] transition-all shadow-lg shadow-[var(--brand-glow)] hover:-translate-y-1">
                <i class="fas fa-plus mr-2"></i> {{ $actionText }}
            </a>
            
            @if($secondaryActionText && $secondaryActionUrl)
                <a href="{{ $secondaryActionUrl }}" class="px-8 py-4 bg-white border-2 border-[var(--brand-glow)] text-[var(--brand-primary)] text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-[var(--brand-glow)] transition-all shadow-sm hover:-translate-y-1">
                    {{ $secondaryActionText }}
                </a>
            @endif
        </div>
    @endif
</div>
