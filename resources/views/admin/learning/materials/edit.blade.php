@extends('layouts.app')

@section('title', 'Edit Materi: ' . $material->title . ' - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Edit Materi')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-fadeIn pb-12">
    <!-- Header -->
    <div class="flex flex-col gap-2">
        <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
            <a href="{{ route('admin.learning.materials.index') }}" class="hover:text-indigo-600 transition-colors">Daftar Materi</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-indigo-600">Edit Materi</span>
        </nav>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-100">
                <i class="fas fa-edit text-xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Edit Materi</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Sesuaikan konfigurasi materi pembelajaran</p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.learning.materials.update', $material) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 transition-colors">
                    <i class="fas fa-info-circle text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Informasi Materi</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Judul materi, mata pelajaran, dan sampul visual</p>
                </div>
            </div>
            <div class="p-8 space-y-8">
                <div class="space-y-2">
                    <label for="title" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Judul Materi <span class="text-rose-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title', $material->title) }}" 
                        class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-gray-300 @error('title') ring-2 ring-rose-500 @enderror" 
                        placeholder="Contoh: Pemrograman Dasar Python" required>
                    @error('title')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <label for="subject_id" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select id="subject_id" name="subject_id" 
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer @error('subject_id') ring-2 ring-rose-500 @enderror" 
                                required>
                                <option value="">Pilih Mata Pelajaran</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id', $material->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label for="exam_id" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Ujian Latihan Terkait (Opsional)</label>
                        <div class="relative">
                            <select id="exam_id" name="exam_id" 
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all appearance-none cursor-pointer @error('exam_id') ring-2 ring-rose-500 @enderror">
                                <option value="">Tanpa Ujian Latihan</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" {{ old('exam_id', $material->exam_id) == $exam->id ? 'selected' : '' }}>
                                        [{{ $exam->subject->name }}] {{ $exam->title }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-pencil-alt text-xs"></i>
                            </div>
                        </div>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter mt-1 italic">Siswa akan diarahkan ke latihan ini setelah menyelesaikan materi.</p>
                    </div>

                    <div class="space-y-4">
                        <label for="order" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Urutan Tampilan</label>
                        <input type="number" id="order" name="order" value="{{ old('order', $material->order) }}" 
                            class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Cover Image / Thumbnail</label>
                    <div class="relative group/upload h-48 bg-gray-50 rounded-[2rem] border-2 border-dashed border-gray-200 flex flex-col items-center justify-center hover:bg-indigo-50 hover:border-indigo-200 transition-all overflow-hidden cursor-pointer"
                         onclick="document.getElementById('cover_image').click()">
                        
                        <div id="preview-container" class="absolute inset-0 {{ $material->cover_image ? '' : 'hidden' }}">
                            <img id="image-preview" src="{{ $material->cover_image ? asset('storage/' . $material->cover_image) : '' }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover/upload:opacity-100 transition-opacity">
                                <span class="text-[10px] font-black text-white uppercase tracking-widest">Klik untuk Ubah</span>
                            </div>
                        </div>

                        <div id="placeholder-container" class="flex flex-col items-center gap-3 {{ $material->cover_image ? 'hidden' : '' }}">
                            <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-gray-400 shadow-sm group-hover/upload:text-indigo-600 transition-colors">
                                <i class="fas fa-image text-xl"></i>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] font-black text-gray-900 uppercase tracking-widest">Pilih Gambar Sampul</p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase mt-1 tracking-tighter">PNG, JPG up to 2MB (Ratio 16:9 disarankan)</p>
                            </div>
                        </div>

                        <input type="file" id="cover_image" name="cover_image" class="hidden" accept="image/*" onchange="previewImage(this)">
                    </div>
                    @error('cover_image')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                </div>

                <div class="pt-4 border-t border-gray-50">
                    <label class="group relative flex items-center p-6 bg-gray-50 rounded-[2rem] border-2 border-transparent hover:border-emerald-400 hover:bg-white transition-all duration-200 cursor-pointer overflow-hidden shadow-sm hover:shadow-lg">
                        <input type="checkbox" name="is_published" value="1" {{ old('is_published', $material->is_published) ? 'checked' : '' }} class="hidden peer">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center mr-5 shadow-sm transition-colors duration-200 bg-gray-300 text-gray-500 peer-checked:bg-emerald-500 peer-checked:text-white">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="flex flex-col flex-1">
                            <span class="text-sm font-black text-gray-900 leading-tight">Publikasikan Materi</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mt-1 leading-relaxed text-left">Siswa dapat langsung melihat materi ini jika aktif</span>
                        </div>
                        <div class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out bg-gray-300 peer-checked:bg-emerald-500 peer-checked:[&>span]:translate-x-5">
                            <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md transition-transform duration-200 translate-x-0"></span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse md:flex-row gap-4">
            <a href="{{ route('admin.learning.materials.index') }}" class="flex-1 h-14 bg-gray-100 text-gray-500 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-200 transition flex items-center justify-center">
                Batal & Kembali
            </a>
            <button type="submit" class="flex-[2] h-14 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center justify-center gap-3 group">
                <i class="fas fa-save text-[10px] group-hover:scale-110 transition-transform"></i> Perbarui Materi
            </button>
        </div>
    </form>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview').src = e.target.result;
                document.getElementById('preview-container').classList.remove('hidden');
                document.getElementById('placeholder-container').classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
