@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color: var(--brand-primary)">
                <i class="fas fa-folder-open mr-2"></i>Galeri Tugas Siswa
            </h1>
            <p class="text-sm text-gray-500 mt-1">Kelola tugas project web dan pamerkan karya siswa di Galeri Publik</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('gallery.index') }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 shadow-xs hover:bg-gray-50 transition">
                <i class="fas fa-external-link-alt text-indigo-500"></i> Buka Galeri Publik
            </a>
            <a href="{{ route('admin.project-assignments.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-medium shadow-md transition hover:opacity-90"
               style="background: var(--brand-primary)">
                <i class="fas fa-plus"></i> Buat Assignment Baru
            </a>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="rounded-xl p-4 text-sm font-medium bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-500"></i>{{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($assignments->isEmpty())
            <div class="text-center py-16">
                <div class="w-16 h-16 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-folder-open"></i>
                </div>
                <p class="text-gray-600 font-semibold text-base">Belum ada assignment project</p>
                <p class="text-xs text-gray-400 mt-1 mb-5">Mulai buat assignment baru untuk memamerkan karya project siswa.</p>
                <a href="{{ route('admin.project-assignments.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-xs font-semibold transition hover:opacity-90"
                   style="background: var(--brand-primary)">
                    <i class="fas fa-plus"></i> Buat Assignment Pertama
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-500 uppercase tracking-wider" style="background: var(--brand-bg, #f8fafc)">
                        <tr>
                            <th class="px-6 py-4 text-left">Judul Assignment</th>
                            <th class="px-6 py-4 text-left">Mata Pelajaran</th>
                            <th class="px-6 py-4 text-center">Slot</th>
                            <th class="px-6 py-4 text-center">Max Size</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Total Submission</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($assignments as $assignment)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 leading-snug">{{ $assignment->title }}</div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs font-mono text-gray-400">/gallery/{{ $assignment->slug }}</span>
                                    <a href="{{ route('gallery.show', $assignment) }}" target="_blank"
                                       class="text-indigo-600 hover:text-indigo-800 text-xs" title="Lihat Galeri Publik">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                @if($assignment->subject)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700">
                                        {{ $assignment->subject->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">Umum / Semua Mapel</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-mono font-semibold text-gray-700">
                                {{ $assignment->max_slots }} slot
                            </td>
                            <td class="px-6 py-4 text-center text-xs font-semibold text-gray-600">
                                {{ $assignment->max_file_size_mb }} MB
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form method="POST" action="{{ route('admin.project-assignments.toggle', $assignment) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition {{ $assignment->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                                            title="Klik untuk ubah status">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $assignment->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                        {{ $assignment->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.project-assignments.submissions', $assignment) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                    <i class="fas fa-users text-[10px]"></i>
                                    {{ $assignment->submissions_count }} Karya
                                </a>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.project-assignments.submissions', $assignment) }}"
                                       class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-xl transition"
                                       title="Lihat Submissions">
                                        <i class="fas fa-list-check"></i>
                                    </a>
                                    <a href="{{ route('admin.project-assignments.edit', $assignment) }}"
                                       class="p-2 text-amber-600 hover:bg-amber-50 rounded-xl transition"
                                       title="Edit Assignment">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.project-assignments.destroy', $assignment) }}"
                                          onsubmit="return confirm('Hapus assignment {{ addslashes($assignment->title) }}?\nSemua data submission siswa akan ikut terhapus.')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 text-rose-600 hover:bg-rose-50 rounded-xl transition"
                                                title="Hapus Assignment">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($assignments->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $assignments->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
