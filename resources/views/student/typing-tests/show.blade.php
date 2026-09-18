@extends('layouts.app')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════════════════
   EXAM ACTIVE: Full Screen Immersion
   ═══════════════════════════════════════════════════════ */
body.exam-active #sidebar,
body.exam-active #sidebarOverlay,
body.exam-active .flex.flex-col.flex-1.overflow-hidden > nav {
    display: none !important;
}

body.exam-active {
    background: #f8fafc !important;
}

body.exam-active .flex.flex-col.flex-1.overflow-hidden {
    max-width: 100% !important;
    height: 100vh !important;
    overflow-y: auto !important;
}

#typingArenaWrapper {
    min-height: calc(100vh - 4rem);
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* ═══════════════════════════════════════════════════════
   READY MODAL
   ═══════════════════════════════════════════════════════ */
#readyModal {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.modal-card {
    background: #ffffff !important;
    border-radius: 2rem;
    padding: 2.5rem;
    max-width: 440px;
    width: 92%;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
    position: relative;
    z-index: 100000;
    text-align: center;
}

/* ═══════════════════════════════════════════════════════
   TYPING ARENA (Natural Tight Kerning & Spacing)
   ═══════════════════════════════════════════════════════ */
.typing-box {
    font-family: 'JetBrains Mono', 'Fira Code', Consolas, Monaco, monospace;
    font-size: 1.65rem;
    line-height: 3rem;
    letter-spacing: 0;
    color: #94a3b8;
    user-select: none;
    cursor: text;
    max-height: 9rem;
    overflow: hidden;
    position: relative;
    outline: none;
    transition: all 0.2s ease;
}

/* Word styling — inline-flex to guarantee zero inter-character whitespace gap */
.typing-box .word {
    display: inline-flex;
    margin-right: 0.7em;
    padding: 0 1px;
    border-radius: 4px;
    vertical-align: middle;
    letter-spacing: 0;
}

.typing-box .word.current {
    color: #334155;
}

/* Character styling — strict zero extra spacing */
.typing-box .char {
    position: relative;
    display: inline-block;
    padding: 0;
    margin: 0;
    letter-spacing: 0;
    transition: color 0.08s ease;
}

.typing-box .char.correct {
    color: #10b981; /* Emerald 500 */
}

.typing-box .char.wrong {
    color: #ef4444; /* Rose 500 */
    background-color: rgba(239, 68, 68, 0.12);
    border-radius: 2px;
}

.typing-box .char.extra {
    color: #dc2626;
    opacity: 0.75;
}

/* Caret / Cursor */
.typing-box .char.caret::before {
    content: '';
    position: absolute;
    left: -1px;
    top: 10%;
    height: 80%;
    width: 2.5px;
    background: var(--brand-primary, #4f46e5);
    border-radius: 2px;
    animation: caretBlink 1s infinite;
}

.typing-box .char.caret-after::after {
    content: '';
    position: absolute;
    right: -1px;
    top: 10%;
    height: 80%;
    width: 2.5px;
    background: var(--brand-primary, #4f46e5);
    border-radius: 2px;
    animation: caretBlink 1s infinite;
}

@keyframes caretBlink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0; }
}

/* Blur overlay when arena lost focus */
#blurWarning {
    position: absolute;
    inset: 0;
    background: rgba(248, 250, 252, 0.85);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 1.5rem;
    z-index: 20;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
}

#blurWarning.active {
    opacity: 1;
    pointer-events: auto;
    cursor: pointer;
}

/* Hidden input buffer */
#keyBuffer {
    position: absolute;
    opacity: 0;
    pointer-events: none;
    left: -9999px;
    top: -9999px;
}
</style>
@endpush

@section('content')
<div id="typingArenaWrapper" class="max-w-4xl mx-auto px-4 py-8">

  {{-- ════════════════════════════════════
       READY MODAL
       ════════════════════════════════════ --}}
  <div id="readyModal">
    <div class="modal-card">
      {{-- Icon --}}
      <div style="width: 4.5rem; height: 4.5rem; border-radius: 1.5rem; margin: 0 auto 1.25rem auto; display: flex; align-items: center; justify-content: center; font-size: 2rem; background: var(--brand-glow, rgba(79,70,229,0.12)); color: var(--brand-primary, #4f46e5); box-shadow: 0 8px 16px -4px var(--brand-glow, rgba(79,70,229,0.25));">
        <i class="fas fa-keyboard"></i>
      </div>

      <h2 style="font-size: 1.35rem; font-weight: 900; color: #0f172a; margin: 0 0 0.4rem 0;">
        {{ $test->title }}
      </h2>
      
      <div style="display: flex; align-items: center; justify-content: center; gap: 0.85rem; font-size: 0.875rem; color: #64748b; margin-bottom: 1.5rem; font-weight: 600;">
        <span><i class="fas fa-clock" style="margin-right: 0.4rem; color: var(--brand-primary, #4f46e5);"></i>{{ $test->duration_seconds }} Detik</span>
        <span style="color: #cbd5e1;">•</span>
        <span><i class="fas fa-bolt" style="margin-right: 0.4rem; color: #f59e0b;"></i>Target {{ $test->target_wpm }} WPM</span>
      </div>

      {{-- Rules Box --}}
      <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 1.25rem; padding: 1.15rem; text-align: left; font-size: 0.8125rem; color: #92400e; margin-bottom: 1.75rem;">
        <p style="font-weight: 900; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: #b45309; margin: 0 0 0.65rem 0; display: flex; align-items: center; gap: 0.4rem;">
          <i class="fas fa-shield-alt"></i> Petunjuk & Tata Tertib
        </p>
        <div style="display: flex; flex-direction: column; gap: 0.45rem; line-height: 1.45;">
          <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
            <span>⏱️</span>
            <span>Timer otomatis berjalan saat kamu menekan tombol pertama.</span>
          </div>
          <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
            <span>🖥️</span>
            <span>Ujian wajib berjalan dalam <strong>Layar Penuh (Fullscreen)</strong>.</span>
          </div>
          <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
            <span>⚠️</span>
            <span>Keluar fullscreen dihitung <strong>pelanggaran</strong> (maks. 3×).</span>
          </div>
        </div>
      </div>

      {{-- Start Button --}}
      <button id="btnReady"
              style="width: 100%; padding: 1rem 1.5rem; border-radius: 1rem; font-weight: 900; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.08em; color: #ffffff; background: linear-gradient(135deg, var(--brand-primary, #4f46e5), var(--brand-dark, #3730a3)); border: none; cursor: pointer; box-shadow: 0 12px 24px -6px rgba(79, 70, 229, 0.4); transition: transform 0.15s ease, box-shadow 0.15s ease;"
              onmouseover="this.style.transform='translateY(-1px)'"
              onmouseout="this.style.transform='translateY(0)'">
        <i class="fas fa-play" style="margin-right: 0.6rem;"></i>Mulai Tes Sekarang
      </button>
    </div>
  </div>

  {{-- ════════════════════════════════════
       TOP HUD: Title, Live Stats, Timer
       ════════════════════════════════════ --}}
  <div class="flex items-end justify-between mb-6">
    <div>
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 mb-2">
        <i class="fas fa-keyboard text-xs"></i> Tes Mengetik
      </span>
      <h1 class="text-2xl font-black text-slate-800 tracking-tight">{{ $test->title }}</h1>
    </div>

    {{-- Live HUD Stats --}}
    <div class="flex items-center gap-4 sm:gap-6">
      {{-- Live WPM --}}
      <div class="text-center">
        <div class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-slate-700" id="liveWpm">0</div>
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">WPM</div>
      </div>

      {{-- Live Accuracy --}}
      <div class="text-center">
        <div class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-emerald-600" id="liveAcc">100%</div>
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Akurasi</div>
      </div>

      {{-- Divider --}}
      <div class="h-8 w-px bg-slate-200"></div>

      {{-- Timer --}}
      <div class="text-center min-w-[70px]">
        <div id="timerEl"
             class="text-3xl sm:text-4xl font-black font-mono tabular-nums leading-none"
             style="color: var(--brand-primary, #4f46e5)">
          {{ gmdate('i:s', $test->duration_seconds) }}
        </div>
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-1">Sisa Waktu</div>
      </div>
    </div>
  </div>

  {{-- ════════════════════════════════════
       MAIN TYPING CARD
       ════════════════════════════════════ --}}
  <div class="relative bg-white rounded-3xl p-8 sm:p-10 shadow-xl border border-slate-100 transition-all duration-300"
       id="arenaCard"
       onclick="focusArena()">

    {{-- Violation Alert Badge (if any) --}}
    <div id="violBadge"
         class="hidden absolute top-4 right-6 items-center gap-1.5 bg-rose-50 border border-rose-200 text-rose-600 rounded-full px-3 py-1 text-xs font-bold animate-pulse">
      <i class="fas fa-exclamation-triangle"></i>
      Pelanggaran: <span id="violCount">0</span>/3
    </div>

    {{-- Words Arena (No whitespace between char tags) --}}
    @php $words = explode(' ', $attempt->words_generated); @endphp
    <div id="wordsContainer" class="typing-box">
      @foreach($words as $i => $word)<span class="word{{ $i === 0 ? ' current' : '' }}" data-word-idx="{{ $i }}">@foreach(str_split($word) as $ci => $char)<span class="char{{ ($i === 0 && $ci === 0) ? ' caret' : '' }}" data-char-idx="{{ $ci }}">{{ $char }}</span>@endforeach</span>@endforeach
    </div>

    {{-- Lost focus warning overlay --}}
    <div id="blurWarning" onclick="focusArena()">
      <div class="text-center p-6 bg-white/95 rounded-2xl shadow-lg border border-slate-200">
        <i class="fas fa-mouse-pointer text-indigo-600 text-2xl mb-2 animate-bounce"></i>
        <p class="font-bold text-slate-800 text-base">Klik di sini untuk melanjutkan mengetik</p>
        <p class="text-xs text-slate-400 mt-0.5">Fokus area mengetik terlepas</p>
      </div>
    </div>

    {{-- Hidden input stream --}}
    <input type="text" id="keyBuffer" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
  </div>

  {{-- ════════════════════════════════════
       BOTTOM BAR: Progress & Key Hints
       ════════════════════════════════════ --}}
  <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400 px-2">
    <div class="flex items-center gap-4">
      <span class="flex items-center gap-1.5">
        <kbd class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-600 font-mono font-semibold">Spasi</kbd>
        Kata berikutnya
      </span>
      <span class="flex items-center gap-1.5">
        <kbd class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-600 font-mono font-semibold">Backspace</kbd>
        Hapus huruf
      </span>
    </div>

    <div class="flex items-center gap-2">
      <span>Progres:</span>
      <span id="wordProgress" class="font-mono font-bold text-slate-700">0 / {{ count($words) }} Kata</span>
    </div>
  </div>

  {{-- Progress Bar --}}
  <div class="mt-3 h-1.5 bg-slate-100 rounded-full overflow-hidden">
    <div id="timeProgressBar"
         class="h-full rounded-full transition-all duration-300 ease-linear"
         style="width: 100%; background: linear-gradient(90deg, var(--brand-primary, #4f46e5), #06b6d4);"></div>
  </div>

  {{-- ════════════════════════════════════
       SUBMIT LOADING OVERLAY
       ════════════════════════════════════ --}}
  <div id="loadingOverlay"
       class="hidden fixed inset-0 z-[99999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl p-8 max-w-xs w-full text-center shadow-2xl space-y-4">
      <div class="w-14 h-14 border-4 rounded-full animate-spin mx-auto"
           style="border-color: var(--brand-primary, #4f46e5); border-top-color: transparent"></div>
      <div>
        <h3 class="font-black text-slate-800 text-lg">Menghitung Hasil...</h3>
        <p class="text-xs text-slate-400 mt-1">Menyimpan kecepatan & akurasi kamu</p>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
window.isSubmitting = false;

document.addEventListener('DOMContentLoaded', () => {

  /* ── Configuration ────────────────────────── */
  const DURATION       = {{ $test->duration_seconds }};
  const CSRF           = '{{ csrf_token() }}';
  const SUBMIT_URL     = '{{ route("student.typing-tests.submit", $test) }}';
  const MAX_VIOLATIONS = 3;

  /* ── Words Data ───────────────────────────── */
  const wordElements = [...document.querySelectorAll('#wordsContainer .word')];
  const totalWords   = wordElements.length;

  /* ── State ────────────────────────────────── */
  let timeLeft        = DURATION;
  let timerInterval   = null;
  let statsInterval   = null;
  let timerStarted    = false;
  let startTime       = null;
  let currentWordIdx  = 0;
  let currentCharIdx  = 0;
  let typedWords      = [];
  let currentTyped    = '';
  let violations      = 0;
  let totalCharsTyped = 0;
  let totalCorrectChars = 0;

  /* ── DOM Elements ─────────────────────────── */
  const readyModal      = document.getElementById('readyModal');
  const btnReady        = document.getElementById('btnReady');
  const keyBuffer       = document.getElementById('keyBuffer');
  const wordsContainer  = document.getElementById('wordsContainer');
  const blurWarning     = document.getElementById('blurWarning');
  const timerEl         = document.getElementById('timerEl');
  const liveWpmEl       = document.getElementById('liveWpm');
  const liveAccEl       = document.getElementById('liveAcc');
  const wordProgressEl  = document.getElementById('wordProgress');
  const timeProgressBar = document.getElementById('timeProgressBar');
  const violBadge       = document.getElementById('violBadge');
  const violCountEl     = document.getElementById('violCount');
  const loadingOverlay  = document.getElementById('loadingOverlay');

  /* ─────────────────────────────────────────── */
  /* FULLSCREEN & FOCUS                          */
  /* ─────────────────────────────────────────── */
  function enterExamMode() {
    document.body.classList.add('exam-active');
    const el = document.documentElement;
    const fn = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen;
    if (fn) fn.call(el).catch(() => {});
  }

  function isFullscreen() {
    return !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement);
  }

  ['fullscreenchange', 'webkitfullscreenchange', 'mozfullscreenchange', 'MSFullscreenChange'].forEach(evt => {
    document.addEventListener(evt, () => {
      if (window.isSubmitting || !timerStarted) return;
      if (!isFullscreen()) triggerViolation();
    });
  });

  window.focusArena = function() {
    if (window.isSubmitting) return;
    keyBuffer.focus();
    blurWarning.classList.remove('active');
  };

  keyBuffer.addEventListener('blur', () => {
    if (timerStarted && !window.isSubmitting) {
      blurWarning.classList.add('active');
    }
  });

  btnReady.addEventListener('click', () => {
    readyModal.style.display = 'none';
    enterExamMode();
    focusArena();
    updateCaretPosition();
  });

  /* ─────────────────────────────────────────── */
  /* VIOLATIONS                                  */
  /* ─────────────────────────────────────────── */
  function triggerViolation() {
    violations++;
    violCountEl.textContent = violations;
    violBadge.classList.remove('hidden');
    violBadge.classList.add('inline-flex');

    if (violations >= MAX_VIOLATIONS) {
      showViolModal(true);
    } else {
      showViolModal(false);
    }
  }

  function showViolModal(isFinal) {
    document.getElementById('violPopup')?.remove();
    const div = document.createElement('div');
    div.id = 'violPopup';
    div.style.cssText = 'position:fixed;inset:0;z-index:999999;display:flex;align-items:center;justify-content:center;background:rgba(15,23,42,0.85);backdrop-filter:blur(8px);';

    if (isFinal) {
      div.innerHTML = `
        <div style="background:#fff;border-radius:2rem;padding:2.5rem;max-width:380px;width:92%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,0.5);">
          <div style="font-size:3.5rem;margin-bottom:0.75rem;">⛔</div>
          <h3 style="font-weight:900;font-size:1.25rem;color:#0f172a;margin:0 0 0.5rem 0;">Batas Pelanggaran Tercapai!</h3>
          <p style="color:#64748b;font-size:0.875rem;margin:0 0 1.5rem 0;">Ujian selesai dan hasil akan dikirim otomatis...</p>
        </div>`;
      document.body.appendChild(div);
      setTimeout(() => submitTest(), 2000);
    } else {
      div.innerHTML = `
        <div style="background:#fff;border-radius:2rem;padding:2.5rem;max-width:380px;width:92%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,0.5);">
          <div style="font-size:3.5rem;margin-bottom:0.75rem;">⚠️</div>
          <h3 style="font-weight:900;font-size:1.25rem;color:#0f172a;margin:0 0 0.4rem 0;">Pelanggaran (${violations}/${MAX_VIOLATIONS})</h3>
          <p style="color:#64748b;font-size:0.875rem;margin:0 0 1.5rem 0;line-height:1.4;">
            Kamu keluar dari layar penuh.<br>Toleransi tersisa: <strong>${MAX_VIOLATIONS - violations} kali</strong>.
          </p>
          <button id="btnReturnFs"
                  style="width:100%;padding:1rem;border-radius:1rem;font-weight:900;font-size:0.875rem;text-transform:uppercase;letter-spacing:0.08em;color:#fff;background:var(--brand-primary,#4f46e5);border:none;cursor:pointer;box-shadow:0 10px 20px -5px rgba(79,70,229,0.4);">
            <i class="fas fa-expand" style="margin-right:0.5rem;"></i>Kembali ke Layar Penuh
          </button>
        </div>`;
      document.body.appendChild(div);
      document.getElementById('btnReturnFs').onclick = () => {
        div.remove();
        enterExamMode();
        focusArena();
      };
    }
  }

  /* ─────────────────────────────────────────── */
  /* TYPING LOGIC (Character-by-character)       */
  /* ─────────────────────────────────────────── */
  function updateCaretPosition() {
    // Remove existing carets
    document.querySelectorAll('.typing-box .char').forEach(ch => {
      ch.classList.remove('caret', 'caret-after');
    });

    const activeWordEl = wordElements[currentWordIdx];
    if (!activeWordEl) return;

    const charEls = activeWordEl.querySelectorAll('.char');
    if (currentCharIdx < charEls.length) {
      charEls[currentCharIdx].classList.add('caret');
    } else if (charEls.length > 0) {
      charEls[charEls.length - 1].classList.add('caret-after');
    }

    // Auto-scroll 3-line box
    const currentTop = activeWordEl.offsetTop;
    const containerTop = wordsContainer.offsetTop;
    if (currentTop - containerTop > 45) {
      wordsContainer.scrollTop = (currentTop - containerTop) - 40;
    } else {
      wordsContainer.scrollTop = 0;
    }
  }

  function renderCurrentWord() {
    const wordEl = wordElements[currentWordIdx];
    if (!wordEl) return;

    const targetWord = wordEl.innerText.trim();
    const charEls = wordEl.querySelectorAll('.char');

    charEls.forEach((ch, idx) => {
      ch.classList.remove('correct', 'wrong');
      if (idx < currentTyped.length) {
        if (currentTyped[idx] === targetWord[idx]) {
          ch.classList.add('correct');
        } else {
          ch.classList.add('wrong');
        }
      }
    });

    // Remove extra chars if deleted
    const extraEls = wordEl.querySelectorAll('.char.extra');
    extraEls.forEach(el => el.remove());

    // Add extra chars if typed beyond target
    if (currentTyped.length > targetWord.length) {
      for (let i = targetWord.length; i < currentTyped.length; i++) {
        const extraSpan = document.createElement('span');
        extraSpan.className = 'char wrong extra';
        extraSpan.textContent = currentTyped[i];
        wordEl.appendChild(extraSpan);
      }
    }

    updateCaretPosition();
  }

  function commitWord() {
    const wordEl = wordElements[currentWordIdx];
    if (!wordEl) return;

    typedWords[currentWordIdx] = currentTyped;
    wordEl.classList.remove('current');

    // Count stats
    const targetWord = wordEl.innerText.trim();
    totalCharsTyped += currentTyped.length + 1; // +1 space
    for (let i = 0; i < Math.min(currentTyped.length, targetWord.length); i++) {
      if (currentTyped[i] === targetWord[i]) totalCorrectChars++;
    }
    if (currentTyped === targetWord) totalCorrectChars++; // correct space

    currentWordIdx++;
    currentCharIdx = 0;
    currentTyped = '';
    keyBuffer.value = '';

    wordProgressEl.textContent = `${currentWordIdx} / ${totalWords} Kata`;

    if (currentWordIdx >= totalWords) {
      submitTest();
    } else {
      wordElements[currentWordIdx].classList.add('current');
      updateCaretPosition();
    }
  }

  /* ─────────────────────────────────────────── */
  /* KEYBOARD INPUT INTERCEPTOR                  */
  /* ─────────────────────────────────────────── */
  window.addEventListener('keydown', (e) => {
    if (window.isSubmitting || readyModal.style.display !== 'none') return;

    // Start timer on first printable keypress
    if (!timerStarted && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
      startTimer();
    }

    if (e.key === ' ') {
      e.preventDefault();
      if (currentTyped.length > 0) {
        commitWord();
      }
      return;
    }

    if (e.key === 'Backspace') {
      e.preventDefault();
      if (currentTyped.length > 0) {
        currentTyped = currentTyped.slice(0, -1);
        currentCharIdx = currentTyped.length;
        renderCurrentWord();
      }
      return;
    }

    if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
      e.preventDefault();
      currentTyped += e.key;
      currentCharIdx = currentTyped.length;
      renderCurrentWord();
    }
  });

  /* ─────────────────────────────────────────── */
  /* LIVE TIMER & STATS HUD                     */
  /* ─────────────────────────────────────────── */
  function fmt(s) {
    return String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
  }

  function startTimer() {
    if (timerStarted) return;
    timerStarted = true;
    startTime = Date.now();

    timerInterval = setInterval(() => {
      timeLeft--;
      timerEl.textContent = fmt(timeLeft);

      const pct = (timeLeft / DURATION) * 100;
      timeProgressBar.style.width = pct + '%';

      if (timeLeft <= 10) {
        timerEl.style.color = '#ef4444';
        timeProgressBar.style.background = '#ef4444';
      }

      if (timeLeft <= 0) {
        clearInterval(timerInterval);
        clearInterval(statsInterval);
        submitTest();
      }
    }, 1000);

    statsInterval = setInterval(() => {
      const elapsedMinutes = (Date.now() - startTime) / 60000;
      if (elapsedMinutes > 0) {
        const grossWpm = Math.round((totalCharsTyped + currentTyped.length) / 5 / elapsedMinutes);
        liveWpmEl.textContent = Math.max(0, grossWpm);

        const allChars = totalCharsTyped + currentTyped.length;
        if (allChars > 0) {
          const acc = Math.round((totalCorrectChars / allChars) * 100);
          liveAccEl.textContent = Math.min(100, Math.max(0, acc)) + '%';
        }
      }
    }, 500);
  }

  /* ─────────────────────────────────────────── */
  /* SUBMIT TEST                                 */
  /* ─────────────────────────────────────────── */
  async function submitTest() {
    if (window.isSubmitting) return;
    window.isSubmitting = true;

    clearInterval(timerInterval);
    clearInterval(statsInterval);

    if (currentTyped.length > 0 && currentWordIdx < totalWords) {
      typedWords[currentWordIdx] = currentTyped;
    }

    loadingOverlay.classList.remove('hidden');

    try {
      const res = await fetch(SUBMIT_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ words_typed: typedWords.join(' ') }),
      });

      const data = await res.json();
      if (data.success) {
        window.location.href = data.redirect;
      } else {
        loadingOverlay.classList.add('hidden');
        alert('Gagal menyimpan hasil ujian: ' + (data.message || 'Silakan hubungi pengawas.'));
      }
    } catch (err) {
      loadingOverlay.classList.add('hidden');
      console.error(err);
      alert('Terjadi kesalahan jaringan saat mengirim hasil ujian.');
    }
  }

});
</script>
@endpush
