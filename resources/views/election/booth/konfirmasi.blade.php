@extends('election.booth.layout')

@section('title', 'Konfirmasi Identitas — ' . $period->title)
@section('container_width', 'max-w-2xl')

@section('content')
@php
    $displayName  = $voter->student_name ?? '-';
    $nameUpper    = mb_strtoupper($displayName, 'UTF-8');
    $className    = $voter->class_name ?? '-';
    $nis          = $voter->nis ?? '-';
    $isGuruStaf   = ($className === 'GURU & STAF');

    // Inisial: 1 huruf dari kata 1 + 1 huruf dari kata 2 (atau 2 huruf pertama jika hanya 1 kata)
    $words    = preg_split('/\s+/', trim($displayName));
    $initials = count($words) >= 2
        ? mb_strtoupper(mb_substr($words[0], 0, 1, 'UTF-8') . mb_substr($words[1], 0, 1, 'UTF-8'), 'UTF-8')
        : mb_strtoupper(mb_substr($words[0] ?? '', 0, 2, 'UTF-8'), 'UTF-8');
@endphp

<div x-data="{ hasPhoto: {{ $photoUrl ? 'true' : 'false' }} }" class="bg-slate-800/90 border border-slate-700 rounded-2xl shadow-2xl p-6 sm:p-8 backdrop-blur">

    <h2 class="text-xs font-semibold text-indigo-400 uppercase tracking-widest mb-6 text-center">
        Konfirmasi Identitas Pemilih
    </h2>

    {{-- Layout: Foto besar (±220px) di kiri/atas + Info di samping/bawah --}}
    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 sm:gap-8 mb-6">

        {{-- Foto atau Lingkaran Inisial Besar --}}
        <div class="flex-shrink-0 flex items-center justify-center">
            {{-- Jika ada URL foto --}}
            @if($photoUrl)
                <img
                    x-show="hasPhoto"
                    src="{{ $photoUrl }}"
                    alt="Foto {{ $displayName }}"
                    x-on:error="hasPhoto = false"
                    class="w-[200px] sm:w-[220px] rounded-2xl object-cover object-top shadow-xl border border-slate-700"
                    style="aspect-ratio: 3/4; background: #1e293b;"
                >
            @endif

            {{-- Lingkaran Inisial Besar (fallback jika tidak ada foto atau gambar gagal dimuat) --}}
            <div
                x-show="!hasPhoto"
                x-cloak
                class="w-36 h-36 sm:w-48 sm:h-48 rounded-full bg-gradient-to-br from-indigo-600/40 to-slate-700 border-2 border-indigo-400/30 flex items-center justify-center shadow-xl shadow-indigo-950/50"
            >
                <span class="text-5xl sm:text-6xl font-black text-indigo-200 tracking-tight">{{ $initials }}</span>
            </div>
        </div>

        {{-- Info Pemilih: NAMA (besar, kapital), KELAS, NIS --}}
        <div class="flex-1 flex flex-col justify-center text-center sm:text-left self-center">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1.5">Nama Pemilih</p>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight leading-snug mb-4">
                {{ $nameUpper }}
            </h1>

            @if($isGuruStaf)
                <div class="flex flex-wrap gap-2.5 justify-center sm:justify-start">
                    <span class="inline-block px-3.5 py-1.5 rounded-lg bg-slate-700 text-slate-200 text-xs font-semibold">GURU &amp; STAF</span>
                    <span class="inline-block px-3.5 py-1.5 rounded-lg bg-slate-700/60 text-slate-300 text-xs font-mono">ID: {{ $nis }}</span>
                </div>
            @else
                <div class="space-y-1.5 text-slate-300 text-sm sm:text-base">
                    <div>
                        <span class="text-slate-400 text-xs uppercase tracking-wider block">Kelas</span>
                        <span class="font-bold text-white text-base sm:text-lg">{{ $className }}</span>
                    </div>
                    <div class="pt-1">
                        <span class="text-slate-400 text-xs uppercase tracking-wider block">NIS</span>
                        <span class="font-mono font-bold text-white text-base sm:text-lg">{{ $nis }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Pesan Bantu --}}
    <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 mb-6">
        <template x-if="hasPhoto">
            <p class="text-amber-300 font-semibold text-sm sm:text-base">
                Cocokkan wajah dan nama dengan kartu serta orang di bilik.
            </p>
        </template>
        <template x-if="!hasPhoto">
            <p class="text-amber-300 font-semibold text-sm sm:text-base">
                Foto belum tersedia. Cocokkan nama dengan kartu dan identitas siswa.
            </p>
        </template>
        <p class="text-xs text-amber-200/70 mt-1">
            Pastikan data di atas sesuai dengan pemilih sebelum melanjutkan ke bilik suara.
        </p>
    </div>

    {{-- Tombol Aksi Tetap --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        {{-- Tombol Merah: Bukan, batalkan --}}
        <form method="POST" action="{{ route('election.cancel') }}">
            @csrf
            <button type="submit"
                    class="w-full py-3.5 px-4 bg-rose-600 hover:bg-rose-700 active:scale-[0.99] text-white font-bold rounded-xl transition text-sm sm:text-base shadow-lg shadow-rose-600/30">
                ✕ Bukan, batalkan
            </button>
        </form>

        {{-- Tombol Hijau: Ya, sesuai kartu --}}
        <form method="POST" action="{{ route('election.confirm') }}">
            @csrf
            <button type="submit"
                    class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-bold rounded-xl transition text-sm sm:text-base shadow-lg shadow-emerald-600/30">
                ✓ Ya, sesuai kartu
            </button>
        </form>
    </div>
</div>
@endsection

