@extends('layouts.app')

@section('title', 'Import Results - SesekaliCBT')

@section('content')
    <div>
        <div class="mb-8">
            <a href="{{ route('admin.questions.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Questions</a>
            <h2 class="text-3xl font-bold text-gray-900 mt-2">Import Results</h2>
        </div>

        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                            <span class="text-2xl">✓</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Successful Imports</p>
                        <p class="text-2xl font-bold text-green-600">{{ $success_count }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center">
                            <span class="text-2xl">⊘</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Skipped (Duplicates)</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $skipped_count ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center">
                            <span class="text-2xl">✕</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Failed Imports</p>
                        <p class="text-2xl font-bold text-red-600">{{ $failure_count }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if(($skipped_count ?? 0) > 0 && !empty($skipped))
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">⊘ Skipped Questions (Duplicates)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-yellow-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Row</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Question Preview</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Reason</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($skipped as $skip)
                                <tr class="bg-yellow-50 hover:bg-yellow-100">
                                    <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $skip['row'] }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-700">{{ $skip['subject'] ?? 'N/A' }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-700">{{ $skip['question'] ?? 'N/A' }}</td>
                                    <td class="px-6 py-3 text-sm text-yellow-700">{{ $skip['reason'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($failure_count > 0 && !empty($errors))
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">⚠️ Failed Rows</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Row</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Errors</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($errors as $row => $rowErrors)
                                <tr class="bg-red-50">
                                    <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row }}</td>
                                    <td class="px-6 py-3 text-sm text-red-600">
                                        <ul class="list-disc list-inside">
                                            @foreach($rowErrors as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="flex gap-4">
            <a href="{{ route('admin.questions.index') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                View All Questions
            </a>
            <a href="{{ route('admin.questions.importForm') }}" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                Import Another File
            </a>
        </div>
    </div>
@endsection
