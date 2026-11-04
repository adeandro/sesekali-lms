@extends('layouts.app')

@section('title', 'Kunci Jawaban CT — Kelas ' . $ctClass->name)

@section('page-title', 'Kunci Jawaban — Kelas ' . $ctClass->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fadeIn pb-12">

    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ct.teams', $ctClass) }}" class="text-gray-400 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">Kunci Jawaban — {{ $ctClass->name }}</h2>
        </div>
        <a href="{{ route('admin.ct.answer-key.pdf', $ctClass) }}"
           class="px-4 py-2 bg-orange-500 text-white text-xs font-bold rounded-xl hover:bg-orange-600 transition">
            <i class="fas fa-file-pdf mr-1"></i>Download PDF
        </a>
    </div>

    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-xs font-medium">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        Halaman ini hanya untuk guru. Jangan ditampilkan kepada siswa.
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs font-black text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left w-14">Tim</th>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Menit</th>
                    <th class="px-4 py-3 text-left">n</th>
                    <th class="px-4 py-3 text-left">Files</th>
                    <th class="px-4 py-3 text-left">Mapel Utama</th>
                    <th class="px-4 py-3 text-left">Jam Unduh</th>
                    <th class="px-4 py-3 text-left">Shift Caesar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($teams as $team)
                    @php
                        $p     = $team->params;
                        $code  = $generator->deriveCode($p);
                        $key   = $generator->answerKey($p);
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-black text-indigo-700">{{ $team->number }}</td>
                        <td class="px-4 py-3 font-mono font-black text-xl text-gray-900 tracking-widest">{{ $code }}</td>
                        <td class="px-4 py-3 font-mono text-gray-600">{{ $key['minute'] }}</td>
                        <td class="px-4 py-3 font-mono text-gray-600">{{ $p['n'] }}</td>
                        <td class="px-4 py-3 font-mono text-gray-600">{{ $p['files_count'] }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $p['subjects'][0] ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-gray-600">{{ $p['real_time'] }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $p['shift'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Detailed breakdown per team --}}
    <div class="space-y-4">
        @foreach($teams as $team)
            @php
                $p    = $team->params;
                $code = $generator->deriveCode($p);
                $key  = $generator->answerKey($p);
            @endphp
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-black text-gray-900">Tim {{ $team->number }}
                        <span class="font-mono text-indigo-600 ml-2">{{ $code }}</span>
                    </h3>
                    <a href="{{ route('admin.ct.teams.worksheet', $team) }}"
                       class="text-xs text-purple-600 hover:underline">
                        <i class="fas fa-file-pdf mr-1"></i>Lembar Kerja
                    </a>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-gray-400 font-bold uppercase tracking-widest mb-0.5">Tahap 1</p>
                        <p class="font-mono font-bold text-gray-700">{{ $p['real_time'] }}</p>
                        <p class="text-gray-400">(jam unduh nyata)</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-gray-400 font-bold uppercase tracking-widest mb-0.5">Tahap 2 (Caesar)</p>
                        <p class="font-mono font-bold text-gray-700">Shift {{ $p['shift'] }}</p>
                        <p class="text-gray-400">{{ $key['n_word'] }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-gray-400 font-bold uppercase tracking-widest mb-0.5">Tahap 3</p>
                        <p class="font-mono font-bold text-gray-700">{{ $p['files_count'] }} file</p>
                        <p class="text-gray-400">mapel {{ $p['subjects'][0] }}</p>
                    </div>
                    <div class="bg-indigo-50 rounded-xl p-3">
                        <p class="text-indigo-400 font-bold uppercase tracking-widest mb-0.5">Kode Final</p>
                        <p class="font-mono font-black text-indigo-700 text-lg tracking-widest">{{ $code }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
