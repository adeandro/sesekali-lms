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

                <a href="{{ Auth::user()->role === 'student' ? route('student.profile') : (Auth::user()->role === 'teacher' ? route('teacher.settings.index') : (Auth::user()->role === 'superadmin' ? route('admin.settings.index') : '#')) }}" 
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
                                        {{ Auth::user()->role === 'teacher' ? 'GURU' : Auth::user()->role }}
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
            <nav class="p-4 space-y-1">
                
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('dashboard*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-th-large w-5 text-lg mr-3"></i>
                    <span class="font-medium">Beranda</span>
                </a>

                <!-- Student Section (Module 5) -->
                @if(Auth::user()->role === 'student')
                    <div class="pt-4 pb-2">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ujian</p>
                    </div>

                    <!-- Available Exams -->
                    <a href="{{ route('student.exams.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('student.exams.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-file-alt w-5 text-lg mr-3"></i>
                        <span class="font-medium">Ujian Saya</span>
                        @if(request()->routeIs('student.exams.*'))
                            <i class="fas fa-chevron-right ml-auto"></i>
                        @endif
                    </a>

                    <!-- Exam Results (Module 6) -->
                    <a href="{{ route('student.results') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('student.results*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-chart-line w-5 text-lg mr-3"></i>
                        <span class="font-medium">Hasil Ujian Saya</span>
                        @if(request()->routeIs('student.results*'))
                            <i class="fas fa-chevron-right ml-auto"></i>
                        @endif
                    </a>
                @endif

                {{-- ── Communication Hub (all roles) ── --}}
                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">💬 Komunikasi</p>
                </div>

                {{-- Announcements --}}
                <a href="{{ route('communication.announcements.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('communication.announcements*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-bullhorn w-5 text-lg mr-3"></i>
                    <span class="font-medium">Pengumuman</span>
                    @if(request()->routeIs('communication.announcements*'))
                        <i class="fas fa-chevron-right ml-auto"></i>
                    @endif
                </a>

                {{-- Direct Messages with unread badge --}}
                @php $__unread = auth()->user()->unreadMessagesCount(); @endphp
                <a href="{{ route('communication.messages.inbox') }}" class="relative flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('communication.messages*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-envelope w-5 text-lg mr-3"></i>
                    <span class="font-medium">Pesan</span>
                    @if($__unread > 0)
                        <span class="ml-auto unread-badge">{{ $__unread > 99 ? '99+' : $__unread }}</span>
                    @elseif(request()->routeIs('communication.messages*'))
                        <i class="fas fa-chevron-right ml-auto"></i>
                    @endif
                </a>

                <!-- Management Section (Teacher & Superadmin) -->
                @if(in_array(Auth::user()->role, ['teacher', 'superadmin']))
                    <div class="pt-4 pb-2">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Manajemen LMS</p>
                    </div>

                    <!-- Teacher Management (Superadmin Only) -->
                    @if(Auth::user()->role === 'superadmin')
                    <a href="{{ route('superadmin.teachers.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('superadmin.teachers.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-chalkboard-teacher w-5 text-lg mr-3"></i>
                        <span class="font-medium">Manajemen Guru</span>
                        @if(request()->routeIs('superadmin.teachers.*'))
                            <i class="fas fa-chevron-right ml-auto"></i>
                        @endif
                    </a>
                    @endif

                    <!-- Students Management (Superadmin Only) -->
                    @if(Auth::user()->role === 'superadmin')
                    <a href="{{ route('admin.students.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('admin.students.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-users w-5 text-lg mr-3"></i>
                        <span class="font-medium">Siswa</span>
                        @if(request()->routeIs('admin.students.*'))
                            <i class="fas fa-chevron-right ml-auto"></i>
                        @endif
                    </a>
                    @endif

                    <!-- Subjects Management (Superadmin Only) -->
                    @if(Auth::user()->role === 'superadmin')
                    <a href="{{ route('admin.subjects.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('admin.subjects.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-book w-5 text-lg mr-3"></i>
                        <span class="font-medium">Mata Pelajaran</span>
                        @if(request()->routeIs('admin.subjects.*'))
                            <i class="fas fa-chevron-right ml-auto"></i>
                        @endif
                    </a>
                    @endif

                    <!-- Questions Management -->
                    <a href="{{ route('admin.questions.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('admin.questions.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-question-circle w-5 text-lg mr-3"></i>
                        <span class="font-medium">Soal</span>
                        @if(request()->routeIs('admin.questions.*'))
                            <i class="fas fa-chevron-right ml-auto"></i>
                        @endif
                    </a>

                    <!-- Exams Management (Module 4) -->
                    <a href="{{ route('admin.exams.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('admin.exams.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-file-alt w-5 text-lg mr-3"></i>
                        <span class="font-medium">Ujian</span>
                        @if(request()->routeIs('admin.exams.*'))
                            <i class="fas fa-chevron-right ml-auto"></i>
                        @endif
                    </a>

                    <!-- Exam Results (Module 6) -->
                    <a href="{{ route('admin.results.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('admin.results.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-chart-line w-5 text-lg mr-3"></i>
                        <span class="font-medium">Hasil</span>
                        @if(request()->routeIs('admin.results.*'))
                            <i class="fas fa-chevron-right ml-auto"></i>
                        @endif
                    </a>

                    <!-- Divider -->
                    <div class="my-4 border-t border-gray-200"></div>

                    <!-- Monitoring & Security Section (Module - Monitoring) -->
                    <div class="pb-2">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">🔒 Pengawasan & Keamanan</p>
                    </div>

                    <!-- Kelola Token - Generate & Manage tokens -->
                    <a href="{{ route('admin.tokens.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('admin.tokens.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-key w-5 text-lg mr-3"></i>
                        <span class="font-medium">Kelola Token</span>
                        @if(request()->routeIs('admin.tokens.*'))
                            <i class="fas fa-chevron-right ml-auto"></i>
                        @endif
                    </a>

                    <!-- Pantau Ujian - Links to monitoring exams list -->
                    <a href="{{ route('admin.monitor-exams.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('admin.monitor-exams.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-video w-5 text-lg mr-3"></i>
                        <span class="font-medium">Pantau Ujian</span>
                        @if(request()->routeIs('admin.monitor-exams.*'))
                            <i class="fas fa-chevron-right ml-auto"></i>
                        @endif
                    </a>
                @endif

                @if(Auth::user()->role === 'superadmin')
                <!-- ── Gamification Center (Superadmin Only) ── -->
                <div class="pt-4 pb-1">
                    <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">🏆 Gamification Center</p>
                </div>

                <div x-data="{ open: {{ request()->routeIs('admin.gamification.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="w-full flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('admin.gamification.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-trophy w-5 text-lg mr-3"></i>
                        <span class="font-medium flex-1 text-left">Gamification</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="ml-4 mt-1 space-y-0.5">
                        <a href="{{ route('admin.gamification.settings') }}"
                           class="flex items-center px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('admin.gamification.settings') ? 'submenu-item-active' : '' }}">
                            <i class="fas fa-sliders-h w-4 mr-2.5 text-sm"></i>
                            <span class="font-medium">Global Settings</span>
                        </a>
                        <a href="{{ route('admin.gamification.achievements') }}"
                           class="flex items-center px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('admin.gamification.achievements*') ? 'submenu-item-active' : '' }}">
                            <i class="fas fa-medal w-4 mr-2.5 text-sm"></i>
                            <span class="font-medium">Achievement Manager</span>
                        </a>
                        <a href="{{ route('admin.gamification.themes') }}"
                           class="flex items-center px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('admin.gamification.themes*') ? 'submenu-item-active' : '' }}">
                            <i class="fas fa-palette w-4 mr-2.5 text-sm"></i>
                            <span class="font-medium">Theme Manager</span>
                        </a>
                    </div>
                </div>
                @endif

                <!-- Divider -->
                <div class="my-4 border-t border-gray-200"></div>

                <!-- Account Section -->
                <div class="pb-2">
                    <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Akun</p>
                </div>

                @if(Auth::user()->role === 'superadmin')
                <!-- Settings -->
                <a href="{{ route('admin.settings.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('admin.settings.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-cog w-5 text-lg mr-3"></i>
                    <span class="font-medium">Pengaturan</span>
                </a>
                @elseif(Auth::user()->role === 'teacher')
                <a href="{{ route('teacher.settings.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('teacher.settings.*') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-user-cog w-5 text-lg mr-3"></i>
                    <span class="font-medium">Pengaturan</span>
                </a>
                @elseif(Auth::user()->role === 'student')
                <a href="{{ route('student.profile') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('student.profile') ? 'menu-item-active' : '' }}">
                    <i class="fas fa-user-circle w-5 text-lg mr-3"></i>
                    <span class="font-medium">Pengaturan Profil</span>
                </a>
                @endif

                <!-- Logout -->
                <form action="{{ route('logout') }}" method="POST" class="block">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition font-medium">
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
    </script>
    <script src="{{ asset('js/delete-modal.js') }}"></script>
    <!-- Avatar Generator -->
    <script src="https://cdn.jsdelivr.net/npm/@multiavatar/multiavatar/multiavatar.min.js"></script>
    @if(Auth::user()->role === 'student' && \App\Models\Setting::get('enable_gamification', '1') == '1')
        <x-celebration-modal />
    @endif
    {{-- Urgent Announcement Modal: blocks interaction until user acknowledges --}}
    @if(Auth::user()->role === 'student')
        <x-urgent-announcement-modal />
    @endif
</body>
</html>
