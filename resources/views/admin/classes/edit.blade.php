@extends('layouts.app')

@section('title', 'Edit Kelas - ' . ($configs['school_name'] ?? 'SesekaliCBT'))
@section('page-title', 'Edit Kelas')

@section('content')
<div class="max-w-2xl mx-auto space-y-8 animate-fadeIn pb-12">
    {{-- Header --}}
    <div class="flex flex-col gap-2">
        <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
            <a href="{{ route('admin.classes.index') }}" class="hover:text-indigo-600 transition-colors">Kelas</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-indigo-600">Edit: {{ $class->name }}</span>
        </nav>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                <i class="fas fa-edit text-xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Edit: {{ $class->name }}</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kelas {{ $class->grade }} — {{ $class->academic_year }}</p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.classes.update', $class) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        {{-- Info Kelas --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-school text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Informasi Kelas</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nama kelas, tingkat, dan periode aktif</p>
                </div>
            </div>
            <div class="p-8 space-y-6">
                {{-- Nama --}}
                <div class="space-y-4">
                    <label for="name" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nama Kelas <span class="text-rose-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $class->name) }}"
                        class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-gray-300 @error('name') ring-2 ring-rose-500 @enderror"
                        required>
                    @error('name')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Grade --}}
                    <div class="space-y-4">
                        <label for="grade" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tingkat <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select id="grade" name="grade"
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer @error('grade') ring-2 ring-rose-500 @enderror"
                                required>
                                <option value="10" {{ old('grade', $class->grade) == '10' ? 'selected' : '' }}>Kelas 10 (X)</option>
                                <option value="11" {{ old('grade', $class->grade) == '11' ? 'selected' : '' }}>Kelas 11 (XI)</option>
                                <option value="12" {{ old('grade', $class->grade) == '12' ? 'selected' : '' }}>Kelas 12 (XII)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        @error('grade')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>

                    {{-- Section --}}
                    <div class="space-y-4">
                        <label for="section" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Jurusan / Section</label>
                        <input type="text" id="section" name="section" value="{{ old('section', $class->section) }}"
                            class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-gray-300">
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div class="space-y-4">
                        <label for="academic_year" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input type="text" id="academic_year" name="academic_year"
                            value="{{ old('academic_year', $class->academic_year) }}"
                            placeholder="2024/2025" pattern="\d{4}\/\d{4}"
                            class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-gray-300 @error('academic_year') ring-2 ring-rose-500 @enderror"
                            required>
                        @error('academic_year')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>

                    {{-- Kapasitas --}}
                    <div class="space-y-4">
                        <label for="capacity" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Kapasitas Siswa</label>
                        <div class="relative">
                            <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $class->capacity) }}"
                                min="1" max="100"
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-300 uppercase tracking-widest">Siswa</span>
                        </div>
                    </div>
                </div>

                {{-- Status Aktif --}}
                <label class="group relative flex items-center p-6 bg-gray-50 rounded-[2rem] border-2 border-transparent hover:border-indigo-400 hover:bg-white transition-all duration-200 cursor-pointer overflow-hidden shadow-sm hover:shadow-lg">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $class->is_active) ? 'checked' : '' }} class="hidden peer">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mr-5 shadow-sm transition-colors duration-200 bg-gray-300 text-gray-500 peer-checked:bg-indigo-600 peer-checked:text-white">
                        <i class="fas fa-toggle-on"></i>
                    </div>
                    <div class="flex flex-col flex-1">
                        <span class="text-sm font-black text-gray-900 leading-tight">Kelas Aktif</span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mt-1">Centang jika kelas ini aktif di tahun ajaran ini</span>
                    </div>
                    <div class="relative w-12 h-6 shrink-0 bg-gray-300 peer-checked:bg-indigo-600 rounded-full transition-colors duration-200 after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:bg-white after:rounded-full after:shadow-sm after:transition-transform after:duration-200 peer-checked:after:translate-x-6"></div>
                </label>
            </div>
        </div>

        {{-- Wali Kelas --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fas fa-chalkboard-teacher text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Wali Kelas</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Assign wali kelas (opsional)</p>
                </div>
            </div>
            <div class="p-8">
                <div class="space-y-4">
                    <label for="homeroom_teacher_id" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Pilih Wali Kelas</label>
                    <div class="relative">
                        <select id="homeroom_teacher_id" name="homeroom_teacher_id"
                            class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer @error('homeroom_teacher_id') ring-2 ring-rose-500 @enderror">
                            <option value="">— Belum Ditentukan —</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('homeroom_teacher_id', $class->homeroom_teacher_id) == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                    @error('homeroom_teacher_id')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse md:flex-row gap-4 pt-4">
            <a href="{{ route('admin.classes.index') }}" class="flex-1 h-14 bg-gray-100 text-gray-500 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-200 transition flex items-center justify-center">
                Batal & Kembali
            </a>
            <button type="submit" class="flex-[2] h-14 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center justify-center gap-3 group">
                <i class="fas fa-save text-[10px] group-hover:scale-110 transition-transform"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
