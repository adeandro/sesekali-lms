<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard - SesekaliCBT')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            @apply bg-blue-50 border-l-4 border-blue-600 text-blue-700;
        }
        
        .submenu-item {
            @apply px-4 py-2 text-sm text-gray-700 hover:bg-gray-100;
        }
        
        .submenu-item-active {
            @apply bg-blue-50 text-blue-700;
        }

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
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 flex items-center justify-between sticky top-0 z-10">
                <div>
                    <h2 class="text-2xl font-bold">ExamFlow</h2>
                    <p class="text-xs text-blue-100">Learning Managemen System</p>
                </div>
                <button id="closeSidebarBtn" class="lg:hidden text-white hover:bg-blue-800 p-2 rounded transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- User Profile Card -->
            <div class="px-4 py-4 border-b border-gray-200">
                <div class="bg-blue-50 p-3 rounded-lg">
                    <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                    <div class="mt-1">
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full 
                            @if(Auth::user()->role === 'superadmin') bg-red-100 text-red-800
                            @elseif(Auth::user()->role === 'admin') bg-blue-100 text-blue-800
                            @else bg-green-100 text-green-800 @endif">
                            <i class="fas fa-shield-alt mr-1"></i>{{ ucfirst(Auth::user()->role) }}
                        </span>
                    </div>
                </div>
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

                <!-- Management Section (Admin & Superadmin) -->
                @if(in_array(Auth::user()->role, ['admin', 'superadmin']))
                    <div class="pt-4 pb-2">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Management</p>
                    </div>

                    <!-- Students Management -->
                    <a href="{{ route('admin.students.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('admin.students.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-users w-5 text-lg mr-3"></i>
                        <span class="font-medium">Siswa</span>
                        @if(request()->routeIs('admin.students.*'))
                            <i class="fas fa-chevron-right ml-auto"></i>
                        @endif
                    </a>

                    <!-- Subjects Management -->
                    <a href="{{ route('admin.subjects.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('admin.subjects.*') ? 'menu-item-active' : '' }}">
                        <i class="fas fa-book w-5 text-lg mr-3"></i>
                        <span class="font-medium">Mata Pelajaran</span>
                        @if(request()->routeIs('admin.subjects.*'))
                            <i class="fas fa-chevron-right ml-auto"></i>
                        @endif
                    </a>

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

                <!-- Settings -->
                <a href="#" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-cog w-5 text-lg mr-3"></i>
                    <span class="font-medium">Pengaturan</span>
                </a>

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
                <div class="px-4 lg:px-8 py-4 flex items-center justify-between gap-4">
                    <button id="toggleSidebarBtn" class="lg:hidden text-gray-600 hover:text-gray-900 p-2 -ml-2 transition">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div class="hidden lg:block flex-1">
                        <h1 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="hidden sm:block text-sm text-gray-600 font-medium">
                            {{ Auth::user()->name }}
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="p-4 lg:p-8 max-w-7xl mx-auto w-full">
                    @if ($message = Session::get('success'))
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 flex items-start gap-3">
                            <i class="fas fa-check-circle flex-shrink-0 mt-0.5"></i>
                            <div class="flex-1">
                                <p class="font-semibold">Berhasil</p>
                                <p class="text-sm">{{ $message }}</p>
                            </div>
                            <button onclick="this.parentElement.style.display='none'" class="text-green-800 hover:text-green-600 transition">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endif

                    @if ($message = Session::get('error'))
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 flex items-start gap-3">
                            <i class="fas fa-exclamation-circle flex-shrink-0 mt-0.5"></i>
                            <div class="flex-1">
                                <p class="font-semibold">Kesalahan</p>
                                <p class="text-sm">{{ $message }}</p>
                            </div>
                            <button onclick="this.parentElement.style.display='none'" class="text-red-800 hover:text-red-600 transition">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
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

        // Close sidebar on larger screens
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('sidebar-hidden');
                overlay.classList.remove('active');
            } else {
                sidebar.classList.add('sidebar-hidden');
                overlay.classList.remove('active');
            }
        });

        // Close sidebar when clicking on a link
        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    toggleSidebar();
                }
            });
        });

        // Close alert messages with button
        document.querySelectorAll('[onclick*="style.display"]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
            });
        });
    </script>
</body>
</html>
