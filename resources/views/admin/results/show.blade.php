@extends('layouts.app')

@section('title', $exam->title . ' Results - SesekaliCBT')

@section('page-title', $exam->title . ' - Results')

@section('content')
    <div class="space-y-6">
        <!-- Exam Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold mb-2">{{ $exam->title }}</h1>
                    <p class="text-blue-100 mb-4">{{ $exam->subject->name }}</p>
                </div>
                <div class="space-x-2">
                    <a href="{{ route('admin.results.export', $exam->id) }}" class="inline-block px-6 py-2 bg-white text-blue-600 rounded-lg font-semibold hover:bg-gray-100 transition">
                        <i class="fas fa-download"></i> Export Excel
                    </a>
                    <a href="{{ route('admin.exams.print-card', $exam->id) }}" target="_blank" class="inline-block px-6 py-2 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 transition">
                        <i class="fas fa-print"></i> Print Cards
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium mb-2">Total Participants</p>
                <p class="text-3xl font-bold text-blue-600">{{ $stats['total_participants'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium mb-2">Average Score</p>
                <p class="text-3xl font-bold text-purple-600">{{ round($stats['average_score'], 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium mb-2">Highest Score</p>
                <p class="text-3xl font-bold text-green-600">{{ round($stats['highest_score'], 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium mb-2">Pass Rate (70+)</p>
                <p class="text-3xl font-bold text-orange-600">{{ round($stats['pass_rate'], 2) }}</p>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Grade Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Filter by Grade</label>
                    <select name="class" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                        <option value="">All Grades</option>
                        @foreach($classes as $class)
                            <option value="{{ $class }}" {{ request('class') === $class ? 'selected' : '' }}>
                                Grade {{ $class }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Search by Name/NIS</label>
                    <input type="text" name="search" placeholder="Name or NIS..." value="{{ request('search') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                </div>

                <!-- Submit -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>
                    <a href="{{ route('admin.results.show', $exam->id) }}" class="px-4 py-2 bg-gray-400 text-white rounded-lg font-semibold hover:bg-gray-500 transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Student Results ({{ $attempts->count() }})</h2>
            </div>

            @if($attempts->isEmpty())
                <div class="p-6 text-center text-gray-600">
                    <p>No results found.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Rank</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Student Name</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">NIS</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">MC Score</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Essay Score</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Final Score</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Submitted</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($attempts as $attempt)
                                <tr class="hover:bg-gray-50 transition {{ $attempt->ranking === 1 ? 'bg-yellow-50' : '' }}">
                                    <td class="px-6 py-4 text-center font-bold">
                                        @if($attempt->ranking === 1)
                                            <span class="inline-block px-3 py-1 bg-yellow-200 text-yellow-900 rounded-full">
                                                <i class="fas fa-crown mr-1"></i>{{ $attempt->ranking }}
                                            </span>
                                        @elseif($attempt->ranking === 2)
                                            <span class="inline-block px-3 py-1 bg-gray-200 text-gray-900 rounded-full">
                                                {{ $attempt->ranking }}
                                            </span>
                                        @elseif($attempt->ranking === 3)
                                            <span class="inline-block px-3 py-1 bg-orange-200 text-orange-900 rounded-full">
                                                {{ $attempt->ranking }}
                                            </span>
                                        @else
                                            {{ $attempt->ranking }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $attempt->student->name }}</td>
                                    <td class="px-6 py-4 text-gray-700">{{ $attempt->student->nis ?? '-' }}</td>
                                    <td class="px-6 py-4 text-center font-semibold text-blue-600">
                                        {{ round($attempt->score_mc, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-semibold {{ $attempt->score_essay > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                        {{ round($attempt->score_essay, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold">
                                        <span class="px-3 py-1 rounded-full {{ $attempt->final_score >= 70 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ round($attempt->final_score, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-600 text-sm">
                                        {{ $attempt->submitted_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('admin.results.review', [$exam->id, $attempt->id]) }}" 
                                            class="text-blue-600 hover:text-blue-700 font-medium transition">
                                            <i class="fas fa-edit mr-1"></i>Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Back Button -->
        <div class="flex gap-2">
            <a href="{{ route('admin.results.index') }}" class="px-6 py-2 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Results
            </a>
        </div>
    </div>
@endsection
