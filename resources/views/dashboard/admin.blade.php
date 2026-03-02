@extends('layouts.app')

@section('title', 'Dasbor Admin - ' . ($configs['school_name'] ?? 'ExamFlow'))

@section('page-title', 'Dasbor Admin')

@section('content')
    <div>
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Dasbor Admin</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Pengguna</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalUsers }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Students -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Siswa</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ $studentCount }}</p>
                    </div>
                    <div class="bg-green-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Pengguna Aktif</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2">{{ $activeUsersCount }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Panel -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📚 Kelola Ujian</h3>
            <p class="text-gray-600 mb-4">Buat, edit, kelola soal, dan atur ujian.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.students.index') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center font-semibold">
                    👥 Kelola Siswa
                </a>
                <a href="{{ route('admin.subjects.index') }}" class="inline-block px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-center font-semibold">
                    📚 Kelola Mata Pelajaran
                </a>
                <a href="{{ route('admin.questions.index') }}" class="inline-block px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-center font-semibold">
                    ❓ Kelola Soal
                </a>
            </div>
        </div>

        <!-- Exam Management -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📝 Manajemen Ujian</h3>
            <p class="text-gray-600 mb-4">Buat ujian, kelola soal, dan tentukan jadwal ujian.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('admin.exams.index') }}" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-center font-semibold">
                    <i class="fas fa-list mr-2"></i>Daftar Ujian
                </a>
                <a href="{{ route('admin.exams.create') }}" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-center font-semibold">
                    <i class="fas fa-plus mr-2"></i>Buat Ujian Baru
                </a>
            </div>
        </div>

        <!-- Token & Monitoring Section -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🔒 Pengawasan & Keamanan</h3>
            <p class="text-gray-600 mb-4">Kelola token akses dan pantau ujian yang sedang berlangsung.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.tokens.index') }}" class="block px-4 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition text-center font-semibold">
                    <i class="fas fa-key mr-2"></i>Kelola Token
                </a>
                <a href="{{ route('admin.exams.index') }}?tab=monitoring" class="block px-4 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition text-center font-semibold">
                    <i class="fas fa-video mr-2"></i>Pantau Ujian
                </a>
                <a href="{{ route('admin.results.index') }}" class="block px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-center font-semibold">
                    <i class="fas fa-chart-line mr-2"></i>Lihat Hasil
                </a>
            </div>
        </div>

        <!-- Quick Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h4 class="font-semibold text-blue-900 mb-3">💡 Cara Memulai Ujian</h4>
                <ol class="text-sm text-blue-800 space-y-2 list-decimal list-inside">
                    <li><strong>Buat Ujian</strong> di "Daftar Ujian"</li>
                    <li><strong>Generate Token</strong> di "Kelola Token"</li>
                    <li><strong>Bagikan Token</strong> kepada siswa sebelum ujian</li>
                    <li><strong>Pantau Ujian</strong> real-time di "Pantau Ujian"</li>
                    <li><strong>Lihat Hasil</strong> setelah ujian selesai</li>
                </ol>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h4 class="font-semibold text-green-900 mb-3">🎯 Fitur Pengawasan</h4>
                <ul class="text-sm text-green-800 space-y-2">
                    <li>✅ Monitoring real-time siswa yang ujian</li>
                    <li>✅ Deteksi koneksi offline</li>
                    <li>✅ Hentikan ujian siswa kapan saja</li>
                    <li>✅ Logout paksa siswa jika curang</li>
                    <li>✅ Audit log semua aksi admin</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
