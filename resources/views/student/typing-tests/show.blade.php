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
   READY MODAL WITH KEYBOARD TESTER
   ═══════════════════════════════════════════════════════ */
#readyModal {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.82);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    overflow-y: auto;
    padding: 1.5rem;
}

.modal-card {
    background: #ffffff !important;
    border-radius: 2.25rem;
    padding: 2rem 2.25rem;
    max-width: 820px;
    width: 100%;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.45);
    position: relative;
    z-index: 100000;
    text-align: center;
}

/* ═══════════════════════════════════════════════════════
   VIRTUAL KEYBOARD STYLING (key-test.ru style)
   ═══════════════════════════════════════════════════════ */
.keyboard-tester-wrap {
    background: #0f172a;
    border-radius: 1.5rem;
    padding: 1.15rem 0.85rem;
    box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.6), 0 4px 12px rgba(0, 0, 0, 0.15);
    border: 1px solid #1e293b;
    margin: 1.1rem 0;
}

.kb-row {
    display: flex;
    justify-content: center;
    gap: 4px;
    margin-bottom: 4px;
}
.kb-row:last-child {
    margin-bottom: 0;
}

.kb-key {
    background: #1e293b;
    color: #94a3b8;
    border: 1px solid #334155;
    border-radius: 7px;
    font-family: 'JetBrains Mono', Consolas, monospace;
    font-size: 0.75rem;
    font-weight: 600;
    height: 36px;
    min-width: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    user-select: none;
    transition: all 0.12s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 0 #0f172a;
    padding: 0 5px;
    text-transform: uppercase;
}

/* Key size variations */
.kb-key.w-wide-1 { min-width: 54px; font-size: 0.7rem; }
.kb-key.w-wide-2 { min-width: 64px; font-size: 0.7rem; }
.kb-key.w-wide-3 { min-width: 78px; font-size: 0.7rem; }
.kb-key.w-space  { flex-grow: 1; max-width: 300px; }

/* Tested key state (Teal/Emerald Glow) */
.kb-key.tested {
    background: #10b981 !important;
    border-color: #059669 !important;
    color: #ffffff !important;
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.45), 0 2px 0 #047857 !important;
}

/* Currently pressed active state */
.kb-key.pressed {
    transform: translateY(2px);
    box-shadow: 0 0 0 transparent !important;
    background: #06b6d4 !important;
    color: #ffffff !important;
    border-color: #0891b2 !important;
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

/* Word styling */
.typing-box .word {
    display: inline-block;
    margin-right: 0.7em;
    padding: 0;
    border-radius: 4px;
    vertical-align: middle;
    letter-spacing: 0;
    white-space: nowrap;
}

.typing-box .word.current {
    color: #334155;
}

/* Character styling — strict zero gap between chars */
.typing-box .char {
    position: relative;
    display: inline;
    padding: 0;
    margin: 0;
    letter-spacing: 0;
    transition: color 0.05s ease, background-color 0.05s ease;
}

.typing-box .char.correct {
    color: #10b981; /* Emerald 500 */
    background: transparent;
}

.typing-box .char.wrong {
    color: #ef4444; /* Rose 500 */
    background-color: rgba(239, 68, 68, 0.15);
    border-radius: 2px;
}

.typing-box .char.extra {
    color: #dc2626;
    background-color: rgba(220, 38, 38, 0.2);
    border-radius: 2px;
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
       READY MODAL WITH KEYBOARD TESTER
       ════════════════════════════════════ --}}
  <div id="readyModal">
    <div class="modal-card">
      
      {{-- Header Info --}}
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-slate-100 pb-3">
        <div class="flex items-center gap-3 text-left">
          <div style="width: 3rem; height: 3rem; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; background: var(--brand-glow, rgba(79,70,229,0.12)); color: var(--brand-primary, #4f46e5); flex-shrink: 0;">
            <i class="fas fa-keyboard"></i>
          </div>
          <div>
            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md mb-0.5">Uji Kesiapan Keyboard</span>
            <h2 style="font-size: 1.15rem; font-weight: 900; color: #0f172a; margin: 0; line-height: 1.2;">
              {{ $test->title }}
            </h2>
          </div>
        </div>

        <div class="flex items-center gap-3 text-xs text-slate-500 font-semibold bg-slate-50 px-3.5 py-1.5 rounded-xl border border-slate-100">
          <span><i class="fas fa-clock mr-1 text-indigo-600"></i>{{ $test->duration_seconds }}s</span>
          <span class="text-slate-300">•</span>
          <span><i class="fas fa-bolt mr-1 text-amber-500"></i>Target {{ $test->target_wpm }} WPM</span>
        </div>
      </div>

      {{-- Instructions & Status Badge --}}
      <div class="mt-3 flex items-center justify-between text-left text-xs">
        <p class="text-slate-500">
          <i class="fas fa-info-circle text-indigo-500 mr-1"></i>
          Tekan tombol keyboard fisik kamu untuk memastikan tombol merespons dengan baik:
        </p>
        <span id="kbTestedCount" class="font-mono font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-lg">
          0 Tombol Teruji
        </span>
      </div>

      {{-- ════ VIRTUAL KEYBOARD (Key-Test Style) ════ --}}
      <div class="keyboard-tester-wrap">
        {{-- Row 1 --}}
        <div class="kb-row">
          <div class="kb-key" data-code="Backquote">` ~</div>
          <div class="kb-key" data-code="Digit1">1</div>
          <div class="kb-key" data-code="Digit2">2</div>
          <div class="kb-key" data-code="Digit3">3</div>
          <div class="kb-key" data-code="Digit4">4</div>
          <div class="kb-key" data-code="Digit5">5</div>
          <div class="kb-key" data-code="Digit6">6</div>
          <div class="kb-key" data-code="Digit7">7</div>
          <div class="kb-key" data-code="Digit8">8</div>
          <div class="kb-key" data-code="Digit9">9</div>
          <div class="kb-key" data-code="Digit0">0</div>
          <div class="kb-key" data-code="Minus">- _</div>
          <div class="kb-key" data-code="Equal">= +</div>
          <div class="kb-key w-wide-2" data-code="Backspace">⌫ Back</div>
        </div>

        {{-- Row 2 --}}
        <div class="kb-row">
          <div class="kb-key w-wide-1" data-code="Tab">Tab</div>
          <div class="kb-key" data-code="KeyQ">Q</div>
          <div class="kb-key" data-code="KeyW">W</div>
          <div class="kb-key" data-code="KeyE">E</div>
          <div class="kb-key" data-code="KeyR">R</div>
          <div class="kb-key" data-code="KeyT">T</div>
          <div class="kb-key" data-code="KeyY">Y</div>
          <div class="kb-key" data-code="KeyU">U</div>
          <div class="kb-key" data-code="KeyI">I</div>
          <div class="kb-key" data-code="KeyO">O</div>
          <div class="kb-key" data-code="KeyP">P</div>
          <div class="kb-key" data-code="BracketLeft">[ {</div>
          <div class="kb-key" data-code="BracketRight">] }</div>
          <div class="kb-key" data-code="Backslash">\ |</div>
        </div>

        {{-- Row 3 --}}
        <div class="kb-row">
          <div class="kb-key w-wide-2" data-code="CapsLock">Caps</div>
          <div class="kb-key" data-code="KeyA">A</div>
          <div class="kb-key" data-code="KeyS">S</div>
          <div class="kb-key" data-code="KeyD">D</div>
          <div class="kb-key" data-code="KeyF">F</div>
          <div class="kb-key" data-code="KeyG">G</div>
          <div class="kb-key" data-code="KeyH">H</div>
          <div class="kb-key" data-code="KeyJ">J</div>
          <div class="kb-key" data-code="KeyK">K</div>
          <div class="kb-key" data-code="KeyL">L</div>
          <div class="kb-key" data-code="Semicolon">; :</div>
          <div class="kb-key" data-code="Quote">' "</div>
          <div class="kb-key w-wide-2" data-code="Enter">↵ Enter</div>
        </div>

        {{-- Row 4 --}}
        <div class="kb-row">
          <div class="kb-key w-wide-3" data-code="ShiftLeft">⇧ Shift</div>
          <div class="kb-key" data-code="KeyZ">Z</div>
          <div class="kb-key" data-code="KeyX">X</div>
          <div class="kb-key" data-code="KeyC">C</div>
          <div class="kb-key" data-code="KeyV">V</div>
          <div class="kb-key" data-code="KeyB">B</div>
          <div class="kb-key" data-code="KeyN">N</div>
          <div class="kb-key" data-code="KeyM">M</div>
          <div class="kb-key" data-code="Comma">, &lt;</div>
          <div class="kb-key" data-code="Period">. &gt;</div>
          <div class="kb-key" data-code="Slash">/ ?</div>
          <div class="kb-key w-wide-3" data-code="ShiftRight">⇧ Shift</div>
        </div>

        {{-- Row 5 --}}
        <div class="kb-row">
          <div class="kb-key w-wide-1" data-code="ControlLeft">Ctrl</div>
          <div class="kb-key" data-code="MetaLeft">Win</div>
          <div class="kb-key" data-code="AltLeft">Alt</div>
          <div class="kb-key w-space" data-code="Space">Space</div>
          <div class="kb-key" data-code="AltRight">Alt</div>
          <div class="kb-key" data-code="ContextMenu">☰</div>
          <div class="kb-key w-wide-1" data-code="ControlRight">Ctrl</div>
        </div>
      </div>

      {{-- Rules Notice --}}
      <div class="bg-amber-50 border border-amber-200/80 rounded-xl p-2.5 text-left text-xs text-amber-800 mb-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span>🖥️</span>
          <span>Saat klik mulai, mode <strong>Layar Penuh (Fullscreen)</strong> akan aktif otomatis. Keluar layar penuh dihitung pelanggaran (maks. 3×).</span>
        </div>
        <button type="button" id="btnResetKb" class="text-xs text-amber-600 hover:text-amber-800 font-bold underline ml-2 flex-shrink-0">
          Reset Tes
        </button>
      </div>

      {{-- Start Button --}}
      <button id="btnReady"
              style="width: 100%; padding: 0.9rem 1.5rem; border-radius: 0.875rem; font-weight: 900; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.08em; color: #ffffff; background: linear-gradient(135deg, var(--brand-primary, #4f46e5), var(--brand-dark, #3730a3)); border: none; cursor: pointer; box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4); transition: transform 0.15s ease, box-shadow 0.15s ease;"
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

    {{-- Words Arena (Strict single line per word with zero whitespace gaps) --}}
    @php $words = explode(' ', $attempt->words_generated); @endphp
    <div id="wordsContainer" class="typing-box">
      @foreach($words as $i => $word)<span class="word{{ $i === 0 ? ' current' : '' }}" data-word-idx="{{ $i }}" data-word="{{ $word }}">@foreach(str_split($word) as $ci => $char)<span class="char{{ ($i === 0 && $ci === 0) ? ' caret' : '' }}" data-char-idx="{{ $ci }}">{{ $char }}</span>@endforeach</span>@endforeach
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
  const WORDS_ARRAY    = @json($words);
  const TOTAL_WORDS    = WORDS_ARRAY.length;

  /* ── State ────────────────────────────────── */
  let timeLeft        = DURATION;
  let timerInterval   = null;
  let statsInterval   = null;
  let timerStarted    = false;
  let startTime       = null;
  let currentWordIdx  = 0;
  let typedWords      = [];
  let currentTyped    = '';
  let violations      = 0;
  let totalCharsTyped = 0;
  let totalCorrectChars = 0;
  let testedKeysSet   = new Set();

  /* ── DOM Elements ─────────────────────────── */
  const readyModal      = document.getElementById('readyModal');
  const btnReady        = document.getElementById('btnReady');
  const btnResetKb      = document.getElementById('btnResetKb');
  const kbTestedCount   = document.getElementById('kbTestedCount');
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
  const wordElements    = [...document.querySelectorAll('#wordsContainer .word')];
  const kbKeyElements   = document.querySelectorAll('.kb-key');

  /* ─────────────────────────────────────────── */
  /* KEYBOARD TESTER IN READY MODAL              */
  /* ─────────────────────────────────────────── */
  function handleKeyboardTester(e) {
    if (readyModal.style.display === 'none') return;

    // Prevent default browser shortcuts while testing keyboard (e.g. Tab, Space scroll, Backspace history, Quick search /)
    if (['Tab', 'Space', 'Backspace', 'Slash', 'Quote', 'AltLeft', 'AltRight'].includes(e.code)) {
      e.preventDefault();
    }

    const code = e.code;
    const targetKey = document.querySelector(`.kb-key[data-code="${code}"]`);

    if (targetKey) {
      targetKey.classList.add('tested', 'pressed');
      testedKeysSet.add(code);
      kbTestedCount.textContent = `${testedKeysSet.size} Tombol Teruji`;
    }
  }

  function handleKeyboardTesterKeyUp(e) {
    if (readyModal.style.display === 'none') return;
    const code = e.code;
    const targetKey = document.querySelector(`.kb-key[data-code="${code}"]`);
    if (targetKey) {
      targetKey.classList.remove('pressed');
    }
  }

  window.addEventListener('keydown', handleKeyboardTester);
  window.addEventListener('keyup', handleKeyboardTesterKeyUp);

  if (btnResetKb) {
    btnResetKb.addEventListener('click', () => {
      testedKeysSet.clear();
      kbKeyElements.forEach(k => k.classList.remove('tested', 'pressed'));
      kbTestedCount.textContent = '0 Tombol Teruji';
    });
  }

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
  /* TYPING LOGIC (Clean string matching)        */
  /* ─────────────────────────────────────────── */
  function updateCaretPosition() {
    // Clear all existing carets
    document.querySelectorAll('.typing-box .char').forEach(ch => {
      ch.classList.remove('caret', 'caret-after');
    });

    const activeWordEl = wordElements[currentWordIdx];
    if (!activeWordEl) return;

    const charEls = activeWordEl.querySelectorAll('.char:not(.extra)');
    const currentCharIdx = currentTyped.length;

    if (currentCharIdx < charEls.length) {
      charEls[currentCharIdx].classList.add('caret');
    } else {
      const allChars = activeWordEl.querySelectorAll('.char');
      if (allChars.length > 0) {
        allChars[allChars.length - 1].classList.add('caret-after');
      }
    }

    // Smooth auto-scroll container
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

    // TARGET WORD from the pure JavaScript array (no DOM string whitespace issues)
    const targetWord = WORDS_ARRAY[currentWordIdx] || '';
    const charEls = wordEl.querySelectorAll('.char:not(.extra)');

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

    // Remove old extra chars
    const extraEls = wordEl.querySelectorAll('.char.extra');
    extraEls.forEach(el => el.remove());

    // Add extra chars if typed past word length
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

    const targetWord = WORDS_ARRAY[currentWordIdx] || '';
    typedWords[currentWordIdx] = currentTyped;
    wordEl.classList.remove('current');

    // Finalize highlights on the committed word
    const charEls = wordEl.querySelectorAll('.char:not(.extra)');
    charEls.forEach((ch, idx) => {
      ch.classList.remove('correct', 'wrong');
      if (idx < currentTyped.length) {
        if (currentTyped[idx] === targetWord[idx]) {
          ch.classList.add('correct');
        } else {
          ch.classList.add('wrong');
        }
      } else {
        // Untyped missing characters in an incomplete word
        ch.classList.add('wrong');
      }
    });

    // Count stats for HUD
    totalCharsTyped += currentTyped.length + 1; // +1 for space
    for (let i = 0; i < Math.min(currentTyped.length, targetWord.length); i++) {
      if (currentTyped[i] === targetWord[i]) totalCorrectChars++;
    }
    if (currentTyped === targetWord) totalCorrectChars++; // correct space

    currentWordIdx++;
    currentTyped = '';
    keyBuffer.value = '';

    wordProgressEl.textContent = `${currentWordIdx} / ${TOTAL_WORDS} Kata`;

    if (currentWordIdx >= TOTAL_WORDS) {
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
        renderCurrentWord();
      }
      return;
    }

    if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
      e.preventDefault();
      currentTyped += e.key;
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

    // Live WPM & Accuracy updater
    statsInterval = setInterval(() => {
      const elapsedMinutes = (Date.now() - startTime) / 60000;
      if (elapsedMinutes > 0) {
        const grossWpm = Math.round((totalCharsTyped + currentTyped.length) / 5 / elapsedMinutes);
        liveWpmEl.textContent = Math.max(0, grossWpm);

        let liveCorrect = totalCorrectChars;
        const currentTarget = WORDS_ARRAY[currentWordIdx] || '';
        for (let i = 0; i < currentTyped.length; i++) {
          if (i < currentTarget.length && currentTyped[i] === currentTarget[i]) {
            liveCorrect++;
          }
        }
        const liveTotal = totalCharsTyped + currentTyped.length;
        if (liveTotal > 0) {
          const acc = Math.round((liveCorrect / liveTotal) * 100);
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

    if (currentTyped.length > 0 && currentWordIdx < TOTAL_WORDS) {
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
