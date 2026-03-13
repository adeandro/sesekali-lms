@extends('layouts.blank')

@section('title', 'Battle Selesai')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-950 to-slate-900 flex flex-col items-center justify-center p-6 text-center space-y-6">

    @php
        $isAlive = $participant->status !== 'disqualified';
    @endphp

    <div class="text-7xl">{{ $isAlive ? '🎉' : '💀' }}</div>

    <div class="space-y-2">
        <h1 class="text-3xl font-black text-white tracking-tight">
            {{ $isAlive ? 'Battle Selesai!' : 'Kamu Gugur!' }}
        </h1>
        <p class="text-gray-400 text-sm">
            {{ $isAlive ? 'Kamu berhasil menyelesaikan semua soal dengan selamat!' : 'HP-mu habis. Tetap semangat untuk battle berikutnya!' }}
        </p>
    </div>

    {{-- Stats --}}
    <div class="w-full max-w-sm bg-white/5 rounded-[2rem] border border-white/10 p-6 space-y-4">
        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Statistik Kamu</p>
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white/5 rounded-2xl p-3">
                <p class="text-2xl font-black text-emerald-400">{{ $participant->correct_count }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5">Benar</p>
            </div>
            <div class="bg-white/5 rounded-2xl p-3">
                <p class="text-2xl font-black text-red-400">{{ $participant->wrong_count }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5">Salah</p>
            </div>
            <div class="bg-white/5 rounded-2xl p-3">
                <p class="text-2xl font-black {{ $isAlive ? 'text-white' : 'text-red-500' }}">{{ $participant->hp }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5">HP Sisa</p>
            </div>
        </div>

        @if($participant->rank)
        <div class="text-center pt-2">
            <p class="text-[10px] text-gray-500 uppercase tracking-widest">Peringkat Akhir</p>
            <p class="text-4xl font-black text-amber-400 mt-1">#{{ $participant->rank }}</p>
        </div>
        @endif
    </div>

    <p class="text-gray-500 text-xs">Hasil lengkap akan ditampilkan oleh admin di podium.</p>

    <a href="{{ route('dashboard') }}"
       class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-lg hover:-translate-y-0.5 transition-all">
        <i class="fas fa-home"></i> Kembali ke Dashboard
    </a>
</div>
@endsection
