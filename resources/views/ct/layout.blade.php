<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', 'Kode Merah') — {{ config('ct.school_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('head')
</head>
<body class="h-full bg-slate-900 text-slate-100 antialiased flex flex-col min-h-screen">

    {{-- Header --}}
    <header class="py-4 px-6 border-b border-slate-800 bg-slate-950/70 backdrop-blur flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-red-700 flex items-center justify-center text-xl shadow-lg shadow-red-800/30 select-none">
            🔐
        </div>
        <div>
            <h1 class="font-bold text-sm text-white tracking-wide uppercase">Kode Merah</h1>
            <p class="text-xs text-slate-400">{{ config('ct.school_name') }} &mdash; Operasi Forensik Digital</p>
        </div>
        <div class="ml-auto text-xs text-slate-500">
            @yield('header_right')
        </div>
    </header>

    {{-- Main --}}
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6">
        <div class="w-full @yield('container_width', 'max-w-md')">
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-950/80 border border-red-700/60 rounded-xl text-red-200 text-sm text-center shadow animate-pulse">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-950/80 border border-green-700/60 rounded-xl text-green-200 text-sm text-center shadow">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="py-3 px-6 border-t border-slate-800 text-center text-xs text-slate-600">
        {{ config('ct.school_name') }} &mdash; Lembar Kasus CT &mdash; Semua data diproses di server.
    </footer>

    @stack('scripts')
</body>
</html>
