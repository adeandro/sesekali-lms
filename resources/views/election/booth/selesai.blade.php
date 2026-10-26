@extends('election.booth.layout')

@section('title', 'Suara Terkirim — Selesai')

@section('content')
<div class="bg-slate-800/90 border border-slate-700 rounded-2xl shadow-2xl p-8 text-center backdrop-blur"
     x-data="{
         countdown: 6,
         init() {
             let timer = setInterval(() => {
                 this.countdown--;
                 if (this.countdown <= 0) {
                     clearInterval(timer);
                     window.location.href = '{{ route('election.index') }}';
                 }
             }, 1000);
         }
     }">

    {{-- Success Checkmark --}}
    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-emerald-500/10 border-2 border-emerald-500/30 flex items-center justify-center text-4xl text-emerald-400">
        ✓
    </div>

    <h2 class="text-2xl font-black text-white">TERIMA KASIH!</h2>
    <p class="text-base font-semibold text-emerald-400 mt-1">Suara Anda Berhasil Dicatat</p>

    <div class="my-6 p-4 rounded-xl bg-slate-900/70 border border-slate-700/80 text-xs text-slate-300 leading-relaxed text-left space-y-2">
        <div class="flex items-start gap-2">
            <span class="text-emerald-400 font-bold">1.</span>
            <span>Pilihan Anda telah dienkripsi dan disimpan ke dalam kotak suara digital secara anonim.</span>
        </div>
        <div class="flex items-start gap-2">
            <span class="text-emerald-400 font-bold">2.</span>
            <span class="text-amber-300 font-bold">Silakan serahkan kertas kredensial (kartu token) Anda kepada petugas panitia di luar bilik.</span>
        </div>
    </div>

    <div class="text-xs text-slate-400">
        Layar akan kembali otomatis ke halaman awal dalam
        <span class="font-bold text-white font-mono text-sm" x-text="countdown">6</span> detik...
    </div>

    <div class="mt-4">
        <a href="{{ route('election.index') }}"
           class="inline-block px-5 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-xs font-semibold text-slate-200 transition">
            Kembali ke Awal Sekarang &rarr;
        </a>
    </div>
</div>
@endsection
