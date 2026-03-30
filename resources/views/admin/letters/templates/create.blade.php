@extends('layouts.app')

@section('title', 'Tambah Template - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Tambah Template Surat')

@section('content')
{{-- Quill CSS --}}
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">

<div class="max-w-5xl mx-auto space-y-8 animate-fadeIn pb-20">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col gap-4">
        <nav class="flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">
            <a href="{{ route('admin.letters.templates.index') }}" class="hover:text-indigo-600 transition-colors">Template Surat</a>
            <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
            <span class="text-indigo-600">Tambah Baru</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                    <i class="fas fa-plus text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Buat Template</h2>
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Definisikan format surat baru ke dalam sistem</p>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.letters.templates.store') }}" method="POST" id="templateForm" class="space-y-8 relative">
        @csrf

        <!-- Section 1: Informasi Dasar -->
        <div class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-8 md:p-12 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50/30 rounded-full -mr-32 -mt-32 blur-3xl group-hover:bg-indigo-100/40 transition-colors duration-700"></div>
            
            <div class="flex items-center gap-5 mb-10 relative">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-info-circle text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900 tracking-tight uppercase">Informasi Dasar</h3>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Identitas dan kategori template surat</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative">
                <div class="space-y-3">
                    <label for="name" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nama Template <span class="text-rose-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all placeholder:text-gray-300 @error('name') ring-2 ring-rose-500 @enderror" placeholder="Contoh: Surat Keterangan Siswa Aktif" required>
                    @error('name')<p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase italic tracking-tighter">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-3">
                    <label for="code" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Kode Template <span class="text-rose-500">*</span></label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}" class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all placeholder:text-gray-300 @error('code') ring-2 ring-rose-500 @enderror" placeholder="Contoh: SK-SISWA" required>
                    @error('code')<p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase italic tracking-tighter">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-3">
                    <label for="category" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Kategori <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select id="category" name="category" class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold appearance-none cursor-pointer @error('category') ring-2 ring-rose-500 @enderror" required>
                            <option value="siswa" {{ old('category') == 'siswa' ? 'selected' : '' }}>Administrasi Siswa</option>
                            <option value="guru" {{ old('category') == 'guru' ? 'selected' : '' }}>Administrasi Guru</option>
                            <option value="umum" {{ old('category') == 'umum' ? 'selected' : '' }}>Umum / Lainnya</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none text-[10px]"></i>
                    </div>
                    @error('category')<p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase italic tracking-tighter">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-3">
                    <label for="sort_order" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Urutan Tampilan</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all @error('sort_order') ring-2 ring-rose-500 @enderror">
                    @error('sort_order')<p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase italic tracking-tighter">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <!-- Section 2: Editor Body Surat -->
        <div class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-8 md:p-12 relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-64 h-64 bg-amber-50/30 rounded-full -ml-32 -mt-32 blur-3xl group-hover:bg-amber-100/40 transition-colors duration-700"></div>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 relative">
                <div class="flex items-center gap-5">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                        <i class="fas fa-file-signature text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900 tracking-tight uppercase">Konten Surat</h3>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Gunakan placeholder untuk data dinamis</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    {{-- Siswa --}}
                    <span class="text-[8px] font-black text-gray-300 uppercase tracking-widest self-center">Siswa:</span>
                    @foreach([
                        '[nama_siswa]' => 'Nama Siswa',
                        '[nis]' => 'NIS',
                        '[nisn]' => 'NISN',
                        '[kelas]' => 'Kelas',
                        '[jenis_kelamin]' => 'Jenis Kelamin',
                        '[tempat_lahir]' => 'Tempat Lahir',
                        '[tanggal_lahir]' => 'Tgl Lahir',
                    ] as $placeholder => $label)
                        <button type="button" 
                                onclick="insertPlaceholder('{{ $placeholder }}')" 
                                class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 
                                       text-indigo-600 rounded-lg text-[9px] font-black 
                                       uppercase tracking-widest transition-colors shadow-sm">
                            {{ $label }}
                        </button>
                    @endforeach

                    <span class="w-full"></span>

                    {{-- Surat & Sekolah --}}
                    <span class="text-[8px] font-black text-gray-300 uppercase tracking-widest self-center">Surat:</span>
                    @foreach([
                        '[nomor_surat]' => 'No. Surat',
                        '[tanggal_surat]' => 'Tgl Surat',
                        '[tahun_ajaran]' => 'Tahun Ajaran',
                        '[nama_sekolah]' => 'Nama Sekolah',
                        '[alamat_sekolah]' => 'Alamat',
                        '[nama_kepsek]' => 'Nama Kepsek',
                        '[nip_kepsek]' => 'NIP Kepsek',
                    ] as $placeholder => $label)
                        <button type="button" 
                                onclick="insertPlaceholder('{{ $placeholder }}')" 
                                class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 
                                       text-amber-600 rounded-lg text-[9px] font-black 
                                       uppercase tracking-widest transition-colors shadow-sm">
                            {{ $label }}
                        </button>
                    @endforeach

                    <span class="w-full"></span>

                    {{-- Guru --}}
                    <span class="text-[8px] font-black text-gray-300 uppercase tracking-widest self-center">Guru:</span>
                    @foreach([
                        '[nama_guru]' => 'Nama Guru',
                        '[nip_guru]' => 'NIP Guru',
                        '[jabatan_guru]' => 'Jabatan',
                    ] as $placeholder => $label)
                        <button type="button" 
                                onclick="insertPlaceholder('{{ $placeholder }}')" 
                                class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 
                                       text-emerald-600 rounded-lg text-[9px] font-black 
                                       uppercase tracking-widest transition-colors shadow-sm">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6 relative">
                <div class="space-y-3">
                    {{-- Hidden textarea untuk menyimpan value ke form --}}
                    <textarea id="body" name="body" class="hidden">{{ old('body') }}</textarea>

                    {{-- Quill editor container --}}
                    <div id="quill-editor" 
                         class="bg-gray-50 rounded-[2rem] @error('body') ring-2 ring-rose-500 @enderror"
                         style="min-height: 500px; font-size: 12pt; font-family: Arial, sans-serif;">
                    </div>
                    @error('body')<p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase italic tracking-tighter">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-10 border-t border-gray-50">
                    <a href="{{ route('admin.letters.templates.index') }}" class="h-16 bg-gray-50 text-gray-400 text-[11px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-100 hover:text-gray-500 transition-all flex items-center justify-center">
                        Batal
                    </a>
                    <button type="submit" class="h-16 bg-indigo-600 text-white text-[11px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-2xl shadow-indigo-500/20 flex items-center justify-center gap-4 group/submit">
                        <i class="fas fa-save group-hover:rotate-12 transition-transform"></i> Simpan Template
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Vite bundled Quill & ImageResize --}}
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

<script>
    // ── Daftarkan custom font ──────────────────────────────────────
    const Font = Quill.import('formats/font');
    Font.whitelist = [
        false,              // default (sans-serif)
        'arial',
        'times-new-roman', 
        'calibri',
        'georgia',
        'verdana',
        'courier-new',
        'amiri'
    ];
    Quill.register(Font, true);

    // ── Inisialisasi Quill ─────────────────────────────────────────
    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Tuliskan isi template surat di sini...',
        modules: {
            toolbar: {
                container: [
                    [{ 'font': [false, 'arial', 'times-new-roman', 'calibri', 'georgia', 'verdana', 'courier-new', 'amiri'] }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    [{ 'indent': '-1' }, { 'indent': '+1' }],
                    ['link', 'image', 'clean'],
                ],
                handlers: {
                    // Custom image handler — upload via file input
                    image: imageHandler
                }
            },
            /* 
            imageResize: {
                parchment: Quill.import('parchment'),
                modules: ['Resize', 'DisplaySize', 'Toolbar'],
            },
            */
        }
    });

    // ── Image Handler ──────────────────────────────────────────────
    function imageHandler() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = function() {
            const file = input.files[0];
            if (!file) return;

            // Batasi ukuran file max 2MB
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran gambar maksimal 2MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const base64 = e.target.result;
                const range = quill.getSelection(true);
                quill.insertEmbed(range.index, 'image', base64);
                quill.setSelection(range.index + 1);
                // Sync ke textarea
                document.getElementById('body').value = quill.root.innerHTML;
            };
            reader.readAsDataURL(file);
        };
    }

    // ── Load existing content ──────────────────────────────────────
    const existingContent = document.getElementById('body').value;
    if (existingContent) {
        quill.clipboard.dangerouslyPasteHTML(existingContent);
    }

    // ── Sync ke textarea saat ada perubahan ───────────────────────
    quill.on('text-change', function() {
        document.getElementById('body').value = quill.root.innerHTML;
    });

    // ── Deteksi RTL otomatis ──────────────────────────────────────
    function isArabicText(text) {
        const arabicRegex = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF]/;
        return arabicRegex.test(text);
    }

    function applyDirectionToLine(quillInstance, index) {
        const [line] = quillInstance.getLine(index);
        if (!line) return;

        const lineText = line.domNode?.textContent ?? '';
        const isArabic = isArabicText(lineText);

        const lineIndex = quillInstance.getIndex(line);
        const lineLength = line.length();

        if (isArabic) {
            quillInstance.formatLine(lineIndex, lineLength, {
                'direction': 'rtl',
                'align': 'right'
            }, 'user');
        }
    }

    function addRtlListener(quillInstance) {
        quillInstance.on('text-change', function(delta, oldDelta, source) {
            if (source !== 'user') return;
            const selection = quillInstance.getSelection();
            if (!selection) return;
            applyDirectionToLine(quillInstance, selection.index);
        });
    }

    // Pasang RTL listener ke editor template
    addRtlListener(quill);

    // ── Validasi & sync sebelum submit ────────────────────────────
    document.getElementById('templateForm').addEventListener('submit', function(e) {
        const content = quill.root.innerHTML;
        if (content === '<p><br></p>' || content.trim() === '') {
            e.preventDefault();
            alert('Konten surat tidak boleh kosong.');
            return false;
        }
        document.getElementById('body').value = content;
    });

    // ── Insert placeholder ke kursor ──────────────────────────────
    function insertPlaceholder(placeholder) {
        const range = quill.getSelection(true);
        if (range) {
            quill.insertText(range.index, placeholder, {
                'color': '#4F46E5',
                'background': '#EEF2FF',
                'code': true,
            });
            quill.setSelection(range.index + placeholder.length);
        } else {
            const length = quill.getLength();
            quill.insertText(length - 1, placeholder, {
                'color': '#4F46E5',
                'background': '#EEF2FF',
            });
        }
        document.getElementById('body').value = quill.root.innerHTML;
    }
</script>

<style>
    /* Override Quill styling agar konsisten dengan UI */
    #quill-editor .ql-editor {
        min-height: 480px;
        font-family: Arial, sans-serif;
        font-size: 12pt;
        line-height: 1.8;
        padding: 24px 32px;
        color: #1f2937;
    }
    #quill-editor .ql-editor p {
        margin-bottom: 8px;
    }
    .ql-toolbar.ql-snow {
        border: none;
        border-bottom: 1px solid #f3f4f6;
        border-radius: 2rem 2rem 0 0;
        background: white;
        padding: 12px 16px;
    }
    .ql-container.ql-snow {
        border: none;
        border-radius: 0 0 2rem 2rem;
    }
    /* Placeholder styling di dalam editor */
    .ql-editor .placeholder-tag {
        background: #EEF2FF;
        color: #4F46E5;
        padding: 1px 4px;
        border-radius: 3px;
        font-family: monospace;
    }

    /* ── Custom font mapping ── */
    .ql-editor .ql-font-arial { font-family: Arial, sans-serif !important; }
    .ql-editor .ql-font-times-new-roman { font-family: 'Times New Roman', serif !important; }
    .ql-editor .ql-font-calibri { font-family: Calibri, sans-serif !important; }
    .ql-editor .ql-font-georgia { font-family: Georgia, serif !important; }
    .ql-editor .ql-font-verdana { font-family: Verdana, sans-serif !important; }
    .ql-editor .ql-editor .ql-font-courier-new { font-family: 'Courier New', monospace !important; }

    /* ── Quill image resize ── */
    .ql-editor img {
        cursor: pointer;
        max-width: 100%;
    }
    .ql-editor img.ql-selected {
        outline: 3px solid #4f46e5;
    }
    /* Override resize handle styling */
    .image-resizer-overlay {
        border: 2px dashed #4f46e5 !important;
    }
    .image-resizer-handle {
        background: #4f46e5 !important;
        border: 2px solid white !important;
        border-radius: 50% !important;
        width: 12px !important;
        height: 12px !important;
    }
    .image-toolbar {
        background: #4f46e5 !important;
        border-radius: 8px !important;
        padding: 4px 8px !important;
    }
    .image-toolbar button {
        color: white !important;
        font-size: 10px !important;
        font-weight: bold !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }
    /* ── Self-hosted font Amiri ── */
    @font-face {
        font-family: 'Amiri';
        src: url('/fonts/Amiri-Regular.ttf') format('truetype');
        font-weight: 400;
        font-style: normal;
    }
    @font-face {
        font-family: 'Amiri';
        src: url('/fonts/Amiri-Bold.ttf') format('truetype');
        font-weight: 700;
        font-style: normal;
    }
    @font-face {
        font-family: 'Amiri';
        src: url('/fonts/Amiri-Italic.ttf') format('truetype');
        font-weight: 400;
        font-style: italic;
    }
    @font-face {
        font-family: 'Amiri';
        src: url('/fonts/Amiri-BoldItalic.ttf') format('truetype');
        font-weight: 700;
        font-style: italic;
    }

    /* ── Mapping class Quill ke font Amiri ── */
    .ql-font-amiri,
    .ql-font-amiri * {
        font-family: 'Amiri', serif !important;
        font-size: 18pt !important;
        line-height: 2 !important;
    }

    /* ── RTL support di editor ── */
    .ql-editor [dir="rtl"],
    .ql-font-amiri {
        text-align: right;
        direction: rtl;
        font-family: 'Amiri', serif !important;
        font-size: 18pt;
        line-height: 2;
    }

    /* ── Label font di toolbar dropdown (Fix) ── */
    .ql-snow .ql-picker.ql-font .ql-picker-label::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item::before { content: 'Sans Serif'; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="arial"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="arial"]::before { content: 'Arial'; font-family: Arial, sans-serif; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="times-new-roman"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="times-new-roman"]::before { content: 'Times New Roman'; font-family: 'Times New Roman', serif; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="calibri"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="calibri"]::before { content: 'Calibri'; font-family: Calibri, sans-serif; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="georgia"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="georgia"]::before { content: 'Georgia'; font-family: Georgia, serif; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="verdana"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="verdana"]::before { content: 'Verdana'; font-family: Verdana, sans-serif; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="courier-new"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="courier-new"]::before { content: 'Courier New'; font-family: 'Courier New', monospace; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="amiri"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="amiri"]::before { content: 'Amiri (Arab)' !important; font-family: 'Amiri', serif !important; }
    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="amiri"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="amiri"]::before { content: 'Amiri (Arab)'; font-family: 'Amiri'; }

    /* ── RTL support di editor ── */
    .ql-editor [dir="rtl"],
    .ql-font-amiri {
        text-align: right;
        direction: rtl;
        font-family: 'Amiri', serif !important;
        font-size: 18pt;
        line-height: 2;
    }

    /* ── Font picker label di toolbar ── */
    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="arial"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="arial"]::before { content: 'Arial'; font-family: Arial; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="times-new-roman"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="times-new-roman"]::before { content: 'Times New Roman'; font-family: 'Times New Roman'; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="calibri"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="calibri"]::before { content: 'Calibri'; font-family: Calibri; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="georgia"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="georgia"]::before { content: 'Georgia'; font-family: Georgia; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="verdana"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="verdana"]::before { content: 'Verdana'; font-family: Verdana; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="courier-new"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="courier-new"]::before { content: 'Courier New'; font-family: 'Courier New'; }

    /* ── Gambar di dalam editor ── */
    .ql-editor img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 8px 0;
        border-radius: 4px;
    }
    
    .animate-fadeIn { animation: fadeIn 0.5s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
