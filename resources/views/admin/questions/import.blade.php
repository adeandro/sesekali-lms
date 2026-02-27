@extends('layouts.app')

@section('title', 'Import Questions - SesekaliCBT')

@section('content')
    <div>
        <div class="mb-8">
            <a href="{{ route('admin.questions.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Questions</a>
            <h2 class="text-3xl font-bold text-gray-900 mt-2">📥 Import Questions</h2>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Instructions -->
            <div class="lg:col-span-1">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-md p-6 border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center gap-2">
                        <span>📋</span> Panduan Format File
                    </h3>
                    <div class="text-blue-800 space-y-4 text-sm">
                        <div>
                            <p class="font-semibold mb-1">Format File:</p>
                            <p class="text-blue-700">Excel (.xlsx)</p>
                        </div>
                        <div>
                            <p class="font-semibold mb-2">Kolom Wajib:</p>
                            <ul class="list-disc list-inside space-y-1 text-blue-700">
                                <li>subject (nama)</li>
                                <li>jenjang (10, 11, atau 12)</li>
                                <li>topic</li>
                                <li>difficulty (easy/medium/hard)</li>
                                <li>question_type (multiple_choice/essay)</li>
                                <li>question_text</li>
                                <li>option_a - option_d</li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-semibold mb-2">Kolom Opsional:</p>
                            <ul class="list-disc list-inside space-y-1 text-blue-700">
                                <li>option_e</li>
                                <li>correct_answer</li>
                                <li>explanation</li>
                            </ul>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mt-3">
                            <p class="text-yellow-700 text-xs">
                                <strong>⚠️ Catatan:</strong> Mohon pastikan semua mata pelajaran sudah ada di database, atau akan dibuat otomatis.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md border border-gray-200">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <span>📤</span> Upload File Excel
                        </h3>
                    </div>
                    <form action="{{ route('admin.questions.import') }}" method="POST" enctype="multipart/form-data" class="p-6">
                        @csrf

                        <!-- Drag & Drop Area -->
                        <div class="mb-6">
                            <label for="file" class="block text-sm font-medium text-gray-700 mb-3">
                                File Excel <span class="text-red-500">*</span>
                            </label>
                            <div id="dropZone" class="relative border-2 border-dashed border-blue-300 rounded-lg p-8 bg-blue-50 cursor-pointer hover:bg-blue-100 transition hover:border-blue-500">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-blue-400 mb-3" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v12a4 4 0 01-4 4H12a4 4 0 01-4-4V12a4 4 0 014-4h16m8 0l4 4m-4-4v8m0-8l-4 4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <p class="text-lg font-semibold text-blue-900 mb-1">Drag & drop file di sini</p>
                                    <p class="text-sm text-blue-700 mb-4">atau klik untuk memilih file</p>
                                    <p class="text-xs text-blue-600">Format: .xlsx | Ukuran maksimal: 150MB</p>
                                </div>
                                <input id="file" name="file" type="file" accept=".xlsx,.xls" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            </div>
                            <p id="fileName" class="mt-2 text-sm text-gray-600"></p>
                            @error('file')<p class="text-red-500 text-sm mt-2">{{ $message }}</p>@enderror
                        </div>

                        <!-- Options -->
                        <div class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="update_existing" value="1" class="w-4 h-4 rounded border-gray-300 text-blue-600 cursor-pointer">
                                <span class="text-sm text-gray-700">
                                    <strong>Update soal yang sudah ada</strong><br>
                                    <span class="text-xs text-gray-600">Jika ada perubahan data, akan diupdate otomatis</span>
                                </span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 transition shadow-md">
                            🚀 Import Soal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('file');
        const fileName = document.getElementById('fileName');

        // Drag over
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-blue-500', 'bg-blue-100');
        });

        // Drag leave
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-100');
        });

        // Drop
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-100');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                updateFileName();
            }
        });

        // File change
        fileInput.addEventListener('change', updateFileName);

        function updateFileName() {
            if (fileInput.files.length > 0) {
                const name = fileInput.files[0].name;
                const size = (fileInput.files[0].size / 1024 / 1024).toFixed(2);
                fileName.textContent = `✓ ${name} (${size}MB)`;
                fileName.classList.remove('text-gray-600');
                fileName.classList.add('text-green-600', 'font-semibold');
            }
        }

        // Click zone
        dropZone.addEventListener('click', () => fileInput.click());
    </script>
@endsection
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
