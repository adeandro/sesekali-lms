@extends('layouts.app')

@section('title', 'Superadmin Dashboard - ' . ($configs['school_name'] ?? 'ExamFlow'))

@section('page-title', 'Superadmin Dashboard')

@section('content')
    <div class="space-y-8">
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <p class="text-[10px] font-black uppercase tracking-[0.25em] text-gray-400">📍 Dashboard — Superadmin</p>
            <h2 class="text-2xl font-black tracking-tight animate-fadeIn" style="color: var(--brand-text);">
                Selamat {{ $greetingWord }}, <span style="color: var(--brand-primary);">{{ auth()->user()->full_name }}!</span> {{ $greetingEmoji }}
            </h2>
            <p class="text-sm font-bold text-gray-500">{{ $greetingMotif }}</p>
        </div>
        <div class="text-sm text-gray-500 px-4 py-2 rounded-full shadow-sm border" style="background-color: var(--brand-surface); border-color: var(--brand-glow);">
            <i class="fas fa-calendar-alt mr-2" style="color: var(--brand-primary);"></i> Tahun Ajaran: {{ $configs['academic_year'] ?? '2023/2024' }}
        </div>
    </div>

    {{-- Main Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Total Pengguna --}}
        <div class="bg-white rounded-xl border theme-soft-shadow theme-card-hover p-6 animate-fadeIn" style="border-color: var(--brand-glow);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Pengguna</p>
                    <p class="text-3xl font-bold mt-1" style="color: var(--brand-text);">{{ $totalUsers }}</p>
                </div>
                <div class="p-3 rounded-lg" style="background-color: var(--brand-glow); color: var(--brand-primary);">
                    <i class="fas fa-users-cog text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-semibold" style="color: var(--brand-primary);">
                <i class="fas fa-check-circle mr-1"></i> {{ $activeUsersCount }} Aktif
            </div>
        </div>

        {{-- Guru --}}
        <div class="bg-white rounded-xl border theme-soft-shadow theme-card-hover p-6 animate-fadeIn delay-100" style="border-color: var(--brand-glow);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Guru (Teachers)</p>
                    <p class="text-3xl font-bold mt-1" style="color: var(--brand-text);">{{ $teacherCount }}</p>
                </div>
                <div class="p-3 rounded-lg" style="background-color: var(--brand-glow); color: var(--brand-primary);">
                    <i class="fas fa-chalkboard-teacher text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-xs text-gray-400">Pendidik terdaftar</div>
        </div>

        {{-- Siswa --}}
        <div class="bg-white rounded-xl border theme-soft-shadow theme-card-hover p-6 animate-fadeIn delay-200" style="border-color: var(--brand-glow);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Siswa (Students)</p>
                    <p class="text-3xl font-bold mt-1" style="color: var(--brand-text);">{{ $studentCount }}</p>
                </div>
                <div class="p-3 rounded-lg bg-emerald-50 text-emerald-600">
                    <i class="fas fa-user-graduate text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-xs text-gray-400">Peserta didik terdaftar</div>
        </div>

        {{-- Superadmin --}}
        <div class="bg-white rounded-xl border theme-soft-shadow theme-card-hover p-6 animate-fadeIn delay-300" style="border-color: var(--brand-glow);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Superadmin</p>
                    <p class="text-3xl font-bold mt-1" style="color: var(--brand-text);">{{ $superadminCount }}</p>
                </div>
                <div class="p-3 rounded-lg bg-rose-50 text-rose-600">
                    <i class="fas fa-user-shield text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-xs text-gray-400">Administrator utama</div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 animate-fadeIn delay-500">
        <div class="bg-white rounded-3xl p-8 border border-indigo-50 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Distribusi Akun Pengguna</h3>
                <i class="fas fa-chart-pie text-indigo-200"></i>
            </div>
            <div class="relative h-[300px]">
                <canvas id="userDistChartSuper"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-8 border border-indigo-50 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Statistik Sistem</h3>
                <i class="fas fa-chart-bar text-indigo-200"></i>
            </div>
            <div class="relative h-[300px]">
                <canvas id="systemStatsChartSuper"></canvas>
            </div>
        </div>
    </div>

    {{-- Management Sections --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left & Middle: Quick Access Cards --}}
        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- User Control --}}
            <div class="bg-white rounded-xl border theme-soft-shadow overflow-hidden" style="border-color: var(--brand-glow);">
                <div class="px-6 py-4 border-b" style="border-color: var(--brand-glow); background-color: var(--brand-bg);">
                    <h3 class="font-bold flex items-center" style="color: var(--brand-text);">
                        <i class="fas fa-users mr-2" style="color: var(--brand-primary);"></i> Kontrol Pengguna
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('superadmin.teachers.index') }}" class="flex items-center justify-between p-3 rounded-lg border transition-all group hover:shadow-sm" style="border-color: var(--brand-glow);">
                        <span class="font-medium" style="color: var(--brand-text);">Manajemen Guru</span>
                        <i class="fas fa-arrow-right text-gray-300 group-hover:translate-x-1 transition-all" style="color: var(--brand-primary);"></i>
                    </a>
                    <a href="{{ route('admin.students.index') }}" class="flex items-center justify-between p-3 rounded-lg border transition-all group hover:shadow-sm" style="border-color: var(--brand-glow);">
                        <span class="font-medium" style="color: var(--brand-text);">Data Siswa</span>
                        <i class="fas fa-arrow-right text-gray-300 group-hover:translate-x-1 transition-all" style="color: var(--brand-primary);"></i>
                    </a>
                </div>
            </div>

            {{-- Academic & System --}}
            <div class="bg-white rounded-xl border theme-soft-shadow overflow-hidden" style="border-color: var(--brand-glow);">
                <div class="px-6 py-4 border-b" style="border-color: var(--brand-glow); background-color: var(--brand-bg);">
                    <h3 class="font-bold flex items-center" style="color: var(--brand-text);">
                        <i class="fas fa-laptop-code mr-2" style="color: var(--brand-primary);"></i> LMS & Sistem
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('admin.subjects.index') }}" class="flex items-center justify-between p-3 rounded-lg border transition-all group hover:shadow-sm" style="border-color: var(--brand-glow);">
                        <span class="font-medium" style="color: var(--brand-text);">Mata Pelajaran</span>
                        <i class="fas fa-arrow-right text-gray-300 group-hover:translate-x-1 transition-all" style="color: var(--brand-primary);"></i>
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="flex items-center justify-between p-3 rounded-lg border transition-all group hover:shadow-sm" style="border-color: var(--brand-glow);">
                        <span class="font-medium" style="color: var(--brand-text);">Pengaturan Global</span>
                        <i class="fas fa-arrow-right text-gray-300 group-hover:translate-x-1 transition-all" style="color: var(--brand-primary);"></i>
                    </a>
                </div>
            </div>

            {{-- School Identity Banner --}}
            <div class="md:col-span-2 rounded-xl shadow-lg p-8 text-white" style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-dark));">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="space-y-2">
                        <h3 class="text-2xl font-bold">Identitas Sekolah: {{ $configs['school_name'] ?? 'ExamFlow' }}</h3>
                        <p class="opacity-80">Kustomisasi logo, nama, dan konfigurasi sistem di menu Pengaturan.</p>
                    </div>
                    <a href="{{ route('admin.settings.index') }}" class="px-6 py-3 bg-white rounded-lg font-bold hover:opacity-90 transition-colors shadow-sm whitespace-nowrap" style="color: var(--brand-primary);">Buka Pengaturan</a>
                </div>
            </div>
        </div>

        {{-- Right: System Info --}}
        <div class="bg-white rounded-xl border theme-soft-shadow overflow-hidden h-fit" style="border-color: var(--brand-glow);">
            <div class="px-6 py-4 border-b" style="border-color: var(--brand-glow); background-color: var(--brand-bg);">
                <h3 class="font-bold" style="color: var(--brand-text);">🛠️ Info Server</h3>
            </div>
            <div class="p-6">
                <ul class="space-y-4">
                    <li class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Laravel Version</span>
                        <span class="font-bold" style="color: var(--brand-text);">{{ app()::VERSION }}</span>
                    </li>
                    <li class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">PHP Version</span>
                        <span class="font-bold" style="color: var(--brand-text);">{{ PHP_VERSION }}</span>
                    </li>
                    <li class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Environment</span>
                        <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-xs font-bold uppercase">{{ app()->environment() }}</span>
                    </li>
                    <li class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Anti-Cheat Mode</span>
                        <span class="px-2 py-1 rounded text-xs font-bold uppercase {{ ($configs['anti_cheat_active'] ?? 1) ? '' : 'bg-gray-100 text-gray-700' }}" style="{{ ($configs['anti_cheat_active'] ?? 1) ? 'background-color: var(--brand-glow); color: var(--brand-primary);' : '' }}">
                            {{ ($configs['anti_cheat_active'] ?? 1) ? 'Enabled' : 'Disabled' }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // User Distribution Chart
        const userCtx = document.getElementById('userDistChartSuper').getContext('2d');
        new Chart(userCtx, {
            type: 'doughnut',
            data: {
                labels: ['Guru', 'Siswa', 'Superadmin'],
                datasets: [{
                    data: [{{ $teacherCount }}, {{ $studentCount }}, {{ $superadminCount }}],
                    backgroundColor: ['#6366f1', '#10b981', '#f43f5e'],
                    borderWidth: 0,
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 30,
                            font: { size: 11, weight: 'bold' }
                        }
                    }
                }
            }
        });

        // System Stats Chart
        const sysCtx = document.getElementById('systemStatsChartSuper').getContext('2d');
        new Chart(sysCtx, {
            type: 'bar',
            data: {
                labels: ['Ujian', 'Mata Pelajaran', 'Ruang Ujian'],
                datasets: [{
                    label: 'Total Data',
                    data: [
                        {{ $configs['total_exams'] ?? 0 }}, 
                        {{ $configs['total_subjects'] ?? 0 }}, 
                        {{ $configs['total_rooms'] ?? 0 }}
                    ],
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderColor: '#6366f1',
                    borderWidth: 2,
                    borderRadius: 12,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
</script>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out both; }
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }
    .delay-500 { animation-delay: 0.5s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
