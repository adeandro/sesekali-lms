@extends('layouts.app')
@section('title', 'Battle Arena')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <div class="flex items-center gap-3 mb-1">
        <div class="w-10 h-10 rounded-xl bg-purple-600
                    flex items-center justify-center shadow-lg
                    shadow-purple-500/20">
          <i class="fas fa-fist-raised text-white text-sm"></i>
        </div>
        <h1 class="text-2xl font-black text-gray-900
                    dark:text-white tracking-tight">
          Battle Arena
        </h1>
      </div>
      <p class="text-sm font-bold text-gray-600
                  dark:text-gray-400 ml-13">
        Kelola sesi kuis interaktif untuk kelasmu
      </p>
    </div>
    <a href="{{ route('admin.gamification.arena.create') }}"
       class="inline-flex items-center gap-2 px-5 py-3
              rounded-2xl bg-purple-600 text-white text-sm
              font-black uppercase tracking-widest
              hover:bg-purple-700 hover:shadow-lg
              active:scale-[0.98]
              transition-all shadow-md
              shadow-purple-500/25">
      <i class="fas fa-plus text-xs"></i>
      Buat Room
    </a>
  </div>

  @if(session('success'))
  <div class="flex items-center gap-3 px-4 py-3
              rounded-xl bg-emerald-50
              dark:bg-emerald-900/20
              border border-emerald-200
              dark:border-emerald-800
              text-emerald-800 dark:text-emerald-300
              text-sm">
    <i class="fas fa-check-circle text-emerald-500
               shrink-0"></i>
    {{ session('success') }}
  </div>
  @endif

  @if($rooms->isEmpty())
  {{-- Empty state --}}
  <div class="text-center py-20 bg-white
              dark:bg-gray-800 rounded-3xl border
              border-gray-100 dark:border-gray-700">
    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl
                bg-purple-50 dark:bg-purple-900/30
                flex items-center justify-center">
      <i class="fas fa-fist-raised text-2xl
                 text-purple-400"></i>
    </div>
    <p class="text-gray-500 text-sm mb-1">
      Belum ada Battle Room
    </p>
    <p class="text-gray-500 text-xs mb-6 font-medium">
      Buat room pertama untuk memulai sesi kuis
    </p>
    <a href="{{ route('admin.gamification.arena.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5
              rounded-xl bg-purple-600 text-white text-sm
              font-bold hover:bg-purple-700
              transition-colors">
      <i class="fas fa-plus text-xs"></i>
      Buat Room Sekarang
    </a>
  </div>

  @else
  {{-- Room cards --}}
  <div class="grid gap-3">
    @foreach($rooms as $room)
    @php
      $statusConfig = match($room->status) {
        'waiting'  => ['bg-amber-50 text-amber-700
                        border-amber-200
                        dark:bg-amber-900/20
                        dark:text-amber-400
                        dark:border-amber-700',
                       'Menunggu'],
        'ongoing'  => ['bg-emerald-50 text-emerald-700
                        border-emerald-200
                        dark:bg-emerald-900/20
                        dark:text-emerald-400
                        dark:border-emerald-700',
                       'Berlangsung'],
        'finished' => ['bg-gray-100 text-gray-500
                        border-gray-200
                        dark:bg-gray-700
                        dark:text-gray-400
                        dark:border-gray-600',
                       'Selesai'],
        default    => ['bg-gray-100 text-gray-500
                        border-gray-200', '-'],
      };
      $modeLabel = match($room->mode) {
        'individual' => ['Siswa vs Siswa',
                         'fa-user-friends'],
        'group'      => ['Mode Grup',
                         'fa-users'],
        'class'      => ['Kelas vs Kelas',
                         'fa-school'],
        default      => [$room->mode, 'fa-gamepad'],
      };
    @endphp

    <div class="bg-white dark:bg-gray-800 rounded-2xl
                border border-gray-100
                dark:border-gray-700 p-5
                hover:border-purple-200
                dark:hover:border-purple-700
                transition-colors group">
      <div class="flex items-center gap-4">

        {{-- Mode icon --}}
        <div class="w-12 h-12 rounded-2xl
                    bg-purple-50 dark:bg-purple-900/30
                    flex items-center justify-center
                    shrink-0 group-hover:bg-purple-100
                    dark:group-hover:bg-purple-900/50
                    transition-colors">
          <i class="fas {{ $modeLabel[1] }}
                     text-purple-600
                     dark:text-purple-400"></i>
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-0.5">
            <h3 class="font-black text-gray-900
                        dark:text-white truncate">
              {{ $room->name }}
            </h3>
            <code class="text-[10px] font-mono font-black
                          px-1.5 py-0.5 rounded
                          bg-purple-100
                          dark:bg-purple-900/40
                          text-purple-700
                          dark:text-purple-300
                          tracking-widest shrink-0">
              {{ $room->token }}
            </code>
          </div>
          <div class="flex items-center gap-3
                       text-[11px] font-bold text-gray-500
                       dark:text-gray-400">
            <span>
              <i class="fas fa-list-ol mr-1 opacity-70"></i>
              {{ $room->total_questions }} soal
            </span>
            <span>·</span>
            <span>
              <i class="fas fa-clock mr-1 opacity-70"></i>
              {{ $room->duration_per_question }}d/soal
            </span>
            <span>·</span>
            <span class="uppercase tracking-wide">{{ $modeLabel[0] }}</span>
          </div>
        </div>

        {{-- Status + Actions --}}
        <div class="flex items-center gap-3 shrink-0">
          <span class="px-2.5 py-1 rounded-full border
                        text-xs font-bold
                        {{ $statusConfig[0] }}">
            @if($room->status === 'ongoing')
            <span class="inline-block w-1.5 h-1.5
                          rounded-full bg-emerald-500
                          animate-pulse mr-1"></span>
            @endif
            {{ $statusConfig[1] }}
          </span>

          <div class="flex items-center gap-2">
            @if($room->status === 'waiting' ||
                $room->status === 'ongoing')
              <a href="{{ route('admin.gamification.arena.control', $room->token) }}"
                 class="px-3 py-1.5 rounded-lg text-xs
                        font-bold border
                        border-purple-200
                        dark:border-purple-700
                        text-purple-700
                        dark:text-purple-400
                        hover:bg-purple-50
                        dark:hover:bg-purple-900/30
                        transition-colors">
                <i class="fas fa-gamepad mr-1"></i>
                Kontrol
              </a>
              <a href="{{ route('admin.gamification.arena.display', $room->token) }}"
                 target="_blank"
                 class="px-3 py-1.5 rounded-lg text-xs
                        font-bold border
                        border-gray-200
                        dark:border-gray-600
                        text-gray-600
                        dark:text-gray-300
                        hover:bg-gray-50
                        dark:hover:bg-gray-700/40
                        transition-colors">
                <i class="fas fa-tv mr-1"></i>
                Proyektor
              </a>
            @else
              <a href="{{ route('admin.gamification.arena.debriefing', $room->token) }}"
                 class="px-3 py-1.5 rounded-lg text-xs
                        font-bold border
                        border-gray-200
                        dark:border-gray-600
                        text-gray-600
                        dark:text-gray-300
                        hover:bg-gray-50
                        transition-colors">
                <i class="fas fa-chart-bar mr-1"></i>
                Rekap
              </a>
            @endif

            <form method="POST"
                  action="{{ route('admin.gamification.arena.destroy', $room->token) }}"
                  onsubmit="return confirm(
                    'Hapus room {{ $room->name }}?')">
              @csrf @method('DELETE')
              <button type="submit"
                      class="px-3 py-1.5 rounded-lg
                             text-xs font-bold border
                             border-red-100
                             dark:border-red-900/50
                             text-red-400
                             hover:bg-red-50
                             dark:hover:bg-red-900/20
                             transition-colors">
                <i class="fas fa-trash-alt"></i>
              </button>
            </form>
          </div>
        </div>

      </div>
    </div>
    @endforeach
  </div>

  <div class="mt-2">{{ $rooms->links() }}</div>
  @endif

</div>
@endsection
