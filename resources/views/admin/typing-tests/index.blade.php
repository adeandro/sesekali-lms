@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color: var(--brand-primary)">
                <i class="fas fa-keyboard mr-2"></i>Tes Mengetik
            </h1>
            <p class="text-sm text-gray-500 mt-1">Kelola jadwal tes mengetik untuk siswa</p>
        </div>
        <a href="{{ route('admin.typing-tests.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-medium shadow-md transition hover:opacity-90"
           style="background: var(--brand-primary)">
            <i class="fas fa-plus"></i> Buat Tes Mengetik
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="rounded-xl p-4 text-sm font-medium" style="background: #d1fae5; color: #065f46;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($tests->isEmpty())
            <div class="text-center py-16">
                <i class="fas fa-keyboard text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">Belum ada tes mengetik. Buat yang pertama!</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-500 uppercase" style="background: var(--brand-bg, #f8fafc)">
                        <tr>
                            <th class="px-6 py-4 text-left">Judul</th>
                            <th class="px-6 py-4 text-left">Durasi</th>
                            <th class="px-6 py-4 text-left">Target WPM</th>
                            <th class="px-6 py-4 text-left">Peserta</th>
                            <th class="px-6 py-4 text-left">Status</th>
                            <th class="px-6 py-4 text-left">Dibuat</th>
                            <th class="px-6 py-4 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($tests as $test)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800">{{ $test->title }}</div>
                                @if($test->token)
                                    <span class="text-xs text-gray-400"><i class="fas fa-key mr-1"></i>Butuh token</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $test->duration_seconds }} detik</td>
                            <td class="px-6 py-4 text-gray-600">{{ $test->target_wpm }} WPM</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                    <i class="fas fa-users"></i> {{ $test->attempts_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($test->status === 'published')
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Published</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Draft</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $test->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.typing-tests.results', $test) }}"
                                       class="px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                        <i class="fas fa-chart-bar mr-1"></i>Hasil
                                    </a>
                                    <a href="{{ route('admin.typing-tests.edit', $test) }}"
                                       class="px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.typing-tests.destroy', $test) }}"
                                          onsubmit="return confirm('Hapus tes mengetik ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100 transition">
                                            <i class="fas fa-trash mr-1"></i>Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-50">
                {{ $tests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
