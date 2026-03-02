@extends('layouts.app')

@section('title', 'Import Soal - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Import Soal')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 animate-fadeIn pb-20">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col gap-4">
        <nav class="flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">
            <a href="{{ route('admin.questions.index') }}" class="hover:text-indigo-600 transition-colors">Bank Soal</a>
            <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
            <span class="text-indigo-600">Import Soal</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                    <i class="fas fa-file-import text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Import Soal</h2>
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Unggah berkas Excel untuk menambah soal secara massal</p>
                </div>
            </div>
            <a href="{{ route('admin.questions.index') }}" class="inline-flex items-center gap-3 px-6 py-4 bg-white border border-gray-100 text-[10px] font-black uppercase tracking-widest text-gray-500 rounded-2xl hover:bg-gray-50 hover:text-indigo-600 transition-all shadow-sm">
                <i class="fas fa-arrow-left text-[8px]"></i> Kembali
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-8 bg-rose-50 border-2 border-rose-100 rounded-[2.5rem] animate-pop">
            <div class="flex items-center gap-4 mb-4 text-rose-600">
                <i class="fas fa-exclamation-triangle text-xl"></i>
                <h4 class="font-black uppercase tracking-widest text-sm">Terjadi Kesalahan Validasi</h4>
            </div>
            <ul class="space-y-2">
                @foreach ($errors->all() as $error)
                    <li class="flex items-center gap-3 text-rose-700 text-[11px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 relative">
        <!-- Instructions Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-10 relative overflow-hidden group">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-indigo-50/50 rounded-full blur-3xl group-hover:bg-indigo-100/50 transition-colors duration-700"></div>
                
                <h3 class="text-xs font-black text-indigo-900 uppercase tracking-[0.2em] mb-8 flex items-center gap-3 relative">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-[10px]">1</span>
                    Panduan Format
                </h3>
                
                <div class="space-y-8 relative">
                    <div class="p-5 bg-gray-50 rounded-[1.5rem] border border-gray-100">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Jenis Berkas:</p>
                        <div class="flex items-center gap-3 text-sm font-black text-gray-800">
                            <i class="far fa-file-excel text-emerald-500 text-lg"></i>
                            Microsoft Excel (.xlsx)
                        </div>
                    </div>

                    <div class="space-y-4">
                        <p class="text-[10px] font-black text-indigo-900 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                            Kolom Wajib:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['subject', 'jenjang', 'topic', 'difficulty', 'question_type', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d'] as $col)
                                <span class="px-3 py-1.5 bg-indigo-50 text-indigo-600 text-[9px] font-black uppercase tracking-tighter rounded-lg border border-indigo-100/50">{{ $col }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-gray-50">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                            Kolom Opsional:
                        </p>
                        <div class="flex flex-wrap gap-2 text-gray-400">
                            @foreach(['option_e', 'correct_answer', 'explanation'] as $col)
                                <span class="px-3 py-1.5 bg-gray-50 text-[9px] font-black uppercase tracking-tighter rounded-lg border border-gray-100">{{ $col }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-6 bg-amber-50 rounded-[2rem] border border-amber-100 mt-6 group/tip">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 group-hover/tip:rotate-12 transition-transform">
                                <i class="fas fa-lightbulb text-xs"></i>
                            </div>
                            <p class="text-[10px] font-bold text-amber-800 leading-relaxed uppercase tracking-tighter">
                                Pastikan mata pelajaran sudah terdaftar, atau akan dibuat otomatis oleh sistem saat proses import.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <a href="#" class="block p-8 bg-indigo-600 rounded-[2.5rem] shadow-xl shadow-indigo-100 group/dl hover:bg-indigo-700 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-white text-[11px] font-black uppercase tracking-widest mb-1">Unduh Template</h4>
                        <p class="text-white/60 text-[9px] font-bold uppercase tracking-tighter">Excel Template v1.0</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-white group-hover/dl:translate-y-1 transition-transform">
                        <i class="fas fa-cloud-download-alt text-xl"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Upload Form -->
        <div class="lg:col-span-2 relative">
            <div class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-8 md:p-12 h-full relative overflow-hidden group/form">
                <!-- Form Loading Overlay -->
                <div id="loadingOverlay" class="absolute inset-0 bg-white/80 backdrop-blur-md z-50 hidden flex-col items-center justify-center">
                    <div class="w-20 h-20 border-4 border-indigo-50 border-t-indigo-600 rounded-full animate-spin mb-6 shadow-2xl shadow-indigo-100"></div>
                    <p class="text-[11px] font-black text-indigo-600 uppercase tracking-[0.3em] animate-pulse">Memproses Data Excel...</p>
                </div>

                <div class="flex items-center gap-5 mb-12 relative">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-upload text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900 tracking-tight uppercase relative">
                            <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-[10px] inline-flex mr-2">2</span>
                            Unggah Berkas
                        </h3>
                    </div>
                </div>

                <form action="{{ route('admin.questions.import') }}" method="POST" id="importForm" enctype="multipart/form-data" class="space-y-10 relative">
                    @csrf

                    <!-- Drag & Drop Area -->
                    <div class="relative">
                        <div id="dropZone" class="group/drop relative border-4 border-dashed border-gray-100 rounded-[3rem] p-16 bg-gray-50/50 cursor-pointer hover:bg-white hover:border-indigo-200 transition-all duration-500 overflow-hidden flex flex-col items-center justify-center text-center">
                            <div class="absolute inset-0 bg-indigo-600/0 group-hover/drop:bg-indigo-600/[0.02] transition-colors duration-500"></div>
                            
                            <div class="relative space-y-6">
                                <div class="w-24 h-24 rounded-[2rem] bg-white flex items-center justify-center text-gray-300 group-hover/drop:text-indigo-600 group-hover/drop:scale-110 group-hover/drop:shadow-2xl group-hover/drop:shadow-indigo-100 transition-all duration-500 mx-auto">
                                    <i class="fas fa-file-excel text-4xl"></i>
                                </div>
                                <div>
                                    <p class="text-lg font-black text-gray-900 uppercase tracking-tight group-hover/drop:text-indigo-600 transition-colors">Klik atau Tarik Berkas</p>
                                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest mt-2">Mendukung format .xlsx atau .xls</p>
                                </div>
                                <div class="px-6 py-2 bg-white rounded-full text-[9px] font-black text-gray-400 border border-gray-100 uppercase tracking-widest shadow-sm">
                                    Maksimal ukuran: 150MB
                                </div>
                            </div>
                            <input id="file" name="file" type="file" accept=".xlsx,.xls" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        </div>
                        
                        <div id="fileInfo" class="mt-6 hidden animate-pop">
                            <div class="p-5 bg-emerald-50 rounded-[1.5rem] border border-emerald-100 flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-white text-emerald-600 flex items-center justify-center shadow-sm">
                                        <i class="fas fa-check text-sm"></i>
                                    </div>
                                    <div>
                                        <p id="fileName" class="text-sm font-black text-emerald-900 uppercase tracking-tight">-</p>
                                        <p id="fileSize" class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">0 MB</p>
                                    </div>
                                </div>
                                <button type="button" onclick="resetFile()" class="w-10 h-10 rounded-xl hover:bg-rose-50 hover:text-rose-600 transition-colors text-gray-300">
                                    <i class="fas fa-times text-sm"></i>
                                </button>
                            </div>
                        </div>
                        @error('file')<p class="text-rose-500 text-[10px] font-black mt-4 ml-4 uppercase tracking-tighter italic">{{ $message }}</p>@enderror
                    </div>

                    <!-- Options -->
                    <div class="p-8 bg-gray-50 rounded-[2.5rem] border border-gray-100 group/opt hover:bg-white hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
                        <label class="flex items-center gap-6 cursor-pointer group/label">
                            <div class="relative w-8 h-8 shrink-0">
                                <input type="checkbox" name="update_existing" value="1" class="peer absolute inset-0 opacity-0 cursor-pointer z-10">
                                <div class="w-full h-full bg-white border-2 border-gray-200 rounded-xl transition-all peer-checked:bg-indigo-600 peer-checked:border-indigo-600 peer-hover:border-indigo-300 flex items-center justify-center">
                                    <i class="fas fa-check text-white text-[10px] scale-0 peer-checked:scale-100 transition-transform"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-[11px] font-black text-gray-900 uppercase tracking-widest group-hover/label:text-indigo-600 transition-colors">Update Soal yang Sudah Ada</p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter mt-1 opacity-70">Sistem akan memperbarui data jika ditemukan duplikasi soal</p>
                            </div>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full h-20 bg-indigo-600 text-white rounded-[2rem] text-[11px] font-black uppercase tracking-[0.3em] hover:bg-indigo-700 transition-all duration-300 shadow-2xl shadow-indigo-100 flex items-center justify-center gap-4 group/btn overflow-hidden relative">
                        <div class="absolute inset-0 bg-white/10 -translate-x-full group-hover/btn:translate-x-full transition-transform duration-1000"></div>
                        <i class="fas fa-rocket text-sm group-hover/btn:-translate-y-1 transition-transform"></i> 
                        Proses Import Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('file');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const importForm = document.getElementById('importForm');
    const overlay = document.getElementById('loadingOverlay');

    // Drag events
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.add('border-indigo-500', 'bg-indigo-50/50');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-indigo-500', 'bg-indigo-50/50');
        }, false);
    });

    // Handle drops
    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            fileInput.files = files;
            updateFileInfo();
        }
    });

    // File input change
    fileInput.addEventListener('change', updateFileInfo);

    function updateFileInfo() {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            fileName.textContent = file.name;
            fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            fileInfo.classList.remove('hidden');
            dropZone.classList.add('hidden');
        }
    }

    function resetFile() {
        fileInput.value = '';
        fileInfo.classList.add('hidden');
        dropZone.classList.remove('hidden');
    }

    importForm.addEventListener('submit', () => {
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    });
</script>

<style>
    .animate-fadeIn { animation: fadeIn 0.5s ease-out; }
    .animate-pop { animation: pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pop { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
</style>
@endsection
