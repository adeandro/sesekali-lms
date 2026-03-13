@extends('layouts.blank')

@section('title', 'Podium — ' . $room->name)

@push('head')
<style>
@keyframes riseUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
@keyframes confetti-fall { to { transform: translateY(110vh) rotate(720deg); opacity: 0; } }
.podium-1 { animation: riseUp 0.8s ease 0.2s both; }
.podium-2 { animation: riseUp 0.8s ease 0.5s both; }
.podium-3 { animation: riseUp 0.8s ease 0.8s both; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-950 via-indigo-950 to-gray-950 flex flex-col items-center justify-center p-6 relative overflow-hidden">

    {{-- Background stars --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        @for($i = 0; $i < 50; $i++)
        <div class="absolute w-1 h-1 bg-white rounded-full opacity-{{ rand(10,40) }}"
             style="left:{{ rand(0,100) }}%; top:{{ rand(0,100) }}%; animation: pulse {{ rand(2,5) }}s infinite;"></div>
        @endfor
    </div>

    {{-- Header --}}
    <div class="text-center mb-12 relative z-10">
        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-indigo-400 mb-2">{{ $room->name }}</p>
        <h1 class="text-4xl font-black text-white tracking-tight">🏆 Battle Selesai!</h1>
        <p class="text-gray-400 mt-2">{{ $room->mode === 'class' ? 'Fleet' : 'Pejuang' }} terbaik telah ditentukan.</p>
    </div>

    {{-- 3-D Podium --}}
    <div class="relative z-10 flex items-end justify-center gap-4 mb-10">

        {{-- 2nd Place --}}
        @if($winners->count() >= 2)
        <div class="podium-2 flex flex-col items-center">
            @if($room->mode === 'class')
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-slate-400 to-gray-500 flex items-center justify-center shadow-xl mb-3 ring-4 ring-slate-300/20">
                    <i class="fas fa-ship text-white text-2xl"></i>
                </div>
                <p class="text-sm font-black text-white text-center mb-2">{{ $winners[1]['class_id'] }}</p>
                <p class="text-xs text-gray-400 mb-3">{{ number_format($winners[1]['progress'], 1) }}%</p>
            @else
                <img src="{{ $winners[1]->user->photo_url }}" class="w-16 h-16 rounded-full object-cover mb-3 ring-4 ring-slate-300/30 shadow-xl">
                <p class="text-sm font-black text-white mb-2">{{ $winners[1]->user->name }}</p>
            @endif
            <div class="w-28 h-24 bg-gradient-to-b from-slate-500 to-slate-700 rounded-t-2xl flex flex-col items-center justify-center shadow-2xl border-2 border-slate-400/30">
                <span class="text-4xl font-black text-slate-200">2</span>
                <span class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Silver</span>
            </div>
        </div>
        @endif

        {{-- 1st Place --}}
        @if($winners->count() >= 1)
        <div class="podium-1 flex flex-col items-center -mt-8">
            <div class="text-4xl mb-1">👑</div>
            @if($room->mode === 'class')
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-2xl mb-3 ring-8 ring-amber-400/30 animate-pulse">
                    <i class="fas fa-ship text-white text-2xl"></i>
                </div>
                <p class="text-base font-black text-white text-center mb-2">{{ $winners[0]['class_id'] }}</p>
                <p class="text-sm text-amber-400 font-black mb-3">{{ number_format($winners[0]['progress'], 1) }}%</p>
            @else
                <img src="{{ $winners[0]->user->photo_url }}" class="w-20 h-20 rounded-full object-cover mb-3 ring-8 ring-amber-400/30 shadow-2xl">
                <p class="text-base font-black text-white mb-2">{{ $winners[0]->user->name }}</p>
            @endif
            <div class="w-32 h-32 bg-gradient-to-b from-amber-400 to-amber-600 rounded-t-2xl flex flex-col items-center justify-center shadow-2xl border-2 border-amber-300/40">
                <span class="text-5xl font-black text-amber-100">1</span>
                <span class="text-[10px] text-amber-200 uppercase tracking-widest font-bold">Champion</span>
            </div>
        </div>
        @endif

        {{-- 3rd Place --}}
        @if($winners->count() >= 3)
        <div class="podium-3 flex flex-col items-center">
            @if($room->mode === 'class')
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-orange-700 to-amber-900 flex items-center justify-center shadow-xl mb-3 ring-4 ring-orange-700/20">
                    <i class="fas fa-ship text-white text-xl"></i>
                </div>
                <p class="text-sm font-black text-white text-center mb-2">{{ $winners[2]['class_id'] }}</p>
                <p class="text-xs text-gray-400 mb-3">{{ number_format($winners[2]['progress'], 1) }}%</p>
            @else
                <img src="{{ $winners[2]->user->photo_url }}" class="w-14 h-14 rounded-full object-cover mb-3 ring-4 ring-orange-700/30 shadow-xl">
                <p class="text-sm font-black text-white mb-2">{{ $winners[2]->user->name }}</p>
            @endif
            <div class="w-24 h-16 bg-gradient-to-b from-orange-700 to-orange-900 rounded-t-2xl flex flex-col items-center justify-center shadow-2xl border-2 border-orange-600/30">
                <span class="text-3xl font-black text-orange-200">3</span>
                <span class="text-[10px] text-orange-400 uppercase tracking-widest font-bold">Bronze</span>
            </div>
        </div>
        @endif
    </div>

    {{-- Action Buttons --}}
    <div class="relative z-10 flex gap-3 flex-wrap justify-center">
        <a href="{{ route('admin.gamification.arena.debriefing', $room) }}"
           class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-black text-xs uppercase tracking-widest rounded-2xl border border-white/10 transition">
            <i class="fas fa-chart-bar mr-2"></i> Debriefing
        </a>
        <a href="{{ route('admin.gamification.arena.index') }}"
           class="px-6 py-3 bg-gradient-to-r from-red-500 to-orange-500 text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-lg hover:-translate-y-0.5 transition-all">
            <i class="fas fa-home mr-2"></i> Kembali ke Arena
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Confetti burst
    const duration = 5 * 1000;
    const end = Date.now() + duration;
    (function frame() {
        confetti({ particleCount: 3, angle: 60, spread: 55, origin: { x: 0 }, colors: ['#f59e0b','#ef4444','#6366f1'] });
        confetti({ particleCount: 3, angle: 120, spread: 55, origin: { x: 1 }, colors: ['#f59e0b','#ef4444','#10b981'] });
        if (Date.now() < end) requestAnimationFrame(frame);
    }());
});
</script>
@endpush
