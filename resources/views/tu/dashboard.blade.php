@extends('layouts.app')

@section('title', 'Dashboard TU - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Beranda Tata Usaha')

@section('content')
<div class="max-w-7xl mx-auto space-y-10 animate-fadeIn pb-12">
    <!-- Hero / Welcome Section -->
    <div class="relative bg-indigo-950 rounded-[3rem] p-8 md:p-16 overflow-hidden shadow-2xl">
        <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-indigo-800/20 to-transparent"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-indigo-600/10 rounded-full blur-[100px]"></div>
        
        <div class="relative flex flex-col md:flex-row items-center justify-between gap-12">
            <div class="space-y-6 text-center md:text-left max-w-2xl">
                <div class="inline-flex items-center gap-3 px-4 py-2 bg-indigo-500/10 border border-indigo-400/20 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                    <span class="text-[10px] font-black text-indigo-300 uppercase tracking-[0.2em]">Sistem Administrasi Terpadu</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-white leading-tight tracking-tight uppercase">Selamat Datang, <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-white">{{ Auth::user()->full_name }}</span></h1>
                <p class="text-indigo-200/70 text-sm md:text-base leading-relaxed font-medium">
                    Panel Tata Usaha siap membantu Anda mengelola administrasi surat-menyurat dengan lebih cepat, akurat, dan profesional.
                </p>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 pt-4">
                    <a href="{{ route('admin.letters.index') }}" class="px-8 py-4 bg-white text-indigo-900 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-50 transition-all shadow-lg shadow-white/5">
                        <i class="fas fa-magic mr-2"></i> Buat Surat Baru
                    </a>
                    <a href="{{ route('admin.letters.history') }}" class="px-8 py-4 bg-indigo-800/50 text-white border border-indigo-700/50 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-800 transition-all">
                        <i class="fas fa-history mr-2"></i> Arsip Surat
                    </a>
                </div>
            </div>
            
            <div class="hidden lg:block w-72 h-72 relative">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-3xl rotate-6 opacity-20 animate-pulse"></div>
                <div class="absolute inset-0 bg-white/5 backdrop-blur-3xl rounded-3xl border border-white/10 flex items-center justify-center shadow-2xl">
                    <i class="fas fa-stamp text-7xl text-indigo-300/50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-[2rem] p-8 border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 transition-all">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-paper-plane text-xl"></i>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Total Surat<br>Keluar</p>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-4xl font-black text-gray-900 tracking-tighter">{{ $totalLetters }}</h3>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Surat</span>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] p-8 border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 transition-all">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <i class="fas fa-file-invoice text-xl"></i>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Template<br>Aktif</p>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-4xl font-black text-gray-900 tracking-tighter">{{ $totalTemplates }}</h3>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Model</span>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] p-8 border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 transition-all">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fas fa-calendar-check text-xl"></i>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Surat<br>Bulan Ini</p>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-4xl font-black text-gray-900 tracking-tighter">{{ $recentLetters->count() }}</h3>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Terbit</span>
            </div>
        </div>

         <div class="bg-white rounded-[2rem] p-8 border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 transition-all">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600">
                    <i class="fas fa-user-tie text-xl"></i>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Level Akses<br>Pengguna</p>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-black text-gray-900 tracking-tighter uppercase">Tata Usaha</h3>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Recent Letters -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between px-2">
                <div class="flex items-center gap-4">
                    <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Surat Baru Terbit</h3>
                    <span class="w-8 h-1 bg-indigo-600 rounded-full"></span>
                </div>
                <a href="{{ route('admin.letters.history') }}" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:underline">Lihat Semua</a>
            </div>

            <div class="bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-sm">
                @if($recentLetters->isEmpty())
                    <div class="p-16 text-center">
                        <i class="fas fa-ghost text-4xl text-gray-200 mb-4 block"></i>
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Belum ada aktivitas surat</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nomor & Tanggal</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Penerima</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($recentLetters as $letter)
                                    <tr class="group hover:bg-indigo-50/30 transition-colors">
                                        <td class="px-8 py-6">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-black text-gray-900 uppercase tracking-tight">{{ $letter->letter_number }}</span>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $letter->issued_date->isoFormat('D MMMM YYYY') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 group-hover:bg-white transition-colors">
                                                    <i class="fas fa-{{ $letter->recipient_type === 'student' ? 'user-graduate' : 'user-tie' }} text-xs"></i>
                                                </div>
                                                <span class="text-[11px] font-black text-gray-700 uppercase tracking-tight">{{ $letter->recipient_name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button class="w-9 h-9 rounded-xl bg-white border border-gray-100 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                                    <i class="fas fa-print text-xs"></i>
                                                </button>
                                                <button class="w-9 h-9 rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 transition-all shadow-sm">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Access / Info -->
        <div class="space-y-6">
            <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight px-2">Akses Cepat</h3>
            
            <div class="space-y-4">
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex items-center gap-5 hover:border-indigo-200 transition-all group">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl transition-transform group-hover:rotate-6">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest">Input Data Siswa</h4>
                        <p class="text-[10px] text-gray-400 font-medium leading-relaxed mt-1">Perbarui biodata untuk data surat</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex items-center gap-5 hover:border-amber-200 transition-all group text-left w-full">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl transition-transform group-hover:rotate-6">
                        <i class="fas fa-cloud-download-alt"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest">Unduh Laporan</h4>
                        <p class="text-[10px] text-gray-400 font-medium leading-relaxed mt-1">Rekap data persuratan semester ini</p>
                    </div>
                </div>

                <div class="bg-indigo-600 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-2xl shadow-indigo-200">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-xl"></div>
                    <div class="relative space-y-4">
                        <i class="fas fa-lightbulb text-3xl opacity-50"></i>
                        <h4 class="text-sm font-black uppercase tracking-widest leading-relaxed">Pusat Bantuan</h4>
                        <p class="text-[10px] text-indigo-100 leading-relaxed font-medium">Butuh panduan penggunaan fitur generator surat? Lihat dokumentasi teknis kami.</p>
                        <a href="#" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] border-b-2 border-white/30 hover:border-white transition-all pt-2 text-white">Buka Panduan</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
