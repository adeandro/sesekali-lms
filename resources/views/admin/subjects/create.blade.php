@extends('layouts.app')

@section('title', 'Add Subject - SesekaliCBT')

@section('content')
    <div>
        <div class="mb-8">
            <a href="{{ route('admin.subjects.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Subjects</a>
            <h2 class="text-3xl font-bold text-gray-900 mt-2">Add New Subject</h2>
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

        <div class="bg-white rounded-lg shadow p-8 max-w-2xl">
            <form action="{{ route('admin.subjects.store') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Subject Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                        placeholder="e.g., Mathematics, Physics"
                        required
                    >
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Create Subject
                    </button>
                    <a href="{{ route('admin.subjects.index') }}" class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
