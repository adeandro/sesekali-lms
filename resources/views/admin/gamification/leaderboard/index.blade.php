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
                <button type="submit" title="Refresh Cache Admin" class="px-4 py-2 bg-gray-100 hover:bg-indigo-100 text-gray-600 hover:text-indigo-700 rounded-2xl text-sm font-bold transition">
                    <i class="fas fa-sync-alt mr-1"></i>
                </button>
            </form>

            {{-- Sinkronisasi Dasbor Siswa --}}
            <form action="{{ route('admin.gamification.leaderboard.sync-dashboards') }}" method="POST" class="no-loading">
                @csrf
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-sm font-black shadow-lg shadow-indigo-200 transition-all hover:scale-105">
                    <i class="fas fa-bolt mr-2"></i> Sinkronisasi Dasbor Siswa
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
        @php
            $isGrouped = $tab === 'career' && count($data) > 0 && !is_numeric(array_key_first($data));
        @endphp
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r {{ $gradient }} px-8 py-5">
                <h2 class="text-lg font-black text-white uppercase tracking-widest flex items-center gap-2">
                    <i class="fas {{ $isCareer ? 'fa-infinity' : 'fa-star' }}"></i> {{ $title }}
                </h2>
                <p class="text-white/70 text-xs mt-1">{{ $subtitle }}</p>
            </div>

            <div class="{{ $isGrouped ? 'grid grid-cols-1 lg:grid-cols-3' : 'divide-y divide-gray-50' }}">
                @if($isGrouped)
                    @foreach($data as $groupLabel => $students)
                        <div class="flex flex-col bg-white">
                            <div class="bg-gray-50/80 px-6 py-3 border-b border-gray-100 sticky top-0 z-10 backdrop-blur-sm">
                                <h3 class="text-xs font-black text-purple-600 uppercase tracking-widest flex items-center gap-2">
                                    <i class="fas fa-school shadow-sm"></i> {{ $groupLabel }}
                                </h3>
                            </div>
                            <div class="divide-y divide-gray-50">
                                @forelse($students as $student)
                                    @php
                                        $rank = $loop->iteration;
                                        $medal = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => "#{$rank}" };
                                        $themes = ['legendary-golden'=>'ring-2 ring-amber-400','elite-silver'=>'ring-2 ring-slate-300','master-bronze'=>'ring-2 ring-orange-400','survivor-common'=>'ring-1 ring-gray-400'];
                                        $ringClass = $themes[$student['active_theme_id'] ?? ''] ?? '';
                                    @endphp
                                    <div class="flex items-center gap-3 px-5 py-4 hover:bg-violet-50/30 transition group">
                                        <span class="text-base font-black w-8 text-center bg-gray-50 rounded-lg group-hover:bg-white transition {{ $rank <= 3 ? 'text-amber-500' : 'text-gray-400 text-xs' }}">{{ $medal }}</span>
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-[10px] font-black flex-shrink-0 {{ $ringClass }}">
                                            {{ strtoupper(substr($student['name'], 0, 2)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-gray-900 text-xs truncate group-hover:text-purple-700 transition">{{ ucwords(strtolower($student['name'])) }}</p>
                                            <p class="text-[9px] text-gray-400 font-medium">{{ $student['grade_level'] ?? '-' }}{{ $student['class_group'] ? '-'.$student['class_group'] : '' }}</p>
                                        </div>
                                        <span class="font-black text-[11px] text-violet-700 text-right whitespace-nowrap">
                                            {{ number_format($student['career_exp'] ?? 0, 0) }} XP
                                        </span>
                                    </div>
                                @empty
                                    <div class="px-6 py-12 text-center text-gray-400">
                                        <p class="text-[10px] italic">Tidak ada data</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                @else
                    @forelse($data as $student)
                        @php
                            $rank = $loop->iteration;
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
                            <span class="font-black text-sm text-amber-600 w-32 text-right">
                                {{ number_format($student['performance_points'] ?? 0, 1) }} APP
                            </span>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-star text-3xl mb-3 block text-gray-200"></i>
                            Belum ada data untuk ditampilkan.
                        </div>
                    @endforelse
                @endif
            </div>
        </div>
    @endif

</div>
@endsection
