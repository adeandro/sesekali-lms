@extends('layouts.app')

@section('title', 'Detail Butir Soal - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Detail Soal')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6 animate-fadeIn pb-12">
        <!-- Breadcrumbs & Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="flex flex-col gap-2">
                <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
                    <a href="{{ route('admin.questions.index') }}" class="hover:text-indigo-600 transition-colors">Bank Soal</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span class="text-indigo-600">Detail Soal</span>
                </nav>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Detail Butir Soal</h2>
                <p class="text-sm text-gray-500 mt-1">Informasi lengkap mengenai pertanyaan, pilihan jawaban, dan pembahasan.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.questions.edit', $question) }}" class="px-6 py-3 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center gap-2">
                    <i class="fas fa-edit"></i> Edit Soal
                </a>
                <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" class="delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-6 py-3 bg-rose-50 text-rose-600 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-rose-600 hover:text-white transition flex items-center gap-2">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-book text-xl"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Mata Pelajaran</p>
                    <p class="text-sm font-black text-gray-900">{{ $question->subject->name }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <i class="fas fa-tags text-xl"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Topik</p>
                    <p class="text-sm font-black text-gray-900 truncate max-w-[120px]">{{ $question->topic }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fas fa-layer-group text-xl"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Kelas</p>
                    <p class="text-sm font-black text-gray-900">Kelas {{ $question->jenjang }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex items-center gap-4">
                @php
                    $diffColor = $question->difficulty_level === 'easy' ? 'emerald' : ($question->difficulty_level === 'medium' ? 'amber' : 'rose');
                    $diffIcon = $question->difficulty_level === 'easy' ? 'smile' : ($question->difficulty_level === 'medium' ? 'meh' : 'frown');
                @endphp
                <div class="w-12 h-12 rounded-2xl bg-{{ $diffColor }}-50 flex items-center justify-center text-{{ $diffColor }}-600">
                    <i class="fas fa-{{ $diffIcon }} text-xl"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Kesulitan</p>
                    <p class="text-sm font-black text-{{ $diffColor }}-600 uppercase">{{ $question->difficulty_level }}</p>
                </div>
            </div>
        </div>

        <!-- Question Content -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 md:p-12 space-y-8">
                <div class="space-y-4">
                    <h3 class="text-xs font-black text-indigo-600 uppercase tracking-[0.2em]">Pertanyaan</h3>
                    <div class="text-xl font-bold text-gray-900 leading-relaxed">
                        {!! nl2br(e($question->question_text)) !!}
                    </div>
                </div>

                @if($question->question_image)
                    <div class="p-4 bg-gray-50 rounded-3xl border border-gray-100 inline-block">
                        <img src="{{ asset($question->question_image) }}" class="max-h-80 w-auto rounded-2xl shadow-lg border-4 border-white" alt="Gambar Soal">
                    </div>
                @endif
            </div>

            @if($question->question_type === 'multiple_choice')
                <div class="bg-gray-50/50 border-t border-gray-100 p-8 md:p-12">
                    <h3 class="text-xs font-black text-indigo-600 uppercase tracking-[0.2em] mb-8">Pilihan Jawaban</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach(['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d', 'E' => 'option_e'] as $label => $name)
                            @if($question->$name || $question->{$name . '_image'})
                                @php $isCorrect = ($question->correct_answer === $label); @endphp
                                <div class="relative group">
                                    <div class="p-6 bg-white rounded-3xl border-2 transition-all {{ $isCorrect ? 'border-emerald-500 bg-emerald-50/30' : 'border-gray-100 group-hover:border-indigo-100' }}">
                                        <div class="flex items-start gap-4">
                                            <span class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-2xl text-sm font-black transition-colors {{ $isCorrect ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-400 group-hover:bg-indigo-600 group-hover:text-white' }}">
                                                {{ $label }}
                                            </span>
                                            <div class="flex-1 space-y-4">
                                                <p class="text-sm font-bold {{ $isCorrect ? 'text-emerald-900' : 'text-gray-700' }} mt-2">
                                                    {{ $question->$name ?? '(Tanpa Teks)' }}
                                                </p>
                                                @if($question->{$name . '_image'})
                                                    <img src="{{ asset($question->{$name . '_image'}) }}" class="max-h-32 rounded-xl border border-gray-100" alt="Gambar Opsi {{ $label }}">
                                                @endif
                                            </div>
                                            @if($isCorrect)
                                                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @if($question->explanation)
            <div class="bg-indigo-600 rounded-[2.5rem] shadow-xl shadow-indigo-100 p-8 md:p-12 text-white overflow-hidden relative">
                <div class="absolute top-0 right-0 p-8 opacity-10">
                    <i class="fas fa-comment-medical text-9xl"></i>
                </div>
                <div class="relative z-10">
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] mb-4 opacity-70">Pembahasan</h3>
                    <div class="text-lg font-bold leading-relaxed">
                        {!! nl2br(e($question->explanation)) !!}
                    </div>
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between text-[10px] font-black text-gray-400 uppercase tracking-widest px-8">
            <p>Dibuat: {{ $question->created_at->translatedFormat('d F Y, H:i') }}</p>
            <p>Terakhir Diperbarui: {{ $question->updated_at->translatedFormat('d F Y, H:i') }}</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForm = document.querySelector('.delete-form');
            if (deleteForm) {
                deleteForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Hapus Butir Soal?',
                        text: 'Tindakan ini tidak dapat dibatalkan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#64748b',
                        background: '#ffffff',
                        customClass: {
                            popup: 'rounded-[2.5rem]',
                            confirmButton: 'rounded-2xl px-8 py-3 font-black uppercase tracking-widest text-[10px]',
                            cancelButton: 'rounded-2xl px-8 py-3 font-black uppercase tracking-widest text-[10px]'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            }
        });
    </script>

    <style>
        .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
        .animate-pop { animation: pop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pop { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    </style>
@endsection
