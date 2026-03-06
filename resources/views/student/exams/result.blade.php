@extends('layouts.app')

@section('title', 'Hasil Ujian - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('content')
<div class="space-y-10 pb-20">
    <!-- Top Nav / Back -->
    <div class="flex items-center justify-between px-2">
        <a href="{{ route('student.results') }}" class="group flex items-center gap-3 text-gray-500 hover:text-indigo-600 transition-colors">
            <div class="w-10 h-10 rounded-xl bg-white border border-gray-100 flex items-center justify-center shadow-sm group-hover:border-indigo-100 group-hover:bg-indigo-50 transition-all">
                <i class="fas fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform"></i>
            </div>
            <span class="text-[10px] font-black uppercase tracking-widest">Riwayat Hasil</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Sesi Terarsip & Tervalidasi</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Main Result Area -->
        <div class="lg:col-span-2 space-y-10">
            @if($attempt->exam->show_score_after_submit)
                <!-- Score Header Card -->
                <div class="group relative bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900 rounded-[3rem] p-10 md:p-14 text-white shadow-2xl shadow-indigo-200 overflow-hidden">
                    <!-- Abstract Decoration -->
                    <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 bg-indigo-400/20 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative flex flex-col md:flex-row items-center justify-between gap-12">
                        <div class="space-y-6 text-center md:text-left">
                            <div class="space-y-2">
                                <p class="text-indigo-100 font-black uppercase tracking-[0.4em] text-[10px] opacity-80 italic">Pencapaian Akademik</p>
                                <h1 class="text-3xl md:text-5xl font-black leading-tight uppercase tracking-wider">{{ $attempt->exam->title }}</h1>
                            </div>
                            
                            <div class="flex flex-wrap justify-center md:justify-start gap-4">
                                <div class="px-5 py-2 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20">
                                    <p class="text-[8px] font-black text-indigo-200 uppercase tracking-widest mb-1">Dikerjakan Pada</p>
                                    <p class="text-xs font-black uppercase tracking-wider">{{ $attempt->submitted_at->format('d M Y - H:i') }}</p>
                                </div>
                                <div class="px-5 py-2 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20">
                                    <p class="text-[8px] font-black text-indigo-200 uppercase tracking-widest mb-1">Durasi Sesi</p>
                                    <p class="text-xs font-black uppercase tracking-wider">{{ $attempt->started_at->diffInMinutes($attempt->submitted_at) }} Menit</p>
                                </div>
                            </div>
                        </div>

                        <div class="relative text-center">
                            <div class="relative inline-block group">
                                <div class="absolute -inset-4 bg-white/20 rounded-full blur opacity-30 group-hover:opacity-60 transition duration-1000"></div>
                                <div class="relative w-40 h-40 md:w-52 md:h-52 bg-white/15 backdrop-blur-2xl border-4 border-white/30 rounded-full flex flex-col items-center justify-center shadow-2xl">
                                    <p class="text-[10px] md:text-xs font-black text-indigo-100 uppercase tracking-[0.3em] mb-1">Skor Akhir</p>
                                    <span class="text-6xl md:text-8xl font-black tracking-tighter">{{ intval($attempt->final_score) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-[2.5rem] p-7 border border-gray-100 shadow-sm text-center space-y-1">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Total Soal</p>
                        <p class="text-2xl font-black text-gray-900">{{ $questions->count() }}</p>
                    </div>
                    <div class="bg-emerald-50 rounded-[2.5rem] p-7 border border-emerald-100 shadow-sm text-center space-y-1">
                        <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">Jawaban Benar</p>
                        <p class="text-2xl font-black text-emerald-600">{{ $correct_count }}</p>
                    </div>
                    <div class="bg-rose-50 rounded-[2.5rem] p-7 border border-rose-100 shadow-sm text-center space-y-1">
                        <p class="text-[9px] font-black text-rose-600 uppercase tracking-widest">Jawaban Salah</p>
                        <p class="text-2xl font-black text-rose-600">{{ $incorrect_count }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-[2.5rem] p-7 border border-gray-100 shadow-sm text-center space-y-1">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Dilewati</p>
                        <p class="text-2xl font-black text-gray-600">{{ $unanswered_count }}</p>
                    </div>
                </div>
            @else
                <!-- Score Hidden Placeholder -->
                <div class="bg-white rounded-[3rem] p-12 border border-gray-100 shadow-sm text-center space-y-8">
                    <div class="w-24 h-24 bg-indigo-50 rounded-[2rem] flex items-center justify-center mx-auto text-indigo-600 text-4xl">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="max-w-md mx-auto space-y-4">
                        <h2 class="text-2xl font-black text-gray-900 uppercase tracking-wider">{{ $attempt->exam->title }}</h2>
                        <div class="bg-blue-50/50 p-6 rounded-[2rem] border border-blue-100">
                             <p class="text-xs font-bold text-blue-800 leading-relaxed italic">Ujian Anda telah berhasil dikirimkan. Pengajar telah mengatur agar nilai tidak ditampilkan secara instan. Silakan hubungi pengajar Anda untuk informasi lebih lanjut.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Answer Review Section -->
            @if($attempt->exam->allow_review_results)
                <div class="space-y-6">
                    <div class="flex items-center justify-between px-2">
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-wider flex items-center gap-3">
                            <span class="w-2 h-8 bg-indigo-600 rounded-full"></span>
                            Review Jawaban
                        </h3>
                    </div>

                    <div class="space-y-8">
                        @foreach($questions as $index => $question)
                            @php
                                $answer = $answers->firstWhere('question_id', $question->id);
                                $isCorrect = $answer?->is_correct;
                                $questionNumber = isset($question->nav_position) ? $question->nav_position : ($index + 1);
                                
                                $statusClass = $isCorrect === true ? 'border-emerald-100 bg-emerald-50/20' : ($isCorrect === false ? 'border-rose-100 bg-rose-50/20' : 'border-gray-100 bg-gray-50/20');
                                $iconClass = $isCorrect === true ? 'bg-emerald-500 text-white' : ($isCorrect === false ? 'bg-rose-500 text-white' : 'bg-gray-400 text-white');
                            @endphp

                            <div class="group bg-white rounded-[2.5rem] border {{ $statusClass }} shadow-sm overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-gray-100">
                                <div class="p-8 space-y-8">
                                    <!-- Q Header -->
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 {{ $iconClass }} rounded-2xl flex items-center justify-center font-black text-lg shadow-lg">
                                                {{ $questionNumber }}
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Pertanyaan {{ $question->question_type === 'multiple_choice' ? 'PG' : 'Esai' }}</p>
                                                <p class="text-[11px] font-black @if($isCorrect === true) text-emerald-600 @elseif($isCorrect === false) text-rose-600 @else text-gray-500 @endif uppercase tracking-wider">
                                                    @if($isCorrect === true) Jawaban Benar @elseif($isCorrect === false) Jawaban Salah @else Tidak Dijawab @endif
                                                </p>
                                            </div>
                                        </div>
                                        
                                        @if($isCorrect === true)
                                            <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        @elseif($isCorrect === false)
                                            <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center">
                                                <i class="fas fa-times"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Question Text -->
                                    <div class="bg-gray-50/50 p-6 rounded-[2rem] border border-gray-100">
                                        <p class="text-sm font-bold text-gray-800 leading-relaxed">{{ $question->question_text }}</p>
                                    </div>

                                    <!-- Option Comparison -->
                                    @if($question->question_type === 'multiple_choice')
                                        @php
                                            $studentAnswerText = $answer?->selected_answer_text;
                                            $correctAnswerText = $answer?->correct_answer_text;
                                        @endphp

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <!-- Your Answer -->
                                            <div class="space-y-3">
                                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest pl-2">Jawaban Anda</p>
                                                <div class="p-5 rounded-2xl border @if($isCorrect) border-emerald-100 bg-emerald-50 text-emerald-800 @else border-rose-100 bg-rose-50 text-rose-800 @endif flex items-center gap-4">
                                                     <p class="text-xs font-black uppercase tracking-wider leading-tight">
                                                         @if($studentAnswerText)
                                                            {{ $studentAnswerText }}
                                                         @else
                                                            <span class="italic font-bold opacity-60">Tidak Menjawab</span>
                                                         @endif
                                                     </p>
                                                </div>
                                            </div>
                                            <!-- Correct Answer -->
                                            <div class="space-y-3">
                                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest pl-2">Kunci Jawaban</p>
                                                <div class="p-5 rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-800 flex items-center gap-4">
                                                     <p class="text-xs font-black uppercase tracking-wider leading-tight">{{ $correctAnswerText }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Essay View -->
                                        <div class="space-y-6">
                                            <div class="space-y-3">
                                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest pl-2">Jawaban Esai Anda</p>
                                                <div class="p-8 rounded-[2rem] border border-gray-100 bg-gray-50 text-gray-800 italic leading-relaxed text-sm whitespace-pre-wrap">
                                                    {{ $answer?->essay_answer ?? '(Tidak ada jawaban)' }}
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-4 px-6 py-4 bg-indigo-50 rounded-2xl border border-indigo-100">
                                                <i class="fas fa-info-circle text-indigo-500"></i>
                                                <p class="text-[10px] font-black text-indigo-700 uppercase tracking-widest italic">Penilaian Esai dilakukan secara manual oleh pengajar.</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar Summary Area -->
        <div class="lg:col-span-1 space-y-10">
            <!-- Grade Badge Card -->
            @if($attempt->exam->show_score_after_submit)
                <div class="bg-white rounded-[3rem] p-10 border border-gray-100 shadow-sm text-center relative overflow-hidden group">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>

                    @php
                        $score = intval($attempt->final_score);
                        if ($score >= 85) { $grade = 'A'; $gradeC = 'text-emerald-500 bg-emerald-50'; $gradeT = 'Excellent Performance'; }
                        elseif ($score >= 75) { $grade = 'B'; $gradeC = 'text-blue-500 bg-blue-50'; $gradeT = 'Good Attempt'; }
                        elseif ($score >= 65) { $grade = 'C'; $gradeC = 'text-amber-500 bg-amber-50'; $gradeT = 'Fair Performance'; }
                        elseif ($score >= 50) { $grade = 'D'; $gradeC = 'text-orange-500 bg-orange-50'; $gradeT = 'Needs Improvement'; }
                        else { $grade = 'F'; $gradeC = 'text-rose-500 bg-rose-50'; $gradeT = 'Keep Learning'; }
                    @endphp

                    <div class="relative space-y-6">
                        <div class="w-32 h-32 {{ $gradeC }} rounded-full flex items-center justify-center mx-auto border-8 border-white shadow-2xl transition-transform duration-500 group-hover:scale-110">
                            <span class="text-6xl font-black">{{ $grade }}</span>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-lg font-black text-gray-900 uppercase tracking-widest">{{ $gradeT }}</h4>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Kategori Kompetensi</p>
                        </div>
                    </div>
                </div>

                <!-- Performance Breakdown Small -->
                <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm space-y-8">
                    <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-chart-pie text-indigo-600"></i> Distribusi Performa
                    </h3>
                    
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <div class="flex justify-between text-[10px] font-black uppercase tracking-widest">
                                <span class="text-emerald-600">Ketepatan Jawaban</span>
                                <span class="text-gray-900">{{ round(($correct_count / $questions->count()) * 100) }}%</span>
                            </div>
                            <div class="w-full bg-gray-50 rounded-full h-3 overflow-hidden border border-gray-100">
                                <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-full rounded-full" style="width: {{ ($correct_count / $questions->count()) * 100 }}%"></div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex justify-between text-[10px] font-black uppercase tracking-widest">
                                <span class="text-rose-600">Tingkat Kesalahan</span>
                                <span class="text-gray-900">{{ round(($incorrect_count / $questions->count()) * 100) }}%</span>
                            </div>
                            <div class="w-full bg-gray-50 rounded-full h-3 overflow-hidden border border-gray-100">
                                <div class="bg-gradient-to-r from-rose-400 to-rose-600 h-full rounded-full" style="width: {{ ($incorrect_count / $questions->count()) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Meta Information List -->
            <div class="bg-indigo-900 rounded-[3rem] p-10 text-white shadow-2xl shadow-indigo-200 space-y-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-10 -mt-10 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>

                <h3 class="text-xs font-black uppercase tracking-[0.3em] flex items-center gap-3">
                    <span class="w-1.5 h-6 bg-white rounded-full"></span> Detail Meta
                </h3>

                <div class="space-y-6">
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-indigo-300 uppercase tracking-widest">Mata Pelajaran</p>
                        <p class="text-sm font-black uppercase tracking-wider">{{ $attempt->exam->subject->name }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-indigo-300 uppercase tracking-widest">Metode Gating</p>
                        <p class="text-sm font-black uppercase tracking-wider">Token Sistem Global</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-indigo-300 uppercase tracking-widest">Keamanan Sesi</p>
                        <p class="text-sm font-black uppercase tracking-wider flex items-center gap-2">
                            Tervalidasi <i class="fas fa-check-shield text-[10px] text-emerald-400"></i>
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-indigo-300 uppercase tracking-widest">Status Data</p>
                        <div class="flex items-center gap-2 pt-1">
                             <span class="w-2 h-2 bg-emerald-400 rounded-full animate-ping"></span>
                             <span class="text-[10px] font-black uppercase tracking-widest">Sinkron Terpusat</span>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-white/10 space-y-4">
                     <a href="{{ route('student.exams.print', $attempt->id) }}" target="_blank" class="w-full py-4 bg-white text-indigo-900 hover:bg-indigo-50 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-xl shadow-indigo-950/20 flex items-center justify-center gap-3">
                         <i class="fas fa-print"></i> Cetak Hasil Ujian
                     </a>
                     <a href="{{ route('dashboard.student') }}" class="block w-full text-center text-[10px] font-black text-indigo-300 uppercase tracking-widest hover:text-white transition-colors">
                         Selesaikan & Keluar
                     </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Success celebration on page load
        setTimeout(() => {
            triggerConfetti();
            Swal.fire({
                title: '🎉 Ujian Selesai!',
                html: '<p class="text-[11px] font-black text-gray-500 uppercase tracking-widest leading-relaxed">Sesi Anda telah berhasil sinkron dan diarsipkan dengan aman ke server pusat.</p>',
                icon: 'success',
                confirmButtonText: 'Tutup & Review',
                confirmButtonColor: '#4f46e5',
                background: '#ffffff',
                allowOutsideClick: false,
                customClass: {
                    popup: 'rounded-[3rem] p-10',
                    title: 'text-2xl font-black text-gray-900 tracking-wider',
                    confirmButton: 'rounded-2xl px-10 py-4 text-[10px] font-black uppercase tracking-widest mt-4 shadow-xl shadow-indigo-100'
                }
            });
        }, 500);
    });

    function triggerConfetti() {
        const canvas = document.createElement('canvas');
        canvas.style.position = 'fixed';
        canvas.style.top = '0'; canvas.style.left = '0';
        canvas.style.width = '100%'; canvas.style.height = '100%';
        canvas.style.pointerEvents = 'none';
        canvas.style.zIndex = '99999';
        document.body.appendChild(canvas);
        
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        
        const particles = [];
        for (let i = 0; i < 150; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height - canvas.height,
                size: Math.random() * 4 + 2,
                speedX: Math.random() * 6 - 3,
                speedY: Math.random() * 5 + 3,
                r: Math.random() * 360,
                rs: Math.random() * 10 - 5,
                c: ['#6366f1', '#10b981', '#f59e0b', '#f43f5e', '#a855f7'][Math.floor(Math.random() * 5)]
            });
        }
        
        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach(p => {
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.r * Math.PI / 180);
                ctx.fillStyle = p.c;
                ctx.fillRect(-p.size/2, -p.size/2, p.size, p.size);
                ctx.restore();
                p.x += p.speedX; p.y += p.speedY; p.r += p.rs; p.speedY += 0.1;
            });
            if (particles.some(p => p.y < canvas.height)) requestAnimationFrame(animate);
            else { canvas.style.opacity = '0'; setTimeout(() => canvas.remove(), 1000); }
        }
        animate();
    }
</script>
@endsection
