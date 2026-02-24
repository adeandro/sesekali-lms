@extends('layouts.app')

@section('title', 'Exam Results - SesekaliCBT')

@section('page-title', 'Exam Results')

@section('content')
    <div class="space-y-6">
        <!-- Statistics Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium mb-2">Total Exams</p>
                <p class="text-3xl font-bold text-blue-600">{{ $exams->count() }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium mb-2">Total Participants</p>
                <p class="text-3xl font-bold text-green-600">
                    {{ $exams->sum(fn($e) => $e->stats['total_participants']) }}
                </p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium mb-2">Average Score</p>
                <p class="text-3xl font-bold text-purple-600">
                    @php
                        $avgScore = $exams->sum(fn($e) => $e->stats['average_score'] * $e->stats['total_participants']) / 
                                    max($exams->sum(fn($e) => $e->stats['total_participants']), 1);
                    @endphp
                    {{ round($avgScore, 2) }}
                </p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium mb-2">Pass Rate</p>
                <p class="text-3xl font-bold text-orange-600">
                    @php
                        $passRate = $exams->sum(fn($e) => $e->stats['pass_rate'] * $e->stats['total_participants']) / 
                                   max($exams->sum(fn($e) => $e->stats['total_participants']), 1);
                    @endphp
                    {{ round($passRate, 2) }}
                </p>
            </div>
        </div>

        <!-- Exams Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Exam Results</h2>
            </div>

            @if($exams->isEmpty())
                <div class="p-6 text-center text-gray-600">
                    <i class="fas fa-inbox text-4xl mb-4"></i>
                    <p>No exams found.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Exam Title</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Subject</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Participants</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Avg Score</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Highest</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Lowest</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($exams as $exam)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $exam->title }}</td>
                                    <td class="px-6 py-4 text-gray-700">{{ $exam->subject->name }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                            {{ $exam->stats['total_participants'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-semibold text-gray-900">
                                        {{ round($exam->stats['average_score'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-green-600 font-semibold">
                                        {{ round($exam->stats['highest_score'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-red-600 font-semibold">
                                        {{ round($exam->stats['lowest_score'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('admin.results.show', $exam->id) }}" class="text-blue-600 hover:text-blue-700 font-medium transition">
                                            <i class="fas fa-eye mr-1"></i>View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
