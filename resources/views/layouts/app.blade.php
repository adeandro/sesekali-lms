<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard - ' . ($configs['school_name'] ?? 'ExamFlow'))</title>
    <link rel="icon" type="image/x-icon" href="{{ isset($configs['logo']) ? asset('storage/' . $configs['logo']) : asset('favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            transition: background-color 0.5s ease, color 0.4s ease;
        }

        /* Fire Pulse Effect for Rank 1 Leaderboard */
        @keyframes fire-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes crown-bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        @keyframes premium-shine {
            0% { transform: translateX(-100%) skewX(-15deg); }
            100% { transform: translateX(200%) skewX(-15deg); }
        }

        .premium-motion {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .gpu-accelerated {
            transform: translateZ(0);
            backface-visibility: hidden;
        }

        .premium-shine {
            position: relative;
            overflow: hidden;
        }

        .premium-shine::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(
                to right,
                transparent,
                rgba(255, 255, 255, 0.1),
                transparent
            );
            transform: skewX(-15deg);
            transition: none;
        }

        .group:hover .premium-shine::after {
            animation: premium-shine 0.75s ease-in-out forwards;
        }

        .crown-bounce {
            animation: crown-bounce 4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            will-change: transform;
        }
        
        .animate-float-slow {
            animation: float 4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            will-change: transform;
        }

        .sovereign-ring {
            position: relative;
            transform: translateZ(0);
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.5);
            border-radius: 50%;
        }

        .sovereign-ring::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.4), 
                        inset 0 0 10px rgba(251, 191, 36, 0.2);
            animation: aura-pulse 3s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            z-index: -1;
            will-change: transform, opacity;
        }

        @keyframes aura-pulse {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
        }
        
        
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }
        
        .sidebar-hidden {
            transform: translateX(-100%);
        }
        
        .sidebar-mobile-overlay {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
            pointer-events: none;
        }
        
        .sidebar-mobile-overlay.active {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        
        .menu-item-active {
            background-color: var(--brand-glow) !important;
            border-left: 4px solid var(--brand-primary) !important;
            color: var(--brand-primary) !important;
            font-weight: 900 !important;
            opacity: 1 !important;
        }
        
        .submenu-item {
            @apply px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors;
        }
        
        .submenu-item-active {
            background-color: var(--brand-glow) !important;
            color: var(--brand-primary) !important;
            font-weight: 800 !important;
        }

        /* Loading Spinner */
        #loading-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.7);
            z-index: 9999;
            backdrop-filter: blur(2px);
        }
        .spinner {
            width: 40px; height: 40px;
            border: 4px solid var(--brand-glow);
            border-top: 4px solid var(--brand-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Smooth layout transitions */
        html, body {
            height: 100%;
            width: 100%;
            overflow-x: hidden;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
        }
        
        @media print {
            aside, nav, header, footer {
                display: none !important;
            }
            
            #sidebar, #sidebarOverlay {
                display: none !important;
            }
            
            .flex {
                display: block !important;
            }

            main {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }

        /* =====================================================
           THEME ENGINE v2 — Universal for ALL roles
           All theme classes always defined; access control 
           is enforced at the PHP controller level.
        ===================================================== */

        /* Base / Indigo (default) */
        :root,
        .theme-indigo {
            --brand-primary:      #4f46e5;
            --brand-secondary:    #818cf8;
            --brand-dark:         #3730a3;
            --brand-glow:         rgba(79, 70, 229, 0.2);
            --brand-bg:           #f8faff;
            --brand-surface:      #ffffff;
            --brand-text:         #1e293b;
            --brand-text-accent:  #ffffff;
            --sidebar-header:     linear-gradient(135deg, #4f46e5, #3730a3);
        }

        /* Emerald — Student only (Lv 5+) */
        .theme-emerald {
            --brand-primary:      #10b981;
            --brand-secondary:    #34d399;
            --brand-dark:         #065f46;
            --brand-glow:         rgba(16, 185, 129, 0.18);
            --brand-bg:           #f0fdf9;
            --brand-surface:      #f9fffd;
            --brand-text:         #064e3b;
            --brand-text-accent:  #ffffff;
            --sidebar-header:     linear-gradient(135deg, #10b981, #065f46);
        }

        /* Rose — Student only (Lv 25+) */
        .theme-rose {
            --brand-primary:      #f43f5e;
            --brand-secondary:    #fb7185;
            --brand-dark:         #9f1239;
            --brand-glow:         rgba(244, 63, 94, 0.18);
            --brand-bg:           #fff5f7;
            --brand-surface:      #fffbfc;
            --brand-text:         #4c0519;
            --brand-text-accent:  #ffffff;
            --sidebar-header:     linear-gradient(135deg, #f43f5e, #9f1239);
        }

        /* Amber — Student only (Lv 35+) */
        .theme-amber {
            --brand-primary:      #d97706;
            --brand-secondary:    #f59e0b;
            --brand-dark:         #78350f;
            --brand-glow:         rgba(217, 119, 6, 0.18);
            --brand-bg:           #fffbf0;
            --brand-surface:      #fffdf7;
            --brand-text:         #451a03;
            --brand-text-accent:  #ffffff;
            --sidebar-header:     linear-gradient(135deg, #d97706, #78350f);
        }

        /* Violet — Student only */
        .theme-violet {
            --brand-primary:      #7c3aed;
            --brand-secondary:    #a78bfa;
            --brand-dark:         #4c1d95;
            --brand-glow:         rgba(124, 58, 237, 0.18);
            --brand-bg:           #f7f4ff;
            --brand-surface:      #fbf9ff;
            --brand-text:         #2e1065;
            --brand-text-accent:  #ffffff;
            --sidebar-header:     linear-gradient(135deg, #7c3aed, #4c1d95);
        }

        /* Midnight — Student only (Lv 45+) — Dark Theme */
        .theme-midnight {
            --brand-primary:      #818cf8;
            --brand-secondary:    #a5b4fc;
            --brand-dark:         #0f172a;
            --brand-glow:         rgba(129, 140, 248, 0.25);
            --brand-bg:           #0f172a;
            --brand-surface:      #1e293b;
            --brand-text:         #e2e8f0;
            --brand-text-accent:  #1e293b;
            --sidebar-header:     linear-gradient(135deg, #1e293b, #0f0f1a);
        }

        /* Cyberpunk — Student only (Achievement: The Flash) — Dark Theme */
        .theme-cyberpunk {
            --brand-primary:      #e040fb;
            --brand-secondary:    #00e5ff;
            --brand-dark:         #12002b;
            --brand-glow:         rgba(224, 64, 251, 0.3);
            --brand-bg:           #06000e;
            --brand-surface:      #130025;
            --brand-text:         #f0e6ff;
            --brand-text-accent:  #06000e;
            --sidebar-header:     linear-gradient(135deg, #e040fb, #00e5ff);
        }

        /* Volcano — Student only (Lv 15+) */
        .theme-volcano {
            --brand-primary:      #ef4444;
            --brand-secondary:    #f87171;
            --brand-dark:         #7f1d1d;
            --brand-glow:         rgba(239, 68, 68, 0.18);
            --brand-bg:           #fffafa;
            --brand-surface:      #fff8f8;
            --brand-text:         #450a0a;
            --brand-text-accent:  #ffffff;
            --sidebar-header:     linear-gradient(135deg, #ef4444, #7f1d1d);
        }

        /* Slate — Professional (Admin/Teacher/Student) */
        .theme-slate {
            --brand-primary:      #475569;
            --brand-secondary:    #94a3b8;
            --brand-dark:         #1e293b;
            --brand-glow:         rgba(71, 85, 105, 0.15);
            --brand-bg:           #f6f8fa;
            --brand-surface:      #f9fbfc;
            --brand-text:         #1e293b;
            --brand-text-accent:  #ffffff;
            --sidebar-header:     linear-gradient(135deg, #334155, #1e293b);
        }

        /* Ocean — Professional (Admin/Teacher/Student) */
        .theme-ocean {
            --brand-primary:      #0284c7;
            --brand-secondary:    #38bdf8;
            --brand-dark:         #0c4a6e;
            --brand-glow:         rgba(2, 132, 199, 0.18);
            --brand-bg:           #f0f9ff;
            --brand-surface:      #f5fbff;
            --brand-text:         #0c2a3e;
            --brand-text-accent:  #ffffff;
            --sidebar-header:     linear-gradient(135deg, #0284c7, #0c4a6e);
        }

        /* =====================================================
           GAMIFICATION KILL SWITCH — Force Indigo for students
           CSS layer: non-student themes remain usable by role.
        ===================================================== */
        @if(($configs['enable_gamification'] ?? '1') != '1' && auth()->check() && auth()->user()->role === 'student')
            body {
                /* Override ANY theme class to force Indigo when gamification OFF */
                --brand-primary:      #4f46e5 !important;
                --brand-secondary:    #818cf8 !important;
                --brand-dark:         #3730a3 !important;
                --brand-glow:         rgba(79, 70, 229, 0.2) !important;
                --brand-bg:           #f8faff !important;
                --brand-surface:      #ffffff !important;
                --brand-text:         #1e293b !important;
                --brand-text-accent:  #ffffff !important;
                --sidebar-header:     linear-gradient(135deg, #4f46e5, #3730a3) !important;
            }
        @endif

        /* =====================================================
           GLOBAL THEME APPLICATION
        ===================================================== */
        body {
            background-color: var(--brand-bg) !important;
            color: var(--brand-text);
            transition: background-color 0.5s ease, color 0.4s ease;
        }

        main { background-color: var(--brand-bg) !important; }
        .bg-white  { background-color: var(--brand-surface) !important; }
        .bg-gray-50 { background-color: var(--brand-bg) !important; }
        .text-gray-900 { color: var(--brand-text) !important; }
        .text-gray-600 { color: var(--brand-text); opacity: 0.75; }

        /* Tailwind Indigo Override → Brand Primary */
        .bg-indigo-600, .bg-indigo-700, .bg-indigo-500,
        .hover\:bg-indigo-700:hover {
            background-color: var(--brand-primary) !important;
        }
        .text-indigo-600, .text-indigo-500, .text-indigo-700 {
            color: var(--brand-primary) !important;
        }
        .border-indigo-600, .border-indigo-500 {
            border-color: var(--brand-primary) !important;
        }
        .from-indigo-600, .from-indigo-500 {
            --tw-gradient-from: var(--brand-primary) !important;
            --tw-gradient-stops: var(--tw-gradient-from), var(--brand-secondary) !important;
        }
        .to-indigo-800, .to-indigo-700 {
            --tw-gradient-to: var(--brand-dark) !important;
        }
        .bg-indigo-50\/50, .bg-indigo-50, .bg-indigo-500\/10 {
            background-color: var(--brand-glow) !important;
        }
        .shadow-indigo-200, .shadow-indigo-100 {
            --tw-shadow-color: var(--brand-glow) !important;
            box-shadow: 0 8px 30px -4px var(--brand-glow) !important;
        }
        .ring-indigo-200, .ring-indigo-100 {
            --tw-ring-color: var(--brand-glow) !important;
        }
        .focus\:border-indigo-600:focus, .focus\:border-indigo-500:focus {
            border-color: var(--brand-primary) !important;
        }
        .peer-checked\:bg-indigo-600:checked ~ * {
            background-color: var(--brand-primary) !important;
        }

        /* Reusable Component Utility Classes */
        .sidebar-header-gradient  { background: var(--sidebar-header) !important; }
        .theme-active-frame        { border-color: var(--brand-primary) !important; }
        .theme-active-glow         { box-shadow: 0 0 20px var(--brand-glow), 0 0 40px var(--brand-glow); }
        .theme-surface-card        { background-color: var(--brand-surface) !important; }
        .theme-text-accent         { color: var(--brand-text-accent) !important; }
        .theme-soft-shadow         { box-shadow: 0 4px 24px -4px var(--brand-glow), 0 1px 4px rgba(0,0,0,0.04); }

        .theme-primary-btn {
            background-color: var(--brand-primary) !important;
            color: var(--brand-text-accent) !important;
            box-shadow: 0 4px 20px -4px var(--brand-glow) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .theme-primary-btn:hover {
            opacity: 0.92;
            transform: translateY(-2px);
            box-shadow: 0 12px 30px -6px var(--brand-glow) !important;
        }
        .theme-secondary-btn {
            background-color: var(--brand-glow) !important;
            color: var(--brand-primary) !important;
            transition: all 0.3s ease;
        }
        .theme-secondary-btn:hover {
            background-color: var(--brand-primary) !important;
            color: var(--brand-text-accent) !important;
        }
        .theme-card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .theme-card-hover:hover {
            transform: scale(1.02) translateY(-2px);
            box-shadow: 0 12px 40px -8px var(--brand-glow) !important;
        }

        /* Grandmaster Frame Shine */
        .frame-shine { position: relative; overflow: hidden; }
        .frame-shine::after {
            content: "";
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: linear-gradient(45deg, transparent 45%, rgba(255,255,255,0.6) 50%, transparent 55%);
            animation: shine 3s infinite;
        }
        @keyframes shine {
            0%   { transform: translateX(-100%) rotate(30deg); }
            100% { transform: translateX(100%) rotate(30deg); }
        }
    </style>
    @yield('styles')
</head>
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
                                     @if($configs['enable_gamification'] ?? '1' == '1')
                                         @if(isset($index) && $index === 0)
                                            <!-- Crown Icon for Rank 1 -->
                                            <div class="absolute -top-3 -right-2 text-amber-400 drop-shadow-[0_0_5px_rgba(251,191,36,0.8)] z-20 text-lg hover:scale-125 transition-transform origin-bottom">
                                                <i class="fas fa-crown"></i>
                                            </div>
                                         @endif
                                         <div class="absolute -bottom-1 -right-1 bg-[var(--brand-primary)] text-white text-[6px] font-black w-4 h-4 rounded-full flex items-center justify-center border border-white shadow-sm z-10">
                                             {{ (isset($index) ? $index : 0) + 1 }}
                                         </div>
                                     @endif                             <!-- Level Badge -->
                                <div class="absolute -bottom-1 -right-1 bg-[var(--brand-primary)] text-white text-[8px] font-black w-6 h-6 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                                    {{ Auth::user()->current_level }}
                                </div>
                            @else
                                <!-- Standard Avatar (Kill Switch Active or Non-Student) -->
                                <div class="w-12 h-12 rounded-full border-2 border-white shadow-sm overflow-hidden bg-white">
                                @if(Auth::user()->has_avatar)
                                    <img src="{{ Auth::user()->avatar_url }}" alt="Formal Photo" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white text-[10px] font-black italic leading-none">
                                        {{ Auth::user()->initials }}
                                    </div>
                                @endif
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-black text-gray-900 truncate tracking-tight">{{ Auth::user()->full_name }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                @if($isGamified)
                                    <span class="px-2 py-0.5 text-[8px] font-black bg-[var(--brand-primary)] text-white rounded-lg uppercase tracking-widest shadow-sm">
                                        {{ Auth::user()->level_title }}
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
                        <div class="hidden sm:flex items-center gap-3">
                            <div class="text-right">
                                <p class="text-sm font-black text-gray-900 uppercase tracking-wide leading-none group-hover:text-[var(--brand-primary)]">{{ Auth::user()->name }}</p>
                                <p class="text-[9px] font-bold text-[var(--brand-primary)] uppercase tracking-widest mt-1 opacity-80">Sesi Aktif</p>
                            </div>

                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="p-4 lg:p-8 max-w-7xl mx-auto w-full">
                    @yield('content')
                </div>
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
</body>
</html>
