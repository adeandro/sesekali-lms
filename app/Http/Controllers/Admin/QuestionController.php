<?php

namespace App\Http\Controllers\Admin;

use App\Models\Question;
use App\Models\Subject;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Http\Requests\ImportQuestionRequest;
use App\Imports\QuestionImport;
use App\Exports\QuestionExport;
use App\Services\QuestionService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class QuestionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of questions.
     */
    public function index(Request $request)
    {
        $query = Question::with('subject');

        // Scoping for Teacher
        if (auth()->user()->role === 'teacher') {
            $mySubjectIds = auth()->user()->subjects->pluck('id');
            $query->whereIn('subject_id', $mySubjectIds);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('question_text', 'like', "%{$search}%")
                    ->orWhere('topic', 'like', "%{$search}%");
            });
        }

        // Filter by subject
        if ($request->filled('subject')) {
            $query->where('subject_id', $request->input('subject'));
        }

        // Filter by jenjang
        if ($request->filled('jenjang')) {
            $query->where('jenjang', $request->input('jenjang'));
        }

        // Filter by difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty_level', $request->input('difficulty'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('question_type', $request->input('type'));
        }

        if (auth()->user()->role === 'teacher') {
            $subjects = auth()->user()->subjects;
        } else {
            $subjects = Subject::orderBy('name')->get();
        }
        $questions = $query->paginate(15)->appends($request->query());

        return view('admin.questions.index', compact('questions', 'subjects'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create()
    {
        if (auth()->user()->role === 'teacher') {
            $subjects = auth()->user()->subjects;
        } else {
            $subjects = Subject::orderBy('name')->get();
        }
        return view('admin.questions.create', compact('subjects'));
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(StoreQuestionRequest $request)
    {
        $question = QuestionService::createQuestion($request->validated());

        return redirect()->route('admin.questions.index')
            ->with('success', 'Soal berhasil ditambahkan ke bank soal');
    }

    /**
     * Display the specified question.
     */
    public function show(Question $question)
    {
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $question->subject_id)) {
            abort(403, 'Unauthorized.');
        }

        return view('admin.questions.show', compact('question'));
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(Question $question)
    {
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $question->subject_id)) {
            abort(403, 'Unauthorized access to this question.');
        }

        if (auth()->user()->role === 'teacher') {
            $subjects = auth()->user()->subjects;
        } else {
            $subjects = Subject::orderBy('name')->get();
        }
        return view('admin.questions.edit', compact('question', 'subjects'));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(UpdateQuestionRequest $request, Question $question)
    {
        QuestionService::updateQuestion($question, $request->validated());

        return redirect()->route('admin.questions.index')
            ->with('success', 'Perubahan soal berhasil disimpan');
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy(Question $question)
    {
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $question->subject_id)) {
            abort(403, 'Unauthorized.');
        }

        QuestionService::deleteQuestion($question);
        // Force delete permanently (including soft-deleted records)
        $question->forceDelete();

        return redirect()->route('admin.questions.index')
            ->with('success', 'Soal telah dihapus secara permanen');
    }

    /**
     * Show import form.
     */
    public function importForm()
    {
        return view('admin.questions.import');
    }

    /**
     * Import questions from Excel.
     */
    public function import(ImportQuestionRequest $request)
    {
        $importer = new QuestionImport();
        Excel::import($importer, $request->file('file'));

        // Restructure errors to match view expectations
        $errors = [];
        foreach ($importer->errors as $error) {
            $rowNum = $error['row'];
            $errorMessages = array_values($error['errors']); // Get just the error message strings
            $errors[$rowNum] = $errorMessages;
        }

        return redirect()->route('admin.questions.importResult')->with('import_data', [
            'success_count' => $importer->successCount,
            'updated_count' => $importer->updatedCount,
            'skipped_count' => $importer->skippedCount,
            'failure_count' => $importer->failureCount,
            'errors' => $errors,
            'skipped' => $importer->skipped,
            'updated' => $importer->updated,
        ]);
    }

    /**
     * Show import result.
     */
    public function importResult()
    {
        $data = session('import_data');

        if (!$data) {
            return redirect()->route('admin.questions.importForm');
        }

        return view('admin.questions.import_result', $data);
    }

    /**
     * Export questions to Excel.
     */
    public function export(Request $request)
    {
        $query = Question::with('subject');

        // Scoping for Teacher
        if (auth()->user()->role === 'teacher') {
            $mySubjectIds = auth()->user()->subjects->pluck('id');
            $query->whereIn('subject_id', $mySubjectIds);
        }

        // Apply same filters as index
        if ($request->filled('subject')) {
            $query->where('subject_id', $request->input('subject'));
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty_level', $request->input('difficulty'));
        }

        if ($request->filled('type')) {
            $query->where('question_type', $request->input('type'));
        }

        return Excel::download(
            new QuestionExport($query->get()),
            'questions-' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Delete multiple questions.
     */
    public function bulkDelete(Request $request)
    {
        $questionIds = $request->input('question_ids', []);

        if (empty($questionIds)) {
            return redirect()->route('admin.questions.index')
                ->with('error', 'Tidak ada soal yang dipilih untuk dihapus');
        }

        try {
            // Delete questions and their images permanently
            $deleted = 0;
            foreach ($questionIds as $id) {
                $question = Question::withTrashed()->find($id);
                if ($question) {
                    // Security check for Teacher
                    if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $question->subject_id)) {
                        continue;
                    }
                    // Delete all images (question image + all option images)
                    QuestionService::deleteQuestion($question);
                    // Permanently delete (force delete soft deleted records)
                    $question->forceDelete();
                    $deleted++;
                }
            }

            return redirect()->route('admin.questions.index')
                ->with('success', "$deleted soal berhasil dihapus secara massal");
        } catch (\Exception $e) {
            return redirect()->route('admin.questions.index')
                ->with('error', 'Gagal menghapus soal: ' . $e->getMessage());
        }
    }

    /**
     * Delete all questions
     */
    public function deleteAllQuestions()
    {
        try {
            // Get questions including soft deleted ones, scoped for Teacher
            $query = Question::withTrashed();
            if (auth()->user()->role === 'teacher') {
                $mySubjectIds = auth()->user()->subjects->pluck('id');
                $query->whereIn('subject_id', $mySubjectIds);
            }
            $questions = $query->get();
            $count = $questions->count();

            foreach ($questions as $question) {
                // Delete all images (question image + all option images)
                QuestionService::deleteQuestion($question);
                // Permanently delete (force delete soft deleted records too)
                $question->forceDelete();
            }

            return redirect()->route('admin.questions.index')
                ->with('success', "Seluruh soal ({$count}) berhasil dihapus dari sistem.");
        } catch (\Exception $e) {
            return redirect()->route('admin.questions.index')
                ->with('error', 'Gagal menghapus seluruh soal: ' . $e->getMessage());
        }
    }
}
