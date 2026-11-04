@extends('ct.layout')
@section('title', 'Papan Skor — Kelas ' . $class->name)
@section('container_width', 'max-w-2xl')

@section('header_right')
    Papan Skor · Kelas {{ $class->name }}
@endsection

@section('content')

{{-- Header --}}
<div class="text-center mb-8">
    <div class="text-5xl mb-3">🏆</div>
    <h1 class="text-2xl font-black text-white tracking-tight">Papan Skor</h1>
    <p class="text-slate-400 text-sm mt-1">Kelas {{ $class->name }} — CT Escape Room</p>
</div>

{{-- Progress bar --}}
@php $pct = $total > 0 ? round(($opened->count() / $total) * 100) : 0; @endphp
<div class="mb-6 bg-slate-800 border border-slate-700 rounded-2xl p-4">
    <div class="flex justify-between text-xs text-slate-400 mb-2">
        <span>Brankas terbuka</span>
        <span class="font-bold text-white">{{ $opened->count() }} / {{ $total }} tim</span>
    </div>
    <div class="w-full bg-slate-700 rounded-full h-2">
        <div class="bg-green-500 h-2 rounded-full transition-all" style="width:{{ $pct }}%"></div>
    </div>
    <p class="text-xs text-slate-500 mt-2 text-center">
        {{ $pct }}% kelompok sudah berhasil membuka brankas
    </p>
</div>

{{-- Opened teams --}}
@if($opened->isEmpty())
    <div class="text-center py-12 text-slate-500">
        <div class="text-4xl mb-3">🔒</div>
        <p class="font-medium">Belum ada kelompok yang berhasil membuka brankas.</p>
        <p class="text-sm mt-1">Jadilah yang pertama!</p>
    </div>
@else
    <div class="space-y-3">
        @foreach($opened as $i => $t)
        @php
            $rank   = $i + 1;
            $medal  = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => '#' . $rank };
            $isGold = $rank === 1;
        @endphp
        <div class="flex items-center gap-4 bg-slate-800 border {{ $isGold ? 'border-yellow-600/50' : 'border-slate-700' }} rounded-2xl p-4">
            {{-- Rank --}}
            <div class="text-2xl min-w-[2.5rem] text-center">{{ $medal }}</div>

            {{-- Team info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-white">Tim {{ $t->number }}</span>
                    @if($t->hint_count > 0)
                        <span class="text-xs px-1.5 py-0.5 bg-amber-900/50 text-amber-400 rounded-lg">
                            💡 {{ $t->hint_count }}× petunjuk
                        </span>
                    @endif
                    @if($t->wrong_count > 0)
                        <span class="text-xs px-1.5 py-0.5 bg-red-900/30 text-red-400 rounded-lg">
                            {{ $t->wrong_count }}× salah
                        </span>
                    @endif
                </div>
                {{-- Member names (shown to celebrate the team) --}}
                @if($t->member_names)
                    <p class="text-xs text-slate-400 mt-0.5 truncate">
                        {{ implode(' · ', $t->member_names) }}
                    </p>
                @endif
            </div>

            {{-- Time --}}
            <div class="text-right flex-shrink-0">
                <p class="text-xs font-mono font-bold text-green-400">
                    {{ $t->opened_at->format('H:i:s') }}
                </p>
                <p class="text-xs text-slate-500">{{ $t->opened_at->format('d/m/Y') }}</p>
            </div>
        </div>
        @endforeach
    </div>
@endif

{{-- Not-yet-opened teams placeholder --}}
@php $notYet = $total - $opened->count(); @endphp
@if($notYet > 0)
    <div class="mt-4 bg-slate-800/40 border border-dashed border-slate-700 rounded-2xl p-4 text-center">
        <p class="text-xs text-slate-500">
            Masih ada <strong class="text-slate-400">{{ $notYet }} kelompok</strong> yang sedang berusaha membuka brankas…
        </p>
    </div>
@endif

{{-- Back link --}}
<div class="mt-6 text-center">
    <a href="{{ route('ct.index') }}" class="text-sm text-slate-500 hover:text-slate-300 transition">
        &larr; Kembali ke pilih kelas
    </a>
</div>

@endsection
