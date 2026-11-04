@extends('ct.layout')
@section('title', 'Pilih Kelas — Kode Merah')

@section('content')
<div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-red-800/30 border border-red-700/40 text-4xl mb-4">🔎</div>
    <h2 class="text-2xl font-bold text-white">Operasi Kode Merah</h2>
    <p class="text-slate-400 text-sm mt-1">Pilih kelas tim forensik digital Anda</p>
</div>

@if($classes->isEmpty())
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-8 text-center text-slate-400">
        <p class="text-lg mb-1">Belum ada kelas yang aktif.</p>
        <p class="text-sm">Hubungi guru untuk membuka sesi Kode Merah.</p>
    </div>
@else
    <div class="grid gap-3">
        @foreach($classes as $class)
            @php
                $isOpen = $class->isOpen();
                $opensAt = $class->opens_at;
                $closesAt = $class->closes_at;
            @endphp
            @if($isOpen)
                <a href="{{ route('ct.teams', $class) }}"
                   class="block bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-red-700/60 rounded-2xl p-5 transition group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-red-700/20 border border-red-700/30 flex items-center justify-center text-xl">🏫</div>
                            <div>
                                <div class="font-bold text-white group-hover:text-red-300 transition">Kelas {{ $class->name }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    @if($closesAt)
                                        Tutup pukul {{ $closesAt->format('H:i') }}
                                    @else
                                        Sesi terbuka
                                    @endif
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-green-400 font-semibold flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-green-400 inline-block animate-pulse"></span> Buka
                        </span>
                    </div>
                </a>
            @else
                <div class="bg-slate-800/50 border border-slate-700/50 rounded-2xl p-5 opacity-60">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-700/30 border border-slate-700/40 flex items-center justify-center text-xl">🏫</div>
                            <div>
                                <div class="font-bold text-slate-300">Kelas {{ $class->name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    @if(!$class->is_active)
                                        Sesi tidak aktif
                                    @elseif($opensAt && now()->lt($opensAt))
                                        Buka pukul {{ $opensAt->format('H:i, d M Y') }}
                                    @elseif($closesAt && now()->gt($closesAt))
                                        Sesi telah ditutup pukul {{ $closesAt->format('H:i') }}
                                    @else
                                        Di luar jadwal
                                    @endif
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-slate-500 font-semibold">Tutup</span>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endif
@endsection
