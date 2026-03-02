@extends('layouts.app')

@section('title', 'Hasil Impor - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Hasil Impor Data')

@section('content')
    <div class="max-w-6xl mx-auto space-y-8 animate-fadeIn">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('admin.students.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">
                                <i class="fas fa-users mr-2"></i> Manajemen Siswa
                            </a>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-300 text-xs mx-2"></i>
                                <span class="text-sm font-bold text-indigo-600">Hasil Impor</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                    <i class="fas fa-clipboard-check text-indigo-600"></i> Laporan Proses Impor
                </h2>
                @if(isset($duration))
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-2 flex items-center gap-2">
                        <i class="far fa-clock text-indigo-400"></i> Selesai dalam <span class="text-indigo-600">{{ $duration }} detik</span>
                    </p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.students.importForm') }}" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 font-bold rounded-2xl hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                    <i class="fas fa-redo-alt"></i> Impor Lagi
                </a>
                <a href="{{ route('admin.students.index') }}" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition shadow-md shadow-indigo-100 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Selesai
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-3xl p-6 border-b-4 border-emerald-500 shadow-sm transition-transform hover:scale-[1.02]">
                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">Berhasil</p>
                <p class="text-4xl font-black text-gray-900">{{ $success_count }}</p>
                <p class="text-xs text-gray-400 mt-1 font-medium italic">Siswa baru ditambahkan</p>
            </div>

            <div class="bg-white rounded-3xl p-6 border-b-4 border-amber-500 shadow-sm transition-transform hover:scale-[1.02]">
                <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">Dilewati</p>
                <p class="text-4xl font-black text-gray-900">{{ $skipped_count }}</p>
                <p class="text-xs text-gray-400 mt-1 font-medium italic">Sudah ada di sistem</p>
            </div>

            <div class="bg-white rounded-3xl p-6 border-b-4 border-rose-500 shadow-sm transition-transform hover:scale-[1.02]">
                <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest mb-1">Gagal</p>
                <p class="text-4xl font-black text-gray-900">{{ $failure_count }}</p>
                <p class="text-xs text-gray-400 mt-1 font-medium italic">Data tidak valid</p>
            </div>

            <div class="bg-white rounded-3xl p-6 border-b-4 border-indigo-500 shadow-sm transition-transform hover:scale-[1.02]">
                <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-1">Total</p>
                <p class="text-4xl font-black text-gray-900">{{ $success_count + $skipped_count + $failure_count }}</p>
                <p class="text-xs text-gray-400 mt-1 font-medium italic">Baris diproses</p>
            </div>
        </div>

        @if($success_count > 0)
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex items-center justify-between bg-emerald-50/30">
                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        Siswa Berhasil Ditambahkan ({{ $success_count }})
                    </h3>
                    <button onclick="window.print()" class="text-xs font-bold text-emerald-700 hover:underline">
                        <i class="fas fa-print mr-1"></i> Cetak Laporan
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">NIS</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama Lengkap</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Kelas</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Password Default</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($students as $item)
                                <tr class="hover:bg-indigo-50/30 transition-colors">
                                    <td class="px-8 py-4 text-sm font-bold text-gray-900">{{ $item['student']->nis }}</td>
                                    <td class="px-8 py-4 text-sm font-medium text-gray-600">{{ $item['student']->name }}</td>
                                    <td class="px-8 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black bg-indigo-100 text-indigo-700 uppercase tracking-tight">
                                            {{ $item['student']->grade }} - {{ $item['student']->class_group }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-4">
                                        <code class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-xl font-mono text-xs font-bold border border-gray-200">
                                            {{ $item['password'] }}
                                        </code>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-6 bg-amber-50 border-t border-amber-100 flex items-start gap-4 mx-8 mb-8 mt-4 rounded-2xl">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-amber-500 shadow-sm border border-amber-100 shrink-0">
                        <i class="fas fa-lock text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-amber-900">Catatan Keamanan!</p>
                        <p class="text-xs text-amber-700 leading-relaxed mt-1">Simpan daftar password di atas sekarang. Password hanya ditampilkan sekali ini saja dan sistem mengenkripsinya demi keamanan. Bagikan kepada masing-masing siswa untuk login.</p>
                    </div>
                </div>
            </div>
        @endif

        @if($skipped_count > 0)
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex items-center gap-3 bg-amber-50/30">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                        <i class="fas fa-forward"></i>
                    </div>
                    <h3 class="text-lg font-black text-gray-900">Data Dilewati ({{ $skipped_count }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Baris</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">NIS</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($skipped as $item)
                                <tr class="hover:bg-amber-50/30 transition-colors">
                                    <td class="px-8 py-4 text-xs font-black text-gray-400">#{{ $item['row'] }}</td>
                                    <td class="px-8 py-4 text-sm font-bold text-gray-900">{{ $item['nis'] }}</td>
                                    <td class="px-8 py-4 text-sm text-gray-600">{{ $item['name'] }}</td>
                                    <td class="px-8 py-4">
                                        <span class="text-xs font-bold text-amber-600 bg-amber-100 px-3 py-1 rounded-lg">
                                            {{ $item['reason'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($failure_count > 0)
            <div class="bg-rose-50 rounded-[2rem] border border-rose-100 p-8">
                <h3 class="text-lg font-black text-rose-900 flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-rose-600 shadow-sm">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    Data Gagal Diimpor ({{ $failure_count }})
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($errors as $error_item)
                        <div class="bg-white border border-rose-100 p-5 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Baris Excel #{{ $error_item['row'] }}</span>
                                <i class="fas fa-times-circle text-rose-400"></i>
                            </div>
                            <div class="space-y-2">
                                @foreach($error_item['errors'] as $field => $message)
                                    <div class="flex items-start gap-2">
                                        <div class="w-1.5 h-1.5 bg-rose-300 rounded-full mt-1.5 shrink-0"></div>
                                        <p class="text-xs text-gray-700 italic leading-relaxed">
                                            <b class="text-rose-600 uppercase tracking-tighter">{{ $field }}:</b> {{ $message }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <style>
        .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @media print {
            body * { visibility: hidden; }
            .animate-fadeIn, .animate-fadeIn * { visibility: visible; }
            .animate-fadeIn { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none; }
        }
    </style>
@endsection
