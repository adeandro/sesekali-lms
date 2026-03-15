@extends('layouts.app')

@section('title', 'Leaderboard — Hall of Fame')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 uppercase tracking-tight flex items-center gap-3">
                <i class="fas fa-trophy text-amber-500"></i> Leaderboard Global
            </h1>
            <p class="text-sm text-gray-500 mt-1">Liga Angkatan · Fleet Prestige · Hall of Fame</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Grade Filter --}}
            <select onchange="window.location.href='?grade='+this.value+'&tab={{ $tab }}'"
                    class="px-4 py-2 rounded-2xl border border-gray-200 text-sm font-bold bg-white focus:ring-2 focus:ring-amber-400 focus:outline-none">
                <option value="all" {{ $gradeLevel === 'all' ? 'selected' : '' }}>Semua Angkatan</option>
                <option value="10"  {{ $gradeLevel == '10'   ? 'selected' : '' }}>Kelas 10</option>
                <option value="11"  {{ $gradeLevel == '11'   ? 'selected' : '' }}>Kelas 11</option>
                <option value="12"  {{ $gradeLevel == '12'   ? 'selected' : '' }}>Kelas 12</option>
            </select>
            {{-- Refresh Cache --}}
            <form action="{{ route('admin.gamification.leaderboard.refresh') }}" method="POST" class="no-loading">
                @csrf
                <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-indigo-100 text-gray-600 hover:text-indigo-700 rounded-2xl text-sm font-bold transition">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh Cache
                </button>
            </form>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 bg-gray-100 p-1 rounded-2xl w-fit">
        @foreach(['liga' => ['icon'=>'fa-star','label'=>'Liga Angkatan'], 'fleet' => ['icon'=>'fa-ship','label'=>'Fleet Prestige'], 'career' => ['icon'=>'fa-infinity','label'=>'Career EXP'], 'hall' => ['icon'=>'fa-monument','label'=>'Hall of Fame']] as $key => $info)
            <a href="?grade={{ $gradeLevel }}&tab={{ $key }}"
               class="px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition
                      {{ $tab === $key ? 'bg-white shadow text-amber-600' : 'text-gray-500 hover:text-gray-700' }}">
                <i class="fas {{ $info['icon'] }} mr-1"></i> {{ $info['label'] }}
            </a>
        @endforeach
    </div>

    @if($tab === 'hall')
        {{-- ── Hall of Fame ── --}}
        <div class="bg-white rounded-[2rem] border border-amber-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-amber-500 to-yellow-400 px-8 py-5">
                <h2 class="text-lg font-black text-white uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-monument"></i> Hall of Fame — Arsip Pemenang
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-amber-50 border-b border-amber-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-amber-700 uppercase tracking-widest">Rank</th>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-amber-700 uppercase tracking-widest">Siswa</th>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-amber-700 uppercase tracking-widest">Season</th>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-amber-700 uppercase tracking-widest">Pencapaian</th>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-amber-700 uppercase tracking-widest">Final APP</th>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-amber-700 uppercase tracking-widest">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($hallOfFame as $winner)
                            <tr class="hover:bg-amber-50/50 transition">
                                <td class="px-6 py-4">
                                    <span class="text-lg">{{ match((int)$winner->rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => '#'.$winner->rank } }}</span>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900">{{ $winner->display_name ?? $winner->user->name }}</td>
                                <td class="px-6 py-4 text-gray-600 text-xs">{{ $winner->season->name ?? 'Unknown Season' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-[9px] font-black bg-indigo-100 text-indigo-700 rounded-lg uppercase tracking-widest">
                                        Lv.{{ $winner->level_final }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-black text-indigo-600">{{ number_format($winner->app_points_final, 1) }} APP</td>
                                <td class="px-6 py-4 text-gray-400 text-xs">{{ $winner->recorded_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400"><i class="fas fa-monument text-2xl mb-2 block"></i>Belum ada pemenang di arsip.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($tab === 'fleet')
        {{-- ── Fleet Rank / Class Prestige ── --}}
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-sky-600 to-indigo-600 px-8 py-5">
                <h2 class="text-lg font-black text-white uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-ship"></i> Fleet Rank — Prestasi Kelas
                </h2>
                <p class="text-sky-200 text-xs mt-1">Diurutkan berdasarkan rata-rata Seasonal EXP seluruh anggota kelas</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Fleet</th>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Kelas</th>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Anggota</th>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Avg Performance Points</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($data as $i => $fleet)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-xl">{{ match($i) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => '#'.($i+1) } }}</td>
                                <td class="px-6 py-4">
                                    <span class="font-black text-gray-900">{{ $fleet['grade_level'] }}-{{ $fleet['class_group'] ?? '?' }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $fleet['member_count'] }} siswa</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-100 rounded-full h-2">
                                            @php $maxPP = max(array_column((array)$data, 'performance_points') ?: [1]); @endphp
                                            <div class="bg-gradient-to-r from-sky-500 to-indigo-500 h-2 rounded-full" style="width: {{ min(100, round($fleet['performance_points']/$maxPP*100)) }}%"></div>
                                        </div>
                                        <span class="font-bold text-indigo-700 text-xs w-20 text-right">{{ number_format($fleet['performance_points'], 1) }} APP</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Belum ada data fleet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        {{-- ── Liga Angkatan / Career EXP ── --}}
        @php
            $isCareer = $tab === 'career';
            $expKey   = $isCareer ? 'career_exp' : 'seasonal_exp';
            $title    = $isCareer ? 'Career EXP — Prestasi Seumur Hidup' : 'Liga Angkatan — Seasonal';
            $subtitle = $isCareer ? 'Akumulasi EXP sepanjang karir — tidak pernah di-reset' : 'EXP musim ini (di-reset tiap semester)';
            $gradient = $isCareer ? 'from-violet-600 to-purple-700' : 'from-amber-500 to-yellow-500';
        @endphp
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r {{ $gradient }} px-8 py-5">
                <h2 class="text-lg font-black text-white uppercase tracking-widest flex items-center gap-2">
                    <i class="fas {{ $isCareer ? 'fa-infinity' : 'fa-star' }}"></i> {{ $title }}
                </h2>
                <p class="text-white/70 text-xs mt-1">{{ $subtitle }}</p>
            </div>

            <div class="divide-y divide-gray-50">
                @forelse($data as $i => $student)
                    @php
                        $rank = $i + 1;
                        $medal = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => "#{$rank}" };
                        $themes = ['legendary-golden'=>'ring-2 ring-amber-400','elite-silver'=>'ring-2 ring-slate-300','master-bronze'=>'ring-2 ring-orange-400','survivor-common'=>'ring-1 ring-gray-400'];
                        $ringClass = $themes[$student['active_theme_id'] ?? ''] ?? '';
                    @endphp
                    <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition
                                {{ $rank <= 3 ? 'bg-gradient-to-r from-amber-50/60 to-transparent' : '' }}">
                        <span class="text-lg font-black w-10 text-center {{ $rank <= 3 ? '' : 'text-gray-400 text-sm' }}">{{ $medal }}</span>
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-black flex-shrink-0 {{ $ringClass }}">
                            {{ strtoupper(substr($student['name'], 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-black text-gray-900 text-sm truncate">{{ ucwords(strtolower($student['name'])) }}</p>
                            <p class="text-[10px] text-gray-400">Kelas {{ $student['grade_level'] ?? '-' }}{{ $student['class_group'] ? '-'.$student['class_group'] : '' }}</p>
                        </div>
                        @if($student['active_theme_id'] ?? null)
                            <span class="text-[9px] font-black px-2 py-0.5 rounded-full uppercase
                                {{ match($student['active_theme_id']) {
                                    'legendary-golden' => 'bg-amber-100 text-amber-700',
                                    'elite-silver'     => 'bg-slate-100 text-slate-700',
                                    'master-bronze'    => 'bg-orange-100 text-orange-700',
                                    default            => 'bg-gray-100 text-gray-600'} }}">
                                {{ match($student['active_theme_id']) {
                                    'legendary-golden' => '⚡ Legendary',
                                    'elite-silver'     => '🌟 Elite',
                                    'master-bronze'    => '🔥 Master',
                                    default            => '⚔ Survivor'} }}
                            </span>
                        @endif
                        <span class="font-black text-sm {{ $isCareer ? 'text-violet-700' : 'text-amber-600' }} w-32 text-right">
                            {{ number_format($student['performance_points'] ?? 0, 1) }} APP
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center text-gray-400">
                        <i class="fas fa-star text-3xl mb-3 block text-gray-200"></i>
                        Belum ada data untuk ditampilkan.
                    </div>
                @endforelse
            </div>
        </div>
    @endif

</div>
@endsection
