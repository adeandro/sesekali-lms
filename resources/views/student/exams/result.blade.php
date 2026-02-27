@extends('layouts.app')

@section('title', 'Hasil Ujian - SesekaliCBT')

@section('page-title', 'Hasil Ujian')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .score-badge {
            font-size: 3rem;
            font-weight: bold;
        }

        .score-excellent {
            color: #10b981;
        }

        .score-good {
            color: #3b82f6;
        }

        .score-fair {
            color: #f59e0b;
        }

        .score-poor {
            color: #ef4444;
        }

        .answer-correct {
            @apply border-l-4 border-green-500 bg-green-50 p-4 rounded;
        }

        .answer-incorrect {
            @apply border-l-4 border-red-500 bg-red-50 p-4 rounded;
        }

        .answer-unanswered {
            @apply border-l-4 border-gray-400 bg-gray-50 p-4 rounded;
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
    </style>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Result Area -->
        <div class="lg:col-span-2">
            @if($attempt->exam->show_score_after_submit)
            <!-- Score Card -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-8">
                    <h1 class="text-2xl font-bold mb-4">{{ $attempt->exam->title }}</h1>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-blue-100 text-sm mb-2">Nilai Anda</p>
                            <div class="text-5xl font-bold">
                                {{ intval($attempt->final_score) }}
                            </div>
                        </div>
                        <div>
                            <p class="text-blue-100 text-sm mb-2">Dikerjakan</p>
                            <p class="text-2xl font-semibold">{{ $attempt->submitted_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="p-8 border-t border-gray-200">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <p class="text-gray-600 text-sm">Total Soal</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $questions->count() }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-600 text-sm">Benar</p>
                            <p class="text-2xl font-bold text-green-600">{{ $correct_count }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-600 text-sm">Salah</p>
                            <p class="text-2xl font-bold text-red-600">{{ $incorrect_count }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-600 text-sm">Tidak Dijawab</p>
                            <p class="text-2xl font-bold text-gray-600">{{ $unanswered_count }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Score Not Available Notice -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-gray-600 to-gray-700 text-white p-8">
                    <h1 class="text-2xl font-bold mb-4">{{ $attempt->exam->title }}</h1>
                    <p class="text-gray-100">Ujian berhasil dikumpulkan</p>
                </div>

                <!-- Notice -->
                <div class="p-8 border-t border-gray-200 bg-blue-50">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-blue-600 mt-0.5 mr-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-blue-900 font-semibold">Nilai tidak ditampilkan</p>
                            <p class="text-blue-800 text-sm mt-1">Pengajar belum memperbolehkan menampilkan nilai untuk ujian ini. Silakan hubungi pengajar untuk informasi lebih lanjut.</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Answer Review -->
            @if($attempt->exam->allow_review_results)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-gray-900 text-white p-6">
                    <h2 class="text-xl font-bold">Review Jawaban</h2>
                    <p class="text-gray-400 text-sm mt-1">Rincian jawaban Anda secara mendetail</p>
                </div>

                <div class="p-6 space-y-6">
                    @foreach($questions as $index => $question)
                        @php
                            $answer = $answers->firstWhere('question_id', $question->id);
                            $isCorrect = $answer?->is_correct;
                            $answerClass = $isCorrect === true 
                                ? 'answer-correct' 
                                : ($isCorrect === false ? 'answer-incorrect' : 'answer-unanswered');
                            
                            // Use nav_position if available, otherwise use display_index
                            $questionNumber = isset($question->nav_position) ? $question->nav_position : ($index + 1);
                        @endphp

                        <div class="{{ $answerClass }}">
                            <!-- Question Header -->
                            <div class="flex items-start justify-between mb-4 flex-wrap gap-2">
                                <div>
                                    <h3 class="font-bold text-lg text-gray-900">
                                        Soal {{ $questionNumber }}
                                        <span class="ml-3 inline-block px-3 py-1 text-xs font-semibold rounded-full {{ $question->question_type === 'multiple_choice' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ $question->question_type === 'multiple_choice' ? 'Pilihan Ganda' : 'Essay' }}
                                        </span>
                                    </h3>
                                </div>
                                <div class="text-right">
                                    @if($isCorrect === true)
                                        <i class="fas fa-check-circle text-2xl text-green-600 mb-2"></i>
                                        <p class="text-sm font-semibold text-green-700">Benar</p>
                                    @elseif($isCorrect === false)
                                        <i class="fas fa-times-circle text-2xl text-red-600 mb-2"></i>
                                        <p class="text-sm font-semibold text-red-700">Salah</p>
                                    @else
                                        <i class="fas fa-circle text-2xl text-gray-400 mb-2"></i>
                                        <p class="text-sm font-semibold text-gray-700">Tidak Dijawab</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Question Text -->
                            <div class="mb-4">
                                <p class="text-gray-900 font-semibold mb-2">Soal:</p>
                                <p class="text-gray-800">{{ $question->question_text }}</p>
                            </div>

                            <!-- Answer Comparison -->
                            @if($question->question_type === 'multiple_choice')
                                @php
                                    // NO SHUFFLING HERE - Just use stored answer text
                                    $studentAnswer = $answer?->selected_answer;
                                    $studentAnswerText = $answer?->selected_answer_text;  // Use stored text from submission
                                    $correctAnswerText = $answer?->correct_answer_text;   // Use stored correct text
                                    
                                    // Fallback: reconstruct from options if stored text is empty
                                    $options = [
                                        'a' => $question->option_a,
                                        'b' => $question->option_b,
                                        'c' => $question->option_c,
                                        'd' => $question->option_d,
                                        'e' => $question->option_e,
                                    ];
                                    $options = array_filter($options);
                                    
                                    // Fallback for student answer text if not stored
                                    if (!$studentAnswerText && $studentAnswer) {
                                        $studentAnswerText = $options[strtolower($studentAnswer)] ?? null;
                                    }
                                    
                                    // Fallback for correct answer text if not stored
                                    if (!$correctAnswerText) {
                                        $correctAnswerText = $options[strtolower($question->correct_answer)] ?? null;
                                    }
                                @endphp

                                <div class="space-y-3">
                                    <!-- Student's Answer -->
                                    @if($studentAnswer || $studentAnswerText)
                                        <div>
                                            <p class="text-sm font-semibold text-gray-700 mb-2">Jawaban Anda:</p>
                                            <div class="bg-white p-3 rounded border-l-4 {{ $isCorrect ? 'border-green-500' : 'border-red-500' }}">
                                                <p class="text-gray-900 font-semibold flex items-center">
                                                    {{ $studentAnswerText ?? 'Opsi Tidak Dikenal' }}
                                                    @if($isCorrect)
                                                        <i class="fas fa-check text-green-600 ml-2"></i>
                                                    @else
                                                        <i class="fas fa-times text-red-600 ml-2"></i>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <div>
                                            <p class="text-sm font-semibold text-gray-700 mb-2">Jawaban Anda:</p>
                                            <div class="bg-white p-3 rounded border-l-4 border-gray-400">
                                                <p class="text-gray-600 italic">Tidak ada jawaban</p>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Correct Answer - Use stored text, never re-shuffle -->
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700 mb-2">Jawaban Benar:</p>
                                        <div class="bg-white p-3 rounded border-l-4 border-green-500">
                                            <p class="text-gray-900 font-semibold flex items-center">
                                                {{ $correctAnswerText ?? 'Opsi Tidak Dikenal' }}
                                                <i class="fas fa-check-circle text-green-600 ml-2"></i>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Essay Question -->
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700 mb-2">Jawaban Anda:</p>
                                        <div class="bg-white p-4 rounded border-l-4 border-gray-400 whitespace-pre-wrap text-gray-800">
                                            {{ $answer?->essay_answer ?? '(Tidak ada jawaban)' }}
                                        </div>
                                    </div>
                                    <div class="bg-blue-50 p-4 rounded border-l-4 border-blue-400">
                                        <p class="text-sm text-blue-800">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Pertanyaan essay memerlukan penilaian manual oleh instruktur.
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar: Summary -->
        <div class="lg:col-span-1">
            @if($attempt->exam->show_score_after_submit)
            <!-- Score Summary -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-6 text-center">
                <div class="mb-4">
                    <div class="score-badge 
                        @if(intval($attempt->final_score) >= 80) score-excellent
                        @elseif(intval($attempt->final_score) >= 65) score-good
                        @elseif(intval($attempt->final_score) >= 50) score-fair
                        @else score-poor @endif">
                        {{ intval($attempt->final_score) }}
                    </div>
                </div>

                <p class="text-gray-600 text-sm mb-4">Nilai Akhir Anda</p>

                <!-- Grade Badge -->
                @php
                    $score = intval($attempt->final_score);
                    if ($score >= 85) {
                        $grade = 'A';
                        $grade_class = 'bg-green-100 text-green-800';
                    } elseif ($score >= 75) {
                        $grade = 'B';
                        $grade_class = 'bg-blue-100 text-blue-800';
                    } elseif ($score >= 65) {
                        $grade = 'C';
                        $grade_class = 'bg-yellow-100 text-yellow-800';
                    } elseif ($score >= 50) {
                        $grade = 'D';
                        $grade_class = 'bg-orange-100 text-orange-800';
                    } else {
                        $grade = 'F';
                        $grade_class = 'bg-red-100 text-red-800';
                    }
                @endphp

                <div class="inline-block px-6 py-2 rounded-full font-bold text-lg {{ $grade_class }}">
                    Nilai: {{ $grade }}
                </div>
            </div>

            <!-- Performance Breakdown -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="font-bold text-gray-900 mb-4">Performa</h3>
                
                <div class="space-y-4">
                    <!-- Correct -->
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-semibold text-green-700">Benar</span>
                            <span class="text-sm font-bold text-green-700">{{ $correct_count }}/{{ $questions->count() }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($correct_count / $questions->count()) * 100 }}%"></div>
                        </div>
                    </div>

                    <!-- Incorrect -->
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-semibold text-red-700">Salah</span>
                            <span class="text-sm font-bold text-red-700">{{ $incorrect_count }}/{{ $questions->count() }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-600 h-2 rounded-full" style="width: {{ ($incorrect_count / $questions->count()) * 100 }}%"></div>
                        </div>
                    </div>

                    <!-- Unanswered -->
                    @if($unanswered_count > 0)
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-sm font-semibold text-gray-700">Tidak Dijawab</span>
                                <span class="text-sm font-bold text-gray-700">{{ $unanswered_count }}/{{ $questions->count() }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gray-400 h-2 rounded-full" style="width: {{ ($unanswered_count / $questions->count()) * 100 }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Exam Info -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="font-bold text-gray-900 mb-4">Informasi Ujian</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-600">Mata Pelajaran</p>
                        <p class="font-semibold text-gray-900">{{ $attempt->exam->subject->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Durasi</p>
                        <p class="font-semibold text-gray-900">{{ $attempt->exam->duration_minutes }} menit</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Dimulai</p>
                        <p class="font-semibold text-gray-900 text-xs">{{ $attempt->started_at->format('d M Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Dikerjakan</p>
                        <p class="font-semibold text-gray-900 text-xs">{{ $attempt->submitted_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 space-y-3">
                <a href="{{ route('student.exams.index') }}" class="block w-full text-center px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Ujian
                </a>
                <a href="{{ route('dashboard') }}" class="block w-full text-center px-6 py-3 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition">
                    <i class="fas fa-home mr-2"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        // Trigger confetti and success modal on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Always show confetti and success modal on first page load
            // Use a small delay to ensure DOM is fully ready
            setTimeout(function() {
                // Trigger confetti
                triggerConfetti();
                
                // Show success modal
                Swal.fire({
                    title: '🎉 Terima Kasih!',
                    html: '<p class="text-lg font-semibold text-gray-800">Anda telah menyelesaikan ujian</p><p class="text-gray-600 mt-2">Ujian Anda telah berhasil dikirimkan dan sedang diproses</p>',
                    icon: 'success',
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'Lanjutkan',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });
            }, 300);
        });

        // Confetti Animation Function
        function triggerConfetti() {
            const canvas = document.createElement('canvas');
            canvas.id = 'confettiCanvas';
            document.body.appendChild(canvas);
            
            const ctx = canvas.getContext('2d');
            
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            canvas.style.transition = 'opacity 0.5s ease-out';
            
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
                    color: ['#ff6b6b', '#4ecdc4', '#45b7d1', '#f9d56e', '#f8b500', '#ff9e7d', '#a8e6cf', '#ffd3b6'][Math.floor(Math.random() * 8)]
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
@endsection
