@extends('layouts.app')

@section('title', 'Edit Ekskul: ' . $extracurricular->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
        <div class="flex items-center gap-5 mb-10">
            <a href="{{ route('admin.extracurriculars.index') }}" 
               class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:text-emerald-500 hover:bg-emerald-50 transition-all">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight uppercase">Edit Ekstrakurikuler</h1>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Ubah identitas dasar ekstrakurikuler</p>
            </div>
        </div>

        <form action="{{ route('admin.extracurriculars.update-detail', $extracurricular) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="space-y-3">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nama Ekstrakurikuler</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-gray-300 group-focus-within:text-emerald-500 transition-colors">
                        <i class="fas fa-running text-sm"></i>
                    </div>
                    <input type="text" name="name" value="{{ old('name', $extracurricular->name) }}" required
                           placeholder="Contoh: Basket, Musik, Pramuka"
                           class="w-full pl-14 pr-6 py-4 bg-gray-50 border-none rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-500 transition-all">
                </div>
                @error('name')
                    <p class="text-red-500 text-[10px] font-black uppercase tracking-widest italic ml-1 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-3">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Deskripsi Singkat</label>
                <textarea name="description" rows="5" 
                          placeholder="Jelaskan mengenai kegiatan ini..."
                          class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-500 transition-all leading-relaxed">{{ old('description', $extracurricular->description) }}</textarea>
                <p class="text-[9px] text-gray-400 font-medium ml-1">Maksimum 500 karakter.</p>
                @error('description')
                    <p class="text-red-500 text-[10px] font-black uppercase tracking-widest italic ml-1 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-4 pt-6">
                <a href="{{ route('admin.extracurriculars.index') }}"
                   class="flex-1 py-4 rounded-2xl bg-gray-100 text-gray-500 text-[10px] font-black uppercase tracking-widest text-center hover:bg-gray-200 transition-all">
                    Batal
                </a>
                <button type="submit"
                        class="flex-1 py-4 rounded-2xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
