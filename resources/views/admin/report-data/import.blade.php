@extends('layouts.app')
@section('title', 'Import Data Raport Khusus')
@section('page-title', 'Import Data Khusus')

@section('content')
<div class="max-w-4xl mx-auto animate-fadeIn pb-12">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.report-data.index') }}" class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tight">Import Data Raport</h2>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed mt-1">Upload file excel multi-sheet kehadiran, kepribadian, ekskul</p>
        </div>
    </div>

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-600 px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-sm mb-6">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 p-6 rounded-[2rem] mb-6 text-[10px] font-black tracking-widest border border-red-100 shadow-sm leading-relaxed uppercase">
            <ul class="list-disc list-inside space-y-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden relative group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
        <div class="p-8">
            <form action="{{ route('admin.report-data.import') }}" method="POST" enctype="multipart/form-data" class="space-y-8" id="importForm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tahun Ajaran</label>
                        <input type="text" name="academic_year" value="{{ old('academic_year', $academicYear) }}" class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-not-allowed" required readonly>
                    </div>
                    <div class="space-y-4">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Semester</label>
                        <select name="semester" class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" required cursor-pointer>
                            <option value="ganjil">Ganjil</option>
                            <option value="genap">Genap</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-4 pt-4">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">File Excel Data Khusus</label>
                    <div class="relative border-2 border-dashed border-gray-200 rounded-[2rem] p-12 flex flex-col items-center justify-center hover:border-indigo-400 transition-colors bg-gray-50/50 group/upload cursor-pointer" onclick="document.getElementById('file-upload').click()">
                        <div class="w-16 h-16 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center text-indigo-500 mb-4 group-hover/upload:scale-110 group-hover/upload:bg-indigo-600 group-hover/upload:text-white transition-all duration-300">
                            <i class="fas fa-file-excel text-2xl"></i>
                        </div>
                        <input id="file-upload" name="file" type="file" class="hidden" accept=".xlsx,.xls,.csv" onchange="document.getElementById('file-name').textContent = this.files[0].name; document.getElementById('file-name').classList.add('text-indigo-600', 'font-black')" required>
                        <p class="text-[12px] font-black text-gray-700 uppercase tracking-widest mb-2 group-hover/upload:text-indigo-600 transition-colors">Klik untuk Upload File</p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest" id="file-name">Maksimal 10MB (.xlsx)</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between pt-8 border-t border-gray-50 gap-6 mt-8">
                    <a href="{{ route('admin.report-data.download-template') }}" class="inline-flex items-center text-indigo-600 text-[10px] font-black uppercase tracking-widest hover:text-indigo-800 transition-colors bg-indigo-50 px-6 py-4 rounded-xl">
                        <i class="fas fa-download mr-3 text-sm"></i> Download Template
                    </a>
                    <button type="submit" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin text-[10px]\'></i> Memproses...'; this.classList.add('opacity-70', 'cursor-wait'); setTimeout(() => { document.getElementById('importForm').submit(); }, 100);" class="h-14 px-10 bg-gray-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-600 transition-all duration-300 shadow-lg shadow-gray-200 flex items-center gap-3">
                        <i class="fas fa-upload text-[10px]"></i> Proses Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection