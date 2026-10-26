<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Bilik Suara') — {{ $settings['school_name'] ?? $settings['app_name'] ?? 'Pilketos' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-slate-900 text-slate-100 flex flex-col justify-between antialiased selection:bg-indigo-500 selection:text-white">

    {{-- Top Banner --}}
    <header class="py-4 px-6 border-b border-slate-800 bg-slate-950/60 backdrop-blur flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-xl shadow-lg shadow-indigo-500/20">
                🗳️
            </div>
            <div>
                <h1 class="font-bold text-sm text-white tracking-wide uppercase">
                    {{ $settings['school_name'] ?? 'Bilik Suara Digital' }}
                </h1>
                <p class="text-xs text-slate-400">
                    {{ $period->title ?? 'Pemilihan Ketua & Wakil Ketua OSIS' }}
                </p>
            </div>
        </div>

        @yield('header_extra')
    </header>

    {{-- Main Body --}}
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 !bg-slate-900">
        <div class="w-full @yield('container_width', 'max-w-md')">
            {{-- Flash Messages --}}
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-950/80 border border-red-800/80 rounded-xl text-red-200 text-sm text-center shadow-lg animate-shake">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-950/80 border border-green-800/80 rounded-xl text-green-200 text-sm text-center shadow-lg">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="py-3 px-6 border-t border-slate-800 text-center text-xs text-slate-400">
        &bull; Hak suara bersifat rahasia, bebas, dan jujur &bull;
    </footer>

</body>
</html>
