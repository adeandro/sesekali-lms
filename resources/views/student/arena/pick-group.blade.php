@extends('layouts.app')
@section('title', 'Pilih Grup — {{ $room->name }}')
@section('content')
<div class="min-h-[80vh] flex items-center
            justify-center px-4 py-8">
  <div class="w-full max-w-md space-y-5">

    {{-- Header --}}
    <div class="text-center">
      <p class="text-xs font-mono font-bold
                 text-purple-500 uppercase
                 tracking-[0.3em] mb-1">
        {{ $room->token }}
      </p>
      <h1 class="text-xl font-black text-gray-900
                  dark:text-white">
        Pilih Grupmu
      </h1>
      <p class="text-sm text-gray-500 mt-1">
        {{ $room->name }}
      </p>
    </div>

    @if($errors->any())
    <div class="flex items-start gap-3 px-4 py-3
                rounded-xl bg-red-50 dark:bg-red-900/20
                border border-red-200
                dark:border-red-800">
      <i class="fas fa-exclamation-circle
                 text-red-500 mt-0.5 shrink-0"></i>
      <p class="text-sm text-red-700
                 dark:text-red-300 font-medium">
        {{ $errors->first('group_label') }}
      </p>
    </div>
    @endif

    <form method="POST"
          action="{{ route('student.arena.join-group',
            $room->token) }}"
          class="bg-white dark:bg-gray-800 rounded-3xl
                 border border-gray-100
                 dark:border-gray-700 p-5 shadow-sm">
      @csrf

      @php
        $groupColors = [
          'Merah'  => ['from-red-500 to-rose-600',
                       'border-red-300
                        dark:border-red-700',
                       'text-red-700
                        dark:text-red-300',
                       '🔴'],
          'Biru'   => ['from-blue-500 to-indigo-600',
                       'border-blue-300
                        dark:border-blue-700',
                       'text-blue-700
                        dark:text-blue-300',
                       '🔵'],
          'Hijau'  => ['from-emerald-500 to-green-600',
                       'border-emerald-300
                        dark:border-emerald-700',
                       'text-emerald-700
                        dark:text-emerald-300',
                       '🟢'],
          'Kuning' => ['from-yellow-400 to-amber-500',
                       'border-yellow-300
                        dark:border-yellow-700',
                       'text-yellow-700
                        dark:text-yellow-300',
                       '🟡'],
          'Ungu'   => ['from-purple-500 to-violet-600',
                       'border-purple-300
                        dark:border-purple-700',
                       'text-purple-700
                        dark:text-purple-300',
                       '🟣'],
          'Oranye' => ['from-orange-500 to-amber-600',
                       'border-orange-300
                        dark:border-orange-700',
                       'text-orange-700
                        dark:text-orange-300',
                       '🟠'],
          'Pink'   => ['from-pink-500 to-rose-500',
                       'border-pink-300
                        dark:border-pink-700',
                       'text-pink-700
                        dark:text-pink-300',
                       '🩷'],
          'Hitam'  => ['from-gray-700 to-gray-900',
                       'border-gray-400
                        dark:border-gray-600',
                       'text-gray-700
                        dark:text-gray-300',
                       '⚫'],
        ];
      @endphp

      <div class="grid grid-cols-2 gap-3 mb-5">
        @foreach($room->group_names as $groupName)
        @php
          $count  = $groupCounts[$groupName] ?? 0;
          $max    = $room->max_per_group ?? 40;
          $isFull = $count >= $max;
          $pct    = $max > 0
              ? round(($count / $max) * 100) : 0;
          $colors = $groupColors[$groupName]
              ?? ['from-gray-500 to-gray-600',
                  'border-gray-300',
                  'text-gray-700', '⚪'];
        @endphp

        <label class="{{ $isFull
            ? 'opacity-40 cursor-not-allowed'
            : 'cursor-pointer' }}">
          <input type="radio"
                 name="group_label"
                 value="{{ $groupName }}"
                 {{ $isFull ? 'disabled' : '' }}
                 class="peer sr-only">
          <div class="relative rounded-2xl border-2
                       overflow-hidden transition-all
                       duration-200
                       {{ $colors[1] }}
                       peer-checked:ring-2
                       peer-checked:ring-purple-500
                       peer-checked:ring-offset-2
                       {{ !$isFull
                          ? 'hover:shadow-md hover:scale-[1.02]'
                          : '' }}">

            {{-- Gradient top bar --}}
            <div class="h-2 bg-gradient-to-r
                         {{ $colors[0] }}">
            </div>

            <div class="p-4">
              <div class="flex items-center
                           justify-between mb-2">
                <span class="text-xl">
                  {{ $colors[3] }}
                </span>
                @if($isFull)
                <span class="text-[10px] font-black
                              px-2 py-0.5 rounded-full
                              bg-red-100 text-red-600
                              dark:bg-red-900/30
                              dark:text-red-400">
                  PENUH
                </span>
                @endif
              </div>
              <p class="font-black text-gray-900
                          dark:text-white text-base">
                {{ $groupName }}
              </p>
              <p class="text-xs text-gray-400 mt-1">
                {{ $count }}/{{ $max }} anggota
              </p>

              {{-- Progress bar --}}
              <div class="mt-2 h-1.5 bg-gray-100
                           dark:bg-gray-700
                           rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r
                             {{ $colors[0] }}
                             rounded-full transition-all"
                     style="width: {{ $pct }}%">
                </div>
              </div>
            </div>

            {{-- Checkmark --}}
            <div class="absolute top-3 right-3
                         w-5 h-5 rounded-full
                         bg-purple-500 text-white
                         items-center justify-center
                         text-[10px] font-black
                         hidden peer-checked:flex">
              ✓
            </div>
          </div>
        </label>
        @endforeach
      </div>

      <button type="submit"
              class="w-full py-3.5 rounded-2xl
                     bg-purple-600 text-white
                     font-black text-sm uppercase
                     tracking-widest
                     hover:bg-purple-700
                     active:scale-[0.98]
                     transition-all shadow-sm
                     shadow-purple-500/20">
        Bergabung ke Grup
        <i class="fas fa-arrow-right ml-2"></i>
      </button>
    </form>

  </div>
</div>
@endsection
