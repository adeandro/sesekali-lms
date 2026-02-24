@extends('layouts.app')

@section('title', 'Superadmin Dashboard - SesekaliCBT')

@section('page-title', 'Superadmin Dashboard')

@section('content')
    <div>
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Superadmin Dashboard</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalUsers }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Superadmins -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Superadmins</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">{{ $superadminCount }}</p>
                    </div>
                    <div class="bg-red-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Admins -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Admins</p>
                        <p class="text-3xl font-bold text-blue-600 mt-2">{{ $adminCount }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Students -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Students</p>
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
                        <p class="text-gray-600 text-sm font-medium">Active Users</p>
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

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Content Management -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Content Management</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.subjects.index') }}" class="block px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-center">
                        📚 Manage Subjects
                    </a>
                    <a href="{{ route('admin.questions.index') }}" class="block px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-center">
                        ❓ Manage Questions
                    </a>
                </div>
            </div>

            <!-- User Management -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">User Management</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.students.index') }}" class="block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center">
                        👥 Manage Students
                    </a>
                </div>
            </div>

            <!-- System Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">System Information</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li><strong>Laravel Version:</strong> {{ app()::VERSION }}</li>
                    <li><strong>PHP Version:</strong> {{ PHP_VERSION }}</li>
                    <li><strong>Environment:</strong> {{ app()->environment() }}</li>
                    <li><strong>Debug Mode:</strong> {{ config('app.debug') ? 'Enabled' : 'Disabled' }}</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
