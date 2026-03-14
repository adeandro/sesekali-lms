@extends('layouts.app')

@section('title', 'Manajemen Musim')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 uppercase tracking-tight flex items-center gap-3">
                <i class="fas fa-calendar-alt text-indigo-500"></i> Manajemen Musim
            </h1>
            <p class="text-sm text-gray-500 mt-1">Kelola semester, reset seasonal, dan migrasi tahunan.</p>
        </div>
        <a href="{{ route('admin.gamification.seasons.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-violet-500 text-white text-sm font-black rounded-2xl shadow hover:shadow-indigo-300/50 hover:-translate-y-0.5 transition-all uppercase tracking-widest">
            <i class="fas fa-plus"></i> Buat Season
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Season</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Periode</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Reset</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Migrasi</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($seasons as $season)
                        <tr class="hover:bg-gray-50 transition {{ $season->is_active ? 'bg-indigo-50/50' : '' }}">
                            <td class="px-6 py-4">
                                <p class="font-black text-gray-900">{{ $season->name }}</p>
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest">{{ ucfirst($season->semester_type) }} {{ $season->academic_year }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-xs">
                                <div>{{ $season->start_date->format('d M Y') }}</div>
                                <div class="text-gray-400">s/d {{ $season->end_date->format('d M Y') }}</div>
                                @if(!$season->isEnded() && $season->is_active)
                                    <span class="text-emerald-600 text-[9px] font-black">↗ {{ $season->daysRemaining() }} hari lagi</span>
                                @elseif($season->isEnded())
                                    <span class="text-gray-400 text-[9px]">Selesai</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($season->is_active)
                                    <span class="px-2 py-1 text-[9px] font-black bg-emerald-100 text-emerald-700 rounded-full uppercase">🟢 Aktif</span>
                                @else
                                    <span class="px-2 py-1 text-[9px] font-black bg-gray-100 text-gray-500 rounded-full uppercase">Tidak Aktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($season->reset_done)
                                    <span class="text-xs text-emerald-600 font-bold"><i class="fas fa-check mr-1"></i>Selesai</span>
                                    <div class="text-[9px] text-gray-400">{{ $season->reset_executed_at?->format('d M Y') }}</div>
                                @else
                                    <form action="{{ route('admin.gamification.seasons.reset', $season) }}" method="POST" class="no-loading"
                                          onsubmit="return confirm('Reset seasonal EXP? Snapshot Hall of Fame akan dibuat otomatis.')">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 text-[10px] font-black bg-orange-100 text-orange-700 hover:bg-orange-200 rounded-xl uppercase tracking-widest transition">
                                            <i class="fas fa-redo-alt mr-1"></i> Reset Musim
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($season->migration_done)
                                    <span class="text-xs text-emerald-600 font-bold"><i class="fas fa-check mr-1"></i>Selesai</span>
                                    <div class="text-[9px] text-gray-400">{{ $season->migration_executed_at?->format('d M Y') }}</div>
                                @else
                                    <a href="{{ route('admin.gamification.seasons.migration', $season) }}"
                                       class="px-3 py-1.5 text-[10px] font-black bg-rose-100 text-rose-700 hover:bg-rose-200 rounded-xl uppercase tracking-widest transition inline-block">
                                        <i class="fas fa-graduation-cap mr-1"></i> Grand Migration
                                    </a>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.gamification.seasons.edit', $season) }}" class="text-gray-400 hover:text-indigo-600 transition p-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <i class="fas fa-calendar-alt text-4xl text-gray-200 mb-3 block"></i>
                                <p class="text-gray-400 font-medium">Belum ada season. Buat season pertama!</p>
                                <a href="{{ route('admin.gamification.seasons.create') }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2 bg-indigo-500 text-white text-xs font-black rounded-2xl uppercase tracking-widest">
                                    <i class="fas fa-plus"></i> Buat Season
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($seasons->hasPages())
            <div class="p-4">{{ $seasons->links() }}</div>
        @endif
    </div>

</div>
@endsection
