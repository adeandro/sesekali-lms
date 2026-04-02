@extends('layouts.blank')

@section('title', 'Menunggu Battle — ' . $room->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-950 via-indigo-950 to-gray-950 flex flex-col items-center justify-center p-6"
     x-data="lobbyWatcher()" x-init="init()">

    {{-- Logo/Title --}}
    <div class="text-center mb-8">
        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-red-500 to-orange-600 flex items-center justify-center mx-auto mb-4 shadow-2xl shadow-orange-500/30 animate-pulse">
            <i class="fas fa-swords text-white text-3xl"></i>
        </div>
        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-indigo-400">Battle Arena</p>
        <h1 class="text-2xl font-black text-white tracking-tight mt-1">{{ $room->name }}</h1>
        @if($room->mode === 'class')
        <p class="text-sm text-amber-400 font-black mt-1">⚓ Fleet Mode — Kelas: {{ $participant->class_id }}</p>
        @endif
    </div>

    {{-- Waiting card --}}
    <div class="w-full max-w-sm bg-white/5 backdrop-blur rounded-[2rem] border border-white/10 p-8 text-center space-y-5">
        <div class="space-y-2">
            <div class="flex items-center justify-center gap-2">
                <div class="w-2 h-2 bg-emerald-400 rounded-full animate-ping"></div>
                <span class="text-emerald-400 text-xs font-black uppercase tracking-widest">Terhubung</span>
            </div>
            <p class="text-gray-300 text-sm">Menunggu admin memulai battle…</p>
        </div>

        {{-- Player card --}}
        <div class="bg-white/5 rounded-2xl border border-white/10 p-4 flex items-center gap-3">
            <img src="{{ Auth::user()->photo_url }}" class="w-12 h-12 rounded-full object-cover border-2 border-indigo-500/30">
            <div class="text-left">
                <p class="text-sm font-black text-white">{{ Auth::user()->name }}</p>
                <p class="text-[10px] text-gray-400">{{ $participant->class_id }}</p>
                <p class="text-[10px] text-emerald-400 font-bold mt-0.5">❤️ {{ $participant->hp }} HP</p>
            </div>
        </div>

        {{-- HP Rules recap --}}
        <div class="text-left space-y-2">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-500">Aturan Battle</p>
            <div class="space-y-1.5">
                @foreach([
                    ['fa-times-circle text-red-400', "-{$room->penalty_hp} HP per jawaban salah"],
                    ['fa-bolt text-orange-400', "-" . ($room->penalty_hp * 2) . " HP saat Sudden Death (2 menit terakhir)"],
                    ['fa-eye-slash text-amber-400', "-10 HP jika keluar tab"],
                    ['fa-skull text-red-500', "HP = 0 → DISKUALIFIKASI"],
                ] as [$icon, $desc])
                <div class="flex items-start gap-2">
                    <i class="fas {{ $icon }} text-xs mt-0.5 shrink-0"></i>
                    <p class="text-[11px] text-gray-400">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Prize Pool --}}
        @php
            $rewards = $room->settings['rewards'] ?? [];
            $r1 = $rewards['rank_1'] ?? ['exp' => 500];
            $r2 = $rewards['rank_2'] ?? ['exp' => 300];
            $r3 = $rewards['rank_3'] ?? ['exp' => 200];
        @endphp
        <div class="text-left space-y-2 mt-4 pt-4 border-t border-white/10">
            <p class="text-[10px] font-black uppercase tracking-widest text-amber-500"><i class="fas fa-gift mr-1"></i> Prize Pool</p>
            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-[11px] bg-white/5 p-2 rounded-lg border border-amber-500/20">
                    <span class="text-amber-400 font-bold"><i class="fas fa-crown w-4"></i> Juara 1</span>
                    <span class="text-gray-300">{{ $r1['exp'] }} EXP</span>
                </div>
                <div class="flex items-center justify-between text-[11px] bg-white/5 p-2 rounded-lg border border-slate-300/20">
                    <span class="text-slate-300 font-bold"><i class="fas fa-medal w-4"></i> Juara 2</span>
                    <span class="text-gray-300">{{ $r2['exp'] }} EXP</span>
                </div>
                <div class="flex items-center justify-between text-[11px] bg-white/5 p-2 rounded-lg border border-orange-700/30">
                    <span class="text-orange-400 font-bold"><i class="fas fa-award w-4"></i> Juara 3</span>
                    <span class="text-gray-300">{{ $r3['exp'] }} EXP</span>
                </div>
            </div>
        </div>

        <div class="text-[10px] text-gray-600 font-mono mt-4" x-text="'Checking status...'"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function lobbyWatcher() {
    return {
        pollInterval: 3000,
        maxInterval: 10000,
        errorCount: 0,

        init() {
            this.schedule();
        },

        schedule() {
            setTimeout(() => this.checkStatus(),
                this.pollInterval);
        },

        async checkStatus() {
            try {
                const res = await fetch(
                    '{{ route('student.arena.lobby.status', $room) }}',
                    {
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

                if (!res.ok) throw new Error('Server error');

                const data = await res.json();

                this.errorCount = 0;
                this.pollInterval = 3000;

                if (data.status === 'ongoing'
                    && data.participant_id) {
                    window.location.href =
                        '{{ url('student/arena/' . $room->id . '/battle') }}/'
                        + data.participant_id;
                    return;
                }

                this.schedule();

            } catch(e) {
                this.errorCount++;
                this.pollInterval = Math.min(
                    3000 * Math.pow(1.5, this.errorCount),
                    this.maxInterval
                );
                this.schedule();
            }
        }
    }
}
</script>
@endpush
