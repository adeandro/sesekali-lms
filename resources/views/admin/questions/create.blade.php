@extends('layouts.app')

@section('title', 'Tambah Soal - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Tambah Soal Baru')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">

<div class="max-w-5xl mx-auto space-y-8 animate-fadeIn pb-20">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col gap-4">
        <nav class="flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">
            <a href="{{ route('admin.questions.index') }}" class="hover:text-indigo-600 transition-colors">Bank Soal</a>
            <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
            <span class="text-indigo-600">Tambah Baru</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                    <i class="fas fa-plus text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Tambah Soal</h2>
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Buat butir soal baru untuk bank soal sistem</p>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.questions.store') }}" method="POST" id="questionForm" enctype="multipart/form-data" class="space-y-8 relative">
        @csrf

        <!-- Form Loading Overlay -->
        <div id="loadingOverlay" class="absolute inset-0 bg-white/80 backdrop-blur-md z-50 hidden flex-col items-center justify-center rounded-[3rem]">
            <div class="w-20 h-20 border-4 border-indigo-50 border-t-indigo-600 rounded-full animate-spin mb-6"></div>
            <p class="text-[11px] font-black text-indigo-600 uppercase tracking-[0.3em] animate-pulse">Memproses Data Soal...</p>
        </div>

        <!-- Section 1: Klasifikasi -->
        <div class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-8 md:p-12 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50/30 rounded-full -mr-32 -mt-32 blur-3xl group-hover:bg-indigo-100/40 transition-colors duration-700"></div>
            
            <div class="flex items-center gap-5 mb-10 relative">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-tags text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900 tracking-tight uppercase">Klasifikasi Soal</h3>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Tentukan kategori dan level kesulitan</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 relative">
                <div class="space-y-3">
                    <label for="subject_id" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select id="subject_id" name="subject_id" class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold appearance-none cursor-pointer @error('subject_id') ring-2 ring-rose-500 @enderror" required>
                            <option value="">Pilih Mapel</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none text-[10px]"></i>
                    </div>
                    @error('subject_id')<p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase italic tracking-tighter">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-3">
                    <label for="jenjang" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Kelas <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select id="jenjang" name="jenjang" class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold appearance-none cursor-pointer @error('jenjang') ring-2 ring-rose-500 @enderror" required>
                            <option value="">Pilih Kelas</option>
                            <option value="10" {{ old('jenjang') == '10' ? 'selected' : '' }}>Kelas 10</option>
                            <option value="11" {{ old('jenjang') == '11' ? 'selected' : '' }}>Kelas 11</option>
                            <option value="12" {{ old('jenjang') == '12' ? 'selected' : '' }}>Kelas 12</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none text-[10px]"></i>
                    </div>
                    @error('jenjang')<p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase italic tracking-tighter">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-3">
                    <label for="difficulty_level" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tingkat Kesulitan <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select id="difficulty_level" name="difficulty_level" class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold appearance-none cursor-pointer @error('difficulty_level') ring-2 ring-rose-500 @enderror" required>
                            <option value="">Pilih Kesulitan</option>
                            <option value="easy" {{ old('difficulty_level') == 'easy' ? 'selected' : '' }}>Mudah (Easy)</option>
                            <option value="medium" {{ old('difficulty_level') == 'medium' ? 'selected' : '' }}>Sedang (Medium)</option>
                            <option value="hard" {{ old('difficulty_level') == 'hard' ? 'selected' : '' }}>Sulit (Hard)</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none text-[10px]"></i>
                    </div>
                    @error('difficulty_level')<p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase italic tracking-tighter">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2 space-y-3">
                    <label for="topic" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Topik / Sub-Bab <span class="text-rose-500">*</span></label>
                    <input type="text" id="topic" name="topic" value="{{ old('topic') }}" class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all placeholder:text-gray-300 @error('topic') ring-2 ring-rose-500 @enderror" placeholder="Contoh: Turunan Fungsi Trigonometri" required>
                    @error('topic')<p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase italic tracking-tighter">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-3">
                    <label for="question_type" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tipe Soal <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select id="question_type" name="question_type" class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold appearance-none cursor-pointer @error('question_type') ring-2 ring-rose-500 @enderror" required>
                            <option value="multiple_choice" {{ old('question_type') == 'multiple_choice' ? 'selected' : '' }}>Pilihan Ganda</option>
                            <option value="essay" {{ old('question_type') == 'essay' ? 'selected' : '' }}>Esai / Uraian</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none text-[10px]"></i>
                    </div>
                    @error('question_type')<p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase italic tracking-tighter">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <!-- Section 2: Konten Soal -->
        <div class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-8 md:p-12 relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-64 h-64 bg-amber-50/30 rounded-full -ml-32 -mt-32 blur-3xl group-hover:bg-amber-100/40 transition-colors duration-700"></div>
            
            <div class="flex items-center gap-5 mb-10 relative">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <i class="fas fa-pen-nib text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900 tracking-tight uppercase">Konten Soal</h3>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Pertanyaan utama dan lampiran visual</p>
                </div>
            </div>

            <div class="space-y-3">
                <label class="block text-[10px] font-black text-gray-400 
                              uppercase tracking-widest ml-1">
                    Butir Pertanyaan <span class="text-rose-500">*</span>
                </label>
                {{-- Hidden textarea untuk form submit --}}
                <textarea id="question_text" name="question_text" 
                          class="hidden">{{ old('question_text') }}</textarea>
                {{-- Quill editor --}}
                <div id="quill-question" 
                     class="bg-gray-50 rounded-[2rem] @error('question_text') ring-2 ring-rose-500 @enderror"
                     style="min-height: 200px;">
                </div>
                @error('question_text')
                    <p class="text-rose-500 text-[10px] font-black mt-2 ml-1 
                              uppercase italic tracking-tighter">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Section 3: Pilihan Jawaban (JS Controlled) -->
        <div id="multipleChoiceSection" class="animate-fadeIn hidden">
            <div class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-8 md:p-12 relative overflow-hidden group">
                <div class="absolute bottom-0 right-0 w-80 h-80 bg-emerald-50/20 rounded-full -mr-40 -mb-40 blur-3xl group-hover:bg-emerald-100/30 transition-colors duration-700"></div>

                <div class="flex items-center gap-5 mb-10 relative">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-list-check text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900 tracking-tight uppercase">Pilihan Jawaban</h3>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Tentukan opsi dan tandai jawaban yang benar</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 relative mb-20 overflow-visible">
                    @foreach(['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d', 'E' => 'option_e'] as $label => $name)
                        <div class="p-8 bg-gray-50 rounded-[2.5rem] border border-gray-100 space-y-6 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 group/opt relative hover:z-50 focus-within:z-50 min-h-[300px] flex flex-col">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <span class="w-10 h-10 flex items-center justify-center bg-indigo-600 text-white rounded-[1.25rem] text-sm font-black shadow-lg shadow-indigo-100 group-hover/opt:scale-110 transition-transform">
                                        {{ $label }}
                                    </span>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mt-1">Opsi {{ $label }} {{ $label !== 'E' ? '*' : '' }}</label>
                                </div>
                            </div>
                            
                            {{-- Hidden textarea untuk form submit --}}
                            <textarea id="{{ $name }}" name="{{ $name }}" 
                                      class="hidden">{{ old($name) }}</textarea>
                            {{-- Quill editor untuk opsi --}}
                            <div id="quill-{{ $name }}" 
                                 class="bg-white rounded-[1.5rem] overflow-hidden flex-1"
                                 style="min-height: 150px;">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-10 bg-indigo-600 rounded-[2.5rem] shadow-2xl shadow-indigo-200 relative overflow-hidden group/key z-50">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-xl group-hover/key:scale-150 transition-transform duration-700"></div>
                    
                    <div class="flex flex-col lg:flex-row items-center justify-between gap-10 relative">
                        <div class="text-white text-center lg:text-left">
                            <h4 class="text-base font-black uppercase tracking-[0.2em] mb-2">Pilih Kunci Jawaban</h4>
                            <p class="text-[10px] font-black opacity-70 uppercase tracking-widest leading-relaxed">Tandai satu opsi sebagai jawaban yang tepat</p>
                        </div>
                        <div class="flex flex-wrap justify-center gap-3">
                            @foreach(['A', 'B', 'C', 'D', 'E'] as $val)
                                <label class="cursor-pointer group/radio">
                                    <input type="radio" name="correct_answer" value="{{ $val }}" class="peer hidden" {{ old('correct_answer') == $val ? 'checked' : '' }}>
                                    <span class="w-16 h-16 flex items-center justify-center bg-indigo-50/50 text-white/50 rounded-[1.5rem] text-xl font-black peer-checked:bg-white peer-checked:text-indigo-600 peer-checked:shadow-2xl transition-all duration-300 border-2 border-transparent peer-checked:border-white hover:text-white uppercase">
                                        {{ $val }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Pembahasan -->
        <div class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-8 md:p-12 relative overflow-hidden group">
            <div class="flex items-center gap-5 mb-10 relative">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-comment-medical text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900 tracking-tight uppercase">Pembahasan Soal</h3>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed italic">Opsional • Penjelasan untuk membantu belajar siswa</p>
                </div>
            </div>

            <div class="space-y-10 relative">
                {{-- Hidden textarea --}}
                <textarea id="explanation" name="explanation" 
                          class="hidden">{{ old('explanation') }}</textarea>
                {{-- Quill editor --}}
                <div id="quill-explanation" 
                     class="bg-gray-50 rounded-[2rem]"
                     style="min-height: 150px;">
                </div>
                
                <div class="flex flex-col-reverse md:flex-row gap-5 pt-10 border-t-2 border-gray-50">
                    <a href="{{ route('admin.questions.index') }}" class="flex-1 h-16 bg-gray-50 text-gray-400 text-[11px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-100 hover:text-gray-500 transition-all flex items-center justify-center">
                        Batal
                    </a>
                    <button type="submit" class="flex-[3] h-16 bg-indigo-600 text-white text-[11px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-2xl shadow-indigo-500/20 flex items-center justify-center gap-4 group/submit">
                        <i class="fas fa-save group-hover:rotate-12 transition-transform"></i> Simpan Butir Soal
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
{{-- Vite bundled Quill & ImageResize --}}
<script>
// ── Daftarkan custom font ──────────────────────────────────────
const Font = Quill.import('formats/font');
Font.whitelist = [
    false, 'arial', 'times-new-roman', 'calibri',
    'georgia', 'verdana', 'courier-new', 'amiri'
];
Quill.register(Font, true);



// ── Toolbar config ─────────────────────────────────────────────
const toolbarFull = [
    [{ 'font': [false, 'arial', 'times-new-roman', 'calibri', 
                'georgia', 'verdana', 'courier-new', 'amiri'] }],
    [{ 'size': ['small', false, 'large', 'huge'] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ 'script': 'sub' }, { 'script': 'super' }],
    [{ 'color': [] }, { 'background': [] }],
    [{ 'align': [] }],
    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
    ['link', 'image', 'clean'],
];

const toolbarMinimal = [
    ['bold', 'italic', 'underline'],
    [{ 'script': 'sub' }, { 'script': 'super' }],
    [{ 'color': [] }],
    ['image', 'clean'],
];

// ── Image Handler ──────────────────────────────────────────────
function createImageHandler(quillInstance) {
    return function() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();
        input.onchange = function() {
            const file = input.files[0];
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran gambar maksimal 2MB.');
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                const range = quillInstance.getSelection(true);
                quillInstance.insertEmbed(range.index, 'image', e.target.result);
                quillInstance.setSelection(range.index + 1);
                syncQuill();
            };
            reader.readAsDataURL(file);
        };
    };
}

// ── Inisialisasi Quill per field ──────────────────────────────
function initQuill(editorId, textareaId, toolbar) {
    const quill = new Quill('#' + editorId, {
        theme: 'snow',
        modules: {
            toolbar: {
                container: toolbar,
            },
        }
    });

    // Set image handler setelah inisialisasi
    quill.getModule('toolbar').addHandler('image', createImageHandler(quill));

    // Load existing content
    const existing = document.getElementById(textareaId).value;
    if (existing) {
        quill.clipboard.dangerouslyPasteHTML(existing);
    }

    // Sync on change
    quill.on('text-change', syncQuill);

    return quill;
}

// ── Init semua editor ─────────────────────────────────────────
const quillQuestion    = initQuill('quill-question', 'question_text', toolbarFull);
const quillOptionA     = initQuill('quill-option_a', 'option_a', toolbarMinimal);
const quillOptionB     = initQuill('quill-option_b', 'option_b', toolbarMinimal);
const quillOptionC     = initQuill('quill-option_c', 'option_c', toolbarMinimal);
const quillOptionD     = initQuill('quill-option_d', 'option_d', toolbarMinimal);
const quillOptionE     = initQuill('quill-option_e', 'option_e', toolbarMinimal);
const quillExplanation = initQuill('quill-explanation', 'explanation', toolbarFull);

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
    } else {
        // Reset ke LTR jika tidak ada karakter Arab
        quillInstance.formatLine(lineIndex, lineLength, {
            'direction': false,
            'align': false
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

// Pasang RTL listener ke semua editor
addRtlListener(quillQuestion);
addRtlListener(quillOptionA);
addRtlListener(quillOptionB);
addRtlListener(quillOptionC);
addRtlListener(quillOptionD);
addRtlListener(quillOptionE);
addRtlListener(quillExplanation);

const allEditors = {
    'question_text': quillQuestion,
    'option_a':      quillOptionA,
    'option_b':      quillOptionB,
    'option_c':      quillOptionC,
    'option_d':      quillOptionD,
    'option_e':      quillOptionE,
    'explanation':   quillExplanation,
};

// ── Sync semua editor ke textarea ─────────────────────────────
function syncQuill() {
    Object.entries(allEditors).forEach(([id, editor]) => {
        const content = editor.root.innerHTML;
        document.getElementById(id).value = 
            (content === '<p><br></p>' || content.trim() === '') 
            ? '' : content;
    });
}

// ── Sync + validasi sebelum submit ────────────────────────────
document.getElementById('questionForm').addEventListener('submit', function(e) {
    syncQuill();

    // Validasi question_text tidak kosong
    const questionContent = quillQuestion.root.innerHTML;
    if (questionContent === '<p><br></p>' || questionContent.trim() === '') {
        e.preventDefault();
        alert('Butir pertanyaan tidak boleh kosong.');
        return false;
    }

    // Tampilkan loading overlay
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    }
});

// ── Toggle multiple choice section ───────────────────────────
const questionTypeSelect = document.getElementById('question_type');
const multipleChoiceSection = document.getElementById('multipleChoiceSection');

function toggleMultipleChoice() {
    if (questionTypeSelect.value === 'multiple_choice') {
        multipleChoiceSection.classList.remove('hidden');
        multipleChoiceSection.classList.add('animate-fadeIn');
    } else {
        multipleChoiceSection.classList.add('hidden');
    }
}

questionTypeSelect.addEventListener('change', toggleMultipleChoice);
window.addEventListener('load', toggleMultipleChoice);
</script>

<style>
    /* ── Quill styling untuk soal ── */
    .ql-toolbar.ql-snow {
        border: none;
        border-bottom: 1px solid #f3f4f6;
        border-radius: 1.5rem 1.5rem 0 0;
        background: white;
        padding: 8px 12px;
    }
    .ql-container.ql-snow {
        border: none;
        border-radius: 0 0 1.5rem 1.5rem;
    }
    #quill-question .ql-editor { min-height: 180px; font-size: 13pt; font-family: Arial, sans-serif; }
    #quill-option_a .ql-editor,
    #quill-option_b .ql-editor,
    #quill-option_c .ql-editor,
    #quill-option_d .ql-editor,
    #quill-option_e .ql-editor { min-height: 150px; font-size: 11pt; font-family: Arial, sans-serif; }
    #quill-explanation .ql-editor { min-height: 120px; font-size: 11pt; font-family: Arial, sans-serif; }
    .ql-editor img { max-width: 100%; height: auto; border-radius: 8px; margin: 4px 0; }

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
    .ql-font-amiri { font-family: 'Amiri', serif !important; }
    .ql-editor .ql-font-amiri { font-family: 'Amiri', serif !important; }
    span.ql-font-amiri { font-family: 'Amiri', serif !important; }

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
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="amiri"]::before { 
        content: 'Amiri (Arab)' !important; 
        font-family: 'Amiri', serif !important; 
    }

    /* Ensure font dropdown is above other cards */
    .ql-snow .ql-picker-options {
        z-index: 1000 !important;
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

    /* ── Animasi ── */
    .animate-fadeIn { animation: fadeIn 0.5s ease-out; }
    @keyframes fadeIn { 
        from { opacity: 0; transform: translateY(20px); } 
        to { opacity: 1; transform: translateY(0); } 
    }

    /* ── Custom font mapping ── */
    .ql-editor .ql-font-arial { font-family: Arial, sans-serif !important; }
    .ql-editor .ql-font-times-new-roman { font-family: 'Times New Roman', serif !important; }
    .ql-editor .ql-font-calibri { font-family: Calibri, sans-serif !important; }
    .ql-editor .ql-font-georgia { font-family: Georgia, serif !important; }
    .ql-editor .ql-font-verdana { font-family: Verdana, sans-serif !important; }
    .ql-editor .ql-editor .ql-font-courier-new { font-family: 'Courier New', monospace !important; }

    /* ── Fix overlap layout opsi jawaban ── */
    #quill-option_a,
    #quill-option_b,
    #quill-option_c,
    #quill-option_d,
    #quill-option_e {
        position: relative;
        z-index: 1;
        overflow: visible;
    }

    /* Pastikan card opsi tidak clip konten Quill */
    .group\/opt {
        overflow: visible !important;
        position: relative;
        z-index: 1;
    }

    /* Pastikan grid tidak clip overlay resize */
    .grid.grid-cols-1.lg\\:grid-cols-2 {
        overflow: visible !important;
    }

    /* Image resize overlay harus di atas semua card */
    .quill-image-resizer,
    .ql-resizer,
    [class*="resizer"] {
        z-index: 9999 !important;
    }

    /* ── Quill image resize legacy styles (optional to keep or remove if conflicts) ── */
    .ql-editor img {
        cursor: pointer;
        max-width: 100%;
    }

    /* ── Fix toolbar Quill overlap di opsi jawaban ── */
    #multipleChoiceSection .grid {
        overflow: visible !important;
    }

    /* Pastikan toolbar Quill berada di atas elemen sekitarnya jika absolute */
    .ql-toolbar.ql-snow {
        position: relative;
        z-index: 10;
    }
    .ql-container.ql-snow {
        position: relative;
        z-index: 1;
    }
</style>
@endsection
