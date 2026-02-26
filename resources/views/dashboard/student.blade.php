@extends('layouts.app')

@section('title', 'Dasbor Siswa - SesekaliCBT')

@section('page-title', 'Dasbor Siswa')

@section('content')
    <div>
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Selamat datang, {{ Auth::user()->name }}!</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Welcome Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Akun</h3>
                <p class="text-gray-600 mb-4">
                    Selamat datang di SesekaliCBT! Ini adalah dasbor siswa Anda untuk mengakses ujian.
                </p>
                <div class="space-y-2">
                    <p class="text-sm text-gray-600"><strong>NIS:</strong> {{ Auth::user()->nis }}</p>
                    <p class="text-sm text-gray-600"><strong>Peran:</strong> {{ Auth::user()->role === 'student' ? 'Siswa' : ucfirst(Auth::user()->role) }}</p>
                    <p class="text-sm text-gray-600"><strong>Kelas:</strong> Grade {{ Auth::user()->grade ?? '-' }} - {{ Auth::user()->class_group ?? '-' }}</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
                <div class="space-y-2">
                    <a href="{{ route('student.exams.index') }}" class="block p-4 bg-green-50 border-2 border-green-300 rounded-lg hover:bg-green-100 transition">
                        <i class="fas fa-pencil-alt text-green-600 mr-2 text-lg"></i>
                        <p class="font-semibold text-green-900">Mengerjakan Ujian</p>
                        <p class="text-sm text-green-700">Lihat dan kerjakan ujian yang tersedia</p>
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Ujian Dikerjakan</p>
                        <p class="text-3xl font-bold text-blue-600 mt-2">
                            @php
                                $completedExams = Auth::user()->examAttempts()->whereNotNull('submitted_at')->count();
                            @endphp
                            {{ $completedExams }}
                        </p>
                    </div>
                    <i class="fas fa-check-circle text-4xl text-blue-200"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Nilai Rata-rata</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2">
                            @php
                                $avgScore = Auth::user()->examAttempts()
                                    ->whereNotNull('final_score')
                                    ->avg('final_score');
                            @endphp
                            {{ $avgScore ? intval($avgScore) : '-' }}
                        </p>
                    </div>
                    <i class="fas fa-chart-bar text-4xl text-purple-200"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Ujian Berlalu</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">
                            @php
                                $passedExams = Auth::user()->examAttempts()
                                    ->where('final_score', '>=', 70)
                                    ->count();
                            @endphp
                            {{ $passedExams }}
                        </p>
                    </div>
                    <i class="fas fa-trophy text-4xl text-green-200"></i>
                </div>
            </div>
        </div>

        <!-- Recent Results -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Hasil Ujian Terbaru</h3>
            @php
                $recentResults = Auth::user()->examAttempts()
                    ->with('exam')
                    ->whereNotNull('submitted_at')
                    ->orderBy('submitted_at', 'DESC')
                    ->limit(5)
                    ->get();
            @endphp
            
            @if($recentResults->isEmpty())
                <p class="text-gray-600 text-center py-8">Belum ada hasil ujian belum.</p>
            @else
                <div class="divide-y divide-gray-200">
                    @foreach($recentResults as $result)
                        @if($result->exam)
                        <div class="py-3 px-0 flex items-center justify-between hover:bg-gray-50 transition">
                            <div>
                                <p class="font-medium text-gray-900">{{ $result->exam->title }}</p>
                                <p class="text-sm text-gray-500">{{ $result->submitted_at->format('d M Y H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold {{ $result->final_score >= 70 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ intval($result->final_score) }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $result->final_score >= 70 ? 'Lulus' : 'Tidak Lulus' }}</p>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                <div class="mt-4">
                    <a href="{{ route('student.results') }}" class="text-blue-600 hover:text-blue-700 font-medium">
                        Lihat semua hasil →
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
