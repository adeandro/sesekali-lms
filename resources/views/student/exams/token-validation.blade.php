@extends('layouts.app')

@section('title', 'Validasi Token Ujian - SesekaliCBT')

@section('page-title', 'Validasi Token Ujian')

@section('content')
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-8">
                <div class="text-center">
                    <i class="fas fa-lock text-4xl mb-4"></i>
                    <h1 class="text-2xl font-bold mb-2">{{ $exam->title }}</h1>
                    <p class="text-blue-100 text-sm">🔐 Masukkan Token Ujian untuk Mulai</p>
                    @if($exam->token)
                        <p class="text-blue-200 text-xs mt-2">Sistem Token Global</p>
                    @endif
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <!-- Token Status Info -->
                @if(!$exam->token || $exam->status !== 'published')
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                        <p class="text-red-800 text-sm font-semibold">
                            ⚠️ Token ujian belum disiapkan oleh pengawas
                        </p>
                        <p class="text-red-600 text-xs mt-1">Silakan hubungi pengawas untuk mendapatkan token ujian</p>
                    </div>
                @endif

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">🔑 Masukkan Token Ujian</label>
                    <p class="text-xs text-gray-500 mb-4">Token Global yang diberikan oleh pengawas. Format: 6 karakter (contoh: A1B2C3)</p>
                    <input type="text" id="tokenInput" 
                        placeholder="Contoh: A1B2C3" 
                        maxlength="6" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none font-mono tracking-widest text-center text-2xl uppercase font-bold"
                        autocomplete="off"
                        required>
                    <small class="text-xs text-gray-500 mt-2 block">6 karakter (huruf dan angka, TANPA spasi atau dash)</small>
                </div>

                <!-- Exam Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-sm">
                    <h3 class="font-semibold text-gray-900 mb-3">📋 Informasi Ujian</h3>
                    <div class="space-y-2 text-gray-700 text-xs">
                        <div class="flex justify-between">
                            <span>Mata Pelajaran:</span>
                            <span class="font-semibold">{{ $exam->subject->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Durasi:</span>
                            <span class="font-semibold">{{ $exam->duration_minutes }} menit</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Jumlah Soal:</span>
                            <span class="font-semibold">{{ $exam->total_questions }} soal</span>
                        </div>
                    </div>
                </div>

                <!-- Token Info (for published exams) -->
                @if($exam->token && $exam->status === 'published')
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-xs">
                        <h3 class="font-semibold text-gray-900 mb-2">🔐 Status Token Global</h3>
                        <div class="space-y-1 text-gray-700">
                            <div class="flex justify-between">
                                <span>Token Aktif:</span>
                                <span class="font-mono font-bold text-green-700">{{ $exam->token }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Dibuat:</span>
                                <span>{{ $exam->token_last_updated?->format('d M H:i') ?? 'Tadi' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Auto-Refresh:</span>
                                <span class="text-blue-600 font-semibold">{{ $exam->minutesUntilTokenRefresh() }} menit lagi</span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Buttons -->
                <div class="flex gap-3">
                    <a href="{{ route('student.exams.index') }}" 
                        class="flex-1 px-4 py-3 bg-gray-300 text-gray-900 rounded-lg font-semibold hover:bg-gray-400 transition text-center">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                    <button type="button" id="validateBtn" 
                        class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-check mr-2"></i>Validasi
                    </button>
                </div>

                <!-- Loading State -->
                <div id="loadingState" class="hidden mt-4 text-center">
                    <div class="inline-block">
                        <i class="fas fa-spinner fa-spin text-blue-600 text-2xl"></i>
                        <p class="text-gray-600 mt-2">Memvalidasi token...</p>
                    </div>
                </div>

                <!-- Error Message -->
                <div id="errorMessage" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-800 text-sm flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span id="errorText"></span>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 border-t border-gray-200 p-4 text-center text-xs text-gray-600">
                <div class="space-y-1">
                    <div>
                        <i class="fas fa-shield-alt text-green-600 mr-1"></i>Koneksi aman terenkripsi
                    </div>
                    <div class="text-gray-500 text-xs">
                        Token Global • Auto-Refresh 20 menit • Session Persisten
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tokenInput = document.getElementById('tokenInput');
            const validateBtn = document.getElementById('validateBtn');
            const errorMessage = document.getElementById('errorMessage');
            const loadingState = document.getElementById('loadingState');
            const errorText = document.getElementById('errorText');
            const examId = {{ $exam->id }};

            // Format input: remove special characters, uppercase
            tokenInput.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                e.target.value = value;
                
                // Enable button when token has correct format (6 chars)
                validateBtn.disabled = value.length !== 6;
            });

            // Allow Enter key to validate
            tokenInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !validateBtn.disabled) {
                    validateToken();
                }
            });

            validateBtn.addEventListener('click', validateToken);

            async function validateToken() {
                const token = tokenInput.value.trim();

                if (!token || token.length !== 6) {
                    showError('Silakan masukkan token yang valid (6 karakter, format: A1B2C3)');
                    return;
                }

                try {
                    // Show loading state
                    loadingState.classList.remove('hidden');
                    errorMessage.classList.add('hidden');
                    validateBtn.disabled = true;
                    tokenInput.disabled = true;

                    const response = await fetch(
                        `{{ route('student.exams.validate-and-start', ['exam' => ':exam']) }}`.replace(':exam', examId),
                        {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            },
                            body: JSON.stringify({ token: token })
                        }
                    );

                    const data = await response.json();

                    if (data.success) {
                        // Show success alert then redirect
                        await Swal.fire({
                            title: '✅ Token Valid!',
                            text: 'Ujian dimulai sekarang...',
                            icon: 'success',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            timer: 2000,
                            timerProgressBar: true,
                        });

                        // Redirect to exam
                        window.location.href = data.redirect_url;
                    } else {
                        showError(data.message || 'Token tidak valid');
                    }
                } catch (error) {
                    showError(error.message || 'Terjadi kesalahan saat memvalidasi token');
                } finally {
                    loadingState.classList.add('hidden');
                    validateBtn.disabled = false;
                    tokenInput.disabled = false;
                    tokenInput.focus();
                }
            }

            function showError(message) {
                errorText.textContent = message;
                errorMessage.classList.remove('hidden');
            }

            // Focus on input on load
            tokenInput.focus();
        });
    </script>

    <style>
        input[type="text"]::placeholder {
            letter-spacing: 0.1em;
        }
    </style>
@endsection
