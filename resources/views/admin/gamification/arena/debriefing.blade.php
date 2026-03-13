@extends('layouts.blank')

@section('title', 'Debriefing — ' . $room->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-950 to-slate-900 p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-indigo-400">Post-Battle Analysis</p>
            <h1 class="text-2xl font-black text-white tracking-tight">📊 Debriefing</h1>
            <p class="text-gray-400 text-sm mt-1">{{ $room->name }}</p>
        </div>
        <a href="{{ route('admin.gamification.arena.podium', $room) }}"
           class="px-4 py-2 bg-white/10 hover:bg-white/20 text-gray-300 text-xs font-black rounded-xl border border-white/10 transition">
            <i class="fas fa-trophy mr-1"></i> Ke Podium
        </a>
    </div>

    {{-- Stats Overview --}}
    @php
        $totalParticipants = $room->participants->count();
        $activeCount = $room->participants->where('status', 'active')->count() + $room->participants->where('status', 'finished')->count();
        $fallenCount = $room->participants->where('status', 'disqualified')->count();
        $avgCorrect = $room->participants->avg('correct_count');
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['Peserta', $totalParticipants, 'fa-users', 'from-indigo-500 to-purple-500'],
            ['Selamat', $activeCount, 'fa-shield-alt', 'from-emerald-500 to-teal-500'],
            ['Gugur', $fallenCount, 'fa-skull', 'from-red-500 to-orange-500'],
            ['Avg Benar', number_format($avgCorrect, 1), 'fa-check-circle', 'from-amber-400 to-orange-400'],
        ] as [$label, $val, $icon, $grad])
        <div class="bg-white/5 rounded-[1.5rem] border border-white/10 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $grad }} flex items-center justify-center shrink-0">
                <i class="fas {{ $icon }} text-white text-sm"></i>
            </div>
            <div>
                <p class="text-xl font-black text-white">{{ $val }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">{{ $label }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Toughest Questions --}}
    <div class="bg-white/5 rounded-[1.5rem] border border-white/10 p-6 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-red-500 to-orange-500 flex items-center justify-center">
                <i class="fas fa-fire text-white text-sm"></i>
            </div>
            <div>
                <h2 class="text-sm font-black text-white uppercase tracking-wide">Soal Terpanas</h2>
                <p class="text-[10px] text-gray-400">Soal dengan persentase jawaban salah tertinggi</p>
            </div>
        </div>

        @forelse($toughest as $idx => $answer)
        @php $q = $answer->question; @endphp
        @if($q)
        <div class="bg-white/5 rounded-2xl border border-white/5 p-4 flex gap-4">
            <div class="shrink-0 w-8 h-8 rounded-full bg-red-500/20 border border-red-500/30 flex items-center justify-center">
                <span class="text-xs font-black text-red-400">{{ $idx + 1 }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-white font-medium leading-relaxed line-clamp-3">
                    {!! Str::limit(strip_tags($q->question_text), 160) !!}
                </p>
                <div class="flex items-center gap-3 mt-2">
                    <span class="text-[10px] text-red-400 font-black bg-red-500/10 px-2 py-1 rounded-full border border-red-500/20">
                        <i class="fas fa-times mr-1"></i> {{ $answer->wrong_count }} salah
                    </span>
                    <span class="text-[10px] text-gray-400">Jawaban benar: <span class="text-emerald-400 font-black uppercase">{{ $q->correct_answer }}</span></span>
                </div>
            </div>
        </div>
        @endif
        @empty
        <div class="text-center py-8">
            <i class="fas fa-check-circle text-3xl text-emerald-400 mb-3 block"></i>
            <p class="text-gray-400 text-sm">Tidak ada data jawaban untuk ditampilkan.</p>
        </div>
        @endforelse
    </div>

    {{-- Leaderboard --}}
    <div class="bg-white/5 rounded-[1.5rem] border border-white/10 p-6 space-y-3">
        <h2 class="text-sm font-black text-white uppercase tracking-wide mb-4">📋 Papan Skor Final</h2>
        @foreach($room->participants()->with('user')->orderBy('rank')->orderByDesc('correct_count')->get() as $p)
        <div class="flex items-center gap-3 py-2 border-b border-white/5 last:border-0">
            <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-black shrink-0
                @if($p->rank == 1) bg-amber-400/20 text-amber-400
                @elseif($p->rank == 2) bg-slate-400/20 text-slate-300
                @elseif($p->rank == 3) bg-orange-700/20 text-orange-500
                @else bg-white/5 text-gray-400 @endif">
                {{ $p->rank ?? '—' }}
            </span>
            <img src="{{ $p->user->photo_url }}" class="w-8 h-8 rounded-full object-cover border border-white/10">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-black text-white truncate">{{ $p->user->name }}</p>
                <p class="text-[10px] text-gray-400">{{ $p->class_id }}</p>
            </div>
            <div class="text-right shrink-0 space-y-0.5">
                <p class="text-xs font-black text-emerald-400">✓ {{ $p->correct_count }}</p>
                <p class="text-[10px] {{ $p->status === 'disqualified' ? 'text-red-400' : 'text-gray-500' }}">
                    {{ $p->status === 'disqualified' ? '💀 Gugur' : ($p->hp . ' HP') }}
                </p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="text-center">
        <a href="{{ route('admin.gamification.arena.index') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-red-500 to-orange-500 text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-lg hover:-translate-y-0.5 transition-all">
            <i class="fas fa-home"></i> Kembali ke Arena
        </a>
    </div>
</div>
@endsection
