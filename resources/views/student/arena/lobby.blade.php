@extends('layouts.app')
@section('title', 'Lobby — ' . $room->name)
@section('content')
@push('styles')
<style>
    main#main-content-scroll { 
        background-color: #020617 !important; 
    }
</style>
@endpush
<div class="fixed inset-0 !bg-[#020617] -z-20 pointer-events-none"></div>
<div class="fixed inset-0 bg-[#020617] -z-10"></div>
<div class="min-h-[85vh] flex flex-col items-center justify-center px-4 py-8 select-none relative z-10"
     x-data="lobbyPoller()"
     x-init="init()">

    {{-- Main Container --}}
    <div class="w-full max-w-md space-y-8">

        {{-- Top Status --}}
        <div class="flex flex-col items-center gap-4">
            <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full bg-slate-900/80 border border-slate-800 backdrop-blur-xl shadow-2xl">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
                </span>
                <span class="text-[10px] font-black text-emerald-400 uppercase tracking-[0.3em]">
                    Sinyal Stabil
                </span>
            </div>
        </div>

        {{-- Room Card --}}
        <div class="bg-slate-900 rounded-[3rem] border border-slate-800 p-10 text-center shadow-[0_32px_64px_-12px_rgba(0,0,0,0.6)] relative overflow-hidden group">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl transition-all group-hover:bg-indigo-500/20"></div>
            
            <div class="relative z-10">
                <div class="w-20 h-20 mx-auto mb-6 rounded-[2rem] bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-xl shadow-indigo-500/20">
                    <i class="fas fa-bolt text-3xl text-white"></i>
                </div>

                <h1 class="text-3xl font-black text-white tracking-tight leading-tight mb-2 uppercase">
                    {{ $room->name }}
                </h1>
                
                <p class="text-slate-400 font-bold text-sm mb-8 px-4">
                    Guru sedang menyiapkan medan tempur. Siapkan strategimu!
                </p>

                {{-- Joined Counter --}}
                <div class="inline-flex items-center gap-4 px-6 py-4 rounded-3xl bg-slate-950/50 border border-slate-800/50 shadow-inner">
                    <div class="flex -space-x-3 overflow-hidden">
                        <div class="w-8 h-8 rounded-full bg-slate-800 border-2 border-slate-900 flex items-center justify-center">
                            <i class="fas fa-user-friends text-[10px] text-slate-500"></i>
                        </div>
                    </div>
                    <div class="text-left">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Terkumpul</p>
                        <p class="text-lg font-black text-white tabular-nums leading-none">
                            <span x-text="count"></span> <span class="text-xs text-slate-400 uppercase ml-1">Peserta</span>
                        </p>
                    </div>
                </div>

                {{-- Group Info --}}
                @if($room->mode === 'group' && $participant->group_label)
                <div class="mt-8">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-indigo-500/10 border border-indigo-500/20">
                        <i class="fas fa-shield-halved text-indigo-400 text-xs shadow-sm"></i>
                        <span class="text-xs font-black text-indigo-300 uppercase tracking-widest">
                            FRAKSI: {{ $participant->group_label }}
                        </span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Reward Dashboard --}}
        <div class="bg-slate-900/50 backdrop-blur-xl rounded-[2.5rem] border border-slate-800/50 p-8 shadow-2xl relative overflow-hidden">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xs font-black text-slate-500 uppercase tracking-[0.2em]">
                    <i class="fas fa-gift mr-2 text-amber-500"></i> Alokasi Hadiah
                </h3>
                <div class="h-px flex-1 bg-slate-800 ml-4"></div>
            </div>

            <div class="grid grid-cols-1 gap-3">
                 @foreach([
                    ['🥇', 'RANK #1', $room->reward_rank1_exp, 'from-amber-400 to-yellow-600', $room->reward_rank1_physical],
                    ['🥈', 'RANK #2', $room->reward_rank2_exp, 'from-slate-300 to-slate-500', $room->reward_rank2_physical],
                    ['🥉', 'RANK #3', $room->reward_rank3_exp, 'from-orange-400 to-orange-700', $room->reward_rank3_physical],
                ] as [$emoji, $label, $exp, $gradient, $physical])
                <div class="flex flex-col p-4 rounded-2xl bg-slate-950/40 border border-slate-800/30 group hover:border-slate-700 transition-all">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <span class="text-2xl drop-shadow-md grayscale group-hover:grayscale-0 transition-all">{{ $emoji }}</span>
                            <div>
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">{{ $label }}</p>
                                <p class="text-sm font-black text-indigo-100">+{{ number_format($exp) }} <span class="text-[9px] opacity-50 uppercase ml-1">EXP</span></p>
                            </div>
                        </div>
                    </div>
                    @if($physical)
                    <div class="mt-3 flex items-center gap-2 px-3 py-2 bg-amber-500/10 border border-amber-500/20 rounded-xl">
                        <i class="fas fa-gift text-amber-500 text-[10px]"></i>
                        <span class="text-[9px] font-black text-amber-400 uppercase tracking-[0.1em] truncate">Prize: {{ $physical }}</span>
                    </div>
                    @endif
                </div>
                @endforeach

            </div>
        </div>

        {{-- Metadata footer --}}
        <div class="flex items-center justify-center gap-5 opacity-40 hover:opacity-100 transition-opacity">
            <div class="flex flex-col items-center">
                <span class="text-[10px] font-black text-slate-500 uppercase">Soal</span>
                <span class="text-xs font-black text-white tracking-widest">{{ $room->total_questions }}</span>
            </div>
            <div class="w-px h-6 bg-slate-800"></div>
            <div class="flex flex-col items-center">
                <span class="text-[10px] font-black text-slate-500 uppercase">Tempo</span>
                <span class="text-xs font-black text-white tracking-widest">{{ $room->duration_per_question }}s</span>
            </div>
            <div class="w-px h-6 bg-slate-800"></div>
            <div class="flex flex-col items-center">
                <span class="text-[10px] font-black text-slate-500 uppercase">Mode</span>
                <span class="text-[10px] font-black text-white tracking-widest uppercase">
                    {{ $room->mode === 'individual' ? 'Solo' : ($room->mode === 'group' ? 'Squad' : 'Class') }}
                </span>
            </div>
        </div>

        {{-- MODAL PILIH GRUP --}}
        @if($room->mode === 'group')
        <template x-if="showGroupModal">
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-950/90 backdrop-blur-xl transition-all"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="w-full max-w-sm bg-slate-900 rounded-[3rem] border border-slate-800 p-8 text-center shadow-2xl relative overflow-hidden">
                    {{-- Decorative highlight --}}
                    <div class="absolute -top-24 -left-24 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl"></div>
                    
                    <div class="relative z-10">
                        <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-indigo-500/20 flex items-center justify-center">
                            <i class="fas fa-users-viewfinder text-2xl text-indigo-400"></i>
                        </div>
                        <h2 class="text-2xl font-black text-white uppercase tracking-tight mb-2">Pilih Tim Kamu</h2>
                        <p class="text-slate-400 text-sm mb-8">Bergabunglah dengan salah satu tim untuk memulai pertempuran!</p>

                        @php
                            $groups = $room->group_names ?? [];
                            if (empty($groups) && $room->group_count > 0) {
                                for ($i = 1; $i <= $room->group_count; $i++) {
                                    $groups[] = "Grup " . $i;
                                }
                            }
                            $colors = [
                                'rose'    => ['border' => 'border-rose-500/30', 'bg' => 'bg-rose-500/10', 'text' => 'text-rose-400', 'hover' => 'hover:bg-rose-500/20 hover:border-rose-500'],
                                'blue'    => ['border' => 'border-blue-500/30', 'bg' => 'bg-blue-500/10', 'text' => 'text-blue-400', 'hover' => 'hover:bg-blue-500/20 hover:border-blue-500'],
                                'emerald' => ['border' => 'border-emerald-500/30', 'bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'hover' => 'hover:bg-emerald-500/20 hover:border-emerald-500'],
                                'amber'   => ['border' => 'border-amber-500/30', 'bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'hover' => 'hover:bg-amber-500/20 hover:border-amber-500'],
                                'indigo'  => ['border' => 'border-indigo-500/30', 'bg' => 'bg-indigo-500/10', 'text' => 'text-indigo-400', 'hover' => 'hover:bg-indigo-500/20 hover:border-indigo-500'],
                                'purple'  => ['border' => 'border-purple-500/30', 'bg' => 'bg-purple-500/10', 'text' => 'text-purple-400', 'hover' => 'hover:bg-purple-500/20 hover:border-purple-500'],
                                'sky'     => ['border' => 'border-sky-500/30', 'bg' => 'bg-sky-500/10', 'text' => 'text-sky-400', 'hover' => 'hover:bg-sky-500/20 hover:border-sky-500']
                            ];
                            $colorKeys = array_keys($colors);
                        @endphp

                        <div class="grid gap-4 {{ count($groups) > 4 ? 'grid-cols-2' : 'grid-cols-1' }}">
                            @foreach($groups as $idx => $gname)
                            @php
                                $colorKey = $colorKeys[$idx % count($colorKeys)];
                                $c = $colors[$colorKey];
                                
                                // Override khusus 'Merah' dan 'Biru' untuk kompatibilitas warna lama
                                if ($gname === 'Merah') $c = $colors['rose'];
                                if ($gname === 'Biru') $c = $colors['blue'];
                            @endphp
                            <button @click="selectGroup('{{ $gname }}')"
                                    :disabled="loading"
                                    class="w-full py-4 px-6 rounded-2xl border-2 transition-all font-black uppercase tracking-widest flex items-center justify-between group {{ $c['border'] }} {{ $c['bg'] }} {{ $c['text'] }} {{ $c['hover'] }}">
                                <span>{{ $gname }}</span>
                                <i class="fas fa-chevron-right text-[10px] opacity-30 group-hover:opacity-100 transition-opacity"></i>
                            </button>
                            @endforeach
                        </div>

                        <div x-show="loading" class="mt-6">
                            <div class="w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        @endif
        
        {{-- OVERLAY ROOM LOCKED --}}
        <template x-if="isLocked">
            <div class="fixed inset-0 z-[200] flex flex-col items-center justify-center p-8 bg-slate-950/80 backdrop-blur-md transition-all"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                
                <div class="w-24 h-24 rounded-full bg-rose-500/20 border-2 border-rose-500/50 flex items-center justify-center mb-6 shadow-[0_0_50px_rgba(244,63,94,0.3)]">
                    <i class="fas fa-lock text-4xl text-rose-500 animate-pulse"></i>
                </div>
                
                <h2 class="text-3xl font-black text-rose-500 uppercase tracking-tighter mb-2 text-center">ROOM LOCKED</h2>
                <div class="w-12 h-1 bg-rose-500/30 rounded-full mb-6"></div>
                
                <p class="text-slate-300 text-center font-bold max-w-xs leading-relaxed">
                    Guru telah mengunci pendaftaran. Tidak ada peserta baru yang dapat bergabung.
                </p>
                
                <p class="text-slate-500 text-[10px] uppercase font-black tracking-[0.2em] mt-10">
                    Kamu tetap bisa mengikuti battle
                </p>
            </div>
        </template>
    </div>
</div>

<script>
function lobbyPoller() {
    return {
        count: {{ $room->participants()->count() }},
        pollInterval: null,
        showGroupModal: {{ ($room->mode === 'group' && !$participant->group_label) ? 'true' : 'false' }},
        isLocked: {{ $room->is_locked ? 'true' : 'false' }},
        loading: false,

        init() {
            // Jitter awal agar tidak semua nembak di detik yg sama
            const jitter = Math.floor(Math.random() * 1500);
            setTimeout(() => {
                this.fetchStatus();
                this.pollInterval = setInterval(() => this.fetchStatus(), 3000);
            }, jitter);
        },

        async selectGroup(label) {
            this.loading = true;
            try {
                const res = await fetch('{{ route('student.arena.update-group', $room->token) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ group_label: label })
                });
                const data = await res.json();
                if (data.status === 'ok') {
                    this.showGroupModal = false;
                    // Force refresh to update UI with group name or just update local
                    window.location.reload(); 
                } else {
                    alert(data.message || 'Gagal memilih grup.');
                }
            } catch (e) {
                alert('Terjadi kesalahan jaringan.');
            } finally {
                this.loading = false;
            }
        },


        async fetchStatus() {
            try {
                // Poll Mirror JSON (Statik - No PHP)
                const res = await fetch(`/battle-mirror/${this.token}.json?t=${Date.now()}`, {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (!res.ok) {
                    // Fallback ke PHP jika mirror belum terbuat
                    this.fetchStatusFallback();
                    return;
                }

                const data = await res.json();
                this.count = data.member_count || 0;
                this.isLocked = data.is_locked || false;

                if (data.state && data.state.state !== 'lobby') {
                    clearInterval(this.pollInterval);
                    window.location.href = '{{ route('student.arena.battle', $room->token) }}';
                }
            } catch (e) {
                // Silent fail
            }
        },

        async fetchStatusFallback() {
            try {
                const res = await fetch('{{ route('student.arena.lobby.status', $room->token) }}', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.count = data.count;
                if (data.state && data.state !== 'lobby') {
                    clearInterval(this.pollInterval);
                    window.location.href = '{{ route('student.arena.battle', $room->token) }}';
                }
            } catch (e) {}
        }
    }
}
</script>
@endsection
