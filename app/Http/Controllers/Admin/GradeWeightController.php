<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeWeight;
use App\Models\Subject;
use Illuminate\Http\Request;

class GradeWeightController extends Controller
{
    /**
     * Display a listing of grade weights.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'superadmin') {
            $weights = GradeWeight::with(['subject', 'teacher'])
                ->orderByDesc('updated_at')
                ->paginate(20);
        } else {
            // Teacher: only their own weights
            $weights = GradeWeight::with(['subject', 'teacher'])
                ->where('teacher_id', $user->id)
                ->orderByDesc('updated_at')
                ->paginate(20);
        }

        return view('admin.grade-weights.index', compact('weights'));
    }

    /**
     * Show the form for creating a new grade weight.
     */
    public function create()
    {
        $subjects = $this->getSubjectsForUser();
        $jenjangs = \App\Models\ClassRoom::active()->distinct()->pluck('grade')->sort();
        return view('admin.grade-weights.create', compact('subjects', 'jenjangs'));
    }

    /**
     * Store a newly created grade weight in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validateRequest($request);

        // Custom rule: total must be 100
        $total = $data['weight_harian'] + $data['weight_uts'] + $data['weight_uas'];
        if (abs($total - 100) > 0.01) {
            return back()->withInput()->withErrors([
                'weight_harian' => 'Total bobot harus 100%. Saat ini: ' . $total . '%',
            ]);
        }

        $data['teacher_id'] = auth()->id();

        // updateOrCreate: guru bisa update konfigurasi yang sudah ada
        // (jika akses halaman create tapi entry sudah ada untuk jenjang ini)
        GradeWeight::updateOrCreate(
            [
                'subject_id'    => $data['subject_id'],
                'teacher_id'    => $data['teacher_id'],
                'jenjang'       => $data['jenjang'],
                'semester'      => $data['semester'],
                'academic_year' => $data['academic_year'],
            ],
            [
                'weight_harian' => $data['weight_harian'],
                'weight_uts'    => $data['weight_uts'],
                'weight_uas'    => $data['weight_uas'],
            ]
        );

        return redirect()->route('admin.grade-weights.index')
            ->with('success', 'Konfigurasi bobot nilai berhasil disimpan.');
    }

    /**
     * Show the form for editing the specified grade weight.
     */
    public function edit(GradeWeight $gradeWeight)
    {
        $this->authorizeWeight($gradeWeight);
        $subjects = $this->getSubjectsForUser();
        $jenjangs = \App\Models\ClassRoom::active()->distinct()->pluck('grade')->sort();

        return view('admin.grade-weights.edit', compact('gradeWeight', 'subjects', 'jenjangs'));
    }

    /**
     * Update the specified grade weight in storage.
     */
    public function update(Request $request, GradeWeight $gradeWeight)
    {
        $this->authorizeWeight($gradeWeight);

        $data = $this->validateRequest($request);

        // Custom rule: total must be 100
        $total = $data['weight_harian'] + $data['weight_uts'] + $data['weight_uas'];
        if (abs($total - 100) > 0.01) {
            return back()->withInput()->withErrors([
                'weight_harian' => 'Total bobot harus 100%. Saat ini: ' . $total . '%',
            ]);
        }

        $gradeWeight->update($data);

        return redirect()->route('admin.grade-weights.index')
            ->with('success', 'Bobot nilai berhasil diperbarui.');
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    /**
     * Validate the request for store/update.
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'subject_id'     => 'required|exists:subjects,id',
            'jenjang'        => 'required|integer|min:1|max:12',
            'semester'       => 'required|in:1,2',
            'academic_year'  => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'weight_harian'  => 'required|numeric|min:0|max:100',
            'weight_uts'     => 'required|numeric|min:0|max:100',
            'weight_uas'     => 'required|numeric|min:0|max:100',
        ], [
            'academic_year.regex' => 'Format tahun ajaran harus: YYYY/YYYY (contoh: 2024/2025)',
            'jenjang.required'    => 'Kelas/Jenjang wajib dipilih.',
        ]);
    }

    /**
     * Get subjects scoped by the current user's role.
     */
    private function getSubjectsForUser()
    {
        $user = auth()->user();
        if ($user->role === 'teacher') {
            return $user->subjects()->orderBy('name')->get();
        }
        return Subject::orderBy('name', 'asc')->get();
    }

    /**
     * Authorize that the current user owns the grade weight.
     */
    private function authorizeWeight(GradeWeight $weight): void
    {
        if (auth()->user()->role === 'teacher' && $weight->teacher_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this grade weight configuration.');
        }
    }
}
