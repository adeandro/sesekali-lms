@extends('layouts.app')

@section('title', 'Historical Hall of Fame')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 uppercase tracking-tight flex items-center gap-3">
                <i class="fas fa-monument text-amber-500"></i> Hall of Fame Arsitektur
            </h1>
            <p class="text-sm text-gray-500 mt-1">Daftar lengkap jawara dari musim-musim sebelumnya.</p>
        </div>
        <a href="{{ route('admin.gamification.leaderboard.index') }}?tab=hall"
           class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-50 transition">
            <i class="fas fa-chevron-left mr-1"></i> Kembali
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest w-20">Rank</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Siswa</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Season</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Pencapaian</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Final APP</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Tanggal Arsip</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($winners as $winner)
                        <tr class="hover:bg-amber-50/40 transition">
                            <td class="px-6 py-4">
                                <span class="text-xl">{{ match((int)$winner->rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => '#'.$winner->rank } }}</span>
                            </td>
                            <td class="px-6 py-4 font-black text-gray-900">
                                {{ $winner->display_name ?? $winner->user->name }}
                                <p class="text-[9px] text-gray-400 font-normal uppercase tracking-widest">{{ $winner->class_name }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-bold text-gray-700">{{ $winner->season->name ?? 'N/A' }}</p>
                                <p class="text-[9px] text-gray-400 uppercase">{{ $winner->season->academic_year ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-[9px] font-black bg-indigo-100 text-indigo-700 rounded-lg uppercase tracking-widest">
                                    Lv.{{ $winner->level_final }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-black text-indigo-600">{{ number_format($winner->app_points_final, 1) }} APP</td>
                            <td class="px-6 py-4 text-gray-400 text-xs">{{ $winner->recorded_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <i class="fas fa-monument text-5xl text-gray-100 mb-4 block"></i>
                                <p class="text-gray-400 font-medium italic">Belum ada data kemenangan di Hall of Fame.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($winners->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $winners->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
