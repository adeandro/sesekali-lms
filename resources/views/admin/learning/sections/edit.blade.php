@extends('layouts.app')

@section('title', 'Edit Bab: ' . $section->title . ' - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Edit Bab Materi')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">

<div class="max-w-4xl mx-auto space-y-8 animate-fadeIn pb-12" x-data="{ 
    type: '{{ old('type', $section->type) }}',
    confirmDeleteFile() {
        Swal.fire({
            title: 'Hapus Berkas?',
            text: 'Tindakan ini hanya akan menghapus berkas lampiran, bab materi tetap ada.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#6366f1',
            confirmButtonText: 'Ya, Hapus Berkas!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-[2.5rem]',
                confirmButton: 'rounded-xl font-black uppercase text-[10px] tracking-widest px-6 py-3',
                cancelButton: 'rounded-xl font-black uppercase text-[10px] tracking-widest px-6 py-3'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('admin.learning.sections.delete-file', $section) }}';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                
                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
}">
    <!-- Header -->
    <div class="flex flex-col gap-2">
        <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
            <a href="{{ route('admin.learning.materials.index') }}" class="hover:text-indigo-600 transition-colors">Daftar Materi</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <a href="{{ route('admin.learning.materials.show', $material) }}" class="hover:text-indigo-600 transition-colors">Detail Materi</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-indigo-600">Edit Bab</span>
        </nav>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-100">
                <i class="fas fa-edit text-xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Edit Bab Materi</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Materi: {{ $material->title }}</p>
            </div>
        </div>
    </div>

    {{-- Toast Notification --}}
    @if(session('success'))
    <div id="toast-success" class="flex items-center gap-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-6 py-4 shadow-sm animate-fadeIn">
        <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center text-white shrink-0">
            <i class="fas fa-check text-xs"></i>
        </div>
        <p class="text-[11px] font-black uppercase tracking-widest">{{ session('success') }}</p>
        <button onclick="document.getElementById('toast-success').remove()" class="ml-auto text-emerald-400 hover:text-emerald-600 transition-colors">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
    @endif
    @if(session('error'))
    <div id="toast-error" class="flex items-center gap-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl px-6 py-4 shadow-sm">
        <div class="w-8 h-8 rounded-xl bg-rose-500 flex items-center justify-center text-white shrink-0">
            <i class="fas fa-exclamation text-xs"></i>
        </div>
        <p class="text-[11px] font-black uppercase tracking-widest">{{ session('error') }}</p>
        <button onclick="document.getElementById('toast-error').remove()" class="ml-auto text-rose-400 hover:text-rose-600 transition-colors">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
    @endif

    <form action="{{ route('admin.learning.sections.update', $section) }}" method="POST" enctype="multipart/form-data" id="sectionForm" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-stream text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Detail Bab</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Judul, tipe konten, dan isi materi</p>
                </div>
            </div>
            
            <div class="p-8 space-y-8">
                <!-- Title -->
                <div class="space-y-2">
                    <label for="title" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Judul Bab <span class="text-rose-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title', $section->title) }}" 
                        class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-gray-300 @error('title') ring-2 ring-rose-500 @enderror" 
                        placeholder="Contoh: Pendahuluan dan Instalasi" required>
                    @error('title')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                </div>

                <!-- Type Selector -->
                <div class="space-y-4">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tipe Konten <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="cursor-pointer group/type">
                            <input type="radio" name="type" value="text" x-model="type" class="hidden peer">
                            <div class="p-6 bg-gray-50 rounded-[2rem] border-2 border-transparent peer-checked:border-indigo-500 peer-checked:bg-white transition-all text-center group-hover/type:bg-white">
                                <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center mx-auto mb-3 transition-transform group-hover/type:scale-110">
                                    <i class="fas fa-align-left"></i>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 peer-checked:text-indigo-600 transition-colors">Teks / Artikel</span>
                            </div>
                        </label>
                        <label class="cursor-pointer group/type">
                            <input type="radio" name="type" value="video" x-model="type" class="hidden peer">
                            <div class="p-6 bg-gray-50 rounded-[2rem] border-2 border-transparent peer-checked:border-rose-500 peer-checked:bg-white transition-all text-center group-hover/type:bg-white">
                                <div class="w-10 h-10 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center mx-auto mb-3 transition-transform group-hover/type:scale-110">
                                    <i class="fab fa-youtube"></i>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 peer-checked:text-rose-600 transition-colors">Embed Video</span>
                            </div>
                        </label>
                        <label class="cursor-pointer group/type">
                            <input type="radio" name="type" value="file" x-model="type" class="hidden peer">
                            <div class="p-6 bg-gray-50 rounded-[2rem] border-2 border-transparent peer-checked:border-amber-500 peer-checked:bg-white transition-all text-center group-hover/type:bg-white">
                                <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center mx-auto mb-3 transition-transform group-hover/type:scale-110">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 peer-checked:text-amber-600 transition-colors">File Unduhan</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Content Area: TEXT -->
                <div class="space-y-4" x-show="type === 'text'" x-transition>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Isi Materi <span class="text-rose-500">*</span></label>
                    <textarea id="content" name="content" class="hidden">{{ old('content', $section->content) }}</textarea>
                    <div id="quill-editor" class="bg-gray-50 rounded-[2rem] overflow-hidden" style="min-height: 400px;"></div>
                </div>

                <!-- Content Area: VIDEO -->
                <div class="space-y-4" x-show="type === 'video'" x-transition>
                    <label for="video_url" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">URL Video (YouTube / GDrive) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-gray-300">
                            <i class="fab fa-youtube"></i>
                        </div>
                        <input type="url" id="video_url" name="video_url" value="{{ old('video_url', $section->video_url) }}" 
                            class="w-full h-14 bg-gray-50 border-transparent rounded-2xl pl-14 pr-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-rose-500/10 transition-all placeholder:text-gray-300" 
                            placeholder="https://www.youtube.com/watch?v=..." :required="type === 'video'">
                    </div>
                </div>

                <!-- Content Area: FILE -->
                <div class="space-y-4" x-show="type === 'file'" x-transition>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Unggah Berkas Materi (PDF, DOCX, ZIP)</label>
                    
                    @if($section->file_path)
                        <div class="p-4 bg-amber-50 border border-amber-100 rounded-2xl flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-file-alt text-amber-500"></i>
                                <span class="text-[10px] font-black text-amber-900 uppercase">Berkas Saat Ini: {{ basename($section->file_path) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ asset('storage/' . $section->file_path) }}" target="_blank" class="text-[9px] font-black text-indigo-600 uppercase hover:underline">Lihat Berkas</a>
                                <span class="text-gray-200">|</span>
                                <button type="button" @click="confirmDeleteFile()" class="text-[9px] font-black text-rose-500 uppercase hover:underline">Hapus Berkas</button>
                            </div>
                        </div>
                    @endif

                    <div class="relative group/upload h-32 bg-gray-50 rounded-[2rem] border-2 border-dashed border-gray-200 flex flex-col items-center justify-center hover:bg-amber-50 hover:border-amber-200 transition-all cursor-pointer"
                         onclick="document.getElementById('file_path').click()">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-gray-400 shadow-sm group-hover/upload:text-amber-600 transition-colors">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-900 uppercase tracking-widest" id="file-name-label">Pilih Berkas Baru (Opsional)</p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter mt-1">Maksimal 10MB</p>
                            </div>
                        </div>
                        <input type="file" id="file_path" name="file_path" class="hidden" @change="document.getElementById('file-name-label').innerText = $event.target.files[0].name">
                    </div>
                </div>

                <!-- Order -->
                <input type="hidden" name="order" value="{{ $section->order }}">
            </div>
        </div>

        <div class="flex flex-col-reverse md:flex-row gap-4">
            <a href="{{ route('admin.learning.materials.show', $material) }}" class="flex-1 h-14 bg-gray-100 text-gray-500 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-200 transition flex items-center justify-center">
                Batal & Kembali
            </a>
            <button type="submit" class="flex-[3] h-14 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center justify-center gap-3">
                <i class="fas fa-save text-[10px]"></i> Perbarui Bab Materi
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quill = new Quill('#quill-editor', {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'script': 'sub'}, { 'script': 'super' }],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }],
                        ['link', 'image', 'clean']
                    ],
                    handlers: {
                        image: imageHandler
                    }
                }
            }
        });

        function imageHandler() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = async () => {
                const file = input.files[0];
                const formData = new FormData();
                formData.append('image', file);

                // Show loading state
                Swal.fire({
                    title: 'Mengunggah Gambar...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch("{{ route('admin.learning.sections.upload-image') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (result.url) {
                        const range = quill.getSelection();
                        quill.insertEmbed(range.index, 'image', result.url);
                        Swal.close();
                    } else {
                        throw new Error(result.error || 'Gagal mengunggah gambar');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Unggah Gagal',
                        text: error.message
                    });
                }
            };
        }

        const textarea = document.getElementById('content');
        if (textarea.value) {
            quill.clipboard.dangerouslyPasteHTML(textarea.value);
        }

        quill.on('text-change', function() {
            textarea.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
        });

        document.getElementById('sectionForm').addEventListener('submit', function(e) {
            if (document.querySelector('input[name="type"]:checked').value === 'text') {
                if (!textarea.value || textarea.value.trim() === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Konten teks tidak boleh kosong!',
                        customClass: { popup: 'rounded-[2rem]' }
                    });
                }
            }
        });
    });
</script>

<style>
    .ql-toolbar.ql-snow { border: none; border-bottom: 1px solid #f3f4f6; background: white; padding: 12px; }
    .ql-container.ql-snow { border: none; font-size: 1rem; }
    .ql-editor { padding: 2rem; min-height: 350px; }
    .ql-editor img { max-width: 100%; border-radius: 1rem; margin: 1rem 0; }

    /* List & Indentation Styles in Editor
       CATATAN: Quill sudah punya counter CSS sendiri via quill.snow.css.
       Di sini hanya override padding/indentation saja, TIDAK counter,
       agar tidak konflik dengan sistem counter bawaan Quill. */
    .ql-editor ol,
    .ql-editor ul {
        padding-left: 1.5em;
    }
    /* Indent levels (Quill uses ql-indent-1 through ql-indent-8) */
    .ql-editor .ql-indent-1:not(.ql-direction-rtl) { padding-left: 3em; }
    .ql-editor .ql-indent-2:not(.ql-direction-rtl) { padding-left: 6em; }
    .ql-editor .ql-indent-3:not(.ql-direction-rtl) { padding-left: 9em; }
    .ql-editor .ql-indent-4:not(.ql-direction-rtl) { padding-left: 12em; }
    .ql-editor .ql-indent-5:not(.ql-direction-rtl) { padding-left: 15em; }
    .ql-editor .ql-indent-6:not(.ql-direction-rtl) { padding-left: 18em; }
    .ql-editor .ql-indent-7:not(.ql-direction-rtl) { padding-left: 21em; }
    .ql-editor .ql-indent-8:not(.ql-direction-rtl) { padding-left: 24em; }
</style>
@endsection
