{{-- Celebration Modal Component --}}
@php
    $celebration = session('celebration');
    // If not set directly, maybe fallback to level_ups or new_achievements mapping 
    // for backward compatibility during the session lifecycle.
@endphp

@if($celebration || session('level_ups') || session('new_achievements'))
    @php
        // Standardize the payload
        $title = 'CONGRATULATIONS!';
        $subtitle = '';
        $icon = 'fas fa-star';
        $rewardText = '';
        $iconClass = 'text-yellow-400';
        $bgGradient = 'from-amber-300 to-orange-500';

        if ($celebration) {
            $title = $celebration['title'] ?? 'CONGRATULATIONS!';
            $subtitle = $celebration['subtitle'] ?? '';
            $icon = $celebration['icon'] ?? 'fas fa-trophy';
            $rewardText = $celebration['reward'] ?? '';
        } elseif (session('level_ups')) {
            $levelData = is_array(session('level_ups')) ? session('level_ups')[0] : session('level_ups');
            $title = 'LEVEL UP!';
            $subtitle = 'Kamu Mencapai Level ' . ($levelData['new'] ?? session('level_ups'));
            $icon = 'fas fa-angle-double-up';
            $rewardText = 'Terus tingkatkan belajarmu!';
            $bgGradient = 'from-indigo-400 to-purple-600';
        } elseif (session('new_achievements')) {
            $badges = session('new_achievements');
            $badge = is_array($badges) && isset($badges[0]) ? $badges[0] : (is_array($badges) ? $badges : null);
            $title = 'ACHIEVEMENT UNLOCKED!';
            $subtitle = $badge['title'] ?? 'Piala Baru';
            $icon = 'fas fa-medal';
            $rewardText = '+' . ($badge['xp_reward'] ?? 100) . ' EXP';
        }
    @endphp

    <div x-data="{ show: true }" x-show="show" x-init="
        if(typeof confetti === 'function') {
            confetti({
                particleCount: 150,
                spread: 100,
                origin: { y: 0.6 },
                colors: ['#fbbf24', '#f59e0b', '#d97706', '#4f46e5', '#ec4899']
            });
        }
    " class="fixed inset-0 z-[100] flex items-center justify-center overflow-hidden" style="display: none;">
        
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-500"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        {{-- Modal Content --}}
        <div class="relative bg-white/10 backdrop-blur-xl rounded-[2.5rem] w-full max-w-md p-1 border border-white/20 shadow-[0_0_80px_rgba(251,191,36,0.2)] will-change-transform"
             @click.away="show = false"
             x-transition:enter="ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 scale-50 translate-y-12"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-300"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4">
            
            <div class="bg-gradient-to-br from-white/90 to-white/50 rounded-[2.3rem] p-10 text-center relative overflow-hidden shadow-inner">
                {{-- Decorative Background Glow --}}
                <div class="absolute inset-x-0 -top-32 h-[200%] animate-[spin-slow_20s_linear_infinite] opacity-10 pointer-events-none"
                     style="background: repeating-conic-gradient(from 0deg, transparent 0deg 15deg, #f59e0b 15deg 30deg); clip-path: circle(50% at 50% 50%);"></div>

                {{-- Central Icon --}}
                <div class="relative w-32 h-32 mx-auto mb-6 transform hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-yellow-400 rounded-full blur-2xl opacity-40 animate-pulse"></div>
                    <div class="relative w-full h-full bg-gradient-to-br {{ $bgGradient }} rounded-full border-4 border-white/80 shadow-2xl flex items-center justify-center text-white backdrop-blur-sm">
                        <i class="{{ $icon }} text-5xl drop-shadow-lg"></i>
                        <i class="fas fa-sparkles absolute -top-2 -right-2 text-yellow-200 text-2xl animate-bounce"></i>
                        <i class="fas fa-star absolute bottom-4 -left-4 text-yellow-100 text-xl animate-bounce" style="animation-delay: 0.2s"></i>
                    </div>
                </div>

                <div class="relative z-10 space-y-3">
                    <p class="text-[11px] font-black uppercase tracking-[0.4em] text-[var(--brand-primary)]">{{ $title }}</p>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight leading-none bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600">
                        {{ $subtitle }}
                    </h2>
                    
                    @if($rewardText)
                    <div class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-[var(--brand-glow)] rounded-full border border-[var(--brand-primary)]/20">
                        <i class="fas fa-gift text-[var(--brand-primary)]"></i>
                        <span class="text-xs font-black text-[var(--brand-primary)] uppercase tracking-wider">{{ $rewardText }}</span>
                    </div>
                    @endif

                    <button @click="show = false" 
                            class="mt-8 relative inline-flex group w-full justify-center">
                        <div class="absolute transition-all duration-1000 opacity-70 -inset-px bg-gradient-to-r from-[#44BCFF] via-[#FF44EC] to-[#FF675E] rounded-xl blur-lg group-hover:opacity-100 group-hover:-inset-1 group-hover:duration-200 animate-[tilt_10s_infinite_linear]"></div>
                        <span class="relative w-full inline-flex items-center justify-center px-8 py-4 text-[11px] font-black uppercase tracking-widest text-white transition-all duration-200 bg-gray-900 rounded-xl hover:bg-black">
                            Lanjutkan <i class="fas fa-arrow-right ml-2 opacity-70"></i>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @php 
        session()->forget('celebration');
        session()->forget('level_ups');
        session()->forget('new_achievements');
        session()->forget('level_up'); // Just in case
    @endphp
@endif