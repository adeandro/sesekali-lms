@extends('layouts.app')

@section('title', 'Manajemen Musim')

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 uppercase tracking-tighter flex items-center gap-4">
                <div class="p-3 bg-indigo-500 text-white rounded-2xl shadow-lg shadow-indigo-200">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <span>Manajemen Musim</span>
            </h1>
            <p class="text-gray-500 font-medium mt-2 ml-1">Kelola siklus akademik, reset poin seasonal, dan arsip data musim.</p>
        </div>
        <a href="{{ route('admin.gamification.seasons.create') }}"
           class="inline-flex items-center justify-center gap-3 px-6 py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-black rounded-2xl shadow-xl shadow-indigo-200 hover:shadow-indigo-300 hover:-translate-y-1 active:translate-y-0 transition-all uppercase tracking-widest">
            <i class="fas fa-plus-circle text-lg"></i>
            <span>Musim Baru</span>
        </a>
    </div>

    {{-- Table Container --}}
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-2xl shadow-gray-200/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Informasi Musim</th>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Rentang Waktu</th>
                        <th class="px-8 py-5 text-center text-[11px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-center text-[11px] font-black text-gray-400 uppercase tracking-widest">Aksi Utama</th>
                        <th class="px-8 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest">Kelola</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($seasons as $season)
                        <tr class="group hover:bg-gray-50/80 transition-colors {{ $season->status === 'active' ? 'bg-indigo-50/30' : '' }}">
                            {{-- Info --}}
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400 group-hover:bg-white group-hover:text-indigo-500 transition-all shadow-sm group-hover:shadow-md">
                                        <i class="fas fa-leaf text-xl {{ $season->status === 'active' ? 'text-indigo-500' : '' }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-black text-gray-900 text-base leading-tight">{{ $season->name }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest bg-gray-100 px-2 py-0.5 rounded-md group-hover:bg-white transition-colors">
                                                {{ $season->semester_type }}
                                            </span>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest bg-gray-100 px-2 py-0.5 rounded-md group-hover:bg-white transition-colors">
                                                {{ $season->academic_year }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Periode --}}
                            <td class="px-8 py-6">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 text-gray-700 font-bold">
                                        <i class="fas fa-play-circle text-[10px] text-gray-400"></i>
                                        <span>{{ $season->started_at?->translatedFormat('d F Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-400 font-medium text-xs">
                                        <i class="fas fa-stop-circle text-[10px]"></i>
                                        <span>{{ $season->closed_at?->translatedFormat('d F Y') ?? 'Berjalan...' }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-8 py-6 text-center">
                                @if($season->status === 'active')
                                    <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-widest shadow-sm border border-emerald-200 animate-pulse">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Musim Aktif
                                    </div>
                                @else
                                    <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-gray-100 text-gray-500 rounded-full text-[10px] font-black uppercase tracking-widest border border-gray-200">
                                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                        Arsip
                                    </div>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-8 py-6">
                                <div class="flex flex-col items-center gap-2">
                                    @if($season->status === 'active')
                                        {{-- Close Action --}}
                                        <form action="{{ route('admin.gamification.seasons.close', $season) }}" method="POST" class="no-loading w-full"
                                              onsubmit="return confirm('Tutup season ini? Data Hall of Fame akan dibuat dan seasonal EXP akan di-reset (ketik RESET untuk konfirmasi).')">
                                            @csrf
                                            <div class="flex flex-col gap-2 p-3 bg-rose-50 rounded-2xl border border-rose-100 shadow-sm">
                                                <input type="text" name="confirmation" placeholder="reset / RESET" 
                                                       class="w-full px-3 py-1.5 text-[10px] font-bold border-rose-200 rounded-xl uppercase tracking-tighter focus:ring-rose-500 focus:border-rose-500 placeholder:text-rose-300" required>
                                                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 text-[10px] font-black bg-rose-500 text-white hover:bg-rose-600 rounded-xl uppercase tracking-widest transition-all shadow-md shadow-rose-200 hover:shadow-rose-300">
                                                    <i class="fas fa-redo"></i> Reset & Selesaikan
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <div class="flex flex-wrap justify-center gap-2">
                                            {{-- Activate --}}
                                            <form action="{{ route('admin.gamification.seasons.activate', $season) }}" method="POST" class="no-loading"
                                                  onsubmit="return confirm('Aktifkan kembali season ini? Season aktif saat ini akan ditutup.')">
                                                @csrf
                                                <button type="submit" class="px-4 py-2 bg-indigo-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 transition shadow-md shadow-indigo-100 scale-100 active:scale-95">
                                                    <i class="fas fa-bolt mr-1"></i> Aktivasi
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Management --}}
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.gamification.seasons.edit', $season) }}" 
                                       class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if($season->status !== 'active')
                                        <form action="{{ route('admin.gamification.seasons.destroy', $season) }}" method="POST" class="no-loading inline"
                                              onsubmit="return confirm('Hapus permanen data season ini? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-24 text-center">
                                <div class="relative inline-block">
                                    <div class="absolute inset-0 bg-indigo-100 rounded-full blur-2xl opacity-50 pulse"></div>
                                    <i class="fas fa-calendar-times text-6xl text-indigo-300 relative"></i>
                                </div>
                                <p class="text-gray-400 font-bold mt-6 text-lg tracking-tight">Belum ada season terdaftar.</p>
                                <p class="text-gray-400 text-sm">Silakan buat season pertama untuk memulai siklus gamifikasi.</p>
                                <a href="{{ route('admin.gamification.seasons.create') }}" class="mt-8 inline-flex items-center gap-3 px-8 py-3 bg-indigo-500 text-white text-xs font-black rounded-2xl uppercase tracking-[0.2em] shadow-lg shadow-indigo-200 hover:shadow-indigo-300 hover:-translate-y-1 transition-all">
                                    <i class="fas fa-plus"></i> Buat Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($seasons->hasPages())
            <div class="px-8 py-6 bg-gray-50/30 border-t border-gray-100">
                {{ $seasons->links() }}
            </div>
        @endif
    </div>

    {{-- Footer/Info Card --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-indigo-50 p-6 rounded-[2rem] border border-indigo-100">
            <h4 class="font-black text-indigo-900 text-xs uppercase tracking-widest mb-2">Penutupan Season</h4>
            <p class="text-[11px] text-indigo-600 leading-relaxed font-medium">Menutup season akan mengambil snapshot skor untuk **Hall of Fame** dan mereset seasonal EXP seluruh siswa.</p>
        </div>
        <div class="bg-amber-50 p-6 rounded-[2rem] border border-amber-100">
            <h4 class="font-black text-amber-900 text-xs uppercase tracking-widest mb-2">Migrasi Tahunan</h4>
            <p class="text-[11px] text-amber-600 leading-relaxed font-medium">Jika Anda ingin menaikkan tingkat kelas siswa (contoh: Kelas 10 → Kelas 11), silakan buka menu **Siswa > Migrasi Tahunan**.</p>
        </div>
        <div class="bg-emerald-50 p-6 rounded-[2rem] border border-emerald-100">
            <h4 class="font-black text-emerald-900 text-xs uppercase tracking-widest mb-2">Siklus Akademik</h4>
            <p class="text-[11px] text-emerald-600 leading-relaxed font-medium">Satu season merepresentasikan satu semester. Pastikan Tahun Akademik diinput dengan format yang benar (contoh: 2023/2024).</p>
        </div>
    </div>

</div>
@endsection
