@extends('layouts.app')

@section('title', 'Battle Arena')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 uppercase tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-red-500 to-orange-600 flex items-center justify-center shadow-lg">
                    <i class="fas fa-swords text-white text-lg"></i>
                </div>
                Battle Arena
            </h1>
            <p class="text-sm text-gray-500 mt-1 font-medium">Kelola sesi pertarungan antar kelas & individu</p>
        </div>
        <a href="{{ route('admin.gamification.arena.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-red-500 to-orange-500 text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-lg hover:shadow-orange-500/30 hover:-translate-y-0.5 transition-all duration-200">
            <i class="fas fa-plus"></i> Buat Battle Room
        </a>
    </div>

    {{-- Room Grid --}}
    @if($rooms->count())
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($rooms as $room)
        @php
            $statusColor = match($room->status) {
                'waiting'  => 'text-amber-600 bg-amber-50 border-amber-200',
                'ongoing'  => 'text-emerald-600 bg-emerald-50 border-emerald-200',
                'finished' => 'text-gray-500 bg-gray-50 border-gray-200',
                default    => 'text-gray-500 bg-gray-50 border-gray-200',
            };
            $statusIcon = match($room->status) {
                'waiting'  => 'fa-clock',
                'ongoing'  => 'fa-fire animate-pulse',
                'finished' => 'fa-flag-checkered',
                default    => 'fa-question',
            };
            $statusLabel = match($room->status) {
                'waiting'  => 'Menunggu',
                'ongoing'  => 'Berlangsung',
                'finished' => 'Selesai',
                default    => $room->status,
            };
            $modeLabel = match($room->mode) {
                'individual' => 'Individual',
                'group'      => 'Group (Random)',
                'class'      => '⚓ Fleet Mode',
            };
        @endphp
        <div class="bg-white rounded-[1.5rem] border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden group">
            {{-- Top accent bar --}}
            <div class="h-1.5 w-full {{ $room->status === 'ongoing' ? 'bg-gradient-to-r from-red-500 to-orange-400' : ($room->status === 'finished' ? 'bg-gray-200' : 'bg-gradient-to-r from-indigo-400 to-purple-400') }}"></div>
            <div class="p-5 space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-black text-gray-900 text-base truncate">{{ $room->name }}</h3>
                        <p class="text-xs text-gray-400 font-mono font-bold mt-0.5 tracking-widest">CODE: {{ $room->code }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest border {{ $statusColor }} shrink-0">
                        <i class="fas {{ $statusIcon }} text-[9px]"></i> {{ $statusLabel }}
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="bg-gray-50 rounded-xl p-2">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Mode</p>
                        <p class="text-xs font-black text-gray-700 mt-0.5">{{ $modeLabel }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-2">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Peserta</p>
                        <p class="text-xl font-black text-gray-900 mt-0.5">{{ $room->participants_count ?? $room->participants->count() }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-2">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Durasi</p>
                        <p class="text-xs font-black text-gray-700 mt-0.5">{{ $room->duration_minutes }}m</p>
                    </div>
                </div>

                <div class="flex gap-2 pt-1">
                    @if($room->status === 'waiting')
                        <a href="{{ route('admin.gamification.arena.lobby', $room) }}"
                           class="flex-1 text-center py-2 rounded-xl text-xs font-black bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                            <i class="fas fa-door-open mr-1"></i> Lobby
                        </a>
                    @elseif($room->status === 'ongoing')
                        <a href="{{ route('admin.gamification.arena.spectator', $room) }}"
                           class="flex-1 text-center py-2 rounded-xl text-xs font-black bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition animate-pulse">
                            <i class="fas fa-eye mr-1"></i> Live Track
                        </a>
                    @else
                        <a href="{{ route('admin.gamification.arena.podium', $room) }}"
                           class="flex-1 text-center py-2 rounded-xl text-xs font-black bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                            <i class="fas fa-trophy mr-1"></i> Podium
                        </a>
                        <a href="{{ route('admin.gamification.arena.debriefing', $room) }}"
                           class="flex-1 text-center py-2 rounded-xl text-xs font-black bg-gray-50 text-gray-600 hover:bg-gray-100 transition">
                            <i class="fas fa-chart-bar mr-1"></i> Debrief
                        </a>
                    @endif

                    <form action="{{ route('admin.gamification.arena.destroy', $room) }}" method="POST"
                          onsubmit="return confirm('Hapus Battle Room ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="py-2 px-3 rounded-xl text-xs font-black bg-red-50 text-red-600 hover:bg-red-100 transition">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{ $rooms->links() }}

    @else
    <div class="bg-white rounded-[2rem] border border-dashed border-gray-200 p-16 text-center">
        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-red-50 to-orange-50 border-2 border-orange-100 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-swords text-3xl text-orange-400"></i>
        </div>
        <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Belum Ada Battle Room</h3>
        <p class="text-sm text-gray-400 mt-2 mb-6">Mulai pertarungan epik pertama di sekolah Anda!</p>
        <a href="{{ route('admin.gamification.arena.create') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-red-500 to-orange-500 text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-lg hover:-translate-y-0.5 transition-all">
            <i class="fas fa-plus"></i> Buat Battle Room Pertama
        </a>
    </div>
    @endif
</div>
@endsection
