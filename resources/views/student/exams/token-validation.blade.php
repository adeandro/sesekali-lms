@extends('layouts.app')

@section('title', 'Validasi Token - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full space-y-8">
        <!-- Main Card -->
        <div class="bg-white rounded-[3rem] shadow-2xl shadow-indigo-100 overflow-hidden border border-gray-100 transition-all duration-500 hover:shadow-indigo-200">
            <!-- Header with Gradient Area -->
            <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900 px-10 py-12 text-center relative overflow-hidden">
                <!-- Decoration -->
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-32 h-32 bg-indigo-400/20 rounded-full blur-2xl"></div>

                <div class="relative space-y-4">
                    <div class="w-20 h-20 bg-white/15 backdrop-blur-xl border border-white/20 rounded-[2rem] flex items-center justify-center mx-auto shadow-2xl">
                        <i class="fas fa-shield-alt text-3xl text-white"></i>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[10px] font-black text-indigo-200 uppercase tracking-[0.4em] mb-1">Gerbang Keamanan</p>
                        <h1 class="text-3xl font-black text-white leading-tight uppercase tracking-wider">{{ $exam->title }}</h1>
                    </div>
                </div>
            </div>

            <!-- Validation Content -->
            <div class="px-10 py-12 space-y-10">
                <!-- Exam Summary Info (Embedded Card Style) -->
                <div class="bg-gray-50 rounded-[2rem] p-6 border border-gray-100 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ $exam->subject->name }}</p>
                            <p class="text-xs font-black text-gray-800 uppercase tracking-wider">{{ $exam->total_questions }} Soal <span class="text-gray-200 mx-2">|</span> {{ $exam->duration_minutes }} Menit</p>
                        </div>
                    </div>
                </div>

                <!-- Input Section -->
                <div class="space-y-6">
                    <div class="text-center space-y-2">
                        <h2 class="text-sm font-black text-gray-900 uppercase tracking-[0.2em]">Masukkan Token Ujian</h2>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Gunakan 6 karakter kode yang diberikan oleh pengawas Anda.</p>
                    </div>

                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-3xl blur opacity-20 group-hover:opacity-40 transition duration-1000 group-hover:duration-200"></div>
                        <input type="text" id="tokenInput" 
                            placeholder="••••••" 
                            maxlength="6" 
                            class="relative w-full bg-white px-8 py-6 border-2 border-gray-100 rounded-3xl focus:border-indigo-500 focus:outline-none font-mono tracking-[0.6em] text-center text-4xl uppercase font-black text-indigo-600 transition-all placeholder:text-gray-100"
                            autocomplete="off"
                            required>
                    </div>

                    @if($exam->token && $exam->status === 'published')
                         <div class="flex items-center justify-center gap-2 py-2">
                             <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                             <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">Sistem Token Aktif & Tervalidasi</span>
                         </div>
                    @endif
                </div>

                <!-- Action Area -->
                <div class="space-y-4">
                    <button type="button" id="validateBtn" 
                        class="w-full py-5 bg-indigo-600 text-white rounded-3xl text-[11px] font-black uppercase tracking-[0.4em] shadow-2xl shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center justify-center gap-4 group/btn disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-check-circle group-hover/btn:scale-110 transition-transform"></i> Validasi Sekarang
                    </button>
                    
                    <a href="{{ route('student.exams.index') }}" 
                        class="w-full py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-600 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Ujian
                    </a>
                </div>
            </div>

            <!-- Sticky Bottom Info -->
            <div class="bg-indigo-50/50 px-10 py-6 border-t border-indigo-50 flex items-center justify-center gap-4">
                 <div class="flex items-center gap-2">
                     <i class="fas fa-shield-virus text-indigo-400 text-xs"></i>
                     <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">Anti-Tab Switching Active</span>
                 </div>
                 <span class="w-1 h-1 bg-indigo-200 rounded-full"></span>
                 <div class="flex items-center gap-2">
                     <i class="fas fa-wifi text-indigo-400 text-xs"></i>
                     <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">Auto-Save Enabled</span>
                 </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Loading Backdrop -->
<div id="loadingOverlay" class="fixed inset-0 bg-indigo-900/40 backdrop-blur-md z-[9999] hidden items-center justify-center">
    <div class="bg-white p-10 rounded-[3rem] shadow-2xl text-center space-y-6 max-w-xs w-full mx-4">
        <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto">
            <i class="fas fa-spinner fa-spin text-3xl text-indigo-600"></i>
        </div>
        <div class="space-y-1">
            <h3 class="text-sm font-black text-gray-900 uppercase tracking-[0.2em]">Memverifikasi...</h3>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Mohon tunggu sebentar</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tokenInput = document.getElementById('tokenInput');
        const validateBtn = document.getElementById('validateBtn');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const examId = {{ $exam->id }};

        tokenInput.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            e.target.value = value;
            validateBtn.disabled = value.length !== 6;
        });

        tokenInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !validateBtn.disabled) {
                validateToken();
            }
        });

        validateBtn.addEventListener('click', validateToken);

        async function validateToken() {
            const token = tokenInput.value.trim();

            if (!token || token.length !== 6) return;

            try {
                loadingOverlay.classList.replace('hidden', 'flex');
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
                    await Swal.fire({
                        title: '✅ TOKEN VALID!',
                        html: '<p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Sesi Ujian Sedang Disiapkan...</p>',
                        icon: 'success',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                        background: '#ffffff',
                        customClass: {
                            popup: 'rounded-[2rem]',
                            title: 'text-xl font-black text-gray-800 tracking-wider',
                        }
                    });
                    window.location.href = data.redirect_url;
                } else {
                    loadingOverlay.classList.replace('flex', 'hidden');
                    Swal.fire({
                        title: '❌ TOKEN ERROR',
                        text: data.message || 'Token yang dimasukkan salah atau sudah kadaluwarsa.',
                        icon: 'error',
                        confirmButtonText: 'Coba Lagi',
                        confirmButtonColor: '#4f46e5',
                        background: '#ffffff',
                        customClass: {
                            popup: 'rounded-[2rem]',
                            title: 'text-xl font-black text-rose-600 tracking-wider',
                            confirmButton: 'rounded-2xl px-8 py-3 text-[10px] font-black uppercase tracking-widest'
                        }
                    });
                }
            } catch (error) {
                loadingOverlay.classList.replace('flex', 'hidden');
                Swal.fire({
                    title: '⚠️ GANGGUAN SISTEM',
                    text: 'Terjadi kesalahan teknis. Silakan segarkan halaman.',
                    icon: 'warning',
                    confirmButtonText: 'Refresh',
                    confirmButtonColor: '#4f46e5',
                }).then(() => location.reload());
            } finally {
                validateBtn.disabled = false;
                tokenInput.disabled = false;
                tokenInput.focus();
            }
        }

        tokenInput.focus();
    });
</script>

<style>
    @keyframes pulse-custom {
        0%, 100% { transform: scale(1); box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.1), 0 10px 10px -5px rgba(79, 70, 229, 0.04); }
        50% { transform: scale(1.01); box-shadow: 0 25px 30px -5px rgba(79, 70, 229, 0.2), 0 15px 15px -5px rgba(79, 70, 229, 0.1); }
    }
    .animate-pulse-custom {
        animation: pulse-custom 3s infinite ease-in-out;
    }
    input::placeholder {
        letter-spacing: 0.8rem;
        opacity: 0.1;
    }
</style>
@endsection
