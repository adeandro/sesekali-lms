@extends('layouts.app')

@section('title', 'Tambah Siswa - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Tambah Siswa')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6 animate-fadeIn">
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
                            <span class="text-sm font-bold text-indigo-600">Tambah Siswa</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <a href="{{ route('admin.students.index') }}" class="text-sm font-bold text-gray-500 hover:text-rose-600 transition-colors flex items-center gap-1">
                <i class="fas fa-times-circle"></i> Batal
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Tambah Siswa Baru</h2>
                    <p class="text-sm text-gray-500 mt-1">Lengkapi informasi di bawah ini untuk mendaftarkan siswa baru ke sistem.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-8 p-4 bg-rose-50 border border-rose-100 rounded-2xl flex items-start gap-3 animate-headShake">
                        <i class="fas fa-exclamation-circle text-rose-500 mt-1"></i>
                        <div>
                            <p class="font-bold text-rose-800 text-sm">Gagal Menyimpan Data:</p>
                            <ul class="mt-1 list-disc list-inside text-rose-700 text-xs space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Photo Upload -->
                    <div class="space-y-2">
                        <label for="photo" class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Foto Profil (Opsional)</label>
                        <div class="flex items-center gap-6 p-4 bg-gray-50/50 rounded-2xl border border-dashed border-gray-300 hover:border-indigo-400 transition-all group">
                            <div class="h-20 w-20 bg-white rounded-2xl overflow-hidden flex items-center justify-center border border-gray-200 shadow-sm relative group-hover:scale-105 transition-transform">
                                <i class="fas fa-user text-3xl text-gray-300" id="photo-placeholder"></i>
                                <img id="photo-preview" src="#" alt="Preview" class="hidden h-full w-full object-cover">
                            </div>
                            <div class="flex-1 space-y-1">
                                <input 
                                    type="file" 
                                    id="photo" 
                                    name="photo" 
                                    accept="image/*"
                                    onchange="previewImage(this)"
                                    class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all"
                                >
                                <p class="text-[10px] text-gray-400">Format: JPG, PNG, WEBP. Ukuran maks 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- NIS -->
                        <div class="space-y-2">
                            <label for="nis" class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1 flex items-center gap-1">
                                NIS <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="nis" 
                                name="nis" 
                                value="{{ old('nis') }}"
                                placeholder="Masukkan NIS siswa..."
                                required
                                class="w-full px-5 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all text-sm @error('nis') border-rose-300 ring-2 ring-rose-100 @enderror"
                            >
                        </div>

                        <!-- Name -->
                        <div class="space-y-2">
                            <label for="name" class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1 flex items-center gap-1">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}"
                                placeholder="Masukkan nama lengkap siswa..."
                                required
                                class="w-full px-5 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all text-sm @error('name') border-rose-300 ring-2 ring-rose-100 @enderror"
                            >
                        </div>

                        <!-- Grade -->
                        <div class="space-y-2">
                            <label for="grade" class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1 flex items-center gap-1">
                                Tingkat Kelas <span class="text-rose-500">*</span>
                            </label>
                            <select 
                                id="grade" 
                                name="grade" 
                                required
                                class="w-full px-5 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all text-sm appearance-none @error('grade') border-rose-300 ring-2 ring-rose-100 @enderror"
                            >
                                <option value="" disabled selected>Pilih Kelas</option>
                                <option value="10" {{ old('grade') == '10' ? 'selected' : '' }}>10</option>
                                <option value="11" {{ old('grade') == '11' ? 'selected' : '' }}>11</option>
                                <option value="12" {{ old('grade') == '12' ? 'selected' : '' }}>12</option>
                            </select>
                        </div>

                        <!-- Class Group -->
                        <div class="space-y-2">
                            <label for="class_group" class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1 flex items-center gap-1">
                                Rombongan Belajar <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="class_group" 
                                name="class_group" 
                                value="{{ old('class_group') }}"
                                placeholder="misal: MIPA 1, IPS 2, atau A, B..."
                                required
                                class="w-full px-5 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all text-sm @error('class_group') border-rose-300 ring-2 ring-rose-100 @enderror"
                            >
                        </div>
                    </div>

                    <div class="bg-indigo-50 rounded-2xl p-5 flex items-start gap-4 border border-indigo-100">
                        <div class="bg-indigo-100 p-2 rounded-xl">
                            <i class="fas fa-shield-alt text-indigo-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-indigo-900 leading-tight">Keamanan Akun Otomatis</p>
                            <p class="text-xs text-indigo-700 mt-1 leading-relaxed">Sistem akan membuatkan password acak yang aman secara otomatis. Anda dapat memberikan password tersebut kepada siswa setelah data berhasil disimpan.</p>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center gap-3">
                        <button type="submit" class="flex-1 md:flex-none px-8 py-3 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition shadow-sm hover:shadow-md active:scale-95 flex items-center justify-center gap-2">
                            <i class="fas fa-check-circle"></i> Simpan Data Siswa
                        </button>
                        <a href="{{ route('admin.students.index') }}" class="flex-1 md:flex-none px-8 py-3 bg-gray-100 text-gray-600 font-bold rounded-2xl hover:bg-gray-200 transition text-center flex items-center justify-center gap-2">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('photo-preview');
            const placeholder = document.getElementById('photo-placeholder');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.classList.add('hidden');
                placeholder.classList.remove('hidden');
            }
        }
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
