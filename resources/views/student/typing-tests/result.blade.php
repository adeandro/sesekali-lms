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
    </div>

    @if($show_wpm_accuracy)
    {{-- Card WPM & Akurasi --}}
    <div class="grid grid-cols-2 gap-4" x-data="countUpWpm()" x-init="start()">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center">
            <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">WPM</p>
            <p class="text-4xl font-black tabular-nums font-mono"
               style="color:var(--brand-primary)"
               x-text="Math.round(wpm)">0</p>
            <p class="text-xs text-gray-400 mt-1">Kata/Menit</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center">
            <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Akurasi</p>
            <p class="text-4xl font-black tabular-nums font-mono text-emerald-600"
               x-text="accuracy.toFixed(1) + '%'">0%</p>
            <p class="text-xs text-gray-400 mt-1">Ketepatan</p>
        </div>
    </div>
    @endif

    @if($show_score)
    {{-- Card Nilai Akhir --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center" x-data="countUpScore()" x-init="start()">
        <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Nilai Akhir</p>
        <p class="text-6xl font-black tabular-nums font-mono"
           style="color:var(--brand-primary)"
           x-text="Math.round(score)">0</p>

        {{-- Pesan motivasi --}}
        @php
            $score = $attempt->final_score;
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
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Detail Hasil</h3>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Kata benar</span>
                <span class="font-medium">{{ $attempt->words_correct }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Karakter benar</span>
                <span class="font-medium text-emerald-600">{{ $attempt->characters_correct }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Karakter salah</span>
                <span class="font-medium text-red-500">{{ $attempt->characters_wrong }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Total karakter diketik</span>
                <span class="font-medium">{{ $attempt->characters_total }}</span>
            </div>
            <div class="flex justify-between border-t border-gray-100 pt-3">
                <span class="text-gray-500">Selesai pada</span>
                <span class="font-medium">{{ $attempt->completed_at?->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </div>
    @endif

    @if(!$show_wpm_accuracy && !$show_score)
    {{-- Semua disembunyikan --}}
    <div class="text-center py-8 text-gray-500 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <i class="fas fa-check-circle text-4xl text-emerald-400 mb-3"></i>
        <p class="font-bold text-lg">Tes Mengetik Selesai</p>
        <p class="text-sm mt-1">Terima kasih telah mengerjakan tes ini.</p>
    </div>
    @endif

    {{-- Tombol Kembali --}}
    <div class="text-center">
        <a href="{{ route('student.exams.index') }}"
           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-medium shadow-md transition hover:opacity-90"
           style="background: var(--brand-primary)">
            <i class="fas fa-home"></i> Kembali ke Dashboard Ujian
        </a>
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
