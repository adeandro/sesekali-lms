@extends('layouts.app')

@section('title', 'Grade Essays - SesekaliCBT')

@section('page-title', 'Grade Essays - ' . $attempt->student->name)

@section('content')
    <div class="space-y-6">
        <!-- Student Info -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg shadow-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-blue-100 text-sm mb-1">Student Name</p>
                    <p class="text-2xl font-bold">{{ $attempt->student->name }}</p>
                </div>
                <div>
                    <p class="text-blue-100 text-sm mb-1">NIS</p>
                    <p class="text-2xl font-bold">{{ $attempt->student->nis ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-blue-100 text-sm mb-1">Class</p>
                    <p class="text-2xl font-bold">{{ $attempt->student->class ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Exam and Score Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium mb-2">Exam Title</p>
                <p class="text-xl font-bold text-gray-900">{{ $exam->title }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium mb-2">MC Score</p>
                <p class="text-xl font-bold text-blue-600">{{ round($attempt->score_mc, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium mb-2">Current Essay Score</p>
                <p class="text-xl font-bold text-purple-600">{{ round($attempt->score_essay, 2) }}</p>
            </div>
        </div>

        <!-- Essay Grading Form -->
        <form action="{{ route('admin.results.update-grades', [$exam->id, $attempt->id]) }}" method="POST" class="space-y-6">
            @csrf

            @forelse($essayAnswers as $answer)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <!-- Question Header -->
                    <div class="bg-gray-50 border-b border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-900">Question {{ $loop->iteration }}: Essay</h3>
                        <p class="text-gray-700 mt-2">{{ $answer->question->question_text }}</p>
                    </div>

                    <!-- Student Answer -->
                    <div class="p-6 border-b border-gray-200 bg-blue-50">
                        <p class="text-sm font-semibold text-gray-700 mb-3">Student's Answer:</p>
                        <div class="bg-white p-4 rounded-lg border border-blue-200 whitespace-pre-wrap text-gray-800">
                            {{ $answer->essay_answer ?? '(No answer provided)' }}
                        </div>
                    </div>

                    <!-- Score Input -->
                    <div class="p-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Score (0-100)
                            <span class="ml-2 text-red-600">*</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <input type="number" 
                                name="scores[{{ $answer->question->id }}]"
                                min="0" 
                                max="100" 
                                step="0.01"
                                value="{{ round($answer->is_correct ?? 0, 2) }}"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                required>
                            <span class="text-gray-600 font-semibold">/ 100</span>
                        </div>
                        @error("scores.{$answer->question->id}")
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-6 text-center text-gray-600">
                    <i class="fas fa-inbox text-4xl mb-4"></i>
                    <p>No essay answers to grade.</p>
                </div>
            @endforelse

            @if($essayAnswers->count() > 0)
                <!-- Form Actions -->
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">
                        <i class="fas fa-save mr-2"></i> Save Grades
                    </button>
                    <a href="{{ route('admin.results.show', $exam->id) }}" class="flex-1 px-6 py-3 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition text-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            @endif
        </form>

        <!-- Status Messages -->
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="font-semibold text-red-800 mb-2">Validation Errors:</p>
                <ul class="list-disc list-inside text-red-600">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
