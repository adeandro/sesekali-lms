@extends('layouts.app')

@section('title', 'Add Question - SesekaliCBT')

@section('content')
    <div>
        <div class="mb-8">
            <a href="{{ route('admin.questions.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Questions</a>
            <h2 class="text-3xl font-bold text-gray-900 mt-2">Add New Question</h2>
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

        <div class="bg-white rounded-lg shadow p-8 max-w-4xl">
            <form action="{{ route('admin.questions.store') }}" method="POST" id="questionForm" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Subject <span class="text-red-500">*</span>
                        </label>
                        <select id="subject_id" name="subject_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('subject_id') border-red-500 @enderror" required>
                            <option value="">Select Subject</option>
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
                        <select id="jenjang" name="jenjang" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('jenjang') border-red-500 @enderror" required>
                            <option value="">Pilih Kelas</option>
                            <option value="10" {{ old('jenjang') == '10' ? 'selected' : '' }}>10</option>
                            <option value="11" {{ old('jenjang') == '11' ? 'selected' : '' }}>11</option>
                            <option value="12" {{ old('jenjang') == '12' ? 'selected' : '' }}>12</option>
                        </select>
                        @error('jenjang')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="topic" class="block text-sm font-medium text-gray-700 mb-2">
                            Topic <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="topic" name="topic" value="{{ old('topic') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('topic') border-red-500 @enderror" required>
                        @error('topic')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="difficulty_level" class="block text-sm font-medium text-gray-700 mb-2">
                            Difficulty <span class="text-red-500">*</span>
                        </label>
                        <select id="difficulty_level" name="difficulty_level" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('difficulty_level') border-red-500 @enderror" required>
                            <option value="">Select Level</option>
                            <option value="easy" {{ old('difficulty_level') == 'easy' ? 'selected' : '' }}>Easy</option>
                            <option value="medium" {{ old('difficulty_level') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="hard" {{ old('difficulty_level') == 'hard' ? 'selected' : '' }}>Hard</option>
                        </select>
                        @error('difficulty_level')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="question_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Type <span class="text-red-500">*</span>
                        </label>
                        <select id="question_type" name="question_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('question_type') border-red-500 @enderror" required>
                            <option value="">Select Type</option>
                            <option value="multiple_choice" {{ old('question_type') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                            <option value="essay" {{ old('question_type') == 'essay' ? 'selected' : '' }}>Essay</option>
                        </select>
                        @error('question_type')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label for="question_text" class="block text-sm font-medium text-gray-700 mb-2">
                        Question Text <span class="text-red-500">*</span>
                    </label>
                    <textarea id="question_text" name="question_text" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('question_text') border-red-500 @enderror" required>{{ old('question_text') }}</textarea>
                    @error('question_text')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="mb-6">
                    <label for="question_image" class="block text-sm font-medium text-gray-700 mb-2">
                        Question Image (optional)
                    </label>
                    <input type="file" id="question_image" name="question_image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-gray-500 text-sm mt-1">Supported formats: JPG, PNG, GIF (Max 2MB)</p>
                    <div id="question_image_preview" class="mt-2"></div>
                </div>

                <!-- Multiple Choice Options -->
                <div id="multipleChoiceSection" style="display:none;" class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Answer Options</h3>

                    @foreach(['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d', 'E' => 'option_e'] as $label => $name)
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <label for="{{ $name }}" class="block text-sm font-medium text-gray-900 mb-2">
                                Option {{ $label }} {{ in_array($name, ['option_a', 'option_b', 'option_c', 'option_d']) ? '<span class="text-red-500">*</span>' : '' }}
                            </label>
                            <input type="text" id="{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 mb-3" placeholder="Text for option {{ $label }}">
                            
                            <label for="{{ $name }}_image" class="block text-sm font-medium text-gray-700 mb-2">
                                Image for Option {{ $label }} (optional)
                            </label>
                            <input type="file" id="{{ $name }}_image" name="{{ $name }}_image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-gray-500 text-xs mt-1">Supported formats: JPG, PNG, GIF (Max 2MB)</p>
                            <div id="{{ $name }}_image_preview" class="mt-2"></div>
                        </div>
                    @endforeach

                    <div class="mb-4">
                        <label for="correct_answer" class="block text-sm font-medium text-gray-700 mb-2">
                            Correct Answer <span class="text-red-500">*</span>
                        </label>
                        <select id="correct_answer" name="correct_answer" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Correct Answer</option>
                            <option value="a" {{ old('correct_answer') == 'a' ? 'selected' : '' }}>A</option>
                            <option value="b" {{ old('correct_answer') == 'b' ? 'selected' : '' }}>B</option>
                            <option value="c" {{ old('correct_answer') == 'c' ? 'selected' : '' }}>C</option>
                            <option value="d" {{ old('correct_answer') == 'd' ? 'selected' : '' }}>D</option>
                            <option value="e" {{ old('correct_answer') == 'e' ? 'selected' : '' }}>E</option>
                        </select>
                        @error('correct_answer')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label for="explanation" class="block text-sm font-medium text-gray-700 mb-2">
                        Explanation (optional)
                    </label>
                    <textarea id="explanation" name="explanation" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('explanation') }}</textarea>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Create Question
                    </button>
                    <a href="{{ route('admin.questions.index') }}" class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const questionTypeSelect = document.getElementById('question_type');
        const multipleChoiceSection = document.getElementById('multipleChoiceSection');
        const optionInputs = document.querySelectorAll('[name^="option_"]:not([name*="_image"])');
        const correctAnswerSelect = document.getElementById('correct_answer');

        function toggleMultipleChoice() {
            const isMultipleChoice = questionTypeSelect.value === 'multiple_choice';
            multipleChoiceSection.style.display = isMultipleChoice ? 'block' : 'none';
            
            // Update required attributes
            optionInputs.forEach((input, index) => {
                input.required = isMultipleChoice && index < 4;
            });
            correctAnswerSelect.required = isMultipleChoice;
        }

        // Image preview functionality
        function setupImagePreview(inputId, previewId) {
            const inputElement = document.getElementById(inputId);
            const previewElement = document.getElementById(previewId);

            if (inputElement) {
                inputElement.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            previewElement.innerHTML = `<img src="${event.target.result}" alt="Preview" class="max-w-xs h-auto rounded-lg border border-gray-300">`;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewElement.innerHTML = '';
                    }
                });
            }
        }

        // Setup previews for question image
        setupImagePreview('question_image', 'question_image_preview');

        // Setup previews for option images
        ['option_a', 'option_b', 'option_c', 'option_d', 'option_e'].forEach(option => {
            setupImagePreview(`${option}_image`, `${option}_image_preview`);
        });

        questionTypeSelect.addEventListener('change', toggleMultipleChoice);
        toggleMultipleChoice();
    </script>
@endsection
