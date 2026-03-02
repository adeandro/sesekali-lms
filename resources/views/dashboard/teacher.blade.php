@extends('layouts.app')

@section('title', 'Beranda Guru - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('content')
<div class="space-y-10 pb-12">
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900 rounded-[3rem] p-10 md:p-14 text-white shadow-2xl shadow-indigo-100/50">
        <!-- Abstract Decoration -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 bg-indigo-400/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col md:flex-row items-center justify-between gap-10">
            <div class="space-y-6 text-center md:text-left">
                <div class="space-y-2">
                    <p class="text-indigo-200 font-black uppercase tracking-[0.4em] text-[10px] opacity-80 italic">Portal Pendidik Terpusat</p>
                    <h1 class="text-3xl md:text-5xl font-black leading-tight uppercase tracking-wider">
                        Selamat Datang, Guru!
                    </h1>
                    <p class="text-indigo-100/80 text-sm font-bold tracking-wide max-w-xl">
                        Kelola evaluasi akademik dan pantau perkembangan kompetensi siswa mata pelajaran <span class="text-white underline decoration-white/30">{{ $stats['subject_names'] }}</span> secara instan.
                    </p>
                </div>
                
                <div class="flex flex-wrap justify-center md:justify-start gap-4">
                    <a href="{{ route('admin.exams.create') }}" class="px-8 py-4 bg-white text-indigo-700 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl hover:bg-indigo-50 transition-all flex items-center gap-2">
                        <i class="fas fa-plus"></i> Ujian Baru
                    </a>
                    <a href="{{ route('admin.monitor-exams.index') }}" class="px-8 py-4 bg-indigo-500/30 backdrop-blur-md border border-white/20 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-white/10 transition-all flex items-center gap-2">
                        <i class="fas fa-eye"></i> Monitor Real-time
                    </a>
                </div>
            </div>

            <div class="hidden md:block relative">
                <div class="w-48 h-48 bg-white/10 backdrop-blur-2xl border border-white/20 rounded-[3rem] flex items-center justify-center shadow-2xl rotate-3 hover:rotate-0 transition-transform duration-500">
                    <i class="fas fa-microchip text-7xl text-white opacity-40"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Collection -->
        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-24 h-24 bg-emerald-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            <div class="relative space-y-4">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-[1.25rem] flex items-center justify-center text-xl transition-transform group-hover:scale-110">
                    <i class="fas fa-database"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Koleksi Soal</p>
                    <p class="text-3xl font-black text-gray-900 tracking-tighter">{{ $stats['total_questions'] }}</p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('admin.questions.index') }}" class="text-[8px] font-black text-emerald-600 uppercase tracking-widest hover:underline flex items-center gap-1">
                        Kelola Bank Soal <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Exams -->
        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-24 h-24 bg-indigo-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            <div class="relative space-y-4">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-[1.25rem] flex items-center justify-center text-xl transition-transform group-hover:scale-110">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Ujian Aktif</p>
                    <p class="text-3xl font-black text-gray-900 tracking-tighter">{{ $stats['total_exams'] }}</p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('admin.exams.index') }}" class="text-[8px] font-black text-indigo-600 uppercase tracking-widest hover:underline flex items-center gap-1">
                        Daftar Paket Ujian <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Participation -->
        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-24 h-24 bg-amber-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            <div class="relative space-y-4">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-[1.25rem] flex items-center justify-center text-xl transition-transform group-hover:scale-110">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Siswa Terlibat</p>
                    <p class="text-3xl font-black text-gray-900 tracking-tighter">{{ $stats['total_students'] }}</p>
                </div>
                <div class="pt-2">
                    <span class="text-[8px] font-black text-amber-600 uppercase tracking-widest">Sinkronisasi LMS</span>
                </div>
            </div>
        </div>

        <!-- Results / Remedial -->
        <div class="bg-indigo-900 rounded-[2.5rem] p-8 shadow-xl shadow-indigo-100 relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-24 h-24 bg-white/5 rounded-full"></div>
            <div class="relative h-full flex flex-col justify-between">
                <div class="w-12 h-12 bg-rose-500/20 text-rose-300 rounded-[1.25rem] flex items-center justify-center text-xl">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-indigo-300 uppercase tracking-widest mb-1">Siswa Remidial</p>
                    <p class="text-3xl font-black text-white tracking-tighter">{{ $stats['total_remedial'] }}</p>
                </div>
                <div class="pt-4">
                    <a href="{{ route('admin.results.index') }}" class="w-full py-3 bg-white text-indigo-900 rounded-xl text-[8px] font-black uppercase tracking-widest text-center block hover:bg-indigo-50 transition-colors shadow-lg">
                        Buka Detail Nilai
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Management Modules Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Content Management -->
        <div class="bg-white rounded-[3rem] p-10 border border-gray-100 shadow-sm space-y-8">
            <div class="space-y-2">
                <h3 class="text-xl font-black text-gray-900 uppercase tracking-wider flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-emerald-500 rounded-full"></span>
                    Pengembangan Materi
                </h3>
                <p class="text-xs font-bold text-gray-400 leading-relaxed italic">Kelola aset digital dan struktur soal ujian secara mandiri.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('admin.questions.index') }}" class="group p-6 bg-gray-50 hover:bg-white border border-gray-100 hover:border-emerald-100 rounded-[2rem] transition-all hover:shadow-xl hover:shadow-emerald-50 text-left space-y-4">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">Katalog Soal</p>
                        <p class="text-xs font-bold text-gray-600">Import/Export & Bank Soal.</p>
                    </div>
                </a>
                <a href="{{ route('admin.exams.index') }}" class="group p-6 bg-gray-50 hover:bg-white border border-gray-100 hover:border-indigo-100 rounded-[2rem] transition-all hover:shadow-xl hover:shadow-indigo-50 text-left space-y-4">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-1">Paket Ujian</p>
                        <p class="text-xs font-bold text-gray-600">Manajemen Sesi & Durasi.</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Monitoring & Control -->
        <div class="bg-white rounded-[3rem] p-10 border border-gray-100 shadow-sm space-y-8">
            <div class="space-y-2">
                <h3 class="text-xl font-black text-gray-900 uppercase tracking-wider flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-amber-500 rounded-full"></span>
                    Keamanan & Monitoring
                </h3>
                <p class="text-xs font-bold text-gray-400 leading-relaxed italic">Pantau aktivitas siswa dan kendalikan sesi ujian secara real-time.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('admin.tokens.index') }}" class="group p-6 bg-gray-50 hover:bg-white border border-gray-100 hover:border-amber-100 rounded-[2rem] transition-all hover:shadow-xl hover:shadow-amber-50 text-left space-y-4">
                    <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-key"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">Token Akses</p>
                        <p class="text-xs font-bold text-gray-600">Gating Sesi Global.</p>
                    </div>
                </a>
                <a href="{{ route('admin.monitor-exams.index') }}" class="group p-6 bg-gray-50 hover:bg-white border border-gray-100 hover:border-rose-100 rounded-[2rem] transition-all hover:shadow-xl hover:shadow-rose-50 text-left space-y-4">
                    <div class="relative w-12 h-12 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-desktop"></i>
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 border-2 border-white rounded-full"></span>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest mb-1">Monitoring</p>
                        <p class="text-xs font-bold text-gray-600">Track Kecurangan & Fokus.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="bg-emerald-900 rounded-[2.5rem] p-8 md:p-12 text-white shadow-2xl shadow-emerald-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="relative flex flex-col md:flex-row items-center gap-8 text-center md:text-left">
            <div class="w-20 h-20 bg-white/10 backdrop-blur-xl border border-white/20 rounded-[1.5rem] flex items-center justify-center text-3xl shadow-2xl">
                <i class="fas fa-lightbulb text-amber-300"></i>
            </div>
            <div class="space-y-1 flex-1">
                <h4 class="text-lg font-black uppercase tracking-widest">Tips Manajemen Sesi</h4>
                <p class="text-sm font-bold text-emerald-100 leading-relaxed italic opacity-80">"Gunakan fitur Refresh Token setiap 20 menit untuk memastikan keamanan maksimal selama ujian berlangsung."</p>
            </div>
            <div>
                <a href="{{ route('admin.tokens.index') }}" class="px-8 py-4 bg-emerald-500 hover:bg-emerald-400 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-xl">
                    Perbarui Token
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
