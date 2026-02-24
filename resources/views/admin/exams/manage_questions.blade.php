@extends('layouts.app')

@section('title', 'Manage Questions - SesekaliCBT')

@section('content')
    <div>
        <div class="mb-8">
            <a href="{{ route('admin.exams.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Exams</a>
            <h2 class="text-3xl font-bold text-gray-900 mt-2">Manage Questions: {{ $exam->title }}</h2>
        </div>

        @if ($message = Session::get('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex justify-between">
                <span>{{ $message }}</span>
                <button type="button" class="text-green-800 hover:text-green-600" onclick="this.parentElement.style.display='none';">×</button>
            </div>
        @endif

        @if ($message = Session::get('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex justify-between">
                <span>{{ $message }}</span>
                <button type="button" class="text-red-800 hover:text-red-600" onclick="this.parentElement.style.display='none';">×</button>
            </div>
        @endif

        <div class="grid grid-cols-3 gap-6 mb-8">
            <!-- Exam Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Exam Info</h3>
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="font-medium text-gray-700">Duration:</dt>
                        <dd class="text-gray-600">{{ $exam->duration_minutes }} minutes</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Required Questions:</dt>
                        <dd class="text-gray-600">{{ $exam->total_questions }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Status:</dt>
                        <dd>
                            <span class="px-2 py-1 rounded-full text-white text-xs font-semibold 
                                {{ $exam->status === 'published' ? 'bg-green-500' : ($exam->status === 'finished' ? 'bg-gray-500' : 'bg-yellow-500') }}">
                                {{ ucfirst($exam->status) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Current Status -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Status</h3>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $questionCount }}/{{ $exam->total_questions }}</div>
                    <p class="text-gray-600 text-sm mt-2">Questions Added</p>
                    @if($exam->canPublish())
                        <p class="text-green-600 text-sm mt-2">✓ Ready to publish</p>
                    @else
                        <p class="text-red-600 text-sm mt-2">⚠️ Need {{ $exam->total_questions - $questionCount }} more question(s)</p>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.exams.edit', $exam) }}" class="block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center text-sm">
                        ✏️ Edit Exam
                    </a>
                    @if($exam->status !== 'published' && $exam->canPublish())
                        <form action="{{ route('admin.exams.publish', $exam) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">
                                ✓ Publish Exam
                            </button>
                        </form>
                    @endif
                    @if($exam->status === 'published')
                        <form action="{{ route('admin.exams.set-to-draft', $exam) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition text-sm">
                                ↺ Set to Draft
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <!-- Available Questions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Available Questions</h3>
                <p class="text-sm text-gray-600 mb-4">{{ $availableQuestions->total() }} questions from {{ $exam->subject->name }}</p>

                <!-- Search Bar -->
                <div class="mb-4">
                    <input 
                        type="text" 
                        id="questionSearch" 
                        placeholder="🔍 Search questions by topic or text..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <form action="{{ route('admin.exams.attach-questions', $exam) }}" method="POST" id="addQuestionsForm">
                    @csrf
                    
                    <!-- Bulk Action Buttons -->
                    <div class="mb-4 flex gap-2 flex-wrap">
                        <label class="flex items-center px-3 py-2 bg-gray-100 rounded-lg cursor-pointer hover:bg-gray-200 transition text-sm">
                            <input type="checkbox" id="selectAllCheckbox" class="mr-2">
                            <span>Select All</span>
                        </label>
                        <button type="button" id="addAllBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                            ✓ Add All Selected
                        </button>
                        <button type="button" id="autoAddBtn" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium">
                            ⚡ Auto Add Required
                        </button>
                    </div>

                    <div class="space-y-3 max-h-96 overflow-y-auto mb-4" id="questionsContainer">
                        @forelse($availableQuestions as $question)
                            <label class="question-item flex items-start p-3 border border-gray-200 rounded-lg hover:bg-blue-50 cursor-pointer" data-topic="{{ strtolower($question->topic) }}" data-text="{{ strtolower($question->question_text) }}">
                                <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" class="mt-1 text-blue-600 question-checkbox">
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $question->topic }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ Str::limit($question->question_text, 80) }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Difficulty: <span class="px-2 py-0.5 bg-gray-100 rounded text-xs">{{ ucfirst($question->difficulty_level) }}</span>
                                    </p>
                                </div>
                            </label>
                        @empty
                            <p class="text-gray-600 text-sm">No more questions available from this subject.</p>
                        @endforelse
                    </div>

                    @if($availableQuestions->total() > 0)
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm" id="addBtn">
                            Add Selected Questions
                        </button>
                    @endif
                </form>

                <!-- Pagination -->
                @if($availableQuestions->total() > 0)
                    <div class="mt-4 text-sm">
                        {{ $availableQuestions->links() }}
                    </div>
                @endif
            </div>

            <!-- Attached Questions -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Attached Questions</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $questionCount }} of {{ $exam->total_questions }} questions</p>
                    </div>
                    @if($questionCount > 0)
                        <form action="{{ route('admin.exams.detach-all-questions', $exam) }}" method="POST" style="display:inline;" onsubmit="return confirmBulkDelete();">
                            @csrf
                            <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-xs font-medium">
                                🗑️ Remove All
                            </button>
                        </form>
                    @endif
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($attachedQuestions as $question)
                        <div class="p-3 border border-gray-200 rounded-lg bg-gray-50">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $question->topic }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ Str::limit($question->question_text, 80) }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Difficulty: <span class="px-2 py-0.5 bg-gray-100 rounded text-xs">{{ ucfirst($question->difficulty_level) }}</span>
                                    </p>
                                </div>
                                <form action="{{ route('admin.exams.detach-question', $exam) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="question_id" value="{{ $question->id }}">
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs" onclick="return confirm('Remove this question?')">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-600 text-sm">No questions attached yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        // Total questions needed
        const totalQuestionsNeeded = {{ $exam->total_questions }};
        const currentQuestionCount = {{ $questionCount }};
        const questionsNeeded = Math.max(0, totalQuestionsNeeded - currentQuestionCount);

        // Search functionality
        document.getElementById('questionSearch').addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const questions = document.querySelectorAll('.question-item');
            
            questions.forEach(question => {
                const topic = question.getAttribute('data-topic');
                const text = question.getAttribute('data-text');
                
                if (topic.includes(searchTerm) || text.includes(searchTerm)) {
                    question.style.display = '';
                } else {
                    question.style.display = 'none';
                }
            });
        });

        // Select All checkbox
        document.getElementById('selectAllCheckbox').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.question-item:not([style*="display: none"]) .question-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Add All Selected button
        document.getElementById('addAllBtn').addEventListener('click', function(e) {
            e.preventDefault();
            const checkboxes = document.querySelectorAll('.question-item:not([style*="display: none"]) .question-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            
            // Show feedback and scroll to submit button
            alert(`Selected ${checkboxes.length} questions. Click "Add Selected Questions" to proceed.`);
            document.getElementById('addBtn').scrollIntoView({ behavior: 'smooth' });
        });

        // Auto Add button - submits to backend auto-add endpoint
        document.getElementById('autoAddBtn').addEventListener('click', function(e) {
            e.preventDefault();
            
            if (questionsNeeded <= 0) {
                alert('✓ Exam sudah memiliki semua soal yang diperlukan!');
                return;
            }
            
            // Show confirmation modal
            if (confirm('Apakah Anda yakin ingin menambahkan soal secara otomatis sesuai kuota ujian? Sistem akan memilih soal secara acak dari soal yang tersedia.')) {
                // Submit to auto-add endpoint
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('admin.exams.auto-add-questions', $exam) }}';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('input[name="_token"]').value;
                
                form.appendChild(csrfInput);
                document.body.appendChild(form);
                form.submit();
            }
        });

        // Bulk delete confirmation
        function confirmBulkDelete() {
            return confirm('🚨 DANGER ZONE 🚨\n\nApakah Anda yakin ingin menghapus SEMUA soal dari ujian ini?\n\nTindakan ini tidak dapat dibatalkan!');
        }

        // Add button validation
        document.getElementById('addBtn').addEventListener('click', function(e) {
            const checkboxes = document.querySelectorAll('input[name="question_ids[]"]');
            const checked = Array.from(checkboxes).filter(cb => cb.checked);
            
            if (checked.length === 0) {
                e.preventDefault();
                alert('Please select at least one question');
            }
        });
    </script>
@endsection
