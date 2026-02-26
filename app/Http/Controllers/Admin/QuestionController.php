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

        $subjects = Subject::orderBy('name')->get();
        $questions = $query->paginate(15);

        return view('admin.questions.index', compact('questions', 'subjects'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create()
    {
        $subjects = Subject::orderBy('name')->get();
        return view('admin.questions.create', compact('subjects'));
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(StoreQuestionRequest $request)
    {
        $question = QuestionService::createQuestion($request->validated());

        return redirect()->route('admin.questions.index')
            ->with('success', 'Question created successfully');
    }

    /**
     * Display the specified question.
     */
    public function show(Question $question)
    {
        return view('admin.questions.show', compact('question'));
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(Question $question)
    {
        $subjects = Subject::orderBy('name')->get();
        return view('admin.questions.edit', compact('question', 'subjects'));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(UpdateQuestionRequest $request, Question $question)
    {
        QuestionService::updateQuestion($question, $request->validated());

        return redirect()->route('admin.questions.index')
            ->with('success', 'Question updated successfully');
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy(Question $question)
    {
        QuestionService::deleteQuestion($question);

        return redirect()->route('admin.questions.index')
            ->with('success', 'Question deleted successfully');
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
            'failure_count' => $importer->failureCount,
            'errors' => $errors,
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
                ->with('error', 'No questions selected');
        }

        try {
            // Delete questions and their images
            $deleted = 0;
            foreach ($questionIds as $id) {
                $question = Question::find($id);
                if ($question) {
                    // Delete image if exists
                    QuestionService::deleteImageIfExists($question->question_image);
                    $question->delete();
                    $deleted++;
                }
            }

            return redirect()->route('admin.questions.index')
                ->with('success', "$deleted question(s) deleted successfully");
        } catch (\Exception $e) {
            return redirect()->route('admin.questions.index')
                ->with('error', 'Failed to delete questions: ' . $e->getMessage());
        }
    }

    /**
     * Delete all questions
     */
    public function deleteAllQuestions()
    {
        try {
            $questions = Question::all();
            $count = $questions->count();

            foreach ($questions as $question) {
                // Delete image if exists
                QuestionService::deleteImageIfExists($question->question_image);
                $question->delete();
            }

            return redirect()->route('admin.questions.index')
                ->with('success', "All {$count} questions have been permanently deleted.");
        } catch (\Exception $e) {
            return redirect()->route('admin.questions.index')
                ->with('error', 'Error deleting questions: ' . $e->getMessage());
        }
    }
}
