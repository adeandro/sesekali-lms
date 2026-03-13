@extends('layouts.blank')

@section('title', 'Battle! — ' . $room->name)

@push('head')
<style>
@keyframes shake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-6px)} 75%{transform:translateX(6px)} }
@keyframes hp-damage { 0%{opacity:1;transform:translateY(0)} 100%{opacity:0;transform:translateY(-30px)} }
.shake { animation: shake 0.3s ease; }
.hp-damage-text { animation: hp-damage 1s ease forwards; }
.option-btn { transition: all 0.15s ease; }
.option-btn:hover { transform: translateX(4px); }
.option-btn.correct { background: rgba(16,185,129,0.2); border-color: rgb(16,185,129); }
.option-btn.wrong   { background: rgba(239,68,68,0.2);  border-color: rgb(239,68,68); animation: shake 0.3s ease; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-950 via-slate-900 to-gray-950 flex flex-col"
     x-data="battleEngine()" x-init="init()">

    {{-- Top HUD --}}
    <div class="flex items-center justify-between px-4 py-3 bg-black/30 backdrop-blur border-b border-white/5 sticky top-0 z-20">
        {{-- HP Bar --}}
        <div class="flex items-center gap-3 flex-1">
            <div class="relative">
                <span class="text-red-400 font-black text-sm" x-text="hp + ' HP'"></span>
                <div class="absolute -top-5 left-0 text-red-400 text-xs font-black hp-damage-text" x-show="showDamage" x-cloak x-text="damageText"></div>
            </div>
            <div class="flex-1 max-w-40 h-3 bg-white/10 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500"
                     :class="hp > 50 ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : (hp > 20 ? 'bg-gradient-to-r from-amber-500 to-orange-400' : 'bg-gradient-to-r from-red-600 to-red-400')"
                     :style="'width:' + hp + '%'"></div>
            </div>
        </div>

        {{-- Timer --}}
        <div class="text-center">
            <p class="font-mono font-black text-lg"
               :class="isSuddenDeath ? 'text-red-400 animate-pulse' : 'text-white'"
               x-text="formatTime(remaining)">--:--</p>
            <p x-show="isSuddenDeath" x-cloak class="text-[9px] text-red-400 font-black uppercase tracking-widest animate-pulse">⚡ Sudden Death!</p>
        </div>

        {{-- Q Counter --}}
        <div class="text-right flex-1">
            <p class="text-xs font-black text-white">{{ $participant->correct_count + $participant->wrong_count + 1 }} / {{ $room->total_questions }}</p>
            <p class="text-[9px] text-emerald-400">✓ {{ $participant->correct_count }} benar</p>
        </div>
    </div>

    {{-- Question Area --}}
    <div class="flex-1 flex flex-col p-4 max-w-xl mx-auto w-full space-y-4" x-show="!battleEnded">

        {{-- Sudden Death Warning Banner --}}
        <div x-show="isSuddenDeath" x-cloak
             class="bg-red-500/20 border border-red-500/50 rounded-2xl px-4 py-2 text-center">
            <p class="text-xs font-black text-red-400 uppercase tracking-widest">⚡ Sudden Death — Penalti 2× Lipat!</p>
        </div>

        {{-- Question Card --}}
        <div class="bg-white/5 backdrop-blur rounded-[1.5rem] border border-white/10 p-5 flex-1 flex flex-col gap-4">
            <div class="flex items-center gap-2">
                <span class="text-[9px] font-black uppercase tracking-widest text-indigo-400 bg-indigo-500/10 px-2 py-1 rounded-full border border-indigo-500/20">
                    Soal {{ $participant->current_question_index + 1 }}
                </span>
            </div>

            <div class="text-white text-sm font-medium leading-relaxed flex-1">
                {!! $question->question_text !!}
            </div>

            @if($question->question_image)
            <img src="{{ asset('storage/' . $question->question_image) }}" alt="Gambar Soal"
                 class="w-full max-h-48 object-contain rounded-xl border border-white/10">
            @endif

            {{-- Options --}}
            <div class="space-y-2" id="options-container">
                @foreach($options as $key => $value)
                <button type="button"
                        id="opt-{{ $key }}"
                        onclick="submitAnswer('{{ $key }}')"
                        class="option-btn w-full text-left flex items-start gap-3 px-4 py-3 rounded-2xl text-sm text-white bg-white/5 border border-white/10 hover:bg-white/10">
                    <span class="shrink-0 w-6 h-6 rounded-lg bg-white/10 flex items-center justify-center text-[10px] font-black uppercase text-gray-300">{{ $key }}</span>
                    <span class="flex-1 leading-relaxed">{!! $value !!}</span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Feedback overlay --}}
        <div x-show="showFeedback" x-cloak
             class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-40 pointer-events-none">
            <div class="text-6xl" x-text="isCorrectFeedback ? '✅' : '❌'"></div>
        </div>
    </div>

    {{-- Battle Ended State --}}
    <div x-show="battleEnded" x-cloak class="flex-1 flex flex-col items-center justify-center p-6 text-center space-y-5">
        <div class="text-6xl" x-text="isDisqualified ? '💀' : '🎉'"></div>
        <h2 class="text-2xl font-black text-white" x-text="isDisqualified ? 'Kamu Gugur!' : 'Selesai!'"></h2>
        <p class="text-gray-400 text-sm" x-text="isDisqualified ? 'HP habis. Battle berakhir untukmu.' : 'Semua soal sudah dijawab!'"></p>
        <p class="text-indigo-400 text-sm font-bold">Menunggu hasil akhir dari admin…</p>
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-lg hover:-translate-y-0.5 transition-all duration-200">
            <i class="fas fa-home"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
const SUBMIT_URL  = '{{ route('student.arena.submit',    [$room, $participant]) }}';
const HEARTBEAT_URL = '{{ route('student.arena.heartbeat', [$room, $participant]) }}';
const TAB_PENALTY_URL = '{{ route('student.arena.tab-penalty', [$room, $participant]) }}';
const CSRF = document.querySelector('meta[name=csrf-token]').content;

// Tab focus penalty
let hiddenStart = null;
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        hiddenStart = Date.now();
    } else if (hiddenStart) {
        // Apply tab penalty
        fetch(TAB_PENALTY_URL, { method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'} })
            .then(r => r.json())
            .then(d => {
                window._battleEngine && (window._battleEngine.hp = d.hp);
                if (d.status === 'disqualified') {
                    window._battleEngine && (window._battleEngine.battleEnded = true, window._battleEngine.isDisqualified = true);
                }
            });
        hiddenStart = null;
    }
});

// Submit answer function (called by option buttons)
async function submitAnswer(key) {
    // Disable all buttons
    document.querySelectorAll('.option-btn').forEach(b => b.disabled = true);

    const res = await fetch(SUBMIT_URL, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
        body: JSON.stringify({ answer: key })
    });
    const data = await res.json();

    // Visual feedback
    const btn = document.getElementById('opt-' + key);
    btn.classList.add(data.is_correct ? 'correct' : 'wrong');

    window._battleEngine.showFeedback    = true;
    window._battleEngine.isCorrectFeedback = data.is_correct;
    window._battleEngine.hp             = data.hp;
    window._battleEngine.isSuddenDeath  = data.sudden_death;

    if (!data.is_correct) {
        window._battleEngine.damageText  = '-{{ $room->penalty_hp }} HP';
        window._battleEngine.showDamage  = true;
        setTimeout(() => window._battleEngine.showDamage = false, 1000);
    }

    if (data.status === 'disqualified') {
        window._battleEngine.battleEnded = true;
        window._battleEngine.isDisqualified = true;
        return;
    }

    // Reload page for next question after brief pause
    setTimeout(() => {
        if (data.next_index >= {{ $room->total_questions }}) {
            window._battleEngine.battleEnded = true;
        } else {
            window.location.reload();
        }
    }, 900);
}

function battleEngine() {
    return {
        hp: {{ $participant->hp }},
        remaining: {{ $room->remainingSeconds() }},
        isSuddenDeath: {{ $room->isSuddenDeath() ? 'true' : 'false' }},
        showFeedback: false,
        isCorrectFeedback: false,
        battleEnded: false,
        isDisqualified: false,
        showDamage: false,
        damageText: '',

        init() {
            window._battleEngine = this;
            // Countdown timer
            setInterval(() => { if (this.remaining > 0) this.remaining--; }, 1000);
            // Heartbeat every 10s
            setInterval(() => this.heartbeat(), 10000);
        },

        async heartbeat() {
            try {
                const res = await fetch(HEARTBEAT_URL, { method:'POST', headers:{'X-CSRF-TOKEN':CSRF} });
                const d = await res.json();
                this.remaining = d.remaining;
                this.isSuddenDeath = d.sudden_death;
                this.hp = d.hp;
                if (d.status === 'finished' || d.participant_status === 'disqualified') {
                    this.battleEnded = true;
                    this.isDisqualified = d.participant_status === 'disqualified';
                }
            } catch(e) {}
        },

        formatTime(s) {
            const m = Math.floor(s / 60);
            const sc = s % 60;
            return String(m).padStart(2,'0') + ':' + String(sc).padStart(2,'0');
        }
    }
}
</script>
@endpush
