@extends('election.public.layout')

@section('title', $title ?? 'Informasi Pilketos')

@section('content')
<div class="flex-1 flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center bg-slate-800/80 border border-slate-700/80 rounded-2xl p-8 shadow-2xl backdrop-blur">
        <div class="w-16 h-16 rounded-2xl bg-slate-700/60 border border-slate-600/50 flex items-center justify-center text-3xl mx-auto mb-4 text-slate-300 shadow-inner">
            📋
        </div>
        <h2 class="text-xl font-bold text-white mb-2">
            {{ $title ?? 'Informasi Pemilihan' }}
        </h2>
        <p class="text-base text-slate-300 font-medium mb-4">
            {{ $message ?? 'Belum ada informasi yang dipublikasikan.' }}
        </p>
        <p class="text-xs text-slate-400 leading-relaxed">
            Halaman ini akan menampilkan profil kandidat atau hasil rekapitulasi suara resmi setelah dibuka oleh panitia pemilihan.
        </p>
    </div>
</div>
@endsection
