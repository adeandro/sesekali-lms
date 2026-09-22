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
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-7 overflow-hidden"
             x-data="uploadSlotHandler({
                openUpload: {{ $sub ? 'false' : 'true' }},
                maxMb: {{ (int) $assignment->max_file_size_mb }}
             })">
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

                <form method="POST"
                      action="{{ route('student.projects.upload', $assignment) }}"
                      enctype="multipart/form-data"
                      @submit.prevent="submitForm($event)"
                      class="space-y-4 no-loading">
                    @csrf
                    <input type="hidden" name="slot_number" value="{{ $slot }}">

                    <!-- Alert Error AJAX -->
                    <div x-show="errorMessage" x-cloak x-transition
                         class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-rose-500 mt-0.5 text-base shrink-0"></i>
                        <div class="flex-1 leading-relaxed">
                            <strong class="font-bold block mb-0.5">Gagal Mengunggah Proyek:</strong>
                            <span x-text="errorMessage"></span>
                        </div>
                        <button type="button" @click="errorMessage = ''" class="text-rose-400 hover:text-rose-600 transition">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Judul Project <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" :disabled="isUploading"
                               value="{{ old('slot_number') == $slot ? old('title') : ($sub->title ?? '') }}"
                               placeholder="Contoh: Web Animasi CSS - Profil Sekolah"
                               class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent disabled:bg-gray-50 disabled:text-gray-400"
                               style="--tw-ring-color: var(--brand-primary)" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            File Archive Project (.ZIP) <span class="text-rose-500">*</span>
                        </label>
                        <input type="file" name="project_file" accept=".zip,.rar" :disabled="isUploading"
                               @change="onFileChosen($event)"
                               class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-gray-200 rounded-2xl p-2 disabled:opacity-50 disabled:cursor-not-allowed" required>
                        <p class="text-[11px] text-gray-400 mt-1.5">
                            <i class="fas fa-check-circle text-emerald-500 mr-1"></i>
                            Maksimal ukuran: <strong>{{ $assignment->max_file_size_mb }} MB</strong>. File ZIP <strong>wajib</strong> memiliki file <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-700 font-mono font-bold">index.html</code> di dalamnya.
                        </p>
                    </div>

                    <!-- Progress Bar Interaktif -->
                    <div x-show="isUploading" x-cloak x-transition
                         class="p-4 rounded-2xl bg-gradient-to-br from-indigo-50/70 via-purple-50/40 to-blue-50/70 border border-indigo-100 shadow-sm space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 font-bold text-gray-800">
                                <template x-if="progress < 100">
                                    <i class="fas fa-cloud-upload-alt text-indigo-600 text-sm animate-bounce"></i>
                                </template>
                                <template x-if="progress >= 100">
                                    <i class="fas fa-cog fa-spin text-purple-600 text-sm"></i>
                                </template>
                                <span x-text="statusText"></span>
                            </div>
                            <span class="font-mono font-extrabold text-xs px-2.5 py-0.5 rounded-full bg-white text-indigo-700 border border-indigo-100 shadow-xs"
                                  x-text="progress + '%'"></span>
                        </div>

                        <!-- Progress Bar Track -->
                        <div class="w-full bg-gray-200/80 rounded-full h-3 p-0.5 overflow-hidden shadow-inner">
                            <div class="h-full rounded-full transition-all duration-150 relative overflow-hidden"
                                 :style="'width: ' + progress + '%; background: linear-gradient(90deg, #6366f1, #8b5cf6, #ec4899);'">
                                <div class="absolute inset-0 bg-white/25 animate-pulse"></div>
                            </div>
                        </div>

                        <!-- Detail Meta / Ukuran Data -->
                        <div class="flex items-center justify-between text-[11px] text-gray-500 font-medium pt-0.5">
                            <span class="flex items-center gap-1.5">
                                <i class="fas fa-file-archive text-indigo-400"></i>
                                <span x-text="loadedText"></span>
                            </span>
                            <span class="text-gray-400 italic">
                                <template x-if="progress < 100">
                                    <span>Mohon tunggu, jangan tutup halaman ini...</span>
                                </template>
                                <template x-if="progress >= 100">
                                    <span class="text-indigo-600 font-bold flex items-center gap-1">
                                        <i class="fas fa-circle-notch fa-spin text-[10px]"></i> Sedang memproses di server...
                                    </span>
                                </template>
                            </span>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center gap-3">
                        <button type="submit"
                                :disabled="isUploading"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl text-white text-xs font-bold shadow-md transition hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
                                style="background: var(--brand-primary)">
                            <template x-if="!isUploading">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-eye"></i> Upload & Pratinjau
                                </span>
                            </template>
                            <template x-if="isUploading">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <span x-text="progress < 100 ? 'Mengunggah (' + progress + '%)' : 'Memproses...'"></span>
                                </span>
                            </template>
                        </button>
                        @if($sub)
                        <button type="button" @click="openUpload = false"
                                :disabled="isUploading"
                                class="px-4 py-2.5 rounded-2xl text-xs font-semibold text-gray-500 hover:bg-gray-100 transition disabled:opacity-40">
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

<script>
function uploadSlotHandler(config) {
    return {
        openUpload: config.openUpload,
        maxMb: config.maxMb,
        isUploading: false,
        progress: 0,
        statusText: '',
        loadedText: '',
        errorMessage: '',

        onFileChosen(event) {
            const input = event.target;
            this.errorMessage = '';
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const maxBytes = this.maxMb * 1024 * 1024;
                if (file.size > maxBytes) {
                    const actualMb = (file.size / (1024 * 1024)).toFixed(2);
                    alert('Ukuran file (' + actualMb + ' MB) melebihi batas maksimal tugas (' + this.maxMb + ' MB).\n\nSilakan kompres ulang atau pilih file yang lebih kecil.');
                    input.value = '';
                }
            }
        },

        submitForm(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            const loadingElem = document.getElementById('loading-overlay');
            if (loadingElem) loadingElem.style.display = 'none';

            const form = event.target;
            const fileInput = form.querySelector('input[name="project_file"]');
            if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                this.errorMessage = 'Silakan pilih file archive project (.ZIP) terlebih dahulu.';
                return;
            }

            const file = fileInput.files[0];
            const maxBytes = this.maxMb * 1024 * 1024;
            if (file.size > maxBytes) {
                const actualMb = (file.size / (1024 * 1024)).toFixed(2);
                this.errorMessage = 'Ukuran file (' + actualMb + ' MB) melebihi batas maksimal tugas (' + this.maxMb + ' MB).';
                return;
            }

            const titleInput = form.querySelector('input[name="title"]');
            if (!titleInput || !titleInput.value.trim()) {
                this.errorMessage = 'Judul project wajib diisi.';
                return;
            }

            this.isUploading = true;
            this.progress = 0;
            this.errorMessage = '';
            this.statusText = 'Memulai proses upload...';
            this.loadedText = '0 MB / ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB';

            const formData = new FormData(form);
            const xhr = new XMLHttpRequest();

            xhr.open('POST', form.action, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');

            const token = form.querySelector('input[name="_token"]')?.value;
            if (token) {
                xhr.setRequestHeader('X-CSRF-TOKEN', token);
            }

            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    const percent = Math.min(Math.round((e.loaded / e.total) * 100), 100);
                    this.progress = percent;
                    const loadedMb = (e.loaded / (1024 * 1024)).toFixed(2);
                    const totalMb = (e.total / (1024 * 1024)).toFixed(2);
                    this.loadedText = loadedMb + ' MB / ' + totalMb + ' MB';

                    if (percent < 100) {
                        this.statusText = 'Mengunggah file (' + percent + '%)...';
                    } else {
                        this.statusText = 'File 100% terunggah! Mengekstrak & memvalidasi struktur proyek di server...';
                    }
                }
            };

            xhr.onload = () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.redirect) {
                            this.progress = 100;
                            this.statusText = 'Validasi berhasil! Mengalihkan ke halaman pratinjau...';
                            window.location.href = response.redirect;
                            return;
                        }
                    } catch (err) {
                        window.location.reload();
                        return;
                    }
                    window.location.reload();
                } else {
                    this.isUploading = false;
                    let msg = 'Terjadi kesalahan saat mengunggah (' + xhr.status + ').';
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.errors && res.errors.project_file) {
                            msg = Array.isArray(res.errors.project_file) ? res.errors.project_file[0] : res.errors.project_file;
                        } else if (res.errors && res.errors.title) {
                            msg = Array.isArray(res.errors.title) ? res.errors.title[0] : res.errors.title;
                        } else if (res.message) {
                            msg = res.message;
                        }
                    } catch (e) {
                        if (xhr.status === 413) {
                            msg = 'Ukuran file terlalu besar untuk server hosting (HTTP 413 Payload Too Large). Periksa konfigurasi post_max_size di cPanel.';
                        } else if (xhr.status === 504 || xhr.status === 408) {
                            msg = 'Waktu upload habis (Gateway Timeout). Koneksi internet lambat atau file terlalu besar.';
                        }
                    }
                    this.errorMessage = msg;
                }
            };

            xhr.onerror = () => {
                this.isUploading = false;
                const loadingElem = document.getElementById('loading-overlay');
                if (loadingElem) loadingElem.style.display = 'none';
                this.errorMessage = 'Koneksi ke server terputus saat upload. Silakan periksa jaringan internet Anda dan coba lagi.';
            };

            xhr.ontimeout = () => {
                this.isUploading = false;
                const loadingElem = document.getElementById('loading-overlay');
                if (loadingElem) loadingElem.style.display = 'none';
                this.errorMessage = 'Waktu koneksi habis saat upload. Silakan coba kembali.';
            };

            xhr.send(formData);
        }
    };
}
</script>
@endsection
