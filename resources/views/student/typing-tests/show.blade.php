@extends('layouts.app')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   EXAM MODE — hide sidebar & topnav via JS-triggered class
   ═══════════════════════════════════════════════════════ */
body.exam-active #sidebar,
body.exam-active #sidebarOverlay {
    display: none !important;
}
body.exam-active .flex.flex-col.flex-1.overflow-hidden > nav {
    display: none !important;
}
body.exam-active .flex.flex-col.flex-1.overflow-hidden {
    max-width: 100% !important;
}
body.exam-active #exam-page-content {
    padding: 2rem 3rem !important;
    max-width: 1000px !important;
}

/* ═══════════════════════════════════════════════════════
   READY MODAL STYLING (Solid & Clean, No overlap)
   ═══════════════════════════════════════════════════════ */
#readyModal {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}

.modal-card {
    background: #ffffff !important;
    border-radius: 2rem;
    padding: 2.25rem;
    max-width: 440px;
    width: 90%;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.4);
    position: relative;
    z-index: 100000;
    text-align: center;
}

/* ═══════════════════════════════════════════════════════
   WORDS DISPLAY — clean, no whitespace artifacts
   ═══════════════════════════════════════════════════════ */
#wordsDisplay {
    font-family: 'Courier New', Courier, monospace;
    font-size: 1.35rem;
    line-height: 2.6rem;
    color: #9ca3af;
    user-select: none;
    cursor: text;
    max-height: 8rem;
    overflow: hidden;
    position: relative;
}

/* Word spans */
#wordsDisplay .w {
    display: inline-block;
    margin-right: 0.5em;
    border-radius: 4px;
    padding: 0 2px;
    transition: background 0.08s, color 0.08s;
    letter-spacing: 0.01em;
}
#wordsDisplay .w.current {
    outline: 2px solid var(--brand-primary);
    background: var(--brand-glow, rgba(79,70,229,0.08));
    color: #374151;
}
#wordsDisplay .w.ok  { color: #22c55e; }
#wordsDisplay .w.err { color: #ef4444; }

/* Karakter per huruf */
#wordsDisplay .ch     { display: inline; }
#wordsDisplay .ch.ok  { color: #22c55e; }
#wordsDisplay .ch.err { color: #ef4444; }
#wordsDisplay .ch.cur {
    border-bottom: 3px solid var(--brand-primary);
    animation: blink 1s step-end infinite;
}
@keyframes blink { 50% { opacity: 0; } }

/* Gradient fade bottom */
#wordsDisplay::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 2.4rem;
    background: linear-gradient(to bottom, transparent, #ffffff);
    pointer-events: none;
}

/* ═══════════════════════════════════════════════════════
   INPUT FIELD
   ═══════════════════════════════════════════════════════ */
#typingInput {
    font-family: 'Courier New', Courier, monospace;
    font-size: 1.15rem;
    letter-spacing: 0.02em;
    caret-color: var(--brand-primary);
}
#typingInput:focus {
    outline: none;
    border-color: var(--brand-primary) !important;
    box-shadow: 0 0 0 3px var(--brand-glow, rgba(79,70,229,0.15));
}
</style>
@endpush

@section('content')
<div id="exam-page-content" class="max-w-3xl mx-auto px-4 py-6">

  {{-- ════════════════════════════════════
       READY MODAL — Solid Opaque Overlay
       ════════════════════════════════════ --}}
  <div id="readyModal">
    <div class="modal-card">

      {{-- Icon --}}
      <div style="width: 4rem; height: 4rem; border-radius: 1.25rem; margin: 0 auto 1.25rem auto; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; background: var(--brand-glow, rgba(79,70,229,0.12)); color: var(--brand-primary);">
        <i class="fas fa-keyboard"></i>
      </div>

      {{-- Judul & Info --}}
      <h2 style="font-size: 1.25rem; font-weight: 900; color: #111827; margin: 0 0 0.5rem 0; line-height: 1.3;">
        {{ $test->title }}
      </h2>
      
      <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; font-size: 0.875rem; color: #6b7280; margin-bottom: 1.25rem;">
        <span><i class="fas fa-clock" style="margin-right: 0.35rem; color: #9ca3af;"></i>{{ $test->duration_seconds }} detik</span>
        <span style="color: #d1d5db;">|</span>
        <span><i class="fas fa-tachometer-alt" style="margin-right: 0.35rem; color: #9ca3af;"></i>Target {{ $test->target_wpm }} WPM</span>
      </div>

      {{-- Peraturan Box --}}
      <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 1rem; padding: 1rem; text-align: left; font-size: 0.8125rem; color: #92400e; margin-bottom: 1.5rem;">
        <p style="font-weight: 900; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #b45309; margin: 0 0 0.6rem 0;">
          PERHATIAN SEBELUM MULAI
        </p>
        <div style="display: flex; flex-direction: column; gap: 0.4rem;">
          <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
            <span style="flex-shrink: 0;">⌨️</span>
            <span>Timer mulai saat kamu mengetik karakter pertama</span>
          </div>
          <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
            <span style="flex-shrink: 0;">🖥️</span>
            <span>Mode layar penuh akan aktif — sidebar disembunyikan</span>
          </div>
          <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
            <span style="flex-shrink: 0;">⚠️</span>
            <span>Keluar layar penuh = pelanggaran (maks. 3×)</span>
          </div>
          <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
            <span style="flex-shrink: 0;">🚫</span>
            <span>Tidak bisa kembali setelah tes dimulai</span>
          </div>
        </div>
      </div>

      {{-- Tombol Siap --}}
      <button id="btnReady"
              style="width: 100%; padding: 0.95rem 1rem; border-radius: 0.875rem; font-weight: 900; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.08em; color: #ffffff; background: linear-gradient(135deg, var(--brand-primary), var(--brand-dark, var(--brand-primary))); border: none; cursor: pointer; box-shadow: 0 10px 20px -5px rgba(0,0,0,0.2); transition: all 0.2s;"
              onmouseover="this.style.opacity='0.92'"
              onmouseout="this.style.opacity='1'">
        <i class="fas fa-play" style="margin-right: 0.5rem;"></i>SAYA SIAP — MULAI TES
      </button>
    </div>
  </div>

  {{-- ════════════════════════════════════
       HEADER: Judul + Timer + Violation
       ════════════════════════════════════ --}}
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-xl font-black text-gray-800 tracking-tight">{{ $test->title }}</h1>
      <p class="text-sm text-gray-400 mt-0.5">Durasi: {{ $test->duration_seconds }}s &nbsp;|&nbsp; Target: {{ $test->target_wpm }} WPM</p>
    </div>

    <div class="flex items-center gap-4">
      {{-- Violation badge --}}
      <div id="violBadge"
           class="hidden items-center gap-1.5 bg-rose-50 border border-rose-200 text-rose-600
                  rounded-xl px-3 py-1.5 text-xs font-black">
        <i class="fas fa-exclamation-triangle"></i>
        Pelanggaran: <span id="violCount">0</span>/3
      </div>

      {{-- Timer --}}
      <div class="text-right">
        <div id="timerEl"
             class="text-4xl font-mono font-black tabular-nums leading-none"
             style="color:var(--brand-primary)">
          {{ gmdate('i:s', $test->duration_seconds) }}
        </div>
        <p class="text-xs text-gray-400 mt-0.5">Sisa Waktu</p>
      </div>
    </div>
  </div>

  {{-- ════════════════════════════════════
       ARENA CARD
       ════════════════════════════════════ --}}
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5"
       id="arenaCard"
       onclick="if(!isSubmitting) document.getElementById('typingInput').focus()">

    {{-- Words Display --}}
    @php $words = explode(' ', $attempt->words_generated); @endphp
    <div id="wordsDisplay">
      @foreach($words as $i => $word)
        <span class="w{{ $i === 0 ? ' current' : '' }}"
              data-i="{{ $i }}"
              data-w="{{ $word }}">{{ $word }}</span>
      @endforeach
    </div>

    {{-- Input --}}
    <input
      type="text"
      id="typingInput"
      class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-gray-800 bg-gray-50 transition-colors"
      placeholder="Mulai mengetik di sini..."
      autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
      disabled
    >
  </div>

  {{-- Status Bar --}}
  <div class="mt-3 flex items-center justify-between text-sm px-1">
    <span class="text-gray-500">
      Kata: <span id="wordCountEl" class="font-bold text-gray-700">0</span>
    </span>
    <span id="statusEl" class="text-xs text-gray-400 italic">Timer belum berjalan</span>
  </div>

  {{-- Progress bar --}}
  <div class="mt-2 h-1 bg-gray-100 rounded-full overflow-hidden">
    <div id="progressBar"
         class="h-full rounded-full transition-all duration-500"
         style="width:0%; background:var(--brand-primary)"></div>
  </div>

  {{-- Loading Overlay --}}
  <div id="loadingOverlay"
       class="hidden fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl p-8 text-center shadow-2xl">
      <div class="w-12 h-12 border-4 rounded-full animate-spin mx-auto mb-4"
           style="border-color:var(--brand-primary); border-top-color:transparent"></div>
      <p class="font-bold text-gray-800">Menyimpan hasil tes...</p>
      <p class="text-sm text-gray-500 mt-1">Mohon tunggu</p>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
/* ═══════════════════════════════════════════════
   Typing Test Engine — v3.1 (Clean & Solid Modal)
   ═══════════════════════════════════════════════ */
window.isSubmitting = false;

document.addEventListener('DOMContentLoaded', () => {

  /* ── Config ───────────────────────────────── */
  const DURATION       = {{ $test->duration_seconds }};
  const CSRF           = '{{ csrf_token() }}';
  const SUBMIT_URL     = '{{ route("student.typing-tests.submit", $test) }}';
  const MAX_VIOLATIONS = 3;
  const TOTAL_WORDS    = {{ count($words) }};

  /* ── State ────────────────────────────────── */
  let timeLeft       = DURATION;
  let timerInterval  = null;
  let timerStarted   = false;
  let violations     = 0;
  let currentWordIdx = 0;
  let typedWords     = [];

  /* ── DOM ──────────────────────────────────── */
  const readyModal  = document.getElementById('readyModal');
  const btnReady    = document.getElementById('btnReady');
  const input       = document.getElementById('typingInput');
  const timerEl     = document.getElementById('timerEl');
  const violBadge   = document.getElementById('violBadge');
  const violCountEl = document.getElementById('violCount');
  const wordCountEl = document.getElementById('wordCountEl');
  const statusEl    = document.getElementById('statusEl');
  const loadingEl   = document.getElementById('loadingOverlay');
  const progressBar = document.getElementById('progressBar');
  const wordEls     = [...document.querySelectorAll('#wordsDisplay .w')];

  /* ─────────────────────────────────────────── */
  /* FULLSCREEN + SIDEBAR CONTROL                */
  /* ─────────────────────────────────────────── */

  function enterExamMode() {
    /* 1. Hide sidebar & topnav via CSS class */
    document.body.classList.add('exam-active');

    /* 2. Request browser fullscreen */
    const el = document.documentElement;
    const fn = el.requestFullscreen
             || el.webkitRequestFullscreen
             || el.mozRequestFullScreen
             || el.msRequestFullscreen;
    if (fn) fn.call(el).catch(() => {});
  }

  function exitExamMode() {
    document.body.classList.remove('exam-active');
  }

  /* Detect keluar fullscreen */
  function isFullscreen() {
    return !!(
      document.fullscreenElement ||
      document.webkitFullscreenElement ||
      document.mozFullScreenElement ||
      document.msFullscreenElement
    );
  }

  ['fullscreenchange','webkitfullscreenchange',
   'mozfullscreenchange','MSFullscreenChange'].forEach(evt => {
    document.addEventListener(evt, () => {
      if (window.isSubmitting || !timerStarted) return;
      if (!isFullscreen()) handleViolation();
    });
  });

  /* ─────────────────────────────────────────── */
  /* VIOLATION                                   */
  /* ─────────────────────────────────────────── */

  function handleViolation() {
    violations++;
    violCountEl.textContent = violations;
    violBadge.classList.remove('hidden');
    violBadge.classList.add('flex');

    if (violations >= MAX_VIOLATIONS) {
      showViolAlert(true);
    } else {
      showViolAlert(false);
    }
  }

  function showViolAlert(isFinal) {
    document.getElementById('violAlert')?.remove();

    const div = document.createElement('div');
    div.id = 'violAlert';
    Object.assign(div.style, {
      position: 'fixed', inset: '0', zIndex: '999999',
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      background: 'rgba(15, 23, 42, 0.8)', backdropFilter: 'blur(6px)',
    });

    if (isFinal) {
      div.innerHTML = `
        <div style="background:#ffffff;border-radius:1.75rem;padding:2.25rem;max-width:380px;width:90%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,0.4);">
          <div style="font-size:3rem;margin-bottom:0.75rem;">⛔</div>
          <p style="font-weight:900;font-size:1.2rem;color:#111827;margin-bottom:0.4rem;">Batas Pelanggaran Tercapai!</p>
          <p style="color:#6b7280;font-size:0.875rem;">Tes dikumpulkan otomatis dalam <span id="countdown">3</span> detik...</p>
          <div style="margin-top:1.5rem;height:4px;background:#f3f4f6;border-radius:2px;overflow:hidden;">
            <div id="ctBar" style="height:100%;width:100%;background:#ef4444;transition:width 3s linear;"></div>
          </div>
        </div>`;
      document.body.appendChild(div);
      requestAnimationFrame(() => {
        const ctBar = document.getElementById('ctBar');
        if (ctBar) ctBar.style.width = '0%';
      });
      let c = 3;
      const ct = setInterval(() => {
        c--;
        const el = document.getElementById('countdown');
        if (el) el.textContent = c;
        if (c <= 0) { clearInterval(ct); submitTest(); }
      }, 1000);
    } else {
      div.innerHTML = `
        <div style="background:#ffffff;border-radius:1.75rem;padding:2.25rem;max-width:380px;width:90%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,0.4);">
          <div style="font-size:3rem;margin-bottom:0.75rem;">⚠️</div>
          <p style="font-weight:900;font-size:1.2rem;color:#111827;margin-bottom:0.4rem;">
            Pelanggaran ${violations}/${MAX_VIOLATIONS}!
          </p>
          <p style="color:#6b7280;font-size:0.875rem;margin-bottom:1.5rem;line-height:1.4;">
            Kamu keluar dari layar penuh.<br>Sisa toleransi: <strong>${MAX_VIOLATIONS - violations}</strong> pelanggaran.
          </p>
          <button id="btnReenter"
                  style="width:100%;padding:0.9rem;border-radius:0.875rem;font-weight:900;font-size:0.85rem;
                         text-transform:uppercase;letter-spacing:0.08em;color:#ffffff;
                         background:var(--brand-primary);border:none;cursor:pointer;box-shadow:0 10px 20px -5px rgba(0,0,0,0.2);">
            <i class="fas fa-expand" style="margin-right:0.5rem;"></i>Kembali ke Layar Penuh
          </button>
        </div>`;
      document.body.appendChild(div);
      document.getElementById('btnReenter').onclick = () => {
        div.remove();
        enterExamMode();
        input.focus();
      };
    }
  }

  /* ─────────────────────────────────────────── */
  /* READY BUTTON                                */
  /* ─────────────────────────────────────────── */

  btnReady.addEventListener('click', () => {
    readyModal.style.display = 'none';
    enterExamMode();
    input.disabled = false;
    input.focus();
    activateWord(0);
  });

  /* ─────────────────────────────────────────── */
  /* WORD RENDERING                              */
  /* ─────────────────────────────────────────── */

  function activateWord(idx) {
    const el = wordEls[idx];
    if (!el) return;

    const word = el.dataset.w;

    /* Render per-char spans */
    el.innerHTML = [...word].map((ch, i) =>
      `<span class="ch${i === 0 ? ' cur' : ''}" data-i="${i}">${ch}</span>`
    ).join('');

    el.classList.add('current');
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function commitWord(idx, typed) {
    const el = wordEls[idx];
    if (!el) return;
    const target = el.dataset.w;

    el.classList.remove('current');

    if (typed === target) {
      el.textContent = target;
      el.classList.add('ok');
    } else {
      const maxLen = Math.max(typed.length, target.length);
      el.innerHTML = [...Array(maxLen)].map((_, i) => {
        const tCh = target[i] ?? '';
        const uCh = typed[i];
        if (!tCh) return '';
        const cls = (uCh === undefined) ? 'err' : (uCh === tCh ? 'ok' : 'err');
        return `<span class="ch ${cls}">${tCh}</span>`;
      }).join('');
      el.classList.add('err');
    }
  }

  function updateCharHighlight(current) {
    const el = wordEls[currentWordIdx];
    if (!el) return;
    const target = el.dataset.w;
    const chars  = el.querySelectorAll('.ch');

    chars.forEach((ch, i) => {
      ch.classList.remove('ok', 'err', 'cur');
      if (i < current.length) {
        ch.classList.add(current[i] === target[i] ? 'ok' : 'err');
      } else if (i === current.length) {
        ch.classList.add('cur');
      }
    });
  }

  /* ─────────────────────────────────────────── */
  /* TIMER                                       */
  /* ─────────────────────────────────────────── */

  function fmt(s) {
    return String(Math.floor(s / 60)).padStart(2, '0') +
           ':' + String(s % 60).padStart(2, '0');
  }

  function startTimer() {
    if (timerStarted) return;
    timerStarted = true;
    statusEl.innerHTML =
      '<span style="color:#16a34a;font-weight:700;">' +
      '<i class="fas fa-circle" style="font-size:.6rem;animation:pulse 1s infinite;margin-right:.3rem;"></i>' +
      'Sedang berlangsung</span>';

    timerInterval = setInterval(() => {
      timeLeft--;
      timerEl.textContent = fmt(timeLeft);

      if (timeLeft <= 10) {
        timerEl.style.color = '#ef4444';
        timerEl.style.animation = 'pulse .5s infinite';
      }

      progressBar.style.width = ((timeLeft / DURATION) * 100) + '%';

      if (timeLeft <= 0) {
        clearInterval(timerInterval);
        submitTest();
      }
    }, 1000);
  }

  /* ─────────────────────────────────────────── */
  /* INPUT HANDLER                               */
  /* ─────────────────────────────────────────── */

  input.addEventListener('keydown', e => {
    if (window.isSubmitting) { e.preventDefault(); return; }

    if (!timerStarted && e.key.length === 1) startTimer();

    if (e.key === ' ') {
      e.preventDefault();
      const typed = input.value.trim();
      if (!typed) return;

      typedWords[currentWordIdx] = typed;
      commitWord(currentWordIdx, typed);
      currentWordIdx++;
      wordCountEl.textContent = currentWordIdx;
      input.value = '';

      progressBar.style.width = ((currentWordIdx / TOTAL_WORDS) * 100) + '%';

      if (currentWordIdx >= wordEls.length) {
        submitTest();
      } else {
        activateWord(currentWordIdx);
      }
    }

    if (e.key === 'Backspace' && input.value === '') {
      e.preventDefault();
    }
  });

  input.addEventListener('input', () => {
    if (!window.isSubmitting) updateCharHighlight(input.value);
  });

  ['paste','drop','copy','cut'].forEach(evt =>
    input.addEventListener(evt, e => e.preventDefault())
  );

  /* ─────────────────────────────────────────── */
  /* SUBMIT                                      */
  /* ─────────────────────────────────────────── */

  async function submitTest() {
    if (window.isSubmitting) return;
    window.isSubmitting = true;

    clearInterval(timerInterval);
    input.disabled = true;

    const lastWord = input.value.trim();
    if (lastWord && currentWordIdx < wordEls.length) {
      typedWords[currentWordIdx] = lastWord;
    }

    timerEl.textContent = 'Selesai!';
    timerEl.style.color = '#22c55e';
    statusEl.innerHTML  = '<span style="color:#2563eb;font-weight:700;"><i class="fas fa-check-circle" style="margin-right:.3rem;"></i>Menyimpan...</span>';
    loadingEl.classList.remove('hidden');
    loadingEl.classList.add('flex');

    exitExamMode();

    try {
      const res  = await fetch(SUBMIT_URL, {
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
        loadingEl.classList.add('hidden');
        loadingEl.classList.remove('flex');
        alert('Gagal menyimpan hasil. Silakan hubungi pengawas.');
      }
    } catch (err) {
      loadingEl.classList.add('hidden');
      loadingEl.classList.remove('flex');
      console.error(err);
      alert('Terjadi kesalahan jaringan. Silakan hubungi pengawas.');
    }
  }

});
</script>
@endpush
