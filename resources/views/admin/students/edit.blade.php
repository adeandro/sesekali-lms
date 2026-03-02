@extends('layouts.app')

@section('title', 'Ubah Siswa - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Ubah Siswa')

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
                            <span class="text-sm font-bold text-indigo-600">Ubah Data Siswa</span>
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
                <div class="mb-8 flex items-center gap-4">
                    <div class="h-16 w-16 bg-indigo-50 rounded-2xl flex items-center justify-center border border-indigo-100 shadow-sm overflow-hidden">
                        <img src="{{ $student->photo_url }}" alt="Profile" class="h-full w-full object-cover">
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Ubah Data Siswa</h2>
                        <p class="text-sm text-gray-500 mt-1">Memperbarui informasi untuk <span class="font-bold text-gray-700">{{ $student->name }}</span> ({{ $student->nis }})</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mb-8 p-4 bg-rose-50 border border-rose-100 rounded-2xl flex items-start gap-3 animate-headShake">
                        <i class="fas fa-exclamation-circle text-rose-500 mt-1"></i>
                        <div>
                            <p class="font-bold text-rose-800 text-sm">Gagal Menyimpan Perubahan:</p>
                            <ul class="mt-1 list-disc list-inside text-rose-700 text-xs space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.students.update', $student) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Photo Upload -->
                    <div class="space-y-2">
                        <label for="photo" class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Foto Profil</label>
                        <div class="flex items-center gap-6 p-4 bg-gray-50/50 rounded-2xl border border-dashed border-gray-300 hover:border-indigo-400 transition-all group">
                            <div class="h-20 w-20 bg-white rounded-2xl overflow-hidden flex items-center justify-center border border-gray-200 shadow-sm relative group-hover:scale-105 transition-transform">
                                <img id="photo-preview" src="{{ $student->photo_url }}" alt="Preview" class="h-full w-full object-cover">
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
                                <p class="text-[10px] text-gray-400">Pilih file baru untuk mengganti foto saat ini. Format: JPG, PNG, WEBP. Maks 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- NIS (Readonly) -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1 opacity-60">NIS (ID Siswa)</label>
                            <div class="px-5 py-3 bg-gray-100 border border-gray-200 rounded-2xl text-gray-500 text-sm font-mono flex items-center justify-between">
                                {{ $student->nis }}
                                <i class="fas fa-lock text-xs opacity-30"></i>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1 ml-1">Nomor Induk Siswa tidak dapat diubah setelah didaftarkan.</p>
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
                                value="{{ old('name', $student->name) }}"
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
                                <option value="" disabled>Pilih Kelas</option>
                                <option value="10" {{ old('grade', $student->grade) == '10' ? 'selected' : '' }}>10</option>
                                <option value="11" {{ old('grade', $student->grade) == '11' ? 'selected' : '' }}>11</option>
                                <option value="12" {{ old('grade', $student->grade) == '12' ? 'selected' : '' }}>12</option>
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
                                value="{{ old('class_group', $student->class_group) }}"
                                placeholder="misal: MIPA 1, IPS 2, atau A, B..."
                                required
                                class="w-full px-5 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all text-sm @error('class_group') border-rose-300 ring-2 ring-rose-100 @enderror"
                            >
                        </div>
                    </div>

                    <!-- Account Status -->
                    <div class="bg-gray-50/50 rounded-2xl p-5 border border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="bg-white p-2.5 rounded-xl border border-gray-100 shadow-sm text-indigo-600">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 leading-tight">Status Akses Akun</p>
                                <p class="text-xs text-gray-500 mt-1">Aktifkan atau nonaktifkan hak login siswa ke sistem CBT.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                value="1" 
                                class="sr-only peer" 
                                {{ old('is_active', $student->is_active) ? 'checked' : '' }}
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none ring-0 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <div class="pt-4 flex items-center gap-3">
                        <button type="submit" class="flex-1 md:flex-none px-8 py-3 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition shadow-sm hover:shadow-md active:scale-95 flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Simpan Perubahan
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
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
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
