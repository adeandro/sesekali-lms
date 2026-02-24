<?php

namespace App\Http\Controllers\Admin;

use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Services\ExamService;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * Display a listing of exams.
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'subject' => $request->input('subject'),
            'status' => $request->input('status'),
        ];

        $exams = ExamService::getExamsList($filters);
        $subjects = Subject::orderBy('name')->get();

        return view('admin.exams.index', compact('exams', 'subjects'));
    }

    /**
     * Show the form for creating a new exam.
     */
    public function create()
    {
        $subjects = Subject::orderBy('name')->get();
        return view('admin.exams.create', compact('subjects'));
    }

    /**
     * Store a newly created exam in storage.
     */
    public function store(StoreExamRequest $request)
    {
        ExamService::createExam($request->validated());

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam created successfully');
    }

    /**
     * Show the form for editing the specified exam.
     */
    public function edit(Exam $exam)
    {
        if (!$exam->canEdit()) {
            return redirect()->route('admin.exams.index')
                ->with('error', 'Cannot edit a finished exam');
        }

        $subjects = Subject::orderBy('name')->get();
        return view('admin.exams.edit', compact('exam', 'subjects'));
    }

    /**
     * Update the specified exam in storage.
     */
    public function update(UpdateExamRequest $request, Exam $exam)
    {
        if (!$exam->canEdit()) {
            return redirect()->route('admin.exams.index')
                ->with('error', 'Cannot edit a finished exam');
        }

        ExamService::updateExam($exam, $request->validated());

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam updated successfully');
    }

    /**
     * Remove the specified exam from storage.
     */
    public function destroy(Exam $exam)
    {
        $exam->delete();

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam deleted successfully');
    }

    /**
     * Show the manage questions form.
     */
    public function manageQuestions(Exam $exam)
    {
        if ($exam->status === 'finished') {
            return redirect()->route('admin.exams.index')
                ->with('error', 'Cannot modify a finished exam');
        }

        $availableQuestions = ExamService::getAvailableQuestions($exam);
        $attachedQuestions = $exam->questions()->get();
        $questionCount = $attachedQuestions->count();

        return view('admin.exams.manage_questions', compact(
            'exam',
            'availableQuestions',
            'attachedQuestions',
            'questionCount'
        ));
    }

    /**
     * Attach questions to exam.
     */
    public function attachQuestions(Request $request, Exam $exam)
    {
        if ($exam->status === 'finished') {
            return redirect()->back()
                ->with('error', 'Cannot modify a finished exam');
        }

        $validated = $request->validate([
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'exists:questions,id',
        ]);

        try {
            ExamService::attachQuestions($exam, $validated['question_ids']);
            return redirect()->back()
                ->with('success', count($validated['question_ids']) . ' question(s) added to exam');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Detach a question from exam.
     */
    public function detachQuestion(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
        ]);

        ExamService::detachQuestion($exam, $validated['question_id']);

        return redirect()->back()
            ->with('success', 'Question removed from exam');
    }

    /**
     * Auto add required number of questions to exam.
     */
    public function autoAddQuestions(Request $request, Exam $exam)
    {
        if ($exam->status === 'finished') {
            return redirect()->back()
                ->with('error', 'Cannot modify a finished exam');
        }

        try {
            // Get current question count
            $currentCount = $exam->questions()->count();
            $requiredCount = $exam->total_questions;
            $questionsNeeded = $requiredCount - $currentCount;

            if ($questionsNeeded <= 0) {
                return redirect()->back()
                    ->with('error', 'Exam already has all required questions');
            }

            // Get available questions (not yet attached) using random order
            $availableQuestions = Question::where('subject_id', $exam->subject_id)
                ->where('jenjang', $exam->jenjang)
                ->whereNotIn('questions.id', $exam->questions()->select('questions.id')->pluck('questions.id'))
                ->inRandomOrder()
                ->limit($questionsNeeded)
                ->pluck('id')
                ->toArray();

            if (count($availableQuestions) === 0) {
                return redirect()->back()
                    ->with('error', 'No available questions to add');
            }

            // Attach the questions
            ExamService::attachQuestions($exam, $availableQuestions);

            return redirect()->back()
                ->with('success', count($availableQuestions) . ' question(s) automatically added to exam');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Detach all questions from exam.
     */
    public function detachAllQuestions(Request $request, Exam $exam)
    {
        if ($exam->status === 'finished') {
            return redirect()->back()
                ->with('error', 'Cannot modify a finished exam');
        }

        try {
            $count = $exam->questions()->count();

            if ($count === 0) {
                return redirect()->back()
                    ->with('error', 'No questions to remove');
            }

            // Detach all questions
            $exam->questions()->detach();

            return redirect()->back()
                ->with('success', $count . ' question(s) removed from exam');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Publish an exam.
     */
    public function publish(Exam $exam)
    {
        try {
            ExamService::publishExam($exam);
            return redirect()->route('admin.exams.index')
                ->with('success', 'Exam published successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Set exam to draft.
     */
    public function setToDraft(Exam $exam)
    {
        try {
            ExamService::setToDraft($exam);
            return redirect()->route('admin.exams.index')
                ->with('success', 'Exam set to draft successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
