@extends('layouts.app')

@section('title', 'Unggah Foto Siswa - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Unggah Foto Siswa (ZIP)')

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
                            <span class="text-sm font-bold text-indigo-600">Unggah Foto (ZIP)</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <a href="{{ route('admin.students.index') }}" class="text-sm font-bold text-gray-500 hover:text-rose-600 transition-colors flex items-center gap-1">
                <i class="fas fa-times-circle"></i> Batal
            </a>
        </div>

        @php
            function parsePHPSize($size) {
                $unit = strtoupper(substr($size, -1));
                $val = (int)$size;
                switch($unit) {
                    case 'G': $val *= 1024;
                    case 'M': $val *= 1024;
                    case 'K': $val *= 1024;
                }
                return $val;
            }
            $phpMaxUpload = parsePHPSize(ini_get('upload_max_filesize'));
            $phpMaxPost = parsePHPSize(ini_get('post_max_size'));
            $laravelLimit = 20 * 1024 * 1024; // 20 MB
            $effectiveMaxBytes = min($phpMaxUpload, $phpMaxPost, $laravelLimit);
            $effectiveMaxMB = round($effectiveMaxBytes / (1024 * 1024), 1);
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">

                @if(session('photo_report'))
                    @php $rep = session('photo_report'); @endphp
                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                        <h4 class="font-bold text-gray-900 flex items-center gap-2 text-base">
                            <i class="fas fa-clipboard-list text-indigo-600"></i> Laporan Hasil Sinkronisasi Foto
                        </h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                            <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-100">
                                <span class="text-xs text-emerald-600 font-semibold block">Berhasil Diperbarui</span>
                                <span class="text-2xl font-black text-emerald-700">{{ $rep['berhasil'] ?? 0 }}</span>
                            </div>
                            <div class="p-3 bg-amber-50 rounded-2xl border border-amber-100">
                                <span class="text-xs text-amber-600 font-semibold block">Tidak Ditemukan</span>
                                <span class="text-2xl font-black text-amber-700">{{ count($rep['tidak_ditemukan'] ?? []) }}</span>
                            </div>
                            <div class="p-3 bg-orange-50 rounded-2xl border border-orange-100">
                                <span class="text-xs text-orange-600 font-semibold block">Nama Ambigu</span>
                                <span class="text-2xl font-black text-orange-700">{{ count($rep['ambigu'] ?? []) }}</span>
                            </div>
                            <div class="p-3 bg-rose-50 rounded-2xl border border-rose-100">
                                <span class="text-xs text-rose-600 font-semibold block">Gagal / Dilewati</span>
                                <span class="text-2xl font-black text-rose-700">{{ count($rep['gagal'] ?? []) + count($rep['dilewati'] ?? []) }}</span>
                            </div>
                        </div>

                        @if(!empty($rep['ambigu']))
                            <div class="p-4 bg-orange-50 border border-orange-200 rounded-2xl text-xs space-y-2">
                                <span class="font-bold text-orange-900 flex items-center gap-1.5">
                                    <i class="fas fa-question-circle"></i> Berkas Ambigu (Terdapat lebih dari 1 siswa aktif dengan nama yang sama):
                                </span>
                                <ul class="list-disc list-inside text-orange-800 space-y-1">
                                    @foreach($rep['ambigu'] as $item)
                                        <li><span class="font-mono font-bold">{{ $item }}</span></li>
                                    @endforeach
                                </ul>
                                <p class="text-[11px] text-orange-700 italic">Solusi: Beri nama file menggunakan <b>NIS siswa</b> agar dapat diidentifikasi secara unik.</p>
                            </div>
                        @endif

                        @if(!empty($rep['tidak_ditemukan']))
                            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs space-y-2">
                                <span class="font-bold text-amber-900 flex items-center gap-1.5">
                                    <i class="fas fa-user-slash"></i> Berkas Tidak Ditemukan Siswanya (Tidak disimpan):
                                </span>
                                <div class="max-h-40 overflow-y-auto space-y-1 pr-2 text-amber-800 font-mono">
                                    @foreach($rep['tidak_ditemukan'] as $fn)
                                        <div class="inline-block bg-white px-2 py-0.5 rounded border border-amber-200 mr-1 mb-1 text-[11px]">{{ $fn }}</div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!empty($rep['gagal']))
                            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs space-y-1">
                                <span class="font-bold text-rose-900 flex items-center gap-1.5">
                                    <i class="fas fa-times-circle"></i> Berkas Gagal Diproses:
                                </span>
                                <ul class="list-disc list-inside text-rose-800">
                                    @foreach($rep['gagal'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(!empty($rep['dilewati']))
                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-2xl text-xs space-y-1">
                                <span class="font-bold text-gray-800 flex items-center gap-1.5">
                                    <i class="fas fa-forward"></i> Berkas Dilewati:
                                </span>
                                <ul class="list-disc list-inside text-gray-700">
                                    @foreach($rep['dilewati'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-8">
                        <div class="mb-8 p-6 bg-indigo-50/60 border border-indigo-100 rounded-2xl">
                            <h3 class="font-bold text-indigo-900 mb-2 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-indigo-600"></i> Petunjuk Penamaan Berkas Foto
                            </h3>
                            <ul class="space-y-2 text-xs text-indigo-950 leading-relaxed">
                                <li class="flex items-start gap-2">
                                    <span class="mt-1 w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                                    <span><b>Nama file = nama lengkap siswa (huruf besar/kecil bebas) atau NIS</b>, mis. <code class="bg-white px-1.5 py-0.5 rounded border border-indigo-200 font-mono font-bold text-indigo-700">AHMAD AKHIT MAULANA.jpg</code> atau <code class="bg-white px-1.5 py-0.5 rounded border border-indigo-200 font-mono font-bold text-indigo-700">2324100698.jpg</code>.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="mt-1 w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                                    <span>Format file didukung: <b>JPG, JPEG, PNG, WEBP</b> (maksimal 500 gambar per arsip ZIP).</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="mt-1 w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                                    <span>Batas maksimal ukuran arsip ZIP: <b>{{ $effectiveMaxMB }} MB</b> (maksimal 8 MB per file foto).</span>
                                </li>
                            </ul>
                        </div>

                        <form action="{{ route('admin.students.upload-photos.post') }}" method="POST" enctype="multipart/form-data" id="upload-form">
                            @csrf
                            <div class="mb-8">
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 ml-1">Pilih Arsip ZIP</label>
                                <div class="relative group">
                                    <label for="zip_file" class="flex flex-col items-center justify-center w-full h-56 border-2 border-dashed border-gray-200 rounded-3xl cursor-pointer bg-gray-50/50 hover:bg-indigo-50/30 hover:border-indigo-300 transition-all group-hover:scale-[1.002]">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <div class="w-20 h-20 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center text-indigo-600 mb-4 group-hover:-rotate-3 transition-transform">
                                                <i class="fas fa-file-archive text-3xl"></i>
                                            </div>
                                            <p class="mb-2 text-sm text-gray-700 font-bold" id="file-label">Tekan untuk pilih file ZIP</p>
                                            <p class="text-xs text-gray-500">atau drag & drop di sini</p>
                                        </div>
                                        <input id="zip_file" name="zip_file" type="file" accept=".zip" required class="hidden" onchange="handleZipSelected(this)">
                                    </label>
                                </div>
                                @error('zip_file')
                                    <p class="text-rose-500 text-xs mt-2 font-bold ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" id="submit-btn" class="w-full px-8 py-4 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition shadow-sm hover:shadow-md active:scale-95 flex items-center justify-center gap-2">
                                <i class="fas fa-upload"></i> Mulai Ekstrak & Sinkronkan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Info Cards -->
                <div class="bg-indigo-600 rounded-3xl p-6 text-white shadow-lg shadow-indigo-200">
                    <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center mb-4 backdrop-blur-md">
                        <i class="fas fa-bolt text-xl"></i>
                    </div>
                    <h4 class="font-bold text-lg leading-tight mb-2">Proses Otomatis</h4>
                    <p class="text-indigo-100 text-sm leading-relaxed">Sistem akan me-resize foto otomatis menjadi maksimal 400px untuk menghemat ruang server tanpa mengurangi kualitas tampilan.</p>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
                    <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center mb-4 text-rose-500 border border-rose-100">
                        <i class="fas fa-server text-xl"></i>
                    </div>
                    <h4 class="font-bold text-gray-900 mb-4">Informasi Server</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">max_upload</span>
                            <span class="text-xs font-mono font-bold text-indigo-600">{{ ini_get('upload_max_filesize') }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">post_max</span>
                            <span class="text-xs font-mono font-bold text-indigo-600">{{ ini_get('post_max_size') }}</span>
                        </div>
                    </div>
                    @if($effectiveMaxMB < 20)
                        <div class="mt-4 p-3 bg-rose-50 rounded-xl border border-rose-100 flex items-start gap-2">
                            <i class="fas fa-exclamation-circle text-rose-500 mt-0.5 text-xs"></i>
                            <p class="text-[10px] text-rose-700 leading-tight">Server Anda memiliki limit cukup rendah. Disarankan mengunggah dalam beberapa file ZIP kecil.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        const effectiveLimitBytes = {{ $effectiveMaxBytes }};
        const friendlyLimit = "{{ $effectiveMaxMB }} MB";

        function handleZipSelected(input) {
            const label = document.getElementById('file-label');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                if (file.size > effectiveLimitBytes) {
                    Swal.fire({
                        title: 'File Terlalu Besar!',
                        text: `Ukuran file (${(file.size / (1024 * 1024)).toFixed(2)} MB) melebihi batas server (${friendlyLimit}).`,
                        icon: 'error',
                        confirmButtonColor: '#4f46e5',
                    });
                    input.value = '';
                    label.textContent = 'Tekan untuk pilih file ZIP';
                    return;
                }
                
                label.textContent = file.name;
                label.classList.add('text-indigo-600');
            }
        }

        document.getElementById('upload-form').addEventListener('submit', function() {
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghubungkan ke Server...';
        });

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Data Gagal Diproses!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#f43f5e',
                customClass: { popup: 'rounded-3xl' }
            });
        @endif

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sinkronisasi Berhasil!',
                text: "{{ session('success') }}",
                confirmButtonColor: '#4f46e5',
                customClass: { popup: 'rounded-3xl' }
            });
        @endif
    </script>

    <style>
        .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
@endsection
