@extends('election.booth.layout')

@section('title', 'Bilik Suara Ditutup')

@section('content')
<div class="bg-slate-800/90 border border-slate-700 rounded-2xl shadow-2xl p-8 text-center backdrop-blur">
    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-700/50 border border-slate-600 flex items-center justify-center text-4xl">
        🔒
    </div>
    <h2 class="text-2xl font-bold text-white">Bilik Suara Ditutup</h2>
    <p class="text-sm text-slate-300 mt-2">
        Saat ini tidak ada periode pemilihan Ketua OSIS yang sedang aktif atau dibuka.
    </p>
    <p class="text-xs text-slate-500 mt-6">
        Jika Anda adalah panitia atau pemilih, silakan hubungi penanggung jawab Pilketos.
    </p>
</div>
@endsection
