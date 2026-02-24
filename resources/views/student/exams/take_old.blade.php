@extends('layouts.app')

@section('title', 'Mengerjakan Ujian - SesekaliCBT')

@section('page-title', $attempt->exam->title)

@section('styles')
    <style>
        .timer-expired {
            color: #dc2626;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .question-nav-active {
            background-color: #2563eb !important;
            color: white !important;
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
    </style>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 md:gap-6">
        <!-- Main Exam Area -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Exam Header with Timer -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4 md:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="w-full sm:flex-1">
                        <h1 class="text-xl md:text-2xl font-bold break-words">{{ $attempt->exam->title }}</h1>
                        <p class="text-blue-100 text-sm">{{ $attempt->exam->subject->name }}</p>
                    </div>
                    <div class="text-center sm:text-right w-full sm:w-auto">
                        <p class="text-xs sm:text-sm text-blue-100 mb-1">Waktu Tersisa</p>
                        <div id="timer" class="text-2xl sm:text-3xl font-bold font-mono timer-display">
                            {{ sprintf('%02d:%02d', intval($remainingMinutes), 0) }}
                        </div>
                        <p class="text-xs text-blue-100 mt-1" id="timerStatus"></p>
                    </div>
                </div>

                <!-- Question Display -->
                <div class="p-4 md:p-8 min-h-96">
                    <form id="examForm">
                        @csrf
                        
                        <!-- Current Question Number -->
                        <div class="mb-6">
                            <span class="text-xs sm:text-sm font-semibold text-gray-600">
                                Soal <span id="currentQNum">1</span> dari {{ $questions->count() }}
                            </span>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all" style="width: 0%"></div>
                            </div>
                        </div>

                        <!-- Question Container -->
                        <div id="questionContainer">
                            @foreach($questions as $index => $question)
                                <div class="question-slide" data-question-id="{{ $question->id }}" data-question-index="{{ $index }}" style="{{ $index === 0 ? '' : 'display: none;' }}">
                                    <!-- Question Text -->
                                    <div class="mb-8">
                                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ $question->question_text }}</h2>
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
                                                $options = array_filter($options); // Remove null options
                                            @endphp
                                            
                                            @foreach($options as $key => $option)
                                                <label class="flex items-start p-3 md:p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 transition text-sm md:text-base">
                                                    <input type="radio" 
                                                        name="answer_{{ $question->id }}" 
                                                        value="{{ $key }}" 
                                                        class="mt-1 mr-3 question-answer"
                                                        data-question-id="{{ $question->id }}"
                                                        {{ $current_answer === $key ? 'checked' : '' }}>
                                                    <span class="text-gray-800">{{ $option }}</span>
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
                                                class="w-full p-3 md:p-4 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none question-answer text-sm md:text-base" 
                                                rows="6" 
                                                placeholder="Ketik jawaban anda di sini..."
                                                data-question-id="{{ $question->id }}">{{ $current_essay }}</textarea>
                                        @endif
                                    </div>

                                    <!-- Mark for Review Checkbox -->
                                    <div class="mt-6 pt-6 border-t border-gray-200">
                                        <label class="flex items-center gap-3 cursor-pointer text-sm md:text-base">
                                            <input type="checkbox" class="w-4 h-4 mark-review" data-question-id="{{ $question->id }}">
                                            <span class="text-gray-700">Tandai untuk review</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="mt-8 pt-8 border-t border-gray-200 flex flex-col sm:flex-row justify-between gap-2 sm:gap-4">
                            <button type="button" id="prevBtn" class="px-4 sm:px-6 py-2 bg-gray-300 text-gray-900 rounded-lg font-semibold hover:bg-gray-400 transition disabled:opacity-50 text-sm sm:text-base order-2 sm:order-1">
                                <i class="fas fa-chevron-left mr-2"></i>Sebelumnya
                            </button>
                            
                            <div class="flex gap-2 flex-wrap justify-end sm:justify-center order-1 sm:order-2">
                                <button type="button" id="nextBtn" class="flex-1 sm:flex-none px-4 sm:px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition text-sm sm:text-base">
                                    Lanjut<i class="fas fa-chevron-right ml-2"></i>
                                </button>
                                <button type="button" id="submitBtn" class="flex-1 sm:flex-none px-4 sm:px-6 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition hidden text-sm sm:text-base">
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
            <div class="bg-white rounded-lg shadow-md p-4 md:p-6 mb-6">
                <h3 class="font-bold text-gray-900 mb-4 text-sm md:text-base">Informasi Ujian</h3>
                <div class="space-y-3 text-xs md:text-sm">
                    <div>
                        <p class="text-gray-600">Durasi</p>
                        <p class="font-semibold">{{ $attempt->exam->duration_minutes }} menit</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Total Soal</p>
                        <p class="font-semibold">{{ $questions->count() }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Dimulai Pada</p>
                        <p class="font-semibold text-xs">{{ $attempt->started_at->format('H:i:s') }}</p>
                    </div>
                </div>
            </div>

            <!-- Question Navigator -->
            <div class="bg-white rounded-lg shadow-md p-4 md:p-6">
                <h3 class="font-bold text-gray-900 mb-4 text-sm md:text-base">Navigasi Soal</h3>
                <div class="grid grid-cols-5 gap-1 md:gap-2" id="questionNav">
                    @foreach($questions as $index => $question)
                        <button type="button" 
                            class="question-nav-btn w-full py-2 rounded-lg font-semibold text-xs md:text-sm border-2 transition"
                            data-question-index="{{ $index }}"
                            style="{{ $index === 0 ? 'border-color: #2563eb;' : 'border-color: #e5e7eb;' }}">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>

                <!-- Legend -->
                <div class="mt-6 space-y-2 text-xs">
                    <div class="flex gap-2 items-center">
                        <div class="w-3 h-3 bg-blue-600 rounded"></div>
                        <span class="text-gray-700">Sedang Dikerjakan</span>
                    </div>
                    <div class="flex gap-2 items-center">
                        <div class="w-3 h-3 bg-green-100 border-2 border-green-800 rounded"></div>
                        <span class="text-gray-700">Sudah Dijawab</span>
                    </div>
                    <div class="flex gap-2 items-center">
                        <div class="w-3 h-3 bg-yellow-100 border-2 border-yellow-800 rounded"></div>
                        <span class="text-gray-700">Ditandai Review</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const attempt_id = {{ $attempt->id }};
        const exam_id = {{ $attempt->exam->id }};
        const total_questions = {{ $questions->count() }};
        const remaining_minutes = {{ $remainingMinutes }};
        let current_question = 0;
        let answers = {};

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            initTimer();
            initEventListeners();
            updateQuestionNav();
        });

        // Timer Management
        function initTimer() {
            let totalSeconds = remaining_minutes * 60;
            
            const timerInterval = setInterval(() => {
                totalSeconds--;
                
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;
                const display = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                
                const timerEl = document.getElementById('timer');
                timerEl.textContent = display;
                
                // Color change warnings
                if (minutes === 5) {
                    timerEl.style.color = '#fbbf24'; // Yellow
                } else if (minutes === 1) {
                    timerEl.style.color = '#dc2626'; // Red
                    timerEl.classList.add('timer-expired');
                }
                
                if (totalSeconds <= 0) {
                    clearInterval(timerInterval);
                    autoSubmit('Time expired. Auto-submitting your exam...');
                }
            }, 1000);
        }

        // Event Listeners
        function initEventListeners() {
            // Question answers
            document.querySelectorAll('.question-answer').forEach(el => {
                el.addEventListener('change', function() {
                    autosaveAnswer(this.dataset.questionId);
                    updateQuestionNav();
                });
            });

            // Navigation buttons
            document.getElementById('prevBtn').addEventListener('click', previousQuestion);
            document.getElementById('nextBtn').addEventListener('click', nextQuestion);
            document.getElementById('submitBtn').addEventListener('click', submitExam);

            // Question navigator
            document.querySelectorAll('.question-nav-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    goToQuestion(parseInt(this.dataset.questionIndex));
                });
            });

            // Preview submit confirmation
            window.addEventListener('beforeunload', function(e) {
                if (current_question < total_questions) {
                    e.preventDefault();
                    e.returnValue = 'Your exam is still in progress. Are you sure you want to leave?';
                }
            });
        }

        // Question Navigation
        function showQuestion(index) {
            document.querySelectorAll('.question-slide').forEach(q => q.style.display = 'none');
            document.querySelectorAll('.question-slide')[index].style.display = 'block';
            
            current_question = index;
            document.getElementById('currentQNum').textContent = index + 1;
            document.getElementById('progressBar').style.width = ((index + 1) / total_questions * 100) + '%';
            
            // Update button states
            document.getElementById('prevBtn').disabled = index === 0;
            
            updateQuestionNav();
        }

        function nextQuestion() {
            if (current_question < total_questions - 1) {
                showQuestion(current_question + 1);
            }
        }

        function previousQuestion() {
            if (current_question > 0) {
                showQuestion(current_question - 1);
            }
        }

        function goToQuestion(index) {
            showQuestion(index);
        }

        // Autosave
        function autosaveAnswer(questionId) {
            const slide = document.querySelector(`[data-question-id="${questionId}"]`);
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
                if (textarea.value) {
                    data.essay_answer = textarea.value;
                }
            }

            fetch(`/student/exams/${exam_id}/autosave`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                // Answer saved silently
                if (data.success) {
                    // No need for console logging
                }
            })
            .catch(error => {
                console.error('Error saving answer:', error);
            });
        }

        // Update Question Navigator
        function updateQuestionNav() {
            document.querySelectorAll('.question-nav-btn').forEach((btn, index) => {
                const qId = document.querySelectorAll('.question-slide')[index].dataset.questionId;
                const slide = document.querySelector(`[data-question-id="${qId}"]`);
                
                if (!slide) return; // Skip if slide doesn't exist
                
                // Reset button - remove all classes and styles
                btn.classList.remove('question-nav-active', 'question-nav-answered', 'question-nav-review');
                btn.style.borderColor = '';
                btn.style.color = '';
                btn.style.backgroundColor = '';
                
                if (index === current_question) {
                    btn.classList.add('question-nav-active');
                } else {
                    // Check for answer - handle both radio buttons and textarea
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
                    
                    const isMarked = slide.querySelector('.mark-review:checked');
                    
                    if (hasAnswer && !isMarked) {
                        btn.classList.add('question-nav-answered');
                    } else if (isMarked) {
                        btn.classList.add('question-nav-review');
                    }
                }
            });
            
            // Check if all questions answered
            checkAllAnswered();
        }

        // Check if all questions are answered
        function checkAllAnswered() {
            let allAnswered = true;
            let answeredCount = 0;
            
            document.querySelectorAll('.question-slide').forEach((slide) => {
                const checkedInput = slide.querySelector('input:checked');
                const textarea = slide.querySelector('textarea');
                
                let hasAnswer = false;
                if (checkedInput) {
                    hasAnswer = true;
                    answeredCount++;
                } else if (textarea && textarea.value.trim()) {
                    hasAnswer = true;
                    answeredCount++;
                }
                
                if (!hasAnswer) {
                    allAnswered = false;
                }
            });
            
            // Show submit button if all answered or time is about to expire
            const submitBtn = document.getElementById('submitBtn');
            const remainingMinutes = parseInt(document.getElementById('timer').textContent.split(':')[0]);
            
            if (allAnswered || remainingMinutes === 0) {
                submitBtn.classList.remove('hidden');
            } else {
                submitBtn.classList.add('hidden');
            }
            
            return allAnswered;
        }

        function submitExam() {
            if (confirm('Anda yakin ingin mengirim ujian? Anda tidak dapat mengubah jawaban setelah pengiriman.')) {
                document.querySelector('#examForm').action = '/student/exams/' + attempt_id + '/submit';
                document.querySelector('#examForm').method = 'POST';
                document.querySelector('#examForm').submit();
            }
        }

        function autoSubmit(message) {
            alert(message);
            document.querySelector('#examForm').action = '/student/exams/' + attempt_id + '/submit';
            document.querySelector('#examForm').method = 'POST';
            document.querySelector('#examForm').submit();
        }
    </script>
@endsection
