@extends('layouts.app')

@section('title', 'Perbarui Soal - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Perbarui Soal')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 animate-fadeIn pb-20">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col gap-4">
        <nav class="flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">
            <a href="{{ route('admin.questions.index') }}" class="hover:text-indigo-600 transition-colors">Bank Soal</a>
            <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
            <span class="text-indigo-600">Perbarui Soal</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-100">
                    <i class="fas fa-edit text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Edit Soal</h2>
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Modifikasi butir soal yang sudah ada di sistem</p>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.questions.update', $question) }}" method="POST" id="questionForm" enctype="multipart/form-data" class="space-y-8 relative">
        @csrf
        @method('PUT')

        <!-- Form Loading Overlay -->
        <div id="loadingOverlay" class="absolute inset-0 bg-white/80 backdrop-blur-md z-50 hidden flex-col items-center justify-center rounded-[3rem]">
            <div class="w-20 h-20 border-4 border-indigo-50 border-t-indigo-600 rounded-full animate-spin mb-6"></div>
            <p class="text-[11px] font-black text-indigo-600 uppercase tracking-[0.3em] animate-pulse">Menyimpan Perubahan...</p>
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
                                <option value="{{ $subject->id }}" {{ old('subject_id', $question->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
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
                            <option value="10" {{ old('jenjang', $question->jenjang) == '10' ? 'selected' : '' }}>Kelas 10</option>
                            <option value="11" {{ old('jenjang', $question->jenjang) == '11' ? 'selected' : '' }}>Kelas 11</option>
                            <option value="12" {{ old('jenjang', $question->jenjang) == '12' ? 'selected' : '' }}>Kelas 12</option>
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
                            <option value="easy" {{ old('difficulty_level', $question->difficulty_level) == 'easy' ? 'selected' : '' }}>Mudah (Easy)</option>
                            <option value="medium" {{ old('difficulty_level', $question->difficulty_level) == 'medium' ? 'selected' : '' }}>Sedang (Medium)</option>
                            <option value="hard" {{ old('difficulty_level', $question->difficulty_level) == 'hard' ? 'selected' : '' }}>Sulit (Hard)</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none text-[10px]"></i>
                    </div>
                    @error('difficulty_level')<p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase italic tracking-tighter">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2 space-y-3">
                    <label for="topic" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Topik / Sub-Bab <span class="text-rose-500">*</span></label>
                    <input type="text" id="topic" name="topic" value="{{ old('topic', $question->topic) }}" class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all placeholder:text-gray-300 @error('topic') ring-2 ring-rose-500 @enderror" placeholder="Contoh: Turunan Fungsi Trigonometri" required>
                    @error('topic')<p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase italic tracking-tighter">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-3">
                    <label for="question_type" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tipe Soal <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select id="question_type" name="question_type" class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold appearance-none cursor-pointer @error('question_type') ring-2 ring-rose-500 @enderror" required>
                            <option value="multiple_choice" {{ old('question_type', $question->question_type) == 'multiple_choice' ? 'selected' : '' }}>Pilihan Ganda</option>
                            <option value="essay" {{ old('question_type', $question->question_type) == 'essay' ? 'selected' : '' }}>Esai / Uraian</option>
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

            <div class="space-y-8 relative">
                <div class="space-y-3">
                    <label for="question_text" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Butir Pertanyaan <span class="text-rose-500">*</span></label>
                    <textarea id="question_text" name="question_text" rows="8" class="block w-full px-8 py-6 bg-gray-50 border-none rounded-[2rem] focus:ring-4 focus:ring-indigo-500/10 text-base font-bold transition-all placeholder:text-gray-300 leading-relaxed @error('question_text') ring-2 ring-rose-500 @enderror" placeholder="Tuliskan pertanyaan secara lengkap..." required>{{ old('question_text', $question->question_text) }}</textarea>
                    @error('question_text')<p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase italic tracking-tighter">{{ $message }}</p>@enderror
                </div>

                <div class="p-10 bg-gray-50/50 rounded-[2.5rem] border-4 border-dashed border-gray-100 hover:border-indigo-200 hover:bg-white transition-all duration-300 group/upload cursor-pointer relative overflow-hidden">
                    <input type="file" id="question_image" name="question_image" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-10">
                    <div class="flex flex-col items-center justify-center text-center space-y-4">
                        <div class="w-16 h-16 rounded-[1.5rem] bg-white flex items-center justify-center text-gray-300 group-hover/upload:text-indigo-600 shadow-sm transition-all duration-500 group-hover/upload:rotate-6">
                            <i class="fas fa-image text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-black text-gray-500 uppercase tracking-[0.2em] group-hover/upload:text-indigo-600">Klik / Seret Gambar Baru</p>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-1 opacity-60">Opsional • Format: JPG, PNG, GIF (Maks 2MB)</p>
                        </div>
                    </div>
                    <div id="question_image_preview" class="mt-8 flex justify-center @if(!$question->question_image) hidden @endif">
                        @if($question->question_image)
                            <div class="relative group/prev">
                                <img src="{{ asset($question->question_image) }}" class="max-h-48 rounded-[1.5rem] shadow-2xl border-4 border-white animate-pop">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/prev:opacity-100 transition-opacity rounded-[1.5rem] flex items-center justify-center">
                                    <i class="fas fa-check text-white text-2xl"></i>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
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

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 relative mb-12">
                    @foreach(['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d', 'E' => 'option_e'] as $label => $name)
                        <div class="p-8 bg-gray-50 rounded-[2.5rem] border border-gray-100 space-y-6 hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500 group/opt">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <span class="w-10 h-10 flex items-center justify-center bg-indigo-600 text-white rounded-[1.25rem] text-sm font-black shadow-lg shadow-indigo-100 group-hover/opt:scale-110 transition-transform">
                                        {{ $label }}
                                    </span>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mt-1">Opsi {{ $label }} {{ $label !== 'E' ? '*' : '' }}</label>
                                </div>
                            </div>
                            
                            <input type="text" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $question->$name) }}" 
                                class="block w-full px-6 py-4 bg-white border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all placeholder:text-gray-300" 
                                placeholder="Ketik teks pilihan {{ $label }}...">
                            
                            <div class="relative group/cam h-24 bg-white rounded-2xl border-2 border-dashed border-gray-100 flex items-center justify-center cursor-pointer hover:border-indigo-200 transition-all overflow-hidden">
                                <input type="file" name="{{ $name }}_image" id="{{ $name }}_image" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                <div class="flex items-center gap-3 text-gray-300 group-hover/cam:text-indigo-500 transition-colors">
                                    <i class="fas fa-camera text-sm"></i>
                                    <span class="text-[9px] font-black uppercase tracking-widest">Gambar Opsi {{ $label }}</span>
                                </div>
                                <div id="{{ $name }}_image_preview" class="absolute inset-0 flex items-center justify-center bg-white pointer-events-none @if(!$question->{$name . '_image'}) hidden @endif">
                                    @if($question->{$name . '_image'})
                                        <img src="{{ asset($question->{$name . '_image'}) }}" class="max-h-20 rounded-lg shadow-sm border-2 border-white animate-pop">
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-10 bg-indigo-600 rounded-[2.5rem] shadow-2xl shadow-indigo-200 relative overflow-hidden group/key">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-xl group-hover/key:scale-150 transition-transform duration-700"></div>
                    
                    <div class="flex flex-col lg:flex-row items-center justify-between gap-10 relative">
                        <div class="text-white text-center lg:text-left">
                            <h4 class="text-base font-black uppercase tracking-[0.2em] mb-2">Pilih Kunci Jawaban</h4>
                            <p class="text-[10px] font-black opacity-70 uppercase tracking-widest leading-relaxed">Tandai satu opsi sebagai jawaban yang tepat</p>
                        </div>
                        <div class="flex flex-wrap justify-center gap-3">
                            @foreach(['A', 'B', 'C', 'D', 'E'] as $val)
                                <label class="cursor-pointer group/radio">
                                    <input type="radio" name="correct_answer" value="{{ $val }}" class="peer hidden" {{ old('correct_answer', $question->correct_answer) == $val ? 'checked' : '' }}>
                                    <span class="w-16 h-16 flex items-center justify-center bg-indigo-500/50 text-white/50 rounded-[1.5rem] text-xl font-black peer-checked:bg-white peer-checked:text-indigo-600 peer-checked:shadow-2xl transition-all duration-300 border-2 border-transparent peer-checked:border-white hover:text-white uppercase">
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
                <textarea id="explanation" name="explanation" rows="5" class="block w-full px-8 py-6 bg-gray-50 border-none rounded-[2rem] focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all placeholder:text-gray-300 leading-relaxed" placeholder="Tuliskan langkah-langkah penyelesaian atau penjelasan di sini...">{{ old('explanation', $question->explanation) }}</textarea>
                
                <div class="flex flex-col-reverse md:flex-row gap-5 pt-10 border-t-2 border-gray-50">
                    <a href="{{ route('admin.questions.index') }}" class="flex-1 h-16 bg-gray-50 text-gray-400 text-[11px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-100 hover:text-gray-500 transition-all flex items-center justify-center">
                        Batal
                    </a>
                    <button type="submit" class="flex-[3] h-16 bg-indigo-600 text-white text-[11px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-2xl shadow-indigo-500/20 flex items-center justify-center gap-4 group/submit">
                        <i class="fas fa-save group-hover:rotate-12 transition-transform"></i> Simpan Perubahan Soal
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const questionTypeSelect = document.getElementById('question_type');
    const multipleChoiceSection = document.getElementById('multipleChoiceSection');
    const optionInputs = document.querySelectorAll('[name^="option_"]:not([name*="_image"])');
    const overlay = document.getElementById('loadingOverlay');
    const questionForm = document.getElementById('questionForm');

    function toggleMultipleChoice() {
        if (questionTypeSelect.value === 'multiple_choice') {
            multipleChoiceSection.classList.remove('hidden');
            multipleChoiceSection.classList.add('animate-fadeIn');
            optionInputs.forEach((input, index) => {
                if(index < 4) input.required = true;
            });
        } else {
            multipleChoiceSection.classList.add('hidden');
            optionInputs.forEach(input => input.required = false);
        }
    }

    questionForm.addEventListener('submit', () => {
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    });

    questionTypeSelect.addEventListener('change', toggleMultipleChoice);
    window.addEventListener('load', toggleMultipleChoice);

    function setupImagePreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        
        if (input && preview) {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        preview.innerHTML = `
                            <div class="relative group/prev">
                                <img src="${event.target.result}" class="max-h-48 rounded-[1.5rem] shadow-2xl border-4 border-white animate-pop">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/prev:opacity-100 transition-opacity rounded-[1.5rem] flex items-center justify-center">
                                    <i class="fas fa-check text-white text-2xl"></i>
                                </div>
                            </div>
                        `;
                        preview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    setupImagePreview('question_image', 'question_image_preview');
    ['option_a', 'option_b', 'option_c', 'option_d', 'option_e'].forEach(opt => {
        setupImagePreview(`${opt}_image`, `${opt}_image_preview`);
    });
</script>

<style>
    .animate-fadeIn { animation: fadeIn 0.5s ease-out; }
    .animate-pop { animation: pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pop { from { opacity: 0; transform: scale(0.9) rotate(-2deg); } to { opacity: 1; transform: scale(1) rotate(0); } }
</style>
@endsection
