@extends('layouts.app')

@section('title', 'Pengumuman - ' . ($configs['school_name'] ?? 'ExamFlow'))

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div>
        <h1 class="text-xl font-black text-gray-900 flex items-center gap-3">
            <i class="fas fa-bullhorn text-[var(--brand-primary)]"></i> Pengumuman
        </h1>
        <p class="text-sm text-gray-500 mt-0.5">Pengumuman yang ditujukan kepada Anda.</p>
    </div>

    @if($announcements->isEmpty())
    <div class="theme-surface-card rounded-2xl flex flex-col items-center justify-center py-16 text-center theme-soft-shadow">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background: var(--brand-glow)">
            <i class="fas fa-check-circle text-[var(--brand-primary)] text-2xl"></i>
        </div>
        <p class="font-bold text-gray-700">Tidak ada pengumuman aktif</p>
        <p class="text-sm text-gray-400 mt-1">Semua informasi sudah dibaca.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($announcements as $ann)
        <div class="announcement-banner announcement-banner-{{ $ann->type }}">
            {{-- Type icon --}}
            <div class="shrink-0 mt-0.5">
                @if($ann->type === 'urgent')
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                        <i class="fas fa-bell text-red-600 animate-pulse"></i>
                    </div>
                @elseif($ann->type === 'warning')
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-amber-600"></i>
                    </div>
                @else
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-info-circle text-blue-600"></i>
                    </div>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-1">
                @if($ann->type === 'urgent')
                    <span class="inline-block text-[8px] font-black bg-red-600 text-white px-1.5 py-0.5 rounded uppercase tracking-widest mb-1">URGENT</span>
                @endif
                <p class="font-black text-sm">{{ $ann->title }}</p>
                <p class="text-sm mt-1 leading-relaxed opacity-90">{{ $ann->content }}</p>
                <div class="flex items-center gap-3 mt-2 text-[10px] font-semibold opacity-60 uppercase tracking-wide">
                    <span><i class="fas fa-user mr-1"></i>{{ $ann->sender->formatted_name }}</span>
                    <span><i class="fas fa-clock mr-1"></i>{{ $ann->created_at->diffForHumans() }}</span>
                    @if($ann->expires_at)
                        <span class="text-red-500"><i class="fas fa-calendar-times mr-1"></i>Berakhir {{ $ann->expires_at->format('d M Y') }}</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
