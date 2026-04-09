@extends('layouts.blank')

@section('title', $material->title . ' - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@push('head')
<style>
    [x-cloak] { display: none !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: rgba(251,191,36,0.3); }
    
    .course-sidebar-item.active {
        background-color: #FACC15 !important;
        color: #000 !important;
    }
    .course-sidebar-item.active p { color: #000 !important; opacity: 0.8; }
    .course-sidebar-item.active i { color: #000 !important; }

    @keyframes slideIn {
        from { transform: translateX(-20px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .animate-slide-in { animation: slideIn 0.4s ease-out forwards; }
</style>
@endpush

@section('content')
<div class="flex h-screen bg-[#0f1115] text-white overflow-hidden" 
    x-data="{ 
        sidebarOpen: true,
        activeSection: {{ $material->sections->first() ? $material->sections->first()->id : 'null' }},
        sections: {{ json_encode($material->sections) }},
        completedSections: [],
        isMaterialCompleted: {{ $isCompleted ? 'true' : 'false' }},

        init() {
            if (this.activeSection) this.markViewed(this.activeSection);
            if (window.innerWidth < 1024) this.sidebarOpen = false;

            // Watch for section changes to auto-mark as viewed (Focus Mode Sync)
            this.$watch('activeSection', (newId) => {
                if (newId) this.markViewed(newId);
            });
        },

        markViewed(id) {
            if (!this.completedSections.includes(id)) {
                this.completedSections.push(id);
            }
        },

        next() {
            let index = this.sections.findIndex(s => s.id === this.activeSection);
            if (index < this.sections.length - 1) {
                this.activeSection = this.sections[index + 1].id;
                this.$nextTick(() => { this.scrollToTop(); });
            }
        },

        prev() {
            let index = this.sections.findIndex(s => s.id === this.activeSection);
            if (index > 0) {
                this.activeSection = this.sections[index - 1].id;
                this.$nextTick(() => { this.scrollToTop(); });
            }
        },

        scrollToTop() {
            const container = document.querySelector('.content-viewport');
            if (container) container.scrollTop = 0;
        },

        async completeMaterial() {
            if (this.isMaterialCompleted) return;
            try {
                const response = await fetch('{{ route('learning.complete', $material->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.isMaterialCompleted = true;
                    confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 } });
                }
            } catch (error) { console.error('Error', error); }
        }
    }"
>
    <!-- Sidebar Backdrop (Mobile Only) -->
    <div 
        x-show="sidebarOpen" 
        x-cloak
        @click="sidebarOpen = false" 
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden transition-opacity duration-300"
        x-transition:enter="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <!-- Left Chapter Sidebar (Responsive Drawer) -->
    <aside 
        class="bg-[#1a1d21] flex flex-col transition-all duration-300 fixed inset-y-0 left-0 z-50 lg:static lg:z-40 border-r border-white/5 shadow-2xl overflow-hidden w-72 lg:w-80"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:w-0 lg:-translate-x-full'"
    >
        <div class="p-6 border-b border-white/5 flex items-center justify-between shrink-0">
            <h2 class="text-sm font-black uppercase tracking-widest text-amber-400">Daftar Materi</h2>
            <button @click="sidebarOpen = false" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-white transition-colors bg-white/5 rounded-lg lg:bg-transparent">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar">
            @foreach($material->sections as $index => $section)
                <button 
                    @click="activeSection = {{ $section->id }}"
                    :class="activeSection === {{ $section->id }} ? 'active' : 'hover:bg-white/5'"
                    class="course-sidebar-item w-full p-5 flex flex-col gap-2 text-left transition-all duration-200 border-b border-white/[0.02]"
                >
                    <div class="flex items-start gap-4">
                        <div class="mt-1 shrink-0">
                            @php
                                $typeIcons = ['text' => 'fa-file-alt', 'video' => 'fa-play-circle', 'file' => 'fa-paperclip'];
                            @endphp
                            <i class="fas {{ $typeIcons[$section->type] ?? 'fa-circle' }} text-sm opacity-60"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-[12px] font-bold leading-tight line-clamp-2 uppercase tracking-tight">{{ $section->title }}</h4>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-[10px] font-medium opacity-40 uppercase tracking-widest">
                                    @if($section->type === 'video') 01:30 Menit @else Bacaan @endif
                                </span>
                                <template x-if="completedSections.includes({{ $section->id }})">
                                    <span class="flex items-center gap-1.5 text-[9px] font-black text-emerald-400 bg-emerald-400/10 px-2 py-0.5 rounded-md uppercase tracking-widest border border-emerald-400/20 shadow-[0_0_10px_rgba(52,211,153,0.1)]">
                                        <i class="fas fa-check-circle"></i> Selesai
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </button>
            @endforeach
        </div>

        <div class="p-6 bg-black/20 border-t border-white/5">
            <a href="{{ route('learning.index') }}" class="flex items-center gap-3 text-gray-500 hover:text-white transition-colors group">
                <i class="fas fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform"></i>
                <span class="text-[10px] font-black uppercase tracking-widest">Keluar Kelas</span>
            </a>
        </div>
    </aside>

    <!-- Sidebar Toggle (When Hidden) -->
    <div x-show="!sidebarOpen" x-cloak class="fixed left-4 top-4 z-40 lg:z-50">
        <button @click="sidebarOpen = true" class="w-10 h-10 bg-amber-400 text-black rounded-xl shadow-xl flex items-center justify-center hover:scale-110 transition-transform border-4 border-[#0f1115]">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Main Content Canvas -->
    <main class="flex-1 flex flex-col min-w-0 bg-[#0f1115]">
        <!-- Top Nav Info -->
        <header class="h-16 flex items-center justify-between px-4 lg:px-8 border-b border-white/5 bg-[#14171c]/50 backdrop-blur-md shrink-0">
            <div class="flex items-center gap-2 lg:gap-4 text-[10px] lg:text-xs font-bold text-gray-500 overflow-hidden"
                 :class="!sidebarOpen ? 'ml-12 lg:ml-0' : ''">
                <span class="hidden md:inline shrink-0">Materi Saya</span>
                <i class="hidden md:inline fas fa-chevron-right text-[8px] opacity-30"></i>
                <span class="hidden sm:inline shrink-0 text-gray-300 max-w-[80px] lg:max-w-none truncate">{{ $material->subject->name ?? 'Mata Pelajaran' }}</span>
                <i class="hidden sm:inline fas fa-chevron-right text-[8px] opacity-30"></i>
                <span class="truncate text-amber-400 font-black">{{ $material->title }}</span>
            </div>
            
            <div class="flex items-center gap-3 lg:gap-4 ml-2">
                <button 
                    @click="isMaterialCompleted ? window.location.href='{{ route('learning.index') }}' : completeMaterial()"
                    class="h-9 lg:h-10 px-4 lg:px-6 rounded-xl text-[9px] lg:text-[10px] font-black uppercase tracking-widest transition-all shadow-lg whitespace-nowrap"
                    :class="isMaterialCompleted ? 'bg-emerald-500 text-white' : 'bg-white/5 text-gray-400 hover:bg-white/10 hover:text-white'"
                >
                    <span x-text="isMaterialCompleted ? '√ Selesai' : 'Selesaikan'"></span>
                </button>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto content-viewport custom-scrollbar bg-[#0f1115]">
            <div class="max-w-[1000px] mx-auto p-8 lg:p-16 pb-32">
                @foreach($material->sections as $section)
                    <div x-show="activeSection === {{ $section->id }} " 
                         x-cloak 
                         class="animate-slide-in space-y-10"
                    >
                        <!-- Content Heading -->
                        <h1 class="text-3xl lg:text-5xl font-black text-white leading-tight tracking-tight">{{ $section->title }}</h1>

                        <!-- Content Frame -->
                        <div class="bg-[#1a1d21] rounded-3xl border border-white/5 overflow-hidden shadow-2xl">
                            @if($section->type === 'text')
                                <div class="p-10 lg:p-16 prose prose-invert prose-lg max-w-none text-gray-300 leading-relaxed quill-content">
                                    {!! $section->content !!}
                                </div>
                            @elseif($section->type === 'video')
                                <div class="aspect-video bg-black">
                                    @php
                                        $videoUrl = $section->video_url;
                                        $embedUrl = '';
                                        
                                        // Robust YouTube ID Extraction (UX 4.2)
                                        if (str_contains($videoUrl, 'youtube.com') || str_contains($videoUrl, 'youtu.be')) {
                                            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?|shorts)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $videoUrl, $match)) {
                                                $videoId = $match[1];
                                            }
                                            
                                            if ($videoId) {
                                                $embedUrl = "https://www.youtube.com/embed/{$videoId}?rel=0&modestbranding=1&autoplay=0";
                                            }
                                        } elseif (str_contains($videoUrl, 'drive.google.com')) {
                                            $embedUrl = str_replace('/view', '/preview', $videoUrl);
                                        } else {
                                            $embedUrl = $videoUrl;
                                        }
                                    @endphp
                                    @if($embedUrl)
                                        <iframe src="{{ $embedUrl }}" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-500 gap-4">
                                            <i class="fas fa-exclamation-triangle text-4xl"></i>
                                            <p class="text-sm font-bold opacity-60">Format video tidak didukung atau link rusak.</p>
                                        </div>
                                    @endif
                                </div>
                            @elseif($section->type === 'file')
                                <div class="flex flex-col h-[800px] bg-[#1a1d21]">
                                    <!-- PDF Control Bar -->
                                    <div class="h-14 px-6 flex items-center justify-between bg-black/40 border-b border-white/5">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-file-pdf text-rose-500"></i>
                                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Pratinjau Dokumen</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ asset('storage/' . $section->file_path) }}" target="_blank" class="h-8 px-4 bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white rounded-lg transition-all flex items-center gap-2 text-[9px] font-bold uppercase tracking-wider">
                                                <i class="fas fa-external-link-alt"></i> Jendela Baru
                                            </a>
                                            <a href="{{ asset('storage/' . $section->file_path) }}" download class="h-8 px-4 bg-amber-400 hover:bg-amber-500 text-black rounded-lg transition-all flex items-center gap-2 text-[9px] font-bold uppercase tracking-wider">
                                                <i class="fas fa-download"></i> Unduh PDF
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <!-- Embedded PDF Viewer -->
                                    <div class="flex-1 w-full bg-[#111317]">
                                        <object 
                                            data="{{ asset('storage/' . $section->file_path) }}#toolbar=0&navpanes=0&scrollbar=1" 
                                            type="application/pdf" 
                                            class="w-full h-full"
                                        >
                                            <div class="w-full h-full flex flex-col items-center justify-center p-12 text-center gap-6">
                                                <div class="w-20 h-20 bg-rose-500/10 rounded-2xl flex items-center justify-center text-rose-500 border border-rose-500/20">
                                                    <i class="fas fa-file-pdf text-3xl"></i>
                                                </div>
                                                <div class="space-y-2">
                                                    <p class="text-lg font-black text-white">Browser Tidak Mendukung Viewer</p>
                                                    <p class="text-xs text-gray-500">Silakan unduh file untuk melihat materi secara lengkap.</p>
                                                </div>
                                                <a href="{{ asset('storage/' . $section->file_path) }}" download class="h-12 px-8 bg-amber-400 text-black rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-amber-500 transition-all">
                                                    Unduh Materi Sekarang
                                                </a>
                                            </div>
                                        </object>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Sticky Bottom Nav (Modern Floating) -->
        <div class="fixed bottom-6 lg:bottom-10 left-[50%] lg:left-[calc(50%+160px)] -translate-x-[50%] z-30 w-[92%] max-w-[800px] pointer-events-none transition-all duration-300"
             :style="sidebarOpen ? '' : 'left: 50%'">
            <div class="bg-[#1a1d21]/95 backdrop-blur-3xl border border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.5)] rounded-2xl p-3 lg:p-4 flex items-center justify-between gap-4 pointer-events-auto">
                <button @click="prev()" 
                        :disabled="sections.findIndex(s => s.id === activeSection) === 0"
                        class="h-10 lg:h-12 px-4 lg:px-6 bg-white/5 text-gray-400 rounded-xl flex items-center gap-3 hover:text-white transition-all disabled:opacity-0">
                    <i class="fas fa-arrow-left text-xs lg:text-base"></i> <span class="hidden md:inline text-[10px] font-black uppercase tracking-tighter">Sebelumnya</span>
                </button>

                <div class="hidden lg:flex items-center gap-2">
                    <template x-for="(section, index) in sections">
                        <div class="h-1.5 rounded-full transition-all duration-300"
                            :class="activeSection === section.id ? 'w-8 bg-amber-400' : 'w-1.5 bg-white/10'"></div>
                    </template>
                </div>

                <div class="flex items-center gap-3">
                    <template x-if="sections.findIndex(s => s.id === activeSection) < sections.length - 1">
                        <button @click="next()" class="h-10 lg:h-12 px-6 lg:px-8 bg-amber-400 text-black text-[9px] lg:text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-amber-500 transition-all flex items-center gap-2 lg:gap-4 shadow-lg shadow-amber-400/20">
                            <span class="lg:inline">Selanjutnya</span> <i class="fas fa-arrow-right text-xs lg:text-base"></i>
                        </button>
                    </template>
                    <template x-if="sections.findIndex(s => s.id === activeSection) === sections.length - 1">
                        <div class="flex items-center gap-3">
                            <template x-if="!isMaterialCompleted">
                                <button @click="completeMaterial()" class="h-10 lg:h-12 px-6 lg:px-8 bg-emerald-500 text-white text-[9px] lg:text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-900/20">
                                    √ Selesai
                                </button>
                            </template>
                            <template x-if="isMaterialCompleted">
                                @if($material->exam_id)
                                    <a href="{{ route('student.exams.start', $material->exam_id) }}" class="h-10 lg:h-12 px-6 lg:px-8 bg-amber-400 text-black text-[9px] lg:text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-amber-500 transition-all animate-pulse shadow-xl shadow-amber-900/30 whitespace-nowrap">
                                        Latihan Soal <i class="fas fa-pencil-alt ml-1 lg:ml-2"></i>
                                    </a>
                                @else
                                    <a href="{{ route('learning.index') }}" class="h-12 px-8 bg-indigo-500 text-white text-[10px] font-black uppercase tracking-widest rounded-xl">
                                        Kembali ke Beranda <i class="fas fa-home ml-2"></i>
                                    </a>
                                @endif
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
