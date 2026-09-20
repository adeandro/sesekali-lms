<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Karya Project Siswa - {{ $configs['school_name'] ?? 'ExamFlow' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ isset($configs['logo']) ? asset('storage/' . $configs['logo']) : asset('favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            --brand-primary: {{ $configs['brand_primary'] ?? '#4f46e5' }};
            --brand-secondary: {{ $configs['brand_secondary'] ?? '#818cf8' }};
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased flex flex-col justify-between">
    <div>
        {{-- Navbar --}}
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if(isset($configs['logo']))
                        <img src="{{ asset('storage/' . $configs['logo']) }}" alt="Logo" class="w-9 h-9 object-contain rounded-xl">
                    @else
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-black text-sm" style="background: var(--brand-primary)">
                            <i class="fas fa-cubes"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-sm font-extrabold text-slate-900 tracking-tight leading-tight">
                            {{ $configs['school_name'] ?? 'ExamFlow CBT' }}
                        </h1>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Galeri Project Siswa</p>
                    </div>
                </div>

                <div>
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white shadow-sm transition hover:opacity-90"
                           style="background: var(--brand-primary)">
                            <i class="fas fa-home"></i> Ke Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                            <i class="fas fa-sign-in-alt"></i> Masuk LMS
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        {{-- Hero Section --}}
        <section class="relative overflow-hidden pt-12 pb-14 px-4 sm:px-6 lg:px-8 text-center bg-gradient-to-b from-indigo-50/50 via-white to-transparent">
            <div class="max-w-3xl mx-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 border border-indigo-100/80 mb-4">
                    <i class="fas fa-sparkles text-amber-500"></i> Showcase & Portofolio
                </span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight leading-tight">
                    Karya Digital & Kreativitas Siswa
                </h2>
                <p class="mt-3 text-sm sm:text-base text-slate-500 max-w-xl mx-auto">
                    Kumpulan hasil karya tugas pemrograman web, desain interaktif, dan project digital yang dirancang langsung oleh para siswa.
                </p>
            </div>
        </section>

        {{-- Main Content: Assignments Grid --}}
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
            @if($assignments->isEmpty())
                <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 max-w-lg mx-auto shadow-xs">
                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fas fa-images"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Belum Ada Galeri yang Dipublikasikan</h3>
                    <p class="text-xs text-slate-400 mt-1">Saat ini belum ada assignment project yang aktif. Silakan berkunjung kembali nanti.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($assignments as $assignment)
                    <div class="group bg-white rounded-3xl border border-slate-200/80 p-6 flex flex-col justify-between hover:border-indigo-300 hover:shadow-xl hover:-translate-y-1 transition duration-300">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                @if($assignment->subject)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                        {{ $assignment->subject->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                        Umum
                                    </span>
                                @endif

                                <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-500">
                                    <i class="fas fa-layer-group text-slate-400"></i>
                                    {{ $assignment->submissions_count }} Karya
                                </span>
                            </div>

                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition leading-snug mb-2">
                                {{ $assignment->title }}
                            </h3>

                            @if($assignment->description)
                                <p class="text-xs text-slate-500 line-clamp-3 mb-4">{{ $assignment->description }}</p>
                            @endif
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] text-slate-400">
                                @if($assignment->creator)
                                    Oleh {{ $assignment->creator->name }}
                                @endif
                            </span>

                            <a href="{{ route('gallery.show', $assignment) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white shadow-sm transition hover:opacity-90"
                               style="background: var(--brand-primary)">
                                Lihat Galeri <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($assignments->hasPages())
                <div class="mt-8">
                    {{ $assignments->links() }}
                </div>
                @endif
            @endif
        </main>
    </div>

    {{-- Footer --}}
    <footer class="bg-white border-t border-slate-200/80 py-6 text-center text-xs text-slate-400">
        <div class="max-w-7xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} {{ $configs['school_name'] ?? 'ExamFlow' }}. Seluruh hak cipta karya dilindungi.</p>
        </div>
    </footer>
</body>
</html>
