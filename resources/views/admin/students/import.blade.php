@extends('layouts.app')

@section('title', 'Import Students - SesekaliCBT')

@section('content')
    <div>
        <div class="mb-8">
            <a href="{{ route('admin.students.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Students</a>
            <h2 class="text-3xl font-bold text-gray-900 mt-2">Import Students from Excel</h2>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="font-semibold text-red-800 mb-2">Error:</p>
                <ul class="list-disc list-inside text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="font-semibold text-blue-900 mb-2">📋 File Format</h3>
                <p class="text-sm text-blue-800">Use Excel (.xlsx) format with columns:</p>
                <ul class="list-disc list-inside text-sm text-blue-800 mt-2">
                    <li>nis</li>
                    <li>full_name or name</li>
                    <li>class</li>
                    <li>email (optional)</li>
                </ul>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h3 class="font-semibold text-green-900 mb-2">✅ Validation</h3>
                <p class="text-sm text-green-800">Each row will be validated for:</p>
                <ul class="list-disc list-inside text-sm text-green-800 mt-2">
                    <li>Required fields</li>
                    <li>Unique NIS</li>
                    <li>Valid email</li>
                </ul>
            </div>

            <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                <h3 class="font-semibold text-purple-900 mb-2">🔐 Security</h3>
                <p class="text-sm text-purple-800">Passwords are:</p>
                <ul class="list-disc list-inside text-sm text-purple-800 mt-2">
                    <li>Auto-generated</li>
                    <li>Shown once</li>
                    <li>Securely hashed</li>
                </ul>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-8 max-w-2xl">
            <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-6">
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                        Excel File <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center justify-center w-full">
                        <label for="file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                <p class="text-xs text-gray-500">Excel files (.xlsx) up to 5MB</p>
                            </div>
                            <input id="file" name="file" type="file" accept=".xlsx" required class="hidden">
                        </label>
                    </div>
                    @error('file')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="fileUploadInfo" class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg hidden">
                    <p class="text-sm text-gray-700">File: <span id="fileName" class="font-semibold"></span></p>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Import Students
                    </button>
                    <a href="{{ route('admin.students.index') }}" class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('file').addEventListener('change', function() {
            if (this.files.length > 0) {
                document.getElementById('fileName').textContent = this.files[0].name;
                document.getElementById('fileUploadInfo').classList.remove('hidden');
            }
        });
    </script>
@endsection
