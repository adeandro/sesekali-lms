@extends('ct.layout')
@section('title', 'Verifikasi Sertifikat')
@section('container_width', 'max-w-md')

@section('content')
<div class="text-center mb-6">
    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-green-900/30 border border-green-700/30 text-3xl mb-3">✅</div>
    <h2 class="text-xl font-bold text-white">Verifikasi Sertifikat</h2>
</div>

<div class="bg-slate-800 border border-green-700/30 rounded-2xl overflow-hidden shadow-xl">
    <div class="bg-green-900/20 border-b border-green-700/20 p-4 text-center">
        <p class="text-xs text-slate-400">Nomor Sertifikat</p>
        <p class="font-mono font-bold text-green-300 text-lg">{{ $team->certificate_no }}</p>
    </div>

    <div class="p-5 space-y-4">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Anggota Tim</p>
            <ul class="space-y-1">
                @foreach($team->member_names as $i => $name)
                <li class="text-sm text-white flex gap-2">
                    <span class="text-slate-500">{{ $i+1 }}.</span>
                    {{ $name }}
                </li>
                @endforeach
            </ul>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div class="bg-slate-900/60 rounded-xl p-3">
                <p class="text-xs text-slate-500">Kelas</p>
                <p class="font-semibold text-white">{{ $team->ctClass->name }}</p>
            </div>
            <div class="bg-slate-900/60 rounded-xl p-3">
                <p class="text-xs text-slate-500">Kelompok</p>
                <p class="font-semibold text-white">Tim {{ $team->number }}</p>
            </div>
            <div class="bg-slate-900/60 rounded-xl p-3 col-span-2">
                <p class="text-xs text-slate-500">Waktu Penyelesaian</p>
                <p class="font-semibold text-white">{{ $team->opened_at->format('d/m/Y H:i') }} WIB</p>
            </div>
        </div>

        <div class="bg-green-900/20 border border-green-700/30 rounded-xl p-3 text-center">
            <p class="text-xs text-green-400 font-semibold">✓ Sertifikat Valid</p>
            <p class="text-xs text-slate-500 mt-0.5">Data ini otentik dan tersimpan di sistem {{ config('ct.school_name') }}.</p>
        </div>
    </div>
</div>
@endsection
