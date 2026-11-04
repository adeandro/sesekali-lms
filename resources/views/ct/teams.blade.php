@extends('ct.layout')
@section('title', 'Pilih Kelompok — Kelas ' . $class->name)

@section('header_right')
    Kelas {{ $class->name }}
@endsection

@section('content')
<div class="text-center mb-6">
    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-red-800/30 border border-red-700/40 text-3xl mb-3">👥</div>
    <h2 class="text-xl font-bold text-white">Kelas {{ $class->name }}</h2>
    <p class="text-slate-400 text-sm mt-1">Pilih nomor kelompok tim Anda</p>
    @if($class->closes_at)
        <p class="text-xs text-amber-400 mt-1">Sesi tutup pukul {{ $class->closes_at->format('H:i') }}</p>
    @endif
</div>

@if($teams->isEmpty())
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-8 text-center text-slate-400">
        Belum ada kelompok yang digenerate untuk kelas ini.
    </div>
@else
    <div class="grid grid-cols-3 gap-3">
        @foreach($teams as $team)
            @if($team->isOpened())
                {{-- Already opened: link to certificate --}}
                <a href="{{ route('ct.certificate', $team->certificate_no) }}"
                   class="relative bg-green-900/20 border border-green-700/40 rounded-2xl p-4 flex flex-col items-center gap-2 hover:bg-green-900/30 transition group">
                    <span class="text-3xl font-bold text-green-400">{{ $team->number }}</span>
                    <span class="text-xs text-green-400 font-semibold flex items-center gap-1">
                        <span>✓</span> Terbuka
                    </span>
                    <span class="text-xs text-green-600 group-hover:text-green-400 transition">Lihat sertifikat →</span>
                </a>
            @else
                {{-- Not opened: link to vault form --}}
                <a href="{{ route('ct.vault', ['class' => $class, 'team' => $team]) }}"
                   class="bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-red-600/60 rounded-2xl p-4 flex flex-col items-center gap-2 transition group">
                    <span class="text-3xl font-bold text-white group-hover:text-red-300 transition">{{ $team->number }}</span>
                    <span class="text-xs text-slate-400">
                        @if($team->wrong_count > 0)
                            <span class="text-amber-400">{{ $team->wrong_count }}✗</span>
                        @else
                            Belum terbuka
                        @endif
                    </span>
                </a>
            @endif
        @endforeach
    </div>
@endif

<div class="mt-6 text-center space-y-2">
    <a href="{{ route('ct.index') }}" class="block text-sm text-slate-500 hover:text-slate-300 transition">&larr; Ganti kelas</a>
    <a href="{{ route('ct.leaderboard', $class) }}" class="block text-xs text-slate-600 hover:text-slate-400 transition">
        🏆 Papan Skor Kelas {{ $class->name }}
    </a>
</div>
@endsection
