@extends('layouts.app')

@section('title', 'Buat Ujian Baru - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Buat Ujian Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-fadeIn pb-12">
    <!-- Header -->
    <div class="flex flex-col gap-2">
        <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
            <a href="{{ route('admin.exams.index') }}" class="hover:text-indigo-600 transition-colors">Daftar Ujian</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-indigo-600">Buat Baru</span>
        </nav>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                <i class="fas fa-plus-circle text-xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Buat Ujian Baru</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Konfigurasi parameter, jadwal, dan aturan pelaksanaan ujian</p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.exams.store') }}" method="POST" id="examForm" class="space-y-8">
        @csrf

        <!-- Informasi Dasar -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 transition-colors">
                    <i class="fas fa-info-circle text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Informasi Dasar</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Nama ujian, mata pelajaran, dan target jenjang kelas</p>
                </div>
            </div>
            <div class="p-8 space-y-8">
                <div class="space-y-2">
                    <label for="title" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Judul Ujian <span class="text-rose-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" 
                        class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-gray-300 @error('title') ring-2 ring-rose-500 @enderror" 
                        placeholder="Contoh: Penilaian Akhir Semester Ganjil" required>
                    @error('title')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <label for="subject_id" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select id="subject_id" name="subject_id" 
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer @error('subject_id') ring-2 ring-rose-500 @enderror" 
                                required>
                                <option value="" disabled selected>Pilih Mapel</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        @error('subject_id')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-4">
                        <label for="jenjang" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tingkat / Kelas <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select id="jenjang" name="jenjang" 
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer @error('jenjang') ring-2 ring-rose-500 @enderror" 
                                required>
                                <option value="" disabled selected>Pilih Tingkat</option>
                                <option value="10" {{ old('jenjang') == '10' ? 'selected' : '' }}>Kelas 10</option>
                                <option value="11" {{ old('jenjang') == '11' ? 'selected' : '' }}>Kelas 11</option>
                                <option value="12" {{ old('jenjang') == '12' ? 'selected' : '' }}>Kelas 12</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        @error('jenjang')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Durasi & Waktu -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 transition-colors">
                    <i class="fas fa-clock text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Durasi & Waktu</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Atur durasi pengerjaan dan jendela waktu akses ujian</p>
                </div>
            </div>
            <div class="p-8 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="space-y-4">
                        <label for="duration_minutes" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Durasi (Menit) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" 
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all @error('duration_minutes') ring-2 ring-rose-500 @enderror" 
                                min="1" max="480" required>
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-300 uppercase tracking-widest">Menit</span>
                        </div>
                        @error('duration_minutes')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-4">
                        <label for="total_questions" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Jumlah Soal <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="number" id="total_questions" name="total_questions" value="{{ old('total_questions', 40) }}" 
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all @error('total_questions') ring-2 ring-rose-500 @enderror" 
                                min="1" max="500" required>
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-300 uppercase tracking-widest">Butir</span>
                        </div>
                        @error('total_questions')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-4">
                        <label for="status" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Status Awal <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select id="status" name="status" 
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer @error('status') ring-2 ring-rose-500 @enderror" 
                                required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>DRAFT (Sembunyikan)</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>AKTIF (Tampilkan)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-1 lg:col-span-1.5 space-y-4">
                        <label for="start_time" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Waktu Mulai Akses <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" id="start_time" name="start_time" 
                            value="{{ old('start_time') ? str_replace(' ', 'T', old('start_time')) : '' }}" 
                            class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all @error('start_time') ring-2 ring-rose-500 @enderror" 
                            required>
                        @error('start_time')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-1 lg:col-span-1.5 space-y-4">
                        <label for="end_time" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Waktu Selesai Akses <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" id="end_time" name="end_time" 
                            value="{{ old('end_time') ? str_replace(' ', 'T', old('end_time')) : '' }}" 
                            class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all @error('end_time') ring-2 ring-rose-500 @enderror" 
                            required>
                        @error('end_time')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Aturan Pelaksanaan -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 transition-colors">
                    <i class="fas fa-cog text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Aturan Pelaksanaan</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Konfigurasi teknis untuk mencegah kecurangan dan transparansi nilai</p>
                </div>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Bobot Penilain -->
                <div class="col-span-full grid grid-cols-1 md:grid-cols-2 gap-8 mb-4">
                    <div class="space-y-4">
                        <label for="weight_pg" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Bobot Pilihan Ganda (%) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="number" id="weight_pg" name="weight_pg" value="{{ old('weight_pg', 70) }}" 
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all @error('weight_pg') ring-2 ring-rose-500 @enderror" 
                                min="0" max="100" required>
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-300 uppercase tracking-widest">%</span>
                        </div>
                        @error('weight_pg')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-4">
                        <label for="weight_essay" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Bobot Esai (%) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="number" id="weight_essay" name="weight_essay" value="{{ old('weight_essay', 30) }}" 
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all @error('weight_essay') ring-2 ring-rose-500 @enderror" 
                                min="0" max="100" required>
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-300 uppercase tracking-widest">%</span>
                        </div>
                        @error('weight_essay')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>
                </div>

                <label class="group relative flex items-center p-6 bg-gray-50 rounded-[2rem] border-2 border-transparent hover:border-indigo-100 hover:bg-white transition-all cursor-pointer overflow-hidden shadow-sm hover:shadow-lg hover:shadow-indigo-500/5">
                    <input type="checkbox" name="randomize_questions" value="1" {{ old('randomize_questions') ? 'checked' : '' }} class="hidden peer">
                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-gray-400 group-hover:text-indigo-600 transition-colors peer-checked:bg-indigo-600 peer-checked:text-white mr-5 shadow-sm">
                        <i class="fas fa-random"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-black text-gray-900 leading-tight">Acak Urutan Soal</span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mt-1 leading-relaxed">Urutan soal berbeda untuk setiap siswa</span>
                    </div>
                </label>

                <label class="group relative flex items-center p-6 bg-gray-50 rounded-[2rem] border-2 border-transparent hover:border-indigo-100 hover:bg-white transition-all cursor-pointer overflow-hidden shadow-sm hover:shadow-lg hover:shadow-indigo-500/5">
                    <input type="checkbox" name="randomize_options" value="1" {{ old('randomize_options') ? 'checked' : '' }} class="hidden peer">
                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-gray-400 group-hover:text-indigo-600 transition-colors peer-checked:bg-indigo-600 peer-checked:text-white mr-5 shadow-sm">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-black text-gray-900 leading-tight">Acak Pilihan Jawaban</span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mt-1 leading-relaxed">Urutan opsi (A-E) akan diacak secara sistem</span>
                    </div>
                </label>

                <label class="group relative flex items-center p-6 bg-gray-50 rounded-[2rem] border-2 border-transparent hover:border-indigo-100 hover:bg-white transition-all cursor-pointer overflow-hidden shadow-sm hover:shadow-lg hover:shadow-indigo-500/5">
                    <input type="checkbox" name="show_score_after_submit" value="1" {{ old('show_score_after_submit') ? 'checked' : '' }} class="hidden peer">
                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-gray-400 group-hover:text-indigo-600 transition-colors peer-checked:bg-indigo-600 peer-checked:text-white mr-5 shadow-sm">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-black text-gray-900 leading-tight">Tampilkan Nilai Segera</span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mt-1 leading-relaxed">Siswa dapat melihat hasil setelah selesai</span>
                    </div>
                </label>

                <label class="group relative flex items-center p-6 bg-gray-50 rounded-[2rem] border-2 border-transparent hover:border-indigo-100 hover:bg-white transition-all cursor-pointer overflow-hidden shadow-sm hover:shadow-lg hover:shadow-indigo-500/5">
                    <input type="checkbox" name="allow_review_results" value="1" {{ old('allow_review_results') ? 'checked' : '' }} class="hidden peer">
                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-gray-400 group-hover:text-indigo-600 transition-colors peer-checked:bg-indigo-600 peer-checked:text-white mr-5 shadow-sm">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-black text-gray-900 leading-tight">Izinkan Tinjauan</span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mt-1 leading-relaxed">Siswa dapat meninjau soal dan kunci jawaban</span>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex flex-col-reverse md:flex-row gap-4 pt-4">
            <a href="{{ route('admin.exams.index') }}" class="flex-1 h-14 bg-gray-100 text-gray-500 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-200 transition flex items-center justify-center">
                Batal & Kembali
            </a>
            <button type="submit" class="flex-[2] h-14 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center justify-center gap-3 group">
                <i class="fas fa-check-circle text-[10px] group-hover:scale-110 transition-transform"></i> Simpan & Buat Sesi Ujian
            </button>
        </div>
    </form>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-white/80 backdrop-blur-sm z-[100] hidden flex items-center justify-center animate-fadeIn">
    <div class="text-center space-y-6">
        <div class="relative w-20 h-20 mx-auto">
            <div class="absolute inset-0 border-4 border-indigo-50 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-indigo-600 rounded-full border-t-transparent animate-spin"></div>
        </div>
        <div class="space-y-1">
            <p class="text-[10px] font-black text-gray-900 uppercase tracking-[0.3em]">Memproses Data</p>
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Membuat sesi ujian baru...</p>
        </div>
    </div>
</div>

<script>
    const examForm = document.getElementById('examForm');
    const overlay = document.getElementById('loadingOverlay');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');

    examForm.addEventListener('submit', function() {
        overlay.classList.remove('hidden');
    });

    function formatDatetimeLocal(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    if (!startTimeInput.value) {
        const now = new Date();
        startTimeInput.value = formatDatetimeLocal(now);
    }

    startTimeInput.addEventListener('change', () => {
        if (!endTimeInput.value || new Date(endTimeInput.value) <= new Date(startTimeInput.value)) {
            const startDate = new Date(startTimeInput.value);
            const endDate = new Date(startDate.getTime() + 60 * 60 * 1000); // +1 hour
            endTimeInput.value = formatDatetimeLocal(endDate);
        }
    });
    const weightPg = document.getElementById('weight_pg');
    const weightEssay = document.getElementById('weight_essay');

    weightPg.addEventListener('input', () => {
        let val = parseInt(weightPg.value) || 0;
        if (val > 100) val = 100;
        weightPg.value = val;
        weightEssay.value = 100 - val;
    });

    weightEssay.addEventListener('input', () => {
        let val = parseInt(weightEssay.value) || 0;
        if (val > 100) val = 100;
        weightEssay.value = val;
        weightPg.value = 100 - val;
    });
</script>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
