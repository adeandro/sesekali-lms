@extends('ct.layout')
@section('title', 'Sertifikat — Tim ' . $team->number)
@section('container_width', 'max-w-2xl')

@section('content')
<div class="bg-slate-800 border border-green-700/40 rounded-2xl overflow-hidden shadow-xl">
    {{-- Success banner --}}
    <div class="bg-green-900/30 border-b border-green-700/30 p-6 text-center">
        <div class="text-5xl mb-3">🏆</div>
        <h2 class="text-2xl font-bold text-green-300">Brankas Terbuka!</h2>
        <p class="text-slate-400 text-sm mt-1">Kelas {{ $team->ctClass->name }} &mdash; Tim {{ $team->number }}</p>
    </div>

    <div class="p-6 space-y-4">
        {{-- Members --}}
        <div class="bg-slate-900/60 rounded-xl p-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Anggota Tim</p>
            <ul class="space-y-1">
                @foreach($team->member_names as $i => $name)
                <li class="flex items-center gap-2 text-sm text-white">
                    <span class="text-slate-500 text-xs w-4">{{ $i + 1 }}.</span>
                    {{ $name }}
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-slate-900/60 rounded-xl p-3 text-center">
                <div class="text-xl font-bold text-white">{{ $wrongCount }}</div>
                <div class="text-xs text-slate-500 mt-0.5">Percobaan salah</div>
            </div>
            <div class="bg-slate-900/60 rounded-xl p-3 text-center">
                <div class="text-xl font-bold text-white">{{ $team->hint_count }}</div>
                <div class="text-xs text-slate-500 mt-0.5">Petunjuk terpakai</div>
            </div>
            <div class="bg-slate-900/60 rounded-xl p-3 text-center">
                <div class="text-sm font-bold text-white">{{ $team->opened_at->format('H:i') }}</div>
                <div class="text-xs text-slate-500 mt-0.5">Selesai pukul</div>
            </div>
        </div>

        {{-- Certificate no --}}
        <div class="bg-slate-900/60 rounded-xl p-3 flex items-center justify-between gap-3">
            <div>
                <p class="text-xs text-slate-500">Nomor Sertifikat</p>
                <p class="font-mono font-bold text-white text-sm">{{ $team->certificate_no }}</p>
            </div>
            <a href="{{ route('ct.certificate.pdf', $team->certificate_no) }}"
               class="px-4 py-2 bg-green-700 hover:bg-green-600 text-white text-xs font-semibold rounded-lg transition whitespace-nowrap">
                📄 Unduh PDF
            </a>
        </div>

        {{-- Print link --}}
        <div class="text-center pt-2">
            <a href="{{ route('ct.certificate', $team->certificate_no) }}"
               class="inline-block px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium rounded-xl transition">
                🖨️ Buka Halaman Sertifikat (siap cetak)
            </a>
        </div>

        {{-- Verification --}}
        <p class="text-center text-xs text-slate-600">
            Verifikasi:
            <a href="{{ route('ct.verify', $team->certificate_no) }}" class="text-slate-400 hover:text-white underline transition">
                {{ route('ct.verify', $team->certificate_no) }}
            </a>
        </p>
    </div>
</div>
@endsection
