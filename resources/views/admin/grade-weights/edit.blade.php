@extends('layouts.app')

@section('title', 'Edit Bobot Nilai - ' . ($configs['school_name'] ?? 'SesekaliCBT'))
@section('page-title', 'Edit Bobot Nilai')

@section('content')
<div class="max-w-2xl mx-auto space-y-8 animate-fadeIn pb-12">
    {{-- Header --}}
    <div class="flex flex-col gap-2">
        <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
            <a href="{{ route('admin.grade-weights.index') }}" class="hover:text-indigo-600 transition-colors">Bobot Nilai</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-indigo-600">Edit</span>
        </nav>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                <i class="fas fa-edit text-xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Edit Bobot Nilai</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $gradeWeight->subject->name ?? '' }} — Semester {{ $gradeWeight->semester }} / {{ $gradeWeight->academic_year }}</p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.grade-weights.update', $gradeWeight) }}" method="POST" class="space-y-8"
          x-data="{
              harian: {{ old('weight_harian', $gradeWeight->weight_harian) }},
              uts: {{ old('weight_uts', $gradeWeight->weight_uts) }},
              uas: {{ old('weight_uas', $gradeWeight->weight_uas) }},
              get total() { return parseFloat(this.harian || 0) + parseFloat(this.uts || 0) + parseFloat(this.uas || 0); },
              get isValid() { return Math.abs(this.total - 100) < 0.01; }
          }">
        @csrf
        @method('PUT')

        {{-- Mata Pelajaran & Periode --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-book text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Mata Pelajaran & Periode</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tentukan mapel, semester, dan tahun ajaran</p>
                </div>
            </div>
            <div class="p-8 space-y-6">
                {{-- Mata Pelajaran --}}
                <div class="space-y-4">
                    <label for="subject_id" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select id="subject_id" name="subject_id"
                            class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer @error('subject_id') ring-2 ring-rose-500 @enderror"
                            required>
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id', $gradeWeight->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                    @error('subject_id')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                </div>

                {{-- Kelas / Jenjang --}}
                <div class="space-y-4">
                    <label for="jenjang" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Kelas/Jenjang <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select id="jenjang" name="jenjang"
                            class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer @error('jenjang') ring-2 ring-rose-500 @enderror"
                            required>
                            <option value="">Pilih Kelas</option>
                            @foreach(range(7, 12) as $k)
                                <option value="{{ $k }}" {{ old('jenjang', $gradeWeight->jenjang) == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                    @error('jenjang')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Semester --}}
                    <div class="space-y-4">
                        <label for="semester" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Semester <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select id="semester" name="semester"
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer @error('semester') ring-2 ring-rose-500 @enderror"
                                required>
                                <option value="1" {{ old('semester', $gradeWeight->semester) == 1 ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                                <option value="2" {{ old('semester', $gradeWeight->semester) == 2 ? 'selected' : '' }}>Semester 2 (Genap)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        @error('semester')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div class="space-y-4">
                        <label for="academic_year" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input type="text" id="academic_year" name="academic_year"
                            value="{{ old('academic_year', $gradeWeight->academic_year) }}"
                            placeholder="2024/2025" pattern="\d{4}\/\d{4}"
                            class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-gray-300 @error('academic_year') ring-2 ring-rose-500 @enderror"
                            required>
                        @error('academic_year')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Bobot Nilai --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fas fa-balance-scale text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Konfigurasi Bobot</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total ketiga bobot harus = 100%</p>
                </div>
            </div>
            <div class="p-8 space-y-6">
                {{-- Live Total Preview --}}
                <div class="p-5 rounded-2xl border-2 transition-colors duration-300"
                     :class="isValid ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200'">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest" :class="isValid ? 'text-emerald-700' : 'text-rose-700'">Total Bobot</p>
                            <p class="text-3xl font-black mt-1" :class="isValid ? 'text-emerald-600' : 'text-rose-600'" x-text="total.toFixed(0) + '%'"></p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center" :class="isValid ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white'">
                            <i :class="isValid ? 'fas fa-check' : 'fas fa-times'" class="text-xl"></i>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold mt-2 uppercase tracking-wider"
                       :class="isValid ? 'text-emerald-600' : 'text-rose-600'"
                       x-text="isValid ? 'Konfigurasi bobot valid ✓' : 'Sisa: ' + (100 - total).toFixed(0) + '% — Harus tepat 100%'"></p>
                </div>

                @error('weight_harian')
                    <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100">
                        <p class="text-rose-600 text-[10px] font-black uppercase tracking-widest">{{ $message }}</p>
                    </div>
                @enderror

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-4">
                        <label for="weight_harian" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Harian (%) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="number" id="weight_harian" name="weight_harian" x-model="harian"
                                value="{{ old('weight_harian', $gradeWeight->weight_harian) }}"
                                min="0" max="100" step="0.5"
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-300 uppercase tracking-widest">%</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <label for="weight_uts" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">UTS (%) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="number" id="weight_uts" name="weight_uts" x-model="uts"
                                value="{{ old('weight_uts', $gradeWeight->weight_uts) }}"
                                min="0" max="100" step="0.5"
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-300 uppercase tracking-widest">%</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <label for="weight_uas" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">UAS (%) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="number" id="weight_uas" name="weight_uas" x-model="uas"
                                value="{{ old('weight_uas', $gradeWeight->weight_uas) }}"
                                min="0" max="100" step="0.5"
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-300 uppercase tracking-widest">%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse md:flex-row gap-4 pt-4">
            <a href="{{ route('admin.grade-weights.index') }}" class="flex-1 h-14 bg-gray-100 text-gray-500 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-200 transition flex items-center justify-center">
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
