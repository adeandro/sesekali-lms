@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto" x-data="typingArena()" x-init="init()">

    {{-- Header Bar --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold" style="color: var(--brand-primary)">{{ $test->title }}</h1>
            <p class="text-sm text-gray-500">Durasi: {{ $test->duration_seconds }} detik | Target: {{ $test->target_wpm }} WPM</p>
        </div>
        <div class="text-right">
            <div class="text-3xl font-mono font-bold tabular-nums"
                 :class="timeLeft <= 10 ? 'text-red-500 animate-pulse' : ''"
                 style="color: var(--brand-primary)"
                 x-text="formatTime(timeLeft)">
                {{ gmdate('i:s', $test->duration_seconds) }}
            </div>
            <p class="text-xs text-gray-400">Sisa Waktu</p>
        </div>
    </div>

    {{-- Instruksi --}}
    <div x-show="!started" class="mb-4 p-4 rounded-xl text-sm text-center"
         style="background: var(--brand-bg, #f0fdf4); color: var(--brand-primary)">
        <i class="fas fa-info-circle mr-1"></i>
        Klik area teks di bawah, lalu mulai mengetik. Timer otomatis berjalan saat Anda mulai.
    </div>

    {{-- Typing Arena Card --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-4"
         @click="focusInput()">

        {{-- Teks Display --}}
        <div id="text-display"
             class="font-mono text-lg leading-relaxed select-none mb-6 min-h-[8rem] cursor-text overflow-hidden"
             style="line-height: 2.5rem; max-height: 10rem;">
            <span id="chars-container"></span>
        </div>

        {{-- Input Field --}}
        <input
            type="text"
            id="typing-input"
            x-ref="typingInput"
            class="w-full border-2 rounded-xl px-4 py-3 text-sm font-mono focus:outline-none transition"
            style="border-color: var(--brand-primary); opacity: 0.85;"
            placeholder="Mulai mengetik di sini..."
            autocomplete="off"
            autocorrect="off"
            autocapitalize="off"
            spellcheck="false"
            :disabled="finished"
            @keydown="onKeyDown($event)"
            @input="onInput($event)"
            @copy.prevent
            @paste.prevent
            @cut.prevent
        >
    </div>

    {{-- Status Bar --}}
    <div class="flex items-center justify-between text-sm text-gray-500 px-1">
        <div>Kata: <span class="font-semibold text-gray-700" x-text="wordIndex"></span></div>
        <div x-show="!started" class="text-xs text-gray-400 italic">Timer belum berjalan</div>
        <div x-show="started && !finished" class="text-xs text-emerald-600 font-medium">
            <i class="fas fa-circle animate-pulse mr-1"></i>Sedang berlangsung
        </div>
        <div x-show="finished" class="text-xs text-blue-600 font-medium">
            <i class="fas fa-check-circle mr-1"></i>Selesai! Menyimpan...
        </div>
    </div>

    {{-- Loading Overlay --}}
    <div x-show="submitting" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-8 text-center shadow-2xl">
            <div class="w-12 h-12 border-4 rounded-full animate-spin mx-auto mb-4"
                 style="border-color: var(--brand-primary); border-top-color: transparent"></div>
            <p class="text-gray-700 font-medium">Menyimpan hasil tes...</p>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function typingArena() {
    return {
        // State
        totalSeconds:    {{ $test->duration_seconds }},
        timeLeft:        {{ $test->duration_seconds }},
        started:         false,
        finished:        false,
        submitting:      false,
        timer:           null,

        // Kata-kata
        allWords:        @json(explode(' ', $attempt->words_generated)),
        wordIndex:       0,
        typedWords:      [],
        currentInput:    '',

        // Test config
        testId:          {{ $test->id }},
        submitUrl:       '{{ route('student.typing-tests.submit', $test) }}',
        resultUrl:       '{{ route('student.typing-tests.result', $test) }}',
        csrfToken:       '{{ csrf_token() }}',

        // ─── Init ───────────────────────────────────────────
        init() {
            this.renderChars();
            this.$nextTick(() => {
                if (this.$refs.typingInput) {
                    this.$refs.typingInput.focus();
                }
            });
        },

        // ─── Render karakter per span ─────────────────────
        renderChars() {
            const container = document.getElementById('chars-container');
            if (!container) return;
            container.innerHTML = '';

            this.allWords.forEach((word, wIdx) => {
                const wordSpan = document.createElement('span');
                wordSpan.id = `word-${wIdx}`;
                wordSpan.style.marginRight = '0.4rem';

                let wordStatus = 'pending';
                if (wIdx < this.typedWords.length) {
                    wordStatus = this.typedWords[wIdx] === word ? 'correct' : 'error';
                } else if (wIdx === this.wordIndex) {
                    wordStatus = 'active';
                }

                word.split('').forEach((ch, cIdx) => {
                    const charSpan = document.createElement('span');
                    charSpan.textContent = ch;

                    if (wordStatus === 'pending') {
                        charSpan.style.color = '#9ca3af';
                    } else if (wordStatus === 'correct') {
                        charSpan.style.color = '#16a34a';
                    } else if (wordStatus === 'error') {
                        charSpan.style.color = '#dc2626';
                    } else if (wordStatus === 'active') {
                        if (cIdx < this.currentInput.length) {
                            if (this.currentInput[cIdx] === ch) {
                                charSpan.style.color = '#16a34a';
                            } else {
                                charSpan.style.color = '#dc2626';
                                charSpan.style.borderBottom = '2px solid #dc2626';
                            }
                        } else if (cIdx === this.currentInput.length) {
                            charSpan.style.borderBottom = '2px solid var(--brand-primary, #4f46e5)';
                            charSpan.style.color = '#374151';
                        } else {
                            charSpan.style.color = '#374151';
                        }
                    }

                    wordSpan.appendChild(charSpan);
                });

                container.appendChild(wordSpan);

                if (wIdx < this.allWords.length - 1) {
                    const space = document.createElement('span');
                    space.textContent = ' ';
                    space.style.color = '#d1d5db';
                    container.appendChild(space);
                }
            });

            const activeWord = document.getElementById(`word-${this.wordIndex}`);
            if (activeWord) {
                activeWord.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        },

        // ─── Mulai Timer ────────────────────────────────
        startTimer() {
            if (this.started) return;
            this.started = true;
            this.timer = setInterval(() => {
                this.timeLeft--;
                if (this.timeLeft <= 0) {
                    this.timeLeft = 0;
                    clearInterval(this.timer);
                    this.endTest();
                }
            }, 1000);
        },

        // ─── Format timer ───────────────────────────────
        formatTime(seconds) {
            const m = String(Math.floor(seconds / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            return `${m}:${s}`;
        },

        // ─── Key Events ────────────────────────────────
        onKeyDown(e) {
            if (this.finished) { e.preventDefault(); return; }

            if (!this.started) this.startTimer();

            if (e.key === ' ') {
                e.preventDefault();
                this.commitWord();
            }
        },

        // ─── Input Event ────────────────────────────────
        onInput(e) {
            this.currentInput = e.target.value;
            this.renderChars();
        },

        // ─── Commit kata saat spasi ─────────────────────
        commitWord() {
            const typed = this.$refs.typingInput.value.trim();
            if (typed === '') return;

            this.typedWords.push(typed);
            this.wordIndex++;
            this.currentInput = '';
            this.$refs.typingInput.value = '';

            if (this.wordIndex >= this.allWords.length) {
                this.endTest();
            } else {
                this.renderChars();
            }
        },

        // ─── Akhiri tes ─────────────────────────────────
        endTest() {
            if (this.finished) return;
            this.finished = true;
            clearInterval(this.timer);

            const remaining = this.$refs.typingInput.value.trim();
            if (remaining) {
                this.typedWords.push(remaining);
            }
            this.$refs.typingInput.disabled = true;

            this.submitResult();
        },

        // ─── Submit via AJAX ────────────────────────────
        async submitResult() {
            this.submitting = true;
            const wordsTyped = this.typedWords.join(' ');

            try {
                const response = await fetch(this.submitUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ words_typed: wordsTyped }),
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = this.resultUrl;
                } else {
                    this.submitting = false;
                    alert('Gagal menyimpan hasil. Silakan coba lagi.');
                }
            } catch (err) {
                this.submitting = false;
                console.error('Submit error:', err);
                alert('Terjadi kesalahan jaringan. Silakan hubungi pengawas.');
            }
        },

        // ─── Fokus input ────────────────────────────────
        focusInput() {
            if (!this.finished && this.$refs.typingInput) {
                this.$refs.typingInput.focus();
            }
        },
    };
}
</script>
@endpush
