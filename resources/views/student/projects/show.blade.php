@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header Navigation --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('student.projects.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 leading-snug">{{ $assignment->title }}</h1>
                <p class="text-xs text-gray-500 mt-1 flex flex-wrap items-center gap-2">
                    @if($assignment->subject)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700">
                            {{ $assignment->subject->name }}
                        </span>
                        <span>•</span>
                    @endif
                    <span>Maks. {{ $assignment->max_file_size_mb }} MB per file</span>
                    <span>•</span>
                    <span>Total {{ $assignment->max_slots }} Slot Project</span>
                </p>
            </div>
        </div>

        <a href="{{ route('gallery.show', $assignment) }}" target="_blank"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl text-xs font-semibold bg-white border border-gray-200 text-gray-700 shadow-xs hover:bg-gray-50 transition">
            <i class="fas fa-external-link-alt text-indigo-500"></i> Lihat Galeri Publik
        </a>
    </div>

    {{-- Deskripsi Assignment --}}
    @if($assignment->description)
    <div class="bg-indigo-50/50 border border-indigo-100 rounded-3xl p-5">
        <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-700 mb-1">
            <i class="fas fa-info-circle mr-1"></i> Petunjuk Tugas
        </h4>
        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $assignment->description }}</p>
    </div>
    @endif

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="rounded-2xl p-4 text-sm font-medium bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-500"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div class="rounded-2xl p-4 text-sm font-medium bg-blue-50 text-blue-800 border border-blue-200 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-500"></i>{{ session('info') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-2xl p-4 text-sm font-medium bg-rose-50 text-rose-800 border border-rose-200">
            <p class="font-bold flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan saat upload:</p>
            <ul class="list-disc list-inside mt-1 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Slot Containers --}}
    <div class="space-y-6">
        @for($slot = 1; $slot <= $assignment->max_slots; $slot++)
        @php
            $sub = $submissions->get($slot);
        @endphp
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-7 overflow-hidden" x-data="{ openUpload: {{ $sub ? 'false' : 'true' }} }">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 font-black text-sm flex items-center justify-center">
                        #{{ $slot }}
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-gray-800">
                            Slot Project ke-{{ $slot }}
                        </h3>
                        <p class="text-xs text-gray-400">
                            {{ $sub ? 'Sudah mengumpulkan tugas' : 'Belum ada project yang dikumpulkan' }}
                        </p>
                    </div>
                </div>

                @if($sub)
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ $sub->getIndexUrl() }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white shadow-md transition hover:opacity-90"
                           style="background: var(--brand-primary)">
                            <i class="fas fa-play text-[10px]"></i> Buka Web Project
                        </a>
                        <button type="button" @click="openUpload = !openUpload"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            <i class="fas fa-redo-alt text-[10px]"></i>
                            <span x-text="openUpload ? 'Tutup Form' : 'Ganti File'"></span>
                        </button>
                        <form action="{{ route('student.projects.destroy', [$assignment, $sub]) }}"
                              method="POST"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas di Slot #{{ $slot }} ini?\n\nFile project Anda akan dihapus dari server dan galeri publik.');"
                              class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 border border-rose-200/80 transition"
                                    title="Hapus tugas di slot ini">
                                <i class="fas fa-trash-alt text-[10px]"></i> Hapus Tugas
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- Detail submission jika sudah ada --}}
            @if($sub)
            <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Judul Project Siswa</span>
                    <h4 class="text-base font-bold text-gray-900 mt-0.5">{{ $sub->title }}</h4>
                    <p class="text-xs text-gray-500 font-mono mt-1">
                        File: {{ $sub->original_filename }} ({{ $sub->fileSizeFormatted() }}) • Diunggah: {{ $sub->uploaded_at?->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    @if($sub->has_css)
                        <span class="px-2 py-0.5 rounded-lg text-xs font-black bg-blue-100 text-blue-800">CSS</span>
                    @endif
                    @if($sub->has_js)
                        <span class="px-2 py-0.5 rounded-lg text-xs font-black bg-amber-100 text-amber-800">JS</span>
                    @endif
                    @if($sub->has_images)
                        <span class="px-2 py-0.5 rounded-lg text-xs font-black bg-emerald-100 text-emerald-800">Gambar</span>
                    @endif
                    @if($sub->has_audio)
                        <span class="px-2 py-0.5 rounded-lg text-xs font-black bg-purple-100 text-purple-800">Audio</span>
                    @endif
                    @if($sub->has_video)
                        <span class="px-2 py-0.5 rounded-lg text-xs font-black bg-rose-100 text-rose-800">Video</span>
                    @endif
                </div>
            </div>
            @endif

            {{-- Form Upload (Baru atau Update) --}}
            <div x-show="openUpload" x-transition class="mt-5 pt-4 border-t border-dashed border-gray-200">
                @if($sub)
                <div class="mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-amber-600"></i>
                    <span><strong>Perhatian:</strong> Mengunggah file baru akan menggantikan dan menghapus seluruh file project lama di slot ini.</span>
                </div>
                @endif

                <form method="POST" action="{{ route('student.projects.upload', $assignment) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="slot_number" value="{{ $slot }}">

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Judul Project <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('slot_number') == $slot ? old('title') : ($sub->title ?? '') }}"
                               placeholder="Contoh: Web Animasi CSS - Profil Sekolah"
                               class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                               style="--tw-ring-color: var(--brand-primary)" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            File Archive Project (.ZIP) <span class="text-rose-500">*</span>
                        </label>
                        <input type="file" name="project_file" accept=".zip,.rar"
                               class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-gray-200 rounded-2xl p-2" required>
                        <p class="text-[11px] text-gray-400 mt-1.5">
                            <i class="fas fa-check-circle text-emerald-500 mr-1"></i>
                            Maksimal ukuran: <strong>{{ $assignment->max_file_size_mb }} MB</strong>. File ZIP <strong>wajib</strong> memiliki file <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-700 font-mono font-bold">index.html</code> di dalamnya.
                        </p>
                    </div>

                    <div class="pt-2 flex items-center gap-3">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl text-white text-xs font-bold shadow-md transition hover:opacity-90"
                                style="background: var(--brand-primary)">
                            <i class="fas fa-eye"></i> Upload & Pratinjau
                        </button>
                        @if($sub)
                        <button type="button" @click="openUpload = false"
                                class="px-4 py-2.5 rounded-2xl text-xs font-semibold text-gray-500 hover:bg-gray-100 transition">
                            Batal
                        </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        @endfor
    </div>
</div>
@endsection
