@extends('layouts.app')

@section('title', 'Superadmin Dashboard - ' . ($configs['school_name'] ?? 'ExamFlow'))

@section('page-title', 'Superadmin Dashboard')

@section('content')
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Superadmin Dashboard</h2>
            <div class="text-sm text-gray-500 bg-white px-4 py-2 rounded-full shadow-sm border border-gray-100">
                <i class="fas fa-calendar-alt mr-2 text-blue-500"></i> Tahun Ajaran: {{ $configs['academic_year'] ?? '2023/2024' }}
            </div>
        </div>

        <!-- Main Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Pengguna -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Pengguna</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalUsers }}</p>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-lg text-blue-600">
                        <i class="fas fa-users-cog text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs font-semibold text-green-600">
                    <i class="fas fa-check-circle mr-1"></i> {{ $activeUsersCount }} Aktif
                </div>
            </div>

            <!-- Guru -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Guru (Teachers)</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $teacherCount }}</p>
                    </div>
                    <div class="bg-indigo-50 p-3 rounded-lg text-indigo-600">
                        <i class="fas fa-chalkboard-teacher text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-400">
                    Pendidik terdaftar
                </div>
            </div>

            <!-- Siswa -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Siswa (Students)</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $studentCount }}</p>
                    </div>
                    <div class="bg-green-50 p-3 rounded-lg text-green-600">
                        <i class="fas fa-user-graduate text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-400">
                    Peserta didik terdaftar
                </div>
            </div>

            <!-- Admin -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Superadmin</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $superadminCount }}</p>
                    </div>
                    <div class="bg-red-50 p-3 rounded-lg text-red-600">
                        <i class="fas fa-user-shield text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-400">
                    Administrator utama
                </div>
            </div>
        </div>

        <!-- Management Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left & Middle: Quick Access Cards -->
            <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- User Control -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50">
                        <h3 class="font-bold text-gray-800 flex items-center">
                            <i class="fas fa-users mr-2 text-blue-500"></i> Kontrol Pengguna
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('superadmin.teachers.index') }}" class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg hover:bg-blue-50 hover:border-blue-100 transition-all group">
                            <span class="text-gray-700 font-medium group-hover:text-blue-700">Manajemen Guru</span>
                            <i class="fas fa-arrow-right text-gray-300 group-hover:text-blue-500 transform group-hover:translate-x-1 transition-all"></i>
                        </a>
                        <a href="{{ route('admin.students.index') }}" class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg hover:bg-blue-50 hover:border-blue-100 transition-all group">
                            <span class="text-gray-700 font-medium group-hover:text-blue-700">Data Siswa</span>
                            <i class="fas fa-arrow-right text-gray-300 group-hover:text-blue-500 transform group-hover:translate-x-1 transition-all"></i>
                        </a>
                    </div>
                </div>

                <!-- Academic & System -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50">
                        <h3 class="font-bold text-gray-800 flex items-center">
                            <i class="fas fa-laptop-code mr-2 text-indigo-500"></i> LMS & Sistem
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('admin.subjects.index') }}" class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg hover:bg-indigo-50 hover:border-indigo-100 transition-all group">
                            <span class="text-gray-700 font-medium group-hover:text-indigo-700">Mata Pelajaran</span>
                            <i class="fas fa-arrow-right text-gray-300 group-hover:text-indigo-500 transform group-hover:translate-x-1 transition-all"></i>
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg hover:bg-indigo-50 hover:border-indigo-100 transition-all group">
                            <span class="text-gray-700 font-medium group-hover:text-indigo-700">Pengaturan Global</span>
                            <i class="fas fa-arrow-right text-gray-300 group-hover:text-indigo-500 transform group-hover:translate-x-1 transition-all"></i>
                        </a>
                    </div>
                </div>

                <!-- Content Stats Card -->
                <div class="md:col-span-2 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-lg p-8 text-white">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="space-y-2">
                            <h3 class="text-2xl font-bold">Identitas Sekolah: {{ $configs['school_name'] ?? 'ExamFlow' }}</h3>
                            <p class="text-blue-100">Kustomisasi logo, nama, dan keamanan ujian di menu Pengaturan.</p>
                        </div>
                        <a href="{{ route('admin.settings.index') }}" class="px-6 py-3 bg-white text-blue-600 rounded-lg font-bold hover:bg-blue-50 transition-colors shadow-sm whitespace-nowrap">
                            Buka Pengaturan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right: System Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-fit">
                <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="font-bold text-gray-800">🛠️ Info Server</h3>
                </div>
                <div class="p-6">
                    <ul class="space-y-4">
                        <li class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Laravel Version</span>
                            <span class="font-bold text-gray-800">{{ app()::VERSION }}</span>
                        </li>
                        <li class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">PHP Version</span>
                            <span class="font-bold text-gray-800">{{ PHP_VERSION }}</span>
                        </li>
                        <li class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Environment</span>
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold uppercase">{{ app()->environment() }}</span>
                        </li>
                        <li class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Anti-Cheat Mode</span>
                            <span class="px-2 py-1 {{ ($configs['anti_cheat_active'] ?? 1) ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }} rounded text-xs font-bold uppercase">
                                {{ ($configs['anti_cheat_active'] ?? 1) ? 'Enabled' : 'Disabled' }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
