@extends('layouts.app')

@section('title', 'Detail Pertemuan: ' . $session->topic)

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <a href="{{ route('admin.extracurriculars.sessions.index', $extracurricular) }}?academic_year={{ $session->academic_year }}&semester={{ $session->semester }}" 
               class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:text-emerald-500 hover:bg-emerald-50 transition-all">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <nav class="flex items-center gap-2 mb-1">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Jurnal & Presensi</span>
                    <i class="fas fa-chevron-right text-[8px] text-gray-300"></i>
                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">{{ $session->date->format('d/m/Y') }}</span>
                </nav>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight uppercase flex items-center gap-3">
                    {{ $session->topic }}
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <form action="{{ route('admin.extracurriculars.sessions.destroy', [$extracurricular, $session]) }}" 
                  method="POST" 
                  onsubmit="return confirm('Hapus data pertemuan ini? Seluruh data presensi akan ikut terhapus.')"
                  class="inline">
                @csrf @method('DELETE')
                <button type="submit" 
                        class="flex items-center gap-2 px-6 py-3 bg-rose-50 text-rose-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-rose-100 transition-all">
                    <i class="fas fa-trash-alt"></i> Hapus Data
                </button>
            </form>
        </div>
    </div>

    {{-- Info Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        {{-- Metadata --}}
        <div class="lg:col-span-1 space-y-8">
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-8">
                <div>
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-4">Informasi</h3>
                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-gray-50 text-gray-400 rounded-xl flex items-center justify-center">
                                <i class="fas fa-calendar-day text-xs"></i>
                            </div>
                            <div>
                                <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Tanggal</div>
                                <div class="text-[11px] font-bold text-gray-900">{{ $session->date->translatedFormat('d F Y') }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-gray-50 text-gray-400 rounded-xl flex items-center justify-center">
                                <i class="fas fa-layer-group text-xs"></i>
                            </div>
                            <div>
                                <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Semester</div>
                                <div class="text-[11px] font-bold text-gray-900">{{ $session->semester }} ({{ $session->semester == 1 ? 'Ganjil' : 'Genap' }})</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-gray-50 text-gray-400 rounded-xl flex items-center justify-center">
                                <i class="fas fa-user-edit text-xs"></i>
                            </div>
                            <div>
                                <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Dicatat Oleh</div>
                                <div class="text-[11px] font-bold text-gray-900">{{ $session->creator->name }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($session->notes)
                    <div class="pt-8 border-t border-gray-50">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-4">Catatan Jurnal</h3>
                        <div class="bg-gray-50 p-5 rounded-2xl">
                            <p class="text-[11px] text-gray-600 leading-relaxed italic">"{{ $session->notes }}"</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Coach Attendance Small Table --}}
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-6 text-center">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Pembina</h3>
                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[8px] font-black rounded uppercase tracking-tighter">Presence</span>
                </div>
                <div class="space-y-3">
                    @forelse($coachAttendances as $ca)
                        <div class="p-4 bg-gray-50 rounded-2xl flex items-center justify-between">
                            <div class="text-left">
                                <div class="text-[10px] font-bold text-gray-900 truncate max-w-[120px]">{{ $ca->coach->name }}</div>
                                <div class="text-[7px] font-black text-gray-400 uppercase tracking-widest">Oleh: {{ $ca->recorder->name }}</div>
                            </div>
                            @if($ca->status === 'hadir')
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-600 text-[8px] font-black rounded uppercase tracking-widest">Hadir</span>
                            @else
                                <span class="px-2 py-1 bg-rose-100 text-rose-600 text-[8px] font-black rounded uppercase tracking-widest">Absen</span>
                            @endif
                        </div>
                    @empty
                        <div class="py-6 text-center">
                            <p class="text-[9px] font-black text-gray-300 uppercase tracking-widest">Tidak ada data</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Student Attendance Detail --}}
        <div class="lg:col-span-3 space-y-8">
            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-emerald-50 p-6 rounded-[2rem] border border-emerald-100">
                    <div class="text-emerald-400 text-xl mb-2"><i class="fas fa-check-circle"></i></div>
                    <div class="text-[24px] font-black text-emerald-600 line-height-1">{{ $studentAttendances->where('status', 'hadir')->count() }}</div>
                    <div class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Siswa Hadir</div>
                </div>
                <div class="bg-blue-50 p-6 rounded-[2rem] border border-blue-100">
                    <div class="text-blue-400 text-xl mb-2"><i class="fas fa-info-circle"></i></div>
                    <div class="text-[24px] font-black text-blue-600 line-height-1">{{ $studentAttendances->where('status', 'izin')->count() }}</div>
                    <div class="text-[9px] font-black text-blue-500 uppercase tracking-widest">Siswa Izin</div>
                </div>
                <div class="bg-amber-50 p-6 rounded-[2rem] border border-amber-100">
                    <div class="text-amber-400 text-xl mb-2"><i class="fas fa-plus-square"></i></div>
                    <div class="text-[24px] font-black text-amber-600 line-height-1">{{ $studentAttendances->where('status', 'sakit')->count() }}</div>
                    <div class="text-[9px] font-black text-amber-500 uppercase tracking-widest">Siswa Sakit</div>
                </div>
                <div class="bg-rose-50 p-6 rounded-[2rem] border border-rose-100">
                    <div class="text-rose-400 text-xl mb-2"><i class="fas fa-times-circle"></i></div>
                    <div class="text-[24px] font-black text-rose-600 line-height-1">{{ $studentAttendances->where('status', 'alfa')->count() }}</div>
                    <div class="text-[9px] font-black text-rose-500 uppercase tracking-widest">Siswa Alfa</div>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-gray-50">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Detail Presensi Siswa</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-50">
                            <tr>
                                <th class="px-8 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest">Siswa</th>
                                <th class="px-8 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest">Kelas</th>
                                <th class="px-8 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                                <th class="px-8 py-5 text-[9px] font-black text-gray-400 uppercase tracking-widest">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($studentAttendances as $sa)
                                <tr class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-6">
                                        <div class="text-xs font-black text-gray-900 tracking-tight">{{ $sa->student->name }}</div>
                                        <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ $sa->student->nis ?? '-' }}</div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-[10px] font-bold text-gray-700 tracking-tight uppercase">{{ $sa->student->classroom->name ?? '-' }}</div>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        @php
                                            $color = match($sa->status) {
                                                'hadir' => 'bg-emerald-100 text-emerald-600',
                                                'izin'  => 'bg-blue-100 text-blue-600',
                                                'sakit' => 'bg-amber-100 text-amber-600',
                                                'alfa'  => 'bg-rose-100 text-rose-600',
                                            };
                                        @endphp
                                        <span class="px-3 py-1.5 {{ $color }} text-[10px] font-black rounded-xl uppercase tracking-widest">
                                            {{ $sa->status }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-[10px] text-gray-500 italic">{{ $sa->note ?? '-' }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
