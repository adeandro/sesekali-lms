<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard - ' . ($configs['school_name'] ?? 'ExamFlow'))</title>
    <link rel="icon" type="image/x-icon" href="{{ isset($configs['logo']) ? asset('storage/' . $configs['logo']) : asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* ---- Runtime Theme Injection (Database-driven / PHP-rendered) ---- */
        @php
            if(isset($activeTheme)):
        @endphp
        :root, .theme-{{ $activeTheme->slug }} {
            --brand-primary:     {{ $activeTheme->primary_color }};
            --brand-secondary:   {{ $activeTheme->secondary_color }};
            --brand-dark:        {{ $activeTheme->dark_color ?? $activeTheme->primary_color }};
            --brand-glow:        {{ $activeTheme->glow_color }};
            --brand-bg:          {{ $activeTheme->bg_color }};
            --brand-surface:     {{ $activeTheme->surface_color ?? '#ffffff' }};
            --brand-text:        {{ $activeTheme->text_color }};
            --brand-text-accent: #ffffff;
            --sidebar-header:    linear-gradient(135deg, {{ $activeTheme->primary_color }}, {{ $activeTheme->dark_color ?? $activeTheme->primary_color }});
        }
        @php
            endif;
        @endphp

        @if(($configs['enable_gamification'] ?? '1') != '1' && auth()->check() && auth()->user()->role === 'student')
        body {
            --brand-primary:     #4f46e5 !important;
            --brand-secondary:   #818cf8 !important;
            --brand-dark:        #3730a3 !important;
            --brand-glow:        rgba(79, 70, 229, 0.2) !important;
            --brand-bg:          #f8faff !important;
            --brand-surface:     #ffffff !important;
            --brand-text:        #1e293b !important;
            --brand-text-accent: #ffffff !important;
            --sidebar-header:    linear-gradient(135deg, #4f46e5, #3730a3) !important;
        }
        @endif
    </style>
    @stack('styles')

@php
    $__themeRole = auth()->check() ? auth()->user()->role : 'guest';
    $__gamified  = ($configs['enable_gamification'] ?? '1') == '1';
    $__rawTheme  = auth()->check() ? (auth()->user()->ui_theme ?? 'indigo') : 'indigo';
    // Pro roles only get 3 formal themes; fall back to indigo if they somehow have a student theme
    $__proThemes = ['slate', 'indigo', 'ocean'];
    if (in_array($__themeRole, ['teacher', 'superadmin']) && !in_array($__rawTheme, $__proThemes)) {
        $__rawTheme = 'indigo';
    }
    // Students: if gamification off, force indigo
    if ($__themeRole === 'student' && !$__gamified) {
        $__rawTheme = 'indigo';
    }
    $__bodyTheme = $__rawTheme;
@endphp
<body class="bg-gray-50 theme-{{ $__bodyTheme }}">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="sidebar-mobile-overlay fixed inset-0 bg-black/50 lg:hidden z-30"></div>

    <!-- Page Wrapper -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar-transition lg:translate-x-0 fixed lg:relative left-0 top-0 h-screen w-64 z-40 overflow-y-auto flex-shrink-0" style="background-color: var(--brand-surface); box-shadow: 4px 0 24px -4px var(--brand-glow), 1px 0 0 rgba(0,0,0,0.04);">
            <!-- Sidebar Header -->
            <div class="sidebar-header-gradient text-white p-6 flex items-center justify-between sticky top-0 z-10 shadow-sm">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">{{ $configs['school_name'] ?? 'SesekaliCBT' }}</h2>
                    <p class="text-[10px] text-indigo-100 uppercase tracking-widest font-semibold">{{ $configs['academic_year'] ?? '2023/2024' }}</p>
                </div>
                <button id="closeSidebarBtn" class="lg:hidden text-indigo-100 hover:text-white hover:bg-white/10 p-2 rounded-lg transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="px-4 py-6 border-b border-gray-100">
                @php
                    $isGamified = ($configs['enable_gamification'] ?? '1') == '1' && Auth::user()->role === 'student';
                @endphp

                <a href="{{ Auth::user()->role === 'student' ? route('student.profile') : (in_array(Auth::user()->role, ['teacher', 'principal']) ? route('teacher.settings.index') : (Auth::user()->role === 'superadmin' ? route('admin.settings.index') : '#')) }}" 
                   x-data="{ showGreeting: false }"
                   @mouseenter="showGreeting = true"
                   @mouseleave="showGreeting = false"
                   class="relative bg-[var(--brand-glow)] p-4 rounded-[2rem] flex flex-col gap-3 border border-[var(--brand-glow)] hover:bg-[var(--brand-glow)]/80 transition-all duration-500 group">
                    
                    <div class="flex items-center gap-4">
                        <!-- Avatar with Frame -->
                        <div class="relative">
                            @if($isGamified)
                                <!-- Gamified Avatar -->
                                <div class="w-14 h-14 rounded-full border-2 bg-white flex items-center justify-center overflow-hidden transition-all duration-500 {{ Auth::user()->avatar_frame_class }}">
                                    @if(Auth::user()->is_avatar_seed)
                                        <div x-html="multiavatar('{{ Auth::user()->avatar_seed }}')" class="w-full h-full"></div>
                                    @elseif(Auth::user()->has_avatar)
                                        <img src="{{ Auth::user()->avatar_url }}" alt="Avatar" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-[var(--brand-primary)] to-[var(--brand-dark)] flex items-center justify-center text-white text-xs font-black italic leading-none">
                                            {{ Auth::user()->initials }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <!-- Standard Avatar (Kill Switch Active or Non-Student) -->
                                <div class="w-12 h-12 rounded-full border-2 border-white shadow-sm overflow-hidden bg-white">
                                    <img src="{{ Auth::user()->photo_url }}" alt="Formal Photo" class="w-full h-full object-cover">
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-black text-gray-900 truncate tracking-tight">{{ Auth::user()->full_name }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                @if($isGamified)
                                    <span class="px-2 py-0.5 text-[8px] font-black bg-[var(--brand-primary)] text-white rounded-lg uppercase tracking-widest shadow-sm">
                                        LVL {{ Auth::user()->current_level }} • {{ Auth::user()->level_title }}
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 text-[9px] font-extrabold rounded-md uppercase tracking-wider
                                         @if(isset($index) && $index === 0) border-amber-400 ring-4 ring-amber-100 shadow-[0_0_15px_rgba(251,191,36,0.3)] fire-ring
                                         @elseif(isset($index) && $index === 1) border-slate-300 ring-4 ring-slate-100 shadow-[0_0_15px_rgba(203,213,225,0.3)]
                                        @elseif(Auth::user()->role === 'superadmin') bg-rose-100 text-rose-700
                                        @elseif(Auth::user()->role === 'teacher') bg-indigo-100 text-indigo-700
                                        @else bg-emerald-100 text-emerald-700 @endif">
                                        @if(Auth::user()->role === 'teacher') GURU
                                        @elseif(Auth::user()->role === 'principal') KEPALA SEKOLAH
                                        @else {{ Auth::user()->role }} @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($isGamified)
                        <!-- XP Progress Bar (Gamified Only) -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[8px] font-bold text-[var(--brand-primary)] uppercase tracking-widest">
                                <span>XP Progressive</span>
                                <span>{{ Auth::user()->xp_progress_percentage }}%</span>
                            </div>
                            <div class="h-1.5 w-full bg-[var(--brand-glow)] rounded-full overflow-hidden border border-white/50">
                                <div class="h-full bg-gradient-to-r from-[var(--brand-primary)] to-[var(--brand-secondary)] rounded-full transition-all duration-1000 shadow-[0_0_8px_var(--brand-glow)]" 
                                     style="width: {{ Auth::user()->xp_progress_percentage }}%"></div>
                            </div>
                        </div>

                        <!-- Mini Greeting (Floating on Hover) -->
                        <div x-show="showGreeting" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-full ml-4 top-0 w-48 p-3 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-[var(--brand-glow)] z-50 pointer-events-none hidden lg:block">
                            <p class="text-[9px] font-bold text-gray-600 leading-relaxed">
                                {{ Auth::user()->dynamic_greeting }}
                            </p>
                            <div class="absolute top-6 -left-2 w-4 h-4 bg-white/95 border-l border-b border-[var(--brand-glow)] rotate-45"></div>
                        </div>
                    @endif
                </a>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto custom-scrollbar">

                {{-- ── 1. DASHBOARD ── --}}
                <div class="nav-group-label">Dashboard</div>
                <a href="{{ route('dashboard') }}" 
                   class="nav-item {{ request()->routeIs('dashboard*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-th-large w-5 text-lg mr-3"></i>
                    <span>Beranda</span>
                </a>

                {{-- ── 2. KOMUNIKASI ── --}}
                <div class="nav-group-label">Komunikasi</div>
                <a href="{{ route('communication.announcements.index') }}" 
                   class="nav-item {{ request()->routeIs('communication.announcements*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-bullhorn w-5 text-lg mr-3"></i>
                    <span>Pengumuman</span>
                </a>
                @php $__unread = auth()->user()->unreadMessagesCount(); @endphp
                <a href="{{ route('communication.messages.inbox') }}" 
                   class="nav-item {{ request()->routeIs('communication.messages*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-envelope w-5 text-lg mr-3"></i>
                    <span class="flex-1">Pesan</span>
                    @if($__unread > 0)
                        <span class="unread-badge">{{ $__unread > 99 ? '99+' : $__unread }}</span>
                    @endif
                </a>

                {{-- ── 3. CBT & UJIAN (teacher, superadmin) ── --}}
                @if(in_array(Auth::user()->role, ['teacher', 'superadmin']))
                <div class="nav-group-label">CBT & Ujian</div>
                <a href="{{ route('admin.questions.index') }}" 
                   class="nav-item {{ request()->routeIs('admin.questions.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-question-circle w-5 text-lg mr-3"></i>
                    <span>Soal</span>
                </a>
                <a href="{{ route('admin.exams.index') }}" 
                   class="nav-item {{ request()->routeIs('admin.exams.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-file-alt w-5 text-lg mr-3"></i>
                    <span>Ujian</span>
                </a>
                <a href="{{ route('admin.results.index') }}" 
                   class="nav-item {{ request()->routeIs('admin.results.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-chart-line w-5 text-lg mr-3"></i>
                    <span>Hasil Ujian</span>
                </a>
                <a href="{{ route('admin.tokens.index') }}" 
                   class="nav-item {{ request()->routeIs('admin.tokens.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-key w-5 text-lg mr-3"></i>
                    <span>Kelola Token</span>
                </a>
                <a href="{{ route('admin.monitor-exams.index') }}" 
                   class="nav-item {{ request()->routeIs('admin.monitor-exams.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-video w-5 text-lg mr-3"></i>
                    <span>Pantau Ujian</span>
                </a>
                @endif

                {{-- ── 4. AKADEMIK (teacher, superadmin) ── --}}
                @if(in_array(Auth::user()->role, ['teacher', 'superadmin']))
                <div class="nav-group-label">Akademik</div>
                @if(Auth::user()->role === 'superadmin')
                    <a href="{{ route('superadmin.teachers.index') }}" 
                       class="nav-item {{ request()->routeIs('superadmin.teachers.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-chalkboard-teacher w-5 text-lg mr-3"></i>
                        <span>Guru</span>
                    </a>
                    <a href="{{ route('admin.students.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.students.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-users w-5 text-lg mr-3"></i>
                        <span>Siswa</span>
                    </a>
                    <a href="{{ route('admin.subjects.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.subjects.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-book w-5 text-lg mr-3"></i>
                        <span>Mapel</span>
                    </a>
                    <a href="{{ route('admin.classes.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.classes.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-school w-5 text-lg mr-3"></i>
                        <span>Kelas</span>
                    </a>
                @endif
                <a href="{{ route('admin.grade-weights.index') }}" 
                   class="nav-item {{ request()->routeIs('admin.grade-weights.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-balance-scale w-5 text-lg mr-3"></i>
                    <span>Bobot Nilai</span>
                </a>
                <a href="{{ route('admin.manual-grades.input') }}" 
                   class="nav-item {{ request()->routeIs('admin.manual-grades.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-pen-to-square w-5 text-lg mr-3"></i>
                    <span>Input Nilai</span>
                </a>
                @endif

                {{-- ── 5. EKSTRAKURIKULER (teacher, superadmin) ── --}}
                @if(in_array(Auth::user()->role, ['teacher', 'superadmin']))
                <div class="nav-group-label">Ekstrakurikuler</div>
                @if(Auth::user()->role === 'superadmin')
                    <a href="{{ route('admin.extracurriculars.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.extracurriculars.index') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-running w-5 text-lg mr-3"></i>
                        <span>Kelola Ekskul</span>
                    </a>
                @endif
                {{-- Guru pembina: akses ekskul yang dibina (Superadmin lihat semua) --}}
                @if(Auth::user()->role === 'superadmin' || Auth::user()->isExtracurricularCoach())
                    <a href="{{ route('admin.extracurriculars.my-assignments') }}" 
                       class="nav-item {{ request()->routeIs('admin.extracurriculars.my-assignments') || request()->routeIs('admin.extracurriculars.sessions.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-skating w-5 text-lg mr-3"></i>
                        <span>Ekskul Saya</span>
                    </a>
                @endif
                @endif

                {{-- ── PRINCIPAL MENU (kepala sekolah) ── --}}
                @if(Auth::user()->role === 'principal')
                <div class="nav-group-label">Monitoring</div>

                <a href="{{ route('dashboard.principal') }}"
                   class="nav-item {{ request()->routeIs('dashboard.principal') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-chart-pie w-5 text-lg mr-3"></i>
                    <span>Ringkasan Sekolah</span>
                </a>

                <a href="{{ route('admin.reports.index') }}"
                   class="nav-item {{ request()->routeIs('admin.reports.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-file-invoice w-5 text-lg mr-3"></i>
                    <span>Raport Siswa</span>
                </a>

                <a href="{{ route('admin.results.index') }}"
                   class="nav-item {{ request()->routeIs('admin.results.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-chart-bar w-5 text-lg mr-3"></i>
                    <span>Hasil Ujian</span>
                </a>

                <a href="{{ route('admin.monitor-exams.index') }}"
                   class="nav-item {{ request()->routeIs('admin.monitor-exams.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-desktop w-5 text-lg mr-3"></i>
                    <span>Monitor Ujian</span>
                </a>
                @endif

                {{-- ── 6. RAPORT (wali kelas saja) ── --}}
                @if(in_array(Auth::user()->role, ['teacher', 'superadmin']) && Auth::user()->isHomeroom())
                <div class="nav-group-label">Raport</div>
                <a href="{{ route('admin.report-data.index') }}" 
                   class="nav-item {{ request()->routeIs('admin.report-data.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-user-check w-5 text-lg mr-3"></i>
                    <span>Kehadiran & Kepribadian</span>
                </a>
                <a href="{{ route('admin.dudi.index') }}" 
                   class="nav-item {{ request()->routeIs('admin.dudi.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-briefcase w-5 text-lg mr-3"></i>
                    <span>Kegiatan DU/DI</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" 
                   class="nav-item {{ request()->routeIs('admin.reports.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-file-invoice w-5 text-lg mr-3"></i>
                    <span>Cetak Raport</span>
                </a>
                @endif

                {{-- ── 7. ADMINISTRASI SURAT (superadmin, tu) ── --}}
                @if(in_array(Auth::user()->role, ['superadmin', 'tu']))
                <div class="nav-group-label">Administrasi Surat</div>
                <a href="{{ route('admin.letters.index') }}" 
                   class="nav-item {{ request()->routeIs('admin.letters.*') 
                       && !request()->routeIs('admin.letters.templates.*') 
                       && !request()->routeIs('admin.letters.history') 
                       ? 'menu-item-active' : '' }}">
                    <i class="fas fa-magic w-5 text-lg mr-3"></i>
                    <span>Buat Surat</span>
                </a>
                <a href="{{ route('admin.letters.history') }}" 
                   class="nav-item {{ request()->routeIs('admin.letters.history') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-history w-5 text-lg mr-3"></i>
                    <span>Arsip Surat</span>
                </a>
                @if(Auth::user()->role === 'superadmin')
                    <a href="{{ route('admin.letters.templates.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.letters.templates.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-file-code w-5 text-lg mr-3"></i>
                        <span>Template Surat</span>
                    </a>
                @endif
                @endif

                {{-- ── 8. SISWA (student role) ── --}}
                @if(Auth::user()->role === 'student')
                <div class="nav-group-label">Akademik</div>
                <a href="{{ route('student.exams.index') }}" 
                   class="nav-item {{ request()->routeIs('student.exams.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-file-alt w-5 text-lg mr-3"></i>
                    <span>Ujian Saya</span>
                </a>
                <a href="{{ route('student.results') }}" 
                   class="nav-item {{ request()->routeIs('student.results*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-chart-line w-5 text-lg mr-3"></i>
                    <span>Hasil Saya</span>
                </a>
                <a href="{{ route('student.coupons.index') }}" 
                   class="nav-item {{ request()->routeIs('student.coupons.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-ticket-alt w-5 text-lg mr-3"></i>
                    <span>Kupon Fisik</span>
                </a>
                <div class="nav-group-label">Gamifikasi</div>
                <a href="{{ route('student.leaderboard') }}" 
                   class="nav-item {{ request()->routeIs('student.leaderboard') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-trophy w-5 text-lg mr-3"></i>
                    <span>Hall of Fame</span>
                </a>
                <a href="#" 
                   onclick="document.getElementById('arenaJoinModal').classList.remove('hidden'); return false;"
                   class="nav-item {{ request()->routeIs('student.arena.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-fist-raised w-5 text-lg mr-3"></i>
                    <span class="flex-1">Battle Arena</span>
                    <span class="ml-auto text-[9px] font-black bg-red-500 text-white px-1.5 py-0.5 rounded-full uppercase tracking-widest">LIVE</span>
                </a>
                @endif

                {{-- ── 9. TU MENU ── --}}
                @if(Auth::user()->role === 'tu')
                <div class="nav-group-label">Tata Usaha</div>
                <a href="{{ route('tu.dashboard') }}" 
                   class="nav-item {{ request()->routeIs('tu.dashboard') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-tachometer-alt w-5 text-lg mr-3"></i>
                    <span>Dashboard TU</span>
                </a>
                @endif

                {{-- ── 10. GAMIFIKASI (superadmin only) ── --}}
                @if(Auth::user()->role === 'superadmin')
                <div class="pt-2" x-data="{ 
                    open: sessionStorage.getItem('sidebar_gamification_open') === 'true' 
                        || {{ request()->routeIs('admin.gamification.*') ? 'true' : 'false' }}
                }" x-init="$watch('open', value => sessionStorage.setItem('sidebar_gamification_open', value))">
                    <div class="nav-group-label">Gamifikasi</div>
                    <button @click="open = !open" 
                            class="w-full nav-item justify-between 
                                   {{ request()->routeIs('admin.gamification.*') ? 'bg-gray-50' : '' }}">
                        <div class="flex items-center">
                            <i class="fas fa-trophy w-5 text-lg mr-3"></i>
                            <span class="font-bold text-[11px] uppercase tracking-widest">
                                Gamification
                            </span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" 
                           :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-cloak x-collapse 
                         class="mt-1 ml-4 space-y-1 border-l-2 border-gray-100 pl-2">
                        <a href="{{ route('admin.gamification.settings') }}" 
                           class="nav-item py-2 text-sm {{ request()->routeIs('admin.gamification.settings') ? 'menu-item-active' : '' }}">
                            <i class="fas fa-sliders-h w-4 mr-2"></i><span>Settings</span>
                        </a>
                        <a href="{{ route('admin.gamification.achievements') }}" 
                           class="nav-item py-2 text-sm {{ request()->routeIs('admin.gamification.achievements*') ? 'menu-item-active' : '' }}">
                            <i class="fas fa-medal w-4 mr-2"></i><span>Achievements</span>
                        </a>
                        <a href="{{ route('admin.gamification.themes') }}" 
                           class="nav-item py-2 text-sm {{ request()->routeIs('admin.gamification.themes*') ? 'menu-item-active' : '' }}">
                            <i class="fas fa-palette w-4 mr-2"></i><span>Themes</span>
                        </a>
                        <a href="{{ route('admin.gamification.arena.index') }}" 
                           class="nav-item py-2 text-sm {{ request()->routeIs('admin.gamification.arena*') ? 'menu-item-active' : '' }}">
                            <i class="fas fa-fist-raised w-4 mr-2"></i><span>Battle Arena</span>
                        </a>
                        <a href="{{ route('admin.gamification.coupons.index') }}" 
                           class="nav-item py-2 text-sm {{ request()->routeIs('admin.gamification.coupons*') ? 'menu-item-active' : '' }}">
                            <i class="fas fa-ticket-alt w-4 mr-2"></i><span>Kupon Hadiah</span>
                        </a>
                        <a href="{{ route('admin.gamification.seasons.index') }}" 
                           class="nav-item py-2 text-sm {{ request()->routeIs('admin.gamification.seasons*') ? 'menu-item-active' : '' }}">
                            <i class="fas fa-calendar-alt w-4 mr-2"></i><span>Seasons</span>
                        </a>
                        <a href="{{ route('admin.gamification.leaderboard.index') }}" 
                           class="nav-item py-2 text-sm {{ request()->routeIs('admin.gamification.leaderboard*') ? 'menu-item-active' : '' }}">
                            <i class="fas fa-trophy w-4 mr-2"></i><span>Leaderboard</span>
                        </a>
                    </div>
                </div>
                @endif

                {{-- ── 11. AKUN ── --}}
                <div class="nav-group-label">Akun</div>
                @if(Auth::user()->role === 'superadmin')
                    <a href="{{ route('admin.settings.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.settings.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-cog w-5 text-lg mr-3"></i>
                        <span>Pengaturan</span>
                    </a>
                @elseif(in_array(Auth::user()->role, ['teacher', 'principal']))
                    <a href="{{ route('teacher.settings.index') }}" 
                       class="nav-item {{ request()->routeIs('teacher.settings.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-user-cog w-5 text-lg mr-3"></i>
                        <span>Pengaturan</span>
                    </a>
                @elseif(Auth::user()->role === 'student')
                    <a href="{{ route('student.profile') }}" 
                       class="nav-item {{ request()->routeIs('student.profile') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-user-circle w-5 text-lg mr-3"></i>
                        <span>Profil</span>
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="POST" class="block">
                    @csrf
                    <button type="submit" class="w-full nav-item text-red-600 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt w-5 text-lg mr-3"></i>
                        <span>Keluar</span>
                    </button>
                </form>

            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Top Navigation Bar -->
            <nav class="z-20 flex-shrink-0" style="background-color: var(--brand-surface); box-shadow: 0 1px 0 rgba(0,0,0,0.05), 0 2px 8px -2px var(--brand-glow);">
                <div class="px-4 lg:px-8 py-4 flex  items-center justify-end gap-4">
                    <button id="toggleSidebarBtn" class="lg:hidden text-gray-600 hover:text-gray-900 p-2 -ml-2 transition">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <!-- <div class="hidden lg:block flex-1">
                        <h1 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                    </div> -->
                    <div class="flex items-center gap-4">
                    <div class="flex items-center gap-4">
                        {{-- Universal Notification Bell --}}
                        <div x-data="{ 
                            open: false, 
                            unreadCount: {{ Auth::user()->unreadNotifications->count() }} 
                        }" class="relative">
                            <button @click="open = !open" @click.away="open = false" class="relative p-2 text-gray-400 hover:text-[var(--brand-primary)] bg-gray-50 hover:bg-[var(--brand-glow)] rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:ring-offset-2">
                                <i class="fas fa-bell text-lg"></i>
                                <span x-show="unreadCount > 0" x-cloak class="absolute top-0 right-0 p-1 flex items-center justify-center">
                                    <span class="absolute inline-flex h-full w-full rounded-full bg-[var(--color-notification-unread,theme(colors.rose.400))] opacity-75 animate-ping"></span>
                                    <span class="relative inline-flex items-center justify-center rounded-full h-4 w-4 bg-[var(--color-notification-unread,theme(colors.rose.500))] text-white text-[8px] font-black border-2 border-white" x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
                                </span>
                            </button>

                            {{-- Dropdown --}}
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                                 class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-gray-100 overflow-hidden z-[60]" style="display: none;">
                                
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                                    <h3 class="text-[11px] font-black uppercase tracking-widest text-gray-900">Notifikasi</h3>
                                </div>
                                
                                <div class="max-h-[350px] overflow-y-auto">
                                    @php
                                        $notifications = Auth::user()->notifications()->take(10)->get();
                                    @endphp
                                    @forelse($notifications as $notification)
                                        @php
                                            $data    = $notification->data;
                                            $type    = $data['type']     ?? 'info';
                                            $cat     = $data['category'] ?? null;

                                            // Use pre-built icon_color/icon_bg from payload (new canonical classes)
                                            // Fall back to old-style mapping for backward compatibility
                                            if (!empty($data['icon_color'])) {
                                                $icon   = ($data['icon'] ?? 'fas fa-bell') . ' ' . $data['icon_color'];
                                                $iconBg = $data['icon_bg'] ?? 'bg-gray-50';
                                            } else {
                                                // Legacy fallback mapping
                                                $icon   = 'fas fa-bell text-gray-400';
                                                $iconBg = 'bg-gray-100';
                                                if ($type === 'new_message' || $type === 'message') {
                                                    $icon   = 'fas fa-envelope text-blue-500';
                                                    $iconBg = 'bg-blue-50';
                                                } elseif ($type === 'global_announcement' || $type === 'info') {
                                                    $icon   = 'fas fa-bullhorn text-emerald-500';
                                                    $iconBg = 'bg-emerald-50';
                                                } elseif ($type === 'gamification') {
                                                    if ($cat === 'achievement') { $icon = 'fas fa-trophy text-amber-500';   $iconBg = 'bg-amber-50'; }
                                                    elseif ($cat === 'level_up') { $icon = 'fas fa-arrow-up text-purple-500'; $iconBg = 'bg-purple-50'; }
                                                    elseif ($cat === 'theme')    { $icon = 'fas fa-paint-brush text-emerald-500'; $iconBg = 'bg-emerald-50'; }
                                                    elseif ($cat === 'item')     { $icon = 'fas fa-unlock text-indigo-500'; $iconBg = 'bg-indigo-50'; }
                                                    else { $icon = 'fas fa-star text-amber-400'; $iconBg = 'bg-amber-50'; }
                                                } elseif ($type === 'achievement_unlocked') {
                                                    $icon = 'fas fa-trophy text-amber-500'; $iconBg = 'bg-amber-50';
                                                } elseif ($type === 'level_up') {
                                                    $icon = 'fas fa-arrow-up text-purple-500'; $iconBg = 'bg-purple-50';
                                                }
                                            }

                                            $actionUrl = $data['action_url'] ?? null;
                                        @endphp
                                        @if($actionUrl)
                                        <a href="{{ $actionUrl }}" class="block p-4 border-b border-gray-50 hover:bg-gray-50 transition-colors {{ is_null($notification->read_at) ? 'bg-[var(--brand-glow)]/30' : '' }}">
                                        @else
                                        <div class="p-4 border-b border-gray-50 hover:bg-gray-50 transition-colors {{ is_null($notification->read_at) ? 'bg-[var(--brand-glow)]/30' : '' }}">
                                        @endif
                                            <div class="flex gap-3">
                                                <div class="mt-0.5 shadow-sm {{ $iconBg }} w-8 h-8 rounded-full flex items-center justify-center shrink-0">
                                                    <i class="{{ $icon }} text-sm"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-bold text-gray-900 mb-0.5 leading-tight truncate">{{ $notification->data['title'] ?? 'Pesan Sistem' }}</p>
                                                    <p class="text-[10px] text-gray-500 leading-snug line-clamp-2">
                                                        {{ $notification->data['body'] ?? $notification->data['subtitle'] ?? '' }}
                                                    </p>
                                                    <p class="text-[9px] text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                        @if($actionUrl)
                                        </a>
                                        @else
                                        </div>
                                        @endif
                                    @empty
                                        <div class="p-6 text-center">
                                            <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-300 mx-auto mb-2">
                                                <i class="fas fa-bell-slash"></i>
                                            </div>
                                            <p class="text-xs text-gray-500 font-medium">Belum ada notifikasi baru</p>
                                        </div>
                                    @endforelse
                                </div>
                                
                                @if($notifications->count() > 0)
                                <div class="p-2 border-t border-gray-50 bg-gray-50/50">
                                    <form action="{{ route('communication.notifications.read.all') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full text-center text-[10px] items-center justify-center font-bold text-gray-500 hover:text-[var(--brand-primary)] transition p-2">
                                            Tandai semua dibaca <i class="fas fa-check-double ml-1"></i>
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="hidden sm:flex items-center gap-3">
                            <div class="text-right">
                                <p class="text-sm font-black text-gray-900 uppercase tracking-wide leading-none group-hover:text-[var(--brand-primary)]">{{ Auth::user()->name }}</p>
                                <p class="text-[9px] font-bold text-[var(--brand-primary)] uppercase tracking-widest mt-1 opacity-80">Sesi Aktif</p>
                            </div>

                        </div>
                    </div>
                </div>
            </nav>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto relative">
                <div class="p-4 lg:p-8 max-w-7xl mx-auto w-full">
                    {{-- Announcement Banner: student-facing active announcements --}}
                    @if(auth()->check() && auth()->user()->role === 'student')
                        <x-announcement-banner />
                    @endif
                    @yield('content')
                </div>
                
                {{-- Toast Notification Overlay --}}
                <x-toast-notification />
            </main>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay">
        <div class="flex items-center justify-center h-full">
            <div class="text-center">
                <div class="spinner mx-auto mb-4"></div>
                <p class="text-[var(--brand-primary)] font-black uppercase tracking-widest text-xs">Sedang memproses...</p>
            </div>
        </div>
    </div>

    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('toggleSidebarBtn');
        const closeBtn = document.getElementById('closeSidebarBtn');

        function toggleSidebar() {
            sidebar.classList.toggle('sidebar-hidden');
            overlay.classList.toggle('active');
        }

        toggleBtn?.addEventListener('click', toggleSidebar);
        closeBtn?.addEventListener('click', toggleSidebar);
        overlay?.addEventListener('click', toggleSidebar);

        // Sidebar responsive handling
        const handleResize = () => {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('sidebar-hidden');
                overlay.classList.remove('active');
            } else {
                sidebar.classList.add('sidebar-hidden');
                overlay.classList.remove('active');
            }
        };
        window.addEventListener('resize', handleResize);
        handleResize(); // Initial call

        // Global Loading Logic for Forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                // Don't show for tiny forms or if it has a specific no-loading class
                if (!this.classList.contains('no-loading')) {
                    document.getElementById('loading-overlay').style.display = 'block';
                }
            });
        });

        // Global Notifications using SweetAlert2
        @if(Session::has('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ Session::get('success') }}",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(Session::has('error'))
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan!',
                text: "{{ Session::get('error') }}",
                confirmButtonColor: 'var(--brand-primary)',
            });
        @endif

        @if(Session::has('info'))
            Swal.fire({
                icon: 'info',
                title: 'Informasi',
                text: "{{ Session::get('info') }}",
                confirmButtonColor: '#4f46e5',
            });
        @endif

        // Gamification: New Achievements notification
        @if(Session::has('new_achievements'))
            @foreach(Session::get('new_achievements') as $achievement)
                Swal.fire({
                    title: '<span class="text-[var(--brand-primary)] uppercase tracking-widest font-black text-xs">Achievement Unlocked!</span>',
                    html: `
                        <div class="mt-4 p-6 bg-white rounded-[2rem] border border-[var(--brand-glow)] flex flex-col items-center gap-4">
                            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center text-3xl shadow-xl ring-8 ring-[var(--brand-glow)] animate-bounce">
                                <i class="{{ $achievement['icon'] }}" style="color: {{ $achievement['color'] }}"></i>
                            </div>
                            <div class="text-center">
                                <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">{{ $achievement['name'] }}</h3>
                                <p class="text-xs text-gray-500 font-medium mt-1">{{ $achievement['description'] }}</p>
                                <div class="mt-4 inline-flex px-3 py-1 bg-[var(--brand-primary)] text-white text-[9px] font-black rounded-lg uppercase tracking-widest">+100 XP</div>
                            </div>
                        </div>
                    `,
                    showConfirmButton: true,
                    confirmButtonText: 'KEREN!',
                    confirmButtonColor: 'var(--brand-primary)',
                    backdrop: 'rgba(0, 0, 0, 0.4)',
                    customClass: {
                        popup: 'rounded-[3rem] border-none shadow-2xl overflow-hidden',
                        confirmButton: 'rounded-2xl px-8 py-3 font-black uppercase tracking-widest text-xs'
                    }
                });
            @endforeach
            @php Session::forget('new_achievements') @endphp
        @endif

        // Gamification: Level Up notification
        @if(Session::has('level_ups'))
            @foreach(Session::get('level_ups') as $levelUp)
                Swal.fire({
                    title: '<span class="text-emerald-600 uppercase tracking-widest font-black text-xs">Level Up!</span>',
                    html: `
                        <div class="mt-4 p-8 bg-white rounded-[2rem] border border-[var(--brand-glow)] flex flex-col items-center gap-6">
                            <div class="relative">
                                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center text-4xl shadow-2xl ring-8 ring-[var(--brand-glow)] scale-110 animate-pulse">
                                    <i class="fas fa-bolt text-amber-500"></i>
                                </div>
                                <div class="absolute -top-2 -right-2 bg-[var(--brand-primary)] text-white text-xs font-black w-10 h-10 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                    {{ $levelUp['new'] }}
                                </div>
                            </div>
                            <div class="text-center">
                                <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Naik ke Level {{ $levelUp['new'] }}!</h3>
                                <p class="text-xs text-gray-500 font-bold mt-2 uppercase tracking-widest">Gelar Baru: <span class="text-[var(--brand-primary)]">{{ $levelUp['title'] }}</span></p>
                                <p class="text-[10px] text-gray-400 mt-4 leading-relaxed font-medium">Bagus sekali! Teruslah belajar untuk mencapai puncak Grandmaster.</p>
                            </div>
                        </div>
                    `,
                    showConfirmButton: true,
                    confirmButtonText: 'MANTAP!',
                    confirmButtonColor: 'var(--brand-primary)',
                    backdrop: 'rgba(0, 0, 0, 0.4)',
                    customClass: {
                        popup: 'rounded-[3rem] border-none shadow-2xl overflow-hidden',
                        confirmButton: 'rounded-2xl px-8 py-3 font-black uppercase tracking-widest text-xs'
                    }
                });
            @endforeach
            @php Session::forget('level_ups') @endphp
        @endif

        // Gamification: Celebration notification
        @if(Session::has('celebrations'))
            @foreach(Session::get('celebrations') as $msg)
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '{{ $msg }}',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
            @endforeach
            @php Session::forget('celebrations') @endphp
        @endif
        // Open Arena Join Modal if session has open_arena_modal OR there are validation errors on 'code'
        @if(Session::has('open_arena_modal') || $errors->has('code'))
            document.getElementById('arenaJoinModal').classList.remove('hidden');
        @endif
    </script>
    <script src="{{ asset('js/delete-modal.js') }}"></script>
    <!-- Avatar Generator -->
    <script src="https://cdn.jsdelivr.net/npm/@multiavatar/multiavatar/multiavatar.min.js"></script>
    @if(Auth::user()->role === 'student' && \App\Models\Setting::get('enable_gamification', '1') == '1')
        <x-celebration-modal />
    @endif
    {{-- Urgent Announcement Modal: blocks interaction until user acknowledges --}}
    @if(Auth::user()->role === 'student')
        {{-- Battle Arena Join Modal --}}
        <div id="arenaJoinModal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-[200] flex items-center justify-center p-4">
            <div class="bg-gray-900 border border-white/10 rounded-[2rem] shadow-2xl w-full max-w-sm p-8 space-y-6 relative">
                <button onclick="document.getElementById('arenaJoinModal').classList.add('hidden')"
                        class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-gray-400 hover:text-white flex items-center justify-center transition">
                    <i class="fas fa-times text-xs"></i>
                </button>

                <div class="text-center space-y-2">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-red-500 to-orange-600 flex items-center justify-center mx-auto shadow-xl shadow-orange-500/30">
                        <i class="fas fa-fist-raised text-white text-2xl"></i>
                    </div>
                    <h2 class="text-xl font-black text-white tracking-tight">Join Battle Arena</h2>
                    <p class="text-gray-400 text-xs">Masukkan kode 6 karakter dari gurumu.</p>
                </div>

                <form id="arenaJoinForm" action="{{ route('student.arena.join') }}" method="POST" class="no-loading space-y-4">
                    @csrf
                    <div>
                        <input type="text" name="code" id="arenaCodeInput" maxlength="6" placeholder="XXXXXX"
                               value="{{ old('code') }}"
                               autocomplete="off" autocapitalize="characters"
                               oninput="this.value = this.value.toUpperCase()"
                               class="w-full text-center text-2xl font-black tracking-[0.4em] uppercase bg-white/5 border {{ $errors->has('code') ? 'border-red-500/50 ring-2 ring-red-500/20' : 'border-white/10' }} text-white rounded-2xl py-4 px-4 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition placeholder:text-gray-600"
                               required>
                        <div id="arenaErrorContainer">
                            @error('code')
                                <p class="text-red-500 text-[10px] font-black uppercase tracking-widest mt-3 text-center animate-pulse">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" id="arenaJoinSubmitBtn"
                            class="w-full py-3.5 bg-gradient-to-r from-red-500 to-orange-500 text-white font-black text-sm uppercase tracking-widest rounded-2xl shadow-lg hover:shadow-orange-500/30 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="btn-text"><i class="fas fa-door-open mr-2"></i> Masuk ke Lobby</span>
                        <span class="loading-text hidden"><i class="fas fa-spinner fa-spin mr-2"></i> Memeriksa...</span>
                    </button>
                </form>
            </div>
        </div>

        <script>
            document.getElementById('arenaJoinForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const form = this;
                const input = document.getElementById('arenaCodeInput');
                const errorContainer = document.getElementById('arenaErrorContainer');
                const submitBtn = document.getElementById('arenaJoinSubmitBtn');
                const btnText = submitBtn.querySelector('.btn-text');
                const loadingText = submitBtn.querySelector('.loading-text');

                // Reset state
                errorContainer.innerHTML = '';
                input.classList.remove('border-red-500/50', 'ring-2', 'ring-red-500/20');
                input.classList.add('border-white/10');
                submitBtn.disabled = true;
                btnText.classList.add('hidden');
                loadingText.classList.remove('hidden');

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        code: input.value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        throw new Error(data.message || 'Terjadi kesalahan.');
                    }
                })
                .catch(error => {
                    errorContainer.innerHTML = `
                        <p class="text-red-500 text-[10px] font-black uppercase tracking-widest mt-3 text-center animate-pulse">
                            <i class="fas fa-exclamation-triangle mr-1"></i> ${error.message}
                        </p>
                    `;
                    input.classList.add('border-red-500/50', 'ring-2', 'ring-red-500/20');
                    input.classList.remove('border-white/10');
                    submitBtn.disabled = false;
                    btnText.classList.remove('hidden');
                    loadingText.classList.add('hidden');
                });
            });
        </script>
        <x-urgent-announcement-modal />
    @endif
    {{-- Exam overlays: rendered at body root to escape overflow-hidden/auto --}}
    @stack('body-overlays')
    @stack('scripts')
</body>
</html>
