@extends('layouts.app')

@section('title', 'Dasbor Admin - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('content')
<div class="space-y-10 pb-12">

    {{-- ═══════════════════════════════════════════════
         HERO SECTION
    ═══════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden rounded-[3rem] p-10 md:p-14 text-white shadow-2xl theme-soft-shadow"
         style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-dark));">
        {{-- Decorative blobs --}}
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col md:flex-row items-center justify-between gap-10">
            <div class="space-y-6 text-center md:text-left">
                <div class="space-y-2">
                    <p class="font-black uppercase tracking-[0.4em] text-[10px] opacity-70 italic" style="color: var(--brand-text-accent);">Pusat Kontrol Sistem</p>
                    <h1 class="text-3xl md:text-5xl font-black leading-tight uppercase tracking-wider">
                        Selamat Datang,<br>Superadmin! 🛡️
                    </h1>
                    <p class="text-sm font-bold tracking-wide max-w-xl opacity-80">
                        Kelola pengguna, mata pelajaran, ujian, dan seluruh konfigurasi sistem <span class="underline decoration-white/30">{{ $configs['school_name'] ?? 'SesekaliCBT' }}</span> dari satu panel terpusat.
                    </p>
                </div>

                <div class="flex flex-wrap justify-center md:justify-start gap-4">
                    <a href="{{ route('admin.students.index') }}" class="px-7 py-3.5 bg-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl hover:scale-[1.03] transition-all flex items-center gap-2" style="color: var(--brand-primary);">
                        <i class="fas fa-users"></i> Kelola Siswa
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="px-7 py-3.5 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-white/20 transition-all flex items-center gap-2">
                        <i class="fas fa-cog"></i> Pengaturan
                    </a>
                </div>
            </div>

            <div class="hidden md:block relative">
                <div class="w-44 h-44 bg-white/10 backdrop-blur-2xl border border-white/20 rounded-[3rem] flex items-center justify-center shadow-2xl rotate-3 hover:rotate-0 transition-transform duration-500">
                    <i class="fas fa-shield-alt text-7xl text-white opacity-40"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════
         STATISTICS GRID
    ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Total Users --}}
        <div class="group bg-white rounded-[2.5rem] p-7 border theme-soft-shadow relative overflow-hidden theme-card-hover" style="border-color: var(--brand-glow);">
            <div class="absolute -top-6 -right-6 w-20 h-20 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700" style="background-color: var(--brand-glow);"></div>
            <div class="relative space-y-4">
                <div class="w-12 h-12 rounded-[1.25rem] flex items-center justify-center text-xl" style="background-color: var(--brand-glow); color: var(--brand-primary);">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Pengguna</p>
                    <p class="text-3xl font-black tracking-tighter" style="color: var(--brand-text);">{{ $totalUsers }}</p>
                </div>
                <div class="flex items-center gap-2 text-[9px] font-black uppercase tracking-widest" style="color: var(--brand-primary);">
                    <i class="fas fa-check-circle"></i> {{ $activeUsersCount }} Aktif
                </div>
            </div>
        </div>

        {{-- Teachers --}}
        <div class="group bg-white rounded-[2.5rem] p-7 border theme-soft-shadow relative overflow-hidden theme-card-hover" style="border-color: var(--brand-glow);">
            <div class="absolute -top-6 -right-6 w-20 h-20 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700" style="background-color: var(--brand-glow);"></div>
            <div class="relative space-y-4">
                <div class="w-12 h-12 rounded-[1.25rem] flex items-center justify-center text-xl" style="background-color: var(--brand-glow); color: var(--brand-primary);">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Guru (Teachers)</p>
                    <p class="text-3xl font-black tracking-tighter" style="color: var(--brand-text);">{{ $teacherCount }}</p>
                </div>
                <a href="{{ route('superadmin.teachers.index') }}" class="text-[8px] font-black uppercase tracking-widest hover:underline flex items-center gap-1" style="color: var(--brand-primary);">
                    Kelola Guru <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Students --}}
        <div class="group bg-white rounded-[2.5rem] p-7 border theme-soft-shadow relative overflow-hidden theme-card-hover" style="border-color: var(--brand-glow);">
            <div class="absolute -top-6 -right-6 w-20 h-20 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700" style="background-color: var(--brand-glow);"></div>
            <div class="relative space-y-4">
                <div class="w-12 h-12 rounded-[1.25rem] flex items-center justify-center text-xl" style="background-color: var(--brand-glow); color: var(--brand-primary);">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Siswa (Students)</p>
                    <p class="text-3xl font-black tracking-tighter" style="color: var(--brand-text);">{{ $studentCount }}</p>
                </div>
                <a href="{{ route('admin.students.index') }}" class="text-[8px] font-black uppercase tracking-widest hover:underline flex items-center gap-1" style="color: var(--brand-primary);">
                    Data Siswa <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Superadmin CTA --}}
        <div class="rounded-[2.5rem] p-7 relative overflow-hidden shadow-xl" style="background: linear-gradient(135deg, var(--brand-dark), color-mix(in srgb, var(--brand-dark) 80%, black));">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-white/5 rounded-full"></div>
            <div class="relative flex flex-col justify-between h-full gap-4">
                <div class="w-12 h-12 bg-white/15 text-white rounded-[1.25rem] flex items-center justify-center text-xl">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-white/60 uppercase tracking-widest mb-1">Superadmin</p>
                    <p class="text-3xl font-black text-white tracking-tighter">{{ $superadminCount }}</p>
                </div>
                <a href="{{ route('admin.settings.index') }}" class="w-full py-3 bg-white rounded-xl text-[8px] font-black uppercase tracking-widest text-center block hover:opacity-90 transition-all shadow-lg" style="color: var(--brand-dark);">
                    Pengaturan Global
                </a>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════
         MANAGEMENT MODULES
    ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- User & Academic Management --}}
        <div class="bg-white rounded-[3rem] p-10 border theme-soft-shadow space-y-7" style="border-color: var(--brand-glow);">
            <div class="space-y-1">
                <h3 class="text-xl font-black uppercase tracking-wider flex items-center gap-3" style="color: var(--brand-text);">
                    <span class="w-1.5 h-8 rounded-full" style="background-color: var(--brand-primary);"></span>
                    Manajemen Pengguna
                </h3>
                <p class="text-xs font-bold text-gray-400 leading-relaxed italic">Kelola seluruh akun siswa, guru, dan struktur mata pelajaran.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('superadmin.teachers.index') }}" class="group p-6 rounded-[2rem] border transition-all hover:shadow-xl text-left space-y-4" style="background-color: var(--brand-bg); border-color: var(--brand-glow);">
                    <div class="w-11 h-11 rounded-[1rem] flex items-center justify-center text-lg transition-transform group-hover:scale-110" style="background-color: var(--brand-glow); color: var(--brand-primary);">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest mb-1" style="color: var(--brand-primary);">Guru</p>
                        <p class="text-xs font-bold text-gray-500">Tambah, edit, assign mapel.</p>
                    </div>
                </a>
                <a href="{{ route('admin.students.index') }}" class="group p-6 rounded-[2rem] border transition-all hover:shadow-xl text-left space-y-4" style="background-color: var(--brand-bg); border-color: var(--brand-glow);">
                    <div class="w-11 h-11 rounded-[1rem] flex items-center justify-center text-lg transition-transform group-hover:scale-110" style="background-color: var(--brand-glow); color: var(--brand-primary);">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest mb-1" style="color: var(--brand-primary);">Siswa</p>
                        <p class="text-xs font-bold text-gray-500">Import, export, reset sandi.</p>
                    </div>
                </a>
                <a href="{{ route('admin.subjects.index') }}" class="group p-6 rounded-[2rem] border transition-all hover:shadow-xl text-left space-y-4" style="background-color: var(--brand-bg); border-color: var(--brand-glow);">
                    <div class="w-11 h-11 rounded-[1rem] flex items-center justify-center text-lg transition-transform group-hover:scale-110" style="background-color: var(--brand-glow); color: var(--brand-primary);">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest mb-1" style="color: var(--brand-primary);">Mata Pelajaran</p>
                        <p class="text-xs font-bold text-gray-500">Kelola kurikulum & mapel.</p>
                    </div>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="group p-6 rounded-[2rem] border transition-all hover:shadow-xl text-left space-y-4" style="background-color: var(--brand-bg); border-color: var(--brand-glow);">
                    <div class="w-11 h-11 rounded-[1rem] flex items-center justify-center text-lg transition-transform group-hover:scale-110" style="background-color: var(--brand-glow); color: var(--brand-primary);">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest mb-1" style="color: var(--brand-primary);">Pengaturan</p>
                        <p class="text-xs font-bold text-gray-500">Logo, nama, & konfigurasi.</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- Monitoring & Security --}}
        <div class="bg-white rounded-[3rem] p-10 border theme-soft-shadow space-y-7" style="border-color: var(--brand-glow);">
            <div class="space-y-1">
                <h3 class="text-xl font-black uppercase tracking-wider flex items-center gap-3" style="color: var(--brand-text);">
                    <span class="w-1.5 h-8 rounded-full bg-amber-500 rounded-full"></span>
                    LMS & Pengawasan
                </h3>
                <p class="text-xs font-bold text-gray-400 leading-relaxed italic">Buat soal, kelola ujian, dan pantau sesi secara real-time.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('admin.questions.index') }}" class="group p-6 rounded-[2rem] border transition-all hover:shadow-xl text-left space-y-4 bg-emerald-50/50 border-emerald-100 hover:border-emerald-200">
                    <div class="w-11 h-11 bg-emerald-100 text-emerald-600 rounded-[1rem] flex items-center justify-center text-lg transition-transform group-hover:scale-110">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">Bank Soal</p>
                        <p class="text-xs font-bold text-gray-500">Import/Export & katalog.</p>
                    </div>
                </a>
                <a href="{{ route('admin.exams.index') }}" class="group p-6 rounded-[2rem] border transition-all hover:shadow-xl text-left space-y-4" style="background-color: var(--brand-bg); border-color: var(--brand-glow);">
                    <div class="w-11 h-11 rounded-[1rem] flex items-center justify-center text-lg transition-transform group-hover:scale-110" style="background-color: var(--brand-glow); color: var(--brand-primary);">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest mb-1" style="color: var(--brand-primary);">Paket Ujian</p>
                        <p class="text-xs font-bold text-gray-500">Sesi, durasi, soal.</p>
                    </div>
                </a>
                <a href="{{ route('admin.tokens.index') }}" class="group p-6 rounded-[2rem] border transition-all hover:shadow-xl text-left space-y-4 bg-amber-50/50 border-amber-100 hover:border-amber-200">
                    <div class="w-11 h-11 bg-amber-100 text-amber-600 rounded-[1rem] flex items-center justify-center text-lg transition-transform group-hover:scale-110">
                        <i class="fas fa-key"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">Token Akses</p>
                        <p class="text-xs font-bold text-gray-500">Gating sesi global.</p>
                    </div>
                </a>
                <a href="{{ route('admin.monitor-exams.index') }}" class="group p-6 rounded-[2rem] border transition-all hover:shadow-xl text-left space-y-4 bg-rose-50/50 border-rose-100 hover:border-rose-200">
                    <div class="relative w-11 h-11 bg-rose-100 text-rose-600 rounded-[1rem] flex items-center justify-center text-lg transition-transform group-hover:scale-110">
                        <i class="fas fa-desktop"></i>
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 border-2 border-white rounded-full animate-pulse"></span>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest mb-1">Monitoring</p>
                        <p class="text-xs font-bold text-gray-500">Track kecurangan live.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════
         SYSTEM INFO + THEME SELECTOR
    ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- System Info --}}
        <div class="lg:col-span-2 rounded-[3rem] p-10 text-white shadow-xl relative overflow-hidden" style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-dark));">
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
            <div class="relative flex flex-col md:flex-row items-center gap-8">
                <div class="w-20 h-20 bg-white/10 backdrop-blur-xl border border-white/20 rounded-[1.5rem] flex items-center justify-center text-3xl shadow-2xl flex-shrink-0">
                    <i class="fas fa-server"></i>
                </div>
                <div class="space-y-4 flex-1">
                    <h4 class="text-lg font-black uppercase tracking-widest">Info Server</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white/10 rounded-2xl p-4 space-y-1">
                            <p class="text-[9px] font-black uppercase tracking-widest opacity-60">Laravel</p>
                            <p class="text-sm font-black">v{{ app()::VERSION }}</p>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-4 space-y-1">
                            <p class="text-[9px] font-black uppercase tracking-widest opacity-60">PHP</p>
                            <p class="text-sm font-black">{{ PHP_VERSION }}</p>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-4 space-y-1">
                            <p class="text-[9px] font-black uppercase tracking-widest opacity-60">Env</p>
                            <p class="text-sm font-black uppercase">{{ app()->environment() }}</p>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-4 space-y-1">
                            <p class="text-[9px] font-black uppercase tracking-widest opacity-60">Anti-Cheat</p>
                            <p class="text-sm font-black">{{ ($configs['anti_cheat_active'] ?? 1) ? '✅ ON' : '❌ OFF' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pro Theme Selector --}}
        <div class="bg-white rounded-[3rem] p-8 border theme-soft-shadow space-y-6" style="border-color: var(--brand-glow);">
            <div class="space-y-1">
                <p class="text-[9px] font-black uppercase tracking-widest" style="color: var(--brand-primary);">Personalisasi</p>
                <h3 class="text-base font-black uppercase tracking-wider" style="color: var(--brand-text);">Tema Dashboard</h3>
                <p class="text-[10px] font-bold text-gray-400 leading-relaxed">Pilih tema yang sesuai untuk panel Anda.</p>
            </div>

            <div class="space-y-3" id="admin-theme-picker">
                @php
                    $proThemes = [
                        ['key' => 'indigo', 'name' => 'Indigo Pro',  'color' => '#4f46e5', 'desc' => 'Elegan & Formal'],
                        ['key' => 'slate',  'name' => 'Slate Dark',  'color' => '#475569', 'desc' => 'Minimalis & Tegas'],
                        ['key' => 'ocean',  'name' => 'Ocean Blue',  'color' => '#0284c7', 'desc' => 'Segar & Profesional'],
                    ];
                    $currentTheme = Auth::user()->ui_theme ?? 'indigo';
                @endphp
                @foreach($proThemes as $t)
                    <button onclick="switchTheme('{{ $t['key'] }}')"
                            id="theme-btn-{{ $t['key'] }}"
                            class="w-full flex items-center gap-4 p-4 rounded-2xl border-2 transition-all duration-300 {{ $currentTheme === $t['key'] ? 'shadow-md' : 'border-gray-100 hover:border-gray-200' }}"
                            style="{{ $currentTheme === $t['key'] ? 'border-color: var(--brand-primary); background-color: var(--brand-glow);' : '' }}">
                        <div class="w-10 h-10 rounded-xl shadow-md flex-shrink-0" style="background-color: {{ $t['color'] }};"></div>
                        <div class="text-left">
                            <p class="text-[11px] font-black uppercase tracking-wider" style="color: var(--brand-text);">{{ $t['name'] }}</p>
                            <p class="text-[9px] font-bold text-gray-400">{{ $t['desc'] }}</p>
                        </div>
                        @if($currentTheme === $t['key'])
                            <i class="fas fa-check-circle ml-auto text-sm" style="color: var(--brand-primary);"></i>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function switchTheme(theme) {
    fetch('{{ route("profile.update-theme") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ theme })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Instantly apply the new theme class to body
            document.body.className = document.body.className.replace(/theme-\w+/, 'theme-' + theme);
            // Update button states
            document.querySelectorAll('[id^="theme-btn-"]').forEach(btn => {
                btn.style.borderColor = '';
                btn.style.backgroundColor = '';
                btn.classList.remove('shadow-md');
                btn.classList.add('border-gray-100');
                const check = btn.querySelector('.fa-check-circle');
                if (check) check.remove();
            });
            const active = document.getElementById('theme-btn-' + theme);
            if (active) {
                active.style.borderColor = 'var(--brand-primary)';
                active.style.backgroundColor = 'var(--brand-glow)';
                active.classList.add('shadow-md');
                active.classList.remove('border-gray-100');
                const check = document.createElement('i');
                check.className = 'fas fa-check-circle ml-auto text-sm';
                check.style.color = 'var(--brand-primary)';
                active.appendChild(check);
            }
        }
    });
}
</script>
@endsection
