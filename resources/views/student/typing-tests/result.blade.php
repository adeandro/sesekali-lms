@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    @if($test->show_result)
    {{-- Hasil Lengkap --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4"
             style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary, #818cf8))">
            <i class="fas fa-keyboard text-white text-3xl"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Tes Selesai!</h1>
        <p class="text-gray-500">{{ $test->title }}</p>
    </div>

    {{-- Score Cards --}}
    <div class="grid grid-cols-3 gap-4 mb-8" x-data="countUp()" x-init="start()">
        {{-- WPM --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
            <div class="text-4xl font-bold font-mono tabular-nums" style="color: var(--brand-primary)"
                 x-text="Math.round(wpm)">0</div>
            <div class="text-sm text-gray-500 mt-1 font-medium">WPM</div>
            <div class="text-xs text-gray-400 mt-0.5">Kata/Menit</div>
        </div>
        {{-- Akurasi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
            <div class="text-4xl font-bold font-mono tabular-nums text-blue-600"
                 x-text="accuracy.toFixed(1) + '%'">0%</div>
            <div class="text-sm text-gray-500 mt-1 font-medium">Akurasi</div>
            <div class="text-xs text-gray-400 mt-0.5">Ketepatan</div>
        </div>
        {{-- Nilai --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
            <div class="text-4xl font-bold font-mono tabular-nums"
                 :class="finalScore >= 85 ? 'text-emerald-600' : finalScore >= 70 ? 'text-amber-600' : 'text-red-600'"
                 x-text="finalScore.toFixed(1)">0</div>
            <div class="text-sm text-gray-500 mt-1 font-medium">Nilai</div>
            <div class="text-xs text-gray-400 mt-0.5">0 - 100</div>
        </div>
    </div>

    {{-- Pesan Motivasi --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center mb-6">
        @php
            $score = $attempt->final_score;
            if ($score >= 85) {
                $msg = '🎉 Luar biasa! Kemampuan mengetikmu sangat baik.';
                $color = 'text-emerald-600';
            } elseif ($score >= 70) {
                $msg = '👍 Bagus! Terus latihan untuk meningkatkan kecepatan.';
                $color = 'text-blue-600';
            } elseif ($score >= 55) {
                $msg = '✏️ Cukup baik. Fokuslah pada akurasi terlebih dahulu.';
                $color = 'text-amber-600';
            } else {
                $msg = '💪 Tetap semangat! Latihan rutin akan meningkatkan kemampuanmu.';
                $color = 'text-gray-600';
            }
        @endphp
        <p class="text-lg font-medium {{ $color }}">{{ $msg }}</p>
    </div>

    {{-- Detail --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
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

    @else
    {{-- Tanpa nilai --}}
    <div class="text-center py-16">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-6"
             style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary, #818cf8))">
            <i class="fas fa-check text-white text-4xl"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-3">Tes Mengetik Selesai</h1>
        <p class="text-gray-500">Terima kasih! Hasil tes Anda telah disimpan.</p>
    </div>
    @endif

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
function countUp() {
    return {
        wpm:        0,
        accuracy:   0,
        finalScore: 0,
        start() {
            const targets = {
                wpm:        {{ $attempt->wpm ?? 0 }},
                accuracy:   {{ $attempt->accuracy ?? 0 }},
                finalScore: {{ $attempt->final_score ?? 0 }},
            };
            const duration = 1500;
            const steps = 60;
            const interval = duration / steps;
            let step = 0;
            const timer = setInterval(() => {
                step++;
                const progress = step / steps;
                this.wpm        = targets.wpm        * progress;
                this.accuracy   = targets.accuracy   * progress;
                this.finalScore = targets.finalScore * progress;
                if (step >= steps) {
                    clearInterval(timer);
                    this.wpm        = targets.wpm;
                    this.accuracy   = targets.accuracy;
                    this.finalScore = targets.finalScore;
                }
            }, interval);
        }
    };
}
</script>
@endpush
