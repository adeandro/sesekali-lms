@extends('election.booth.layout')

@section('title', 'Konfirmasi Identitas — ' . $period->title)

@section('content')
<div class="bg-slate-800/90 border border-slate-700 rounded-2xl shadow-2xl p-6 sm:p-8 backdrop-blur text-center">
    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-3xl">
        👤
    </div>

    <h2 class="text-xs font-semibold text-indigo-400 uppercase tracking-widest mb-2">
        Konfirmasi Identitas Pemilih
    </h2>

    {{-- Nama Pemilih (Besar) --}}
    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mb-2">
        {{ $voter->student_name }}
    </h1>

    {{-- Info Kelas & NIS --}}
    <div class="text-sm sm:text-base text-slate-300 font-medium mb-6">
        @if($voter->class_name === 'GURU & STAF')
            <span class="inline-block px-3 py-1 rounded-lg bg-slate-700 text-slate-200 text-xs font-semibold mr-1">GURU &amp; STAF</span>
            ID: <span class="font-mono font-bold text-white">{{ $voter->nis }}</span>
        @else
            Kelas: <span class="font-bold text-white">{{ $voter->class_name ?? '-' }}</span>
            &bull;
            NIS: <span class="font-mono font-bold text-white">{{ $voter->nis }}</span>
        @endif
    </div>

    {{-- Instruksi Pencocokan --}}
    <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 mb-6">
        <p class="text-amber-300 font-semibold text-sm sm:text-base">
            Cocokkan dengan kartu pemilih.
        </p>
        <p class="text-xs text-amber-200/80 mt-1">
            Pastikan nama dan kelas di atas sesuai dengan kartu pemilih fisik yang Anda bawa.
        </p>
    </div>

    {{-- Tombol Aksi --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        {{-- Tombol Merah: Bukan, batalkan --}}
        <form method="POST" action="{{ route('election.cancel') }}">
            @csrf
            <button type="submit"
                    class="w-full py-3.5 px-4 bg-rose-600 hover:bg-rose-700 active:scale-[0.99] text-white font-bold rounded-xl transition text-sm shadow-lg shadow-rose-600/30">
                ✕ Bukan, batalkan
            </button>
        </form>

        {{-- Tombol Hijau: Ya, sesuai kartu --}}
        <form method="POST" action="{{ route('election.confirm') }}">
            @csrf
            <button type="submit"
                    class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-bold rounded-xl transition text-sm shadow-lg shadow-emerald-600/30">
                ✓ Ya, sesuai kartu
            </button>
        </form>
    </div>
</div>
@endsection
