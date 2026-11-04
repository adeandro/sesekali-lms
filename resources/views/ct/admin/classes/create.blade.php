@extends('layouts.app')

@section('title', 'Tambah Kelas CT')

@section('page-title', 'Tambah Kelas CT')

@section('content')
<div class="max-w-lg mx-auto space-y-6 animate-fadeIn">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.ct.index') }}" class="text-gray-400 hover:text-indigo-600 transition">
            <i class="fas fa-arrow-left text-lg"></i>
        </a>
        <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">Tambah Kelas CT</h2>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
        <form method="POST" action="{{ route('admin.ct.classes.store') }}" class="space-y-5">
            @csrf

            {{-- Name --}}
            <div>
                <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-1.5">Nama Kelas</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: 10-A"
                       class="w-full border @error('name') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Opens at --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-1.5">Jam Buka</label>
                    <input type="datetime-local" name="opens_at" value="{{ old('opens_at') }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                    @error('opens_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-1.5">Jam Tutup</label>
                    <input type="datetime-local" name="closes_at" value="{{ old('closes_at') }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                    @error('closes_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Active --}}
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       {{ old('is_active', '1') ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 rounded">
                <label for="is_active" class="text-sm font-medium text-gray-700">Kelas aktif (bisa diakses siswa)</label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 text-white font-bold text-sm rounded-xl hover:bg-indigo-700 transition">
                    Simpan
                </button>
                <a href="{{ route('admin.ct.index') }}"
                   class="flex-1 py-2.5 bg-gray-100 text-gray-600 font-bold text-sm rounded-xl hover:bg-gray-200 transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
