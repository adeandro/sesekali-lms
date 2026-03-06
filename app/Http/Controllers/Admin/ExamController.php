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

        // Scoping for Teacher
        if (auth()->user()->role === 'teacher') {
            $mySubjectIds = auth()->user()->subjects->pluck('id')->toArray();
            
            // If filtering by a specific subject, ensure it's in their authorized list
            if ($request->filled('subject')) {
                $requestedSubjectId = $request->input('subject');
                if (in_array($requestedSubjectId, $mySubjectIds)) {
                    $filters['subject'] = [$requestedSubjectId];
                } else {
                    // Unauthorized subject requested, fallback to all their subjects
                    $filters['subject'] = $mySubjectIds;
                }
            } else {
                // No specific filter, show all authorized subjects
                $filters['subject'] = $mySubjectIds;
            }
        }

        $exams = ExamService::getExamsList($filters);
        
        if (auth()->user()->role === 'teacher') {
            $subjects = auth()->user()->subjects;
        } else {
            $subjects = Subject::orderBy('name')->get();
        }

        return view('admin.exams.index', compact('exams', 'subjects'));
    }

    /**
     * Show the form for creating a new exam.
     */
    public function create()
    {
        if (auth()->user()->role === 'teacher') {
            $subjects = auth()->user()->subjects;
        } else {
            $subjects = Subject::orderBy('name')->get();
        }
        return view('admin.exams.create', compact('subjects'));
    }

    /**
     * Store a newly created exam in storage.
     */
    public function store(StoreExamRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        
        ExamService::createExam($data);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam created successfully');
    }

    /**
     * Display the specified exam.
     */
    public function show(Exam $exam)
    {
        return redirect()->route('admin.exams.edit', $exam);
    }

    /**
     * Show the form for editing the specified exam.
     */
    public function edit(Exam $exam)
    {
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            abort(403, 'Unauthorized access to this exam.');
        }

        if (!$exam->canEdit()) {
            return redirect()->route('admin.exams.index')
                ->with('error', 'Cannot edit a finished exam');
        }

        if (auth()->user()->role === 'teacher') {
            $subjects = auth()->user()->subjects;
        } else {
            $subjects = Subject::orderBy('name')->get();
        }
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
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            abort(403, 'Unauthorized.');
        }

        // Force delete permanently (Exam model uses SoftDeletes)
        $exam->forceDelete();

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam deleted successfully');
    }

    /**
     * Show the manage questions form.
     */
    public function manageQuestions(Exam $exam)
    {
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            abort(403, 'Unauthorized access.');
        }

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
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            abort(403, 'Unauthorized.');
        }

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
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            abort(403, 'Unauthorized.');
        }

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
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            abort(403, 'Unauthorized.');
        }

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
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            abort(403, 'Unauthorized.');
        }

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
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            abort(403, 'Unauthorized.');
        }

        try {
            ExamService::publishExam($exam);

            // Auto-generate token when exam is published
            $this->generateTokenForExam($exam);

            return redirect()->route('admin.exams.index')
                ->with('success', 'Ujian dipublikasikan dan token telah dibuat');
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
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            abort(403, 'Unauthorized.');
        }

        try {
            ExamService::setToDraft($exam);

            // Clear token when exam is unpublished
            $exam->update([
                'token' => null,
                'token_last_updated' => null,
            ]);

            return redirect()->route('admin.exams.index')
                ->with('success', 'Ujian dikembalikan ke draft dan token dihapus');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Generate a new token for exam and update timestamp.
     * This is the internal method called by publish() and refreshToken()
     */
    private function generateTokenForExam(Exam $exam): void
    {
        // Generate random 6-character alphanumeric token
        $token = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

        $exam->update([
            'token' => $token,
            'token_last_updated' => now(),
        ]);
    }

    /**
     * Generate a new token for exam (Admin endpoint).
     */
    public function generateToken(Exam $exam)
    {
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized subject access'], 403);
        }

        try {
            if ($exam->status !== 'published') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ujian harus dipublikasikan terlebih dahulu untuk membuat token.',
                ], 400);
            }

            $this->generateTokenForExam($exam);

            return response()->json([
                'success' => true,
                'message' => 'Token berhasil dibuat.',
                'token' => $exam->token,
                'token_last_updated' => $exam->token_last_updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat token: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh token manually (with immediate effect).
     */
    public function refreshToken(Exam $exam)
    {
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            abort(403, 'Unauthorized access.');
        }

        try {
            if ($exam->status !== 'published') {
                return redirect()->route('admin.tokens.index')
                    ->with('error', 'Hanya ujian yang dipublikasikan yang dapat diperbarui tokennya.');
            }

            $this->generateTokenForExam($exam);

            return redirect()->route('admin.tokens.index')
                ->with('success', 'Token berhasil diperbarui: ' . $exam->token);
        } catch (\Exception $e) {
            return redirect()->route('admin.tokens.index')
                ->with('error', 'Gagal memperbarui token: ' . $e->getMessage());
        }
    }

    /**
     * Update exam token (manual override).
     */
    public function updateToken(Request $request, Exam $exam)
    {
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized subject access'], 403);
        }

        $request->validate([
            'token' => 'required|string|max:10|unique:exams,token,' . $exam->id,
        ]);

        try {
            if ($exam->status !== 'published') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ujian harus dipublikasikan terlebih dahulu.',
                ], 400);
            }

            $exam->update([
                'token' => strtoupper($request->token),
                'token_last_updated' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token berhasil diperbarui.',
                'token' => $exam->token,
                'token_last_updated' => $exam->token_last_updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui token: ' . $e->getMessage(),
            ], 500);
        }
    }
}
