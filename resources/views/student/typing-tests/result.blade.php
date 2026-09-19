@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="text-center mb-2">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4"
             style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary, #818cf8))">
            <i class="fas fa-keyboard text-white text-3xl"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Tes Selesai!</h1>
        <p class="text-gray-500">{{ $test->title }}</p>

        <div class="mt-3 flex items-center justify-center gap-2">
            @if($attempts->count() > 1)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <i class="fas fa-trophy text-amber-500"></i> Nilai Terbaik dari {{ $attempts->count() }} Kesempatan
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                    Kesempatan ke-1 dari {{ $maxAttempts }}
                </span>
            @endif
        </div>
    </div>

    {{-- Retry Prompt jika masih ada kesempatan --}}
    @if($canRetry)
    <div class="bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50 border-2 border-dashed border-indigo-200 rounded-3xl p-6 text-center shadow-xs">
        <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center mx-auto mb-3 shadow-md shadow-indigo-200">
            <i class="fas fa-redo-alt text-lg"></i>
        </div>
        <h3 class="text-lg font-black text-slate-800">Masih Ada Kesempatan Ke-{{ $attempts->count() + 1 }}!</h3>
        <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-md mx-auto">
            Kamu memiliki total <strong>{{ $maxAttempts }} kesempatan</strong>. Sistem otomatis mengambil <strong>nilai tertinggi</strong> dari kedua kesempatan. Jika kesempatan kedua nilainya lebih rendah, nilai pertamamu tetap aman!
        </p>
        <div class="mt-4">
            <a href="{{ route('student.typing-tests.show', $test) }}"
               class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-white font-bold text-sm shadow-md transition hover:opacity-95 hover:scale-[1.02] active:scale-[0.98]"
               style="background: var(--brand-primary, #4f46e5)">
                <i class="fas fa-play"></i> Coba Lagi (Kesempatan Ke-{{ $attempts->count() + 1 }})
            </a>
        </div>
    </div>
    @endif

    {{-- Perbandingan Kesempatan jika sudah >= 2 percobaan --}}
    @if($attempts->count() > 1)
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fas fa-history text-indigo-500"></i> Perbandingan Riwayat Kesempatan
            </h3>
            <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                <i class="fas fa-check-circle mr-1"></i> Nilai Tertinggi Digunakan
            </span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-{{ $attempts->count() }} gap-4">
            @foreach($attempts as $att)
            @php
                $isBest = ($att->id === $bestAttempt->id);
            @endphp
            <div class="relative rounded-2xl p-4 border transition {{ $isBest ? 'bg-indigo-50/50 border-indigo-300 ring-2 ring-indigo-500/20 shadow-sm' : 'bg-slate-50 border-slate-200' }}">
                @if($isBest)
                <span class="absolute -top-2.5 right-3 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-600 text-white shadow-xs">
                    <i class="fas fa-trophy mr-1 text-amber-300"></i> Terbaik
                </span>
                @endif
                <div class="text-xs font-black {{ $isBest ? 'text-indigo-700' : 'text-slate-500' }} mb-2">
                    Kesempatan Ke-{{ $att->attempt_number }}
                </div>
                <div class="grid grid-cols-3 gap-2 text-center">
                    @if($show_wpm_accuracy)
                    <div>
                        <div class="text-base font-black font-mono {{ $isBest ? 'text-indigo-900' : 'text-slate-700' }}">{{ number_format($att->wpm, 1) }}</div>
                        <div class="text-[10px] text-slate-400 font-semibold">WPM</div>
                    </div>
                    <div>
                        <div class="text-base font-black font-mono text-blue-600">{{ number_format($att->accuracy, 1) }}%</div>
                        <div class="text-[10px] text-slate-400 font-semibold">Akurasi</div>
                    </div>
                    @endif
                    @if($show_score)
                    <div>
                        <div class="text-base font-black font-mono text-emerald-600">{{ number_format($att->final_score, 1) }}</div>
                        <div class="text-[10px] text-slate-400 font-semibold">Nilai</div>
                    </div>
                    @endif
                </div>
                <div class="mt-3 pt-2.5 border-t {{ $isBest ? 'border-indigo-100 text-indigo-400' : 'border-slate-200 text-slate-400' }} flex items-center justify-between text-[11px]">
                    <span>Selesai</span>
                    <span class="font-mono">{{ $att->completed_at?->format('H:i') }} WIB</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($show_wpm_accuracy)
    {{-- Card WPM & Akurasi --}}
    <div class="grid grid-cols-2 gap-4" x-data="countUpWpm()" x-init="start()">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 text-center">
            <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">WPM {{ $attempts->count() > 1 ? '(Terbaik)' : '' }}</p>
            <p class="text-4xl font-black tabular-nums font-mono"
               style="color:var(--brand-primary)"
               x-text="Math.round(wpm)">0</p>
            <p class="text-xs text-gray-400 mt-1">Kata/Menit</p>
        </div>
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 text-center">
            <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Akurasi {{ $attempts->count() > 1 ? '(Terbaik)' : '' }}</p>
            <p class="text-4xl font-black tabular-nums font-mono text-emerald-600"
               x-text="accuracy.toFixed(1) + '%'">0%</p>
            <p class="text-xs text-gray-400 mt-1">Ketepatan</p>
        </div>
    </div>
    @endif

    @if($show_score)
    {{-- Card Nilai Akhir --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 text-center" x-data="countUpScore()" x-init="start()">
        <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">
            Nilai Akhir {{ $attempts->count() > 1 ? '(Terbaik - Ke-' . $bestAttempt->attempt_number . ')' : '' }}
        </p>
        <p class="text-6xl font-black tabular-nums font-mono"
           style="color:var(--brand-primary)"
           x-text="Math.round(score)">0</p>

        {{-- Pesan motivasi --}}
        @php
            $score = $bestAttempt->final_score;
            if ($score >= 85) {
                $msg   = ['Luar biasa! 🎉', 'Kemampuan mengetikmu sangat baik.', 'emerald'];
            } elseif ($score >= 70) {
                $msg   = ['Bagus! 👍', 'Terus latihan untuk meningkatkan kecepatan.', 'blue'];
            } elseif ($score >= 55) {
                $msg   = ['Cukup Baik 📈', 'Fokuslah pada akurasi terlebih dahulu.', 'amber'];
            } else {
                $msg   = ['Tetap Semangat! 💪', 'Latihan rutin akan meningkatkan kemampuanmu.', 'gray'];
            }
        @endphp
        <p class="mt-3 font-black text-{{ $msg[2] }}-600">{{ $msg[0] }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ $msg[1] }}</p>
    </div>

    {{-- Detail Hasil --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-700 mb-4 flex items-center justify-between">
            <span>Detail Hasil {{ $attempts->count() > 1 ? '(Nilai Terbaik)' : '' }}</span>
            @if($attempts->count() > 1)
            <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-lg">
                Kesempatan Ke-{{ $bestAttempt->attempt_number }}
            </span>
            @endif
        </h3>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Kata benar</span>
                <span class="font-medium">{{ $bestAttempt->words_correct }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Karakter benar</span>
                <span class="font-medium text-emerald-600">{{ $bestAttempt->characters_correct }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Karakter salah</span>
                <span class="font-medium text-red-500">{{ $bestAttempt->characters_wrong }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Total karakter diketik</span>
                <span class="font-medium">{{ $bestAttempt->characters_total }}</span>
            </div>
            <div class="flex justify-between border-t border-gray-100 pt-3">
                <span class="text-gray-500">Selesai pada</span>
                <span class="font-medium">{{ $bestAttempt->completed_at?->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </div>
    @endif

    @if(!$show_wpm_accuracy && !$show_score)
    {{-- Semua disembunyikan --}}
    <div class="text-center py-8 text-gray-500 bg-white rounded-3xl border border-gray-100 shadow-sm">
        <i class="fas fa-check-circle text-4xl text-emerald-400 mb-3"></i>
        <p class="font-bold text-lg">Tes Mengetik Selesai</p>
        <p class="text-sm mt-1">Terima kasih telah mengerjakan tes ini.</p>
    </div>
    @endif

    {{-- Tombol Navigasi --}}
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        @if($canRetry)
            <a href="{{ route('student.typing-tests.show', $test) }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl text-white font-bold shadow-md transition hover:opacity-90"
               style="background: var(--brand-primary)">
                <i class="fas fa-redo-alt"></i> Coba Lagi (Ke-{{ $attempts->count() + 1 }})
            </a>
            <a href="{{ route('student.exams.index') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium transition">
                <i class="fas fa-home"></i> Kembali ke Dashboard
            </a>
        @else
            <a href="{{ route('student.exams.index') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl text-white font-medium shadow-md transition hover:opacity-90"
               style="background: var(--brand-primary)">
                <i class="fas fa-home"></i> Kembali ke Dashboard Ujian
            </a>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function countUpWpm() {
    return {
        wpm:      0,
        accuracy: 0,
        start() {
            const targets = {
                wpm:      {{ $attempt->wpm ?? 0 }},
                accuracy: {{ $attempt->accuracy ?? 0 }},
            };
            const steps    = 60;
            const interval = 1500 / steps;
            let step = 0;
            const timer = setInterval(() => {
                step++;
                const progress  = step / steps;
                this.wpm        = targets.wpm      * progress;
                this.accuracy   = targets.accuracy * progress;
                if (step >= steps) {
                    clearInterval(timer);
                    this.wpm      = targets.wpm;
                    this.accuracy = targets.accuracy;
                }
            }, interval);
        }
    };
}

function countUpScore() {
    return {
        score: 0,
        start() {
            const target   = {{ $attempt->final_score ?? 0 }};
            const steps    = 60;
            const interval = 1500 / steps;
            let step = 0;
            const timer = setInterval(() => {
                step++;
                this.score = target * (step / steps);
                if (step >= steps) {
                    clearInterval(timer);
                    this.score = target;
                }
            }, interval);
        }
    };
}
</script>
@endpush
