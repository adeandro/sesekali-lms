@extends('layouts.blank')

@section('title', 'Lobby — ' . $room->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-950 via-indigo-950 to-gray-950 p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <span class="text-[10px] font-black uppercase tracking-widest text-indigo-400">Battle Lobby</span>
            <h1 class="text-2xl font-black text-white tracking-tight mt-0.5">{{ $room->name }}</h1>
            <p class="text-sm text-gray-400 mt-1">Mode: <span class="text-amber-400 font-black">{{ $room->mode === 'class' ? '⚓ Fleet Mode' : ucfirst($room->mode) }}</span></p>
        </div>
        <div class="text-right space-y-2">
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-5 py-3 border border-white/10">
                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Join Code</p>
                <p class="text-3xl font-black text-white tracking-[0.3em]">{{ $room->code }}</p>
            </div>
            <p class="text-[10px] text-gray-500">{{ $room->participants->count() }} peserta bergabung</p>
        </div>
    </div>

    {{-- Fleet Grouping Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @forelse($fleetGroups as $classId => $participants)
        <div class="bg-white/5 backdrop-blur rounded-[1.5rem] border border-white/10 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 bg-white/5 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        @if($room->mode === 'class')
                            <i class="fas fa-ship text-white text-sm"></i>
                        @else
                            <i class="fas fa-users text-white text-sm"></i>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-black text-white">{{ $classId ?: 'Kelompok Umum' }}</p>
                        <p class="text-[10px] text-gray-400">{{ $participants->count() }} siswa</p>
                    </div>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-full border border-emerald-500/20">
                    Siap
                </span>
            </div>
            <div class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($participants as $p)
                <div class="flex items-center gap-2 bg-white/5 rounded-xl px-3 py-2 hover:bg-white/10 transition">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-[9px] font-black text-white shrink-0">
                        {{ strtoupper(substr($p->user->name, 0, 2)) }}
                    </div>
                    <p class="text-[11px] font-bold text-white truncate">{{ $p->user->name }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="col-span-2 text-center py-16">
            <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-hourglass text-2xl text-gray-500 animate-spin"></i>
            </div>
            <p class="text-gray-400 font-medium">Menunggu peserta bergabung dengan kode <span class="text-white font-black">{{ $room->code }}</span>…</p>
        </div>
        @endforelse
    </div>

    {{-- Control Panel --}}
    <div class="bg-white/5 backdrop-blur rounded-[1.5rem] border border-white/10 p-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="space-y-1">
                <p class="text-sm font-black text-white">Siap untuk IGNITE? 🔥</p>
                <p class="text-xs text-gray-400">Pastikan semua peserta sudah bergabung. Battle akan dimulai untuk semua secara bersamaan.</p>
            </div>
            <div class="flex gap-3 shrink-0">
                <a href="{{ route('admin.gamification.arena.index') }}"
                   class="px-5 py-3 rounded-2xl text-xs font-black text-gray-400 bg-white/5 hover:bg-white/10 border border-white/10 transition">
                   Kembali
                </a>
                <form action="{{ route('admin.gamification.arena.ignite', $room) }}" method="POST"
                      x-data
                      @submit="
                        setTimeout(() => {
                          window.location.href = '{{ route('admin.gamification.arena.spectator', $room) }}';
                        }, 1500)
                      ">
                    @csrf
                    <button type="submit"
                            class="px-8 py-3 bg-gradient-to-r from-red-500 to-orange-500 text-white font-black text-sm uppercase tracking-widest rounded-2xl shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50 hover:-translate-y-0.5 transition-all duration-200">
                        <i class="fas fa-fire mr-2"></i> IGNITE BATTLE!
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Live refresh indicator --}}
    <div x-data="{}" x-init="setInterval(() => window.location.reload(), 5000)"
         class="text-center text-[10px] text-gray-600 font-mono">
        Auto-refresh setiap 5 detik • {{ $room->participants->count() }} peserta terhubung
    </div>
</div>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ icon:'success', title:'Berhasil!', text:'{{ session("success") }}', toast:true, position:'top-end', timer:3000, showConfirmButton:false });
    });
</script>
@endif
@endsection
