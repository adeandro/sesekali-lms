@extends('layouts.app')

@section('title', 'Question Management - SesekaliCBT')

@section('page-title', 'Question Management')

@section('content')
    <div>
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Question Management</h2>
            <div class="space-x-2">
                <a href="{{ route('admin.questions.create') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    + Add Question
                </a>
                <a href="{{ route('admin.questions.importForm') }}" class="inline-block px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    📥 Import
                </a>
                <a href="{{ route('admin.questions.export', request()->query()) }}" class="inline-block px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    📤 Export
                </a>
                <button type="button" id="bulkDeleteBtn" class="inline-block px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition" style="display: none;">
                    🗑 Delete Selected
                </button>
            </div>
        </div>

        @if ($message = Session::get('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex justify-between">
                <span>{{ $message }}</span>
                <button type="button" class="text-green-800 hover:text-green-600" onclick="this.parentElement.style.display='none';">×</button>
            </div>
        @endif

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form action="{{ route('admin.questions.index') }}" method="GET" class="grid grid-cols-5 gap-4">
                <div>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search question or topic..." 
                        value="{{ request('search') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                <div>
                    <select name="subject" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="jenjang" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Kelas</option>
                        <option value="10" {{ request('jenjang') == '10' ? 'selected' : '' }}>10</option>
                        <option value="11" {{ request('jenjang') == '11' ? 'selected' : '' }}>11</option>
                        <option value="12" {{ request('jenjang') == '12' ? 'selected' : '' }}>12</option>
                    </select>
                </div>
                <div>
                    <select name="difficulty" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Levels</option>
                        <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
                <div>
                    <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="multiple_choice" {{ request('type') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                        <option value="essay" {{ request('type') == 'essay' ? 'selected' : '' }}>Essay</option>
                    </select>
                </div>
                <button type="submit" class="col-span-1 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Filter
                </button>
            </form>
        </div>

        <!-- Questions Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <form id="bulkDeleteForm" action="{{ route('admin.questions.bulkDelete') }}" method="POST">
                @csrf
                @method('DELETE')
                <table class="min-w-full">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">
                                <input type="checkbox" id="selectAllCheckbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Topic</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Subject</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Kelas</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Type</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Difficulty</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Preview</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($questions as $question)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm">
                                    <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" class="question-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                </td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $question->topic }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $question->subject->name }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-block px-2 py-1 rounded text-xs font-semibold bg-indigo-100 text-indigo-800">
                                    {{ $question->jenjang ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $question->question_type === 'multiple_choice' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ str_replace('_', ' ', ucfirst($question->question_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ match($question->difficulty_level) {
                                    'easy' => 'bg-green-100 text-green-800',
                                    'medium' => 'bg-yellow-100 text-yellow-800',
                                    'hard' => 'bg-red-100 text-red-800',
                                } }}">
                                    {{ ucfirst($question->difficulty_level) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ substr($question->question_text, 0, 50) }}...</td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <a href="{{ route('admin.questions.edit', $question) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                                <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" style="display: inline;" onclick="return confirm('Delete question?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                No questions found
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </form>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $questions->links() }}
        </div>
    </div>

    <script>
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const questionCheckboxes = document.querySelectorAll('.question-checkbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        // Toggle all checkboxes when select all is clicked
        selectAllCheckbox.addEventListener('change', () => {
            questionCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateBulkDeleteButton();
        });

        // Update select all checkbox state when individual checkboxes change
        questionCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const allChecked = Array.from(questionCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(questionCheckboxes).some(cb => cb.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
                
                updateBulkDeleteButton();
            });
        });

        // Update bulk delete button visibility
        function updateBulkDeleteButton() {
            const checkedCount = Array.from(questionCheckboxes).filter(cb => cb.checked).length;
            if (checkedCount > 0) {
                bulkDeleteBtn.style.display = 'inline-block';
            } else {
                bulkDeleteBtn.style.display = 'none';
            }
        }

        // Handle bulk delete button click
        bulkDeleteBtn.addEventListener('click', () => {
            const checkedCount = Array.from(questionCheckboxes).filter(cb => cb.checked).length;
            if (confirm(`Delete ${checkedCount} selected question(s)? This action cannot be undone.`)) {
                bulkDeleteForm.submit();
            }
        });
    </script>
@endsection
