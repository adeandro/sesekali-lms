@extends('layouts.app')

@section('title', 'Edit Mata Pelajaran - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Edit Mata Pelajaran')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-fadeIn pb-12">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col gap-4">
        <nav class="flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">
            <a href="{{ route('admin.subjects.index') }}" class="hover:text-indigo-600 transition-colors flex items-center gap-2">
                <i class="fas fa-book-open"></i> Mata Pelajaran
            </a>
            <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
            <span class="text-indigo-600">Edit Data</span>
            <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-full">ID: #{{ $subject->id }}</span>
        </nav>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-100">
                <i class="fas fa-pen-nib text-xl"></i>
            </div>
            <div class="flex-1">
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase line-clamp-1">Edit: {{ $subject->name }}</h2>
                <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Perbarui informasi kategori mata pelajaran</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-[3rem] shadow-sm border border-gray-100 overflow-hidden relative group">
        <!-- Decorative Background -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-amber-50/30 rounded-full -mr-32 -mt-32 group-hover:scale-110 transition-transform duration-700"></div>
        
        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="absolute inset-0 bg-white/90 backdrop-blur-md z-50 hidden flex-col items-center justify-center transition-all duration-500">
            <div class="relative">
                <div class="w-20 h-20 border-4 border-amber-50 border-t-amber-600 rounded-full animate-spin"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fas fa-sync-alt text-amber-600 animate-spin"></i>
                </div>
            </div>
            <p class="mt-6 text-[10px] font-black text-amber-600 uppercase tracking-[0.3em] animate-pulse">Memperbarui...</p>
        </div>

        <form action="{{ route('admin.subjects.update', $subject) }}" method="POST" id="subjectForm" class="p-10 md:p-14 space-y-10 relative z-10">
            @csrf
            @method('PUT')

            <!-- Form Group -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="space-y-4">
                    <label for="name" class="inline-flex items-center gap-2 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">
                        <i class="fas fa-tag text-amber-400"></i> Nama Mata Pelajaran <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative group mt-1">
                        <div class="absolute inset-y-0 left-0 pl-7 flex items-center pointer-events-none text-gray-400 group-focus-within:text-amber-600 transition-colors">
                            <i class="fas fa-book-reader text-lg"></i>
                        </div>
                        <input type="text" id="name" name="name" value="{{ old('name', $subject->name) }}" 
                            class="block w-full pl-16 pr-8 py-5 bg-gray-50 border-2 border-transparent rounded-[2rem] focus:bg-white focus:border-amber-500/20 focus:ring-4 focus:ring-amber-500/5 text-base font-bold transition-all placeholder:text-gray-300 @error('name') border-rose-500/20 ring-4 ring-rose-500/5 @enderror" 
                            placeholder="Contoh: Matematika, Fisika, atau Sejarah Modern" required autofocus>
                    </div>
                    @error('name')
                        <div class="flex items-center gap-2 mt-3 ml-2 text-rose-500 animate-shake">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <p class="text-[10px] font-black uppercase tracking-widest italic">{{ $message }}</p>
                        </div>
                    @enderror
                </div>

                <div class="space-y-4">
                    <label for="kkm" class="inline-flex items-center gap-2 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">
                        <i class="fas fa-bullseye text-amber-400"></i> KKM (Kriteria Ketuntasan Minimum) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative group mt-1">
                        <div class="absolute inset-y-0 left-0 pl-7 flex items-center pointer-events-none text-gray-400 group-focus-within:text-amber-600 transition-colors">
                            <i class="fas fa-star text-lg"></i>
                        </div>
                        <input type="number" id="kkm" name="kkm" value="{{ old('kkm', $subject->kkm) }}" 
                            class="block w-full pl-16 pr-8 py-5 bg-gray-50 border-2 border-transparent rounded-[2rem] focus:bg-white focus:border-amber-500/20 focus:ring-4 focus:ring-amber-500/5 text-base font-bold transition-all @error('kkm') border-rose-500/20 ring-4 ring-rose-500/5 @enderror" 
                            placeholder="Default: 75" min="0" max="100" required>
                    </div>
                    @error('kkm')
                        <div class="flex items-center gap-2 mt-3 ml-2 text-rose-500 animate-shake">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <p class="text-[10px] font-black uppercase tracking-widest italic">{{ $message }}</p>
                        </div>
                    @enderror
                </div>
            </div>

            <!-- Info Box -->
            <div class="p-8 bg-amber-50/50 rounded-[2.5rem] border-2 border-amber-100/30 flex gap-6 items-start">
                <div class="w-14 h-14 rounded-2xl bg-white flex items-center justify-center text-amber-600 shadow-sm shrink-0">
                    <i class="fas fa-info-circle text-xl"></i>
                </div>
                <div class="space-y-2">
                    <h4 class="text-[11px] font-black text-amber-900 uppercase tracking-[0.2em]">Pemberitahuan</h4>
                    <p class="text-xs font-bold text-amber-700/60 leading-relaxed uppercase tracking-tight">Perubahan pada nama mata pelajaran akan diterapkan secara otomatis ke seluruh butir soal dan jadwal ujian yang menggunakan kategori ini.</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col-reverse md:flex-row gap-5 pt-8">
                <a href="{{ route('admin.subjects.index') }}" class="flex-1 h-16 flex items-center justify-center bg-gray-50 text-gray-400 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-100 transition-all border-2 border-transparent">
                    <i class="fas fa-arrow-left mr-3"></i> Batal
                </a>
                <button type="submit" class="flex-[2] h-16 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 group">
                    <i class="fas fa-save mr-3 group-hover:scale-125 transition-transform text-sm text-indigo-200"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const subjectForm = document.getElementById('subjectForm');
    const overlay = document.getElementById('loadingOverlay');

    if (subjectForm) {
        subjectForm.addEventListener('submit', function() {
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        });
    }
</script>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    .animate-shake { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes shake { 10%, 90% { transform: translate3d(-1px, 0, 0); } 20%, 80% { transform: translate3d(2px, 0, 0); } 30%, 50%, 70% { transform: translate3d(-4px, 0, 0); } 40%, 60% { transform: translate3d(4px, 0, 0); } }
</style>
@endsection
