@extends('layouts.app')

@section('title', 'Import Questions - SesekaliCBT')

@section('content')
    <div>
        <div class="mb-8">
            <a href="{{ route('admin.questions.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Questions</a>
            <h2 class="text-3xl font-bold text-gray-900 mt-2">Import Questions</h2>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="font-semibold text-red-800 mb-2">Validation Errors:</p>
                <ul class="list-disc list-inside text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-6">
            <!-- Instructions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Instructions</h3>
                <div class="text-gray-700 space-y-3 text-sm">
                    <p><strong>File Format:</strong> Excel (.xlsx)</p>
                    <p><strong>Required Columns:</strong></p>
                    <ul class="list-disc list-inside ml-2">
                        <li>subject (name)</li>
                        <li>jenjang (10, 11, atau 12)</li>
                        <li>topic</li>
                        <li>difficulty (easy/medium/hard)</li>
                        <li>question_type (multiple_choice/essay)</li>
                        <li>question_text</li>
                        <li>option_a</li>
                        <li>option_b</li>
                        <li>option_c</li>
                        <li>option_d</li>
                    </ul>
                    <p><strong>Optional Columns:</strong></p>
                    <ul class="list-disc list-inside ml-2">
                        <li>option_e</li>
                        <li>correct_answer (for multiple choice)</li>
                        <li>explanation</li>
                    </ul>
                    <p class="text-yellow-700 bg-yellow-50 p-2 rounded">
                        ⚠️ Make sure subjects exist before importing, otherwise they will be created.
                    </p>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📤 Upload File</h3>
                <form action="{{ route('admin.questions.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-6">
                        <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                            Excel File <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="file" 
                            id="file" 
                            name="file" 
                            accept=".xlsx,.xls" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('file') border-red-500 @enderror" 
                            required
                        >
                        @error('file')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        <p class="text-gray-500 text-xs mt-2">Supported formats: .xlsx, .xls (Max size: 150MB)</p>
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="update_existing" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm">
                            <span class="ml-2 text-sm text-gray-700">Update existing questions</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                        🚀 Import Questions
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-800 text-sm">
                <strong>💡 Tip:</strong> You can download a sample file from the questions index page to use as a template.
            </p>
        </div>
    </div>
@endsection
