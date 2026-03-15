@extends('layouts.exam')

@section('title', 'Mengerjakan Ujian - SesekaliCBT')

@section('page-title', $attempt->exam->title)

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Hide sidebar on exam taking page only */
        #sidebar {
            display: none !important;
        }

        /* Hide sidebar overlay on exam page */
        #sidebarOverlay {
            display: none !important;
        }

        /* Adjust page wrapper for hidden sidebar */
        .flex.h-screen > div:last-child {
            width: 100% !important;
        }

        /* Anti-Cheating: Disable text selection */
        .exam-container {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        /* Sticky Header for Exam */
        .exam-sticky-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-dark) 100%);
            box-shadow: 0 4px 15px var(--brand-glow);
        }

        /* Enhanced Progress Bar */
        #progressBar {
            background: var(--brand-primary);
            box-shadow: 0 0 10px var(--brand-glow);
            transition: width 0.3s ease;
        }

        /* Progress Container */
        .progress-container {
            background-color: #f3f4f6;
            border-radius: 12px;
            overflow: hidden;
            height: 6px;
        }

        /* Timer Warning Colors */
        .timer-normal {
            color: #ffffff;
        }

        .timer-warning {
            color: #fbbf24;
            animation: pulse-warning 1s infinite;
        }

        .timer-critical {
            color: #ef4444;
            animation: pulse-critical 0.7s infinite;
        }

        @keyframes pulse-warning {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        @keyframes pulse-critical {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Enhanced Typography */
        .question-text-enhanced {
            font-size: 1.125rem;
            line-height: 1.75;
            color: #111827;
            font-weight: 500;
        }

        .exam-info-stat {
            display: flex;
            flex-direction: column;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .exam-info-stat:last-child {
            border-bottom: none;
        }

        .exam-info-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .exam-info-value {
            font-size: 1rem;
            color: #1f2937;
            font-weight: 700;
            margin-top: 0.25rem;
        }

        /* Improved Button Styles for Accessibility */
        button, input[type="button"], input[type="submit"] {
            font-size: 1rem;
            min-height: 44px;
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            font-weight: 600;
            cursor: pointer;
        }

        button:focus, input[type="button"]:focus, input[type="submit"]:focus {
            outline: 2px solid var(--brand-primary);
            outline-offset: 2px;
        }

        button:active {
            transform: scale(0.98);
        }

        /* Navigation Buttons */
        #prevBtn, #nextBtn, #submitBtn, #raguBtn {
            border: none;
            align-items: center;
            justify-content: center;
        }

        #prevBtn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Better label styling for form inputs */
        label {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        label:hover {
            transform: translateY(-2px);
        }

        label:focus-within {
            outline: 2px solid var(--brand-primary);
            outline-offset: 2px;
        }

        /* Enhanced spacing */
        .exam-container {
            padding: 1rem;
        }

        @media (min-width: 768px) {
            .exam-container {
                padding: 1.5rem;
            }
        }

        /* Improved readability */
        .question-slide label {
            padding: 1rem;
            margin-bottom: 0.75rem;
        }

        .question-slide label:last-child {
            margin-bottom: 0;
        }

        /* Focus visible for keyboard navigation */
        :focus-visible {
            outline: 2px solid var(--brand-primary);
            outline-offset: 2px;
        }

        .timer-expired {
            color: #dc2626;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .question-nav-active {
            background-color: var(--brand-primary) !important;
            box-shadow: 0 0 10px var(--brand-glow) !important;
            color: white !important;
            border-color: var(--brand-primary) !important;
        }

        .question-nav-answered {
            background-color: #dcfce7 !important;
            color: #166534 !important;
            border-color: #166534 !important;
        }

        .question-nav-review {
            background-color: #fef3c7 !important;
            color: #92400e !important;
            border-color: #92400e !important;
        }

        /* Confetti Canvas */
        #confettiCanvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
        }

        /* Anti-Cheating: Fullscreen Exit Overlay */
        #fullscreenOverlay {
            display: none;                    /* sembunyi by default */
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.97);
            z-index: 99998;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 20px;
        }

        #fullscreenOverlay.active {
            display: flex;
        }

        #fullscreenOverlay h2 {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #ff6b6b;
        }

        #fullscreenOverlay p {
            font-size: 16px;
            line-height: 1.6;
            color: #e8e8e8;
            margin-bottom: 10px;
        }

        #fullscreenOverlay button {
            margin-top: 30px;
            padding: 16px 40px;
            background-color: var(--brand-primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px var(--brand-glow);
        }

        #fullscreenOverlay button:hover {
            opacity: 0.9;
            transform: scale(1.05);
            box-shadow: 0 6px 20px var(--brand-glow);
        }

        #fullscreenOverlay button:active {
            transform: scale(0.98);
        }

        /* Exam Readiness Modal - Gating System */
        #readinessOverlay {
            display: flex;
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 99999;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }

        #readinessOverlay.hidden {
            display: none !important;
        }

        #readinessOverlay > div {
            max-width: 700px;
            background-color: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        #readinessOverlay h2 {
            font-size: 28px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #readinessOverlay .rules {
            background-color: #f3f4f6;
            border-left: 4px solid var(--brand-primary);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            max-height: 300px;
            overflow-y: auto;
        }

        #readinessOverlay .rules ol {
            margin: 0;
            padding-left: 20px;
            color: #374151;
            line-height: 1.8;
        }

        #readinessOverlay .rules li {
            margin-bottom: 10px;
            font-size: 14px;
        }

        #readinessOverlay .warning {
            background-color: #fef3c7;
            border: 2px solid #fbbf24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            color: #92400e;
            font-weight: 500;
        }

        #readinessOverlay .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }

        #readinessOverlay button {
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #startExamBtn {
            background-color: #10b981;
            color: white;
            min-width: 200px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }

        #startExamBtn:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
        }

        #startExamBtn:active {
            transform: translateY(0);
        }

        #cancelExamBtn {
            background-color: #e5e7eb;
            color: #374151;
            min-width: 150px;
        }

        #cancelExamBtn:hover {
            background-color: #d1d5db;
        }

        /* Exam Container Gating */
        .exam-container {
            opacity: 1;
            transition: opacity 0.3s ease;
        }

        .exam-container.blurred {
            opacity: 0.3;
            pointer-events: none;
            filter: blur(5px);
        }

        /* Anti-Cheating: Print Screen Overlay */
        #printscreenOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.95);
            z-index: 9998;
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Responsive Exam Images */
        .exam-content-img {
            max-width: 100%;
            height: auto !important;
            display: block;
            margin: 1rem 0;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 640px) {
            .exam-content-img {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')


    <!-- Anti-Cheating: Print Screen Overlay -->
    <div id="printscreenOverlay"></div>

    <div class="exam-container grid grid-cols-1 lg:grid-cols-4 gap-4 md:gap-6">
        <!-- Main Exam Area -->
        <div class="lg:col-span-3">
            <div class="bg-white border-l-4 border-[var(--brand-primary)] rounded-lg shadow-lg overflow-hidden">
                <!-- STICKY Exam Header with Timer -->
                <div class="exam-sticky-header text-white p-4 md:p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <div class="w-full sm:flex-1">
                            <h1 class="text-xl md:text-2xl font-bold break-words">{{ $attempt->exam->title }}</h1>
                            <p class="text-[var(--brand-glow)] brightness-200 text-sm mt-1">{{ $attempt->exam->subject->name }}</p>
                        </div>
                        <div class="text-center sm:text-right w-full sm:w-auto">
                            <p class="text-xs sm:text-sm text-[var(--brand-glow)] brightness-200 mb-2">⏱️ Waktu Tersisa</p>
                            <div id="timer" class="text-3xl sm:text-4xl font-bold font-mono timer-normal timer-display">
                                {{ sprintf('%02d:%02d', intval($remainingMinutes), 0) }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress Section -->
                    <div class="border-t border-white/20 pt-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs sm:text-sm font-semibold text-white/90">
                                📋 Soal <span id="currentQNum">1</span> dari {{ $questions->count() }} (<span id="completionPercent">0</span>%)
                            </span>
                            <span class="text-xs font-semibold text-white/90" id="answeredCount">0 terjawab</span>
                        </div>
                        <div class="progress-container">
                            <div id="progressBar" class="h-full rounded-full transition-all" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <!-- Question Display -->
                <div class="p-4 md:p-8 min-h-96">
                    <form id="examForm">
                        @csrf
                        
                        <!-- Question Container (no duplicate progress bar) -->
                        <div id="questionContainer">
                            @foreach($questions as $index => $question)
                                <div class="question-slide" 
                                    data-question-id="{{ $question->id }}" 
                                    data-question-index="{{ $question->display_index }}" 
                                    data-question-type="{{ $question->nav_type }}"
                                    data-nav-position="{{ $question->nav_position }}"
                                    style="{{ $index === 0 ? '' : 'display: none;' }}">
                                    
                                    <!-- Question Text -->
                                    <div class="mb-8">
                                        <h2 class="question-text-enhanced">{{ $question->question_text }}</h2>
                                        
                                        @if($question->question_image)
                                            <div class="my-4">
                                                <img src="{{ asset($question->question_image) }}" 
                                                     alt="Question Image" 
                                                     class="exam-content-img">
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Answer Options -->
                                    <div class="space-y-3">
                                        @if($question->question_type === 'multiple_choice')
                                            <!-- Multiple Choice Options -->
                                            <input type="hidden" class="question-type" value="mc">
                                            @php
                                                $current_answer = $attempt->answers()->where('question_id', $question->id)->first()?->selected_answer;
                                                $options = [
                                                    'a' => $question->option_a,
                                                    'b' => $question->option_b,
                                                    'c' => $question->option_c,
                                                    'd' => $question->option_d,
                                                    'e' => $question->option_e,
                                                ];
                                                $options = array_filter($options);
                                            @endphp
                                            
                                            @foreach($options as $key => $option)
                                                @php
                                                    $imageFieldName = 'option_' . $key . '_image';
                                                    $optionImage = $question->$imageFieldName ?? null;
                                                @endphp
                                <label class="flex items-start p-3 md:p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-[var(--brand-primary)]/50 transition text-sm md:text-base">
                                    <input type="radio" 
                                        name="answer_{{ $question->id }}" 
                                        value="{{ $key }}" 
                                        class="mt-1 mr-3 question-answer text-[var(--brand-primary)] focus:ring-[var(--brand-primary)]"
                                        data-question-id="{{ $question->id }}"
                                        {{ $current_answer === $key ? 'checked' : '' }}>
                                                    <div class="flex-1">
                                                        <span class="text-gray-800 block mb-2">{{ $option }}</span>
                                                        @if($optionImage)
                                                            <img src="{{ asset($optionImage) }}" 
                                                                 alt="Option {{ strtoupper($key) }}" 
                                                                 class="exam-content-img mt-2">
                                                        @endif
                                                    </div>
                                                </label>
                                            @endforeach
                                        @else
                                            <!-- Essay Answer -->
                                            <input type="hidden" class="question-type" value="essay">
                                            @php
                                                $current_essay = $attempt->answers()->where('question_id', $question->id)->first()?->essay_answer;
                                            @endphp
                                            
                                            <textarea 
                                                name="answer_{{ $question->id }}" 
                                                class="w-full p-3 md:p-4 border-2 border-gray-200 rounded-lg focus:border-[var(--brand-primary)] focus:ring-4 focus:ring-[var(--brand-glow)] focus:outline-none question-answer text-sm md:text-base transition-all" 
                                                rows="6" 
                                                placeholder="Ketik jawaban anda di sini..."
                                                data-question-id="{{ $question->id }}">{{ $current_essay }}</textarea>
                                        @endif
                                    </div>

                                    <!-- Hidden field to store review status -->
                                    <input type="hidden" class="mark-review" data-question-id="{{ $question->id }}" value="0">
                                </div>
                            @endforeach
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="mt-8 pt-8 border-t border-gray-200 flex flex-col sm:flex-row justify-between gap-2 sm:gap-4">
                            <button type="button" id="prevBtn" class="px-4 sm:px-6 py-2 bg-gray-100 text-gray-700 border border-gray-200 rounded-lg font-semibold hover:bg-gray-200 transition disabled:opacity-50 text-sm sm:text-base order-2 sm:order-1">
                                <i class="fas fa-chevron-left mr-2"></i>Sebelumnya
                            </button>
                            
                            <div class="flex gap-2 flex-wrap justify-center order-3 sm:order-2">
                                <button type="button" id="raguBtn" class="px-4 sm:px-6 py-2 bg-amber-500 text-white rounded-lg font-semibold hover:bg-amber-600 transition text-sm sm:text-base shadow-lg shadow-amber-500/20">
                                    <i class="fas fa-question-circle mr-2"></i>Ragu-ragu
                                </button>
                            </div>
                            
                            <div class="flex gap-2 flex-wrap justify-end sm:justify-center order-1 sm:order-3">
                                <button type="button" id="nextBtn" class="flex-1 sm:flex-none px-4 sm:px-6 py-2 bg-[var(--brand-primary)] text-white rounded-lg font-semibold hover:opacity-90 transition text-sm sm:text-base shadow-lg shadow-[var(--brand-glow)]">
                                    Lanjut<i class="fas fa-chevron-right ml-2"></i>
                                </button>
                                <button type="button" id="submitBtn" class="flex-1 sm:flex-none px-4 sm:px-6 py-2 bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-700 transition hidden text-sm sm:text-base shadow-lg shadow-emerald-500/30">
                                    <i class="fas fa-paper-plane mr-2"></i>Kirim Ujian
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar: Question Navigation & Info -->
        <div class="lg:col-span-1">
            <!-- Exam Info Card -->
            <div class="bg-white border-l-4 border-[var(--brand-primary)] rounded-lg shadow-md p-4 md:p-6 mb-6">
                <h3 class="font-bold text-gray-900 mb-4 text-sm md:text-base">📚 Informasi Ujian</h3>
                <div class="space-y-0">
                    <div class="exam-info-stat">
                        <p class="exam-info-label">⏱️ Durasi</p>
                        <p class="exam-info-value">{{ $attempt->exam->duration_minutes }} menit</p>
                    </div>
                    <div class="exam-info-stat">
                        <p class="exam-info-label">📝 Total Soal</p>
                        <p class="exam-info-value">{{ $questions->count() }} soal</p>
                    </div>
                    <div class="exam-info-stat">
                        <p class="exam-info-label">⏰ Dimulai</p>
                        <p class="exam-info-value text-xs">{{ $attempt->started_at->format('H:i:s') }}</p>
                    </div>
                </div>
            </div>

            <!-- Multiple Choice Navigator -->
            @php
                $mcCount = $questions->where('nav_type', 'mc')->count();
                $essayCount = $questions->where('nav_type', 'essay')->count();
            @endphp

            @if($mcCount > 0)
            <div class="bg-white border-l-4 border-[var(--brand-primary)] rounded-lg shadow-md p-4 md:p-6 mb-6">
                <h3 class="font-bold text-gray-900 mb-4 text-sm md:text-base">Pilihan Ganda</h3>
                <div class="grid grid-cols-5 gap-1 md:gap-2" id="mcNav">
                    @foreach($questions->where('nav_type', 'mc') as $question)
                        <button type="button" 
                            class="question-nav-btn mc-nav-btn w-full py-2 rounded-lg font-semibold text-xs md:text-sm border-2 transition"
                            data-question-id="{{ $question->id }}"
                            data-display-index="{{ $question->display_index }}"
                            style="border-color: var(--brand-glow);">
                            {{ $question->nav_position }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Essay Navigator -->
            @if($essayCount > 0)
            <div class="bg-white border-l-4 border-[var(--brand-primary)] rounded-lg shadow-md p-4 md:p-6 mb-6">
                <h3 class="font-bold text-gray-900 mb-4 text-sm md:text-base">Essay</h3>
                <div class="grid grid-cols-5 gap-1 md:gap-2" id="essayNav">
                    @foreach($questions->where('nav_type', 'essay') as $question)
                        <button type="button" 
                            class="question-nav-btn essay-nav-btn w-full py-2 rounded-lg font-semibold text-xs md:text-sm border-2 transition"
                            data-question-id="{{ $question->id }}"
                            data-display-index="{{ $question->display_index }}"
                            style="border-color: var(--brand-glow);">
                            {{ $question->nav_position }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Legend -->
            <div class="bg-white border-l-4 border-[var(--brand-primary)] rounded-lg shadow-md p-4 md:p-6">
                <h3 class="font-bold text-gray-900 mb-4 text-sm md:text-base">📊 Keterangan Status</h3>
                <div class="space-y-3 text-xs md:text-sm">
                    <div class="flex gap-3 items-center p-2 bg-[var(--brand-glow)]/10 rounded">
                        <div class="w-4 h-4 bg-[var(--brand-primary)] rounded-full flex-shrink-0"></div>
                        <span class="text-gray-700 font-medium">Soal Aktif (sedang dikerjakan)</span>
                    </div>
                    <div class="flex gap-3 items-center p-2 bg-emerald-50 rounded">
                        <div class="w-4 h-4 bg-emerald-500 rounded-full flex-shrink-0"></div>
                        <span class="text-gray-700 font-medium">Sudah Dijawab</span>
                    </div>
                    <div class="flex gap-3 items-center p-2 bg-yellow-50 rounded">
                        <div class="w-4 h-4 bg-yellow-500 rounded-full flex-shrink-0"></div>
                        <span class="text-gray-700 font-medium">Ditandai untuk Review</span>
                    </div>
                    <div class="flex gap-3 items-center p-2 bg-gray-50 rounded">
                        <div class="w-4 h-4 bg-gray-300 rounded-full flex-shrink-0"></div>
                        <span class="text-gray-700 font-medium">Belum Dijawab</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
    <script>
        const attempt_id = {{ $attempt->id }};
        const exam_id = {{ $attempt->exam->id }};
        const total_questions = {{ $questions->count() }};
        const remaining_minutes = {{ $remainingMinutes }};

        // [BARU] Global Constants dari Blade
        const DB_VIOLATION_COUNT = {{ $attempt->violation_count ?? 0 }};
        const MAX_VIOLATIONS = {{ $configs['max_violations'] ?? 3 }};
        const ANTI_CHEAT_ENABLED = {{ ($configs['anti_cheat_active'] ?? '1') == '1' ? 'true' : 'false' }};
        
        let current_question_index = 0;
        let answers = {};

        // Anti-Cheating state
        const FLOATING_WINDOW_COOLDOWN = 3000; // 3 detik
        let isFullscreen = false;
        let examHasStarted = false;   // true setelah siswa klik SIAP & fullscreen aktif
        let tabSwitchWarnings = new Map();
        let isProcessingViolation = false;

        // [FIX] Sync violationCount dari DB sebagai sumber kebenaran utama.
        let violationCount = DB_VIOLATION_COUNT;
        
        // Update sessionStorage agar sinkron dengan database
        sessionStorage.setItem('examViolationCount_' + attempt_id, violationCount.toString());

        // [FIX] Paksa window dan dokumen mendapat fokus secepatnya saat halaman dimuat.
        // Ini penting khususnya di Android yang tidak selalu auto-focus.
        window.focus();
        document.addEventListener('click', function _forceFocus() {
            window.focus();
            // Hanya perlu sekali, setelah itu hapus listener ini
            document.removeEventListener('click', _forceFocus);
        }, { once: true });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Setup gating system FIRST - prevent exam viewing until fullscreen
            setupGatingSystem();

            // Don't initialize exam features yet - wait for fullscreen confirmation
            // These will be called after user enters fullscreen
        });

        // ============================================
        // GATING SYSTEM - Block exam content until fullscreen
        // ============================================

        /**
         * Setup the gating system that blocks exam content until fullscreen is active
         */
        function setupGatingSystem() {
            // ALWAYS show readiness overlay on page load to assure user gesture for fullscreen
            showReadinessOverlay();
            
            // Bind the start button
            const startBtn = document.getElementById('startExamBtn');
            if (startBtn) {
                // Ensure event is only attached once
                const newBtn = startBtn.cloneNode(true);
                startBtn.parentNode.replaceChild(newBtn, startBtn);
                newBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // [CRITICAL FIX] Request fullscreen strictly synchronously inside the click handler.
                    // If placed inside an async function or after awaits, strict mobile browsers 
                    // will drop the 'transient user activation' token and silently block it.
                    const elem = document.documentElement;
                    try {
                        if (elem.requestFullscreen) {
                            elem.requestFullscreen().catch(err => console.warn(err));
                        } else if (elem.webkitRequestFullscreen) {
                            elem.webkitRequestFullscreen();
                        } else if (elem.mozRequestFullScreen) {
                            elem.mozRequestFullScreen();
                        } else if (elem.msRequestFullscreen) {
                            elem.msRequestFullscreen();
                        }
                    } catch (err) {
                        console.warn('Sync fullscreen request issue:', err);
                    }

                    // Proceed with initialization
                    initiateExamWithFullscreen();
                });
            }

            // Initially hide exam content
            hideExamContent();

            console.log('✅ Gating system initialized - exam content protected');
        }

        async function initiateExamWithFullscreen() {
            const examAgreedFlag = 'examAgreedAndInProgress_' + attempt_id;

            // Sembunyikan overlay readiness
            hideReadinessOverlay();

            // Tunggu browser konfirmasi fullscreen aktif (polling max 3 detik)
            const success = await waitForFullscreen(3000);

            // Set flag session
            sessionStorage.setItem(examAgreedFlag, 'true');

            // Init semua fitur ujian (konten soal, timer, dll)
            initializeExamFeatures();

            if (!success) {
                Swal.fire({
                    icon: 'info',
                    title: 'Mode Layar Penuh Gagal',
                    text: 'Browser Anda memblokir layar penuh otomatis. Silakan gunakan dalam mode bebas.',
                    confirmButtonText: 'Saya Mengerti',
                    timer: 5000
                });
            }
        }

        // [FIX 2] - waitForFullscreen
        function waitForFullscreen(timeoutMs) {
            return new Promise(function(resolve) {
                var start = Date.now();
                function check() {
                    var inFs = document.fullscreenElement ||
                               document.webkitFullscreenElement ||
                               document.mozFullScreenElement ||
                               document.msFullscreenElement;
                    if (inFs) {
                        isFullscreen = true;
                        resolve(true);
                    } else if (Date.now() - start >= timeoutMs) {
                        // Timeout: lanjut saja meski fullscreen belum terkonfirmasi
                        resolve(false);
                    } else {
                        setTimeout(check, 100);
                    }
                }
                check();
            });
        }

        /**
         * Initialize all exam features after fullscreen is confirmed
         */
        function initializeExamFeatures() {
            examHasStarted = true;    // Ujian resmi dimulai
            // [FIX] Urutan eksekusi kritis untuk mencegah blank screen
            // 1. Lepas blur dan buat kontainer tampil penuh
            showExamContent();
            
            // 2. Tampilkan soal (sekarang sudah ada ruang fisik di DOM untuk dirender hitungannya)
            showQuestion(current_question_index);
            
            // 3. Update navigator dan komponen lainnya
            updateQuestionNav();
            initAntiCheating();
            initTimer();
            initEventListeners();
            initializeHeartbeat();
            setupConfetti();
            
            // [FIX] Tambahkan delay 1 detik sebelum memulai continuous check.
            // Ini untuk memberi waktu browser menyelesaikan transisi animasi Fullscreen.
            // Pencegahan race condition agar overlay tidak muncul prematur.
            setTimeout(() => {
                startContinuousFullscreenCheck();
                console.log('⏱️ Continuous fullscreen monitoring started after settle delay');
            }, 1500);
            
            console.log('✅ Exam features initialized');
        }

        // [FIX 3] - startContinuousFullscreenCheck
        function startContinuousFullscreenCheck() {
            // Flag internal: Monitoring baru benar-benar arming setelah 
            // deteksi pertama kali masuk fullscreen.
            var monitoringStarted = false;

            setInterval(function() {
                var isCurrentlyFullscreen = document.fullscreenElement ||
                                            document.webkitFullscreenElement ||
                                            document.mozFullScreenElement ||
                                            document.msFullscreenElement;

                if (isCurrentlyFullscreen) {
                    monitoringStarted = true;
                    showExamContent();
                    hideFullscreenOverlay();
                    isFullscreen = true;
                } else if (monitoringStarted && examHasStarted) {
                    // Hanya tampil jika sebelumnya PERNAH fullscreen DAN ujian sudah dimulai
                    hideExamContent();
                    showFullscreenOverlay();
                    isFullscreen = false;
                }
            }, 1000);
        }

        /**
         * Hide exam content and show blur
         */
        function hideExamContent() {
            const container = document.querySelector('.exam-container');
            if (container && !container.classList.contains('blurred')) {
                container.classList.add('blurred');
            }
        }

        /**
         * Show exam content and remove blur
         */
        function showExamContent() {
            const container = document.querySelector('.exam-container');
            if (container && container.classList.contains('blurred')) {
                container.classList.remove('blurred');
            }
        }

        /**
         * Hide readiness overlay
         */
        function hideReadinessOverlay() {
            const overlay = document.getElementById('readinessOverlay');
            if (overlay) {
                overlay.classList.add('hidden');
            }
        }

        /**
         * Show readiness overlay
         */
        function showReadinessOverlay() {
            const overlay = document.getElementById('readinessOverlay');
            if (overlay) {
                overlay.classList.remove('hidden');
            }
        }

        // ============================================
        // ANTI-CHEATING SYSTEM
        // ============================================

        /**
         * Handle fullscreen entry from exam index page
         * Checks if we came from "Mulai Ujian" button and requests fullscreen again
         * since navigation may have exited it
         */
        function handleFullscreenEntry() {
            if (sessionStorage.getItem('shouldEnterFullscreen') === 'true') {
                // Clear the flag
                sessionStorage.removeItem('shouldEnterFullscreen');
                
                // Add small delay to ensure page is fully ready
                setTimeout(() => {
                    enterFullscreenMode();
                }, 500);
            }
        }

        async function enterFullscreenMode() {
            await robustFullscreenRequest();
        }

        /**
         * Initialize comprehensive anti-cheating protections
         */
        function initAntiCheating() {
            // Disable text selection and right-click
            disableTextSelection();
            disableRightClick();
            disableKeyboardShortcuts();
            setupFullscreenMode();
            setupTabSwitchDetection();
            setupPrintscreenProtection();
            startFloatingWindowHeartbeat(); // [TAMBAHAN] Heartbeat cek document.hasFocus() setiap 2 detik
        }

        /**
         * Disable text selection on exam area
         */
        function disableTextSelection() {
            document.addEventListener('selectstart', (e) => {
                e.preventDefault();
                return false;
            });

            document.addEventListener('copy', (e) => {
                e.preventDefault();
                return false;
            });
        }

        /**
         * Disable right-click context menu
         */
        function disableRightClick() {
            document.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                recordViolation('right_click', 'Attempted to access right-click menu');
                Swal.fire({
                    title: '⛔ Dilarang!',
                    text: 'Klik kanan tidak diizinkan selama ujian.',
                    icon: 'warning',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                });
                return false;
            });
        }

        /**
         * Disable keyboard shortcuts (Ctrl+C, Ctrl+V, F12, etc.)
         */
        function disableKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
                const ctrl = isMac ? e.metaKey : e.ctrlKey;

                // Ctrl+C (Copy)
                if (ctrl && e.key === 'c') {
                    e.preventDefault();
                    recordViolation('copy_paste', 'Attempted Ctrl+C');
                    return false;
                }

                // Ctrl+V (Paste)
                if (ctrl && e.key === 'v') {
                    e.preventDefault();
                    recordViolation('copy_paste', 'Attempted Ctrl+V');
                    return false;
                }

                // Ctrl+U (View Source)
                if (ctrl && e.key === 'u') {
                    e.preventDefault();
                    recordViolation('dev_tools', 'Attempted Ctrl+U (View Source)');
                    return false;
                }

                // F12 (Developer Tools)
                if (e.key === 'F12') {
                    e.preventDefault();
                    recordViolation('dev_tools', 'Attempted F12 (Developer Tools)');
                    return false;
                }

                // Ctrl+Shift+I (Inspect Element)
                if (ctrl && e.shiftKey && e.key === 'I') {
                    e.preventDefault();
                    recordViolation('dev_tools', 'Attempted Ctrl+Shift+I (Inspect)');
                    return false;
                }

                // Ctrl+Shift+J (Console)
                if (ctrl && e.shiftKey && e.key === 'J') {
                    e.preventDefault();
                    recordViolation('dev_tools', 'Attempted Ctrl+Shift+J (Console)');
                    return false;
                }

                // Ctrl+Shift+K (Console - Firefox)
                if (ctrl && e.shiftKey && e.key === 'K') {
                    e.preventDefault();
                    recordViolation('dev_tools', 'Attempted Ctrl+Shift+K (Console)');
                    return false;
                }
            });
        }

        /**
         * Setup fullscreen mode requirement - called after exam features initialized
         */
        function setupFullscreenMode() {
            // Detect fullscreen exit using fullscreenchange event
            document.addEventListener('fullscreenchange', handleFullscreenChange);
            document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
            document.addEventListener('mozfullscreenchange', handleFullscreenChange);
            document.addEventListener('msfullscreenchange', handleFullscreenChange);

            // Setup online/offline detection for sync
            window.addEventListener('online', () => {
                console.log('🌐 Connection restored - syncing offline answers');
                syncOfflineAnswers();
            });

            window.addEventListener('offline', () => {
                console.log('📡 Connection lost - will cache answers locally');
            });

            // Return to fullscreen button - MUST be triggered by user interaction
            const returnBtn = document.getElementById('returnFullscreenBtn');
            if (returnBtn) {
                returnBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const elem = document.documentElement;
                    try {
                        if (elem.requestFullscreen) {
                            elem.requestFullscreen().catch(err => console.warn(err));
                        } else if (elem.webkitRequestFullscreen) {
                            elem.webkitRequestFullscreen();
                        } else if (elem.mozRequestFullScreen) {
                            elem.mozRequestFullScreen();
                        } else if (elem.msRequestFullscreen) {
                            elem.msRequestFullscreen();
                        }
                    } catch (err) {
                        console.warn('Sync fullscreen request issue:', err);
                    }
                });
            }

            // Detect Esc key press (browser exit without user clicking overlay button)
            document.addEventListener('keydown', handleKeypress);
        }

        /**
         * Handle Escape key press - user trying to exit fullscreen
         */
        function handleKeypress(event) {
            // Only track during exam
            if (!isFullscreen || violationCount >= MAX_VIOLATIONS) return;

            if (event.key === 'Escape' || event.keyCode === 27) {
                event.preventDefault();
                event.stopPropagation();
                
                // Record as violation attempt
                recordViolation('fullscreen_exit', 'User pressed Escape key to exit fullscreen');
                violationCount++;
                
                // Show warning and overlay
                Swal.fire({
                    title: '⚠️ PERINGATAN!',
                    html: `<p>Anda mencoba keluar dari mode ujian.</p><p class="mt-2 font-bold">Pelanggaran ke-<span class="text-red-500">${violationCount}</span> dari ${MAX_VIOLATIONS}</p>`,
                    icon: 'warning',
                    confirmButtonColor: getComputedStyle(document.body).getPropertyValue('--brand-primary').trim() || '#4f46e5',
                    confirmButtonText: 'Kembali ke Ujian',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                });
                
                // Force back into fullscreen
                const overlay = document.getElementById('fullscreenOverlay');
                if (overlay) {
                    overlay.classList.add('active');
                }
                
                // Check if max violations reached
                if (violationCount >= MAX_VIOLATIONS) {
                    autoSubmit(`Anda telah mencapai batas maksimal pelanggaran (${MAX_VIOLATIONS}). Ujian akan dikirim otomatis.`);
                }

                return false;
            }
        }



        /**
         * Handle fullscreen exit - comprehensive detection
         */
        function handleFullscreenChange() {
            // Check all fullscreen API variants
            const isCurrentlyFullscreen = document.fullscreenElement || 
                                         document.webkitFullscreenElement || 
                                         document.mozFullScreenElement || 
                                         document.msFullscreenElement;

            if (!isCurrentlyFullscreen && isFullscreen && examHasStarted) {
                // ❌ STUDENT EXITED FULLSCREEN
                isFullscreen = false;
                violationCount++;
                
                console.warn('⚠️ Fullscreen exit detected! Violation count:', violationCount);
                
                // Record violation
                recordViolation('fullscreen_exit', 'Fullscreen mode exited');
                
                // Show overlay blocking
                showFullscreenOverlay();
                
                // Show warning
                Swal.fire({
                    title: '⚠️ PERINGATAN!',
                    html: `<p>Anda terdeteksi keluar dari mode ujian.</p><p class="mt-2 font-bold">Pelanggaran ke-<span class="text-red-500">${violationCount}</span> dari ${MAX_VIOLATIONS}</p>`,
                    icon: 'warning',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Kembali ke Mode Ujian',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                });
                
                // Check if max violations reached
                if (violationCount >= MAX_VIOLATIONS) {
                    autoSubmit(`Anda telah mencapai batas maksimal pelanggaran fullscreen (${MAX_VIOLATIONS}). Ujian akan dikirim otomatis.`);
                }
                
            } else if (isCurrentlyFullscreen) {
                // ✅ IN FULLSCREEN
                isFullscreen = true;
                hideFullscreenOverlay();
                console.log('✅ In fullscreen mode');
            }
        }

        /**
         * Show fullscreen exit overlay - completely blocks exam view
         */
        function showFullscreenOverlay() {
            const overlay = document.getElementById('fullscreenOverlay');
            if (overlay) {
                overlay.classList.add('active');
            }
        }

        /**
         * Hide fullscreen exit overlay - reveal exam again
         */
        function hideFullscreenOverlay() {
            const overlay = document.getElementById('fullscreenOverlay');
            if (overlay) {
                overlay.classList.remove('active');
            }
        }

        /**
         * Setup tab/window switch detection
         */
        function setupTabSwitchDetection() {
            if (!ANTI_CHEAT_ENABLED) return;
            // Detect when user leaves the tab
            document.addEventListener('visibilitychange', handleVisibilityChange);
            window.addEventListener('blur', handleWindowBlur);
            window.addEventListener('focus', handleWindowFocus);
        }

        /**
         * Handle visibility change (Page Visibility API)
         */
        function handleVisibilityChange() {
            if (!ANTI_CHEAT_ENABLED) return;
            if (document.hidden) {
                // Report violation in real-time to admin dashboard
                reportViolationRealTime('tab_switch', 'Siswa meninggalkan tab ujian');
                
                // Also use legacy method for backward compatibility
                recordViolation('tab_switch', 'Student switched tab/window');
                
                // Increment violation counter
                violationCount++;
                
                const warningMsg = `⚠️ Peringatan! Anda terdeteksi meninggalkan halaman ujian.\\nPerlanggaran ke-${violationCount} dari ${MAX_VIOLATIONS}.`;
                
                Swal.fire({
                    title: '⚠️ PERINGATAN!',
                    html: `<p>Anda terdeteksi meninggalkan halaman ujian.</p><p class="mt-2 font-bold">Pelanggaran ke-<span class="text-red-500">${violationCount}</span> dari ${MAX_VIOLATIONS}</p>`,
                    icon: 'warning',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Kembali ke Ujian',
                });

                // Auto-submit if max violations reached
                if (violationCount >= MAX_VIOLATIONS) {
                    autoSubmit(`Anda telah mencapai batas maksimal pelanggaran (${MAX_VIOLATIONS}). Ujian akan dikirim otomatis.`);
                }
            }
        }

        /**
         * Handle window blur (floating window detection)
         * Detects ketika siswa membuka aplikasi lain atau jendela mengambang
         */
        function handleWindowBlur() {
            if (!ANTI_CHEAT_ENABLED) return;
            // Cegah spam violations - hanya proses 1 violation per 3 detik
            if (isProcessingViolation) {
                console.warn('⏳ Blur event masih dalam cooldown, skip duplicate detection');
                return;
            }

            // Jangan proses jika sudah mencapai max violations
            if (violationCount >= MAX_VIOLATIONS) {
                return;
            }

            // Tandai sedang memproses violation
            isProcessingViolation = true;

            // Increment violation counter
            violationCount++;

            console.warn(`⚠️ Floating window detected! Violation count: ${violationCount}/${MAX_VIOLATIONS}`);

            // Catat violation ke backend
            recordViolation('floating_window', 'Jendela mengambang atau aplikasi lain terdeteksi');

            // Tampilkan warning sesuai strike number
            if (violationCount === 1) {
                Swal.fire({
                    title: '⚠️ PERINGATAN 1',
                    html: `<p>Jangan membuka aplikasi lain atau jendela mengambang saat ujian!</p><p style="margin-top: 10px; font-size: 0.9em;">Pelanggaran: 1 dari ${MAX_VIOLATIONS}</p>`,
                    icon: 'warning',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Mengerti, Lanjut Ujian',
                });
            } else if (violationCount === 2) {
                Swal.fire({
                    title: '⚠️ PERINGATAN 2 (TERAKHIR)',
                    html: `<p>Ini adalah peringatan kedua. Jangan buka aplikasi lain!</p><p style="margin-top: 10px; font-size: 0.9em;">Jika ada pelanggaran lagi, ujian akan ditutup otomatis.</p><p style="margin-top: 5px; font-size: 0.9em;">Pelanggaran: 2 dari ${MAX_VIOLATIONS}</p>`,
                    icon: 'warning',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Mengerti, Lanjut Ujian',
                });
            } else if (violationCount >= MAX_VIOLATIONS) {
                // Strike ke-3: auto submit
                Swal.fire({
                    title: '❌ BATAS PELANGGARAN TERLAMPAUI',
                    html: `<p>Anda telah melanggar aturan ujian ${MAX_VIOLATIONS} kali.</p><p style="margin-top: 10px;">Ujian akan ditutup dan jawaban Anda akan dikirimkan otomatis.</p>`,
                    icon: 'error',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Auto submit menggunakan fungsi yang sudah ada
                    autoSubmit('Batas pelanggaran tercapai. Ujian ditutup otomatis.');
                });
            }

            // Reset cooldown setelah 3 detik
            setTimeout(() => {
                isProcessingViolation = false;
                console.log('✅ Floating window cooldown ended, ready for next detection');
            }, FLOATING_WINDOW_COOLDOWN);
        }

        /**
         * Handle window focus
         */
        function handleWindowFocus() {
            // User returned to window
        }

        /**
         * [TAMBAHAN] Heartbeat Focus Check — setInterval 2 detik
         * Mendeteksi floating window / split-screen yang tidak selalu memicu window.blur.
         * Debounce terintegrasi via isProcessingViolation flag yang sudah ada.
         * Dipanggil dari initAntiCheating() setelah ujian dimulai.
         */
        function startFloatingWindowHeartbeat() {
            if (!ANTI_CHEAT_ENABLED) return;
            // [FIX] Interval dipersingkat dari 2 detik menjadi 1 detik untuk deteksi yang lebih responsif,
            // khususnya di perangkat mobile/Android yang blur event-nya tidak selalu reliable.
            setInterval(() => {
                // Hanya aktif saat ujian berlangsung dan belum mencapai batas pelanggaran
                if (violationCount >= MAX_VIOLATIONS) return;
                if (isProcessingViolation) return;

                // Cek apakah fokus dokumen hilang (aplikasi mengambang / split-screen)
                if (!document.hasFocus()) {
                    console.warn('🔍 [hasFocus Heartbeat] Fokus dokumen hilang — aplikasi mengambang terdeteksi');
                    handleWindowBlur(); // Re-use fungsi blur yang sudah ada (dengan debounce 3 detik)
                }
            }, 1000); // [FIX] Cek setiap 1 detik (lebih agresif, masih efisien karena debounce melindungi from spam)

            console.log('✅ Floating window heartbeat aktif — interval 1 detik (hasFocus check)');
        }

        /**
         * Setup printscreen protection
         */
        function setupPrintscreenProtection() {
            document.addEventListener('keyup', (e) => {
                if (e.key === 'PrintScreen') {
                    recordViolation('printscreen', 'attempted printscreen');
                    showPrintscreenOverlay();
                }
            });
        }

        /**
         * Show printscreen overlay briefly
         */
        function showPrintscreenOverlay() {
            const overlay = document.getElementById('printscreenOverlay');
            if (overlay) {
                overlay.style.display = 'block';
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 500);
            }
        }

        /**
         * Record violation to backend
         */
        function recordViolation(violationType, description = '') {
            // Persist violation count in sessionStorage (already incremented by caller)
            sessionStorage.setItem('examViolationCount_' + attempt_id, violationCount.toString());
            
            // Use new real-time API first
            reportViolationRealTime(violationType, description);

            // Also keep legacy endpoint for backward compatibility
            fetch(`/student/exams/${attempt_id}/save-violation`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]')?.value || ''
                },
                body: JSON.stringify({
                    violation_type: violationType,
                    description: description || null
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('✓ Violation recorded via legacy endpoint:', data);
            })
            .catch(error => {
                console.error('⚠️ Error recording violation via legacy:', error);
            });
        }

        /**
         * Auto-submit exam with message
         */
        function autoSubmit(message) {
            Swal.fire({
                title: '⏱️ Ujian Berakhir',
                text: message,
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonColor: getComputedStyle(document.body).getPropertyValue('--brand-primary').trim() || '#4f46e5',
                confirmButtonText: 'Lanjutkan'
            }).then(() => {
                document.getElementById('examForm').submit();
                document.getElementById('submitBtn').click();
            });
        }

        /**
         * Show exam start confirmation
         */
        function showStartExamConfirmation() {
            if (window.location.hostname !== 'localhost') {
                // Only on production - try to enter fullscreen on page load
                setTimeout(() => {
                    if (!isFullscreen) {
                        Swal.fire({
                            title: '📺 Mode Layar Penuh',
                            text: 'Ujian akan dimulai dalam mode layar penuh untuk menjaga integritas ujian.',
                            icon: 'info',
                            confirmButtonText: 'Mulai Ujian'
                        }).then(() => {
                            robustFullscreenRequest();
                        });
                    }
                }, 500);
            }
        }

        // Setup Confetti Canvas
        function setupConfetti() {
            const canvas = document.createElement('canvas');
            canvas.id = 'confettiCanvas';
            document.body.appendChild(canvas);
        }

        // Timer Management
        function initTimer() {
            console.log('⏱️ Initializing timer...');
            
            // Function to start the actual countdown
            const startCountdown = (seconds) => {
                let totalSeconds = Math.floor(seconds);
                if (totalSeconds <= 0) {
                    autoSubmit('Waktu ujian telah habis.');
                    return;
                }

                console.log(`⏱️ Timer started with ${totalSeconds} seconds`);
                
                const timerInterval = setInterval(() => {
                    totalSeconds--;
                    
                    const minutes = Math.floor(totalSeconds / 60);
                    const secondsLeft = Math.floor(totalSeconds % 60);
                    const display = `${String(minutes).padStart(2, '0')}:${String(secondsLeft).padStart(2, '0')}`;
                    
                    const timerEl = document.getElementById('timer');
                    if (timerEl) {
                        timerEl.textContent = display;
                        
                        // Update timer color based on remaining time
                        timerEl.classList.remove('timer-normal', 'timer-warning', 'timer-critical');
                        if (minutes <= 0 && secondsLeft <= 30) {
                            timerEl.classList.add('timer-critical');
                        } else if (minutes <= 5) {
                            timerEl.classList.add('timer-warning');
                        } else {
                            timerEl.classList.add('timer-normal');
                        }
                    }
                    
                    if (totalSeconds <= 0) {
                        clearInterval(timerInterval);
                        autoSubmit('Waktu ujian habis. Ujian akan dikirim otomatis...');
                    }
                }, 1000);
            };

            // Attempt to sync with server, but fallback to local if it fails
            fetch(`/student/exams/{{ $attempt->id }}/remaining-time`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response non-ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success && !data.expired) {
                        startCountdown(data.total_seconds);
                    } else if (data.expired) {
                        autoSubmit('Waktu ujian telah habis. Ujian akan dikirim otomatis...');
                    } else {
                        throw new Error('Sync failed via data status');
                    }
                })
                .catch(error => {
                    console.error('⚠️ Timer sync failed, using local fallback:', error);
                    // Fallback: use local timer from backend value ($remainingMinutes)
                    startCountdown(remaining_minutes * 60);
                });
        }

        // Event Listeners
        function initEventListeners() {
            // Question answers - safe with forEach
            document.querySelectorAll('.question-answer').forEach(el => {
                el.addEventListener('change', function() {
                    autosaveAnswer(this.dataset.questionId);
                    updateQuestionNav();
                });
            });

            // Navigation buttons - with null-safety guards
            const prevBtn = document.getElementById('prevBtn');
            if (prevBtn) prevBtn.addEventListener('click', previousQuestion);
            else console.warn('⚠️ prevBtn not found in DOM');

            const nextBtn = document.getElementById('nextBtn');
            if (nextBtn) nextBtn.addEventListener('click', nextQuestion);
            else console.warn('⚠️ nextBtn not found in DOM');

            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) submitBtn.addEventListener('click', submitExam);
            else console.warn('⚠️ submitBtn not found in DOM');

            // Ragu-ragu button
            const raguBtn = document.getElementById('raguBtn');
            if (raguBtn) raguBtn.addEventListener('click', toggleRaguReview);
            else console.warn('⚠️ raguBtn not found in DOM');

            // Question navigator buttons - safe with forEach
            document.querySelectorAll('.question-nav-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const displayIndex = parseInt(this.dataset.displayIndex);
                    goToQuestion(displayIndex);
                });
            });

            console.log('✅ Event listeners initialized');
        }

        // Question Navigation
        function showQuestion(index) {
            // Hide all questions
            document.querySelectorAll('.question-slide').forEach(q => q.style.display = 'none');
            
            // Show selected question
            const slides = document.querySelectorAll('.question-slide');
            if (slides[index]) {
                slides[index].style.display = 'block';
                current_question_index = index;
                
                // Update display number and progress
                const questionNumber = index + 1;
                document.getElementById('currentQNum').textContent = questionNumber;
                
                // Calculate and update progress bar and percentage
                const progressPercent = Math.round(((index + 1) / total_questions) * 100);
                document.getElementById('progressBar').style.width = progressPercent + '%';
                document.getElementById('completionPercent').textContent = progressPercent;
                
                // Count answered questions
                updateAnsweredCount();
                
                // Update button states
                document.getElementById('prevBtn').disabled = index === 0;
                
                // Hide Next button and show Submit button when on last question
                const nextBtn = document.getElementById('nextBtn');
                const submitBtn = document.getElementById('submitBtn');
                
                if (index === total_questions - 1) {
                    nextBtn.classList.add('hidden');
                    submitBtn.classList.remove('hidden');
                } else {
                    nextBtn.classList.remove('hidden');
                    submitBtn.classList.add('hidden');
                }
                
                // Update Ragu button styling based on current question's review status
                const markReviewField = slides[index].querySelector('.mark-review');
                const raguBtn = document.getElementById('raguBtn');
                if (markReviewField?.value === '1') {
                    raguBtn.classList.add('bg-yellow-600', 'ring-2', 'ring-yellow-400');
                    raguBtn.classList.remove('bg-yellow-500');
                } else {
                    raguBtn.classList.remove('bg-yellow-600', 'ring-2', 'ring-yellow-400');
                    raguBtn.classList.add('bg-yellow-500');
                }
                
                updateQuestionNav();
            }
        }

        // Function to update answered questions count
        function updateAnsweredCount() {
            let answeredCount = 0;
            document.querySelectorAll('.question-slide').forEach((slide) => {
                const checkedInput = slide.querySelector('input:checked');
                const textarea = slide.querySelector('textarea');
                
                if (checkedInput || (textarea && textarea.value.trim())) {
                    answeredCount++;
                }
            });
            
            const answeredCountEl = document.getElementById('answeredCount');
            if (answeredCountEl) {
                answeredCountEl.textContent = answeredCount + ' terjawab';
            }
        }

        function nextQuestion() {
            if (current_question_index < total_questions - 1) {
                showQuestion(current_question_index + 1);
                
                // Auto-scroll to top of question container with smooth behavior
                setTimeout(() => {
                    document.getElementById('questionContainer').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 100);
            }
        }

        function previousQuestion() {
            if (current_question_index > 0) {
                showQuestion(current_question_index - 1);
                
                // Auto-scroll to top of question container with smooth behavior
                setTimeout(() => {
                    document.getElementById('questionContainer').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 100);
            }
        }

        function toggleRaguReview() {
            const slides = document.querySelectorAll('.question-slide');
            const currentSlide = slides[current_question_index];
            const markReviewField = currentSlide.querySelector('.mark-review');
            const raguBtn = document.getElementById('raguBtn');
            
            // Toggle the review status (0 = not marked, 1 = marked)
            const currentValue = markReviewField.value;
            markReviewField.value = currentValue === '0' ? '1' : '0';
            
            // Update button styling
            if (markReviewField.value === '1') {
                raguBtn.classList.add('bg-yellow-600', 'ring-2', 'ring-yellow-400');
                raguBtn.classList.remove('bg-yellow-500');
            } else {
                raguBtn.classList.remove('bg-yellow-600', 'ring-2', 'ring-yellow-400');
                raguBtn.classList.add('bg-yellow-500');
            }
            
            // Save and update navigation
            autosaveAnswer(markReviewField.dataset.questionId);
            updateQuestionNav();
        }

        function goToQuestion(displayIndex) {
            showQuestion(displayIndex);
        }

        // ============================================
        // DEBOUNCED AUTOSAVE SYSTEM
        // ============================================
        const autosaveTimers = {};
        const AUTOSAVE_DELAY = 500; // 500ms debounce

        /**
         * Autosave answer with 500ms debounce
         * Prevents excessive requests when user changes answers rapidly
         */
        function autosaveAnswer(questionId) {
            // Clear existing timer for this question
            if (autosaveTimers[questionId]) {
                clearTimeout(autosaveTimers[questionId]);
            }

            // Set new timer - will execute after 500ms of inactivity
            autosaveTimers[questionId] = setTimeout(() => {
                executeAutosave(questionId);
            }, AUTOSAVE_DELAY);
        }

        /**
         * Execute the actual autosave request
         */
        async function executeAutosave(questionId) {
            try {
                const slide = document.querySelector(`[data-question-id="${questionId}"]`);
                if (!slide) return;

                const questionType = slide.querySelector('.question-type').value;
                
                let data = {
                    question_id: questionId,
                };

                if (questionType === 'mc') {
                    const selected = slide.querySelector(`input[name="answer_${questionId}"]:checked`);
                    if (selected) {
                        data.selected_answer = selected.value;
                    }
                } else {
                    const textarea = slide.querySelector(`textarea[name="answer_${questionId}"]`);
                    if (textarea && textarea.value) {
                        data.essay_answer = textarea.value;
                    }
                }

                const response = await fetch(`/student/exams/${attempt_id}/autosave`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    throw new Error('Autosave failed');
                }

                const result = await response.json();
                
                // If answer saved successfully, cache to localStorage
                if (result.success) {
                    const cacheKey = `exam_attempt_${attempt_id}_question_${questionId}`;
                    localStorage.setItem(cacheKey, JSON.stringify(data));
                    
                    // Also send real-time progress update (non-blocking)
                    sendAnswerProgress(questionId, data.selected_answer || data.essay_answer || null);
                }
            } catch (error) {
                console.error(`Error autosaving question ${questionId}:`, error);
                
                // If offline, cache the answer
                if (!navigator.onLine) {
                    const cacheKey = `exam_attempt_${attempt_id}_question_${questionId}`;
                    const slide = document.querySelector(`[data-question-id="${questionId}"]`);
                    if (slide) {
                        const questionType = slide.querySelector('.question-type').value;
                        let cacheData = { question_id: questionId };

                        if (questionType === 'mc') {
                            const selected = slide.querySelector(`input[name="answer_${questionId}"]:checked`);
                            if (selected) cacheData.selected_answer = selected.value;
                        } else {
                            const textarea = slide.querySelector(`textarea[name="answer_${questionId}"]`);
                            if (textarea?.value) cacheData.essay_answer = textarea.value;
                        }

                        localStorage.setItem(cacheKey, JSON.stringify(cacheData));
                    }
                }
            }
        }

        // ============================================
        // REAL-TIME PROGRESS TRACKING
        // ============================================

        /**
         * Send answer progress to backend in real-time
         */
        async function sendAnswerProgress(questionId, answer) {
            try {
                const response = await fetch(`/student/exams/${attempt_id}/record-answer`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        question_id: questionId,
                        answer: answer || null
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();
                if (result.success) {
                    console.log(`✓ Progress saved: ${result.total_answered} questions answered`);
                }
            } catch (error) {
                console.log(`⚠️ Could not send progress: ${error.message} - will retry`);
                // Silent fail - autosave is main method, this is supplementary
            }
        }

        /**
         * Report violation in real-time
         */
        async function reportViolationRealTime(violationType, details = '') {
            try {
                console.log(`⚠️ Reporting violation: ${violationType} - ${details}`);
                
                const response = await fetch(`/student/exams/${attempt_id}/report-violation`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        violation_type: violationType,
                        details: details || null
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();
                if (result.success) {
                    console.log(`✓ Violation recorded: ${result.violation_count} total`);
                }
            } catch (error) {
                console.error(`✗ Error reporting violation: ${error.message}`);
                // Save to localStorage as backup
                const violations = JSON.parse(localStorage.getItem('exam_violations_' + attempt_id) || '[]');
                violations.push({
                    type: violationType,
                    description: details,
                    timestamp: new Date().toISOString()
                });
                localStorage.setItem('exam_violations_' + attempt_id, JSON.stringify(violations));
            }
        }

        // ============================================
        // HEARTBEAT SYSTEM - Real-time Session Monitoring
        // ============================================
        let heartbeatInterval;
        let isExamSubmitted = false;
        let offlineAnswerCache = [];

        /**
         * Initialize heartbeat system on exam start
         * Sends session signal every 20 seconds to server
         */
        function initializeHeartbeat() {
            const data = localStorage.getItem('exam_session_data');
            if (!data) return;

            const sessionData = JSON.parse(data);
            
            // First heartbeat after 5 seconds
            setTimeout(() => sendHeartbeat(sessionData), 5000);

            // Then every 20 seconds
            heartbeatInterval = setInterval(() => {
                if (!isExamSubmitted) {
                    sendHeartbeat(sessionData);
                }
            }, 20000);

            // Cleanup on page leave
            window.addEventListener('beforeunload', () => {
                if (heartbeatInterval) clearInterval(heartbeatInterval);
            });

            console.log('✅ Heartbeat system initialized - 20s interval');
        }

        /**
         * Send heartbeat signal to server every 20 seconds
         * Includes: current_question, violation_count, session_id
         */
        async function sendHeartbeat(sessionData) {
            try {
                const currentSlide = document.querySelector('[data-slide-active="true"]');
                const currentQuestion = currentSlide ? 
                    parseInt(currentSlide.dataset.questionId) : 
                    getCurrentQuestionId();
                
                const violationCount = parseInt(
                    document.querySelector('#violationCount')?.textContent || '0'
                );

                const response = await fetch(`/student/exams/${attempt_id}/heartbeat`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        current_question: currentQuestion,
                        violation_count: violationCount,
                        session_id: sessionData.session_id
                    })
                });

                if (!response.ok) {
                    throw new Error(`Heartbeat failed: ${response.statusText}`);
                }

                const result = await response.json();
                
                // Check if session is still active
                if (result.session_status === 'locked' || result.session_status === 'ended') {
                    handleSessionEnded(result.reason);
                }

                // Store heartbeat timestamp for offline detection
                localStorage.setItem('last_heartbeat_time', Date.now());
                
            } catch (error) {
                console.error('❌ Heartbeat error:', error);
                
                // Try to cache answer if offline
                if (!navigator.onLine) {
                    cacheAnswersForSync();
                }
            }
        }

        /**
         * Cache answers to localStorage when offline
         * Will sync when connection is restored
         */
        function cacheAnswersForSync() {
            const cacheKey = `exam_attempt_${attempt_id}_answers`;
            const answers = {};

            document.querySelectorAll('[data-question-id]').forEach(slide => {
                const questionId = slide.dataset.questionId;
                const questionType = slide.querySelector('.question-type')?.value;

                if (questionType === 'mc') {
                    const selected = slide.querySelector(`input[name="answer_${questionId}"]:checked`);
                    if (selected) {
                        answers[questionId] = { selected_answer: selected.value };
                    }
                } else {
                    const textarea = slide.querySelector(`textarea[name="answer_${questionId}"]`);
                    if (textarea?.value) {
                        answers[questionId] = { essay_answer: textarea.value };
                    }
                }
            });

            if (Object.keys(answers).length > 0) {
                localStorage.setItem(cacheKey, JSON.stringify(answers));
                console.log('💾 Answers cached for offline:', answers);
            }
        }

        /**
         * Sync offline cached answers when connection returns
         */
        async function syncOfflineAnswers() {
            const cacheKey = `exam_attempt_${attempt_id}_answers`;
            const cachedAnswers = localStorage.getItem(cacheKey);

            if (!cachedAnswers) return;

            try {
                const answers = JSON.parse(cachedAnswers);
                
                const response = await fetch(`/student/exams/${attempt_id}/sync-offline`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify(answers)
                });

                if (response.ok) {
                    localStorage.removeItem(cacheKey);
                    console.log('✅ Offline answers synced successfully');
                }
            } catch (error) {
                console.error('Error syncing offline answers:', error);
            }
        }

        /**
         * Handle session ended by admin or connection
         */
        function handleSessionEnded(reason = '') {
            isExamSubmitted = true;
            if (heartbeatInterval) clearInterval(heartbeatInterval);

            Swal.fire({
                title: '⚠️ Ujian Dihentikan',
                html: `<p>Ujian Anda telah dihentikan oleh sistem.</p><p class="text-sm text-gray-600 mt-2">${reason}</p>`,
                icon: 'warning',
                allowOutsideClick: false,
                willOpen: () => {
                    // Disable all exam controls
                    document.querySelectorAll('[data-question-id] input, [data-question-id] textarea').forEach(el => {
                        el.disabled = true;
                    });
                    document.getElementById('submitBtn').disabled = true;
                }
            });
        }

        /**
         * Get current question ID from the visible slide
         */
        function getCurrentQuestionId() {
            const activeSlide = document.querySelector('[data-slide-active="true"]');
            if (activeSlide && activeSlide.dataset.questionId) {
                return parseInt(activeSlide.dataset.questionId);
            }
            return 1;
        }

        // Update Question Navigator
        function updateQuestionNav() {
            const slides = document.querySelectorAll('.question-slide');
            
            // Update MC buttons
            document.querySelectorAll('.mc-nav-btn').forEach((btn) => {
                const displayIndex = parseInt(btn.dataset.displayIndex);
                const slide = slides[displayIndex];
                
                if (!slide) return;
                
                // Reset button styles
                btn.classList.remove('question-nav-active', 'question-nav-answered', 'question-nav-review');
                btn.style.borderColor = '';
                
                if (displayIndex === current_question_index) {
                    btn.classList.add('question-nav-active');
                } else {
                    // Check for answer
                    let hasAnswer = false;
                    const checkedInput = slide.querySelector('input:checked');
                    if (checkedInput) {
                        hasAnswer = true;
                    } else {
                        const textarea = slide.querySelector('textarea');
                        if (textarea && textarea.value.trim()) {
                            hasAnswer = true;
                        }
                    }
                    
                    const isMarked = slide.querySelector('.mark-review')?.value === '1';
                    
                    if (hasAnswer && !isMarked) {
                        btn.classList.add('question-nav-answered');
                    } else if (isMarked) {
                        btn.classList.add('question-nav-review');
                    }
                }
            });

            // Update Essay buttons
            document.querySelectorAll('.essay-nav-btn').forEach((btn) => {
                const displayIndex = parseInt(btn.dataset.displayIndex);
                const slide = slides[displayIndex];
                
                if (!slide) return;
                
                // Reset button styles
                btn.classList.remove('question-nav-active', 'question-nav-answered', 'question-nav-review');
                btn.style.borderColor = '';
                
                if (displayIndex === current_question_index) {
                    btn.classList.add('question-nav-active');
                } else {
                    // Check for answer
                    let hasAnswer = false;
                    const textarea = slide.querySelector('textarea');
                    if (textarea && textarea.value.trim()) {
                        hasAnswer = true;
                    }
                    
                    const isMarked = slide.querySelector('.mark-review')?.value === '1';
                    
                    if (hasAnswer && !isMarked) {
                        btn.classList.add('question-nav-answered');
                    } else if (isMarked) {
                        btn.classList.add('question-nav-review');
                    }
                }
            });
            
            // Update answered count display
            updateAnsweredCount();
            
            // Check if all questions answered
            checkAllAnswered();
        }

        // Check if all questions are answered
        function checkAllAnswered() {
            let allAnswered = true;
            
            document.querySelectorAll('.question-slide').forEach((slide) => {
                const checkedInput = slide.querySelector('input:checked');
                const textarea = slide.querySelector('textarea');
                
                let hasAnswer = false;
                if (checkedInput) {
                    hasAnswer = true;
                } else if (textarea && textarea.value.trim()) {
                    hasAnswer = true;
                }
                
                if (!hasAnswer) {
                    allAnswered = false;
                }
            });
            
            // Show submit button if all answered
            const submitBtn = document.getElementById('submitBtn');
            
            if (allAnswered) {
                submitBtn.classList.remove('hidden');
            } else {
                submitBtn.classList.add('hidden');
            }
            
            return allAnswered;
        }

        function countRaguQuestions() {
            const markReviewFields = document.querySelectorAll('.mark-review');
            let count = 0;
            markReviewFields.forEach(field => {
                if (field.value === '1') {
                    count++;
                }
            });
            return count;
        }

        function submitExam() {
            // Check for ragu-ragu questions
            const raguCount = countRaguQuestions();
            
            if (raguCount > 0) {
                // Show warning about ragu-ragu questions
                let submitClickedFromWarning = false;
                
                Swal.fire({
                    title: '⚠️ Perhatian!',
                    html: `<p class="text-gray-700">Masih ada <strong>${raguCount}</strong> soal yang ditandai <strong>Ragu-Ragu</strong>.</p><p class="text-sm text-gray-600 mt-3">Apakah Anda yakin ingin mengirim ujian sekarang?</p>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Kirim Ujian',
                    cancelButtonText: 'Batal',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        // Disable the confirm button for 10 seconds
                        const confirmBtn = Swal.getConfirmButton();
                        confirmBtn.disabled = true;
                        confirmBtn.style.opacity = '0.5';
                        confirmBtn.style.cursor = 'not-allowed';
                        
                        let timeLeft = 10;
                        confirmBtn.textContent = `Ya, Kirim Ujian (${timeLeft}s)`;
                        
                        const countdownInterval = setInterval(() => {
                            timeLeft--;
                            confirmBtn.textContent = `Ya, Kirim Ujian (${timeLeft}s)`;
                            
                            if (timeLeft <= 0) {
                                clearInterval(countdownInterval);
                                confirmBtn.disabled = false;
                                confirmBtn.style.opacity = '1';
                                confirmBtn.style.cursor = 'pointer';
                                confirmBtn.textContent = 'Ya, Kirim Ujian';
                            }
                        }, 1000);
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Mengirim ujian...',
                            html: 'Mohon tunggu sebentar',
                            icon: 'info',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Clear violation count before submission
                        sessionStorage.removeItem('examViolationCount_' + attempt_id);
                        document.body.classList.remove('exam-active-fullscreen');
                        // Submit form
                        document.querySelector('#examForm').action = '/student/exams/' + attempt_id + '/submit';
                        document.querySelector('#examForm').method = 'POST';
                        document.querySelector('#examForm').submit();
                    }
                });
            } else {
                // No ragu-ragu questions, show normal confirmation
                Swal.fire({
                    title: 'Kirim Ujian?',
                    html: '<p class="text-gray-700">Anda yakin ingin mengirim ujian?</p><p class="text-red-600 text-sm mt-3">⚠️ Anda tidak dapat mengubah jawaban setelah pengiriman.</p>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Kirim Ujian',
                    cancelButtonText: 'Batal',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Mengirim ujian...',
                            html: 'Mohon tunggu sebentar',
                            icon: 'info',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Clear violation count before submission
                        sessionStorage.removeItem('examViolationCount_' + attempt_id);
                        // Submit form
                        document.querySelector('#examForm').action = '/student/exams/' + attempt_id + '/submit';
                        document.querySelector('#examForm').method = 'POST';
                        document.querySelector('#examForm').submit();
                    }
                });
            }
        }

        function autoSubmit(message) {
            Swal.fire({
                title: 'Ujian Berakhir Otomatis',
                html: '<p class="text-gray-700">' + message + '</p><p class="text-sm mt-3">Ujian Anda akan dikirimkan secara otomatis. Mohon tunggu sebentar.</p>',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonColor: getComputedStyle(document.body).getPropertyValue('--brand-primary').trim() || '#4f46e5',
                confirmButtonText: 'OK'
            }).then(() => {
                // Bersihkan hitungan pelanggaran dari sessionStorage
                sessionStorage.removeItem('examViolationCount_' + attempt_id);

                // Gunakan endpoint force-submit agar tercatat di backend dengan benar
                fetch(`/student/exams/${attempt_id}/force-submit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ reason: message })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        // Fallback ke form submit biasa jika endpoint gagal
                        document.querySelector('#examForm').action = '/student/exams/' + attempt_id + '/submit';
                        document.querySelector('#examForm').method = 'POST';
                        document.querySelector('#examForm').submit();
                    }
                })
                .catch(() => {
                    // Fallback ke form submit biasa jika jaringan bermasalah
                    document.querySelector('#examForm').action = '/student/exams/' + attempt_id + '/submit';
                    document.querySelector('#examForm').method = 'POST';
                    document.querySelector('#examForm').submit();
                });
            });
        }

        // Confetti Animation
        function triggerConfetti() {
            const canvas = document.getElementById('confettiCanvas');
            const ctx = canvas.getContext('2d');
            
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            
            const particles = [];
            
            for (let i = 0; i < 150; i++) {
                particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height - canvas.height,
                    size: Math.random() * 3 + 2,
                    speedX: Math.random() * 6 - 3,
                    speedY: Math.random() * 5 + 2,
                    rotation: Math.random() * 360,
                    rotationSpeed: Math.random() * 10 - 5,
                    color: [
                        getComputedStyle(document.body).getPropertyValue('--brand-primary').trim() || '#4f46e5',
                        getComputedStyle(document.body).getPropertyValue('--brand-dark').trim() || '#312e81',
                        '#ff6b6b', '#4ecdc4', '#f9d56e', '#a8e6cf'
                    ][Math.floor(Math.random() * 6)]
                });
            }
            
            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                particles.forEach(p => {
                    ctx.save();
                    ctx.translate(p.x, p.y);
                    ctx.rotate(p.rotation * Math.PI / 180);
                    ctx.fillStyle = p.color;
                    ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
                    ctx.restore();
                    
                    p.x += p.speedX;
                    p.y += p.speedY;
                    p.rotation += p.rotationSpeed;
                    p.speedY += 0.1; // gravity
                    p.speedX *= 0.99; // air resistance
                });
                
                if (particles.some(p => p.y < canvas.height)) {
                    requestAnimationFrame(animate);
                } else {
                    canvas.style.opacity = '0';
                    setTimeout(() => canvas.remove(), 500);
                }
            }
            
            animate();
        }
    </script>
@endpush
@endsection

@push('body-overlays')
    <!-- Exam Readiness Gate - Block content until fullscreen -->
    <div id="readinessOverlay">
      <div class="bg-white rounded-2xl p-6 md:p-8 max-w-2xl w-full mx-4 shadow-2xl relative" style="animation: slideUp 0.4s ease-out">
        <div class="absolute -top-12 left-1/2 -translate-x-1/2 bg-[var(--brand-primary)] w-24 h-24 rounded-full border-4 border-white shadow-lg flex items-center justify-center">
          <i class="fas fa-shield-alt text-white text-4xl"></i>
        </div>

        <div class="mt-8 text-center">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Konfirmasi Pengerjaan Ujian</h2>
          <p class="text-gray-500 mb-6">Ujian akan dimulai dalam mode layar penuh untuk menjaga integritas ujian.</p>
        </div>

        <div class="bg-gray-50 rounded-xl p-5 mb-6 border border-gray-100">
          <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-clipboard-list text-[var(--brand-primary)]"></i> TATA TERTIB UJIAN
          </h3>
          <ul class="space-y-3 text-sm text-gray-600">
            <li class="flex items-start gap-3"><i class="fas fa-expand text-indigo-500 mt-1"></i> Mode Layar Penuh Wajib — ujian hanya bisa dikerjakan dalam fullscreen</li>
            <li class="flex items-start gap-3"><i class="fas fa-eye text-emerald-500 mt-1"></i> Fokus pada Ujian — dilarang pindah tab atau jendela</li>
            <li class="flex items-start gap-3"><i class="fas fa-copy text-rose-500 mt-1"></i> Dilarang Menyalin — fungsi copy-paste dinonaktifkan</li>
            <li class="flex items-start gap-3"><i class="fas fa-code text-amber-500 mt-1"></i> Dilarang Inspect Element — developer tools tidak diizinkan</li>
            <li class="flex items-start gap-3"><i class="fas fa-hand-paper text-orange-500 mt-1"></i> Batasan Pelanggaran — {{ $configs['max_violations'] ?? 3 }} pelanggaran akan otomatis submit</li>
            <li class="flex items-start gap-3"><i class="fas fa-hourglass-half text-blue-500 mt-1"></i> Waktu Terbatas — ujian otomatis submit jika waktu habis</li>
          </ul>
        </div>

        <div class="bg-amber-50 rounded-lg p-4 mb-8 border border-amber-200 flex gap-3 text-amber-800 text-sm">
          <i class="fas fa-exclamation-triangle mt-0.5"></i>
          <div><strong class="font-bold">Peringatan:</strong> Setiap pelanggaran dicatat di sistem dan dapat mempengaruhi nilai ujian Anda.</div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
          <button id="startExamBtn" class="flex-1 bg-[var(--brand-primary)] hover:bg-[var(--brand-dark)] text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-[var(--brand-glow)] transition-all flex justify-center items-center gap-2">
            <i class="fas fa-play"></i> SIAP, MULAI UJIAN
          </button>
          <button id="cancelExamBtn" onclick="window.history.back()" class="flex-1 bg-white hover:bg-gray-50 text-gray-700 font-bold py-3 px-6 rounded-xl border border-gray-200 transition-all flex justify-center items-center gap-2">
            <i class="fas fa-times"></i> Kembali
          </button>
        </div>
      </div>
    </div>

    <!-- Anti-Cheating: Fullscreen Exit Overlay - Complete Screen Block -->
    <div id="fullscreenOverlay">
      <div class="max-w-lg w-full mx-4 rounded-3xl p-8" style="animation: slideDown 0.4s ease-out; background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(12px); border: 2px solid #ef4444; box-shadow: 0 0 40px rgba(239, 68, 68, 0.2);">
        <div class="text-7xl mb-4 animate-bounce">🛑</div>
        <h2 class="text-3xl font-black text-rose-500 mb-2 tracking-tight">MODE UJIAN TIDAK AKTIF</h2>
        <p class="text-xl text-white font-medium mb-6">Anda telah keluar dari mode layar penuh!</p>
        <p class="text-gray-300 text-sm leading-relaxed mb-8">Ujian hanya dapat dikerjakan dalam mode layar penuh (Fullscreen) agar integritas ujian terjaga.</p>
        
        <div class="bg-rose-500/10 rounded-xl p-4 mb-8">
          <p class="text-rose-400 font-semibold text-sm">Klik tombol di bawah untuk kembali ke mode ujian:</p>
        </div>

        <button id="returnFullscreenBtn" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-bold py-4 px-8 rounded-xl shadow-lg shadow-rose-500/30 transition-all transform hover:scale-105 active:scale-95 flex items-center justify-center gap-3">
          <i class="fas fa-expand text-xl"></i>
          KEMBALI KE MODE UJIAN
        </button>
      </div>
    </div>
@endpush
