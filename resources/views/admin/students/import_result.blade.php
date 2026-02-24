@extends('layouts.app')

@section('title', 'Import Result - SesekaliCBT')

@section('content')
    <div>
        <div class="mb-8">
            <a href="{{ route('admin.students.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Students</a>
            <h2 class="text-3xl font-bold text-gray-900 mt-2">Import Result</h2>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="bg-green-50 border-2 border-green-200 rounded-lg p-6">
                <p class="text-sm text-green-600 font-medium">Successfully Imported</p>
                <p class="text-4xl font-bold text-green-700 mt-2">{{ $success_count }}</p>
            </div>

            <div class="bg-red-50 border-2 border-red-200 rounded-lg p-6">
                <p class="text-sm text-red-600 font-medium">Failed</p>
                <p class="text-4xl font-bold text-red-700 mt-2">{{ $failure_count }}</p>
            </div>

            <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6">
                <p class="text-sm text-blue-600 font-medium">Total Rows</p>
                <p class="text-4xl font-bold text-blue-700 mt-2">{{ $success_count + $failure_count }}</p>
            </div>
        </div>

        @if($success_count > 0)
            <!-- Successfully Imported Students -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-xl font-bold text-green-700 mb-4">✅ Successfully Imported Students</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-green-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold">NIS</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Class</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Generated Password</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $item)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-3 text-sm font-medium">{{ $item['student']->nis }}</td>
                                    <td class="px-6 py-3 text-sm">{{ $item['student']->name }}</td>
                                    <td class="px-6 py-3 text-sm">{{ $item['student']->class }}</td>
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

        @if($failure_count > 0)
            <!-- Failed Rows -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-xl font-bold text-red-700 mb-4">❌ Failed Rows</h3>
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
