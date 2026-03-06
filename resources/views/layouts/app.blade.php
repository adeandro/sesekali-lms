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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
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
            @apply bg-indigo-50 border-l-4 border-indigo-600 text-indigo-700 font-bold;
        }
        
        .submenu-item {
            @apply px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors;
        }
        
        .submenu-item-active {
            @apply bg-indigo-50 text-indigo-700;
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
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4f46e5;
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
    </style>
    @yield('styles')
</head>
<body class="bg-gray-50">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="sidebar-mobile-overlay fixed inset-0 bg-black/50 lg:hidden z-30"></div>

    <!-- Page Wrapper -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar-transition sidebar-hidden lg:translate-x-0 fixed lg:relative left-0 top-0 h-screen w-64 bg-white shadow-lg lg:shadow-none z-40 overflow-y-auto flex-shrink-0">
            <!-- Sidebar Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white p-6 flex items-center justify-between sticky top-0 z-10 shadow-sm">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">{{ $configs['school_name'] ?? 'SesekaliCBT' }}</h2>
                    <p class="text-[10px] text-indigo-100 uppercase tracking-widest font-semibold">{{ $configs['academic_year'] ?? '2023/2024' }}</p>
                </div>
                <button id="closeSidebarBtn" class="lg:hidden text-indigo-100 hover:text-white hover:bg-white/10 p-2 rounded-lg transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="px-4 py-6 border-b border-gray-100">
                <a href="{{ Auth::user()->role === 'teacher' ? route('teacher.settings.index') : (Auth::user()->role === 'superadmin' ? route('admin.settings.index') : '#') }}" 
                   class="bg-indigo-50/50 p-4 rounded-xl flex items-center gap-4 border border-indigo-100/50 hover:bg-indigo-100/50 transition-colors group">
                    <img src="{{ Auth::user()->photo_url }}" alt="Avatar" class="w-12 h-12 rounded-full border-2 border-white shadow-sm object-cover group-hover:scale-105 transition-transform">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-0.5 text-[10px] font-extrabold rounded-md uppercase tracking-wider
                                @if(Auth::user()->role === 'superadmin') bg-rose-100 text-rose-700
                                @elseif(Auth::user()->role === 'teacher') bg-indigo-100 text-indigo-700
                                @else bg-emerald-100 text-emerald-700 @endif">
                                {{ Auth::user()->role === 'teacher' ? 'GURU' : Auth::user()->role }}
                            </span>
                        </div>
                    </div>
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
            <nav class="bg-white shadow-sm z-20 flex-shrink-0">
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
                                <p class="text-sm font-black text-gray-900 uppercase tracking-wide leading-none">{{ Auth::user()->name }}</p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">Sesi Aktif</p>
                            </div>
                            <a href="{{ Auth::user()->role === 'teacher' ? route('teacher.settings.index') : (Auth::user()->role === 'superadmin' ? route('admin.settings.index') : '#') }}">
                                <img src="{{ Auth::user()->photo_url }}" alt="Avatar" class="w-10 h-10 rounded-xl object-cover border-2 border-white shadow-sm hover:scale-105 transition-transform">
                            </a>
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
                <p class="text-indigo-900 font-bold">Sedang memproses...</p>
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
                confirmButtonColor: '#4f46e5',
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
    </script>
    <script src="{{ asset('js/delete-modal.js') }}"></script>
</body>
</html>
