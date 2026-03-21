@extends('layouts.app')

@section('title', 'Bobot Nilai - ' . ($configs['school_name'] ?? 'SesekaliCBT'))
@section('page-title', 'Konfigurasi Bobot Nilai')

@section('content')
<div class="space-y-8 animate-fadeIn">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                <i class="fas fa-balance-scale text-xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Bobot Nilai</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Konfigurasi persentase bobot Harian / UTS / UAS per mapel</p>
            </div>
        </div>
        <a href="{{ route('admin.grade-weights.create') }}"
           class="inline-flex items-center gap-2 h-12 px-6 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
            <i class="fas fa-plus-circle"></i>
            Tambah Konfigurasi
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                <i class="fas fa-list-alt text-sm"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Daftar Konfigurasi</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $weights->total() }} konfigurasi tersaimpan</p>
            </div>
        </div>

        @if($weights->isEmpty())
            <div class="p-16 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center text-gray-300 mx-auto mb-4">
                    <i class="fas fa-balance-scale text-2xl"></i>
                </div>
                <p class="text-sm font-black text-gray-400 uppercase tracking-widest">Belum ada konfigurasi bobot nilai</p>
                <a href="{{ route('admin.grade-weights.create') }}" class="mt-4 inline-flex items-center gap-2 text-indigo-600 text-xs font-bold hover:underline">
                    <i class="fas fa-plus-circle"></i> Tambah sekarang
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-50">
                            <th class="px-8 py-4 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Mata Pelajaran</th>
                            <th class="px-6 py-4 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Semester</th>
                            <th class="px-6 py-4 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Tahun Ajaran</th>
                            <th class="px-6 py-4 text-center text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Harian %</th>
                            <th class="px-6 py-4 text-center text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">UTS %</th>
                            <th class="px-6 py-4 text-center text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">UAS %</th>
                            <th class="px-6 py-4 text-center text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Total</th>
                            @if(auth()->user()->role === 'superadmin')
                                <th class="px-6 py-4 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Guru</th>
                            @endif
                            <th class="px-8 py-4 text-right text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($weights as $weight)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-8 py-5">
                                <span class="text-sm font-black text-gray-900">{{ $weight->subject->name ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="inline-flex px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded-full uppercase tracking-widest">
                                    Sem {{ $weight->semester }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-sm font-bold text-gray-600">{{ $weight->academic_year }}</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="text-sm font-black text-gray-800">{{ number_format($weight->weight_harian, 0) }}%</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="text-sm font-black text-gray-800">{{ number_format($weight->weight_uts, 0) }}%</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="text-sm font-black text-gray-800">{{ number_format($weight->weight_uas, 0) }}%</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                @php $total = $weight->total_weight; @endphp
                                <span class="inline-flex px-3 py-1 text-[10px] font-black rounded-full uppercase tracking-widest {{ abs($total - 100) < 0.01 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ number_format($total, 0) }}%
                                </span>
                            </td>
                            @if(auth()->user()->role === 'superadmin')
                                <td class="px-6 py-5">
                                    <span class="text-xs font-bold text-gray-500">{{ $weight->teacher->name ?? '—' }}</span>
                                </td>
                            @endif
                            <td class="px-8 py-5 text-right">
                                <a href="{{ route('admin.grade-weights.edit', $weight) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-100 transition-colors">
                                    <i class="fas fa-edit text-[9px]"></i> Edit
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($weights->hasPages())
                <div class="p-6 border-t border-gray-50">
                    {{ $weights->links() }}
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
