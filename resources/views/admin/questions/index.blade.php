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
                <form id="deleteAllForm" action="{{ route('admin.questions.deleteAll') }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmDeleteAllQuestions()" class="inline-block px-4 py-2 bg-red-800 text-white rounded-lg hover:bg-red-900 transition font-bold">
                        🗑️ Hapus Semua Soal
                    </button>
                </form>
            </div>
        </div>

        @if ($message = Session::get('success'))
            <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-lg shadow-sm flex items-start justify-between animate-slideDown">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-green-800">Berhasil!</p>
                        <p class="text-sm text-green-700 mt-1">{{ $message }}</p>
                    </div>
                </div>
                <button type="button" class="text-green-500 hover:text-green-700 transition" onclick="this.parentElement.remove();">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <script>
                setTimeout(() => {
                    const alert = document.querySelector('[class*="animate-slideDown"]');
                    if (alert) alert.remove();
                }, 5000);
            </script>
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
                                <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" style="display: inline;" class="delete-single-form">
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
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Pertanyaan Terpilih?',
                text: `Apakah Anda yakin ingin menghapus ${checkedCount} pertanyaan yang dipilih? Tindakan ini tidak dapat dibatalkan.`,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                showCancelButton: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    bulkDeleteForm.submit();
                }
            });
        });

        // Handle individual delete form submissions
        document.querySelectorAll('.delete-single-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitForm = this;
                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus Pertanyaan?',
                    text: 'Apakah Anda yakin ingin menghapus pertanyaan ini? Tindakan ini tidak dapat dibatalkan.',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitForm.submit();
                    }
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDeleteAllQuestions() {
            Swal.fire({
                title: '⚠️ PERHATIAN SERIUS!',
                html: `
                    <div class="text-left">
                        <p class="font-bold text-red-700 mb-4">Hapus SEMUA data soal? Tindakan ini TIDAK BISA DIBATALKAN.</p>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded mb-4">
                            <p class="font-semibold text-red-900 mb-2">Data yang akan dihapus:</p>
                            <ul class="text-sm text-red-800 space-y-1">
                                <li>✗ Semua soal dan konten</li>
                                <li>✗ Semua gambar/file soal</li>
                                <li>✗ Semua jawaban yang telah dibuat</li>
                                <li>✗ Semua exam yang menggunakan soal ini</li>
                                <li>✗ Data tidak dapat dipulihkan setelah penghapusan</li>
                            </ul>
                        </div>
                        <p class="text-sm text-gray-700 mb-4">Jika Anda yakin ingin melanjutkan, ketik <span class="font-mono font-bold bg-gray-200 px-2 py-1 rounded">HAPUS SEMUA</span> di bawah:</p>
                        <input type="text" id="confirmTextQuestions" placeholder="Ketik: HAPUS SEMUA" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#991b1b',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus Semua',
                cancelButtonText: 'Batal',
                didOpen: () => {
                    document.getElementById('confirmTextQuestions').focus();
                },
                preConfirm: () => {
                    const input = document.getElementById('confirmTextQuestions').value;
                    if (input !== 'HAPUS SEMUA') {
                        Swal.showValidationMessage('Ketik "HAPUS SEMUA" dengan benar untuk konfirmasi');
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show final confirmation
                    Swal.fire({
                        title: 'Konfirmasi Terakhir',
                        html: '<p class="text-gray-800">Ini adalah konfirmasi terakhir. Klik "Hapus Selamanya" untuk menghapus semua soal.</p>',
                        icon: 'error',
                        showCancelButton: true,
                        confirmButtonColor: '#7f1d1d',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Hapus Selamanya',
                        cancelButtonText: 'Batal',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'inline-block px-6 py-2 bg-red-900 text-white rounded-lg font-bold hover:bg-red-950 transition',
                            cancelButton: 'inline-block px-6 py-2 bg-gray-500 text-white rounded-lg font-bold hover:bg-gray-600 transition ml-2'
                        }
                    }).then((finalResult) => {
                        if (finalResult.isConfirmed) {
                            document.getElementById('deleteAllForm').submit();
                        }
                    });
                }
            });
        }
    </script>

    <style>
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-slideDown {
            animation: slideDown 0.3s ease-out;
        }
    </style>
@endsection
