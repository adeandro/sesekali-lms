@extends('layouts.app')

@section('title', 'Daftar Ujian - SesekaliCBT')

@section('page-title', 'Ujian Tersedia')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($exams as $exam)
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden">
                <!-- Exam Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6 text-white">
                    <h3 class="text-xl font-bold mb-2">{{ $exam->title }}</h3>
                    <p class="text-blue-100 text-sm">{{ $exam->subject->name }}</p>
                </div>

                <!-- Exam Details -->
                <div class="p-6 space-y-3">
                    <!-- Duration -->
                    <div class="flex items-center gap-3">
                        <i class="fas fa-clock w-5 text-gray-500"></i>
                        <div>
                            <p class="text-sm text-gray-600">Durasi</p>
                            <p class="font-semibold text-gray-900">{{ $exam->duration_minutes }} menit</p>
                        </div>
                    </div>

                    <!-- Total Questions -->
                    <div class="flex items-center gap-3">
                        <i class="fas fa-question-circle w-5 text-gray-500"></i>
                        <div>
                            <p class="text-sm text-gray-600">Jumlah Soal</p>
                            <p class="font-semibold text-gray-900">{{ $exam->total_questions }} soal</p>
                        </div>
                    </div>

                    <!-- Start & End Time -->
                    <div class="flex items-center gap-3">
                        <i class="fas fa-calendar-alt w-5 text-gray-500"></i>
                        <div>
                            <p class="text-sm text-gray-600">Periode Ujian</p>
                            <p class="text-xs text-gray-700">
                                {{ $exam->start_time->format('d M Y H:i') }} - {{ $exam->end_time->format('d M Y H:i') }}
                            </p>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="pt-2">
                        @php
                            $now = now();
                            if(in_array($exam->id, $submittedExams)) {
                                $status = 'submitted';
                            } elseif($exam->start_time > $now) {
                                $status = 'upcoming';
                            } elseif($exam->end_time < $now) {
                                $status = 'ended';
                            } else {
                                $status = 'active';
                            }
                        @endphp
                        
                        @if($status === 'submitted')
                            <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                <i class="fas fa-check-circle mr-1"></i>Sudah Kirim
                            </span>
                        @elseif($status === 'active')
                            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                                <i class="fas fa-hourglass-start mr-1"></i>Tersedia
                            </span>
                        @elseif($status === 'upcoming')
                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                <i class="fas fa-calendar-check mr-1"></i>Belum Dimulai
                            </span>
                        @else
                            <span class="inline-block px-3 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded-full">
                                <i class="fas fa-times-circle mr-1"></i>Sudah Berakhir
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Action Button -->
                <div class="p-6 border-t border-gray-200">
                    @if($status === 'submitted')
                        <button disabled class="w-full px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold cursor-not-allowed">
                            <i class="fas fa-ban mr-2"></i>Sudah Dikerjakan
                        </button>
                    @elseif($status === 'upcoming')
                        <button disabled class="w-full px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold cursor-not-allowed">
                            <i class="fas fa-clock mr-2"></i>Dimulai {{ $exam->start_time->format('d M H:i') }}
                        </button>
                    @elseif($status === 'ended')
                        <button disabled class="w-full px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold cursor-not-allowed">
                            <i class="fas fa-times-circle mr-2"></i>Ujian Berakhir
                        </button>
                    @else
                        <form action="{{ route('student.exams.start', $exam->id) }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                                <i class="fas fa-play-circle mr-2"></i>Mulai Ujian
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="col-span-1 md:col-span-2 lg:col-span-3">
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Tidak Ada Ujian</h3>
                    <p class="text-gray-600">
                        Saat ini tidak ada ujian yang tersedia untuk Anda. Silakan cek kembali nanti.
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Recently Submitted Exams (if any) -->
    @if($submittedExams)
        <div class="mt-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Ujian Anda</h2>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <p class="text-gray-700">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    Anda telah mengerjakan {{ count($submittedExams) }} ujian. Anda tidak dapat mengerjakan ulang ujian yang sudah dikerjakan.
                </p>
            </div>
        </div>
    @endif

    <script>
        // Simple form submission without fullscreen
        // Fullscreen will be triggered on the exam taking page instead
        document.addEventListener('DOMContentLoaded', function() {
            const startButtons = document.querySelectorAll('button[type="submit"]');
            startButtons.forEach(button => {
                if (button.textContent.includes('Mulai Ujian')) {
                    button.addEventListener('click', function(e) {
                        // Normal form submission - no fullscreen here
                        // Fullscreen will be triggered on exam taking page
                    });
                }
            });
        });
    </script>
@endsection
