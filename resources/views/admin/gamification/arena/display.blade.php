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
            backdrop-filter: blur(16px) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6) !important;
            transform: translateZ(0); /* Force GPU */
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

        /* --- PODIUM STYLES --- */
        .podium-stage {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 2rem;
            padding-bottom: 2rem;
            perspective: 1000px;
        }
        .pedestal {
            width: 250px;
            border-radius: 2rem 2rem 1rem 1rem;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding-bottom: 2rem;
            border: 2px solid rgba(255,255,255,0.1);
            transition: all 1s ease-out;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            will-change: transform, opacity;
            transform: translateZ(0);
        }
        .pedestal-1 { 
            height: 320px; 
            z-index: 10;
            background: linear-gradient(to bottom, rgba(251, 191, 36, 0.2), #0f172a);
            border-top: 4px solid #fbbf24;
        }
        .pedestal-2 { 
            height: 240px; 
            background: linear-gradient(to bottom, rgba(203, 213, 225, 0.2), #0f172a);
            border-top: 4px solid #cbd5e1;
        }
        .pedestal-3 { 
            height: 180px; 
            background: linear-gradient(to bottom, rgba(168, 85, 247, 0.2), #0f172a);
            border-top: 4px solid #a855f7;
        }
        .medal-float {
            animation: float 3s ease-in-out infinite;
            filter: drop-shadow(0 0 15px currentColor);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(5deg); }
        }
        .winner-aura {
            position: absolute;
            top: -50px;
            left: 50%;
            transform: translateX(-50%);
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, var(--aura-color) 0%, transparent 70%);
            opacity: 0.3;
            filter: blur(40px);
            z-index: -1;
            animation: pulse 4s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { scale: 1; opacity: 0.3; }
            50% { scale: 1.2; opacity: 0.5; }
        }
        .spotlight {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 50% 30%, transparent 10%, rgba(0,0,0,0.85) 60%);
            z-index: 50;
            pointer-events: none;
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
                             x-html="getAvatarHtml(m)">
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
                         x-html="getAvatarHtml(s)">
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
    </div>  {{-- STATE: FINISH (PODIUM) --}}
  <template x-if="state.state === 'finish'">
    <div class="relative w-full max-w-6xl min-h-[600px] flex flex-col items-center justify-center">
      
      {{-- SPOTLIGHT EFFECT (Saat reveal tertentu) --}}
      <template x-if="podiumStep === 1 || podiumStep === 3 || podiumStep === 5">
        <div class="spotlight"></div>
      </template>

      {{-- HEADER --}}
      <div class="mb-12 z-20">
        <h2 class="text-6xl font-black uppercase tracking-tighter text-white drop-shadow-2xl animate-popIn">
          <span class="text-amber-400">🏆</span> HALL OF FAME <span class="text-amber-400">🏆</span>
        </h2>
        <p class="text-indigo-300 font-bold uppercase tracking-[0.3em] mt-2 opacity-80">Battle Arena Champions</p>
      </div>

      {{-- COUNTDOWN OVERLAY --}}
      <template x-if="podiumStep === 1 || podiumStep === 3 || podiumStep === 5">
        <div class="fixed inset-0 z-[60] flex flex-col items-center justify-center pointer-events-none">
          <p class="text-4xl text-amber-400 font-black uppercase tracking-[0.5em] mb-10 drop-shadow-lg"
             x-text="podiumStep === 1 ? 'Mencari Juara 3...' : podiumStep === 3 ? 'Siapakah Juara 2?' : '👑 SANG JUARA 👑'"></p>
          <div class="text-[250px] font-black text-white leading-none drop-shadow-[0_0_100px_rgba(251,191,36,0.5)] animate-pulse"
               x-text="podiumCountdown"></div>
        </div>
      </template>

      {{-- PODIUM STAGE --}}
      <div class="podium-stage w-full mt-10">

        {{-- JUARA 2 (Left Back) --}}
        <template x-if="(state.mode === 'group' ? groupScores.length >= 2 : topMembers(3)[1]) && podiumStep >= 4">
          <div class="pedestal pedestal-2" x-transition:enter="transition cubic-bezier(0.34, 1.56, 0.64, 1) duration-1000" x-transition:enter-start="opacity-0 translate-y-32 scale-75" x-transition:enter-end="opacity-100 translate-y-0 scale-100">
            <div class="medal-float text-6xl mb-6 text-slate-300">🥈</div>
            
            {{-- Avatar/Group Name --}}
            <div class="flex flex-col items-center mb-6">
                {{-- Individual mode --}}
                <template x-if="state.mode !== 'group'">
                    <div class="w-24 h-24 rounded-full border-4 border-slate-300/50 mb-3 bg-slate-900 overflow-hidden shadow-2xl">
                        <template x-if="topMembers(3)[1].is_avatar_seed && topMembers(3)[1].avatar_seed">
                            <div class="w-full h-full" x-html="typeof multiavatar === 'function' ? multiavatar(topMembers(3)[1].avatar_seed) : ''"></div>
                        </template>
                        <template x-if="!topMembers(3)[1].is_avatar_seed && topMembers(3)[1].avatar_url">
                            <img :src="topMembers(3)[1].avatar_url" class="w-full h-full object-cover">
                        </template>
                    </div>
                </template>
                <h3 class="text-3xl font-black text-white uppercase tracking-tighter text-center line-clamp-2"
                    x-text="state.mode === 'group' ? (groupScores[1].name || groupScores[1].group_label) : topMembers(3)[1].name"></h3>
                <p class="text-2xl font-black text-slate-300 tabular-nums">
                    <span x-text="(state.mode === 'group' ? groupScores[1].total_score : scores[topMembers(3)[1].user_id]?.total_score)?.toLocaleString()"></span> PTS
                </p>
            </div>
          </div>
        </template>

        {{-- JUARA 1 (Center Front) --}}
        <template x-if="(state.mode === 'group' ? groupScores.length >= 1 : topMembers(3)[0]) && podiumStep >= 6">
          <div class="pedestal pedestal-1" x-transition:enter="transition cubic-bezier(0.34, 1.56, 0.64, 1) duration-1000" x-transition:enter-start="opacity-0 translate-y-48 scale-50" x-transition:enter-end="opacity-100 translate-y-0 scale-110">
            <div class="winner-aura" style="--aura-color: rgba(251,191,36,0.4)"></div>
            <div class="medal-float text-8xl mb-4 text-amber-400 drop-shadow-[0_0_30px_rgba(251,191,36,0.8)]">👑</div>
            
            <div class="flex flex-col items-center mb-8">
                <template x-if="state.mode !== 'group'">
                    <div class="w-32 h-32 rounded-full border-4 border-amber-400 mb-4 bg-slate-900 overflow-hidden shadow-[0_0_50px_rgba(251,191,36,0.3)]">
                        <template x-if="topMembers(3)[0].is_avatar_seed && topMembers(3)[0].avatar_seed">
                            <div class="w-full h-full" x-html="typeof multiavatar === 'function' ? multiavatar(topMembers(3)[0].avatar_seed) : ''"></div>
                        </template>
                        <template x-if="!topMembers(3)[0].is_avatar_seed && topMembers(3)[0].avatar_url">
                            <img :src="topMembers(3)[0].avatar_url" class="w-full h-full object-cover">
                        </template>
                    </div>
                </template>
                <h3 class="text-5xl font-black text-white uppercase tracking-tighter text-center leading-none"
                    x-text="state.mode === 'group' ? (groupScores[0].name || groupScores[0].group_label) : topMembers(3)[0].name"></h3>
                <p class="text-4xl font-black text-amber-400 tabular-nums mt-1 drop-shadow-md">
                    <span x-text="(state.mode === 'group' ? groupScores[0].total_score : scores[topMembers(3)[0].user_id]?.total_score)?.toLocaleString()"></span> PTS
                </p>
            </div>
          </div>
        </template>

        {{-- JUARA 3 (Right Back) --}}
        <template x-if="(state.mode === 'group' ? groupScores.length >= 3 : topMembers(3)[2]) && podiumStep >= 2">
          <div class="pedestal pedestal-3" x-transition:enter="transition cubic-bezier(0.34, 1.56, 0.64, 1) duration-1000" x-transition:enter-start="opacity-0 translate-y-32 scale-75" x-transition:enter-end="opacity-100 translate-y-0 scale-100">
            <div class="medal-float text-5xl mb-4 text-purple-400">🥉</div>
            
            <div class="flex flex-col items-center mb-4">
                <template x-if="state.mode !== 'group'">
                    <div class="w-20 h-20 rounded-full border-4 border-purple-400/40 mb-3 bg-slate-900 overflow-hidden shadow-xl">
                        <template x-if="topMembers(3)[2].is_avatar_seed && topMembers(3)[2].avatar_seed">
                            <div class="w-full h-full" x-html="typeof multiavatar === 'function' ? multiavatar(topMembers(3)[2].avatar_seed) : ''"></div>
                        </template>
                        <template x-if="!topMembers(3)[2].is_avatar_seed && topMembers(3)[2].avatar_url">
                            <img :src="topMembers(3)[2].avatar_url" class="w-full h-full object-cover">
                        </template>
                    </div>
                </template>
                <h3 class="text-2xl font-black text-white uppercase tracking-tighter text-center"
                    x-text="state.mode === 'group' ? (groupScores[2].name || groupScores[2].group_label) : topMembers(3)[2].name"></h3>
                <p class="text-xl font-black text-purple-400 tabular-nums">
                    <span x-text="(state.mode === 'group' ? groupScores[2].total_score : scores[topMembers(3)[2].user_id]?.total_score)?.toLocaleString()"></span> PTS
                </p>
            </div>
          </div>
        </template>

      </div>

      {{-- FOOTER ACTIONS & REWARDS --}}
      <div class="mt-16 flex flex-col items-center gap-6 z-20">
          @if($room->reward_physical)
            <div class="flex items-center gap-4 px-8 py-4 bg-amber-500/10 border-2 border-amber-500/30 rounded-3xl animate-bounce">
                <span class="text-4xl text-amber-400">🎁</span>
                <div class="text-left">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500/60">Grand Reward</p>
                    <p class="text-2xl font-black text-white uppercase leading-none">{{ $room->reward_physical }}</p>
                </div>
            </div>
          @endif

          <div class="flex gap-4">
            <a href="{{ route('admin.gamification.arena.debriefing', $room->token) }}"
               class="px-8 py-4 bg-white/10 hover:bg-white/20 border border-white/20 rounded-3xl text-white font-black uppercase tracking-widest transition-all">
               📜 Lihat Rekap Skor
            </a>
            <button @click="window.location.reload()"
                    class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 rounded-3xl text-white font-black uppercase tracking-widest shadow-2xl transition-all">
                🔄 Selesai
            </button>
          </div>
      </div>

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
            avatarCache: {},

            getAvatarHtml(m) {
                if (!m.avatar_seed) return '';
                const cacheKey = 'm_' + m.avatar_seed;
                if (this.avatarCache[cacheKey]) return this.avatarCache[cacheKey];
                
                if (typeof multiavatar === 'function') {
                    const html = multiavatar(m.avatar_seed);
                    this.avatarCache[cacheKey] = html;
                    return html;
                }
                return '';
            },
            
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
            drumRollAudio: null,

            getAuraColor(idx) {
                const colors = ['#fbbf24', '#cbd5e1', '#a855f7'];
                return colors[idx] || '#6366f1';
            },

            playDrumRoll() {
                try {
                    if (this.drumRollAudio) {
                        this.drumRollAudio.pause();
                        this.drumRollAudio.currentTime = 0;
                    }
                    this.drumRollAudio = new Audio('/sound/ElevenLabs_Dramatic_drum_roll.mp3');
                    this.drumRollAudio.play();
                } catch(e) {
                    console.error("Audio error", e);
                }
            },

            stopDrumRoll() {
                if (this.drumRollAudio) {
                    this.drumRollAudio.pause();
                }
            },

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
                this.playDrumRoll();
                const cd3 = setInterval(() => {
                    this.podiumCountdown--;
                    if (this.podiumCountdown <= 0) {
                        clearInterval(cd3);
                        this.podiumStep = 2; // Reveal 3rd
                        this.stopDrumRoll();
                        
                        setTimeout(() => {
                            this.podiumStep = 3; // Show Countdown 2nd
                            this.podiumCountdown = 3;
                            this.playDrumRoll();
                            const cd2 = setInterval(() => {
                                this.podiumCountdown--;
                                if (this.podiumCountdown <= 0) {
                                    clearInterval(cd2);
                                    this.podiumStep = 4; // Reveal 2nd
                                    this.stopDrumRoll();
                                    
                                    setTimeout(() => {
                                        this.podiumStep = 5; // Show Countdown 1st
                                        this.podiumCountdown = 3;
                                        this.playDrumRoll();
                                        const cd1 = setInterval(() => {
                                            this.podiumCountdown--;
                                            if (this.podiumCountdown <= 0) {
                                                clearInterval(cd1);
                                                this.podiumStep = 6; // Reveal 1st
                                                this.stopDrumRoll();
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
                    this.playDrumRoll();
                    const cd3 = setInterval(() => {
                        this.podiumCountdown--;
                        if (this.podiumCountdown <= 0) {
                            clearInterval(cd3);
                            this.podiumStep = 2; // reveal juara 3
                            this.stopDrumRoll();

                            setTimeout(() => {
                                this.podiumStep = 3;
                                this.podiumCountdown = 3;
                                this.playDrumRoll();
                                const cd2 = setInterval(() => {
                                    this.podiumCountdown--;
                                    if (this.podiumCountdown <= 0) {
                                        clearInterval(cd2);
                                        this.podiumStep = 4; // reveal juara 2
                                        this.stopDrumRoll();

                                        setTimeout(() => {
                                            this.podiumStep = 5;
                                            this.podiumCountdown = 3;
                                            this.playDrumRoll();
                                            const cd1 = setInterval(() => {
                                                this.podiumCountdown--;
                                                if (this.podiumCountdown <= 0) {
                                                    clearInterval(cd1);
                                                    this.podiumStep = 6; // reveal juara 1
                                                    this.stopDrumRoll();
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
                this.count = data.member_count ?? 0;
                
                // Persistence: Only update if server sends new data and members changed
                if (data.members && (data.members.length !== this.members.length)) {
                    this.members = data.members;
                }
                
                if (data.scores && data.scores.length > 0) {
                    let smap = {};
                    data.scores.forEach(s => smap[s.user_id] = s);
                    this.scoresMap = smap;
                    this.scores = data.scores;
                    
                    if (this.state.state !== 'finish' && !['preview', 'question'].includes(this.state.state)) {
                        this.updateLeaderboard(data.scores);
                    }
                }
                
                // Persistence for Question & Stats (Jangan timpa data lengkap dengan data pruned)
                if (data.question) {
                     const isNewPruned = !data.question.correct_answer;
                     const isCurrentlyDiscussion = (this.state.state === 'discussion');
                     
                     if (isCurrentlyDiscussion && isNewPruned && this.question?.correct_answer) {
                         // Mantain existing data if new one is pruned during discussion
                         this.question = { ...this.question, ...data.question, correct_answer: this.question.correct_answer, explanation: this.question.explanation };
                     } else {
                         this.question = data.question;
                     }
                }
                
                if (data.stats && Object.keys(data.stats).length > 0) {
                    this.stats = data.stats;
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
                const duration = 7 * 1000;
                const animationEnd = Date.now() + duration;
                
                let colors = ['#fbbf24', '#cbd5e1', '#a855f7', '#6366f1'];
                
                const interval = setInterval(function() {
                    const timeLeft = animationEnd - Date.now();
                    if (timeLeft <= 0) {
                        return clearInterval(interval);
                    }
                    
                    const particleCount = 60 * (timeLeft / duration);
                    
                    // Sebar dari kiri ke kanan
                    confetti({
                        particleCount,
                        angle: 60,
                        spread: 55,
                        origin: { x: 0, y: 0.8 },
                        colors: colors
                    });
                    confetti({
                        particleCount,
                        angle: 120,
                        spread: 55,
                        origin: { x: 1, y: 0.8 },
                        colors: colors
                    });
                    
                    // Efek burst di tengah sesekali
                    if (Math.random() > 0.7) {
                        confetti({
                            particleCount: 20,
                            origin: { x: 0.5, y: 0.7 },
                            colors: ['#ffffff', '#fbbf24']
                        });
                    }
                }, 250);
            }
        }
    }
    </script>
</body>
</html>
