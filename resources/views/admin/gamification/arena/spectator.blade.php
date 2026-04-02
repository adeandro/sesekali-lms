@extends('layouts.blank')

@section('title', 'Live Track — ' . $room->name)

@push('head')
<style>
    .fleet-track { background: linear-gradient(90deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.06) 100%); }
    .fleet-ship { transition: left 1.5s linear; }
    .hp-bar { transition: width 0.5s ease; }
    @keyframes pulse-red { 0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,0.4)} 50%{box-shadow:0 0 0 12px rgba(239,68,68,0)} }
    .sudden-death { animation: pulse-red 1s infinite; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-950 via-slate-900 to-gray-950 flex flex-col p-4 gap-4"
     x-data="spectator()" x-init="init()">

    {{-- Top Bar --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-indigo-400">Live Race Track</p>
            <h1 class="text-xl font-black text-white tracking-tight">{{ $room->name }}</h1>
        </div>
        <div class="flex gap-3 items-center">
            <div class="text-right">
                <p class="text-[10px] text-gray-500 uppercase tracking-widest">Sisa Waktu</p>
                <p class="text-3xl font-black font-mono" :class="isSuddenDeath ? 'text-red-400 sudden-death' : 'text-white'" x-text="formatTime(remainingSeconds)">--:--</p>
            </div>
            <div x-show="isSuddenDeath" x-cloak
                 class="px-3 py-1 bg-red-500/20 border border-red-500/50 rounded-full text-red-400 text-[10px] font-black uppercase tracking-widest animate-pulse">
                ⚡ SUDDEN DEATH
            </div>
            @if($room->status === 'ongoing')
            <form action="{{ route('admin.gamification.arena.finish', $room) }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Akhiri battle sekarang?')"
                        class="px-4 py-2 bg-gray-800 hover:bg-red-900/50 text-gray-400 hover:text-red-400 rounded-xl text-xs font-black border border-gray-700 hover:border-red-500/50 transition">
                    <i class="fas fa-stop mr-1"></i> Akhiri
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Race Tracks --}}
    <div class="flex-1 space-y-3 overflow-y-auto">
        @if($room->mode === 'class')
        {{-- ⚓ Fleet Mode Tracks --}}
        <template x-for="(fleet, classId) in fleets" :key="classId">
            <div class="fleet-track rounded-[1.5rem] border border-white/10 p-4 relative overflow-hidden">
                {{-- Track line --}}
                <div class="absolute inset-x-4 bottom-0 h-0.5 bg-white/5"></div>

                {{-- Ship position --}}
                <div class="relative h-16 mb-3">
                    {{-- Track background --}}
                    <div class="absolute inset-0 rounded-xl bg-white/3 border border-white/5">
                        {{-- Finish line --}}
                        <div class="absolute right-3 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-white/30 to-transparent"></div>
                        <p class="absolute right-5 top-1/2 -translate-y-1/2 text-[9px] text-white/30 font-black uppercase tracking-widest rotate-90 origin-center">FINISH</p>
                    </div>
                    {{-- Fleet ship icon --}}
                    <div class="absolute top-1/2 -translate-y-1/2 transition-all duration-[1500ms] linear fleet-ship flex flex-col items-center gap-1"
                         :style="'left: calc(' + Math.min(fleet.progress, 95) + '% - 2rem)'">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/40">
                            <i class="fas fa-ship text-white text-sm"></i>
                        </div>
                        <div class="text-[9px] font-black text-white/80 whitespace-nowrap bg-black/40 px-2 py-0.5 rounded-full backdrop-blur-sm"
                             x-text="fleet.progress.toFixed(1) + '%'"></div>
                    </div>
                </div>

                {{-- Fleet Info --}}
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-black text-white" x-text="classId"></p>
                        <p class="text-[10px] text-gray-400 mt-0.5">
                            Members: <span class="text-emerald-400 font-black" x-text="fleet.active"></span> Active
                            <span class="text-red-400 mx-1">|</span>
                            <span class="text-red-400 font-black" x-text="fleet.fallen"></span> Fallen
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-black text-white" x-text="fleet.progress.toFixed(1) + '%'"></p>
                        <p class="text-[9px] text-gray-500 uppercase tracking-widest">Fleet Progress</p>
                    </div>
                </div>
            </div>
        </template>

        @else
        {{-- Individual / Group Tracks --}}
        {{-- TOP 5 AKTIF --}}
        <div class="space-y-3">
            <p class="text-[10px] font-black uppercase tracking-widest text-amber-400 mb-3">
                <i class="fas fa-crown mr-1"></i> Top 5
            </p>
            <template x-for="(p, index) in top5" :key="p.id">
                <div class="bg-white/5 backdrop-blur rounded-2xl border border-white/10 p-4 flex items-center gap-4">
                    {{-- Rank badge --}}
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-sm shrink-0"
                         :class="{
                           'bg-amber-500 text-white': index === 0,
                           'bg-slate-400 text-white': index === 1,
                           'bg-orange-600 text-white': index === 2,
                           'bg-white/10 text-gray-400': index > 2
                         }"
                         x-text="index + 1">
                    </div>

                    {{-- Avatar --}}
                    <img :src="p.avatar_url" class="w-10 h-10 rounded-full object-cover border-2 border-indigo-500/30 shrink-0">

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-black text-white truncate" x-text="p.name"></p>
                        <p class="text-[10px] text-gray-400" x-text="p.class_id"></p>
                    </div>

                    {{-- Stats --}}
                    <div class="text-right shrink-0">
                        <p class="text-sm font-black text-emerald-400" x-text="p.correct + ' ✓'"></p>
                        <p class="text-[10px] font-bold"
                           :class="p.hp > 50 ? 'text-emerald-400' : (p.hp > 20 ? 'text-amber-400' : 'text-red-400')"
                           x-text="p.hp + ' HP'"></p>
                    </div>

                    {{-- HP Bar --}}
                    <div class="w-20 bg-white/10 rounded-full h-1.5 shrink-0">
                        <div class="h-1.5 rounded-full transition-all duration-500"
                             :class="p.hp > 50 ? 'bg-emerald-400' : (p.hp > 20 ? 'bg-amber-400' : 'bg-red-400')"
                             :style="'width:' + p.hp + '%'">
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- DISKUALIFIKASI --}}
        <div class="mt-6" x-show="disqualified.length > 0">
            <p class="text-[10px] font-black uppercase tracking-widest text-red-400 mb-3">
                <i class="fas fa-skull mr-1"></i> Gugur (<span x-text="disqualified.length"></span>)
            </p>
            <div class="space-y-2">
                <template x-for="p in disqualified" :key="p.id">
                    <div class="bg-red-500/5 border border-red-500/20 rounded-xl px-4 py-2 flex items-center gap-3 opacity-60">
                        <i class="fas fa-skull text-red-400 text-xs"></i>
                        <span class="text-sm text-gray-400 flex-1" x-text="p.name"></span>
                        <span class="text-xs text-gray-600" x-text="p.correct + ' benar'"></span>
                        <span class="text-[10px] text-red-400 font-mono" x-text="p.disqualified_at"></span>
                    </div>
                </template>
            </div>
        </div>
        @endif
    </div>

    {{-- Status Footer --}}
    <div class="flex items-center justify-between text-[10px] text-gray-600">
        <span x-text="'Last updated: ' + lastUpdated"></span>
        <span>Room: <span class="text-gray-400 font-mono font-bold">{{ $room->code }}</span></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
function spectator() {
    return {
        top5: [],
        disqualified: [],
        fleets: {},
        remainingSeconds: {{ $room->remainingSeconds() }},
        isSuddenDeath: {{ $room->isSuddenDeath() ? 'true' : 'false' }},
        lastUpdated: '--',
        timer: null,
        pollTimer: null,

        init() {
            this.poll();
            this.pollTimer = setInterval(() => this.poll(), 1000);
            this.timer = setInterval(() => {
                if (this.remainingSeconds > 0) this.remainingSeconds--;
            }, 1000);
        },

        async poll() {
            try {
                const res = await fetch('{{ route('admin.gamification.arena.spectator.data', $room) }}');
                const data = await res.json();
                this.top5         = data.top5 || [];
                this.disqualified = data.disqualified || [];
                this.fleets           = {};
                if (data.fleet) {
                    data.fleet.forEach(f => this.fleets[f.class_id] = f);
                }
                this.remainingSeconds = data.remaining_seconds;
                this.isSuddenDeath    = data.is_sudden_death;
                this.lastUpdated      = new Date().toLocaleTimeString('id-ID');

                if (data.status === 'finished') {
                    clearInterval(this.pollTimer);
                    clearInterval(this.timer);
                    window.location.href = '{{ route('admin.gamification.arena.podium', $room) }}';
                }
            } catch(e) { /* network blip */ }
        },

        formatTime(s) {
            const m = Math.floor(s / 60);
            const sec = s % 60;
            return String(m).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
        }
    }
}
</script>
@endpush
