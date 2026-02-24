@extends('layouts.app')

@section('title', 'Tambah Siswa - SesekaliCBT')

@section('content')
    <div>
        <div class="mb-8">
            <a href="{{ route('admin.students.index') }}" class="text-blue-600 hover:text-blue-800">← Kembali ke Daftar Siswa</a>
            <h2 class="text-3xl font-bold text-gray-900 mt-2">Tambah Siswa Baru</h2>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="font-semibold text-red-800 mb-2">Kesalahan Validasi:</p>
                <ul class="list-disc list-inside text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-8 max-w-2xl">
            <form action="{{ route('admin.students.store') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="nis" class="block text-sm font-medium text-gray-700 mb-2">
                        NIS <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="nis" 
                        name="nis" 
                        value="{{ old('nis') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nis') border-red-500 @enderror"
                        placeholder="misalnya, 001"
                        required
                    >
                    @error('nis')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                        placeholder="Nama siswa"
                        required
                    >
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="grade" class="block text-sm font-medium text-gray-700 mb-2">
                            Kelas <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="grade" 
                            name="grade" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('grade') border-red-500 @enderror"
                            required
                        >
                            <option value="">Pilih Kelas</option>
                            <option value="10" {{ old('grade') == '10' ? 'selected' : '' }}>10</option>
                            <option value="11" {{ old('grade') == '11' ? 'selected' : '' }}>11</option>
                            <option value="12" {{ old('grade') == '12' ? 'selected' : '' }}>12</option>
                        </select>
                        @error('grade')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="class_group" class="block text-sm font-medium text-gray-700 mb-2">
                            Rombel <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="class_group" 
                            name="class_group" 
                            value="{{ old('class_group') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('class_group') border-red-500 @enderror"
                            placeholder="misalnya, A, B, C"
                            required
                        >
                        @error('class_group')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-gray-500">(opsional)</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                        placeholder="siswa@example.com"
                    >
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-800">
                        <strong>Catatan:</strong> Password yang aman akan dibuat otomatis untuk siswa ini. Anda akan melihatnya setelah pembuatan.
                    </p>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Buat Siswa
                    </button>
                    <a href="{{ route('admin.students.index') }}" class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
