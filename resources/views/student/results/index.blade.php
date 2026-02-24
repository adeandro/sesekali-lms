@extends('layouts.app')

@section('title', 'My Exam Results - SesekaliCBT')

@section('page-title', 'My Exam Results')

@section('content')
    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium mb-2">Total Exams Taken</p>
                <p class="text-3xl font-bold text-blue-600">{{ $results->count() }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium mb-2">Average Score</p>
                <p class="text-3xl font-bold text-purple-600">
                    @php
                        $avgScore = $results->where('can_view_score', true)->avg('final_score');
                    @endphp
                    {{ $avgScore ? round($avgScore, 2) : '-' }}
                </p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium mb-2">Highest Score</p>
                <p class="text-3xl font-bold text-green-600">
                    @php
                        $maxScore = $results->where('can_view_score', true)->max('final_score');
                    @endphp
                    {{ $maxScore ? round($maxScore, 2) : '-' }}
                </p>
            </div>
        </div>

        <!-- Results List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Exam Results History</h2>
            </div>

            @if($results->isEmpty())
                <div class="p-6 text-center text-gray-600">
                    <i class="fas fa-inbox text-4xl mb-4"></i>
                    <p>You haven't taken any exams yet.</p>
                    <a href="{{ route('student.exams.index') }}" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                        <i class="fas fa-file-alt mr-2"></i> Browse Available Exams
                    </a>
                </div>
            @else
                <div class="divide-y divide-gray-200">
                    @foreach($results as $result)
                        <div class="p-6 hover:bg-gray-50 transition">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
                                <!-- Exam Title -->
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Exam</p>
                                    <p class="font-semibold text-gray-900">{{ $result->exam->title }}</p>
                                    <p class="text-xs text-gray-500">{{ $result->exam->subject->name }}</p>
                                </div>

                                <!-- Score -->
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Final Score</p>
                                    @if($result->can_view_score)
                                        <p class="text-2xl font-bold">
                                            <span class="px-3 py-1 rounded-full {{ $result->final_score >= 70 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ round($result->final_score, 2) }}
                                            </span>
                                        </p>
                                    @else
                                        <p class="text-lg text-gray-500 italic">Score hidden</p>
                                        <p class="text-xs text-gray-500">Available after exam finalized</p>
                                    @endif
                                </div>

                                <!-- Scores Breakdown (if visible) -->
                                @if($result->can_view_score)
                                    <div>
                                        <p class="text-sm text-gray-600 mb-1">MC / Essay</p>
                                        <p class="font-semibold text-gray-900">
                                            {{ round($result->score_mc, 2) }} / {{ round($result->score_essay, 2) }}
                                        </p>
                                    </div>
                                @else
                                    <div>
                                        <p class="text-sm text-gray-600 mb-1">Status</p>
                                        <p class="font-semibold text-gray-900">Pending</p>
                                    </div>
                                @endif

                                <!-- Submitted Date -->
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Submitted</p>
                                    <p class="font-semibold text-gray-900">{{ $result->submitted_at->format('Y-m-d') }}</p>
                                    <p class="text-xs text-gray-500">{{ $result->submitted_at->format('H:i') }}</p>
                                </div>

                                <!-- Actions -->
                                <div class="flex justify-end gap-2">
                                    @if($result->can_view_score)
                                        <a href="{{ route('student.exams.result', $result->id) }}" 
                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition text-sm">
                                            <i class="fas fa-eye mr-1"></i> View Details
                                        </a>
                                    @else
                                        <span class="px-4 py-2 bg-gray-300 text-gray-600 rounded-lg font-semibold text-sm cursor-not-allowed">
                                            <i class="fas fa-lock mr-1"></i> Locked
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Back Button -->
        <div>
            <a href="{{ route('dashboard.student') }}" class="px-6 py-2 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>
    </div>
@endsection
