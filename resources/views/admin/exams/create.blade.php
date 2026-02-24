@extends('layouts.app')

@section('title', 'Buat Ujian - SesekaliCBT')

@section('content')
    <div>
        <div class="mb-8">
            <a href="{{ route('admin.exams.index') }}" class="text-blue-600 hover:text-blue-800">← Kembali ke Ujian</a>
            <h2 class="text-3xl font-bold text-gray-900 mt-2">Buat Ujian</h2>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="font-semibold text-red-800 mb-2">Kesalahan Validasi:</p>
                <ul class="list-disc list-inside text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-8 max-w-4xl">
            <form action="{{ route('admin.exams.store') }}" method="POST" id="examForm">
                @csrf

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Judul <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror" 
                            placeholder="misalnya, Ujian Tengah Semester" required>
                        @error('title')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Mata Pelajaran <span class="text-red-500">*</span>
                        </label>
                        <select id="subject_id" name="subject_id" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('subject_id') border-red-500 @enderror" 
                            required>
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="jenjang" class="block text-sm font-medium text-gray-700 mb-2">
                            Kelas <span class="text-red-500">*</span>
                        </label>
                        <select id="jenjang" name="jenjang" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('jenjang') border-red-500 @enderror" 
                            required>
                            <option value="">Pilih Kelas</option>
                            <option value="10" {{ old('jenjang') == '10' ? 'selected' : '' }}>10</option>
                            <option value="11" {{ old('jenjang') == '11' ? 'selected' : '' }}>11</option>
                            <option value="12" {{ old('jenjang') == '12' ? 'selected' : '' }}>12</option>
                        </select>
                        @error('jenjang')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-2">
                            Durasi (menit) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes') }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('duration_minutes') border-red-500 @enderror" 
                            min="1" max="480" required>
                        @error('duration_minutes')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="total_questions" class="block text-sm font-medium text-gray-700 mb-2">
                            Total Soal <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="total_questions" name="total_questions" value="{{ old('total_questions') }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('total_questions') border-red-500 @enderror" 
                            min="1" max="500" required>
                        @error('total_questions')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                            Waktu Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" id="start_time" name="start_time" 
                            value="@php
                                $oldStart = old('start_time');
                                if ($oldStart && strpos($oldStart, 'T') === false) {
                                    // Convert from Y-m-d H:i to YYYY-MM-DDTHH:mm
                                    echo str_replace(' ', 'T', $oldStart);
                                } else {
                                    echo $oldStart;
                                }
                            @endphp" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('start_time') border-red-500 @enderror" 
                            required>
                        @error('start_time')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                            Waktu Berakhir <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" id="end_time" name="end_time" 
                            value="@php
                                $oldEnd = old('end_time');
                                if ($oldEnd && strpos($oldEnd, 'T') === false) {
                                    // Convert from Y-m-d H:i to YYYY-MM-DDTHH:mm
                                    echo str_replace(' ', 'T', $oldEnd);
                                } else {
                                    echo $oldEnd;
                                }
                            @endphp" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('end_time') border-red-500 @enderror" 
                            required>
                        @error('end_time')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="randomize_questions" name="randomize_questions" value="1"
                            {{ old('randomize_questions') ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="randomize_questions" class="ml-2 block text-sm text-gray-700">
                            Acak Soal (mengurutkan soal secara acak untuk setiap siswa)
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="randomize_options" name="randomize_options" value="1"
                            {{ old('randomize_options') ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="randomize_options" class="ml-2 block text-sm text-gray-700">
                            Acak Pilihan (mengurutkan pilihan jawaban secara acak untuk setiap siswa)
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="show_score_after_submit" name="show_score_after_submit" value="1"
                            {{ old('show_score_after_submit') ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="show_score_after_submit" class="ml-2 block text-sm text-gray-700">
                            Tampilkan Nilai Setelah Kirim (menampilkan hasil segera setelah pengajuan)
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="allow_review_results" name="allow_review_results" value="1"
                            {{ old('allow_review_results') ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="allow_review_results" class="ml-2 block text-sm text-gray-700">
                            Izinkan Peninjauan Hasil (siswa dapat meninjau jawaban dan jawaban yang benar setelah pengajuan)
                        </label>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('status') border-red-500 @enderror"
                        required>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Dipublikasikan</option>
                    </select>
                    @error('status')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Buat Ujian
                    </button>
                    <a href="{{ route('admin.exams.index') }}" class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const examForm = document.getElementById('examForm');
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');

        // Helper function to format datetime-local
        function formatDatetimeLocal(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        // Set default start time to now if empty
        if (!startTimeInput.value) {
            startTimeInput.value = formatDatetimeLocal(new Date());
        }

        // Update end time when start time changes
        startTimeInput.addEventListener('change', () => {
            if (!endTimeInput.value) {
                const startDate = new Date(startTimeInput.value);
                const endDate = new Date(startDate.getTime() + 60 * 60 * 1000); // 1 hour later
                endTimeInput.value = formatDatetimeLocal(endDate);
            }
        });
    </script>
@endsection
