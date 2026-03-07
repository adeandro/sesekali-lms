@extends('layouts.app')

@section('title', 'Beranda Guru - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('content')
<div class="space-y-10 pb-12">
    {{-- Hero Section --}}
    <div class="relative overflow-hidden rounded-[3rem] p-10 md:p-14 text-white shadow-2xl theme-soft-shadow"
         style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-dark));">
        {{-- Abstract Decoration --}}
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col md:flex-row items-center justify-between gap-10">
            <div class="space-y-6 text-center md:text-left">
                <div class="space-y-2">
                    <p class="font-black uppercase tracking-[0.4em] text-[10px] opacity-70 italic" style="color: var(--brand-text-accent);">Portal Pendidik Terpusat</p>
                    <h1 class="text-3xl md:text-5xl font-black leading-tight uppercase tracking-wider">
                        Selamat Datang, Guru!
                    </h1>
                    <p class="text-sm font-bold tracking-wide max-w-xl opacity-80">
                        Kelola evaluasi akademik dan pantau perkembangan kompetensi siswa mata pelajaran <span class="text-white underline decoration-white/30">{{ $stats['subject_names'] }}</span> secara instan.
                    </p>
                </div>
                
                <div class="flex flex-wrap justify-center md:justify-start gap-4">
                    <a href="{{ route('admin.exams.create') }}" class="px-8 py-4 bg-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl hover:scale-[1.03] transition-all flex items-center gap-2" style="color: var(--brand-primary);">
                        <i class="fas fa-plus"></i> Ujian Baru
                    </a>
                    <a href="{{ route('admin.monitor-exams.index') }}" class="px-8 py-4 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-white/20 transition-all flex items-center gap-2">
                        <i class="fas fa-eye"></i> Monitor Real-time
                    </a>
                </div>
            </div>

            <div class="hidden md:block relative">
                <div class="w-44 h-44 bg-white/10 backdrop-blur-2xl border border-white/20 rounded-[3rem] flex items-center justify-center shadow-2xl rotate-3 hover:rotate-0 transition-transform duration-500">
                    <i class="fas fa-microchip text-7xl text-white opacity-40"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Collection --}}
        <div class="bg-white rounded-[2.5rem] p-8 border theme-soft-shadow relative overflow-hidden group theme-card-hover" style="border-color: var(--brand-glow);">
            <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700" style="background-color: var(--brand-glow);"></div>
            <div class="relative space-y-4">
                <div class="w-12 h-12 rounded-[1.25rem] flex items-center justify-center text-xl transition-transform group-hover:scale-110" style="background-color: var(--brand-glow); color: var(--brand-primary);">
                    <i class="fas fa-database"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Koleksi Soal</p>
                    <p class="text-3xl font-black tracking-tighter" style="color: var(--brand-text);">{{ $stats['total_questions'] }}</p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('admin.questions.index') }}" class="text-[8px] font-black uppercase tracking-widest hover:underline flex items-center gap-1" style="color: var(--brand-primary);">Kelola Bank Soal <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        {{-- Exams --}}
        <div class="bg-white rounded-[2.5rem] p-8 border theme-soft-shadow relative overflow-hidden group theme-card-hover" style="border-color: var(--brand-glow);">
            <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700" style="background-color: var(--brand-glow);"></div>
            <div class="relative space-y-4">
                <div class="w-12 h-12 rounded-[1.25rem] flex items-center justify-center text-xl transition-transform group-hover:scale-110" style="background-color: var(--brand-glow); color: var(--brand-primary);">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Ujian Aktif</p>
                    <p class="text-3xl font-black tracking-tighter" style="color: var(--brand-text);">{{ $stats['total_exams'] }}</p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('admin.exams.index') }}" class="text-[8px] font-black uppercase tracking-widest hover:underline flex items-center gap-1" style="color: var(--brand-primary);">Daftar Paket Ujian <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        {{-- Participation --}}
        <div class="bg-white rounded-[2.5rem] p-8 border theme-soft-shadow relative overflow-hidden group theme-card-hover" style="border-color: var(--brand-glow);">
            <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700" style="background-color: var(--brand-glow);"></div>
            <div class="relative space-y-4">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-[1.25rem] flex items-center justify-center text-xl transition-transform group-hover:scale-110">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Siswa Terlibat</p>
                    <p class="text-3xl font-black tracking-tighter" style="color: var(--brand-text);">{{ $stats['total_students'] }}</p>
                </div>
                <div class="pt-2">
                    <span class="text-[8px] font-black text-amber-600 uppercase tracking-widest">Sinkronisasi LMS</span>
                </div>
            </div>
        </div>

        {{-- Results / Remedial --}}
        <div class="rounded-[2.5rem] p-8 shadow-xl relative overflow-hidden group" style="background: linear-gradient(135deg, var(--brand-dark), color-mix(in srgb, var(--brand-dark) 80%, black));">
            <div class="absolute -top-6 -right-6 w-24 h-24 bg-white/5 rounded-full"></div>
            <div class="relative h-full flex flex-col justify-between gap-4">
                <div class="w-12 h-12 bg-rose-500/20 text-rose-300 rounded-[1.25rem] flex items-center justify-center text-xl">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-white/50 uppercase tracking-widest mb-1">Siswa Remidial</p>
                    <p class="text-3xl font-black text-white tracking-tighter">{{ $stats['total_remedial'] }}</p>
                </div>
                <div class="pt-4">
                    <a href="{{ route('admin.results.index') }}" class="w-full py-3 bg-white rounded-xl text-[8px] font-black uppercase tracking-widest text-center block hover:opacity-90 transition-colors shadow-lg" style="color: var(--brand-dark);">Buka Detail Nilai</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Management Modules Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Content Management --}}
        <div class="bg-white rounded-[3rem] p-10 border theme-soft-shadow space-y-8" style="border-color: var(--brand-glow);">
            <div class="space-y-2">
                <h3 class="text-xl font-black uppercase tracking-wider flex items-center gap-3" style="color: var(--brand-text);">
                    <span class="w-1.5 h-8 bg-emerald-500 rounded-full"></span>
                    Pengembangan Materi
                </h3>
                <p class="text-xs font-bold text-gray-400 leading-relaxed italic">Kelola aset digital dan struktur soal ujian secara mandiri.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('admin.questions.index') }}" class="group p-6 rounded-[2rem] border transition-all hover:shadow-xl text-left space-y-4 bg-emerald-50/40 border-emerald-100 hover:border-emerald-200">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">Katalog Soal</p>
                        <p class="text-xs font-bold text-gray-600">Import/Export & Bank Soal.</p>
                    </div>
                </a>
                <a href="{{ route('admin.exams.index') }}" class="group p-6 rounded-[2rem] border transition-all hover:shadow-xl text-left space-y-4" style="background-color: var(--brand-bg); border-color: var(--brand-glow);">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform" style="background-color: var(--brand-glow); color: var(--brand-primary);">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest mb-1" style="color: var(--brand-primary);">Paket Ujian</p>
                        <p class="text-xs font-bold text-gray-600">Manajemen Sesi & Durasi.</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- Monitoring & Control --}}
        <div class="bg-white rounded-[3rem] p-10 border theme-soft-shadow space-y-8" style="border-color: var(--brand-glow);">
            <div class="space-y-2">
                <h3 class="text-xl font-black uppercase tracking-wider flex items-center gap-3" style="color: var(--brand-text);">
                    <span class="w-1.5 h-8 bg-amber-500 rounded-full"></span>
                    Keamanan & Monitoring
                </h3>
                <p class="text-xs font-bold text-gray-400 leading-relaxed italic">Pantau aktivitas siswa dan kendalikan sesi ujian secara real-time.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('admin.tokens.index') }}" class="group p-6 rounded-[2rem] border transition-all hover:shadow-xl text-left space-y-4 bg-amber-50/40 border-amber-100 hover:border-amber-200">
                    <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-key"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">Token Akses</p>
                        <p class="text-xs font-bold text-gray-600">Gating Sesi Global.</p>
                    </div>
                </a>
                <a href="{{ route('admin.monitor-exams.index') }}" class="group p-6 rounded-[2rem] border transition-all hover:shadow-xl text-left space-y-4 bg-rose-50/40 border-rose-100 hover:border-rose-200">
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

    {{-- Info Banner --}}
    <div class="rounded-[2.5rem] p-8 md:p-12 text-white shadow-2xl theme-soft-shadow relative overflow-hidden"
         style="background: linear-gradient(135deg, #059669, #047857);">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="relative flex flex-col md:flex-row items-center gap-8 text-center md:text-left">
            <div class="w-20 h-20 bg-white/10 backdrop-blur-xl border border-white/20 rounded-[1.5rem] flex items-center justify-center text-3xl shadow-2xl flex-shrink-0">
                <i class="fas fa-lightbulb text-amber-300"></i>
            </div>
            <div class="space-y-1 flex-1">
                <h4 class="text-lg font-black uppercase tracking-widest">Tips Manajemen Sesi</h4>
                <p class="text-sm font-bold text-emerald-100 leading-relaxed italic opacity-80">"Gunakan fitur Refresh Token setiap 20 menit untuk memastikan keamanan maksimal selama ujian berlangsung."</p>
            </div>
            <div>
                <a href="{{ route('admin.tokens.index') }}" class="px-8 py-4 bg-white text-emerald-800 hover:bg-emerald-50 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-xl">Perbarui Token</a>
            </div>
        </div>
    </div>
</div>
@endsection
