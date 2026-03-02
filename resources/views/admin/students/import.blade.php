@extends('layouts.app')

@section('title', 'Impor Siswa - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Impor Data Siswa')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6 animate-fadeIn">
        <!-- Breadcrumb & Header -->
        <div class="flex items-center justify-between mb-2">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.students.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">
                            <i class="fas fa-users mr-2"></i> Manajemen Siswa
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 text-xs mx-2"></i>
                            <span class="text-sm font-bold text-indigo-600">Impor Data Excel</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <a href="{{ route('admin.students.index') }}" class="text-sm font-bold text-gray-500 hover:text-rose-600 transition-colors flex items-center gap-1">
                <i class="fas fa-times-circle"></i> Batal
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-100 rounded-3xl flex items-start gap-3 animate-headShake">
                <i class="fas fa-exclamation-circle text-rose-500 mt-1"></i>
                <div>
                    <p class="font-bold text-rose-800 text-sm">Terjadi Kesalahan:</p>
                    <ul class="mt-1 list-disc list-inside text-rose-700 text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-indigo-50/50 border border-indigo-100 rounded-3xl p-5 hover:bg-indigo-50 transition-colors">
                <div class="w-10 h-10 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 mb-4 shadow-sm">
                    <i class="fas fa-file-excel"></i>
                </div>
                <h3 class="text-indigo-900 font-bold text-sm tracking-tight">Format File</h3>
                <p class="text-xs text-indigo-700 mt-1 leading-relaxed">Gunakan Excel (.xlsx) dengan kolom: <b>nis</b>, <b>full_name</b>, <b>grade</b> (10-12), & <b>class_group</b>.</p>
            </div>

            <div class="bg-emerald-50/50 border border-emerald-100 rounded-3xl p-5 hover:bg-emerald-50 transition-colors">
                <div class="w-10 h-10 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 mb-4 shadow-sm">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="text-emerald-900 font-bold text-sm tracking-tight">Validasi Otomatis</h3>
                <p class="text-xs text-emerald-700 mt-1 leading-relaxed">Sistem akan mengecek NIS duplikat dan kelengkapan data sebelum diproses untuk menjamin keamanan.</p>
            </div>

            <div class="bg-purple-50/50 border border-purple-100 rounded-3xl p-5 hover:bg-purple-50 transition-colors">
                <div class="w-10 h-10 bg-purple-100 rounded-2xl flex items-center justify-center text-purple-600 mb-4 shadow-sm">
                    <i class="fas fa-key"></i>
                </div>
                <h3 class="text-purple-900 font-bold text-sm tracking-tight">Akun Siswa</h3>
                <p class="text-xs text-purple-700 mt-1 leading-relaxed">Password akan dibuat otomatis secara acak. Anda akan menerima daftar password di akhir proses.</p>
            </div>

            <div class="bg-rose-50/50 border border-rose-100 rounded-3xl p-5 hover:bg-rose-50 transition-colors">
                <div class="w-10 h-10 bg-rose-100 rounded-2xl flex items-center justify-center text-rose-600 mb-4 shadow-sm">
                    <i class="fas fa-images"></i>
                </div>
                <h3 class="text-rose-900 font-bold text-sm tracking-tight">Foto Profil?</h3>
                <p class="text-xs text-rose-700 mt-1 leading-relaxed">Foto profil diunggah secara terpisah menggunakan file ZIP.</p>
                <a href="{{ route('admin.students.upload-photos') }}" class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 mt-2 hover:underline">
                    Ke Unggah ZIP <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8">
                <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" id="import-form">
                    @csrf

                    <div class="mb-8">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 ml-1">Pilih Berkas Excel</label>
                        <div class="relative group">
                            <label for="file" class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-200 rounded-3xl cursor-pointer bg-gray-50/50 hover:bg-indigo-50/30 hover:border-indigo-300 transition-all group-hover:scale-[1.002]">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <div class="w-16 h-16 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center text-indigo-600 mb-4 group-hover:rotate-6 transition-transform">
                                        <i class="fas fa-cloud-upload-alt text-2xl"></i>
                                    </div>
                                    <p class="mb-2 text-sm text-gray-700 font-bold">Tekan untuk unggah berkas</p>
                                    <p class="text-xs text-gray-500">atau drag & drop file .xlsx di sini (Maks 10MB)</p>
                                </div>
                                <input id="file" name="file" type="file" accept=".xlsx" required class="hidden" onchange="handleFileSelected(this)">
                            </label>
                        </div>
                    </div>

                    <!-- File Info -->
                    <div id="file-info" class="hidden animate-fadeIn">
                        <div class="mb-8 p-5 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100 shadow-sm">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <div>
                                    <p id="file-name" class="text-sm font-bold text-indigo-900 truncate max-w-xs"></p>
                                    <p id="file-size" class="text-[10px] text-indigo-700"></p>
                                </div>
                            </div>
                            <button type="button" onclick="resetFile()" class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Progress Block -->
                    <div id="progress-block" class="hidden space-y-4 mb-8">
                        <div class="flex items-center justify-between text-xs font-bold text-indigo-600 uppercase tracking-widest">
                            <span id="progress-status">Memproses berkas...</span>
                            <span id="progress-percent">0%</span>
                        </div>
                        <div class="h-3 bg-gray-100 rounded-full overflow-hidden border border-gray-100 shadow-inner">
                            <div id="progress-bar" class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600 w-0 transition-all duration-300 rounded-full shadow-lg shadow-indigo-200"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" id="submit-btn" class="flex-1 px-8 py-4 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition shadow-sm hover:shadow-md active:scale-95 flex items-center justify-center gap-2">
                            <i class="fas fa-file-import"></i> Mulai Impor Siswa
                        </button>
                        <a href="{{ route('admin.students.index') }}" class="px-8 py-4 bg-gray-100 text-gray-600 font-bold rounded-2xl hover:bg-gray-200 transition text-center flex items-center justify-center gap-2 border border-gray-200">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function handleFileSelected(input) {
            const fileInfo = document.getElementById('file-info');
            const fileName = document.getElementById('file-name');
            const fileSize = document.getElementById('file-size');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                fileInfo.classList.remove('hidden');
            } else {
                fileInfo.classList.add('hidden');
            }
        }

        function resetFile() {
            const input = document.getElementById('file');
            input.value = '';
            document.getElementById('file-info').classList.add('hidden');
        }

        document.getElementById('import-form').addEventListener('submit', function(e) {
            const progressBlock = document.getElementById('progress-block');
            const progressBar = document.getElementById('progress-bar');
            const progressPercent = document.getElementById('progress-percent');
            const submitBtn = document.getElementById('submit-btn');

            progressBlock.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyiapkan...';

            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 10;
                if (progress >= 95) {
                    progress = 95;
                    clearInterval(interval);
                    document.getElementById('progress-status').textContent = 'Finalisasi data...';
                }
                progressBar.style.width = progress + '%';
                progressPercent.textContent = Math.round(progress) + '%';
            }, 500);
        });
    </script>

    <style>
        .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes headShake {
            0% { transform: translateX(0); }
            6.5% { transform: translateX(-6px) rotateY(-9deg); }
            18.5% { transform: translateX(5px) rotateY(7deg); }
            31.5% { transform: translateX(-3px) rotateY(-5deg); }
            43.5% { transform: translateX(2px) rotateY(3deg); }
            50% { transform: translateX(0); }
        }
        .animate-headShake { animation: headShake 0.8s ease-in-out; }
    </style>
@endsection
