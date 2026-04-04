@extends('layouts.app')
@section('title', 'Arena Control — ' . $room->name)

@section('content')
<div x-data="arenaControl('{{ $room->token }}')"
     x-init="initControl()"
     class="max-w-7xl mx-auto space-y-6 relative z-10">

    {{-- HEADER --}}
  <div class="bg-white dark:bg-gray-800 rounded-3xl p-6
              border border-gray-100 dark:border-gray-700
              shadow-sm mb-6">
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl
                    bg-purple-50 dark:bg-purple-900/40
                    flex items-center justify-center shrink-0">
          <i class="fas fa-gamepad text-purple-600
                     dark:text-purple-400 text-xl"></i>
        </div>
        <div>
          <h1 class="font-black text-gray-900
                      dark:text-white text-xl leading-tight">
            {{ $room->name }}
          </h1>
          <div class="flex items-center gap-2 mt-0.5
                       text-[11px] font-bold text-gray-500
                       dark:text-gray-400 uppercase tracking-wide">
            <code class="font-mono text-[13px] text-purple-700
                          dark:text-purple-400 tracking-widest bg-purple-50
                          dark:bg-purple-900/30 px-2 py-0.5 rounded-lg">
              {{ $room->token }}
            </code>
            <span>·</span>
            <span>{{ $room->mode }}</span>
            <span>·</span>
            <span>{{ $room->total_questions }} soal</span>
          </div>
        </div>
      </div>
      <a href="{{ route('admin.gamification.arena.display', $room->token) }}"
         target="_blank"
         class="inline-flex items-center gap-2
                px-5 py-2.5 rounded-xl text-sm
                font-black bg-purple-50
                dark:bg-purple-900/30
                text-purple-700 dark:text-purple-400
                hover:bg-purple-600 hover:text-white
                transition-all">
        <i class="fas fa-tv"></i>
        Buka Proyektor
      </a>
    </div>
  </div>

    <div class="flex items-center justify-between
                px-6 py-4 bg-slate-900
                rounded-2xl border
                border-slate-800
                mt-4">
        <div>
            <p class="text-sm font-medium
                       text-slate-300 dark:text-gray-300">
                Soal tampil di device siswa
            </p>
            <p class="text-xs text-indigo-200">
                Ubah kapan saja saat battle berlangsung
            </p>
        </div>
        <button @click="toggleShowQuestion()"
                class="relative w-11 h-6 rounded-full
                       transition-colors duration-200
                       focus:outline-none"
                :class="showQuestionOnDevice
                  ? 'bg-indigo-500'
                  : 'bg-gray-300 dark:bg-gray-600'">
            <span class="absolute top-0.5 left-0.5
                          w-5 h-5 bg-white rounded-full
                          shadow transition-transform
                          duration-200"
                  :class="showQuestionOnDevice
                    ? 'translate-x-5'
                    : 'translate-x-0'">
            </span>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 align-start items-start">
        
        {{-- KOLOM KIRI: ACTION BOX --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-900 rounded-2xl shadow-xl border border-slate-800 p-6 flex flex-col items-center justify-center min-h-[250px] text-center">
                
                <h3 class="w-full text-xs font-black text-indigo-300 uppercase tracking-widest mb-6 border-b border-slate-800 pb-3 text-left">Kontrol Sesi</h3>

                {{-- AKSI LOBBY --}}
                <template x-if="state.state === 'lobby'">
                    <div class="w-full space-y-4">
                        <i class="fas fa-users text-5xl text-gray-300 mb-2"></i>
                        <p class="text-sm text-slate-400">Tunggu peserta bergabung. Saat siap, mulai battle.</p>
                        
                        <div class="flex flex-col gap-3 pt-2">
                            <button @click="setState('preview')" :disabled="isProcessing || count === 0"
                                    class="w-full py-4 rounded-2xl font-black text-sm
                                           uppercase tracking-widest transition-all
                                           active:scale-[0.98] disabled:opacity-40
                                           disabled:cursor-not-allowed
                                           bg-gradient-to-r from-purple-600 to-indigo-600 
                                           text-white hover:from-purple-500 hover:to-indigo-500
                                           shadow-lg shadow-purple-500/20">
                                <i class="fas fa-rocket mr-2"></i> MULAI BATTLE
                            </button>

                            <button @click="toggleLock()" :disabled="isProcessing"
                                    class="w-full py-3 rounded-xl font-bold text-xs
                                           uppercase tracking-widest transition-all
                                           active:scale-[0.95] border-2"
                                    :class="isLocked 
                                        ? 'bg-rose-500/10 border-rose-500/50 text-rose-500 hover:bg-rose-500 hover:text-white' 
                                        : 'bg-emerald-500/10 border-emerald-500/50 text-emerald-500 hover:bg-emerald-500 hover:text-white'">
                                <i class="fas mr-2" :class="isLocked ? 'fa-lock' : 'fa-lock-open'"></i>
                                <span x-text="isLocked ? 'ROOM TERKUNCI (BUKA)' : 'KUNCI PENDAFTARAN'"></span>
                            </button>
                        </div>
                    </div>
                </template>

                {{-- AKSI PREVIEW --}}
                <template x-if="state.state === 'preview'">
                    <div class="w-full space-y-4">
                        <i class="fas fa-eye text-5xl text-blue-300 mb-2"></i>
                        <p class="text-sm text-slate-400">Persiapan soal <span x-text="(state.q_index ?? 0) + 1"></span>. Layar akan menampilkan "Bersiap".</p>
                        <button @click="setState('question')" :disabled="isProcessing"
                                class="w-full py-3 rounded-xl font-black text-sm
                                       uppercase tracking-widest transition-all
                                       active:scale-[0.98] disabled:opacity-40
                                       disabled:cursor-not-allowed
                                       bg-indigo-600 text-white hover:bg-indigo-700">
                            <i class="fas fa-play mr-2"></i> BUKA PERTANYAAN
                        </button>
                    </div>
                </template>

                {{-- AKSI QUESTION --}}
                <template x-if="state.state === 'question'">
                    <div class="w-full space-y-4">
                        
                        <div class="relative w-32 h-32 mx-auto flex items-center justify-center rounded-full bg-slate-800 border-4 border-slate-700">
                            <div class="text-center w-full">
                                <p class="text-slate-500 font-black text-xs uppercase tracking-[0.2em] mb-1">
                                    Terjawab
                                </p>
                                <p class="text-4xl font-black text-white tabular-nums leading-none mt-1">
                                    <span x-text="answersCount">0</span>
                                </p>
                                <div class="w-12 h-1 bg-slate-700 mx-auto mt-2 rounded-full overflow-hidden">
                                     <div class="h-full bg-emerald-500 transition-all duration-300 shadow-[0_0_10px_rgba(16,185,129,0.5)]" :style="'width: ' + ((answersCount / (count || 1)) * 100) + '%'"></div>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-sm text-slate-400 mt-4 mb-2">Siswa sedang menjawab...</p>
                        
                        <button @click="setState('discussion')" :disabled="isProcessing"
                                class="w-full py-3 rounded-xl font-black text-sm
                                       uppercase tracking-widest transition-all
                                       active:scale-[0.98] disabled:opacity-40
                                       disabled:cursor-not-allowed
                                       bg-amber-500 text-white hover:bg-amber-600">
                            <span class="relative z-10"><i class="fas fa-stop-circle mr-2"></i> TUTUP & BAHAS</span>
                        </button>
                    </div>
                </template>

                {{-- AKSI DISCUSSION --}}
                <template x-if="state.state === 'discussion'">
                    <div class="w-full space-y-4">
                        <i class="fas fa-comments text-5xl text-orange-300 mb-2"></i>
                        <p class="text-sm text-slate-400">Membahas jawaban benar dan grafik. Jika sudah jelas, lanjut ke Leaderboard Peringkat.</p>
                        <button @click="setState('leaderboard')" :disabled="isProcessing"
                                class="w-full py-3 rounded-xl font-black text-sm
                                       uppercase tracking-widest transition-all
                                       active:scale-[0.98] disabled:opacity-40
                                       disabled:cursor-not-allowed
                                       bg-cyan-600 text-white hover:bg-cyan-700">
                            <i class="fas fa-trophy mr-2"></i> LIHAT LEADERBOARD
                        </button>
                    </div>
                </template>

                {{-- AKSI LEADERBOARD --}}
                <template x-if="state.state === 'leaderboard'">
                    <div class="w-full space-y-4">
                        <i class="fas fa-list-ol text-5xl text-purple-300 mb-2"></i>
                        <p class="text-sm text-slate-400">Menampilkan peringkat sementara.</p>
                        
                        <template x-if="(state.q_index + 1) >= (state.q_total || 1)">
                                    <button @click="setState('next')" :disabled="isProcessing"
                                            class="w-full py-3 rounded-xl font-black text-sm
                                                   uppercase tracking-widest transition-all
                                                   active:scale-[0.98] disabled:opacity-40
                                                   disabled:cursor-not-allowed
                                                   bg-red-500 text-white hover:bg-red-600">
                                        <i class="fas fa-flag-checkered mr-2"></i> SELESAIKAN BATTLE
                                    </button>
                        </template>
                        
                        <template x-if="(state.q_index + 1) < (state.q_total || 1)">
                                    <button @click="setState('next')" :disabled="isProcessing"
                                            class="w-full py-3 rounded-xl font-black text-sm
                                                   uppercase tracking-widest transition-all
                                                   active:scale-[0.98] disabled:opacity-40
                                                   disabled:cursor-not-allowed
                                                   bg-indigo-600 text-white hover:bg-indigo-700">
                                        <i class="fas fa-forward mr-2"></i> SOAL BERIKUTNYA
                                    </button>
                        </template>
                    </div>
                </template>
                
                {{-- FINISH --}}
                <template x-if="state.state === 'finish'">
                    <div class="w-full space-y-4">
                        <i class="fas fa-flag-checkered text-5xl text-red-600 mb-2"></i>
                        <p class="text-xl font-black text-white">Battle Selesai!</p>
                        <p class="text-sm text-slate-400">Hasil tersimpan ke database.</p>
                        <a href="{{ route('admin.gamification.arena.index') }}"
                           class="inline-block mt-4 px-6 py-3 bg-slate-800 text-slate-300 hover:bg-slate-700 font-bold rounded-xl transition-colors border border-slate-700">
                            Kembali ke Daftar Room
                        </a>
                    </div>
                </template>

                {{-- DANGER ZONE: FORCE FINISH (Always visible when active) --}}
                <template x-if="state.state !== 'lobby' && state.state !== 'finish'">
                    <div class="pt-6 mt-6 border-t border-slate-800/50">
                        <button @click="forceFinish" :disabled="isProcessing"
                                class="w-full py-2.5 rounded-xl font-bold text-[10px]
                                       uppercase tracking-[0.2em] transition-all
                                       active:scale-[0.98] disabled:opacity-40
                                       disabled:cursor-not-allowed
                                       border border-rose-500/30 text-rose-500/70 hover:bg-rose-500/5 hover:text-rose-400">
                            <i class="fas fa-power-off mr-2"></i> Hentikan Paksa Battle
                        </button>
                    </div>
                </template>

            </div>
        </div>

        {{-- KOLOM TENGAH: PREVIEW SOAL --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-slate-900 rounded-2xl shadow-sm border border-slate-800  p-6 min-h-[250px] relative">
                <div class="flex items-center justify-between mb-4 border-b border-slate-800 pb-3">
                    <h3 class="text-xs font-black text-indigo-200 uppercase tracking-widest"><i class="fas fa-file-lines mr-1"></i> Preview Soal <span x-text="(state.q_index ?? 0) + 1"></span></h3>
                    
                    <button @click="toggleShowQuestion" 
                            class="flex items-center gap-2 px-4 py-2 rounded-xl transition-all active:scale-95 border"
                            :class="showQuestionOnDevice ? 'bg-indigo-500/20 text-indigo-300 border-indigo-500/40 shadow-[0_0_15px_rgba(99,102,241,0.2)]' : 'bg-slate-950/50 text-slate-500 border-slate-800'">
                        <i class="fas" :class="showQuestionOnDevice ? 'fa-eye' : 'fa-eye-slash-low-vision'"></i>
                        <span class="text-[10px] font-black uppercase tracking-wider" x-text="showQuestionOnDevice ? 'Soal Tampil (Siswa)' : 'Soal Tersembunyi (Siswa)'"></span>
                    </button>
                </div>
                
                <template x-if="!question">
                    <div class="text-center py-10 opacity-50">
                        <i class="fas fa-file-circle-question text-4xl mb-3"></i>
                        <p class="text-sm">Data Soal Belum Tersedia / Menunggu State</p>
                    </div>
                </template>

                <template x-if="question">
                    <div>
                        <div class="prose max-w-none text-white font-medium mb-6" x-html="question.question_text"></div>
                        <template x-if="question.question_image">
                            <img :src="question.question_image" class="max-h-64 rounded-xl mx-auto mb-6 object-contain border p-1" />
                        </template>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                            <template x-for="(opt, key) in question.options" :key="key">
                                <template x-if="opt.text || opt.image">
                                    <div class="flex items-start gap-4 p-4 rounded-xl border border-slate-800 transition-all"
                                         :class="{'bg-emerald-500/20 border-emerald-500/50 ring-1 ring-emerald-500/30': question.correct_answer === key, 'bg-slate-950/40': question.correct_answer !== key}">
                                        <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center font-black text-sm uppercase shrink-0 transition-colors"
                                             :class="question.correct_answer === key ? 'bg-emerald-500 border-emerald-500 text-white' : 'bg-slate-800 border-slate-700 text-slate-400'"
                                             x-text="key"></div>
                                        <div class="text-sm font-bold pt-1 w-full"
                                             :class="question.correct_answer === key ? 'text-emerald-300' : 'text-slate-100'">
                                            <p x-html="opt.text"></p>
                                            <template x-if="opt.image">
                                                <img :src="opt.image" class="mt-2 max-h-32 rounded border border-slate-800" />
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
            
            {{-- DAFTAR PESERTA BAWAH --}}
            <div class="bg-slate-900 rounded-2xl shadow-sm border border-slate-800  overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest"><i class="fas fa-list-ol mr-1"></i> Peringkat & Peserta Mengerjakan</h3>
                </div>
                <div class="max-h-64 overflow-y-auto w-full custom-scrollbar">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-slate-900 sticky top-0 border-b border-slate-800 z-10 text-[10px] uppercase font-black tracking-widest text-indigo-300/80">
                            <tr>
                                <th class="px-6 py-4 text-left">Rank</th>
                                <th class="px-6 py-4 text-left">Peserta</th>
                                <th class="px-6 py-4 text-right">Skor</th>
                                <th class="px-6 py-4 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="members.length === 0">
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-500 font-medium italic">Belum ada peserta yang bergabung...</td>
                                </tr>
                            </template>
                            <template x-for="(m, i) in members" :key="m.user_id">
                                <tr class="border-b border-slate-800 last:border-0 hover:bg-slate-800/50 transition-colors">
                                    <td class="px-6 py-3 font-black text-indigo-400 w-12 tabular-nums" x-text="getScoreData(m.user_id)?.rank || '-'"></td>
                                    <td class="px-6 py-3 font-bold text-white flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-800 border border-slate-700 shadow-sm">
                                            <template x-if="m.avatar_url">
                                                <img :src="m.avatar_url" class="w-full h-full object-cover" />
                                            </template>
                                            <template x-if="!m.avatar_url">
                                                <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-[10px] font-black italic">
                                                    <span x-text="m.initials"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <span x-text="m.name"></span>
                                    </td>
                                    <td class="px-6 py-3 text-right font-black text-indigo-100 tabular-nums" x-text="(getScoreData(m.user_id)?.total_score || 0).toLocaleString()"></td>
                                    <td class="px-6 py-3 text-right">
                                        <div class="flex justify-end">
                                            <span class="w-3 h-3 rounded-full shadow-[0_0_8px_rgba(16,185,129,0.3)] transition-all duration-300" 
                                                  :class="isAnswered(m.user_id) ? 'bg-emerald-500 scale-110' : 'bg-slate-700'"></span>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
        
    </div>

</div>

<script>
function arenaControl(token) {
    return {
        token: token,
        state: {},
        members: [],
        scores: [],
        question: null,
        count: 0,
        answersCount: 0,
        isProcessing: false,
        pollInterval: null,
        showQuestionOnDevice: {{ $room->show_question_on_device ? 'true' : 'false' }},
        isLocked: {{ $room->is_locked ? 'true' : 'false' }},
        autoAdvanceTriggered: false,
        totalQuestions: {{ $room->total_questions }},
        lastQIndex: -1,

        initControl() {
            this.fetchData();
            // Guru nge-poll setiap detiknya konstan untuk counter realtime (2000ms)
            this.pollInterval = setInterval(() => {
                this.fetchData();
            }, 2000);
        },

        async fetchData() {
            try {
                const res = await fetch('{{ url("admin/gamification/arena") }}/' + this.token + '/control/data');
                const data = await res.json();
                this.state = data.state;
                // Kita sort members berdasarkan rank score mereka
                let s_map = {};
                if(data.scores) {
                    data.scores.forEach(s => s_map[s.user_id] = s);
                }
                this.scores = s_map;
                this.members = (data.members || []).sort((a,b) => {
                    let rA = s_map[a.user_id]?.rank || 999;
                    let rB = s_map[b.user_id]?.rank || 999;
                    return rA - rB;
                });
                this.count = data.count;
                this.question = data.question;
                this.answersCount = data.answers_count;
                this.isLocked = data.is_locked;

                const newQIndex = data.state?.q_index ?? 0;
                if (newQIndex !== this.lastQIndex) {
                    this.lastQIndex = newQIndex;
                    this.autoAdvanceTriggered = false; // reset
                }

                this.checkAutoAdvance();

                if (this.state.state === 'finish') {
                    clearInterval(this.pollInterval);
                }
            } catch(e) {
                console.error("Polling error", e);
            }
        },

        checkAutoAdvance() {
            const s = this.state;
            if (s.state !== 'question') return;
            if (!s.question_started_at) return;
            if (this.autoAdvanceTriggered) return;

            const elapsed = Math.floor(Date.now() / 1000)
                            - s.question_started_at;
            const dur = s.question_duration ?? 0;

            // Timer habis + 2 detik grace period
            if (elapsed >= dur + 2) {
                this.autoAdvanceTriggered = true;
                this.advanceToDiscussion();
            }
        },

        async advanceToDiscussion() {
            try {
                await fetch(
                    '{{ route('admin.gamification.arena.control.setState', $room->token) }}',
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                ).content,
                        },
                        body: JSON.stringify({
                            state: 'discussion'
                        })
                    }
                );
                // Reset flag untuk soal berikutnya
                this.autoAdvanceTriggered = false;
            } catch(e) {}
        },

        async toggleLock() {
            this.isProcessing = true;
            try {
                const res = await fetch(
                    '{{ route('admin.gamification.arena.toggle-lock', $room->token) }}',
                    {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                ).content,
                        }
                    }
                );
                const data = await res.json();
                this.isLocked = data.is_locked;
                if(this.isLocked) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Room Berhasil Terkunci',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            } catch(e) {
                console.error("Toggle lock error", e);
            } finally {
                this.isProcessing = false;
            }
        },

        async toggleShowQuestion() {
            try {
                const res = await fetch(
                    '{{ route('admin.gamification.arena.toggle-show-question', $room->token) }}',
                    {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                ).content,
                        }
                    }
                );
                const data = await res.json();
                this.showQuestionOnDevice =
                    data.show_question_on_device;
            } catch(e) {}
        },

        async setState(newState) {
            this.isProcessing = true;
            try {
                const res = await fetch('{{ url("admin/gamification/arena") }}/' + this.token + '/control/state', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ state: newState })
                });
                
                if(res.ok) {
                    await this.fetchData();
                } else {
                    Swal.fire('Gagal', 'Terjadi kesalahan ubah state', 'error');
                }
            } catch(e) {
                Swal.fire('Error', 'Sistem Gagal. ' + e, 'error');
            }
            this.isProcessing = false;
        },
        
        forceFinish() {
            Swal.fire({
                title: 'Hentikan Paksa Battle?',
                text: "Room akan diubah statusnya menjadi Finish dan poin direkap permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, Hentikan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.setState('finish');
                }
            });
        },
        
        getScoreData(userId) {
            return this.scores[userId] || null;
        },
        
        isAnswered(userId) {
            // Karena answers dirender sebagai counter, the endpoint API could give answersMap, 
            // tapi kita bisa cukup tunjukan stats dari counter untuk ngirit bandwidth
            return false; // Disabled locally per member, just total counter is fine.
        }
    }
}
</script>
@endsection
