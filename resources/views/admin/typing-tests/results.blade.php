@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.typing-tests.index') }}" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold" style="color: var(--brand-primary)">
                    <i class="fas fa-chart-bar mr-2"></i>Hasil Tes: {{ $test->title }}
                </h1>
                <p class="text-sm text-gray-500 mt-0.5 flex flex-wrap items-center gap-3">
                    <span>Durasi: {{ $test->duration_seconds }} detik</span>
                    <span>|</span>
                    <span>Target WPM: {{ $test->target_wpm }}</span>
                    <span>|</span>
                    <span>{{ $attempts->count() }} peserta selesai</span>
                    @if($test->use_token)
                    <span>|</span>
                    <span class="flex items-center gap-1.5">
                        <span class="text-xs text-gray-500">Token:</span>
                        <span class="font-mono font-black text-indigo-600 tracking-widest bg-indigo-50 px-2 py-0.5 rounded-lg">{{ $test->token }}</span>
                        <button onclick="navigator.clipboard.writeText('{{ $test->token }}')"
                                class="text-gray-400 hover:text-gray-600 text-xs">
                            <i class="fas fa-copy"></i> Salin
                        </button>
                    </span>
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ route('admin.typing-tests.export', $test) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-medium shadow-md transition hover:opacity-90"
           style="background: #16a34a">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($attempts->isEmpty())
            <div class="text-center py-16">
                <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">Belum ada siswa yang menyelesaikan tes ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-500 uppercase" style="background: var(--brand-bg, #f8fafc)">
                        <tr>
                            <th class="px-6 py-4 text-left">No</th>
                            <th class="px-6 py-4 text-left">Nama</th>
                            <th class="px-6 py-4 text-left">NIS</th>
                            <th class="px-6 py-4 text-left">Kelas</th>
                            <th class="px-6 py-4 text-right">WPM</th>
                            <th class="px-6 py-4 text-right">Akurasi</th>
                            <th class="px-6 py-4 text-right">Nilai</th>
                            <th class="px-6 py-4 text-left">Waktu Selesai</th>
                            <th class="px-6 py-4 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($attempts as $i => $attempt)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $attempt->student->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $attempt->student->nis ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $attempt->student->classroom->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-right font-mono font-semibold text-blue-700">{{ number_format($attempt->wpm, 2) }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-semibold {{ $attempt->accuracy >= 90 ? 'text-emerald-600' : ($attempt->accuracy >= 70 ? 'text-amber-600' : 'text-red-600') }}">
                                    {{ number_format($attempt->accuracy, 2) }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-lg {{ $attempt->final_score >= 85 ? 'text-emerald-600' : ($attempt->final_score >= 70 ? 'text-amber-600' : 'text-red-600') }}">
                                    {{ number_format($attempt->final_score, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs">
                                {{ $attempt->completed_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <form method="POST"
                                      action="{{ route('admin.typing-tests.attempts.reset', [$test, $attempt]) }}"
                                      class="inline"
                                      onsubmit="return confirm('Reset attempt {{ addslashes($attempt->student->name) }}?\nSiswa akan bisa mengerjakan ulang dari awal.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs font-bold text-rose-500 hover:text-rose-700 transition flex items-center gap-1">
                                        <i class="fas fa-redo-alt"></i> Reset
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
