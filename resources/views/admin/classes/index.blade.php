@extends('layouts.app')

@section('title', 'Manajemen Kelas - ' . ($configs['school_name'] ?? 'SesekaliCBT'))
@section('page-title', 'Manajemen Kelas')

@section('content')
<div class="space-y-8 animate-fadeIn">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                <i class="fas fa-school text-xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Manajemen Kelas</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kelola kelas, wali kelas, dan data siswa</p>
            </div>
        </div>
        <a href="{{ route('admin.classes.create') }}"
           class="inline-flex items-center gap-2 h-12 px-6 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
            <i class="fas fa-plus-circle"></i>
            Tambah Kelas
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                <i class="fas fa-list text-sm"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Daftar Kelas</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $classes->total() }} kelas terdaftar</p>
            </div>
        </div>

        @if($classes->isEmpty())
            <div class="p-16 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center text-gray-300 mx-auto mb-4">
                    <i class="fas fa-school text-2xl"></i>
                </div>
                <p class="text-sm font-black text-gray-400 uppercase tracking-widest">Belum ada kelas terdaftar</p>
                <a href="{{ route('admin.classes.create') }}" class="mt-4 inline-flex items-center gap-2 text-indigo-600 text-xs font-bold hover:underline">
                    <i class="fas fa-plus-circle"></i> Tambah kelas sekarang
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-50">
                            <th class="px-8 py-4 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Nama Kelas</th>
                            <th class="px-6 py-4 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Grade</th>
                            <th class="px-6 py-4 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Wali Kelas</th>
                            <th class="px-6 py-4 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Tahun Ajaran</th>
                            <th class="px-6 py-4 text-center text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Siswa</th>
                            <th class="px-6 py-4 text-center text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Status</th>
                            <th class="px-8 py-4 text-right text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($classes as $class)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-8 py-5">
                                <span class="text-sm font-black text-gray-900">{{ $class->name }}</span>
                                @if($class->section)
                                    <span class="ml-2 text-[10px] text-gray-400 font-bold">{{ $class->section }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                <span class="inline-flex px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded-full uppercase tracking-widest">
                                    Kelas {{ $class->grade }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                @if($class->homeroomTeacher)
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-black">
                                            {{ substr($class->homeroomTeacher->name, 0, 1) }}
                                        </div>
                                        <span class="text-sm font-bold text-gray-700">{{ $class->homeroomTeacher->name }}</span>
                                    </div>
                                @else
                                    <span class="text-[10px] font-bold text-gray-300 uppercase tracking-widest">Belum ditentukan</span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-sm font-bold text-gray-600">{{ $class->academic_year }}</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="text-sm font-black text-gray-800">{{ $class->students_count }}</span>
                                @if($class->capacity)
                                    <span class="text-[10px] text-gray-400">/{{ $class->capacity }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-center">
                                @if($class->is_active)
                                    <span class="inline-flex px-3 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-full uppercase tracking-widest">Aktif</span>
                                @else
                                    <span class="inline-flex px-3 py-1 bg-gray-50 text-gray-400 text-[10px] font-black rounded-full uppercase tracking-widest">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.classes.edit', $class) }}"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-100 transition-colors">
                                        <i class="fas fa-edit text-[9px]"></i> Edit
                                    </a>

                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($classes->hasPages())
                <div class="p-6 border-t border-gray-50">
                    {{ $classes->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
