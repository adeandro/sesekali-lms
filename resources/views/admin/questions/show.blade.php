@extends('layouts.app')

@section('title', 'View Question - SesekaliCBT')

@section('content')
    <div>
        <div class="flex justify-between items-center mb-8">
            <div>
                <a href="{{ route('admin.questions.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Questions</a>
                <h2 class="text-3xl font-bold text-gray-900 mt-2">{{ $question->question_text }}</h2>
            </div>
            <div class="space-x-2">
                <a href="{{ route('admin.questions.edit', $question) }}" class="inline-block px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
                    ✏️ Edit
                </a>
                <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" style="display:inline;" class="delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition" data-confirm="true" data-confirm-title="Hapus Pertanyaan?" data-confirm-text="Apakah Anda yakin ingin menghapus pertanyaan ini? Tindakan ini tidak dapat dibatalkan.">
                        🗑️ Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm text-gray-600 mb-2">Subject</p>
                <p class="text-xl font-semibold text-gray-900">{{ $question->subject->name }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm text-gray-600 mb-2">Topic</p>
                <p class="text-xl font-semibold text-gray-900">{{ $question->topic }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm text-gray-600 mb-2">Difficulty Level</p>
                <p class="text-xl font-semibold text-gray-900">
                    <span class="px-3 py-1 rounded-full text-white text-sm {{ $question->difficulty_level === 'easy' ? 'bg-green-500' : ($question->difficulty_level === 'medium' ? 'bg-yellow-500' : 'bg-red-500') }}">
                        {{ ucfirst($question->difficulty_level) }}
                    </span>
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Question</h3>
            <p class="text-gray-700 leading-relaxed">{{ $question->question_text }}</p>
        </div>

        @if($question->question_type === 'multiple_choice')
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Answer Options</h3>
                <div class="space-y-3">
                    @php
                        $options = ['A' => $question->option_a, 'B' => $question->option_b, 'C' => $question->option_c, 'D' => $question->option_d, 'E' => $question->option_e];
                    @endphp
                    @foreach($options as $label => $text)
                        @if($text)
                            <div class="p-4 border-2 rounded-lg {{ $question->correct_answer === $label ? 'border-green-500 bg-green-50' : 'border-gray-300' }}">
                                <p class="font-semibold">
                                    <span class="inline-block w-8 h-8 bg-blue-600 text-white rounded-full text-center leading-8">{{ $label }}</span>
                                    {{ $text }}
                                    @if($question->correct_answer === $label)
                                        <span class="ml-2 text-green-600">✓ Correct</span>
                                    @endif
                                </p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        @if($question->explanation)
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Explanation</h3>
                <p class="text-gray-700 leading-relaxed">{{ $question->explanation }}</p>
            </div>
        @endif

        <div class="bg-gray-100 rounded-lg p-4">
            <p class="text-sm text-gray-600">
                Created: {{ $question->created_at->format('M d, Y H:i') }} | 
                Updated: {{ $question->updated_at->format('M d, Y H:i') }}
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle delete form confirmation
            const deleteForm = document.querySelector('.delete-form');
            if (deleteForm) {
                deleteForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const form = this;
                    
                    Swal.fire({
                        icon: 'warning',
                        title: 'Hapus Pertanyaan?',
                        text: 'Apakah Anda yakin ingin menghapus pertanyaan ini? Tindakan ini tidak dapat dibatalkan.',
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal',
                        showCancelButton: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            }
        });
    </script>
@endsection
