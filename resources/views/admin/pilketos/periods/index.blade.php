@extends('layouts.app')

@section('title', 'Manajemen Pilketos')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">🗳️ Manajemen Pilketos</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pemilihan Ketua OSIS</p>
        </div>
        <a href="{{ route('admin.pilketos.periods.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Periode Baru
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg text-green-700 dark:text-green-300 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg text-red-700 dark:text-red-300 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Periods Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        @if($periods->isEmpty())
            <div class="p-12 text-center">
                <div class="text-5xl mb-4">🗳️</div>
                <p class="text-slate-500 dark:text-slate-400">Belum ada periode pemilihan.</p>
                <a href="{{ route('admin.pilketos.periods.create') }}" class="mt-4 inline-block text-indigo-600 hover:underline text-sm">
                    Buat periode pertama →
                </a>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Periode</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Status</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Waktu Pemilihan</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Dibuat</th>
                        <th class="px-6 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($periods as $period)
                        @php
                            $colorMap = [
                                'draft'    => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                'ready'    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                'open'     => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                'closed'   => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                'archived' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
                            ];
                            $color = $colorMap[$period->status] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800 dark:text-white">{{ $period->title }}</div>
                                @if($period->description)
                                    <div class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $period->description }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                    {{ $period->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                @if($period->event_date)
                                    <div class="text-xs font-medium">{{ $period->event_date->format('d M Y') }}</div>
                                @else
                                    <span class="text-slate-400 text-xs">Belum diatur</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs">
                                {{ $period->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.pilketos.periods.show', $period) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs font-medium rounded-lg transition">
                                    Detail →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($periods->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700">
                    {{ $periods->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
