@extends('layouts.app')
@section('title', 'Rekap Battle — {{ $room->name }}')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <a href="{{ route('admin.gamification.arena.index') }}"
         class="text-sm text-gray-500 hover:text-gray-700
                dark:text-gray-400 flex items-center gap-1 mb-2">
        ← Kembali ke Daftar Room
      </a>
      <h1 class="text-xl font-black text-gray-900
                  dark:text-white">
        Rekap Battle
      </h1>
      <p class="text-sm text-gray-500 mt-0.5">
        {{ $room->name }} ·
        {{ $room->ended_at?->locale('id')
            ->translatedFormat('d M Y, H:i') ?? '-' }}
      </p>
    </div>
    <div class="text-right">
      <p class="text-xs text-gray-400 uppercase
                 tracking-widest font-bold">Token</p>
      <code class="text-2xl font-black tracking-widest
                    text-purple-600">
        {{ $room->token }}
      </code>
    </div>
  </div>

  {{-- Stats ringkas --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    @foreach([
      ['Peserta', $participants->count(), 'users'],
      ['Soal', $room->total_questions, 'list-ol'],
      ['Durasi/Soal', $room->duration_per_question.'d', 'clock'],
      ['Mode', ucfirst($room->mode), 'gamepad'],
    ] as [$label, $value, $icon])
    <div class="bg-white dark:bg-gray-800 rounded-2xl
                border border-gray-100 dark:border-gray-700
                p-4 text-center">
      <i class="fas fa-{{ $icon }} text-purple-400
                 text-lg mb-2 block"></i>
      <p class="text-xl font-black text-gray-900
                 dark:text-white">{{ $value }}</p>
      <p class="text-xs text-gray-400 uppercase
                 tracking-wider">{{ $label }}</p>
    </div>
    @endforeach
  </div>

  {{-- Group scores (jika ada) --}}
  @if($groupScores && $groupScores->count() > 0)
  <div class="bg-white dark:bg-gray-800 rounded-2xl
              border border-gray-100 dark:border-gray-700 p-5">
    <h2 class="text-sm font-black text-gray-500
                uppercase tracking-widest mb-4">
      Peringkat Grup
    </h2>
    <div class="space-y-2">
      @foreach($groupScores as $i => $group)
      <div class="flex items-center gap-4 px-4 py-3
                  rounded-xl
                  {{ $i === 0
                     ? 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700'
                     : 'bg-gray-50 dark:bg-gray-700/40' }}">
        <span class="font-black text-lg w-8 text-center
                      {{ $i === 0 ? 'text-amber-500' : 'text-gray-400' }}">
          #{{ $i + 1 }}
        </span>
        <div class="flex-1">
          <p class="font-bold text-gray-900 dark:text-white">
            {{ $group['label'] }}
          </p>
          <p class="text-xs text-gray-400">
            {{ $group['members'] }} anggota ·
            rata-rata {{ number_format($group['avg_score']) }} pts
          </p>
        </div>
        <p class="font-black text-xl
                   {{ $i === 0
                      ? 'text-amber-600 dark:text-amber-400'
                      : 'text-gray-600 dark:text-gray-300' }}">
          {{ number_format($group['total_score']) }}
          <span class="text-xs font-normal text-gray-400">pts</span>
        </p>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- Tabel peserta --}}
  <div class="bg-white dark:bg-gray-800 rounded-2xl
              border border-gray-100 dark:border-gray-700
              overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100
                dark:border-gray-700">
      <h2 class="text-sm font-black text-gray-500
                  uppercase tracking-widest">
        Hasil Per Peserta
      </h2>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-gray-50 dark:bg-gray-700/50
                    text-xs text-gray-500 uppercase
                    tracking-wide">
        <tr>
          <th class="px-5 py-3 text-left w-10">Rank</th>
          <th class="px-5 py-3 text-left">Nama</th>
          @if($room->mode !== 'individual')
          <th class="px-5 py-3 text-left">Grup</th>
          @endif
          <th class="px-5 py-3 text-right">Skor</th>
          <th class="px-5 py-3 text-right">Benar</th>
          <th class="px-5 py-3 text-right">Salah</th>
          <th class="px-5 py-3 text-right">EXP Reward</th>
          <th class="px-5 py-3 text-right">Fisik Reward</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50
                    dark:divide-gray-700/50">
        @foreach($participants as $p)
        @php
          $rank = $p->rank ?? '-';
          $isTop3 = is_numeric($rank) && $rank <= 3;
          $expReward = match((int)$rank) {
            1 => $room->reward_rank1_exp,
            2 => $room->reward_rank2_exp,
            3 => $room->reward_rank3_exp,
            default => 50,
          };
          $rankEmoji = match((int)$rank) {
            1 => '🥇', 2 => '🥈', 3 => '🥉',
            default => '',
          };
        @endphp
        <tr class="hover:bg-gray-50
                    dark:hover:bg-gray-700/30
                    transition-colors
                    {{ $isTop3
                       ? 'bg-amber-50/30 dark:bg-amber-900/10'
                       : '' }}">
          <td class="px-5 py-3 font-black
                      text-gray-400 text-center">
            {{ $rankEmoji ?: '#'.$rank }}
          </td>
          <td class="px-5 py-3">
            <p class="font-bold text-gray-900
                        dark:text-white">
              {{ $p->user->name ?? '-' }}
            </p>
          </td>
          @if($room->mode !== 'individual')
          <td class="px-5 py-3 text-gray-500 text-xs">
            {{ $p->group_label ?? '-' }}
          </td>
          @endif
          <td class="px-5 py-3 text-right font-black
                      {{ $isTop3
                         ? 'text-amber-600 dark:text-amber-400'
                         : 'text-gray-700 dark:text-gray-200' }}">
            {{ number_format($p->total_score) }}
          </td>
          <td class="px-5 py-3 text-right text-emerald-600
                      dark:text-emerald-400 font-bold">
            {{ $p->correct_count }}
          </td>
          <td class="px-5 py-3 text-right text-red-400
                      font-bold">
            {{ $p->wrong_count }}
          </td>
          <td class="px-5 py-3 text-right">
            <span class="text-xs font-bold
                          text-purple-600
                          dark:text-purple-400">
              +{{ number_format($expReward) }} EXP
            </span>
          </td>
          <td class="px-5 py-3 text-right">
            <span class="text-xs font-bold
                          text-amber-600
                          dark:text-amber-400">
              {{ $p->physical_reward ?: '-' }}
            </span>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Reward summary --}}
  <div class="bg-white dark:bg-gray-800 rounded-2xl
              border border-gray-100 dark:border-gray-700 p-5">
    <h2 class="text-sm font-black text-gray-500
                uppercase tracking-widest mb-4">
      Reward yang Diberikan
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      @foreach([
        ['🥇', 'Juara 1', $room->reward_rank1_exp, $room->reward_rank1_physical],
        ['🥈', 'Juara 2', $room->reward_rank2_exp, $room->reward_rank2_physical],
        ['🥉', 'Juara 3', $room->reward_rank3_exp, $room->reward_rank3_physical],
        ['👥', 'Partisipan', 50, null],
      ] as [$emoji, $label, $exp, $physical])
      <div class="text-center p-3 rounded-xl
                  bg-gray-50 dark:bg-gray-700/40 flex flex-col justify-between">
        <div>
          <p class="text-2xl mb-1">{{ $emoji }}</p>
          <p class="text-xs text-gray-500 mb-1">
            {{ $label }}
          </p>
          <p class="font-black text-purple-600
                     dark:text-purple-400">
            +{{ number_format($exp) }} EXP
          </p>
        </div>
        @if($physical)
        <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
          <p class="text-[10px] text-amber-600 dark:text-amber-400 font-bold uppercase">Hadiah Fisik</p>
          <p class="text-[11px] font-bold text-gray-700 dark:text-gray-200 leading-tight">{{ $physical }}</p>
        </div>
        @endif
      </div>
      @endforeach
    </div>
  </div>

</div>
@endsection
