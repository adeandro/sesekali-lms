<!DOCTYPE html>
<html lang="id" class="dark" style="background-color: #020617 !important;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arena Display — {{ $room->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@multiavatar/multiavatar/multiavatar.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body {
            background-color: #020617 !important;
            background: radial-gradient(circle at 50% 0%, #1e1b4b 0%, #020617 100%) !important;
            min-height: 100vh !important;
            color: white !important;
            overflow: hidden !important;
            margin: 0 !important;
        }
        main {
            background: transparent !important;
        }
        .glass-panel {
            background: rgba(15, 23, 42, 0.85) !important;
            backdrop-filter: blur(32px) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8) !important;
        }
        .timer-ring {
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        @keyframes popIn {
            0% { transform: scale(0) rotate(-10deg); opacity: 0; }
            70% { transform: scale(1.1) rotate(2deg); opacity: 1; }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }
        .animate-popIn {
            animation: popIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
    </style>
</head>
<body x-data="arenaDisplay('{{ $room->token }}')" 
      x-init="initDisplay()" 
      class="antialiased overflow-hidden flex flex-col h-screen select-none bg-[#020617]"
      style="background-color: #020617 !important;">
    {{-- Force Dark Background Div --}}
    <div class="fixed inset-0 bg-[#020617] -z-20" style="background-color: #020617 !important;"></div>
    <div class="fixed inset-0 opacity-60 -z-10" style="background: radial-gradient(circle at 50% 0%, #1e1b4b 0%, #020617 100%) !important;"></div>

    {{-- HEADER KECIL --}}
    <header class="p-6 flex items-center justify-between z-10 glass-panel border-b-0 border-x-0 border-t-0 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg transform -rotate-6">
                <i class="fas fa-fist-raised text-2xl text-white"></i>
            </div>
            <div>
                <h1 class="text-xl font-black uppercase tracking-widest text-white">{{ $room->name }}</h1>
                <p class="text-[10px] text-indigo-200 font-black tracking-widest uppercase opacity-90">Battle Arena V2</p>
            </div>
        </div>
        <div class="flex gap-4 items-center">
            <template x-if="state.state === 'question'">
                <div class="flex items-center gap-2 px-4 py-2 bg-indigo-900/50 rounded-xl border border-indigo-500/30">
                    <i class="fas fa-list-ol text-indigo-400"></i>
                    <span class="font-black text-white">Soal <span x-text="(state.q_index??0)+1"></span> / {{ $room->total_questions }}</span>
                </div>
            </template>
            <div class="flex items-center gap-3 px-5 py-2.5 bg-black/40 rounded-2xl border border-white/10 shadow-inner">
                <span class="text-xs text-gray-400 font-bold uppercase tracking-widest">Pin Room</span>
                <span class="text-3xl font-black tracking-[0.3em] text-white">
                    {{ $room->token }}
                </span>
            </div>
        </div>
    </header>

    {{-- KONTEN UTAMA ADAPTIF --}}
    <main class="flex-1 relative flex items-center justify-center p-8 overflow-y-auto custom-scrollbar bg-transparent" style="background: transparent !important;">

        {{-- STATE: LOBBY --}}
  <template x-if="state.state === 'lobby'">
    <div class="text-center w-full max-w-6xl
                animate-fadeIn">

      {{-- Simbol Battle --}}
      <div class="mb-6 opacity-40">
        <i class="fas fa-fist-raised text-6xl text-indigo-500 animate-bounce"></i>
      </div>

      {{-- Counter sedang --}}
      <div class="flex items-center justify-center gap-4 mb-2">
        <div class="text-6xl font-black text-white tabular-nums drop-shadow-2xl"
             x-text="members.length"></div>
        <div class="text-left leading-none">
          <p class="text-lg font-black text-indigo-300 uppercase tracking-widest">Peserta</p>
          <p class="text-xs font-bold text-indigo-400/80 uppercase">Telah Bergabung</p>
        </div>
      </div>

      {{-- Info battle --}}
      <div class="flex items-center justify-center
                  gap-6 mb-10 text-indigo-300">
        <span>
          <span class="font-black text-white text-xl">
            {{ $room->total_questions }}
          </span>
          <span class="text-sm ml-1">soal</span>
        </span>
        <span class="text-indigo-600">•</span>
        <span>
          <span class="font-black text-white text-xl">
            {{ $room->duration_per_question }}
          </span>
          <span class="text-sm ml-1">detik/soal</span>
        </span>
        <span class="text-indigo-600">•</span>
        <span class="font-bold text-indigo-200 capitalize">
          {{ $room->mode === 'individual'
              ? 'Siswa vs Siswa'
              : ($room->mode === 'group'
                  ? 'Mode Grup'
                  : 'Kelas vs Kelas') }}
        </span>
      </div>

      {{-- Avatar grid — MAJESTIC GRID --}}
      <div class="flex flex-wrap justify-center gap-6 max-w-7xl mx-auto py-8 px-4 transition-all">
        <template x-for="m in members" :key="m.user_id">
          <div class="flex flex-col items-center gap-3 animate-popIn group">
            {{-- Avatar Wrapper dengan Glow --}}
            <div class="relative">
                <div class="absolute inset-0 bg-indigo-500 rounded-full blur-3xl opacity-10 group-hover:opacity-40 transition-opacity duration-700"></div>
                <div class="absolute -inset-1 bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 rounded-full blur opacity-0 group-hover:opacity-60 transition-opacity duration-500"></div>
                
                <div class="rounded-full overflow-hidden bg-slate-900 border-2 border-white/20 shadow-2xl relative z-10 transition-all duration-500 group-hover:scale-115 group-hover:border-indigo-400 group-hover:rotate-3 shadow-indigo-500/10"
                     :class="{
                        'w-24 h-24': members.length <= 12,
                        'w-20 h-20': members.length > 12 && members.length <= 24,
                        'w-16 h-16': members.length > 24 && members.length <= 48,
                        'w-12 h-12': members.length > 48
                     }">
                    <template x-if="m.is_avatar_seed && m.avatar_seed">
                        <div class="w-full h-full [&>svg]:w-full [&>svg]:h-full [&>svg]:block transition-transform duration-500 group-hover:scale-110"
                             x-html="typeof multiavatar === 'function' ? multiavatar(m.avatar_seed) : ''">
                        </div>
                    </template>
                    <template x-if="!m.is_avatar_seed && m.avatar_url">
                        <img :src="m.avatar_url" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    </template>
                </div>
                {{-- Status Badge (Ready) --}}
                <div class="absolute -bottom-1 -right-1 bg-emerald-500 w-8 h-8 rounded-full border-[3px] border-[#020617] flex items-center justify-center z-20 shadow-xl">
                    <i class="fas fa-check text-[10px] text-white"></i>
                </div>
            </div>
            <div class="text-center transition-all"
                 :class="{
                    'max-w-[140px]': members.length <= 24,
                    'max-w-[100px]': members.length > 24
                 }">
                <p class="text-white font-black uppercase tracking-tighter drop-shadow-2xl leading-none group-hover:text-indigo-200 transition-colors" 
                   :class="{
                      'text-xl': members.length <= 12,
                      'text-lg': members.length > 12 && members.length <= 24,
                      'text-sm': members.length > 24 && members.length <= 48,
                      'text-[10px]': members.length > 48
                   }"
                   x-text="m.name"></p>
                <div class="h-1 bg-indigo-500 mx-auto rounded-full mt-2 opacity-30 group-hover:w-full transition-all duration-700 shadow-[0_0_10px_rgba(99,102,241,0.5)]"
                     :class="members.length > 24 ? 'w-4' : 'w-8'"></div>
            </div>
          </div>
        </template>
      </div>

    </div>
  </template>

        {{-- STATE: PREVIEW --}}
        <template x-if="state.state === 'preview'">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 backdrop-blur-3xl animate-fadeIn">
                <div class="text-center">
                    <div class="w-40 h-40 mx-auto bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 rounded-full flex items-center justify-center mb-10 shadow-[0_0_70px_rgba(99,102,241,0.6)] animate-pulse">
                        <i class="fas fa-bolt text-7xl text-white"></i>
                    </div>
                    <h2 class="text-7xl font-black uppercase tracking-tighter text-white mb-6 drop-shadow-2xl">Bersiaplah!</h2>
                    <p class="text-3xl font-bold text-indigo-300">Pertanyaan <span class="text-white tabular-nums" x-text="(state.q_index??0)+1"></span> segera dimulai</p>
                    <div class="mt-8 flex justify-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-indigo-500 animate-bounce" style="animation-delay: 0.1s"></span>
                        <span class="w-3 h-3 rounded-full bg-purple-500 animate-bounce" style="animation-delay: 0.2s"></span>
                        <span class="w-3 h-3 rounded-full bg-pink-500 animate-bounce" style="animation-delay: 0.3s"></span>
                    </div>
                </div>
            </div>
        </template>

        {{-- STATE: QUESTION --}}
        <template x-if="state.state === 'question'">
            <div class="w-full max-w-7xl animate-fadeIn flex flex-col items-center">
                <template x-if="question">
                    <div class="w-full relative">
                        
                        {{-- Timer Kanan Atas (Absolute) --}}
                        <div class="absolute -top-10 -right-4 z-20 flex flex-col items-center justify-center w-32 h-32 glass-panel rounded-full border border-indigo-500/30 shadow-[0_0_30px_rgba(99,102,241,0.2)]">
                            <span class="text-4xl font-black tracking-tighter" :class="remainingTime <= 10 ? 'text-red-400 animate-pulse' : 'text-emerald-400'" x-text="remainingTime"></span>
                            <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest mt-1">Detik</span>
                        </div>

                        <div class="glass-panel p-8 md:p-12 rounded-3xl w-full mb-8 relative z-10 text-center">
                            <div class="prose prose-invert prose-2xl max-w-none text-white font-medium" x-html="question.question_text"></div>
                            
                            <template x-if="question.question_image">
                                <img :src="question.question_image" class="mt-8 max-h-[40vh] mx-auto rounded-2xl border-2 border-white/20 shadow-2xl">
                            </template>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="(opt, key) in question.options" :key="key">
                                <template x-if="opt.text || opt.image">
                                    <div class="glass-panel rounded-2xl p-6 flex items-center gap-6">
                                        <div class="w-14 h-14 shrink-0 rounded-xl bg-indigo-500/20 text-indigo-300 font-black text-2xl flex items-center justify-center border border-indigo-500/50 uppercase">
                                            <span x-text="key"></span>
                                        </div>
                                        <div class="text-xl font-medium text-gray-200">
                                            <p x-html="opt.text"></p>
                                            <template x-if="opt.image">
                                                <img :src="opt.image" class="mt-4 max-h-48 rounded-xl border border-white/10">
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- STATE: DISCUSSION --}}
        <template x-if="state.state === 'discussion'">
            <div class="w-full max-w-7xl animate-fadeIn">
                <div class="text-center mb-8">
                    <h2 class="text-4xl font-black text-white uppercase tracking-wider mb-2">Penjelasan Soal</h2>
                    <p class="text-lg text-indigo-300">Waktu habis! Mari kita lihat jawaban yang benar.</p>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    {{-- Jawaban Benar & Grafik Di Sini --}}
                    <div class="glass-panel p-8 rounded-3xl flex flex-col justify-center">
                        <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-6">Statistik Jawaban Siswa</h3>
                        
                        <div class="space-y-4">
                            <template x-for="(opt, key) in stats" :key="key">
                                <div class="relative">
                                    <div class="flex justify-between text-sm font-bold mb-1" :class="question?.correct_answer === key ? 'text-emerald-400' : 'text-gray-300'">
                                        <span class="uppercase">Pilihan <span x-text="key"></span> <i x-show="question?.correct_answer === key" class="fas fa-check-circle ml-1"></i></span>
                                        <span x-text="opt.count + ' siswa (' + opt.percent + '%)'"></span>
                                    </div>
                                    <div class="w-full h-6 bg-slate-800 rounded-full overflow-hidden">
                                        <div class="h-full transition-all duration-1000 ease-out" 
                                             :class="question?.correct_answer === key ? 'bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.5)]' : 'bg-indigo-500 opacity-50'"
                                             :style="'width: ' + opt.percent + '%'"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    {{-- Detail Soal --}}
                    <div class="glass-panel p-8 rounded-3xl" x-show="question">
                        <div class="prose prose-invert max-w-none text-white" x-html="question?.question_text"></div>
                        <div class="mt-6 p-4 rounded-xl border-2 border-emerald-500/50 bg-emerald-900/20">
                            <h4 class="text-emerald-400 font-black uppercase tracking-widest text-xs mb-2">Jawaban Benar (<span class="uppercase" x-text="question?.correct_answer"></span>)</h4>
                            <div class="text-emerald-50 font-medium" x-html="question?.options[question?.correct_answer]?.text || ''"></div>
                        </div>
                        <template x-if="question?.explanation">
                            <div class="mt-6 border-t border-white/10 pt-6">
                                <h4 class="text-indigo-300 font-black uppercase tracking-widest text-xs mb-2">Pembahasan</h4>
                                <div class="prose prose-sm prose-invert max-w-none" x-html="question.explanation"></div>
                            </div>
                        </template>
                    </div>

                </div>
            </template>
             {{-- STATE: LEADERBOARD --}}
  <template x-if="state.state === 'leaderboard'">
    <div class="animate-fadeIn px-4 mx-auto" :class="state.mode === 'group' ? 'w-full max-w-5xl' : 'w-full max-w-4xl'">

      <div class="text-center mb-8">
        <p class="text-xs text-amber-400 font-black uppercase tracking-[0.3em] mb-2">
          Soal <span x-text="(state.q_index??0)+1"></span> selesai
        </p>
        <h2 class="text-5xl font-black text-white uppercase tracking-tight"
            x-text="state.mode === 'group' ? 'Team Battle' : 'Leaderboard'">
        </h2>
      </div>

      <div class="grid grid-cols-1 gap-6">
        
        {{-- MODE INDIVIDUAL --}}
        <template x-if="state.mode !== 'group'">
          <template x-for="(s, idx) in leaderboard" :key="s.user_id">
            <div class="glass-panel p-4 rounded-[2rem] flex items-center gap-6 border-2 transition-all 
                         duration-1000 ease-in-out relative overflow-hidden backdrop-blur-3xl hover:shadow-[0_0_30px_rgba(99,102,241,0.4)]"
                 :class="{
                   'bg-amber-500/15 border-amber-400/30 shadow-[0_0_30px_rgba(251,191,36,0.3)]': idx === 0 && rankChanges[s.user_id] !== 'up',
                   'bg-slate-300/15 border-slate-300/30 shadow-[0_0_30px_rgba(203,213,225,0.3)]': idx === 1 && rankChanges[s.user_id] !== 'up',
                   'bg-orange-700/25 border-orange-500/30 shadow-[0_0_30px_rgba(249,115,22,0.3)]': idx === 2 && rankChanges[s.user_id] !== 'up',
                   'bg-white/10 border-white/20': idx > 2 && rankChanges[s.user_id] !== 'up',
                   'bg-indigo-500/30 border-emerald-400 shadow-[0_0_40px_rgba(16,185,129,0.5)] scale-110 z-50': rankChanges[s.user_id] === 'up'
                 }"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 translate-x-12"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 :style="'transition-delay: ' + (idx * 60) + 'ms'">

              {{-- Rank badge --}}
              <div class="w-10 text-center font-black text-lg shrink-0"
                   :class="{
                     'text-amber-400': idx === 0,
                     'text-slate-300': idx === 1,
                     'text-orange-500': idx === 2,
                     'text-gray-500': idx > 2
                   }"
                   x-text="'#' + (idx + 1)"></div>

              {{-- Rank change arrow --}}
              <div class="w-6 text-center text-base shrink-0">
                <template x-if="rankChanges[s.user_id] === 'up'">
                  <span class="text-emerald-400 font-black animate-bounce inline-block">↑</span>
                </template>
                <template x-if="rankChanges[s.user_id] === 'down'">
                  <span class="text-red-400 font-black inline-block">↓</span>
                </template>
                <template x-if="rankChanges[s.user_id] === 'same'">
                  <span class="text-gray-600 inline-block">–</span>
                </template>
                <template x-if="rankChanges[s.user_id] === 'new'">
                  <span class="text-purple-400 font-black inline-block">★</span>
                </template>
              </div>

              {{-- Avatar --}}
              <div class="w-14 h-14 rounded-full overflow-hidden bg-gradient-to-tr from-indigo-500 to-purple-500 p-0.5 shrink-0 shadow-md">
                <div class="w-full h-full rounded-full overflow-hidden bg-slate-800 border-2 border-slate-900 relative">
                  <template x-if="s.is_avatar_seed && s.avatar_seed">
                    <div class="w-full h-full overflow-hidden [&>svg]:w-full [&>svg]:h-full"
                         x-html="typeof multiavatar === 'function' ? multiavatar(s.avatar_seed) : ''">
                    </div>
                  </template>
                  <template x-if="!s.is_avatar_seed && s.avatar_url">
                    <img :src="s.avatar_url" class="w-full h-full object-cover">
                  </template>
                  <template x-if="!s.is_avatar_seed && !s.avatar_url">
                    <div class="w-full h-full bg-slate-700 flex items-center justify-center text-white font-black"
                         x-text="s.name?.charAt(0)"></div>
                  </template>
                </div>
              </div>

              {{-- Nama --}}
              <div class="flex-1 min-w-0">
                <p class="font-black text-white text-xl uppercase tracking-tighter drop-shadow-md leading-none" x-text="s.name"></p>
                <template x-if="s.group_label">
                  <span class="text-xs px-2 py-0.5 rounded-full bg-white/10 text-gray-300" x-text="s.group_label"></span>
                </template>
              </div>

              {{-- Skor --}}
              <div class="shrink-0 relative flex flex-col items-end justify-center">
                 <div class="px-6 py-2.5 rounded-2xl bg-gradient-to-br from-indigo-500/80 to-violet-700/80 backdrop-blur-xl shadow-[0_0_40px_rgba(124,58,237,0.4)] border-2 border-violet-400/30 min-w-[110px] text-center"
                      :class="rankChanges[s.user_id] === 'up' ? 'scale-110 shadow-[0_0_60_rgba(16,185,129,0.5)]' : ''">
                   <p class="text-3xl font-black tabular-nums leading-none drop-shadow-xl"
                      :class="idx === 0 ? 'text-amber-300' : 'text-white'"
                      x-text="s.total_score.toLocaleString()"></p>
                   <p class="text-[10px] text-indigo-100 font-black tracking-[0.2em] leading-tight mt-1 opacity-80 uppercase">PTS</p>
                 </div>
              </div>
            </div>
          </template>
        </template>

        {{-- MODE GRUP --}}
        <template x-if="state.mode === 'group'">
          <div class="flex flex-col gap-8">
            <template x-for="(g, idx) in groupScores" :key="g.group_label">
              <div class="glass-panel p-8 rounded-[4rem] flex items-center justify-between border-4 transition-all duration-1000 min-h-[140px]"
                   :class="{
                     'bg-blue-600/10 border-blue-500/40 shadow-[0_0_40px_rgba(59,130,246,0.3)]': getGroupColorKey(g.name || g.group_label, idx) === 'blue',
                     'bg-rose-600/10 border-rose-500/40 shadow-[0_0_40px_rgba(244,63,94,0.3)]': getGroupColorKey(g.name || g.group_label, idx) === 'rose',
                     'bg-amber-600/10 border-amber-500/40 shadow-[0_0_40px_rgba(245,158,11,0.3)]': getGroupColorKey(g.name || g.group_label, idx) === 'amber',
                     'bg-emerald-600/10 border-emerald-500/40 shadow-[0_0_40px_rgba(16,185,129,0.3)]': getGroupColorKey(g.name || g.group_label, idx) === 'emerald'
                   }"
                   x-transition:enter="transition ease-out duration-700"
                   x-transition:enter-start="opacity-0 translateY-12"
                   x-transition:enter-end="opacity-100 translateY-0">
                
                <div class="flex items-center gap-12">
                   <div class="w-24 h-24 rounded-[2rem] flex items-center justify-center text-5xl font-black text-white shadow-2xl shrink-0"
                        :class="{
                          'bg-blue-500': getGroupColorKey(g.name || g.group_label, idx) === 'blue',
                          'bg-rose-500': getGroupColorKey(g.name || g.group_label, idx) === 'rose',
                          'bg-amber-500': getGroupColorKey(g.name || g.group_label, idx) === 'amber',
                          'bg-emerald-500': getGroupColorKey(g.name || g.group_label, idx) === 'emerald'
                        }"
                        x-text="'#' + (idx + 1)"></div>
                   <h3 class="text-7xl font-black uppercase text-white tracking-tighter drop-shadow-2xl truncate max-w-[600px]" x-text="g.name || g.group_label"></h3>
                </div>
  
                <div class="text-right shrink-0">
                   <p class="text-9xl font-black text-white tabular-nums tracking-tighter drop-shadow-2xl" x-text="g.total_score.toLocaleString()"></p>
                   <p class="text-sm font-black text-white/40 uppercase tracking-[0.5em] mt-2">TOTAL TEAM POINTS</p>
                </div>
              </div>
            </template>
          </div>
        </template>
  
      </div>
    </div>
  </template>

      </div>
    </div>
  </template>

  {{-- STATE: FINISH --}}
  <template x-if="state.state === 'finish'">
    <div class="text-center w-full max-w-6xl">

      <template x-if="state.mode !== 'group'">
        <div>
          <h2 class="text-5xl font-black uppercase tracking-tight text-white mb-2 animate-fadeIn">
            Battle Selesai!
          </h2>
          <p class="text-lg text-indigo-300 mb-10 animate-fadeIn">Pengumuman pemenang...</p>
        </div>
      </template>

      {{-- ─── GROUP MODE REVEAL (3-2-1 Sequence) ─── --}}
      <template x-if="state.mode === 'group'">
        <div class="w-full flex flex-col items-center">
            
            {{-- COUNTDOWN OVERLAY (Group Mode) --}}
            <template x-if="podiumStep === 1 || podiumStep === 3 || podiumStep === 5">
                <div class="flex flex-col items-center justify-center py-16 animate-fadeIn">
                    <p class="text-3xl text-indigo-300 font-black uppercase tracking-[0.5em] mb-8"
                       x-text="podiumStep === 1 ? 'Juara ke-3...' : podiumStep === 3 ? 'Juara ke-2...' : '🏆 SANG JUARA!'"></p>
                    <div class="text-[200px] font-black text-white leading-none tabular-nums drop-shadow-[0_0_80px_rgba(244,63,94,0.6)] animate-pulse"
                         x-text="podiumCountdown"></div>
                </div>
            </template>

            {{-- PODIUM REVEAL --}}
            <div class="flex items-end justify-center gap-10 min-h-[500px] w-full max-w-6xl mt-12 px-6">
                
                {{-- Juara 2 (Step >= 4) --}}
                <template x-if="groupScores.length >= 2 && podiumStep >= 4">
                    <div class="flex flex-col items-center w-1/3" x-transition:enter="transition ease-out duration-1000" x-transition:enter-start="opacity-0 translateY-20 scale-90" x-transition:enter-end="opacity-100 translateY-0 scale-100">
                        <div class="text-6xl mb-6">🥈</div>
                        <div class="glass-panel w-full p-10 rounded-[3rem] border-4 flex flex-col items-center gap-4 shadow-lg transition-all duration-1000"
                             :class="{
                                'bg-gradient-to-b from-blue-600/20 to-slate-950 border-blue-500/50 shadow-[0_0_50px_rgba(59,130,246,0.3)]': getGroupColorKey(groupScores[1].name || groupScores[1].group_label, 1) === 'blue',
                                'bg-gradient-to-b from-rose-600/20 to-slate-950 border-rose-500/50 shadow-[0_0_50px_rgba(244,63,94,0.3)]': getGroupColorKey(groupScores[1].name || groupScores[1].group_label, 1) === 'rose',
                                'bg-gradient-to-b from-amber-600/20 to-slate-950 border-amber-500/50 shadow-[0_0_50px_rgba(245,158,11,0.3)]': getGroupColorKey(groupScores[1].name || groupScores[1].group_label, 1) === 'amber',
                                'bg-gradient-to-b from-emerald-600/20 to-slate-950 border-emerald-500/50 shadow-[0_0_50px_rgba(16,185,129,0.3)]': getGroupColorKey(groupScores[1].name || groupScores[1].group_label, 1) === 'emerald'
                             }">
                            <h3 class="text-6xl font-black uppercase text-white tracking-tighter text-center leading-none drop-shadow-lg" x-text="groupScores[1].name || groupScores[1].group_label"></h3>
                            <div class="h-px w-12 bg-white/10 my-2"></div>
                            <p class="text-5xl font-black text-white tabular-nums" x-text="groupScores[1].total_score.toLocaleString()"></p>
                        </div>
                    </div>
                </template>

                {{-- Juara 1 (Step >= 6) --}}
                <template x-if="groupScores.length >= 1 && podiumStep >= 6">
                    <div class="flex flex-col items-center w-2/5 -mt-20 z-10" x-transition:enter="transition ease-out duration-1000" x-transition:enter-start="opacity-0 translateY-20 scale-90" x-transition:enter-end="opacity-100 translateY-0 scale-110">
                        <div class="text-9xl mb-8 animate-bounce drop-shadow-[0_0_40px_rgba(251,191,36,0.6)]">🏆</div>
                        <div class="glass-panel w-full p-14 rounded-[4rem] border-4 flex flex-col items-center gap-6 shadow-xl transition-all duration-1000"
                             :class="{
                                'bg-gradient-to-b from-blue-600/30 to-slate-950 border-blue-400 shadow-[0_0_100px_rgba(59,130,246,0.4)]': getGroupColorKey(groupScores[0].name || groupScores[0].group_label, 0) === 'blue',
                                'bg-gradient-to-b from-rose-600/30 to-slate-950 border-rose-400 shadow-[0_0_100px_rgba(244,63,94,0.4)]': getGroupColorKey(groupScores[0].name || groupScores[0].group_label, 0) === 'rose',
                                'bg-gradient-to-b from-amber-600/30 to-slate-950 border-amber-400 shadow-[0_0_100px_rgba(245,158,11,0.4)]': getGroupColorKey(groupScores[0].name || groupScores[0].group_label, 0) === 'amber',
                                'bg-gradient-to-b from-emerald-600/30 to-slate-950 border-emerald-400 shadow-[0_0_100px_rgba(16,185,129,0.4)]': getGroupColorKey(groupScores[0].name || groupScores[0].group_label, 0) === 'emerald'
                             }">
                            <h3 class="text-8xl font-black uppercase text-white tracking-tighter text-center leading-none drop-shadow-lg" x-text="groupScores[0].name || groupScores[0].group_label"></h3>
                            <div class="h-px w-20 bg-blue-400/30 my-2"></div>
                            <p class="text-7xl font-black text-white tabular-nums" x-text="groupScores[0].total_score.toLocaleString()"></p>
                        </div>
                    </div>
                </template>

                {{-- Juara 3 (Step >= 2) --}}
                <template x-if="groupScores.length >= 3 && podiumStep >= 2">
                    <div class="flex flex-col items-center w-1/4" x-transition:enter="transition ease-out duration-1000" x-transition:enter-start="opacity-0 translateY-20 scale-90" x-transition:enter-end="opacity-100 translateY-0 scale-100">
                        <div class="text-5xl mb-4">🥉</div>
                        <div class="glass-panel w-full p-8 rounded-[2.5rem] border-4 flex flex-col items-center gap-4 shadow-md transition-all duration-1000"
                             :class="{
                                'bg-gradient-to-b from-blue-600/20 to-slate-950 border-blue-500/50 shadow-[0_0_50px_rgba(59,130,246,0.2)]': getGroupColorKey(groupScores[2].name || groupScores[2].group_label, 2) === 'blue',
                                'bg-gradient-to-b from-rose-600/20 to-slate-950 border-rose-500/50 shadow-[0_0_50px_rgba(244,63,94,0.2)]': getGroupColorKey(groupScores[2].name || groupScores[2].group_label, 2) === 'rose',
                                'bg-gradient-to-b from-amber-600/20 to-slate-950 border-amber-500/50 shadow-[0_0_50px_rgba(245,158,11,0.2)]': getGroupColorKey(groupScores[2].name || groupScores[2].group_label, 2) === 'amber',
                                'bg-gradient-to-b from-emerald-600/20 to-slate-950 border-emerald-500/50 shadow-[0_0_50px_rgba(16,185,129,0.2)]': getGroupColorKey(groupScores[2].name || groupScores[2].group_label, 2) === 'emerald'
                             }">
                            <h3 class="text-5xl font-black uppercase text-white tracking-tighter text-center leading-none drop-shadow-lg" x-text="groupScores[2].name || groupScores[2].group_label"></h3>
                            <div class="h-px w-10 bg-white/10 my-2"></div>
                            <p class="text-4xl font-black text-white tabular-nums" x-text="groupScores[2].total_score.toLocaleString()"></p>
                        </div>
                    </div>
                </template>

            </div>
        </div>
      </template>

      {{-- COUNTDOWN OVERLAY (Individual Mode) --}}
      <template x-if="state.mode !== 'group' && (podiumStep === 1 || podiumStep === 3 || podiumStep === 5)">
        <div class="flex flex-col items-center
                     justify-center py-16">
          <p class="text-2xl text-indigo-300 font-black
                     uppercase tracking-widest mb-6"
             x-text="podiumStep === 1
                      ? 'Juara ke-3...'
                      : podiumStep === 3
                        ? 'Juara ke-2...'
                        : '🏆 Juara ke-1!'">
          </p>
          <div class="text-[180px] font-black text-white
                       leading-none tabular-nums
                       drop-shadow-[0_0_60px_rgba(99,102,241,0.8)]
                       animate-pulse"
               x-text="podiumCountdown">
          </div>
        </div>
      </template>

      {{-- PODIUM WRAPPER (Individual Mode) --}}
      <template x-if="state.mode !== 'group' && podiumStep >= 2">
        <div class="flex items-end justify-center
                     gap-6 min-h-[420px] px-4">

          {{-- ─── JUARA 2 (reveal saat step >= 4) ─── --}}
          <template x-if="topMembers(3)[1] && podiumStep >= 4">
            <div class="relative flex flex-col items-center
                         w-1/4"
                 x-transition:enter="transition ease-out duration-700"
                 x-transition:enter-start="opacity-0 translateY-16"
                 x-transition:enter-end="opacity-100 translateY-0">

              <div class="text-3xl mb-2">🥈</div>
              <div class="w-24 h-24 rounded-full
                           overflow-hidden border-4
                           border-slate-300 mb-4 bg-slate-800
                           shadow-[0_0_30px_rgba(203,213,225,0.3)]
                           z-10 relative">
                <template x-if="topMembers(3)[1].is_avatar_seed
                                  && topMembers(3)[1].avatar_seed">
                  <div class="w-full h-full overflow-hidden
                               [&>svg]:w-full [&>svg]:h-full"
                       x-html="typeof multiavatar !== 'undefined'
                                ? multiavatar(topMembers(3)[1].avatar_seed)
                                : ''">
                  </div>
                </template>
                <template x-if="!topMembers(3)[1].is_avatar_seed
                                  && topMembers(3)[1].avatar_url">
                  <img :src="topMembers(3)[1].avatar_url"
                       class="w-full h-full object-cover">
                </template>
                <template x-if="!topMembers(3)[1].is_avatar_seed
                                  && !topMembers(3)[1].avatar_url">
                  <div class="w-full h-full bg-slate-700
                               flex items-center justify-center
                               text-white font-black text-2xl"
                       x-text="topMembers(3)[1].name?.charAt(0)">
                  </div>
                </template>
              </div>

              <div class="glass-panel w-full pt-12 pb-6
                           px-4 rounded-t-3xl -mt-12
                           h-[200px] flex flex-col
                           justify-end border-slate-400/30
                           bg-gradient-to-t from-slate-900
                           via-slate-800/80 to-slate-800/30">
                <p class="font-black text-xl text-white
                            truncate"
                   x-text="topMembers(3)[1].name.split(' ')[0]">
                </p>
                <p class="text-slate-300 font-bold text-sm mt-1">
                  <span x-text="scores[topMembers(3)[1].user_id]
                                 ?.total_score?.toLocaleString()">
                  </span> PTS
                </p>
              </div>
            </div>
          </template>

          {{-- ─── JUARA 1 (reveal saat step >= 6) ─── --}}
          <template x-if="topMembers(3)[0] && podiumStep >= 6">
            <div class="relative flex flex-col items-center
                         w-1/3"
                 x-transition:enter="transition ease-out duration-1000"
                 x-transition:enter-start="opacity-0 scale-75"
                 x-transition:enter-end="opacity-100 scale-100">

              <div class="text-5xl mb-2
                           drop-shadow-[0_0_20px_rgba(251,191,36,0.8)]
                           animate-bounce">
                👑
              </div>
              <div class="w-36 h-36 rounded-full
                           overflow-hidden border-4
                           border-amber-400 mb-4 bg-slate-800
                           shadow-[0_0_60px_rgba(251,191,36,0.6)]
                           z-10 relative">
                <template x-if="topMembers(3)[0].is_avatar_seed
                                  && topMembers(3)[0].avatar_seed">
                  <div class="w-full h-full overflow-hidden
                               [&>svg]:w-full [&>svg]:h-full"
                       x-html="typeof multiavatar !== 'undefined'
                                ? multiavatar(topMembers(3)[0].avatar_seed)
                                : ''">
                  </div>
                </template>
                <template x-if="!topMembers(3)[0].is_avatar_seed
                                  && topMembers(3)[0].avatar_url">
                  <img :src="topMembers(3)[0].avatar_url"
                       class="w-full h-full object-cover">
                </template>
                <template x-if="!topMembers(3)[0].is_avatar_seed
                                  && !topMembers(3)[0].avatar_url">
                  <div class="w-full h-full bg-slate-700
                               flex items-center justify-center
                               text-white font-black text-3xl"
                       x-text="topMembers(3)[0].name?.charAt(0)">
                  </div>
                </template>
              </div>

              <div class="glass-panel w-full pt-16 pb-6
                           px-4 rounded-t-3xl -mt-16
                           h-[280px] flex flex-col
                           justify-end border-amber-500/40
                           bg-gradient-to-t from-amber-900/60
                           via-amber-800/30 to-amber-800/10
                           shadow-[0_-20px_60px_rgba(251,191,36,0.15)]">
                <p class="font-black text-3xl text-white
                            truncate"
                   x-text="topMembers(3)[0].name.split(' ')[0]">
                </p>
                <p class="text-amber-400 font-bold text-xl mt-1">
                  <span x-text="scores[topMembers(3)[0].user_id]
                                 ?.total_score?.toLocaleString()">
                  </span> PTS
                </p>
                <p class="text-amber-300/60 text-xs mt-2
                            font-bold uppercase tracking-widest">
                  Benar: <span x-text="scores[topMembers(3)[0].user_id]
                                        ?.correct ?? 0"></span>
                  soal
                </p>
              </div>
            </div>
          </template>

          {{-- ─── JUARA 3 (reveal saat step >= 2) ─── --}}
          <template x-if="topMembers(3)[2] && podiumStep >= 2">
            <div class="relative flex flex-col items-center
                         w-1/4"
                 x-transition:enter="transition ease-out duration-700"
                 x-transition:enter-start="opacity-0 translateY-16"
                 x-transition:enter-end="opacity-100 translateY-0">

              <div class="text-2xl mb-2">🥉</div>
              <div class="w-20 h-20 rounded-full
                           overflow-hidden border-4
                           border-orange-400 mb-4 bg-slate-800
                           shadow-[0_0_30px_rgba(251,146,60,0.3)]
                           z-10 relative">
                <template x-if="topMembers(3)[2].is_avatar_seed
                                  && topMembers(3)[2].avatar_seed">
                  <div class="w-full h-full overflow-hidden
                               [&>svg]:w-full [&>svg]:h-full"
                       x-html="typeof multiavatar !== 'undefined'
                                ? multiavatar(topMembers(3)[2].avatar_seed)
                                : ''">
                  </div>
                </template>
                <template x-if="!topMembers(3)[2].is_avatar_seed
                                  && topMembers(3)[2].avatar_url">
                  <img :src="topMembers(3)[2].avatar_url"
                       class="w-full h-full object-cover">
                </template>
                <template x-if="!topMembers(3)[2].is_avatar_seed
                                  && !topMembers(3)[2].avatar_url">
                  <div class="w-full h-full bg-slate-700
                               flex items-center justify-center
                               text-white font-black text-xl"
                       x-text="topMembers(3)[2].name?.charAt(0)">
                  </div>
                </template>
              </div>

              <div class="glass-panel w-full pt-10 pb-6
                           px-4 rounded-t-3xl -mt-10
                           h-[170px] flex flex-col
                           justify-end border-orange-700/40
                           bg-gradient-to-t from-orange-950
                           via-orange-900/50 to-orange-900/20">
                <p class="font-black text-lg text-white
                            truncate"
                   x-text="topMembers(3)[2].name.split(' ')[0]">
                </p>
                <p class="text-orange-400 font-bold text-sm mt-1">
                  <span x-text="scores[topMembers(3)[2].user_id]
                                 ?.total_score?.toLocaleString()">
                  </span> PTS
                </p>
              </div>
            </div>
          </template>

        </div>
      </template>

      {{-- Placeholder saat podiumStep === 0 (loading awal) --}}
      <template x-if="podiumStep === 0">
        <div class="flex items-center justify-center
                     py-20 text-indigo-400">
          <div class="w-12 h-12 border-4 border-indigo-500
                       border-t-transparent rounded-full
                       animate-spin"></div>
        </div>
      </template>

      {{-- Reward fisik (jika ada) --}}
      @if($room->reward_physical)
      <div class="mt-10 inline-flex items-center gap-3
                   px-6 py-3 bg-amber-500/10
                   border border-amber-500/30
                   rounded-2xl animate-fadeIn">
        <span class="text-2xl">🎁</span>
        <div class="text-left">
          <p class="text-xs text-amber-400 font-black
                     uppercase tracking-widest">
            Hadiah Fisik
          </p>
          <p class="text-white font-bold">
            {{ $room->reward_physical }}
          </p>
        </div>
      </div>
      @endif

      <div class="mt-6">
        <a href="{{ route('admin.gamification.arena.debriefing',
                    $room->token) }}"
           class="inline-flex items-center gap-2 px-6 py-3
                  bg-white/10 hover:bg-white/20
                  border border-white/20 rounded-2xl
                  text-white font-bold text-sm
                  transition-colors">
          <i class="fas fa-chart-bar"></i>
          Lihat Rekap Lengkap
        </a>
      </div>

    </div>
  </template>
        
    </main>

    <style>
        @keyframes slideInRight {
            from { transform: translateX(50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out forwards;
        }
        @keyframes countUp {
            from { transform: scale(1.2); color: #34d399; }
            to   { transform: scale(1); }
        }
        .score-counting {
            animation: countUp 0.3s ease-out;
        }
    </style>

    <script>
    function arenaDisplay(token) {
        return {
            token: token,
            state: {
                mode: '{{ $room->mode }}',
                state: '{{ $room->status }}',
                q_index: {{ $room->current_q_index ?? 0 }},
                q_total: {{ $room->total_questions }}
            },
            members: [],
            scores: {},
            groupScores: [],
            prevGroupScores: {},
            question: null,
            stats: {},
            remainingTime: 0,
            timerInterval: null,
            pollInterval: null,
            confettiFired: false,
            
            leaderboard: [],
            prevRanks: {},
            rankChanges: {},
            scoreFlash: {},
            animatingScores: {},
            isAnimating: false,
            
            scoresMap: {},
            podiumStep: 0,
            podiumCountdown: 0,
            podiumInterval: null,
            serverDrift: 0,

            getGroupColorKey(name, idx) {
                const n = (name || '').toLowerCase();
                if (n.includes('biru')) return 'blue';
                if (n.includes('merah') || n.includes('rose')) return 'rose';
                if (n.includes('kuning') || n.includes('amber')) return 'amber';
                if (n.includes('hijau') || n.includes('emerald')) return 'emerald';
                
                // Fallback by index
                const fallbacks = ['blue', 'rose', 'amber', 'emerald'];
                return fallbacks[idx % 4];
            },

            initDisplay() {
                this.pollData();
                this.pollInterval = setInterval(() => this.pollData(), 1500); // Poll proyektor 1.5s
                
                this.timerInterval = setInterval(() => {
                    if (this.state.state === 'question' && this.state.question_started_at) {
                        const correctedNow = Math.floor(Date.now() / 1000) + this.serverDrift;
                        const elapsed = correctedNow - this.state.question_started_at;
                        const dur = this.state.question_duration || 0;
                        const left = dur - elapsed;
                        this.remainingTime = left > 0 ? left : 0;
                    }
                }, 1000);
            },

            updateLeaderboard(newScores) {
                if (this.isAnimating) return;

                const sorted = [...newScores].sort((a, b) => b.total_score - a.total_score);

                const hasChanges = sorted.some(s => {
                    const prev = this.leaderboard.find(x => x.user_id === s.user_id);
                    return prev && prev.total_score !== s.total_score;
                });

                if (!this.leaderboard || this.leaderboard.length === 0) {
                    this.leaderboard = sorted.slice(0, 5);
                    this.isAnimating = false;
                    return;
                }

                if (!hasChanges) {
                    this.leaderboard = sorted.slice(0, 5);
                    return;
                }

                this.isAnimating = true;

                // FASE 1: Data Prep
                const oldScores = {};
                this.leaderboard.forEach(s => {
                    oldScores[s.user_id] = s.total_score;
                });

                let currentLeaderboard = [...this.leaderboard];
                
                // Tambahkan entry baru
                sorted.slice(0, 5).forEach(s => {
                    if (!currentLeaderboard.find(x => x.user_id === s.user_id)) {
                        currentLeaderboard.push({ ...s, total_score: 0 });
                        oldScores[s.user_id] = 0;
                    }
                });

                // FASE 2: Count-Up (1.5s)
                const duration = 1500;
                const fps = 30;
                const steps = Math.floor(duration / (1000 / fps));
                let step = 0;

                const countUpInterval = setInterval(() => {
                    step++;
                    const progress = step / steps;
                    const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic

                    // Update total_score pada masing-masing user_id di posisi yg sama
                    this.leaderboard = currentLeaderboard.map(s => {
                        const sNew = sorted.find(x => x.user_id === s.user_id);
                        const targetScore = sNew ? sNew.total_score : 0;
                        const startScore = oldScores[s.user_id] || 0;
                        const currScore = Math.round(startScore + ((targetScore - startScore) * eased));

                        return { ...s, total_score: currScore };
                    });

                    if (step >= steps) {
                        this.leaderboard = sorted.slice(0, 5);
                        clearInterval(countUpInterval);
                        this.isAnimating = false;
                    }
                }, 1000 / fps);
            },

            updateGroupLeaderboard(newGroupScores) {
                if (this.isAnimatingGroup) return;

                const hasChanges = newGroupScores.some(g => {
                    const prev = this.groupScores.find(x => x.group_label === g.group_label);
                    return prev && prev.total_score !== g.total_score;
                });

                if (!hasChanges && this.groupScores.length > 0) {
                    this.groupScores = newGroupScores;
                    return;
                }

                this.isAnimatingGroup = true;

                const oldScores = {};
                this.groupScores.forEach(g => {
                    oldScores[g.group_label] = g.total_score;
                });

                const duration = 1500;
                const fps = 30;
                const steps = Math.floor(duration / (1000 / fps));
                let step = 0;

                const countUpInterval = setInterval(() => {
                    step++;
                    const progress = step / steps;
                    const eased = 1 - Math.pow(1 - progress, 3);

                    this.groupScores = newGroupScores.map(g => {
                        const start = oldScores[g.group_label] || 0;
                        const target = g.total_score;
                        const curr = Math.round(start + ((target - start) * eased));
                        return { ...g, total_score: curr };
                    });

                    if (step >= steps) {
                        this.groupScores = newGroupScores;
                        clearInterval(countUpInterval);
                        this.isAnimatingGroup = false;
                    }
                }, 1000 / fps);
            },

            initGroupPodium() {
                this.podiumStep = 1; // Show Countdown 3rd
                this.podiumCountdown = 3;
                const cd3 = setInterval(() => {
                    this.podiumCountdown--;
                    if (this.podiumCountdown <= 0) {
                        clearInterval(cd3);
                        this.podiumStep = 2; // Reveal 3rd
                        
                        setTimeout(() => {
                            this.podiumStep = 3; // Show Countdown 2nd
                            this.podiumCountdown = 3;
                            const cd2 = setInterval(() => {
                                this.podiumCountdown--;
                                if (this.podiumCountdown <= 0) {
                                    clearInterval(cd2);
                                    this.podiumStep = 4; // Reveal 2nd
                                    
                                    setTimeout(() => {
                                        this.podiumStep = 5; // Show Countdown 1st
                                        this.podiumCountdown = 3;
                                        const cd1 = setInterval(() => {
                                            this.podiumCountdown--;
                                            if (this.podiumCountdown <= 0) {
                                                clearInterval(cd1);
                                                this.podiumStep = 6; // Reveal 1st
                                                this.fireConfetti();
                                            }
                                        }, 1000);
                                    }, 2000);
                                }
                            }, 1000);
                        }, 2000);
                    }
                }, 1000);
            },

            initPodium() {
                if (this.podiumStep > 0) return; // sudah berjalan
                this.podiumStep     = 0;

                if (this.state.mode === 'group') {
                    this.initGroupPodium();
                    return;
                }

                setTimeout(() => {
                    this.podiumStep = 1; // show countdown
                    this.podiumCountdown = 3;
                    const cd3 = setInterval(() => {
                        this.podiumCountdown--;
                        if (this.podiumCountdown <= 0) {
                            clearInterval(cd3);
                            this.podiumStep = 2; // reveal juara 3

                            setTimeout(() => {
                                this.podiumStep = 3;
                                this.podiumCountdown = 3;
                                const cd2 = setInterval(() => {
                                    this.podiumCountdown--;
                                    if (this.podiumCountdown <= 0) {
                                        clearInterval(cd2);
                                        this.podiumStep = 4; // reveal juara 2

                                        setTimeout(() => {
                                            this.podiumStep = 5;
                                            this.podiumCountdown = 3;
                                            const cd1 = setInterval(() => {
                                                this.podiumCountdown--;
                                                if (this.podiumCountdown <= 0) {
                                                    clearInterval(cd1);
                                                    this.podiumStep = 6; // reveal juara 1
                                                    setTimeout(() => {
                                                        this.fireConfetti();
                                                    }, 500);
                                                }
                                            }, 1000);
                                        }, 2500);
                                    }
                                }, 1000);
                            }, 2500);
                        }
                    }, 1000);
                }, 1000);
            },

            async pollData() {
                try {
                    // Try Static Mirror first
                    const resMirror = await fetch(`/battle-mirror/${this.token}.json?t=${Date.now()}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (resMirror.ok) {
                        const data = await resMirror.json();
                        this.applyData(data, resMirror);
                        return;
                    }

                    // Fallback to PHP
                    const res = await fetch('{{ route('admin.gamification.arena.display.data', $room->token) }}', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        this.applyData(data, res);
                    }
                } catch(e) {
                    console.error("Display poll error", e);
                }
            },

            applyData(data, res) {
                // Sync Drift (Authoritative via HTTP Header) - HANYA HITUNG SEKALI
                if (this.serverDrift === 0 && res) {
                    const sDate = res.headers.get('Date');
                    if (sDate) {
                        this.serverDrift = Math.floor(new Date(sDate).getTime() / 1000) - Math.floor(Date.now() / 1000);
                    }
                }

                this.state = data.state ?? {};
                this.members = data.members ?? [];
                
                let smap = {};
                if (data.scores) {
                    data.scores.forEach(s => smap[s.user_id] = s);
                }
                this.scoresMap = smap;
                this.scores = data.scores ?? [];
                
                this.question = data.question;
                this.stats = data.stats ?? {};

                if (data.scores && this.state.state !== 'finish') {
                    this.updateLeaderboard(data.scores);
                }
                
                if (data.group_scores && this.state.state !== 'finish') {
                    this.updateGroupLeaderboard(data.group_scores);
                }

                if (this.state.state === 'finish') {
                    clearInterval(this.pollInterval);
                    if (!this.confettiFired) {
                        this.confettiFired = true;
                        this.initPodium();
                    }
                }
            },
            
            topMembers(limit) {
                return [...this.members].sort((a,b) => {
                    let rA = this.scoresMap[a.user_id]?.rank || 9999;
                    let rB = this.scoresMap[b.user_id]?.rank || 9999;
                    return rA - rB;
                }).slice(0, limit);
            },
            
            fireConfetti() {
                const duration = 5 * 1000;
                const animationEnd = Date.now() + duration;
                
                let colors = ['#6366f1', '#a855f7', '#ec4899'];
                if (this.state.mode === 'group' && this.groupScores.length > 0) {
                    const winner = [...this.groupScores].sort((a,b) => b.total_score - a.total_score)[0];
                    if (winner.group_label === 'Merah') colors = ['#f43f5e', '#fb7185', '#fda4af'];
                    if (winner.group_label === 'Biru') colors = ['#3b82f6', '#60a5fa', '#93c5fd'];
                }

                const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 100, colors: colors };

                function randomInRange(min, max) {
                    return Math.random() * (max - min) + min;
                }

                const interval = setInterval(function() {
                    const timeLeft = animationEnd - Date.now();
                    if (timeLeft <= 0) {
                        return clearInterval(interval);
                    }
                    const particleCount = 50 * (timeLeft / duration);
                    confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
                    confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
                }, 250);
            }
        }
    }
    </script>
</body>
</html>
