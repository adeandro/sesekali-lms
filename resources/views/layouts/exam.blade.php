<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Exam - ' . ($configs['school_name'] ?? 'ExamFlow'))</title>
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
    
    <!-- Very minimal clean container, NO overflows, NO flex constraints -->
    <div id="exam-app">
        @yield('content')
    </div>

    <!-- Exam overlays: rendered cleanly at body root -->
    @stack('body-overlays')
    
    @stack('scripts')
</body>
</html>
