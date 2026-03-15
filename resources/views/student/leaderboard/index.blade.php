@extends('layouts.app')

@section('title', 'Leader Board')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="bg-gradient-to-br from-amber-500 via-yellow-500 to-orange-500 rounded-[2rem] px-8 py-7 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 80%, white 1px, transparent 1px); background-size: 30px 30px;"></div>
        <div class="relative">
            <p class="text-[10px] font-black uppercase tracking-widest text-amber-100 flex items-center gap-2">
                <i class="fas fa-trophy"></i>
                @if($gradeLevel) Kelas {{ $gradeLevel }} · Liga Angkatan @else Semua Kelas @endif
            </p>
            <h1 class="text-3xl font-black mt-1">Leader Board</h1>
            <p class="text-amber-100 text-sm mt-1">Temukan posisimu di antara para pejuang terbaik!</p>
        </div>
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full"></div>
    </div>

    {{-- My Rank Banner (Liga only) --}}
    @if($myRank && $tab === 'liga')
        <div class="bg-white border border-amber-200 rounded-2xl px-6 py-4 flex items-center justify-between">
            <p class="text-sm font-bold text-gray-700">Peringkat kamu saat ini:</p>
            <span class="text-2xl font-black text-amber-600">#{{ $myRank }}</span>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="flex gap-1 bg-gray-100 p-1.5 rounded-2xl w-fit mx-auto">
        @foreach(['liga' => ['🏆','Liga Angkatan'], 'fleet' => ['🚢','Fleet Kelas'], 'career' => ['📈','Performance Rank'], 'hall' => ['🏛️','Hall of Fame']] as $key => [$icon, $label])
            <a href="?tab={{ $key }}"
               class="px-4 py-1.5 rounded-xl text-xs font-black uppercase tracking-widest transition
                      {{ $tab === $key ? 'bg-white shadow text-amber-600' : 'text-gray-500 hover:text-gray-700' }}">
                {{ $icon }} {{ $label }}
            </a>
        @endforeach
    </div>

    @if($tab === 'hall')
        {{-- Hall of Fame --}}
        <div class="grid grid-cols-1 gap-3">
            @forelse($hallOfFame as $winner)
                <div class="bg-white rounded-2xl border {{ $winner->rank === 1 ? 'border-amber-200 bg-amber-50/50' : 'border-gray-100' }} px-5 py-4 flex items-center gap-4">
                    <span class="text-2xl">{{ match((int)$winner->rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => '#'.$winner->rank } }}</span>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-black flex-shrink-0">
                        {{ strtoupper(substr($winner->user->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-black text-gray-900">{{ ucwords(strtolower($winner->user->name)) }}</p>
                        <p class="text-[10px] text-gray-400 truncate">{{ $winner->battle_room_name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-black text-indigo-600 text-sm">{{ number_format($winner->career_exp_snapshot) }} XP</p>
                        <p class="text-[10px] text-gray-400">{{ $winner->archived_at->format('d M Y') }}</p>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-gray-100 px-6 py-14 text-center">
                    <i class="fas fa-monument text-4xl text-gray-200 mb-3 block"></i>
                    <p class="text-gray-400">Belum ada pemenang yang diarsip.</p>
                </div>
            @endforelse
        </div>

    @elseif($tab === 'fleet')
        {{-- Fleet Rank --}}
        <div class="space-y-3">
            @forelse($data as $i => $fleet)
                <div class="bg-white rounded-2xl border {{ $i < 3 ? 'border-sky-200' : 'border-gray-100' }} px-5 py-4 flex items-center gap-4">
                    <span class="text-xl w-8 text-center">{{ match($i) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => '#'.($i+1) } }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="font-black text-gray-900">Kelas {{ $fleet['grade_level'] }}-{{ $fleet['class_group'] ?? '?' }}</p>
                        <p class="text-[10px] text-gray-400">{{ $fleet['member_count'] }} anggota</p>
                    </div>
                    <div class="text-right">
                        <p class="font-black text-sky-600 text-sm">{{ number_format($fleet['avg_seasonal_exp'],0) }} XP avg</p>
                        <p class="text-[10px] text-gray-400">Total {{ number_format($fleet['total_seasonal_exp']) }}</p>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-gray-100 px-6 py-10 text-center text-gray-400">Belum ada data fleet.</div>
            @endforelse
        </div>

    @else
        {{-- Liga / Career --}}
        @php
            $isCareer = $tab === 'career';
            $expKey   = $isCareer ? 'career_exp' : 'seasonal_exp';
        @endphp
        <div class="space-y-2">
            @forelse($data as $i => $student)
                @php
                    $rank       = $i + 1;
                    $isMe       = $student['id'] === $user->id;
                    $medal      = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => "#{$rank}" };
                    $themeRing  = match($student['active_theme_id'] ?? '') {
                        'legendary-golden' => 'ring-2 ring-amber-400',
                        'elite-silver'     => 'ring-2 ring-slate-300',
                        'master-bronze'    => 'ring-2 ring-orange-400',
                        default            => ''
                    };
                @endphp
                <div class="bg-white rounded-2xl border {{ $isMe ? 'border-indigo-300 ring-2 ring-indigo-200' : ($rank<=3 ? 'border-amber-100' : 'border-gray-100') }} px-5 py-3.5 flex items-center gap-3 transition hover:border-gray-200">
                    <span class="text-base w-8 text-center font-black {{ $rank > 3 ? 'text-gray-400 text-sm' : '' }}">{{ $medal }}</span>
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-[11px] font-black flex-shrink-0 {{ $themeRing }}">
                        {{ strtoupper(substr($student['name'], 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-900 text-sm truncate {{ $isMe ? 'text-indigo-700' : '' }}">
                            {{ ucwords(strtolower($student['name'])) }}
                            @if($isMe)<span class="text-[9px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded font-black ml-1">Kamu</span>@endif
                        </p>
                        @if($student['is_fleet'] ?? false)
                            <p class="text-[10px] text-gray-400">{{ $student['member_count'] }} Anggota</p>
                        @else
                            <p class="text-[10px] text-gray-400">Lv.{{ $student['current_level'] ?? '?' }} · {{ number_format($student['avg_score'] ?? 0, 1) }} avg · {{ $student['total_sessions'] ?? 0 }} ujn</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <span class="font-black text-sm block {{ $isCareer ? 'text-violet-600' : 'text-amber-600' }}">
                            {{ number_format($student['performance_points'] ?? 0, 1) }} APP
                        </span>
                        <p class="text-[8px] text-gray-400 uppercase font-black uppercase tracking-tighter">Perf. Points</p>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-gray-100 px-6 py-12 text-center text-gray-400">
                    <i class="fas fa-star text-3xl mb-3 block text-gray-200"></i>
                    Belum ada data.
                </div>
            @endforelse
        </div>
    @endif

</div>
@endsection
