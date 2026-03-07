@extends('layouts.app')

@section('title', 'Tambah Guru - SesekaliCBT')

@section('page-title', 'Tambah Guru')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('superadmin.teachers.index') }}" class="text-gray-600 hover:text-gray-900 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="text-3xl font-bold text-gray-900">Tambah Guru Baru</h2>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
            <form action="{{ route('superadmin.teachers.store') }}" method="POST" class="p-8 space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Gelar Depan -->
                    <div class="space-y-2">
                        <label for="title_ahead" class="block text-sm font-semibold text-gray-700">Gelar Depan</label>
                        <input type="text" name="title_ahead" id="title_ahead" value="{{ old('title_ahead') }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title_ahead') border-red-500 @enderror">
                        @error('title_ahead') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Nama -->
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-semibold text-gray-700">Nama Lengkap</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Gelar Belakang -->
                    <div class="space-y-2">
                        <label for="title_behind" class="block text-sm font-semibold text-gray-700">Gelar Belakang</label>
                        <input type="text" name="title_behind" id="title_behind" value="{{ old('title_behind') }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title_behind') border-red-500 @enderror">
                        @error('title_behind') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-semibold text-gray-700">Email Utama</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- NIP/NIS -->
                    <div class="space-y-2">
                        <label for="nis" class="block text-sm font-semibold text-gray-700">NIP / Kode Guru</label>
                        <input type="text" name="nis" id="nis" value="{{ old('nis') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nis') border-red-500 @enderror">
                        @error('nis') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Mata Pelajaran -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Mata Pelajaran yang Diampu</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 border rounded-lg p-3 max-h-48 overflow-y-auto">
                            @foreach($subjects as $subject)
                                <label class="flex items-center space-x-2 text-sm">
                                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" 
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        {{ in_array($subject->id, old('subject_ids', [])) ? 'checked' : '' }}>
                                    <span>{{ $subject->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('subject_ids')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                        <input type="password" name="password" id="password" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="space-y-2">
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Ulangi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Status Aktif -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="is_active" class="text-sm font-medium text-gray-700">Akun ini aktif</label>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end gap-3">
                    <a href="{{ route('superadmin.teachers.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Batal</a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold shadow-blue-200 shadow-lg">Simpan Guru</button>
                </div>
            </form>
        </div>
    </div>
@endsection
