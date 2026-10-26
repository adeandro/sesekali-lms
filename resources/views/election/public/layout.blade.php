<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Informasi Pilketos') — {{ $settings['school_name'] ?? $settings['app_name'] ?? 'Pilketos' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-slate-900 text-slate-100 flex flex-col justify-between antialiased selection:bg-indigo-500 selection:text-white">

    {{-- Top Header (Unless in slide mode) --}}
    @hasSection('hide_header')
        {{-- Slide mode hides default header --}}
    @else
        <header class="py-4 px-6 border-b border-slate-800 bg-slate-950/80 backdrop-blur flex items-center justify-between sticky top-0 z-40">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-xl shadow-lg shadow-indigo-500/20">
                    📢
                </div>
                <div>
                    <h1 class="font-bold text-sm text-white tracking-wide uppercase">
                        {{ $settings['school_name'] ?? 'Pemilihan Ketua & Wakil Ketua OSIS' }}
                    </h1>
                    <p class="text-xs text-slate-400">
                        @yield('header_subtitle', $period->title ?? 'Portal Informasi Publik')
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @yield('header_action')
            </div>
        </header>
    @endif

    {{-- Main Content --}}
    <main class="flex-1 flex flex-col justify-start">
        @yield('content')
    </main>

    {{-- Footer (Unless hidden) --}}
    @hasSection('hide_header')
    @else
        <footer class="py-4 px-6 border-t border-slate-800 text-center text-xs text-slate-500">
            <p>{{ $settings['school_name'] ?? 'OSIS' }} &bull; Bilik Suara Digital Pilketos</p>
        </footer>
    @endif

</body>
</html>
