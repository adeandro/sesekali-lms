@extends('layouts.app')
@section('title', 'Battle — ' . $room->name)

@section('content')
<div x-data="studentBattle('{{ $room->token }}')"
     x-init="initBattle()"
     class="min-h-[80vh] flex flex-col justify-center
            max-w-lg mx-auto w-full px-4 py-4 select-none relative z-10">

    {{-- LOADER --}}
    <template x-if="!state.state">
        <div class="text-center py-20 text-gray-400">
            <div class="w-10 h-10 border-4 border-indigo-500
                        border-t-transparent rounded-full
                        animate-spin mx-auto mb-4"></div>
            <p class="font-bold text-sm">
                Menghubungkan ke Arena...
            </p>
        </div>
    </template>

    <template x-if="state.state">
        <div class="w-full flex flex-col gap-4">

            {{-- HEADER: nama + skor + timer --}}
  <div class="flex items-center justify-between
              p-4 bg-white dark:bg-gray-800
              rounded-2xl border border-gray-100
              dark:border-gray-700 shadow-sm">

    {{-- Kiri: avatar + nama + skor --}}
    <div class="flex items-center gap-3">
      @php $authUser = auth()->user(); @endphp
      @if($authUser->is_avatar_seed
          && $authUser->avatar_seed)
        <div class="w-10 h-10 rounded-full
                    overflow-hidden bg-white shrink-0
                    ring-2 ring-purple-200
                    dark:ring-purple-700"
             x-html="typeof multiavatar !== 'undefined'
                      ? multiavatar(
                          '{{ $authUser->avatar_seed }}'
                        )
                      : ''">
        </div>
      @elseif($authUser->avatar_url)
        <img src="{{ $authUser->avatar_url }}"
             class="w-10 h-10 rounded-full object-cover
                    ring-2 ring-purple-200
                    dark:ring-purple-700 shrink-0">
      @else
        <div class="w-10 h-10 rounded-full
                    bg-purple-100 dark:bg-purple-900/40
                    text-purple-700 dark:text-purple-300
                    flex items-center justify-center
                    font-black shrink-0
                    ring-2 ring-purple-200">
          {{ $authUser->initials }}
        </div>
      @endif

      <div>
        <p class="font-black text-gray-900
                   dark:text-white text-sm leading-tight
                   max-w-[120px] truncate">
          {{ $authUser->name }}
        </p>
      </div>
    </div>

    {{-- Kanan: timer atau state badge --}}
    <template x-if="state.state === 'question'">
      <div class="flex flex-col items-center">
        <div class="w-12 h-12 rounded-full border-4
                    flex items-center justify-center
                    font-black text-xl transition-all shadow-inner"
             :class="remainingTime <= 5
               ? 'border-red-500 text-red-600 animate-pulse bg-red-50'
               : remainingTime <= 10
                 ? 'border-amber-500 text-amber-600 bg-amber-50'
                 : 'border-emerald-500 text-emerald-600 bg-emerald-50'">
          <span x-text="remainingTime ?? '--'"></span>
        </div>
        <p class="text-[10px] text-gray-400 mt-0.5
                   font-bold uppercase tracking-wider">
          detik
        </p>
      </div>
    </template>

    <template x-if="state.state !== 'question'">
      <div class="px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider"
           x-text="state.state === 'lobby' ? 'Menunggu' : state.state === 'preview' ? 'Siap!' : state.state === 'discussion' ? 'Diskusi' : state.state === 'leaderboard' ? 'Ranking' : 'Selesai'">
      </div>
    </template>
  </div>

            {{-- Toggle manual dihapus, karena dikontrol guru --}}

            {{-- MAIN STAGE --}}
            <div class="flex flex-col gap-4">

                {{-- ── LOBBY ── --}}
  <template x-if="state.state === 'lobby'">
    <div class="bg-gradient-to-br from-purple-50
                to-indigo-50 dark:from-purple-900/20
                dark:to-indigo-900/20
                rounded-3xl p-8 text-center
                border-2 border-purple-100
                dark:border-purple-800/50">
      <div class="text-6xl mb-4 animate-bounce">⚔️</div>
      <h2 class="text-xl font-black text-gray-900
                  dark:text-white mb-2">
        Bersiap Tempur!
      </h2>
      <p class="text-sm text-gray-500 mb-6">
        Guru akan segera memulai battle.
        Pastikan koneksimu stabil!
      </p>
      <div class="flex justify-center gap-2">
        @foreach([0, 0.15, 0.3] as $delay)
        <div class="w-2.5 h-2.5 rounded-full
                     bg-purple-500 animate-bounce"
             style="animation-delay: {{ $delay }}s">
        </div>
        @endforeach
      </div>
    </div>
  </template>

                {{-- ── PREVIEW ── --}}
  <template x-if="state.state === 'preview'">
    <div class="bg-gradient-to-br from-indigo-600
                to-purple-700 rounded-3xl p-10
                text-center shadow-xl
                shadow-indigo-500/20">
      <div class="w-20 h-20 mx-auto mb-6
                  bg-white/20 rounded-full
                  flex items-center justify-center
                  animate-pulse">
        <span class="text-4xl font-black text-white"
              x-text="(state.q_index ?? 0) + 1">
        </span>
      </div>
      <p class="text-indigo-200 text-xs font-black
                 uppercase tracking-widest mb-2">
        Soal ke-
        <span x-text="(state.q_index ?? 0) + 1">
        </span>
      </p>
      <h2 class="text-3xl font-black text-white
                  uppercase tracking-tight">
        Siap-Siap!
      </h2>
    </div>
  </template>

                {{-- ── QUESTION ── --}}
                <template x-if="state.state === 'question'">
                    <div class="flex flex-col gap-4">

                        {{-- Tampilan soal (jika toggle ON) --}}
                        <template x-if="showQuestion && question">
  <div class="bg-white dark:bg-gray-800
              rounded-2xl p-5 border-2
              border-purple-200
              dark:border-purple-700/50
              shadow-md shadow-purple-500/5">
    {{-- Nomor soal --}}
    <div class="flex items-center gap-2 mb-3">
      <span class="text-xs font-black px-2.5 py-1
                    rounded-lg bg-purple-100
                    dark:bg-purple-900/40
                    text-purple-700
                    dark:text-purple-400">
        Soal <span x-text="(state.q_index??0)+1"></span>
        dari {{ $room->total_questions }}
      </span>
    </div>
    {{-- Teks soal --}}
    <div class="text-base text-gray-900
                 dark:text-gray-100 font-medium
                 leading-relaxed prose prose-sm
                 dark:prose-invert max-w-none"
         x-html="question.question_text">
    </div>
    {{-- Gambar soal --}}
    <template x-if="question.question_image">
      <div class="mt-3 rounded-xl overflow-hidden
                  border border-gray-100
                  dark:border-gray-700">
        <img :src="question.question_image"
             class="w-full max-h-52 object-contain
                    bg-gray-50 dark:bg-gray-700"
             loading="lazy">
      </div>
    </template>
  </div>
                        </template>

                        {{-- Sudah menjawab --}}
                        <template x-if="hasAnswered">
                            <div class="bg-emerald-50
                                        dark:bg-emerald-900/30
                                        rounded-3xl p-8 text-center
                                        border-2 border-emerald-500/50">
                                <div class="text-5xl mb-3">✅</div>
                                <h3 class="text-xl font-black
                                           text-emerald-700
                                           dark:text-emerald-400">
                                    Jawaban Terkunci!
                                </h3>
                                <p class="text-emerald-600/80
                                           dark:text-emerald-500
                                           font-bold mt-1 text-sm">
                                    Menunggu waktu habis...
                                </p>
                            </div>
                        </template>

                        {{-- Belum menjawab: tombol A-E --}}
                        <template x-if="!hasAnswered">
                            <div class="w-full">

                                {{-- Opsi jawaban beserta teks
                                     (jika showQuestion ON) --}}
                                <template x-if="showQuestion && question">
                                    <div class="grid grid-cols-2 gap-4">
                                        <template x-for="(opt, key)
                                            in (question?.options || {})"
                                            :key="key">
                                            <template x-if="opt.text || opt.image">
                                                <button
                                                    @click="submitAnswer(key)"
                                                    :disabled="isSubmitting || remainingTime === 0"
                                                    class="w-full rounded-xl p-4 text-left
                                                           flex items-start gap-3 transition-all
                                                           active:scale-[0.98] font-medium
                                                           text-sm border-2 shadow-sm
                                                           hover:shadow-md
                                                           disabled:opacity-40
                                                           disabled:cursor-not-allowed"
                                                    :class="{
                                                        'bg-rose-500 border-rose-600 text-white': key === 'a',
                                                        'bg-blue-500 border-blue-600 text-white': key === 'b',
                                                        'bg-amber-400 border-amber-500 text-amber-900': key === 'c',
                                                        'bg-emerald-500 border-emerald-600 text-white': key === 'd',
                                                        'bg-purple-500 border-purple-600 text-white col-span-2': key === 'e'
                                                    }">
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <template x-if="key === 'a'"><span class="text-xl">▲</span></template>
                                                        <template x-if="key === 'b'"><span class="text-xl">◆</span></template>
                                                        <template x-if="key === 'c'"><span class="text-xl">●</span></template>
                                                        <template x-if="key === 'd'"><span class="text-xl">■</span></template>
                                                        <span class="font-black text-xl uppercase"
                                                              x-text="key"></span>
                                                    </div>
                                                    <div class="flex flex-col gap-1 min-w-0">
                                                        <template x-if="opt.image">
                                                            <img :src="opt.image"
                                                                 class="max-h-20
                                                                        object-contain
                                                                        rounded-lg
                                                                        w-full"
                                                                 loading="lazy">
                                                        </template>
                                                        <span x-html="opt.text"
                                                              class="leading-tight break-words
                                                                     text-sm font-bold">
                                                        </span>
                                                    </div>
                                                </button>
                                            </template>
                                        </template>
                                    </div>
                                </template>

                                {{-- Tombol besar A-E saja
                                     (jika showQuestion OFF) --}}
                                <template x-if="!showQuestion">
                                    <div class="grid grid-cols-2 gap-3">
                                        <template x-for="(opt, key)
                                            in (question?.options || {})"
                                            :key="key">
                                            <template x-if="opt.text || opt.image">
                                                <button
                                                    @click="submitAnswer(key)"
                                                    :disabled="isSubmitting || remainingTime === 0"
                                                    class="rounded-2xl p-6
                                                           text-center shadow-sm
                                                           active:scale-[0.98]
                                                           transition-all
                                                           disabled:opacity-40
                                                           disabled:cursor-not-allowed
                                                           relative overflow-hidden font-medium border-2"
                                                    :class="{
                                                        'bg-rose-500 border-rose-600 text-white col-span-1': key === 'a',
                                                        'bg-blue-500 border-blue-600 text-white col-span-1': key === 'b',
                                                        'bg-amber-400 border-amber-500 text-amber-900 col-span-1': key === 'c',
                                                        'bg-emerald-500 border-emerald-600 text-white col-span-1': key === 'd',
                                                        'bg-purple-500 border-purple-600 text-white col-span-2': key === 'e'
                                                    }">
                                                    <div class="flex flex-col items-center gap-4">
                                                        <template x-if="key === 'a'"><span class="text-5xl">▲</span></template>
                                                        <template x-if="key === 'b'"><span class="text-5xl">◆</span></template>
                                                        <template x-if="key === 'c'"><span class="text-5xl">●</span></template>
                                                        <template x-if="key === 'd'"><span class="text-5xl">■</span></template>
                                                        <span class="text-4xl font-black
                                                                      uppercase"
                                                              x-text="key"></span>
                                                    </div>
                                                </button>
                                            </template>
                                        </template>
                                    </div>
                                </template>

                                {{-- Tombol disabled saat timer = 0 --}}
                                <template x-if="remainingTime === 0">
                                    <div class="mt-3 text-center text-xs
                                                 text-red-500 font-bold animate-pulse">
                                        Waktu habis!
                                    </div>
                                </template>

                            </div>
                        </template>
                    </div>
                </template>

                {{-- ── DISCUSSION ── --}}
                <template x-if="state.state === 'discussion'">
                    <div class="bg-white dark:bg-gray-800
                                rounded-3xl p-6 text-center
                                border border-gray-100
                                dark:border-gray-700 relative
                                overflow-hidden">

                        {{-- Garis warna atas --}}
                        <div class="absolute top-0 left-0 w-full h-1.5"
                             :class="answerResult?.is_correct
                               ? 'bg-emerald-500'
                               : 'bg-red-500'">
                        </div>

                        {{-- Tidak menjawab --}}
                        <template x-if="!answerResult">
                            <div class="py-4">
                                <div class="text-5xl mb-4">⏱️</div>
                                <h2 class="text-xl font-black
                                           text-slate-400 mb-2 uppercase tracking-tight">
                                    WAKTU HABIS
                                </h2>
                                <p class="text-sm text-slate-500 font-bold">
                                    Jawaban benar:
                                    <span class="font-black uppercase
                                                  text-emerald-400 px-1"
                                          x-text="question?.correct_answer">
                                    </span>
                                </p>
                            </div>
                        </template>

                        {{-- Benar --}}
                        <template x-if="answerResult?.is_correct">
                            <div class="py-4">
                                <div class="text-6xl mb-4 drop-shadow-lg">✨</div>
                                <h2 class="text-3xl font-black
                                           text-emerald-400 mb-2 uppercase tracking-tighter">
                                    HEBAT! BENAR!
                                </h2>
                                <div class="inline-flex flex-col items-center
                                             bg-emerald-500/10 border border-emerald-500/30
                                             rounded-2xl px-8 py-5 mt-4 shadow-inner">
                                    <p class="text-[10px] text-emerald-400/80
                                               font-extrabold uppercase
                                               tracking-[0.2em] mb-1">
                                        Poin Didapat
                                    </p>
                                    <p class="text-5xl font-black
                                               text-emerald-400 tabular-nums"
                                       x-text="'+' + (answerResult?.score_earned ?? 0).toLocaleString()">
                                    </p>
                                </div>
                            </div>
                        </template>

                        {{-- Salah --}}
                        <template x-if="answerResult && !answerResult.is_correct">
                            <div class="py-4">
                                <div class="text-6xl mb-4 drop-shadow-lg opacity-80">❌</div>
                                <h2 class="text-2xl font-black
                                           text-rose-500 mb-1 uppercase tracking-tighter">
                                    KURANG BERUNTUNG
                                </h2>
                                <p class="text-sm text-slate-400 font-bold">
                                    Kamu memilih:
                                    <span class="font-black uppercase
                                                  text-rose-400 px-1"
                                          x-text="answerResult?.chosen ?? '-'">
                                    </span>
                                    &nbsp;·&nbsp;
                                    Benar:
                                    <span class="font-black uppercase
                                                  text-emerald-400 px-1"
                                          x-text="question?.correct_answer">
                                    </span>
                                </p>
                            </div>
                        </template>

                        {{-- Total skor --}}
                        <div class="mt-8 pt-8 border-t border-slate-800">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest mb-1">Poin Kamu</p>
                                    <p class="text-4xl font-black text-white tabular-nums drop-shadow-md"
                                       x-text="(myScore?.total_score ?? 0).toLocaleString()"></p>
                                </div>
                                <template x-if="groupScore !== null">
                                    <div class="border-l border-slate-800 pl-4">
                                        <p class="text-[10px] text-indigo-400 font-black uppercase tracking-widest mb-1">Poin Tim</p>
                                        <p class="text-4xl font-black text-indigo-300 tabular-nums drop-shadow-md"
                                           x-text="groupScore.toLocaleString()"></p>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>
                </template>

                {{-- ── LEADERBOARD ── --}}
                <template x-if="state.state === 'leaderboard'">
                    <div class="bg-slate-900 rounded-[2.5rem] p-12
                                text-center border border-slate-800 shadow-2xl relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-b from-indigo-500/5 to-transparent"></div>
                        <div class="relative z-10">
                            <div class="text-7xl mb-6 drop-shadow-xl animate-bounce">🏆</div>
                            <h2 class="text-sm font-black text-slate-400
                                       uppercase tracking-[0.3em] mb-8">
                                Battle Berlanjut
                            </h2>
                            <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-3xl py-10 px-6 shadow-inner">
                                <p class="text-[10px] text-indigo-300 font-extrabold uppercase tracking-widest mb-1 shadow-sm">Skor Terkumpul</p>
                                <div class="text-7xl font-black text-white tabular-nums drop-shadow-2xl tracking-tighter"
                                     x-text="(myScore?.total_score ?? 0).toLocaleString()">
                                </div>
                                <p class="text-xs text-indigo-400 font-black mt-2 uppercase tracking-widest opacity-60">Poin Arena</p>
                            </div>
                            <p class="text-xs text-slate-500 font-bold mt-10 animate-pulse">
                                Tetap fokus, soal berikutnya segera muncul...
                            </p>
                        </div>
                    </div>
                </template>

                {{-- ── FINISH ── --}}
                <template x-if="state.state === 'finish'">
                    <div class="bg-slate-900 shadow-2xl
                                rounded-[2.5rem] p-12 text-center
                                border border-slate-800 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400"></div>
                        <div class="text-7xl mb-6">🏅</div>
                        <h2 class="text-3xl font-black
                                   text-white mb-2 uppercase tracking-tight">
                            Battle Selesai!
                        </h2>
                        <p class="text-slate-400 text-sm font-bold mb-8">
                            Hasil akhir pencapaianmu:
                        </p>
                        <div class="bg-amber-500/10 border border-amber-500/20 rounded-3xl py-12 px-6 shadow-inner mb-6">
                            <p class="text-[11px] text-amber-400 font-black uppercase tracking-[0.2em] mb-2 opacity-80">Total Poin</p>
                            <div class="text-7xl font-black text-amber-500 tabular-nums drop-shadow-xl tracking-tighter"
                                 x-text="(myScore?.total_score ?? 0).toLocaleString()">
                            </div>
                        </div>

                        {{-- Physical Reward Reveal --}}
                        <template x-if="myScore?.physical_reward">
                            <div class="bg-indigo-600/20 border border-indigo-400/30 rounded-2xl p-6 mb-8 text-center animate-bounce shadow-lg">
                                <p class="text-xs text-indigo-300 font-black uppercase tracking-[0.3em] mb-2">🎁 Hadiah Fisik Menantimu!</p>
                                <p class="text-2xl font-black text-white uppercase tracking-tight" x-text="myScore.physical_reward"></p>
                                <p class="text-[10px] text-indigo-400 font-bold mt-2 uppercase tracking-widest">Tunjukkan layar ini ke guru</p>
                            </div>
                        </template>
                            
                            {{-- EXP earned --}}
                            @php
                              $expEarned = 50; // default partisipan
                            @endphp
                            <div class="mt-3 inline-flex items-center gap-2
                                        px-4 py-2 bg-purple-50
                                        dark:bg-purple-900/20 rounded-xl
                                        border border-purple-100
                                        dark:border-purple-800">
                              <span class="text-purple-600 font-black text-sm">
                                ⚡ + <span x-text="myScore?.exp_earned ?? 50"></span> EXP
                              </span>
                            </div>

                            <div class="flex items-center justify-center gap-4 mt-6">
                                <div class="bg-emerald-500/20 rounded-full px-4 py-1.5 border border-emerald-500/30">
                                    <span class="text-emerald-400 text-xs font-black" x-text="(myScore?.correct ?? 0) + ' BENAR'"></span>
                                </div>
                                <div class="bg-red-500/20 rounded-full px-4 py-1.5 border border-red-500/30">
                                    <span class="text-red-400 text-xs font-black" x-text="(myScore?.wrong ?? 0) + ' SALAH'"></span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('student.arena.index') }}"
                           class="block w-full px-6 py-4
                                  bg-white text-slate-950
                                  font-black rounded-[2rem]
                                  hover:bg-indigo-50 transition-all
                                  text-base shadow-xl active:scale-95">
                            KEMBALI KE BERANDA
                        </a>
                    </div>
                </template>

            </div>
        </div>
    </template>

</div>

<script>
function studentBattle(token) {
    return {
        token:          token,
        state:          {},
        myScore:        null,
        groupScore:     null,
        groupLabel:     '{{ $participant->group_label }}',
        hasAnswered:    false,
        answerResult:   null,   // {chosen, is_correct, score_earned}
        question:       null,
        remainingTime:  0,
        showQuestion:   {{ $room->show_question_on_device ? 'true' : 'false' }},
        isSubmitting:   false,
        timerInterval:  null,
        pollInterval:   null,
        lastStateStr:   '',     // format: {state}-{q_index}
        isLocked:       false,
        serverDrift:    0,

        initBattle() {
            // Jitter awal (0-1500ms) agar riuh request tidak barengan
            const jitter = Math.floor(Math.random() * 1500);
            
            setTimeout(() => {
                this.pollData();
                this.tickTimer();
                this.startAdaptivePolling();
                
                // Timer client-side tetap 1 detik untuk UI smooth
                this.timerInterval = setInterval(() => this.tickTimer(), 1000);
            }, jitter);
        },

        startAdaptivePolling() {
            if (this.pollInterval) clearInterval(this.pollInterval);
            
            let interval = 3000; // default
            
            if (this.state.state === 'question') {
                interval = 2000; // lebih cepat saat soal aktif
            } else if (this.state.state === 'lobby') {
                interval = 5000;
            } else if (this.state.state === 'finish') {
                return; // stop polling
            }

            this.pollInterval = setInterval(() => this.pollData(), interval);
        },

        tickTimer() {
            if (this.state.state !== 'question') {
                this.remainingTime = 0;
                return;
            }
            if (!this.state.question_started_at) return;

            const nowCorrected = Math.floor(Date.now() / 1000) + this.serverDrift;
            const elapsed = nowCorrected - this.state.question_started_at;
            const dur = this.state.question_duration || {{ $room->duration_per_question }};
            
            this.remainingTime = Math.max(0, dur - elapsed);
        },

        async pollData() {
            try {
                // 1. Ambil data dari Static Mirror (.json) - NO PHP
                const resMirror = await fetch(`/battle-mirror/${this.token}.json?t=${Date.now()}`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!resMirror.ok) {
                    // Fallback ke PHP jika mirror rusak/hilang
                    await this.syncWithServer();
                    return;
                }

                const mirror = await resMirror.json();
                
                // Sync Drift (Authoritative via HTTP Header) - HANYA HITUNG SEKALI
                if (this.serverDrift === 0) {
                    const sDate = resMirror.headers.get('Date');
                    if (sDate) {
                        this.serverDrift = Math.floor(new Date(sDate).getTime() / 1000) - Math.floor(Date.now() / 1000);
                    }
                }

                const newStateStr = `${mirror.state?.state}-${mirror.state?.q_index}-${mirror.updated_at}`;

                // Update global state dari mirror (sangat ringan)
                this.state = mirror.state || {};
                this.question = mirror.question;
                this.showQuestion = Boolean(mirror.show_on_device);
                this.isLocked = mirror.is_locked;

                // 2. Cek apakah status berubah? Jika ya, atau jika baru awal, 
                //    PANGGIL PHP untuk ambil skor/status pribadi.
                if (newStateStr !== this.lastStateStr) {
                    this.lastStateStr = newStateStr;
                    
                    // Reset local state jika soal berubah
                    const newQIndex = mirror.state?.q_index ?? 0;
                    if (newQIndex !== this.lastQIndex) {
                        this.lastQIndex   = newQIndex;
                        this.hasAnswered  = false;
                        this.answerResult = null;
                    }

                    // Tambahkan jitter 0-1500ms agar 40 siswa tidak hit PHP bersamaan (mencegah 508 Resource Limit)
                    const jitter = Math.floor(Math.random() * 1500);
                    await new Promise(r => setTimeout(r, jitter));

                    await this.syncWithServer();
                    this.startAdaptivePolling(); // Sesuaikan kecepatan polling
                }
            } catch (e) {
                // Silent fail
            }
        },

        async syncWithServer() {
            try {
                // Ambil data pribadi (skor, rank, hasAnswered) via PHP
                const res = await fetch(`/student/arena/${this.token}/battle/data`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                this.myScore = data.my_score;
                this.groupScore = data.group_score;
                
                if (data.has_answered) {
                    this.hasAnswered = true;
                }
                if (data.answer_result) {
                    this.answerResult = data.answer_result;
                }

                if (this.state.state === 'finish') {
                    clearInterval(this.pollInterval);
                    clearInterval(this.timerInterval);
                }
            } catch (e) {}
        },

        async submitAnswer(opt) {
            if (this.isSubmitting || this.hasAnswered || this.remainingTime === 0 || opt === null) return;

            this.isSubmitting = true;

            try {
                const res = await fetch(
                    '/student/arena/' + this.token + '/answer',
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            answer:         opt,
                            time_remaining: this.remainingTime,
                        }),
                    }
                );

                const data = await res.json();

                if (data.status === 'ok') {
                    this.hasAnswered = true;
                    if (this.myScore) {
                        this.myScore.total_score = data.total_score;
                        this.myScore.rank        = data.rank;
                        this.myScore.streak      = data.streak;
                    }
                } else if (data.status === 'rejected' && data.reason === 'time_expired') {
                    this.remainingTime = 0;
                }
            } catch (e) {}

            this.isSubmitting = false;
        },

        // attemptFullscreen() { ... removed per request ... }
    }
}
</script>
@endsection
