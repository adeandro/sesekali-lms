@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.project-assignments.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold" style="color: var(--brand-primary)">
                    <i class="fas fa-users mr-2"></i>Pengumpulan Tugas: {{ $assignment->title }}
                </h1>
                <p class="text-sm text-gray-500 mt-1 flex flex-wrap items-center gap-2">
                    @if($assignment->subject)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700">
                            {{ $assignment->subject->name }}
                        </span>
                        <span>•</span>
                    @endif
                    <span>Maks. {{ $assignment->max_slots }} slot per siswa</span>
                    <span>•</span>
                    <span>Total <strong>{{ $submissions->count() }}</strong> karya terkumpul</span>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if($submissions->isNotEmpty())
                <a href="{{ route('admin.project-assignments.archive', $assignment) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-xs font-semibold shadow-md transition hover:opacity-90 bg-emerald-600 hover:bg-emerald-700"
                   title="Download seluruh project siswa dalam satu file ZIP">
                    <i class="fas fa-file-archive"></i> Arsipkan & Unduh ZIP ({{ $submissions->count() }})
                </a>

                <form action="{{ route('admin.project-assignments.submissions.clear-all', $assignment) }}"
                      method="POST"
                      onsubmit="return confirm('PERINGATAN BERSIHKAN STORAGE:\n\nApakah Anda yakin ingin MENGHAPUS SEMUA ({{ $submissions->count() }}) karya tugas siswa pada assignment ini dari server?\n\nPastikan Anda sudah mengunduh file arsip ZIP ke komputer Anda sebelum melanjutkan.');"
                      class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition"
                            title="Hapus semua file submission dari server untuk mengosongkan storage">
                        <i class="fas fa-broom"></i> Bersihkan Server
                    </button>
                </form>
            @endif

            <a href="{{ route('gallery.show', $assignment) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-xs font-semibold shadow-md transition hover:opacity-90"
               style="background: var(--brand-primary)">
                <i class="fas fa-external-link-alt"></i> Galeri Publik
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-5 py-3.5 rounded-2xl flex items-center gap-3 shadow-xs">
            <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 text-sm px-5 py-3.5 rounded-2xl flex items-center gap-3 shadow-xs">
            <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
            <span class="font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Submissions Table --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        @if($submissions->isEmpty())
            <div class="text-center py-16">
                <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fas fa-inbox"></i>
                </div>
                <p class="text-gray-600 font-semibold text-base">Belum ada siswa yang mengumpulkan tugas ini</p>
                <p class="text-xs text-gray-400 mt-1">Siswa dapat mengumpulkan tugas melalui menu Tugas Project di akun mereka.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-500 uppercase tracking-wider" style="background: var(--brand-bg, #f8fafc)">
                        <tr>
                            <th class="px-6 py-4 text-left">No</th>
                            <th class="px-6 py-4 text-left">Nama Siswa</th>
                            <th class="px-6 py-4 text-left">NIS</th>
                            <th class="px-6 py-4 text-left">Kelas</th>
                            <th class="px-6 py-4 text-center">Slot</th>
                            <th class="px-6 py-4 text-left">Judul Project</th>
                            <th class="px-6 py-4 text-center">Ukuran</th>
                            <th class="px-6 py-4 text-center">Konten</th>
                            <th class="px-6 py-4 text-left">Waktu Upload</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($submissions as $i => $submission)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-6 py-4 text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-6 py-4 font-bold text-gray-800">
                                {{ $submission->student->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-xs">
                                {{ $submission->student->nis ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-xs">
                                {{ $submission->student->classroom->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                    Slot {{ $submission->slot_number }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $submission->title }}
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-xs text-gray-600">
                                {{ $submission->fileSizeFormatted() }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex flex-wrap items-center justify-center gap-1">
                                    @if($submission->has_css)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200">CSS</span>
                                    @endif
                                    @if($submission->has_js)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200">JS</span>
                                    @endif
                                    @if($submission->has_images)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">IMG</span>
                                    @endif
                                    @if($submission->has_audio)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-purple-50 text-purple-700 border border-purple-200">AUD</span>
                                    @endif
                                    @if($submission->has_video)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-200">VID</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500 font-mono">
                                {{ $submission->uploaded_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2 justify-end">
                                    <a href="{{ $submission->getIndexUrl() }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white shadow-xs hover:opacity-90 transition"
                                       style="background: var(--brand-primary)" title="Buka web project">
                                        <i class="fas fa-play text-[10px]"></i> Lihat
                                    </a>

                                    <form action="{{ route('admin.project-assignments.submissions.destroy', [$assignment, $submission]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas milik {{ addslashes($submission->student->name ?? 'siswa ini') }} (Slot #{{ $submission->slot_number }})?\n\nFile di server akan dihapus dan slot akan bersih kembali.');"
                                          class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-semibold bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 border border-rose-200/80 transition"
                                                title="Hapus Pengumpulan Tugas Siswa">
                                            <i class="fas fa-trash-alt text-[10px]"></i> Hapus
                                        </button>
                                    </form>
                                </div>
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
