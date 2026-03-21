@extends('layouts.app')

@section('title', 'Import Nilai Excel - ' . ($configs['school_name'] ?? 'SesekaliCBT'))
@section('page-title', 'Import Nilai Excel')

@section('content')
<div class="max-w-2xl mx-auto space-y-8 animate-fadeIn pb-12">
    {{-- Header --}}
    <div class="flex flex-col gap-2">
        <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
            <a href="{{ route('admin.manual-grades.index') }}" class="hover:text-indigo-600 transition-colors">Nilai Manual</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-indigo-600">Import Excel</span>
        </nav>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-100">
                <i class="fas fa-file-excel text-xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Import Nilai Excel</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Upload file Excel untuk input nilai massal</p>
            </div>
        </div>
    </div>

    {{-- Import Summary (after import) --}}
    @if(session('import_summary'))
        @php $s = session('import_summary'); @endphp
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fas fa-chart-bar text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Hasil Import</h3>
                </div>
            </div>
            <div class="p-8 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-5 bg-emerald-50 rounded-2xl text-center">
                        <p class="text-3xl font-black text-emerald-600">{{ $s['success'] }}</p>
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mt-1">Berhasil</p>
                    </div>
                    <div class="p-5 bg-gray-50 rounded-2xl text-center">
                        <p class="text-3xl font-black text-gray-400">{{ $s['skipped'] }}</p>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">Dilewati</p>
                    </div>
                </div>
                @if(!empty($s['errors']))
                    <div class="p-5 bg-rose-50 rounded-2xl border border-rose-100 space-y-2">
                        <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest">Error / Peringatan</p>
                        @foreach($s['errors'] as $err)
                            <div class="flex items-start gap-2">
                                <i class="fas fa-exclamation-circle text-rose-400 text-xs mt-0.5"></i>
                                <p class="text-xs text-rose-700 font-bold">Baris {{ $err['row'] }}: {{ $err['reason'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Filter params (pre-fill from redirect) --}}
    <form action="{{ route('admin.manual-grades.import') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        {{-- Mapel & Periode --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-book text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Mapel & Periode</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tentukan mapel, kelas, semester, dan tahun ajaran</p>
                </div>
            </div>
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Mapel --}}
                    <div class="space-y-3 md:col-span-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select name="subject_id" class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer @error('subject_id') ring-2 ring-rose-500 @enderror" required>
                                <option value="">Pilih Mata Pelajaran</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ ($selectedSubject?->id == $subject->id || old('subject_id') == $subject->id) ? 'selected' : '' }}>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400"><i class="fas fa-chevron-down text-xs"></i></div>
                        </div>
                        @error('subject_id')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>

                    {{-- Kelas --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Kelas <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select name="class_id" class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer @error('class_id') ring-2 ring-rose-500 @enderror" required>
                                <option value="">Pilih Kelas</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ ($selectedClass?->id == $class->id || old('class_id') == $class->id) ? 'selected' : '' }}>{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400"><i class="fas fa-chevron-down text-xs"></i></div>
                        </div>
                        @error('class_id')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>

                    {{-- Semester --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Semester <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select name="semester" class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer @error('semester') ring-2 ring-rose-500 @enderror" required>
                                <option value="">Pilih Semester</option>
                                <option value="1" {{ ($semester == 1 || old('semester') == 1) ? 'selected' : '' }}>Semester 1</option>
                                <option value="2" {{ ($semester == 2 || old('semester') == 2) ? 'selected' : '' }}>Semester 2</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-400"><i class="fas fa-chevron-down text-xs"></i></div>
                        </div>
                        @error('semester')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div class="space-y-3 md:col-span-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input type="text" name="academic_year" value="{{ old('academic_year', $academicYear ?? ($configs['academic_year'] ?? '')) }}"
                            placeholder="2024/2025"
                            class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-gray-300 @error('academic_year') ring-2 ring-rose-500 @enderror"
                            required>
                        @error('academic_year')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Format Panduan --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <i class="fas fa-info-circle text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Format File Excel</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Gunakan template yang disediakan untuk hasil terbaik</p>
                </div>
            </div>
            <div class="p-8 space-y-4">
                <div class="overflow-x-auto rounded-2xl border border-gray-100">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-black text-gray-500 uppercase tracking-wider">Kolom A</th>
                                <th class="px-4 py-3 text-left font-black text-gray-500 uppercase tracking-wider">Kolom B</th>
                                <th class="px-4 py-3 text-left font-black text-gray-500 uppercase tracking-wider">Kolom C</th>
                                <th class="px-4 py-3 text-left font-black text-gray-500 uppercase tracking-wider">Kolom D</th>
                                <th class="px-4 py-3 text-left font-black text-gray-500 uppercase tracking-wider">Kolom E</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-t border-gray-100">
                                <td class="px-4 py-3 font-bold text-gray-700">NIS</td>
                                <td class="px-4 py-3 font-bold text-gray-700">Nama Siswa</td>
                                <td class="px-4 py-3 font-bold text-gray-700">Nilai Harian</td>
                                <td class="px-4 py-3 font-bold text-gray-700">Nilai UTS</td>
                                <td class="px-4 py-3 font-bold text-gray-700">Nilai UAS</td>
                            </tr>
                            <tr class="border-t border-gray-50 text-gray-400">
                                <td class="px-4 py-2">1234567</td>
                                <td class="px-4 py-2">Ahmad Rizki</td>
                                <td class="px-4 py-2">85</td>
                                <td class="px-4 py-2">78</td>
                                <td class="px-4 py-2">80</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <ul class="space-y-2 text-xs text-gray-500">
                    <li class="flex items-start gap-2"><i class="fas fa-check-circle text-emerald-500 mt-0.5"></i> Data dimulai dari baris 7 (sesuai template)</li>
                    <li class="flex items-start gap-2"><i class="fas fa-check-circle text-emerald-500 mt-0.5"></i> NIS harus sesuai dengan data siswa yang terdaftar</li>
                    <li class="flex items-start gap-2"><i class="fas fa-check-circle text-emerald-500 mt-0.5"></i> Nilai dikosongkan jika belum ada — jangan isi dengan 0</li>
                    <li class="flex items-start gap-2"><i class="fas fa-exclamation-circle text-amber-500 mt-0.5"></i> Nilai dari CBT tidak akan tertimpa oleh import ini</li>
                    <li class="flex items-start gap-2"><i class="fas fa-info-circle text-indigo-400 mt-0.5"></i> Format yang didukung: .xlsx, .xls, .csv (max 5MB)</li>
                </ul>
            </div>
        </div>

        {{-- Upload --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fas fa-upload text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Upload File</h3>
                </div>
            </div>
            <div class="p-8">
                <label for="file" class="block space-y-4">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">File Excel <span class="text-rose-500">*</span></span>
                    <div class="flex flex-col items-center justify-center w-full min-h-[160px] bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer p-6 group">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 group-hover:text-indigo-400 transition-colors mb-3"></i>
                        <p class="text-sm font-black text-gray-400 group-hover:text-indigo-600 transition-colors">Klik atau drag & drop file Excel di sini</p>
                        <p class="text-[10px] font-bold text-gray-300 mt-1 uppercase tracking-widest">.xlsx · .xls · .csv · maks 5MB</p>
                    </div>
                    <input id="file" type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden" required>
                </label>
                @error('file')<p class="text-rose-500 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse md:flex-row gap-4">
            <a href="{{ route('admin.manual-grades.input') }}" class="flex-1 h-14 bg-gray-100 text-gray-500 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-200 transition flex items-center justify-center">
                Batal & Kembali
            </a>
            <button type="submit" class="flex-[2] h-14 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-100 flex items-center justify-center gap-3 group">
                <i class="fas fa-file-import group-hover:scale-110 transition-transform"></i> Mulai Import
            </button>
        </div>
    </form>
</div>

<script>
    // Show filename when file selected
    document.getElementById('file')?.addEventListener('change', function(e) {
        const label = this.closest('label').querySelector('p');
        if (label && this.files[0]) {
            label.textContent = this.files[0].name;
            label.classList.add('text-indigo-700');
        }
    });
</script>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
