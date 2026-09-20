<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $assignment->title }} - Galeri Karya Siswa</title>
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
                    <a href="{{ route('gallery.index') }}" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition" title="Kembali ke Galeri">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </a>
                    @if(isset($configs['logo']))
                        <img src="{{ asset('storage/' . $configs['logo']) }}" alt="Logo" class="w-9 h-9 object-contain rounded-xl">
                    @else
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-black text-sm" style="background: var(--brand-primary)">
                            <i class="fas fa-cubes"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-sm font-extrabold text-slate-900 tracking-tight leading-tight line-clamp-1">
                            {{ $assignment->title }}
                        </h1>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                            {{ $assignment->subject ? $assignment->subject->name : ($configs['school_name'] ?? 'ExamFlow CBT') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('gallery.index') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                        <i class="fas fa-layer-group text-slate-400"></i> Semua Galeri
                    </a>

                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white shadow-sm transition hover:opacity-90"
                           style="background: var(--brand-primary)">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                            <i class="fas fa-sign-in-alt"></i> Masuk
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        {{-- Hero / Header Info --}}
        <section class="bg-gradient-to-b from-indigo-50/60 via-white to-transparent pt-10 pb-8 px-4 sm:px-6 lg:px-8 border-b border-slate-100">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div class="max-w-3xl">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                <i class="fas fa-laptop-code mr-1.5 text-indigo-500"></i> Project Showcase
                            </span>
                            @if($assignment->subject)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                    {{ $assignment->subject->name }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-500">
                                <i class="fas fa-box text-slate-400"></i> {{ $submissions->count() }} Karya Terkumpul
                            </span>
                        </div>

                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                            {{ $assignment->title }}
                        </h2>

                        @if($assignment->description)
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed max-w-2xl">
                                {{ $assignment->description }}
                            </p>
                        @endif

                        <div class="mt-3 flex items-center gap-3 text-xs text-slate-400 font-medium">
                            @if($assignment->creator)
                                <span><i class="fas fa-chalkboard-teacher mr-1"></i> Pembimbing: <strong>{{ $assignment->creator->name }}</strong></span>
                            @endif
                            @if($assignment->max_slots > 1)
                                <span>•</span>
                                <span><i class="fas fa-clone mr-1"></i> Maks. {{ $assignment->max_slots }} slot per siswa</span>
                            @endif
                        </div>
                    </div>

                    {{-- Search / Filter Box --}}
                    <div class="w-full md:w-80 flex flex-col gap-2">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" id="projectSearch" placeholder="Cari nama siswa atau karya..."
                                   class="w-full pl-9 pr-4 py-2.5 bg-white rounded-2xl border border-slate-200 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-xs">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Submissions Showcase Grid --}}
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            @if($submissions->isEmpty())
                <div class="bg-white rounded-3xl p-16 text-center border border-slate-200/80 max-w-md mx-auto shadow-xs">
                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Belum Ada Karya yang Diunggah</h3>
                    <p class="text-xs text-slate-400 mt-1">Belum ada siswa yang mengumpulkan tugas untuk assignment ini. Silakan cek kembali nanti!</p>
                </div>
            @else
                <div id="submissionsGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($submissions as $submission)
                    @php
                        $student = $submission->student;
                        $initials = '';
                        if ($student) {
                            $words = explode(' ', trim($student->name));
                            $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                        }
                    @endphp
                    <div class="submission-card group bg-white rounded-3xl border border-slate-200/80 p-5 flex flex-col justify-between hover:border-indigo-400 hover:shadow-xl hover:-translate-y-1 transition duration-300"
                         data-student-name="{{ strtolower($student->name ?? '') }}"
                         data-project-title="{{ strtolower($submission->title ?? '') }}"
                         data-classroom="{{ strtolower($student->classroom->name ?? '') }}">
                        <div>
                            {{-- Card Header: Student Avatar & Info --}}
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white font-extrabold text-sm shadow-sm flex-shrink-0"
                                     style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary))">
                                    {{ $initials ?: 'SW' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-extrabold text-slate-900 truncate leading-snug" title="{{ $student->name ?? 'Siswa' }}">
                                        {{ $student->name ?? 'Siswa' }}
                                    </h4>
                                    <p class="text-[11px] font-bold text-slate-400 truncate">
                                        {{ $student->classroom->name ?? 'Kelas -' }}
                                        @if($assignment->max_slots > 1)
                                            <span class="inline-block ml-1 px-1.5 py-0.2 bg-slate-100 text-slate-600 rounded text-[10px]">Slot {{ $submission->slot_number }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- Project Title --}}
                            <div class="mb-4">
                                <h5 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition leading-snug line-clamp-2" title="{{ $submission->title }}">
                                    {{ $submission->title }}
                                </h5>
                            </div>

                            {{-- Tech Badges --}}
                            <div class="flex flex-wrap gap-1.5 mb-4">
                                @if($submission->has_css)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100">
                                        <i class="fab fa-css3-alt"></i> CSS
                                    </span>
                                @endif
                                @if($submission->has_js)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-100">
                                        <i class="fab fa-js"></i> JS
                                    </span>
                                @endif
                                @if($submission->has_images)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                                        <i class="fas fa-image"></i> IMG
                                    </span>
                                @endif
                                @if($submission->has_audio)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-purple-50 text-purple-600 border border-purple-100">
                                        <i class="fas fa-volume-up"></i> AUD
                                    </span>
                                @endif
                                @if($submission->has_video)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100">
                                        <i class="fas fa-video"></i> VID
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                            <span class="text-[11px] font-semibold text-slate-400">
                                <i class="fas fa-file-archive mr-1"></i> {{ $submission->fileSizeFormatted() }}
                            </span>

                            <a href="{{ $submission->getIndexUrl() }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white shadow-sm transition hover:opacity-90 flex-shrink-0"
                               style="background: var(--brand-primary)">
                                Lihat Project <i class="fas fa-external-link-alt text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- No search results message --}}
                <div id="noResults" class="hidden bg-white rounded-3xl p-12 text-center border border-slate-200/80 max-w-md mx-auto my-8">
                    <i class="fas fa-search text-slate-300 text-3xl mb-3"></i>
                    <p class="text-sm font-bold text-slate-700">Tidak ada karya yang sesuai</p>
                    <p class="text-xs text-slate-400 mt-1">Coba kata kunci pencarian yang lain.</p>
                </div>
            @endif
        </main>
    </div>

    {{-- Footer --}}
    <footer class="bg-white border-t border-slate-200/80 py-6 text-center text-xs text-slate-400">
        <div class="max-w-7xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} {{ $configs['school_name'] ?? 'ExamFlow' }}. Seluruh hak cipta karya dilindungi.</p>
        </div>
    </footer>

    <script>
        const searchInput = document.getElementById('projectSearch');
        const cards = document.querySelectorAll('.submission-card');
        const noResults = document.getElementById('noResults');

        if (searchInput && cards.length > 0) {
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase().trim();
                let visibleCount = 0;

                cards.forEach(card => {
                    const student = card.dataset.studentName || '';
                    const title = card.dataset.projectTitle || '';
                    const classroom = card.dataset.classroom || '';

                    if (student.includes(query) || title.includes(query) || classroom.includes(query)) {
                        card.style.display = 'flex';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (noResults) {
                    noResults.classList.toggle('hidden', visibleCount > 0);
                }
            });
        }
    </script>
</body>
</html>
