@extends('layouts.app')

@section('title', 'Koreksi & Review - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Koreksi & Review')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8 animate-fadeIn pb-12">
        <!-- Breadcrumbs & Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="flex flex-col gap-2">
                <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
                    <a href="{{ route('admin.results.index') }}" class="hover:text-indigo-600 transition-colors">Hasil Ujian</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <a href="{{ route('admin.results.show', $exam->id) }}" class="hover:text-indigo-600 transition-colors">Detail Hasil</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span class="text-indigo-600">Review & Koreksi</span>
                </nav>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Review & Koreksi Jawaban</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $attempt->student->name }}</span>
                    <span class="text-gray-300">•</span>
                    <span class="text-sm font-bold text-gray-500">{{ $exam->title }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.results.show', $exam->id) }}" class="px-6 py-3 bg-gray-50 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-gray-100 hover:text-gray-600 transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Student & Exam Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 flex items-center gap-6">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-user-graduate text-2xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nama Siswa</p>
                    <p class="text-lg font-black text-gray-900 leading-tight">{{ $attempt->student->name }}</p>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">NIS: {{ $attempt->student->nis ?? '-' }}</p>
                </div>
            </div>
            
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 flex items-center gap-6 group">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 transition-colors group-hover:bg-emerald-600 group-hover:text-white">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Skor Pilihan Ganda</p>
                    <p class="text-3xl font-black text-gray-900">{{ round($attempt->score_mc, 1) }} <span class="text-xs text-gray-300 font-bold tracking-normal italic">pts</span></p>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 flex items-center gap-6 group">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 transition-colors group-hover:bg-amber-600 group-hover:text-white">
                    <i class="fas fa-pen-nib text-2xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Skor Esai Saat Ini</p>
                    <p class="text-3xl font-black text-gray-900">{{ round($attempt->score_essay, 1) }} <span class="text-xs text-gray-300 font-bold tracking-normal italic">pts</span></p>
                </div>
            </div>
        </div>

        <!-- Multiple Choice Section -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                    <i class="fas fa-list-check text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Review Pilihan Ganda</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kunci Jawaban & Jawaban Siswa</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-20 text-center">No</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Pertanyaan & Analisis Jawaban</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-32 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($mcAnswers as $index => $answer)
                            @php
                                $isCorrect = $answer->is_correct;
                                $selectedLetter = strtoupper($answer->selected_answer);
                                $correctLetter = strtoupper($answer->question->correct_answer);
                                $selectedText = $answer->selected_answer_text;
                                $correctText = $answer->correct_answer_text;

                                if (!$selectedText && $answer->selected_answer) {
                                    $opt = strtolower($answer->selected_answer);
                                    $selectedText = $answer->question->{"option_$opt"} ?? null;
                                }
                                if (!$correctText) {
                                    $opt = strtolower($answer->question->correct_answer);
                                    $correctText = $answer->question->{"option_$opt"} ?? null;
                                }

                                $displaySelected = ($selectedText ? "($selectedLetter) $selectedText" : "($selectedLetter) (Kosong)");
                                $displayCorrect = ($correctText ? "($correctLetter) $correctText" : "($correctLetter)");
                            @endphp
                            <tr class="group hover:bg-gray-50/50 transition-colors">
                                <td class="px-8 py-6 text-center align-top">
                                    <span class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-400 mx-auto">{{ $loop->iteration }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-sm font-bold text-gray-900 leading-relaxed mb-4">
                                        {!! nl2br(e($answer->question->question_text)) !!}
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <div class="p-4 rounded-2xl text-[11px] font-bold border flex items-start gap-3 {{ $isCorrect ? 'bg-emerald-50 border-emerald-100 text-emerald-700' : 'bg-rose-50 border-rose-100 text-rose-700' }}">
                                            <i class="fas fa-{{ $isCorrect ? 'check-circle' : 'times-circle' }} mt-0.5"></i>
                                            <div>
                                                <span class="uppercase tracking-widest opacity-60">Jawaban Siswa:</span>
                                                <span class="ml-2">{{ $displaySelected }}</span>
                                            </div>
                                        </div>
                                        @if(!$isCorrect)
                                            <div class="p-4 rounded-2xl text-[11px] font-bold bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-start gap-3">
                                                <i class="fas fa-key mt-0.5"></i>
                                                <div>
                                                    <span class="uppercase tracking-widest opacity-60">Kunci Jawaban:</span>
                                                    <span class="ml-2">{{ $displayCorrect }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center align-middle">
                                    @if($isCorrect)
                                        <div class="flex flex-col items-center gap-1">
                                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <span class="text-[9px] font-black uppercase tracking-widest text-emerald-600">Benar</span>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center gap-1">
                                            <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xs">
                                                <i class="fas fa-times"></i>
                                            </div>
                                            <span class="text-[9px] font-black uppercase tracking-widest text-rose-600">Salah</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-8 py-12 text-center">
                                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest">Tidak ada record jawaban pilihan ganda.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Essay Grading Section -->
        <div class="space-y-6">
            <div class="flex items-center gap-4 px-8">
                <div class="w-10 h-10 rounded-xl bg-amber-600 flex items-center justify-center text-white shadow-lg shadow-amber-100">
                    <i class="fas fa-pen-fancy text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Penilaian Jawaban Esai</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Berikan skor secara manual berdasarkan jawaban siswa</p>
                </div>
            </div>

            <form action="{{ route('admin.results.update-grades', [$exam->id, $attempt->id]) }}" method="POST" class="space-y-6" id="essayForm">
                @csrf

                @forelse($essayAnswers as $answer)
                    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden animate-fadeIn">
                        <div class="p-8 border-b border-gray-50 bg-gray-50/30">
                            <h4 class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.2em] mb-4">Pertanyaan Esai #{{ $loop->iteration }}</h4>
                            <div class="text-sm font-bold text-gray-900 leading-relaxed">
                                {!! nl2br(e($answer->question->question_text)) !!}
                            </div>
                        </div>

                        <div class="p-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Student Answer -->
                            <div class="space-y-4">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                    <i class="fas fa-comment-dots"></i> Jawaban Siswa
                                </label>
                                <div class="bg-indigo-50/30 p-6 rounded-3xl border-2 border-indigo-100/50 text-sm font-bold text-indigo-900 leading-relaxed min-h-[120px] whitespace-pre-wrap">
                                    {{ $answer->essay_answer ?? '(Siswa tidak memberikan jawaban)' }}
                                </div>
                            </div>

                            <!-- Scoring Input -->
                            <div class="space-y-4">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                    <i class="fas fa-star"></i> Penilaian Skor (0 - 100)
                                </label>
                                <div class="flex items-center gap-6">
                                    <div class="relative flex-1 group">
                                        <input type="number" 
                                            name="scores[{{ $answer->question->id }}]"
                                            min="0" 
                                            max="100" 
                                            step="0.01"
                                            value="{{ round($answer->essay_score ?? 0, 2) }}"
                                            class="w-full h-16 bg-gray-50 border-transparent rounded-[1.5rem] px-6 text-2xl font-black text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all"
                                            required>
                                        <div class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-300 uppercase tracking-widest">pts</div>
                                    </div>
                                    <div class="text-2xl font-black text-gray-200">/ 100</div>
                                </div>
                                <div class="flex flex-wrap gap-2 pt-2">
                                    @foreach([0, 25, 50, 75, 100] as $quickScore)
                                        <button type="button" 
                                            onclick="this.closest('.space-y-4').querySelector('input').value = {{ $quickScore }}"
                                            class="px-4 py-2 bg-gray-100 hover:bg-indigo-600 hover:text-white text-gray-600 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all active:scale-95 shadow-sm border border-transparent hover:border-indigo-200">
                                            {{ $quickScore }}
                                        </button>
                                    @endforeach
                                </div>
                                <p class="text-[10px] font-bold text-gray-400 italic">Klik tombol di atas untuk input cepat atau gunakan titik (.) untuk desimal.</p>
                                @error("scores.{$answer->question->id}")
                                    <p class="text-rose-600 text-[10px] font-black uppercase tracking-widest mt-2 pl-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-16 text-center space-y-4">
                        <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center text-gray-200 mx-auto">
                            <i class="fas fa-feather text-4xl"></i>
                        </div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Tidak ada soal esai untuk dikoreksi.</p>
                    </div>
                @endforelse

                @if($essayAnswers->count() > 0)
                    <div class="flex items-center gap-4 bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                        <button type="submit" class="flex-1 h-14 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center justify-center gap-3 group">
                            <i class="fas fa-save group-hover:scale-125 transition-transform"></i> Simpan Hasil Koreksi
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-indigo-900/40 backdrop-blur-md z-[9999] hidden flex-col items-center justify-center animate-fadeIn">
        <div class="w-24 h-24 relative">
            <div class="absolute inset-0 border-8 border-white/20 rounded-full"></div>
            <div class="absolute inset-0 border-8 border-white rounded-full border-t-transparent animate-spin"></div>
        </div>
        <div class="mt-8 text-center">
            <h3 class="text-white text-xl font-black uppercase tracking-[0.3em] mb-2">Menyimpan...</h3>
            <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest opacity-70">Sabar ya, sedang mengalkulasi skor baru.</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('essayForm');
            const overlay = document.getElementById('loadingOverlay');
            
            if (form) {
                form.addEventListener('submit', function() {
                    overlay.classList.remove('hidden');
                    overlay.classList.add('flex');
                });
            }
        });
    </script>

    <style>
        .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
@endsection
