@extends('layouts.app')

@section('title', 'Import Result - SesekaliCBT')

@section('content')
    <div>
        <div class="mb-8">
            <a href="{{ route('admin.students.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Students</a>
            <h2 class="text-3xl font-bold text-gray-900 mt-2">Import Result</h2>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-4 gap-4 mb-8">
            <div class="bg-green-50 border-2 border-green-200 rounded-lg p-6">
                <p class="text-sm text-green-600 font-medium">New Students Added</p>
                <p class="text-4xl font-bold text-green-700 mt-2">{{ $success_count }}</p>
            </div>

            <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-6">
                <p class="text-sm text-yellow-600 font-medium">Skipped (Already Exist)</p>
                <p class="text-4xl font-bold text-yellow-700 mt-2">{{ $skipped_count }}</p>
            </div>

            <div class="bg-red-50 border-2 border-red-200 rounded-lg p-6">
                <p class="text-sm text-red-600 font-medium">Failed (Errors)</p>
                <p class="text-4xl font-bold text-red-700 mt-2">{{ $failure_count }}</p>
            </div>

            <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6">
                <p class="text-sm text-blue-600 font-medium">Total Rows</p>
                <p class="text-4xl font-bold text-blue-700 mt-2">{{ $success_count + $skipped_count + $failure_count }}</p>
            </div>
        </div>

        @if($success_count > 0)
            <!-- Successfully Imported Students -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-xl font-bold text-green-700 mb-4">✅ New Students Added ({{ $success_count }})</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-green-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold">NIS</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Grade</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Class Group</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Generated Password</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $item)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-3 text-sm font-medium">{{ $item['student']->nis }}</td>
                                    <td class="px-6 py-3 text-sm">{{ $item['student']->name }}</td>
                                    <td class="px-6 py-3 text-sm">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold">
                                            {{ $item['student']->grade }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-sm font-semibold">{{ $item['student']->class_group }}</td>
                                    <td class="px-6 py-3 text-sm font-mono bg-gray-50 p-2 rounded text-gray-800">
                                        {{ $item['password'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-sm text-orange-600 mt-4 p-3 bg-orange-50 border border-orange-200 rounded">
                    ⚠️ <strong>Important:</strong> Save these passwords. Students will need them to login. They are not recoverable.
                </p>
            </div>
        @endif

        @if($skipped_count > 0)
            <!-- Skipped Students -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-xl font-bold text-yellow-700 mb-4">⏭️ Skipped Students ({{ $skipped_count }})</h3>
                <p class="text-sm text-yellow-600 mb-4">These students were skipped because they already exist in the system.</p>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-yellow-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Row</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">NIS</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Grade</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Class Group</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($skipped as $item)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-3 text-sm font-medium text-gray-500">{{ $item['row'] }}</td>
                                    <td class="px-6 py-3 text-sm font-medium">{{ $item['nis'] }}</td>
                                    <td class="px-6 py-3 text-sm">{{ $item['name'] }}</td>
                                    <td class="px-6 py-3 text-sm">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold">
                                            {{ $item['grade'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-sm font-semibold">{{ $item['class_group'] }}</td>
                                    <td class="px-6 py-3 text-sm text-yellow-700">{{ $item['reason'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($failure_count > 0)
            <!-- Failed Rows -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-xl font-bold text-red-700 mb-4">❌ Failed Rows ({{ $failure_count }})</h3>
                <p class="text-sm text-red-600 mb-4">These rows have errors and were not imported. Please fix the issues and try again.</p>
                <div class="space-y-4">
                    @foreach($errors as $error_item)
                        <div class="border-l-4 border-red-500 bg-red-50 p-4 rounded">
                            <p class="font-semibold text-red-900">Row {{ $error_item['row'] }}</p>
                            @foreach($error_item['errors'] as $field => $message)
                                <p class="text-sm text-red-800 mt-1">• <strong>{{ ucfirst($field) }}:</strong> {{ $message }}</p>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex gap-4">
            <a href="{{ route('admin.students.importForm') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Import Again
            </a>
            <a href="{{ route('admin.students.index') }}" class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition">
                Back to Students
            </a>
        </div>
    </div>
@endsection
