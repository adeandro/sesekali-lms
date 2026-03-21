@extends('layouts.app')
@section('title', 'Rata-rata Kelas')
@section('page-title', 'Rata-rata Kelas')

@section('content')
<div class="max-w-6xl mx-auto animate-fadeIn pb-12">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.report-data.index') }}" class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tight">Rata-rata Mata Pelajaran</h2>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1 leading-relaxed">Input manual untuk dicetak di raport tingkat</p>
        </div>
    </div>

    @if($classes->isEmpty() && auth()->user()->role === 'teacher')
    <div class="bg-amber-50 rounded-[2.5rem] border border-amber-100 p-12 text-center mb-12">
        <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center text-amber-500 mx-auto shadow-sm mb-6">
            <i class="fas fa-user-shield text-3xl"></i>
        </div>
        <h3 class="text-xl font-black text-amber-900 uppercase tracking-tight mb-2">Akses Terbatas</h3>
        <p class="text-[10px] font-black text-amber-600 uppercase tracking-[0.2em] max-w-sm mx-auto leading-relaxed">Anda tidak terdaftar sebagai wali kelas manapun. Fitur ini hanya tersedia bagi Wali Kelas atau Superadmin.</p>
    </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 p-6 rounded-[2.5rem] mb-6 text-[10px] font-black border border-red-100 shadow-sm leading-relaxed uppercase tracking-widest">
            <ul class="list-disc list-inside space-y-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        // Fetch existing averages to prefill
        $existingAverages = \App\Models\ClassGradeAverage::where('academic_year', $academicYear)
            ->get()
            ->groupBy(['class_id', 'subject_id'])
            ->map(function ($classGroup) {
                return $classGroup->map(function ($subjectGroup) {
                    return $subjectGroup->first();
                });
            });
    @endphp

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden relative group hover:shadow-xl hover:shadow-amber-500/5 transition-all duration-500">
        <form action="{{ route('admin.report-data.save-class-average') }}" method="POST" id="averageForm">
            @csrf
            
            <div class="p-8 border-b border-gray-50 bg-gray-50/50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tahun Ajaran Aktif</label>
                        <input type="text" name="academic_year" value="{{ old('academic_year', $academicYear) }}" class="w-full h-14 bg-white border border-gray-200 rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-sm cursor-not-allowed" required readonly>
                    </div>
                    <div class="space-y-4">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Semester Target</label>
                        <select name="semester" class="w-full h-14 bg-white border border-gray-200 rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-sm cursor-pointer" required>
                            <option value="ganjil">Ganjil</option>
                            <option value="genap">Genap</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="p-8 overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap border-separate border-spacing-y-2">
                    <thead>
                        <tr>
                            <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 rounded-l-2xl">Mata Pelajaran \ Kelas</th>
                            @foreach ($classes as $class)
                                <th class="py-4 px-6 text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 @if($loop->last) rounded-r-2xl @endif text-center w-32 border-l border-white">{{ $class->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subjects as $subject)
                            <tr class="group/row transition-all duration-300 hover:scale-[1.01]">
                                <td class="py-4 px-6 bg-white border border-gray-100 group-hover/row:border-indigo-100 rounded-l-2xl shadow-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center text-[10px] font-black shadow-inner group-hover/row:bg-indigo-50 group-hover/row:text-indigo-600 transition-colors shrink-0">{{ $loop->iteration }}</div>
                                        <div>
                                            <p class="text-[12px] font-black text-gray-900 truncate max-w-xs uppercase tracking-tight">{{ $subject->name }}</p>
                                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ $subject->category }}</p>
                                        </div>
                                    </div>
                                </td>
                                @foreach ($classes as $class)
                                    @php
                                        // Look up existing value or old input
                                        $dbValue = isset($existingAverages[$class->id]) && isset($existingAverages[$class->id][$subject->id]) ? $existingAverages[$class->id][$subject->id]->class_average : '';
                                        $val = old("averages.{$class->id}.{$subject->id}", $dbValue);
                                    @endphp
                                    <td class="py-4 px-6 text-center bg-white border border-gray-100 border-l-0 group-hover/row:border-indigo-100 @if($loop->last) rounded-r-2xl @endif shadow-sm">
                                        <input type="number" step="0.01" name="averages[{{ $class->id }}][{{ $subject->id }}]" value="{{ $val }}" class="w-24 h-12 bg-gray-50 border-transparent rounded-xl px-4 text-center text-sm font-black text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-200 transition-all mx-auto placeholder-gray-300" placeholder="0.00">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-8 border-t border-gray-50 bg-gray-50/50 flex justify-end">
                <button type="button" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin text-[10px]\'></i> Menyimpan...'; this.classList.add('opacity-70', 'cursor-wait'); setTimeout(() => { document.getElementById('averageForm').submit(); }, 100);" class="h-14 px-10 bg-amber-500 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-amber-600 transition-all duration-300 shadow-lg shadow-amber-200 flex items-center gap-3">
                    <i class="fas fa-save text-[10px]"></i> Simpan Rata-rata Khusus
                </button>
            </div>
        </form>
    </div>
</div>
@endsection